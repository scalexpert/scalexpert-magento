<?php
/**
 * Copyright © Scalexpert.
 * This file is part of Scalexpert plugin for Magento 2. See COPYING.md for license details.
 *
 * @author    Scalexpert (https://scalexpert.societegenerale.com/)
 * @copyright Scalexpert
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Scalexpert\Plugin\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\State;

class ValidityStatus extends Field
{
    protected $_template = 'Scalexpert_Plugin::system/config/status.phtml';

    /**
     * @var State
     */
    private $state;

    public function __construct(State $state, Context $context, array $data = [], ?SecureHtmlRenderer $secureRenderer = null)
    {
        parent::__construct($context, $data, $secureRenderer);
        $this->state = $state;
    }

    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    public function getCurrentScopeWebsiteId() {
        $websiteId = 0;
        if ($this->state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $request = $this->_request;
            $websiteId = (int) $request->getParam('website', 0);
        }
        return $websiteId;
    }

    public function getCurrentScopeStoreId() {
        $storeId = 0;
        if ($this->state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $request = $this->_request;
            $storeId = (int) $request->getParam('store', 0);
        }
        return $storeId;
    }
}
