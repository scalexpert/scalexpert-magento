<?php
namespace Scalexpert\Plugin\Block\Payment;

use Magento\Framework\Serialize\Serializer\Json;

class Info extends \Magento\Payment\Block\Info
{
    /**
     * @var string
     */
    protected $_template = 'Scalexpert_Plugin::financing/info.phtml';

    /**
     * @var Json|null
     */
    private $serializer;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory $trsCollectionFactory,
        Json $serializer,
        array $data = []
    ) {
        $this->localeResolver = $localeResolver;
        $this->trsCollectionFactory = $trsCollectionFactory;
        $this->serializer = $serializer;
        parent::__construct($context, $data);
    }

    public function getPaymentDetail()
    {
        $payment = $this->getInfo();
        $info = array();
        if($payment->getAdditionalData()){
            $add = $this->serializer->unserialize($payment->getAdditionalData());
            $info['credit_subscription_id'] = isset($add['credit_subscription_id']) ? $add['credit_subscription_id'] : false;
            $info['registration_timestamp'] = isset($add['registration_timestamp']) ? $add['registration_timestamp'] : false;
            $info['last_update_timestamp'] = isset($add['last_update_timestamp']) ? $add['last_update_timestamp'] : false;
            $info['solution_code'] = isset($add['solution_code']) ? $add['solution_code'] : false;
            $info['merchant_basket_id'] = isset($add['merchant_basket_id']) ? $add['merchant_basket_id'] : false;
            $info['merchant_global_order_id'] = isset($add['merchant_global_order_id']) ? $add['merchant_global_order_id'] : false;
            $info['buyer_financedAmount'] = isset($add['buyer_financedAmount']) ? $add['buyer_financedAmount'] : false;
            $info['consolidated_status'] = isset($add['consolidated_status']) ? $add['consolidated_status'] : false;
            $info['method_title'] = $payment->getMethodInstance()->getTitle();
        }
            return $info;
    }
}
