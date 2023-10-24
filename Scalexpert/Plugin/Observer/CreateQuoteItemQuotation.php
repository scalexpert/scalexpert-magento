<?php

namespace Scalexpert\Plugin\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Serialize\Serializer\Json;

class CreateQuoteItemQuotation implements ObserverInterface
{
    const INSURANCE_SKU = 'Insurance';

    /**
     * @var \Scalexpert\Plugin\Model\SystemConfigData
     */
    protected $systemConfigData;

    /**
     * @var \Scalexpert\Plugin\Model\RestApi
     */
    protected $restApi;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;


    protected $logger;


    protected $scalexpertHelper;

    /**
     * @var Json|null
     */
    private $serializer;

    /**
     * @param \Scalexpert\Plugin\Logger\Logger $logger
     * @param \Scalexpert\Plugin\Model\SystemConfigData $systemConfigData
     * @param \Scalexpert\Plugin\Model\RestApi $restApi
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Scalexpert\Plugin\Model\Helper $scalexpertHelper
     * @param Json $serializer
     *         \Scalexpert\Plugin\Model\Helper $scalexpertHelper,

     */
    public function __construct(
        \Scalexpert\Plugin\Logger\Logger $logger,
        \Scalexpert\Plugin\Model\SystemConfigData $systemConfigData,
        \Scalexpert\Plugin\Model\RestApi $restApi,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Scalexpert\Plugin\Model\Helper $scalexpertHelper,
        Json $serializer
    ) {
        $this->logger = $logger;
        $this->systemConfigData = $systemConfigData;
        $this->restApi = $restApi;
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
        $this->scalexpertHelper = $scalexpertHelper;
        $this->serializer = $serializer;
    }

    public function execute(Observer $observer)
    {

        $quote_item = $observer->getQuoteItem();
        $quote = $this->checkoutSession->getQuote();
        $insuranceProduct = $this->productRepository->get(self::INSURANCE_SKU);
        $product = $quote_item->getProduct();
        if($product->getSku() == self::INSURANCE_SKU){
            return $this;
        }
        $solutionCode = null;
        $insuranceId = null;
        $insuranceItemIds = array();
        $insurance_value = $this->request->getParam('insurances');
        if($insurance_value) {
            $this->logger->info('----------- CreateQuoteItemQuotation ');
            $solutionCode = preg_replace('/^([^\|]*)\|.*$/', '$1', $insurance_value);
            $insuranceId = preg_replace('/^[^\|]*\|(.*)$/', '$1', $insurance_value);
            if($insuranceId == 0){
                $itemsQuote = $quote->getAllItems();
                foreach($itemsQuote as $itemQuote){
                    if($itemQuote->getSku() == self::INSURANCE_SKU){
                        array_push($insuranceItemIds,$itemQuote->getId());
                    }
                }
                if(count($insuranceItemIds) > 0){
                    foreach ($insuranceItemIds as $insuranceItemId){
                        $insuranceQuoteItem = $quote->getItemById($insuranceItemId);
                        $quoteInsuranceOption = $insuranceQuoteItem->getOptionByCode('info_insurance');
                        if($quoteInsuranceOption){
                            $insuranceOptions = $this->serializer->unserialize($quoteInsuranceOption->getValue());
                            if($insuranceOptions[0]['quote_item_id'] == $quote_item->getId()){
                                $quote->deleteItem($insuranceQuoteItem);
                                return $this;
                            }
                        }
                    }
                }
            }
            $solutionResult = $this->restApi->createInsuranceItem($solutionCode, $product);
            if ($solutionResult['status']) {
                $itemId = $solutionResult['result']->id;
                $searchItem = $this->restApi->searchInsuranceItem($solutionCode, $product->getPrice(), $itemId);
                if (!$searchItem['status']) {
                    return $this;
                }
                $insuranceItem = false;
                foreach ($searchItem['result']->insurances as $insuranceItem_tmp) {
                    if (!$insuranceItem && $insuranceItem_tmp->id == $insuranceId) {
                        $insuranceItem = $insuranceItem_tmp;
                    }
                }
                if (!$insuranceItem) {
                    return $this;
                }
            } else {
                return $this;
            }
            $insuranceItemQty = 0;
            $itemsQuote = $quote->getAllItems();
            $insuranceItemIds = array();
            foreach($itemsQuote as $itemQuote){
                if($itemQuote->getSku() == self::INSURANCE_SKU){
                    array_push($insuranceItemIds,$itemQuote->getId());
                }
            }
            if(count($insuranceItemIds) > 0){
                foreach ($insuranceItemIds as $insuranceItemId){
                    $insuranceQuoteItem = $quote->getItemById($insuranceItemId);
                    $quoteInsuranceOption = $insuranceQuoteItem->getOptionByCode('info_insurance');
                    if($quoteInsuranceOption){
                        $insuranceOptions = $this->serializer->unserialize($quoteInsuranceOption->getValue());
                        if($insuranceOptions[0]['quote_item_id'] == $quote_item->getId()){
                            $insuranceItemQty = $itemQuote->getQty();
                        }
                    }
                }
            }
            $itemPrice = $quote_item->getPrice();
            $itemQty = $quote_item->getQty();
            $qtyToAdd = $itemQty - $insuranceItemQty;
            while ($qtyToAdd > 0) {
                --$qtyToAdd;
                $quotation_result = $this->restApi->initializeInsuranceQuotation($solutionCode, $itemId, $itemPrice, $insuranceId);
                if ($quotation_result['result']) {
                    $insurancesQuoteIds[] = $quotation_result['result']->quoteId;
                    $insurancePrice = $quotation_result['result']->insurancePrice;
                    $expirationDate = $quotation_result['result']->expirationDate;
                }
            }

            $insuranceQuoteItemId = null;
            $quoteHasInsurance = false;
            $insuranceItemIds = array();
            $itemsQuote = $quote->getAllItems();
            foreach($itemsQuote as $itemQuote){
                    if($itemQuote->getSku() == self::INSURANCE_SKU){
                        array_push($insuranceItemIds,$itemQuote->getId());
                    }
            }
            if(count($insuranceItemIds) > 0){
                foreach ($insuranceItemIds as $insuranceItemId){
                    $insuranceQuoteItem = $quote->getItemById($insuranceItemId);
                    $quoteIdsInsuranceOption = $insuranceQuoteItem->getOptionByCode('info_insurance');
                    if($quoteIdsInsuranceOption){
                        $quoteIdsInsurance = $this->serializer->unserialize($quoteIdsInsuranceOption->getValue());
                        $this->logger->info('----------- unserialize ', ['value' => $quoteIdsInsurance[0]]);

                        if($quoteIdsInsurance[0]['quote_item_id'] == $quote_item->getId()){
                            $quoteHasInsurance = true;
                            $quoteIdsInsurance = $quoteIdsInsurance[0]['insurance_quote_id'];
                            $insurancesQuoteIds = array_merge($insurancesQuoteIds,$quoteIdsInsurance);
                            $insuranceQuoteItemId = $itemQuote->getId();
                        }
                    }
                }
            }
            if ($quoteHasInsurance) {
                $insuranceQuoteItem = $quote->getItemById($insuranceQuoteItemId);
                $insuranceQuoteItem->setQty($itemQty);
                $insuranceQuoteItem->setCustomPrice($insurancePrice);
                $insuranceQuoteItem->setOriginalCustomPrice($insurancePrice);
                $insuranceQuoteItem->getProduct()->setIsSuperMode(true);
            } else {
                $insuranceProductItem = $quote->addProduct($insuranceProduct, $itemQty);
                $insuranceProductItem->setCustomPrice($insurancePrice);
                $insuranceProductItem->setOriginalCustomPrice($insurancePrice);
                $insuranceProductItem->getProduct()->setIsSuperMode(true);
            }
            $this->cartRepository->save($quote);
            $quote->setTriggerRecollect(1);
            $quote->setIsActive(true);
            $quote->setTotalsCollectedFlag(false)->collectTotals()->save();
            $this->checkoutSession->replaceQuote($quote);
            if (!$insuranceQuoteItemId) {
                $insuranceQuoteItemId = $insuranceProductItem->getId();
            }
            $insuranceQuoteItem = $quote->getItemById($insuranceQuoteItemId);

            $additionalOptions[] = array(
                'quote_id' => $quote_item->getQuoteId(),
                'quote_item_price' => $itemPrice,
                'solution_code' => $solutionCode,
                'insurance_item_id' => $itemId,
                'insurance_id' => $insuranceId,
                'insurance_quote_id' => $insurancesQuoteIds,
                'insurance_price' => $insurancePrice,
                'insurance_expiration_date' => $expirationDate,
                'quote_item_id' => $quote_item->getId(),
                'insurance_quote_item_id' => $insuranceQuoteItemId
            );
            $insuranceQuoteItem->addOption(
                new \Magento\Framework\DataObject(
                    [
                        'product' => $insuranceQuoteItem->getProduct(),
                        'code' => 'info_insurance',
                        'value' => $this->serializer->serialize($additionalOptions)
                    ]
                )
            );
            if($insuranceItem){
                $additionalOptionsItem[] = array(
                    'label' => $product->getName(),
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
            $this->cartRepository->save($quote);
            $this->logger->info('----------- End CreateQuoteItemQuotation ');
        }

        return $this;

    }

}
