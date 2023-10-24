<?php

namespace Scalexpert\Plugin\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Quote\Api\Data\CartInterface;

class RemoveQuoteItemQuotation implements ObserverInterface
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
     * @var Json|null
     */
    private $serializer;

    /**
     * @param \Scalexpert\Plugin\Logger\Logger $logger
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory
     * @param Json $serializer
     */
    public function __construct(
        \Scalexpert\Plugin\Logger\Logger $logger,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory,
        Json $serializer
    ) {
        $this->logger = $logger;
        $this->checkoutSession = $checkoutSession;
        $this->quoteItemCollectionFactory = $quoteItemCollectionFactory;
        $this->serializer = $serializer;
    }

    public function execute(Observer $observer)
    {
        $quote_item = $observer->getQuoteItem();
        $quote = $this->checkoutSession->getQuote();
        $product = $quote_item->getProduct();
        if($product->getSku() == self::INSURANCE_SKU){
            return $this;
        }
        else{
            $itemsQuote = $quote->getAllItems();
            foreach($itemsQuote as $itemQuote){
                if($itemQuote->getSku() == self::INSURANCE_SKU){
                    $quoteInsuranceOption = $itemQuote->getOptionByCode('info_insurance');
                    if($quoteInsuranceOption){
                        $quoteInsuranceOptions = $this->serializer->unserialize($quoteInsuranceOption->getValue());
                        $itemQuoteId = $quoteInsuranceOptions[0]['quote_item_id'];
                        if($quote_item->getId() == $itemQuoteId){
                            $itemQuote->delete();
                        }
                    }
                }
            }
            $quote->setTriggerRecollect(1);
            $quote->setIsActive(true);
            $quote->setTotalsCollectedFlag(false)->collectTotals()->save();
            $this->checkoutSession->replaceQuote($quote);
        }

        return $this;
    }

}
