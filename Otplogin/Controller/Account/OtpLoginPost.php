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

use Magento\Framework\Session\SessionManagerInterface;

class OtpLoginPost extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        SessionManagerInterface $session,
        \Cinovic\Otplogin\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\ResourceModel\Customer\Collection $collection
    ) {
        $this->helper = $helper;
        $this->collection = $collection;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_sessionManager = $session;
        parent::__construct($context);
    }
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (!isset($params['email'])) {
            $collection = $this->collection->addAttributeToSelect('*')
                ->addAttributeToFilter('mobile_number', $params['mobile_number'])
                ->load()->getData();
            if (empty($collection)) {
                $response = [
                    'errors' => true,
                    'message' => __("Mobile Number Not Registered")
                ];
                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData($response);
            }
        }
        // set session
        $session = $this->_sessionManager;
        $session->setUserFormData($params);

        //update status
        try {
            $this->helper->setUpdateotpstatus($params['mobile_number']);

            //otp
            $otp_code = $this->helper->getOtpcode();

            //sms
             $this->helper->getSendotp($otp_code,$params['mobile_number']);

            //save data
            $otp = base64_encode($otp_code);
            $this->helper->setOtpdata($otp,$params['mobile_number']);

            $response = [
                'errors' => false,
                'message' => __('OTP send to your Mobile Number')
            ];
        } catch (\Exception $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
