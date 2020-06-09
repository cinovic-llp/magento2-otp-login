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

class ResendOtp extends \Magento\Framework\App\Action\Action
{

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        SessionManagerInterface $session,
        \Cinovic\Otplogin\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_sessionManager = $session;
        parent::__construct($context);
    }
    public function execute()
    {
        //get session
        $sessiondata = $this->_sessionManager->getUserFormData();

        try {
            //update status
            $this->helper->setUpdateotpstatus($sessiondata['mobile_number']);

            //otp
            $otp_code = $this->helper->getOtpcode();

            //sms
            $this->helper->getSendotp($otp_code, $sessiondata['mobile_number']);

            //save data
            $otp = base64_encode($otp_code);
            $this->helper->setOtpdata($otp, $sessiondata['mobile_number']);

            $response = [
                'errors' => false,
                'message' => __('OTP Resend Successfully.')
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
