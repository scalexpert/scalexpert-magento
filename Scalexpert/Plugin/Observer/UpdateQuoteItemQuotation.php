<?php

namespace Scalexpert\Plugin\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Quote\Api\Data\CartInterface;

class UpdateQuoteItemQuotation implements ObserverInterface
{
    const INSURANCE_SKU = 'Insurance';

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;


    protected $logger;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory;
     */
    protected $quoteItemCollectionFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Scalexpert\Plugin\Model\RestApi
     */
    protected $restApi;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;

    protected $messageManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    protected $scalexpertHelper;

    /**
     * @var Json|null
     */
    private $serializer;

    /**
     * @param \Scalexpert\Plugin\Logger\Logger $logger
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Scalexpert\Plugin\Model\RestApi $restApi
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Scalexpert\Plugin\Model\Helper $scalexpertHelper,
     * @param Json $serializer
     */
    public function __construct(
        \Scalexpert\Plugin\Logger\Logger $logger,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Scalexpert\Plugin\Model\RestApi $restApi,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        ManagerInterface $messageManager,
        \Magento\Framework\App\RequestInterface $request,
        \Scalexpert\Plugin\Model\Helper $scalexpertHelper,
        Json $serializer
    ) {
        $this->logger = $logger;
        $this->checkoutSession = $checkoutSession;
        $this->quoteItemCollectionFactory = $quoteItemCollectionFactory;
        $this->productRepository = $productRepository;
        $this->restApi = $restApi;
        $this->cartRepository = $cartRepository;
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->scalexpertHelper = $scalexpertHelper;
        $this->serializer = $serializer;
    }

    public function execute(Observer $observer)
    {
        $updates = $observer->getInfo()->getData();
        $quote = $observer->getCart()->getQuote();
        $parentAsQtyChange = false;
        foreach($updates as $itemId => $update){
            $quoteItemsCollection = $this->quoteItemCollectionFactory->create();
            $item = $quoteItemsCollection
                ->addFieldToSelect('*')
                ->addFieldToFilter('item_id', [$itemId])
                ->getFirstItem();
            $param = 'item_'.$itemId.'_insurances';
            $insurance_value = $this->request->getParam($param);
            $insuranceId = false;
            $solutionCode = false;
            if($insurance_value){
                $insuranceId = preg_replace('/^[^\|]*\|(.*)$/', '$1', $insurance_value);
                $solutionCode = preg_replace('/^([^\|]*)\|.*$/', '$1', $insurance_value);
            }
            $insuranceQuoteItem = $quote->getItemById($item->getId());
            $insuranceInfosItem = $this->scalexpertHelper->getCurrentInsuranceForQuoteItem($insuranceQuoteItem);
            if($item->getSku() == self::INSURANCE_SKU){
                if($item->getQty() != $update['qty'] && !$parentAsQtyChange){
                    $this->messageManager->addNoticeMessage(__("The insurance qty cannot be changed."));
                    $insuranceQuoteItem->setQty($item->getQty());
                    $this->cartRepository->save($quote);
                    $quote->setTriggerRecollect(1);
                    $quote->setIsActive(true);
                    $quote->setTotalsCollectedFlag(false)->collectTotals()->save();
                    $this->checkoutSession->replaceQuote($quote);
                    return $this;
                }
                else if($insuranceInfosItem[0]['insurance_id'] != $insuranceId) {
                    $itemPrice = (float)($insuranceInfosItem[0]['quote_item_price']);
                    $insuranceItemId = $insuranceInfosItem[0]['insurance_item_id'];
                    if($insuranceId == 0){
                        $insuranceQuoteItem->delete();
                        $this->cartRepository->save($quote);
                        $quote->setTriggerRecollect(1);
                        $quote->setIsActive(true);
                        $quote->setTotalsCollectedFlag(false)->collectTotals()->save();
                        $this->checkoutSession->replaceQuote($quote);
                        return $this;
                    }
                    $qtyToAdd = $item->getQty();
                    $insurancesQuoteIds = array();
                    while ($qtyToAdd > 0) {
                        --$qtyToAdd;
                        $quotation_result = $this->restApi->initializeInsuranceQuotation($solutionCode, $insuranceItemId, $itemPrice, $insuranceId);
                        if ($quotation_result['result']) {
                            $insurancesQuoteIds[] = $quotation_result['result']->quoteId;
                            $insurancePrice = $quotation_result['result']->insurancePrice;
                            $expirationDate = $quotation_result['result']->expirationDate;
                        }
                    }
                    if(count($insurancesQuoteIds) > 0) {
                        $additionalOptions[] = array(
                            'quote_id' => $insuranceInfosItem[0]['quote_id'],
                            'quote_item_price' => $insuranceInfosItem[0]['quote_item_price'],
                            'solution_code' => $insuranceInfosItem[0]['solution_code'],
                            'insurance_item_id' => $insuranceInfosItem[0]['insurance_item_id'],
                            'insurance_id' => $insuranceId,
                            'insurance_price' => $insurancePrice,
                            'insurance_expiration_date' => $expirationDate,
                            'quote_item_id' => $insuranceInfosItem[0]['quote_item_id'],
                            'insurance_quote_item_id' => $insuranceInfosItem[0]['insurance_quote_item_id'],
                            'insurance_quote_id' => $insurancesQuoteIds
                        );
                        $insuranceQuoteItem->addOption(
                            new \Magento\Framework\DataObject(
                                [
                                    'product' => $insuranceQuoteItem->getProduct(),
                                    'code' => 'info_insurance',
                                    'value' => $this->serializer->serialize(array($additionalOptions[0]))
                                ]
                            )
                        );
                        $insuranceItemId = $this->request->getParam('insurance_item_id');
                        $searchItem = $this->restApi->searchInsuranceItem($solutionCode, $itemPrice, $insuranceItemId);
                        if (!$searchItem['status']) {
                            return $this;
                        }
                        $insuranceItem = false;
                        foreach ($searchItem['result']->insurances as $insuranceItem_tmp) {
                            if (!$insuranceItem && $insuranceItem_tmp->id == $insuranceId) {
                                $insuranceItem = $insuranceItem_tmp;
                            }
                        }
                        if($insuranceItem){
                            $insuranceQuoteParentItem = $quote->getItemById($insuranceInfosItem[0]['quote_item_id']);
                            $additionalOptionsItem[] = array(
                                'label' => $insuranceQuoteParentItem->getName(),
                                'value' => $insuranceItem->description,
                            );
                            $insuranceQuoteItem->addOption(
                                new \Magento\Framework\DataObject(
                                    [
                                        'product' => $insuranceQuoteItem->getProduct(),
                                        'code' => 'additional_options',
                                        'value' => $this->serializer->serialize($additionalOptionsItem)
                                    ]
                                )
                            );
                        }
                        $insuranceQuoteItem->setCustomPrice($insurancePrice);
                        $insuranceQuoteItem->setOriginalCustomPrice($insurancePrice);
                        $insuranceQuoteItem->getProduct()->setIsSuperMode(true);
                    }
                }
            }
            else if($insuranceId && $solutionCode){
                        $insuranceItemId = $this->request->getParam('insurance_item_id');
                        $itemPrice = (float)($item->getPrice());
                        $searchItem = $this->restApi->searchInsuranceItem($solutionCode, $itemPrice, $insuranceItemId);
                        if (!$searchItem['status']) {
                            return $this;
                        }
                        $insuranceItem = false;
                        foreach ($searchItem['result']->insurances as $insuranceItem_tmp) {
                            if (!$insuranceItem && $insuranceItem_tmp->id == $insuranceId) {
                                $insuranceItem = $insuranceItem_tmp;
                            }
                        }
                        $qtyToAdd = $item->getQty();
                        $insurancesQuoteIds = array();
                        while ($qtyToAdd > 0) {
                            --$qtyToAdd;
                            $quotation_result = $this->restApi->initializeInsuranceQuotation($solutionCode, $insuranceItemId, $itemPrice, $insuranceId);
                            if ($quotation_result['result']) {
                                $insurancesQuoteIds[] = $quotation_result['result']->quoteId;
                                $insurancePrice = $quotation_result['result']->insurancePrice;
                                $expirationDate = $quotation_result['result']->expirationDate;
                            }
                        }
                        if(count($insurancesQuoteIds) > 0) {
                            $insuranceProduct = $this->productRepository->get(self::INSURANCE_SKU);
                            $insuranceProductItem = $quote->addProduct($insuranceProduct, $item->getQty());
                            $insuranceProductItem->setCustomPrice($insurancePrice);
                            $insuranceProductItem->setOriginalCustomPrice($insurancePrice);
                            $insuranceProductItem->getProduct()->setIsSuperMode(true);
                            $this->cartRepository->save($quote);
                            $additionalOptions[] = array(
                                'quote_id' => $quote->getId(),
                                'quote_item_price' => $itemPrice,
                                'solution_code' => $solutionCode,
                                'insurance_item_id' => $insuranceItemId,
                                'insurance_id' => $insuranceId,
                                'insurance_price' => $insurancePrice,
                                'insurance_expiration_date' => $expirationDate,
                                'quote_item_id' => $item->getId(),
                                'insurance_quote_item_id' => $insuranceProductItem->getId(),
                                'insurance_quote_id' => $insurancesQuoteIds
                            );
                            $insuranceProductItem->addOption(
                                new \Magento\Framework\DataObject(
                                    [
                                        'product' => $insuranceProductItem->getProduct(),
                                        'code' => 'info_insurance',
                                        'value' => $this->serializer->serialize(array($additionalOptions[0]))
                                    ]
                                )
                            );
                            if($insuranceItem){
                                $additionalOptionsItem[] = array(
                                    'label' => $item->getName(),
                                    'value' => $insuranceItem->description,
                                );
                                $insuranceProductItem->addOption(
                                    new \Magento\Framework\DataObject(
                                        [
                                            'product' => $insuranceProductItem->getProduct(),
                                            'code' => 'additional_options',
                                            'value' => $this->serializer->serialize($additionalOptionsItem)
                                        ]
                                    )
                                );
                            }
                        }
            }
            $quoteHasInsurance = false;
            $insuranceQuoteItemId = false;
            $quoteIdsInsurance = array();
            $itemsQuote = $quote->getAllItems();
            foreach($itemsQuote as $itemQuote){
                if($itemQuote->getSku() == self::INSURANCE_SKU){
                    $quoteIdsInsuranceOption = $itemQuote->getOptionByCode('info_insurance');
                    if($quoteIdsInsuranceOption){
                        $quoteInsuranceOptions = $this->serializer->unserialize($quoteIdsInsuranceOption->getValue());
                        if($quoteInsuranceOptions[0]['quote_item_id'] == $itemId && $item->getQty() != $update['qty']){
                            $quoteHasInsurance = true;
                            $quoteIdsInsurance = $quoteInsuranceOptions[0]['insurance_quote_id'];
                            $insuranceQuoteItemId = $quoteInsuranceOptions[0]['insurance_quote_item_id'];
                            $additionalOptions[] = array(
                                'quote_id' => $quoteInsuranceOptions[0]['quote_id'],
                                'quote_item_price' =>  $quoteInsuranceOptions[0]['quote_item_price'],
                                'solution_code' =>  $quoteInsuranceOptions[0]['solution_code'],
                                'insurance_item_id' =>  $quoteInsuranceOptions[0]['insurance_item_id'],
                                'insurance_id' =>  $quoteInsuranceOptions[0]['insurance_id'],
                                'insurance_price' =>  $quoteInsuranceOptions[0]['insurance_price'],
                                'insurance_expiration_date' =>  $quoteInsuranceOptions[0]['insurance_expiration_date'],
                                'quote_item_id' =>  $quoteInsuranceOptions[0]['quote_item_id'],
                                'insurance_quote_item_id' =>  $quoteInsuranceOptions[0]['insurance_quote_item_id']
                            );
                        }
                    }
                }
            }
            $insuranceQuoteItem = $quote->getItemById($insuranceQuoteItemId);
            if($item->getQty() != $update['qty']){
                $parentAsQtyChange = true;
                if($item->getQty() > $update['qty']){
                    $qtyToDelete = $item->getQty() - $update['qty'];
                    if($quoteHasInsurance && count($quoteIdsInsurance) > $update['qty']){
                        $quoteIdsInsurance = array_slice($quoteIdsInsurance, $qtyToDelete);
                        $additionalOptions[0]['insurance_quote_id'] = $quoteIdsInsurance;
                        $insuranceQuoteItem->addOption(
                            new \Magento\Framework\DataObject(
                                [
                                    'product' => $insuranceQuoteItem->getProduct(),
                                    'code' => 'info_insurance',
                                    'value' => $this->serializer->serialize(array($additionalOptions[0]))
                                ]
                            )
                        );
                        $insuranceQuoteItem->setQty($insuranceQuoteItem->getQty() - $qtyToDelete);
                        $insuranceQuoteItem->getProduct()->setIsSuperMode(true);
                    }
                }
                else{
                    $qtyToAdd = $update['qty'] - $item->getQty();
                    if($quoteHasInsurance && (count($quoteIdsInsurance) < $update['qty'])){
                        $solutionCode = $quoteInsuranceOptions[0]['solution_code'];
                        $quotationItemId = $quoteInsuranceOptions[0]['insurance_item_id'];
                        $itemPrice = (float)($quoteInsuranceOptions[0]['quote_item_price']);
                        $insuranceId = $quoteInsuranceOptions[0]['insurance_id'];
                        while ($qtyToAdd > 0) {
                            --$qtyToAdd;
                            $quotation_result = $this->restApi->initializeInsuranceQuotation($solutionCode, $quotationItemId, $itemPrice, $insuranceId);
                            if ($quotation_result['result']) {
                                array_push($quoteIdsInsurance,$quotation_result['result']->quoteId);
                            }
                        }
                        $additionalOptions[0]['insurance_quote_id'] = $quoteIdsInsurance;
                        $insuranceQuoteItem->addOption(
                            new \Magento\Framework\DataObject(
                                [
                                    'product' => $insuranceQuoteItem->getProduct(),
                                    'code' => 'info_insurance',
                                    'value' => $this->serializer->serialize(array($additionalOptions[0]))
                                ]
                            )
                        );
                        $insuranceQuoteItem->setQty($insuranceQuoteItem->getQty() + ($update['qty'] - $item->getQty()));
                        $insuranceQuoteItem->getProduct()->setIsSuperMode(true);
                    }
                }
                $this->cartRepository->save($quote);
                $quote->setTriggerRecollect(1);
                $quote->setIsActive(true);
                $quote->setTotalsCollectedFlag(false)->collectTotals()->save();
                $this->checkoutSession->replaceQuote($quote);
            }
        }
        return $this;
    }


}
