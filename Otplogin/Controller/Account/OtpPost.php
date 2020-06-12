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


namespace Cinovic\Otplogin\Controller\Account;

use \Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Cinovic\Otplogin\Model\OtpFactory;
use PHPUnit\Framework\Constraint\IsTrue;

/**
 * Class OtpPost
 * @package Cinovic\Otplogin\Controller\Account
 */
class OtpPost extends \Magento\Framework\App\Action\Action
{

    /**
     * OtpPost constructor
     * @param \Magento\Framework\App\Action\Context                $context        [description]
     * @param \Magento\Customer\Model\CustomerFactory              $customer       [description]
     * @param \Cinovic\Otplogin\Model\OtpFactory                   $otpFactory     [description]
     * @param \Magento\Customer\Model\Session                      $session        [description]
     * @param \Magento\Framework\App\Config\ScopeConfigInterface   $scopeConfig    [description]
     * @param \Magento\Framework\Controller\Result\JsonFactory     $resultJsonFactory  [description]
     * @param \Magento\Customer\Model\ResourceModel\Customer\Collection $collection    [description]    
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\CustomerFactory $customer,
        OtpFactory $otpFactory,
        \Magento\Customer\Model\Session $customersession,
        SessionManagerInterface $session,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\ResourceModel\Customer\Collection $collection
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->collection = $collection;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_customer = $customer;
        $this->otpFactory = $otpFactory;
        $this->customersession = $customersession;
        $this->_sessionManager = $session;
        return parent::__construct($context);
    }

    /**
     * @return PageFactory
     */
    public function execute()
    {

        //get session
        $sessiondata = $this->_sessionManager->getUserFormData();
        $collection = $this->collection->addAttributeToSelect('*')
            ->addAttributeToFilter('mobile_number', $sessiondata['mobile_number'])
            ->load()->getData();
        //get otp
        $otpbymobile = $this->getRequest()->getParam('otp');
        $otp = base64_encode($otpbymobile);
        $otpvalue = $this->otpFactory->create()->getCollection()->addFieldToFilter('otp', $otp)->getData();
        $status = $this->otpFactory->create()->getCollection()->addFieldToFilter('otp', $otp)->addFieldToSelect('status')->getData();

        //config expire time
        $expiredtime = $this->scopeConfig->getValue("cinovic_otplogin/general/expire_time", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        // check value is empty or not
        if (!empty($otpvalue)) {
            $created_at = (int) strtotime($otpvalue[0]['created_at']);
            $now = time();
            $now = (int) $now;
            $expire = $now -= $created_at;
            $otpstatus = $status[0]['status'];
            if ($otpstatus == 1) {
                //check expiredtime
                if ($expire <= $expiredtime) {
                    if (!empty($collection)) {
                        $customer = $this->_customer->create()->load($collection[0]['entity_id']);
                        $customerSession = $this->customersession;
                        $customerSession->setCustomerAsLoggedIn($customer);
                        $customerSession->regenerateId();
                        $response = [
                            'errors' => false,
                            'message' => __("Logged In Successfully.")
                        ];
                        $resultJson = $this->resultJsonFactory->create();
                        return $resultJson->setData($response);
                    } else {
                        $customer = $this->_customer->create();
                        $customer->setEmail($sessiondata['email']);
                        $customer->setFirstname($sessiondata['firstname']);
                        $customer->setLastname($sessiondata['lastname']);
                        $customer->setPassword($sessiondata['password']);
                        $customer->save();
                        $customerData = $customer->getDataModel();
                        $customerData->setCustomAttribute('mobile_number', $sessiondata['mobile_number']);
                        $customer->updateData($customerData);
                        $customer->save();

                        $customer = $this->_customer->create()->load($customer->getEntityId());
                        $customerSession = $this->customersession;
                        $customerSession->setCustomerAsLoggedIn($customer);
                        $customerSession->regenerateId();
                        $response = [
                            'errors' => false,
                            'message' => __("User Created Successfully.")
                        ];
                        $resultJson = $this->resultJsonFactory->create();
                        return $resultJson->setData($response);
                    }
                } else {
                    $response = [
                        'errors' => true,
                        'message' => __("OTP Expire")
                    ];
                    $resultJson = $this->resultJsonFactory->create();
                    return $resultJson->setData($response);
                }
            } else {
                $response = [
                    'errors' => true,
                    'message' => __("Invalid OTP")
                ];
                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData($response);
            }
        } else {
            $response = [
                'errors' => true,
                'message' => __("Invalid OTP")
            ];
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($response);
        }
    }
}
