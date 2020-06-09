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


class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const OTP_TYPE = 'cinovic_otplogin/general/otp_type';
    const OTP_LENGTH = 'cinovic_otplogin/general/otp_length';
    const MOBILE_NUMBER = 'cinovic_otplogin/api_configuration/mobile_number';
    const SELLER_ID = 'cinovic_otplogin/api_configuration/sender_id';
    const AUTHORIZATION_KEY = 'cinovic_otplogin/api_configuration/authorization_key';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
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

    public function getConfigvalue($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getOtptype()
    {
        return $this->getConfigvalue(self::OTP_TYPE);
    }

    public function getOtplenght()
    {
        return $this->getConfigvalue(self::OTP_LENGTH);
    }

    public function getSmsmobile()
    {
        return $this->getConfigvalue(self::MOBILE_NUMBER);
    }

    public function getSellerId()
    {
        return $this->getConfigvalue(self::SELLER_ID);
    }

    public function getAUthkey()
    {
        return $this->getConfigvalue(self::AUTHORIZATION_KEY);
    }

    public function getOtpcode()
    {
        $otp_type = $this->getOtptype();
        $otp_length = $this->getOtplenght();

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

    public function setOtpdata($otp, $mobile_number)
    {
        $question = $this->otpFactory->create();
        $question->setOtp($otp);
        $question->setCustomer($mobile_number);
        $question->setStatus('1');
        $question->save();
    }

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
