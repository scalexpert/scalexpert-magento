<?php
namespace Scalexpert\Plugin\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;

class ConvertQuoteItemQuotation implements ObserverInterface
{
    /**
     * @var Json|null
     */
    private $serializer;

    public function __construct(
        Json $serializer
    ) {
        $this->serializer = $serializer;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $quote = $observer->getQuote();
            $order = $observer->getOrder();
            $quoteItems = [];
            foreach ($quote->getAllVisibleItems() as $quoteItem) {
                $quoteItems[$quoteItem->getId()] = $quoteItem;
            }
            foreach ($order->getAllVisibleItems() as $orderItem) {
                $quoteItemId = $orderItem->getQuoteItemId();
                $quoteItem = $quoteItems[$quoteItemId];
                $additionalOptions = $quoteItem->getOptionByCode('additional_options');
                if(isset($additionalOptions)){
                    $options = $orderItem->getProductOptions();
                    $options['additional_options'] = $this->serializer->unserialize($additionalOptions->getValue());
                    $orderItem->setProductOptions($options);
                }
                $insuranceInformations = $quoteItem->getOptionByCode('info_insurance');
                if(isset($insuranceInformations)){
                    $options = $orderItem->getProductOptions();
                    $options['info_insurance'] = $this->serializer->unserialize($insuranceInformations->getValue());
                    $orderItem->setProductOptions($options);
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
