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


namespace Cinovic\Otplogin\Helper;

use Twilio\Rest\Client;

/**
 * Class Data
 * @package Cinovic\Otplogin\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const OTP_TYPE = 'cinovic_otplogin/general/otp_type';
    const OTP_LENGTH = 'cinovic_otplogin/general/otp_length';
    const MOBILE_NUMBER = 'cinovic_otplogin/api_configuration/mobile_number';
    const SELLER_ID = 'cinovic_otplogin/api_configuration/sender_id';
    const AUTHORIZATION_KEY = 'cinovic_otplogin/api_configuration/authorization_key';
    const EXPIRE_TIME = 'cinovic_otplogin/general/expire_time';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * Data constructor
     * @param \Magento\Framework\App\Config\ScopeConfigInterface   $scopeConfig    [description]
     * @param \Magento\Framework\App\Helper\Context                $context        [description]
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager             [description]
     * @param \Cinovic\Otplogin\Model\OtpFactory                   $otpFactory     [description]
     * @param \Magento\Customer\Model\ResourceModel\Customer\Collection $collection    [description]    
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Cinovic\Otplogin\Model\OtpFactory $otpFactory,
        \Magento\Customer\Model\ResourceModel\Customer\Collection $collection
    ) {
        $this->otpFactory = $otpFactory;
        $this->collection = $collection;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        return parent::__construct($context);
    }

     /**
     * @param  String $path
     * @return string
     */
    public function getConfigvalue($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getOtptype()
    {
        return $this->getConfigvalue(self::OTP_TYPE);
    }

    /**
     * @return string
     */
    public function getOtplength()
    {
        return $this->getConfigvalue(self::OTP_LENGTH);
    }

    /**
     * @return string
     */
    public function getSmsmobile()
    {
        return $this->getConfigvalue(self::MOBILE_NUMBER);
    }

    /**
     * @return string
     */
    public function getSellerId()
    {
        return $this->getConfigvalue(self::SELLER_ID);
    }

    /**
     * @return string
     */
    public function getAUthkey()
    {
        return $this->getConfigvalue(self::AUTHORIZATION_KEY);
    }

    /**
     * @return string
     */
    public function getExpiretime()
    {
        return $this->getConfigvalue(self::EXPIRE_TIME);
    }

    /**
     * @return string
     */
    public function getOtpcode()
    {
        $otp_type = $this->getOtptype();
        $otp_length = $this->getOtplength();

        if (empty($otp_length)) {
            $otp_length = 4;
        }
        if ($otp_type == "number") {
            $str_result = '0123456789';
            $otp_code =  substr(str_shuffle($str_result), 0, $otp_length);
        } elseif ($otp_type == "alphabets") {
            $str_result = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            $otp_code =  substr(str_shuffle($str_result), 0, $otp_length);
        } elseif ($otp_type == "alphanumeric") {
            $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            $otp_code =  substr(str_shuffle($str_result), 0, $otp_length);
        } else {
            $otp_code = mt_rand(10000, 99999);
        }
        return $otp_code;
    }

    /**
     * Send Sms
     */
    public function getSendotp($otp_code, $mobile_number)
    {
        $number = $this->getSmsmobile();
        $sid  =     $this->getSellerId();
        $token  = $this->getAUthkey();

        $twilio = new Client($sid, $token);
        $twilio->messages
            ->create(
                $mobile_number, // to
                ["from" => "+" . $number, "body" => $otp_code]
            );
    }

    /**
     * Save Otp
     */
    public function setOtpdata($otp, $mobile_number)
    {
        $question = $this->otpFactory->create();
        $question->setOtp($otp);
        $question->setCustomer($mobile_number);
        $question->setStatus('1');
        $question->save();
    }

    /**
     * Update Otp
     */
    public function setUpdateotpstatus($mobile_number)
    {
        $customerstatus = $this->otpFactory->create()->getCollection()->addFieldToFilter('customer', $mobile_number)->getData();
        if (!empty($customerstatus)) {
            foreach ($customerstatus as $data) {
                $customerstatus1 = $this->otpFactory->create()->load($data['entity_id']);
                $customerstatus1->setStatus('0');
                $customerstatus1->save();
            }
        }
    }
}
