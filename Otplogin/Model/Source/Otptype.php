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

namespace Cinovic\Otplogin\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Otptype
 * @package Cinovic\Otplogin\Model\Source
 */
class Otptype implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'number', 'label' => __('Number')],
            ['value' => 'alphabets', 'label' => __('Alphabets')],
            ['value' => 'alphanumeric', 'label' => __('Alphanumeric')]
        ];
    }
}
