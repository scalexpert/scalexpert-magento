<?php

namespace Scalexpert\Plugin\Model;

use Magento\Framework\Serialize\Serializer\Json;

class Helper
{
    protected $restApi;

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
     * @var Json|null
     */
    private $serializer;

    public function __construct(
        \Scalexpert\Plugin\Model\RestApi $restApi,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory,
        Json $serializer
    ) {
        $this->restApi = $restApi;
        $this->checkoutSession = $checkoutSession;
        $this->quoteItemCollectionFactory = $quoteItemCollectionFactory;
        $this->serializer = $serializer;
    }

    public function getWarranty($product, $countryId = 'FR')
    {
        $solutions = $this->restApi->getInsuranceEligibleSolutions($countryId);

        $searchData = array();

        if ($solutions['status']) {
            foreach ($solutions['result']->solutions as $solution) {
                $solutionCode = $solution->solutionCode;
                $solutionResult = $this->restApi->createInsuranceItem($solutionCode, $product);

                if ($solutionResult['status']) {
                    $itemId = $solutionResult['result']->id;
                    $searchItem = $this->restApi->searchInsuranceItem($solutionCode, $product->getPrice(), $itemId);
                    if ($searchItem['status']) {
                        array_push($searchData, $searchItem);
                    }
                }
            }
        }

        $data = array(
            'solutions' => $solutions,
            'items' => $searchData
        );

        return $data;
    }

    public function getCurrentInsuranceQuotationForProduct($sku)
    {
        $quote = $this->checkoutSession->getQuote();
        $insuranceId = false;
        if($quote){
            $itemsQuote = $quote->getAllItems();
            $itemId = false;
            $insuranceItemIds = array();
            foreach($itemsQuote as $itemQuote) {
                if($itemQuote->getSku() == \Scalexpert\Plugin\Observer\CreateQuoteItemQuotation::INSURANCE_SKU){
                    array_push($insuranceItemIds,$itemQuote->getId());
                }
                if($itemQuote->getSku() == $sku){
                    $itemId = $itemQuote->getId();
                }
            }
            if(count($insuranceItemIds) > 0){
                foreach($insuranceItemIds as $insuranceItemId){
                    $insuranceQuoteItem = $quote->getItemById($insuranceItemId);
                    $quoteInsuranceOption = $insuranceQuoteItem->getOptionByCode('info_insurance');
                    if($quoteInsuranceOption){
                        $insuranceOptions = $this->serializer->unserialize($quoteInsuranceOption->getValue());
                        if($insuranceOptions[0]['quote_item_id'] == $itemId){
                            $insuranceId = $insuranceOptions[0]['insurance_id'];
                        }
                    }
                }
            }
        }
        return $insuranceId;
    }

    public function getCurrentInsuranceQuotationForQuoteItem($quoteItem)
    {
        $insuranceId = false;
        if($quoteItem){
                    $quoteIdsInsuranceOption = $quoteItem->getOptionByCode('info_insurance');
                    if($quoteIdsInsuranceOption){
                        $insuranceOptions = $this->serializer->unserialize($quoteIdsInsuranceOption->getValue());
                        $insuranceId = $insuranceOptions[0]['insurance_id'];
                    }
        }
        return $insuranceId;
    }

    public function getCurrentInsuranceProductNameForQuote($quoteItemId)
    {
        $quote = $this->checkoutSession->getQuote();
        $insuranceProductName = false;
        if($quoteItemId){
            $insuranceProductName = $quote->getItemById($quoteItemId)->getName();
        }
        return $insuranceProductName;
    }

    public function getCurrentInsuranceForQuote()
    {
        $quote = $this->checkoutSession->getQuote();
        $insuranceItemIds = array();
        $insurances = array();
        if($quote){
            $itemsQuote = $quote->getAllItems();
            foreach($itemsQuote as $itemQuote) {
                if ($itemQuote->getSku() == \Scalexpert\Plugin\Observer\CreateQuoteItemQuotation::INSURANCE_SKU) {
                    array_push($insuranceItemIds, $itemQuote->getId());
                }
            }
            if(count($insuranceItemIds) > 0){
                foreach ($insuranceItemIds as $insuranceItemId) {
                    $insuranceQuoteItem = $quote->getItemById($insuranceItemId);
                    $quoteIdsInsuranceOption = $insuranceQuoteItem->getOptionByCode('info_insurance');
                    if($quoteIdsInsuranceOption){
                        $insuranceOptions = $this->serializer->unserialize($quoteIdsInsuranceOption->getValue());
                        $insurances[$insuranceItemId] = $insuranceOptions[0]['quote_item_id'];
                    }
                }
            }
        }
        return $insurances;
    }

    public function getCurrentInsuranceForQuoteItem($quoteItem)
    {
        $insurance = array();
        if($quoteItem){
            $quoteInfoInsurance = $quoteItem->getOptionByCode('info_insurance');
            if($quoteInfoInsurance){
                $insuranceInfos = $this->serializer->unserialize($quoteInfoInsurance->getValue());
                foreach($insuranceInfos as $key => $insuranceInfo){
                    $insurance[$key] = $insuranceInfo;
                }
            }
        }
        return $insurance;
    }
}
