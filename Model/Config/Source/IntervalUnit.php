<?php
/**
 * Copyright (c) 2024 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Model\Config\Source;

use Cardgate\Payment\Model\Subscription;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Source model listing the available subscription interval units.
 */
class IntervalUnit extends AbstractSource implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['value' => Subscription::UNIT_DAY, 'label' => __('Day(s)')],
                ['value' => Subscription::UNIT_WEEK, 'label' => __('Week(s)')],
                ['value' => Subscription::UNIT_MONTH, 'label' => __('Month(s)')],
                ['value' => Subscription::UNIT_YEAR, 'label' => __('Year(s)')],
            ];
        }
        return $this->_options;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
