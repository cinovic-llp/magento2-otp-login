<?php

/**
 * Cinovic Technologies LLP.
 *
 * @category  Cinovic
 * @package   Cinovic_Otplogin
 * @author    Cinovic Technologies LLP
 * @copyright Copyright (c) Cinovic Technologies LLP (https://cinovic.com)
 * @license   https://store.cinovic.com/license.html
 */

namespace Cinovic\Otplogin\Block\Form;

use \Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Login
 * @package Cinovic\Whatsapp\Block\Form
 */
class Login extends \Magento\Framework\View\Element\Template
{
    const COUNTRIES_ALLOWED = 'cinovic_otplogin/generaltelephone/allow';

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Registration
     *
     * @var \Magento\Customer\Model\Registration
     */
    protected $registration;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Model\Registration $registration
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Registration $registration,
        Json $jsonHelper,
        array $data = []
    ) {
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->registration = $registration;
    }

    /**
     * Return registration
     *
     * @return \Magento\Customer\Model\Registration
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * Retrieve form posting url
     *
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->getUrl('customer/ajax/login');
    }


    /**
     * Checking customer login status
     *
     * @return bool
     */
    public function customerIsAlreadyLoggedIn()
    {
        return (bool) $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * Retrieve registering URL
     *
     * @return string
     */
    public function getCustomerRegistrationUrl()
    {
        return $this->getUrl('customer/account/create');
    }

    /**
     * @return bool|string
     */
    public function getPhoneconfig()
    {

        $config  = [
            "nationalMode" => false,
            "utilsScript"  => $this->getViewFileUrl('Cinovic_Otplogin::js/utils.js'),
            "preferredCountries" => ['in']
        ];

        $allowedCountries = $this->_scopeConfig->getValue(self::COUNTRIES_ALLOWED, ScopeInterface::SCOPE_STORE);
        $config["onlyCountries"] = explode(",", $allowedCountries);
        return $this->jsonHelper->serialize($config);
    }
}
