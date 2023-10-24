<?php

namespace Scalexpert\Plugin\Model;

class RestApi
{
    const TIMEOUT = 30;
    const FINANCING = 'financing';
    const INSURANCE = 'insurance';
    const API_URL_ROOT_TEST = 'https://api.scalexpert.hml.societegenerale.com/baas/uatc/';
    const API_URL_ROOT_PRODUCTION = 'https://api.scalexpert.societegenerale.com/baas/prod/';
    const API_URL_AUTH = 'auth-server/api/v1/oauth2/token';
    const API_URL_FINANCING = 'e-financing/api/v1/';
    const API_URL_INSURANCE = 'insurance/api/v1/';

    protected $systemConfigData;
    protected $curl;
    protected $logger;
    protected $_helperData;
    protected $_urlBuilder;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var PaymentRedirectFactory
     */
    protected $paymentRedirectFactory;

    private $_bearer;

    public function __construct(
        \Scalexpert\Plugin\Model\SystemConfigData $systemConfigData,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Scalexpert\Plugin\Logger\Logger $logger,
        \Scalexpert\Plugin\Helper\Data $helperData,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Customer\Model\Session $customerSession,
        PaymentRedirectFactory $paymentRedirectFactory
    ) {
        $this->systemConfigData = $systemConfigData;
        $this->curl = $curl;
        $this->logger = $logger;
        $this->_helperData = $helperData;
        $this->_urlBuilder = $urlBuilder;
        $this->customerSession = $customerSession;
        $this->paymentRedirectFactory = $paymentRedirectFactory;
        $this->_bearer = null;
    }

    /**
     * @param $typeProduct
     * $typeProduct financing or insurance
     * @return array
     */
    public function getBearer($typeProduct, $appId = '', $appKey = '', $appMode = '',$forceDoubleScope = false)
    {
        if (null != $this->_bearer) {
            $this->logger->info("Re use previously bearer");
            return $this->_bearer;
        }

        if($appMode == ''){
            $appMode = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_PLATFORM_ACCESS_MODE);
        }

        $status = false;
        $errorMessage = '';
        $bearer = '';
        $scope = '';
        $urlRootApi = self::API_URL_ROOT_TEST;
        if ($appMode == \Scalexpert\Plugin\Model\System\Config\Source\Mode::MODE_PRODUCTION) {
            $urlRootApi = self::API_URL_ROOT_PRODUCTION;
        }

        $this->logger->info("Get bearer with root url: ".$urlRootApi);

        if(!$forceDoubleScope){
            if($this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_WARRANTY_ENABLE)) {
                $scope .= 'insurance:rw ';
            }

            if(
                $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_PAYMENT_3X_ENABLE) ||
                $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_PAYMENT_4X_ENABLE) ||
                $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_LONG_CREDIT_FR_ENABLE) ||
                $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_LONG_CREDIT_DE_ENABLE)
            ) {
                $scope .= 'e-financing:rw ';
            }
        }else {
            $scope = 'e-financing:rw insurance:rw';
        }


        if(!$scope) {
            $errorMessage = 'API Scalexpert : No scope found';
        }

        if ($appId === '' && $appKey === '') {
            switch ($appMode) {
                case \Scalexpert\Plugin\Model\System\Config\Source\Mode::MODE_TEST:
                    $appId = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_PLATFORM_ACCESS_ID_TEST);
                    $appKey = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_PLATFORM_ACCESS_KEY_TEST);
                    break;
                case \Scalexpert\Plugin\Model\System\Config\Source\Mode::MODE_PRODUCTION:
                    $appId = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_PLATFORM_ACCESS_ID_PROD);
                    $appKey = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_PLATFORM_ACCESS_KEY_PROD);
                    break;
                default:
            }
        }


        if (!is_null($appId) && !is_null($appKey)) {
            try {
                $urlApi = $urlRootApi.self::API_URL_AUTH;
                $client = $appId . ':' . $appKey;
                $clientEncoded = base64_encode($client);

                $this->curl->addHeader("Accept", "application/json");
                $this->curl->addHeader("Content-Type", "application/x-www-form-urlencoded");
                $this->curl->addHeader("Cache-control", "no-cache");
                $this->curl->addHeader("Authorization", "Basic " . $clientEncoded);

                $data = array(
                    'grant_type' => 'client_credentials',
                    'scope' => $scope
                );
                $this->curl->setTimeout(self::TIMEOUT);
                $this->curl->post($urlApi, $data);

                $this->writeLog('endPoint:: ' , $urlApi);
                $this->writeLog('status:: ' , $this->curl->getStatus());

                if ($this->curl->getStatus() === 200) {
                    $result = $this->curl->getBody();
                    $finalResult = (json_decode($result));
                    $bearer = $finalResult->access_token;
                    $status = true;
                } else {
                    switch ($this->curl->getStatus()) {
                        case 400:
                            $errorMessage = 'API Scalexpert : Bad request';
                            break;
                        case 401:
                            $errorMessage = 'API Scalexpert : Unauthorized';
                            break;
                        default:
                            $errorMessage = 'API Scalexpert : Unknow error';
                    }
                }
            } catch (\Exception $e) {
                $errorMessage = "API Scalexpert exception: " . $e->getMessage();
            }
        }

        if($errorMessage) {
            $this->writeLog('ERROR:: ' , $errorMessage);
        }

        $this->_bearer = array(
            'status' => $status,
            'bearer' => $bearer,
            'error-message' => $errorMessage,
            'api_root_url' => $urlRootApi
        );

        return $this->_bearer;
    }

    public function getFinancingEligibleSolutions($amount, $countryId, $appId ="", $appKey="",$mode="",$forceDoubleScope = false)
    {
        $bearer = $this->getBearer(self::FINANCING, $appId, $appKey,$mode,$forceDoubleScope);
        $status = false;
        $errorMessage = '';
        $result = '';

        if ($bearer['status']) {
            try {
                $endPointUrl = $bearer['api_root_url'].self::API_URL_FINANCING . 'eligible-solutions?financedAmount='.(float)$amount.'&buyerBillingCountry='.$countryId;

                $this->curl->addHeader("Accept", "application/json");
                $this->curl->addHeader("Content-Type", "application/json");
                $this->curl->addHeader("Authorization", "Bearer " . $bearer['bearer']);
                $this->curl->addHeader("Cache-control", "no-cache");
                $this->curl->setTimeout(self::TIMEOUT);
                $this->curl->get($endPointUrl);

                $this->writeLog('endPoint:: ' , $endPointUrl);
                $this->writeLog('status:: ' , $this->curl->getStatus());
                $this->writeLog('body:: ' , $this->curl->getBody());

                if ($this->curl->getStatus() === 200) {
                    $result = $this->curl->getBody();
                    $result = json_decode($result);
                    $status = true;
                } else {
                    switch ($this->curl->getStatus()) {
                        case 400:
                            $errorMessage = 'API Scalexpert : Bad request';
                            break;
                        case 403:
                            $errorMessage = 'API Scalexpert : Forbidden';
                            break;
                        default:
                            $errorMessage = 'API Scalexpert : Unknow error';
                    }
                }
            } catch (\Exception $e) {
                $errorMessage = "API Scalexpert exception: " . $e->getMessage();
            }
        } else {
            $errorMessage = $bearer['error-message'];
        }

        if($errorMessage) {
            $this->writeLog('ERROR:: ' , $errorMessage);
        }

        return array(
            'status' => $status,
            'result' => $result,
            'error-message' => $errorMessage
        );
    }

    public function getFinancingSubscriptions($pageSize = 0, $page = 1)
    {
        $bearer = $this->getBearer(self::FINANCING);
        $status = false;
        $errorMessage = '';
        $result = '';

        if ($bearer['status']) {
            if ($pageSize === 0) {
                $endPointUrl = $bearer['api_root_url'].self::API_URL_FINANCING . 'subscriptions';
            } else {
                $endPointUrl = $bearer['api_root_url'].self::API_URL_FINANCING . 'subscriptions?pageSize='.$pageSize.'&page='.$page;
            }

            $this->curl->addHeader("Accept", "application/json");
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("Authorization", "Bearer " . $bearer['bearer']);
            $this->curl->addHeader("Cache-control", "no-cache");
            $this->curl->setTimeout(self::TIMEOUT);

            $this->curl->get($endPointUrl);


            $this->writeLog('endPoint:: ' , $endPointUrl);
            $this->writeLog('status:: ' , $this->curl->getStatus());
            $this->writeLog('body:: ' , $this->curl->getBody());

            if ($this->curl->getStatus() === 200) {
                $result = $this->curl->getBody();
                $result = (json_decode($result));
                $status = true;
            } else {
                switch ($this->curl->getStatus()) {
                    case 400:
                        $errorMessage = 'API Scalexpert : Bad request';
                        break;
                    case 403:
                        $errorMessage = 'API Scalexpert : Forbidden';
                        break;
                    default:
                        $errorMessage = 'API Scalexpert : Unknow error';
                }
            }
        } else {
            $errorMessage = $bearer['error-message'];
        }

        if($errorMessage) {
            $this->writeLog('ERROR:: ' , $errorMessage);
        }

        return array(
            'status' => $status,
            'result' => $result,
            'error-message' => $errorMessage
        );
    }

    public function getFinancingSubscriptionsById($creditSubscriptionId)
    {
        $bearer = $this->getBearer(self::FINANCING);
        $status = false;
        $errorMessage = '';
        $result = '';

        if ($bearer['status']) {

            $endPointUrl = $bearer['api_root_url'].self::API_URL_FINANCING . 'subscriptions/' . $creditSubscriptionId;

            $this->curl->addHeader("Accept", "application/json");
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("Authorization", "Bearer " . $bearer['bearer']);
            $this->curl->addHeader("Cache-control", "no-cache");
            $this->curl->setTimeout(self::TIMEOUT);
            $this->curl->get($endPointUrl);

            $this->writeLog('endPoint:: ' , $endPointUrl);
            $this->writeLog('status:: ' , $this->curl->getStatus());
            $this->writeLog('body:: ' , $this->curl->getBody());

            if ($this->curl->getStatus() === 200) {
                $result = $this->curl->getBody();
                $result = (json_decode($result));
                $status = true;
            } else {
                switch ($this->curl->getStatus()) {
                    case 400:
                        $errorMessage = 'API Scalexpert : Bad request';
                        break;
                    case 403:
                        $errorMessage = 'API Scalexpert : Forbidden';
                        break;
                    default:
                        $errorMessage = 'API Scalexpert : Unknow error';
                }
            }
        } else {
            $errorMessage = $bearer['error-message'];
        }

        if($errorMessage) {
            $this->writeLog('ERROR:: ' , $errorMessage);
        }

        return array(
            'status' => $status,
            'result' => $result,
            'error-message' => $errorMessage
        );
    }

    public function getFinancingSubscriptionsByOrderId($merchantGlobalOrderId)
    {
        $bearer = $this->getBearer(self::FINANCING);
        $status = false;
        $errorMessage = '';
        $result = '';

        if ($bearer['status']) {
            try{
                $endPointUrl = $bearer['api_root_url'].self::API_URL_FINANCING . 'subscriptions?merchantGlobalOrderId=' . urlencode($merchantGlobalOrderId);

                $this->curl->addHeader("Accept", "application/json");
                $this->curl->addHeader("Content-Type", "application/json");
                $this->curl->addHeader("Authorization", "Bearer " . $bearer['bearer']);
                $this->curl->addHeader("Cache-control", "no-cache");
                $this->curl->setTimeout(self::TIMEOUT);
                $this->curl->get($endPointUrl);

                $this->writeLog('endPoint:: ' , $endPointUrl);
                $this->writeLog('status:: ' , $this->curl->getStatus());
                $this->writeLog('body:: ' , $this->curl->getBody());

                if ($this->curl->getStatus() === 200) {
                    $result = $this->curl->getBody();
                    $result = (json_decode($result));
                    $status = true;
                } else {
                    switch ($this->curl->getStatus()) {
                        case 400:
                            $errorMessage = 'API Scalexpert : Bad request';
                            break;
                        case 403:
                            $errorMessage = 'API Scalexpert : Forbidden';
                            break;
                        default:
                            $errorMessage = 'API Scalexpert : Unknow error';
                    }
                }
            } catch (\Exception $e) {
                $errorMessage = "API Scalexpert exception: " . $e->getMessage();
            }
        } else {
            $errorMessage = $bearer['error-message'];
        }

        if($errorMessage) {
            $this->writeLog('ERROR:: ' , $errorMessage);
        }

        return array(
            'status' => $status,
            'result' => $result,
            'error-message' => $errorMessage
        );
    }

    private function _getSolutionCodeFromOrder($order) {
        $quotetotal = $order->getBaseSubtotal();
        $countryId = $order->getBillingAddress()->getCountryId();
        $financing = $this->getFinancingEligibleSolutions($quotetotal, $countryId);

        /** @var \Magento\Sales\Model\Order $order */
        $method = $order->getPayment()->getMethod();
        $searchedCodes = [];
        switch ($method) {
            case \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_3X:
                $searchedCodes = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_3X;
                break;
            case \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_4X:
                $searchedCodes = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_4X;
                break;
            case \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_FR:
                $searchedCodes = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_LONG_FR;
                break;
            case \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_MAGENTO_CODE_LONG_DE:
                $searchedCodes = \Scalexpert\Plugin\Model\SystemConfigData::SCALEXPERT_PAYMENT_CODES_LONG_DE;
                break;
        }

        $solutionCode=false;
        if ($financing['status']) {
            foreach ($financing['result']->solutions as $solution) {
                if ($solutionCode) {
                    continue 1;
                }
                if (in_array($solution->solutionCode, $searchedCodes)) {
                    $solutionCode = $solution->solutionCode;
                }
            }
        }


        return $solutionCode;
    }

    public function createFinancingSubscription($order)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $solutionCode = $this->_getSolutionCodeFromOrder($order);
        $status = false;
        $errorMessage = '';
        $errorCode = '';
        $result = '';

        if ($solutionCode) {
            $bearer = $this->getBearer(self::FINANCING);

            if ($bearer['status']) {
                try {
                    $endPointUrl = $bearer['api_root_url'] . self::API_URL_FINANCING . 'subscriptions/';

                    $this->curl->addHeader("Accept", "application/json");
                    $this->curl->addHeader("Content-Type", "application/json");
                    $this->curl->addHeader("Authorization", "Bearer " . $bearer['bearer']);
                    $this->curl->addHeader("Cache-control", "no-cache");

                    $params = [
                        '_secure' => true
                    ];

                    $cartItems = array();
                    foreach ($order->getAllVisibleItems() as $item) {
                        $itemData = array(
                            "id" => strval($item->getProductId()),
                            "quantity" => floatval($item->getQtyOrdered()),
                            "model" => "NC",
                            "label" => $item->getName(),
                            "price" => floatval($item->getPriceInclTax() - $item->getBaseDiscountAmount()),
                            "currencyCode" => $order->getOrderCurrencyCode(),
                            "orderId" => (string)($order->getId()),
                            "brandName" => "NC",
                            "description" => $item->getDescription() ? trim(strip_tags($item->getDescription())) : "NC",
                            "specifications" => "NC",
                            "category" => "NC",
                            "isFinanced" => true,
                            "sku" => $item->getSku()
                        );

                        array_push($cartItems, $itemData);
                    }
                    $data = array(
                        "financedAmount" => floatval($order->getBaseGrandTotal()),
                        "solutionCode" => $solutionCode,
                        "merchantBasketId" => (string)($order->getQuoteId()),
                        "merchantGlobalOrderId" => (string)($order->getId()),
                        "merchantBuyerId" => $order->getCustomerId() ? $order->getCustomerId() : $order->getCustomerEmail(),
                        "merchantUrls" => [
                            "confirmation" => $this->_urlBuilder->getUrl('scalexpert/payment/paymentresponse', $params)
                        ],
                        "buyers" => [
                            [
                                "billingContact" => [
                                    "lastName" => $order->getBillingAddress()->getLastname(),
                                    "firstName" => $order->getBillingAddress()->getFirstname(),
                                    "commonTitle" => $order->getBillingAddress()->getPrefix() ? $order->getBillingAddress()->getPrefix() : "MR",
                                    "email" => $order->getBillingAddress()->getEmail(),
                                    "mobilePhoneNumber" => preg_replace('/^(?:\+?33|0)?/','+33', $order->getBillingAddress()->getTelephone()),
                                    "professionalTitle" => $order->getBillingAddress()->getCompany() ? $order->getBillingAddress()->getCompany() : "",
                                    "phoneNumber" => ""
                                ],
                                "billingAddress" => [
                                    "locationType" => "BILLING_ADDRESS",
                                    "streetNumberSuffix" => "",
                                    "streetName" => implode("\n", $order->getBillingAddress()->getStreet()),
                                    "streetNameComplement" => "",
                                    "zipCode" => strval($order->getBillingAddress()->getPostcode()),
                                    "cityName" => $order->getBillingAddress()->getCity(),
                                    "regionName" => "NC",
                                    "countryCode" => $order->getBillingAddress()->getCountryId()
                                ],
                                "deliveryContact" => [
                                    "lastName" => $order->getShippingAddress()->getLastname(),
                                    "firstName" => $order->getShippingAddress()->getFirstname(),
                                    "commonTitle" => $order->getShippingAddress()->getPrefix() ? $order->getShippingAddress()->getPrefix() : "MR",
                                    "email" => $order->getShippingAddress()->getEmail(),
                                    "mobilePhoneNumber" => preg_replace('/^(?:\+?33|0)?/','+33', $order->getShippingAddress()->getTelephone()),
                                    "professionalTitle" => $order->getShippingAddress()->getCompany() ? $order->getShippingAddress()->getCompany() : "",
                                    "phoneNumber" => ""
                                ],
                                "deliveryAddress" => [
                                    "locationType" => "DELIVERY_ADDRESS",
                                    "streetNumberSuffix" => "",
                                    "streetName" => implode("\n", $order->getShippingAddress()->getStreet()),
                                    "streetNameComplement" => "",
                                    "zipCode" => strval($order->getShippingAddress()->getPostcode()),
                                    "cityName" => $order->getShippingAddress()->getCity(),
                                    "regionName" => "NC",
                                    "countryCode" => $order->getShippingAddress()->getCountryId()
                                ],
                                "contact" => [
                                    "lastName" => $order->getBillingAddress()->getLastname(),
                                    "firstName" => $order->getBillingAddress()->getFirstname(),
                                    "commonTitle" => $order->getCustomerPrefix() ? $order->getCustomerPrefix() : "MR",
                                    "email" => $order->getCustomerEmail(),
                                    "mobilePhoneNumber" => preg_replace('/^(?:\+?33|0)?/','+33', $order->getBillingAddress()->getTelephone()),
                                    "professionalTitle" => "",
                                    "phoneNumber" => ""
                                ],
                                "contactAddress" => [
                                    "locationType" => "MAIN_ADDRESS",
                                    "streetNumberSuffix" => "",
                                    "streetName" => implode("\n", $order->getBillingAddress()->getStreet()),
                                    "streetNameComplement" => "",
                                    "zipCode" => strval($order->getBillingAddress()->getPostcode()),
                                    "cityName" => $order->getBillingAddress()->getCity(),
                                    "regionName" => "NC",
                                    "countryCode" => $order->getBillingAddress()->getCountryId()
                                ],
                                "deliveryMethod" => $order->getShippingDescription(),
                                "vip" => false
                            ]
                        ],
                        "basketDetails" => [
                            "basketItems" => $cartItems
                        ]
                    );

                    $this->curl->setTimeout(self::TIMEOUT);
                    $this->curl->post($endPointUrl, json_encode($data));

                    $this->writeLog('endPoint:: ', $endPointUrl);
                    $this->writeLog('data send:: ', $data);
                    $this->writeLog('json send:: ', json_encode($data));
                    $this->writeLog('status:: ', $this->curl->getStatus());
                    $this->writeLog('body:: ', $this->curl->getBody());

                    $pattern = '/"id":"([a-f0-9\-]+)".*"value":"https:\/\/[^\/]+\/nxweb\/coordonnees\/([a-f0-9\-]+)"/';
                    $patternCredit = '/"id":"([a-f0-9\-]+)".*"value":"https:\/\/[^\/]+\/integration\/merchant\/userlogin\/\?u=([a-f0-9\-]+)&p/';

                    if (preg_match($pattern, $this->curl->getBody(), $matches) || preg_match($patternCredit, $this->curl->getBody(), $matches)) {
                        $customerId = $this->customerSession->getCustomerId();
                        $id = $matches[1];
                        $coordinatesId = $matches[2];
                        $this->writeLog("Customer id: ",$customerId);
                        $this->writeLog("id ",$id);
                        $this->writeLog("coordonates ",$coordinatesId);

                        try{
                            $paymentRedirect = $this->paymentRedirectFactory->create();
                            $paymentRedirect->setApiId($id);
                            $paymentRedirect->setCoordonatesId($coordinatesId);
                            $paymentRedirect->setCustomerId($customerId);
                            $paymentRedirect->setOrderId($order->getId());
                            $paymentRedirect->save();
                        }catch(\Exception $e){
                            $this->writeLog("Exception: ",$e->getMessage());
                        }
                    }else{
                        $this->writeLog("No match",null);
                    }
                    $result = $this->curl->getBody();
                    $result = (json_decode($result));
                    if ($this->curl->getStatus() === 201
                        || $this->curl->getStatus() === 100
                    ) {
                        $status = true;
                        if($this->curl->getStatus() === 100 &&
                            isset($result->httpStatusCode) &&
                            $result->httpStatusCode == 400){
                            $status = false;
                            $errorMessage = $result->errorMessage;
                            $errorCode = $result->errorCode;
                        }
                    } else {
                        switch ($this->curl->getStatus()) {
                            case 400 :
                            case 401 :
                            case 403 :
                                $errorMessage = $result->errorMessage;
                                $errorCode = $result->errorCode;
                                break;
                            default:
                                $errorMessage = 'API Scalexpert : Unknow error';
                        }

                    }
                } catch (\Exception $e) {
                    $errorMessage = "API Scalexpert exception: " . $e->getMessage();
                }
            } else {
                $errorMessage = $bearer['error-message'];
            }
        }
        else {
            $errorMessage = 'No payment method available, please try again later';
        }

        if($errorMessage) {
            $this->writeLog('ERROR:: ' , $errorMessage);
        }

        return array(
            'status' => $status,
            'result' => $result,
            'error_code' => $errorCode,
            'error_message' => $errorMessage
        );
    }


    public function getInsuranceEligibleSolutions($countryId,$appId ="",$appKey="",$mode="",$forceDoubleScope = false)
    {
        $bearer = $this->getBearer(self::INSURANCE,$appId,$appKey,$mode,$forceDoubleScope);
        $status = false;
        $errorMessage = '';
        $result = '';

        if ($bearer['status']) {
            try {
                $endPointUrl = $bearer['api_root_url'].self::API_URL_INSURANCE . 'eligible-solutions?buyerBillingCountry='.$countryId;

                $this->curl->addHeader("Accept", "application/json");
                $this->curl->addHeader("Content-Type", "application/json");
                $this->curl->addHeader("Authorization", "Bearer " . $bearer['bearer']);
                $this->curl->addHeader("Cache-control", "no-cache");
                $this->curl->setTimeout(self::TIMEOUT);
                $this->curl->get($endPointUrl);

                $this->writeLog('endPoint:: ' , $endPointUrl);
                $this->writeLog('status:: ' , $this->curl->getStatus());
                $this->writeLog('body:: ' , $this->curl->getBody());

                if ($this->curl->getStatus() === 200) {
                    $result = $this->curl->getBody();
                    $result = json_decode($result);
                    $status = true;
                } else {
                    switch ($this->curl->getStatus()) {
                        case 400:
                            $errorMessage = 'API Scalexpert : Bad request';
                            break;
                        case 403:
                            $errorMessage = 'API Scalexpert : Forbidden';
                            break;
                        default:
                            $errorMessage = 'API Scalexpert : Unknow error';
                    }
                }
            } catch (\Exception $e) {
                $errorMessage = "API Scalexpert exception: " . $e->getMessage();
            }
        } else {
            $errorMessage = $bearer['error-message'];
        }

        if($errorMessage) {
            $this->writeLog('ERROR:: ' , $errorMessage);
        }

        return array(
            'status' => $status,
            'result' => $result,
            'error-message' => $errorMessage
        );
    }


    public function getInsuranceSubscriptions($pageSize = 0, $page = 1)
    {
        $bearer = $this->getBearer(self::INSURANCE);
        $status = false;
        $errorMessage = '';
        $result = '';

        if ($bearer['status']) {
            if ($pageSize === 0) {
                $endPointUrl = $bearer['api_root_url'].self::API_URL_INSURANCE . 'subscriptions';
            } else {
                $endPointUrl = $bearer['api_root_url'].self::API_URL_INSURANCE . 'subscriptions?pageSize='.$pageSize.'&page='.$page;
            }

            $this->curl->addHeader("Accept", "application/json");
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("Authorization", "Bearer " . $bearer['bearer']);
            $this->curl->addHeader("Cache-control", "no-cache");
            $this->curl->setTimeout(self::TIMEOUT);
            $this->curl->get($endPointUrl);

            $this->writeLog('endPoint:: ' , $endPointUrl);
            $this->writeLog('status:: ' , $this->curl->getStatus());
            $this->writeLog('body:: ' , $this->curl->getBody());

            if ($this->curl->getStatus() === 200) {
                $result = $this->curl->getBody();
                $result = (json_decode($result));
                $status = true;
            } else {
                switch ($this->curl->getStatus()) {
                    case 400:
                        $errorMessage = 'API Scalexpert : Bad request';
                        break;
                    case 403:
                        $errorMessage = 'API Scalexpert : Forbidden';
                        break;
                    default:
                        $errorMessage = 'API Scalexpert : Unknow error';
                }
            }
        } else {
            $errorMessage = $bearer['error-message'];
        }

        if($errorMessage) {
            $this->writeLog('ERROR:: ' , $errorMessage);
        }

        return array(
            'status' => $status,
            'result' => $result,
            'error-message' => $errorMessage
        );
    }

    public function getInsuranceSubscriptionsById($insuranceSubscriptionId)
    {
        $bearer = $this->getBearer(self::INSURANCE);
        $status = false;
        $errorMessage = '';
        $result = '';

        if ($bearer['status']) {
            try {
                $endPointUrl = $bearer['api_root_url'].self::API_URL_INSURANCE . 'subscriptions/' . $insuranceSubscriptionId;

                $this->curl->addHeader("Accept", "application/json");
                $this->curl->addHeader("Content-Type", "application/json");
                $this->curl->addHeader("Authorization", "Bearer " . $bearer['bearer']);
                $this->curl->addHeader("Cache-control", "no-cache");
                $this->curl->setTimeout(self::TIMEOUT);
                $this->curl->get($endPointUrl);

                $this->writeLog('endPoint:: ' , $endPointUrl);
                $this->writeLog('status:: ' , $this->curl->getStatus());
                $this->writeLog('body:: ' , $this->curl->getBody());

                if ($this->curl->getStatus() === 200) {
                    $result = $this->curl->getBody();
                    $result = (json_decode($result));
                    $status = true;
                } else {
                    switch ($this->curl->getStatus()) {
                        case 400:
                            $errorMessage = 'API Scalexpert : Bad request';
                            break;
                        case 403:
                            $errorMessage = 'API Scalexpert : Forbidden';
                            break;
                        default:
                            $errorMessage = 'API Scalexpert : Unknow error';
                    }
                }
            } catch (\Exception $e) {
                $errorMessage = "API Scalexpert exception: " . $e->getMessage();
            }
        } else {
            $errorMessage = $bearer['error-message'];
        }

        if($errorMessage) {
            $this->writeLog('ERROR:: ' , $errorMessage);
        }

        return array(
            'status' => $status,
            'result' => $result,
            'error-message' => $errorMessage
        );
    }

    public function createInsuranceItem($solutionCode, $product)
    {
        $bearer = $this->getBearer(self::INSURANCE);
        $status = false;
        $errorMessage = '';
        $result = '';

        if ($bearer['status']) {
            try {
                $endPointUrl = $bearer['api_root_url'].self::API_URL_INSURANCE . 'items';

                $this->curl->addHeader("Accept", "application/json");
                $this->curl->addHeader("Content-Type", "application/json");
                $this->curl->addHeader("Authorization", "Bearer " . $bearer['bearer']);
                $this->curl->addHeader("Cache-control", "no-cache");

                $data = array(
                    "solutionCode" => $solutionCode,
                    "sku" => $product->getSku(),
                    "merchantItemId" => $product->getId(),
                    "brand" => $product->getAttributeText('manufacturer')?$product->getAttributeText('manufacturer'):"NC",
                    "model" => $product->getScaleModel()?$product->getScaleModel():"NC",
                    "title" => $product->getName(),
                    "description" => $product->getDescription()?substr(strip_tags($product->getDescription()), 0, 255):"NC",
                    "characteristics" => $product->getScaleCharacteristics()?$product->getScaleCharacteristics():"NC",
                    "category" => $this->_helperData->getCategoryTree($product->getCategoryIds())
                );

                $this->curl->setTimeout(self::TIMEOUT);
                $this->curl->post($endPointUrl, json_encode($data));

                $this->writeLog('endPoint:: ' , $endPointUrl);
                $this->writeLog('data send:: ' , $data);
                $this->writeLog('status:: ' , $this->curl->getStatus());
                $this->writeLog('body:: ' , $this->curl->getBody());

                if ($this->curl->getStatus() === 201) {
                    $result = $this->curl->getBody();
                    $result = (json_decode($result));
                    $status = true;

                } else {
                    switch ($this->curl->getStatus()) {
                        case 401:
                            $errorMessage = 'API Scalexpert : Unauthorized';
                            break;
                        case 403:
                            $errorMessage = 'API Scalexpert : Forbidden';
                            break;
                        default:
                            $errorMessage = 'API Scalexpert : Unknow error';
                    }
                }
            } catch (\Exception $e) {
                $errorMessage = "API Scalexpert exception: " . $e->getMessage();
            }
        } else {
            $errorMessage = $bearer['error-message'];
        }

        if($errorMessage) {
            $this->writeLog('ERROR:: ' , $errorMessage);
        }

        return array(
            'status' => $status,
            'result' => $result,
            'error-message' => $errorMessage
        );
    }

    /**
     * $itemId is the id return by createInsuranceItem function
     */
    public function searchInsuranceItem($solutionCode, $productPrice, $itemId)
    {
        $bearer = $this->getBearer(self::INSURANCE);
        $status = false;
        $errorMessage = '';
        $result = '';

        if ($bearer['status']) {
            try {
                $endPointUrl = $bearer['api_root_url'].self::API_URL_INSURANCE . 'items/_search-insurances';

                $this->curl->addHeader("Accept", "application/json");
                $this->curl->addHeader("Content-Type", "application/json");
                $this->curl->addHeader("Authorization", "Bearer " . $bearer['bearer']);
                $this->curl->addHeader("Cache-control", "no-cache");

                $data = array(
                    "solutionCode" => $solutionCode,
                    "itemId" => $itemId,
                    "price" => (float)$productPrice
                );
                $this->curl->setTimeout(self::TIMEOUT);
                $this->curl->post($endPointUrl, json_encode($data));

                $this->writeLog('endPoint:: ' , $endPointUrl);
                $this->writeLog('data send:: ' , $data);
                $this->writeLog('status:: ' , $this->curl->getStatus());
                $this->writeLog('body:: ' , $this->curl->getBody());

                if ($this->curl->getStatus() === 200) {
                    $result = $this->curl->getBody();
                    $result = (json_decode($result));
                    $status = true;
                } else {
                    switch ($this->curl->getStatus()) {
                        case 204:
                            $errorMessage = 'API Scalexpert : Product not eligible';
                            break;
                        case 401:
                            $errorMessage = 'API Scalexpert : Unauthorized';
                            break;
                        case 403:
                            $errorMessage = 'API Scalexpert : Forbidden';
                            break;
                        default:
                            $errorMessage = 'API Scalexpert : Unknow error';
                    }
                }
            } catch (\Exception $e) {
                $errorMessage = "API Scalexpert exception: " . $e->getMessage();
            }
        } else {
            $errorMessage = $bearer['error-message'];
        }

        if($errorMessage) {
            $this->writeLog('ERROR:: ' , $errorMessage);
        }

        return array(
            'status' => $status,
            'result' => $result,
            'error-message' => $errorMessage
        );
    }

    public function initializeInsuranceQuotation($solutionCode, $itemId, $itemPrice, $insuranceId)
    {
        $bearer = $this->getBearer(self::INSURANCE);
        $status = false;
        $errorMessage = '';
        $result = '';

        if ($bearer['status']) {
            try {
                $endPointUrl = $bearer['api_root_url'].self::API_URL_INSURANCE . 'quotations';

                $this->curl->addHeader("Accept", "application/json");
                $this->curl->addHeader("Content-Type", "application/json");
                $this->curl->addHeader("Authorization", "Bearer " . $bearer['bearer']);
                $this->curl->addHeader("Cache-control", "no-cache");

                $data = array(
                    "solutionCode" => $solutionCode,
                    "itemId" => $itemId,
                    "itemPrice" => $itemPrice,
                    "insuranceId" => $insuranceId
                );
                $this->curl->setTimeout(self::TIMEOUT);
                $this->curl->post($endPointUrl, json_encode($data));


                $this->writeLog('endPoint:: ' , $endPointUrl);
                $this->writeLog('data send:: ' , $data);
                $this->writeLog('status:: ' , $this->curl->getStatus());
                $this->writeLog('body:: ' , $this->curl->getBody());

                if ($this->curl->getStatus() === 201) {
                    $result = $this->curl->getBody();
                    $result = (json_decode($result));
                    $status = true;
                } else {
                    switch ($this->curl->getStatus()) {
                        case 401:
                            $errorMessage = 'API Scalexpert : Unauthorized';
                            break;
                        case 403:
                            $errorMessage = 'API Scalexpert : Forbidden';
                            break;
                        default:
                            $errorMessage = 'API Scalexpert : Unknow error';
                    }
                }
            } catch (\Exception $e) {
                $errorMessage = "API Scalexpert exception: " . $e->getMessage();
            }
        } else {
            $errorMessage = $bearer['error-message'];
        }

        if($errorMessage) {
            $this->writeLog('ERROR:: ' , $errorMessage);
        }

        return array(
            'status' => $status,
            'result' => $result,
            'error-message' => $errorMessage
        );
    }

    /**
     * quoteId response de initializeInsuranceQuotation
     * $quoteExpiration response de initializeInsuranceQuotation
     * quotePrice response de initializeInsuranceQuotation
     * insuranceId response de search insurance
     * quote => magento quote
     */
    public function createInsuranceSubscription($solutionCode, $quoteId, $insuranceId, $order, $quoteExpiration, $quotePrice, $insuranceItemId, $productItem)
    {
        $bearer = $this->getBearer(self::INSURANCE);
        $status = false;
        $errorMessage = '';
        $result = '';

        if ($bearer['status']) {
            try {
                $endPointUrl = $bearer['api_root_url'].self::API_URL_INSURANCE . 'subscriptions';

                $this->curl->addHeader("Accept", "application/json");
                $this->curl->addHeader("Content-Type", "application/json");
                $this->curl->addHeader("Authorization", "Bearer " . $bearer['bearer']);
                $this->curl->addHeader("Cache-control", "no-cache");

                $data = array(
                    "solutionCode" => $solutionCode,
                    "quoteId" => strval($quoteId),
                    "insuranceId" => strval($insuranceId),
                    "merchantBasketId" => strval($order->getEntityId()),
                    "merchantBuyerId" => strval($order->getCustomerId() ? $order->getCustomerId() : $order->getCustomerEmail()),
                    "producerQuoteExpirationDate" => preg_replace('/^(\d{4}-\d{2}-\d{2}).*$/', '$1', $quoteExpiration),
                    "producerQuoteInsurancePrice" => floatval($quotePrice),
                    "buyer" => [
                        "contact" => [
                            "lastName" =>  $order->getCustomerLastname(),
                            "firstName" => $order->getCustomerFirstname(),
                            "email" => $order->getCustomerEmail(),
                            "mobilePhoneNumber" => preg_replace('/^(?:\+?33|0)?/','+33', $order->getBillingAddress()->getTelephone()),
                            "phoneNumber" => preg_replace('/^(?:\+?33|0)?/','+33', $order->getBillingAddress()->getTelephone())
                        ],
                        "address" => [
                            "streetNumber" => 0,
                            "streetNumberSuffix" => "",
                            "streetName" => implode("\n", $order->getBillingAddress()->getStreet()),
                            "streetNameComplement" => "",
                            "zipCode" => strval($order->getBillingAddress()->getPostcode()),
                            "cityName" => $order->getBillingAddress()->getCity(),
                            "regionName" => "NC",
                            "countryCode" => $order->getBillingAddress()->getCountryId()
                        ]
                    ],
                    "insuredItem" => [
                        "id" => $insuranceItemId,
                        "label" => $productItem->getName(),
                        "brandName" => $productItem->getAttributeText('manufacturer')?$productItem->getAttributeText('manufacturer'):"NC",
                        "price" => floatval($productItem->getPrice()),
                        "currencyCode" => $order->getGlobalCurrencyCode(),
                        "orderId" => $order->getEntityId(),
                        "category" => $this->_helperData->getCategoryTree($productItem->getCategoryIds()),
                        "insurancePrice" => floatval($quotePrice),
                        "sku" => $productItem->getSku()
                    ]
                );
                $this->curl->setTimeout(self::TIMEOUT);
                $this->curl->post($endPointUrl, json_encode($data, JSON_UNESCAPED_UNICODE));
                $this->writeLog('endPoint:: ' , $endPointUrl);
                $this->writeLog('data send:: ' , $data);
                $this->writeLog('status:: ' , $this->curl->getStatus());
                $this->writeLog('body:: ' , $this->curl->getBody());

                if ($this->curl->getStatus() === 201) {
                    $result = $this->curl->getBody();
                    $result = (json_decode($result));
                    $status = true;

                } else {
                    switch ($this->curl->getStatus()) {
                        case 401:
                            $errorMessage = 'API Scalexpert : Unauthorized';
                            break;
                        case 403:
                            $errorMessage = 'API Scalexpert : Forbidden';
                            break;
                        default:
                            $errorMessage = 'API Scalexpert : Unknow error';
                    }
                }
            } catch (\Exception $e) {
                $errorMessage = "API Scalexpert exception: " . $e->getMessage();
            }
        } else {
            $errorMessage = $bearer['error-message'];
        }

        if($errorMessage) {
            $this->writeLog('ERROR:: ' , $errorMessage);
        }

        return array(
            'status' => $status,
            'result' => $result,
            'error-message' => $errorMessage
        );
    }

    public function writeLog($name, $data)
    {
        $debug = $this->systemConfigData->getScalexpertConfigData(\Scalexpert\Plugin\Model\SystemConfigData::XML_SCALEXPERT_DEBUG_ENABLE);

        if ($debug) {
            $this->logger->info($name . print_r($data, true));
        }
    }

    public function cancelInsuranceSubscription($insuranceSubscriptionId, $storeName)
    {
        $bearer = $this->getBearer(self::INSURANCE);
        $status = false;
        $errorMessage = '';
        $result = '';

        if ($bearer['status']) {
            try {
                $endPointUrl = $bearer['api_root_url'] . self::API_URL_INSURANCE . 'subscriptions/' . $insuranceSubscriptionId . '/_cancel';

                $this->curl->addHeader("Accept", "application/json");
                $this->curl->addHeader("Content-Type", "application/json");
                $this->curl->addHeader("Authorization", "Bearer " . $bearer['bearer']);
                $this->curl->addHeader("Cache-control", "no-cache");

                $this->curl->setTimeout(self::TIMEOUT);

                $data = array(
                    "cancellationStatus" => "cancelled",
                    "origin" => $storeName
                );

                $this->curl->post($endPointUrl, json_encode($data, JSON_UNESCAPED_UNICODE));
                $this->writeLog('endPoint:: ', $endPointUrl);
                $this->writeLog('data send:: ', $data);
                $this->writeLog('status:: ', $this->curl->getStatus());
                $this->writeLog('body:: ', $this->curl->getBody());

                if ($this->curl->getStatus() === 204) {
                    $status = true;

                } else {
                    switch ($this->curl->getStatus()) {
                        case 400:
                            $errorMessage = 'API Scalexpert : Bad request';
                            break;
                        case 401:
                            $errorMessage = 'API Scalexpert : Unauthorized';
                            break;
                        case 403:
                            $errorMessage = 'API Scalexpert : Forbidden';
                            break;
                        default:
                            $errorMessage = 'API Scalexpert : Unknow error';
                    }
                }

            } catch (\Exception $e) {
                $errorMessage = "API Scalexpert exception: " . $e->getMessage();
            }
        } else {
            $errorMessage = $bearer['error-message'];
        }

        if($errorMessage) {
            $this->writeLog('ERROR:: ' , $errorMessage);
        }

        return array(
            'status' => $status,
            'result' => $result,
            'error-message' => $errorMessage
        );
    }

    public function cancelFinancingSubscription($creditSubscriptionId, $amount)
    {
        $bearer = $this->getBearer(self::FINANCING);
        $status = false;
        $errorMessage = '';
        $result = '';

        if ($bearer['status']) {
            try {
                $endPointUrl = $bearer['api_root_url'] . self::API_URL_FINANCING . 'subscriptions/' . $creditSubscriptionId . '/_cancel';

                $this->curl->addHeader("Accept", "application/json");
                $this->curl->addHeader("Content-Type", "application/json");
                $this->curl->addHeader("Authorization", "Bearer " . $bearer['bearer']);
                $this->curl->addHeader("Cache-control", "no-cache");

                $this->curl->setTimeout(self::TIMEOUT);

                $data = array(
                    "cancelledAmount" => $amount
                );

                $this->curl->post($endPointUrl, json_encode($data, JSON_UNESCAPED_UNICODE));
                $this->writeLog('endPoint:: ', $endPointUrl);
                $this->writeLog('data send:: ', $data);
                $this->writeLog('status:: ', $this->curl->getStatus());
                $this->writeLog('body:: ', $this->curl->getBody());

                if ($this->curl->getStatus() === 200) {
                    $resultApi = $this->curl->getBody();
                    $resultApi = (json_decode($resultApi));
                    if ($resultApi->status === 'ACCEPTED') {
                        $result = $resultApi;
                        $status = true;
                    }

                } else {
                    switch ($this->curl->getStatus()) {
                        case 401:
                            $errorMessage = 'API Scalexpert : Unauthorized';
                            break;
                        case 403:
                            $errorMessage = 'API Scalexpert : Forbidden';
                            break;
                        default:
                            $errorMessage = 'API Scalexpert : Unknow error';
                    }
                }

            } catch (\Exception $e) {
                $errorMessage = "API Scalexpert exception: " . $e->getMessage();
            }
        } else {
            $errorMessage = $bearer['error-message'];
        }

        if($errorMessage) {
            $this->writeLog('ERROR:: ' , $errorMessage);
        }

        return array(
            'status' => $status,
            'result' => $result,
            'error-message' => $errorMessage
        );
    }
}
