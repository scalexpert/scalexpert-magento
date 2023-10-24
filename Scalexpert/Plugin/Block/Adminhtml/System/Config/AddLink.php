<?php

namespace Scalexpert\Plugin\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class AddLink extends Field
{

    /**
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    private $_store;

    public function __construct(\Magento\Framework\Locale\Resolver $store, Context $context, array $data = [], ?SecureHtmlRenderer $secureRenderer = null)
    {
        $this->_store = $store;
        parent::__construct($context, $data, $secureRenderer);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        $elementId = $element->getHtmlId();
        $default = 'en';
        $hideShowText = __('Show key');
        $locale = substr($this->_store->getLocale(),0,2);
        $locale = (in_array($locale, array('fr','en'))) ? $locale : $default;
        $html = $element->getElementHtml();
        $html.= "<a href ='https://dev.scalexpert.societegenerale.com/".$locale."/prod' target='_blank'>".__('Find my key')."</a>";
        $html.= "<div>
                    <input type='checkbox' id='show-key-$elementId'>
                    <label for='show-key-$elementId'>$hideShowText</label>
                </div>";

        $html.= "<script>
        require([
            'jquery'
        ], function($) {
            $('#show-key-$elementId').change(function(){
                $(this).prop('checked') ?  $('#$elementId').prop('type', 'text') : $('#$elementId').prop('type', 'password');
            });
        });
        </script>";
        return $html;
    }

}
