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


namespace Cinovic\Otplogin\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Otp
 * @package Cinovic\Otplogin\Model
 */
class Otp extends AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Cinovic\Otplogin\Model\ResourceModel\Otp');
    }
}
