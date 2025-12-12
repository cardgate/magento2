<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @license     The MIT License (MIT) https://opensource.org/licenses/MIT
 * @author      CardGate B.V.
 * @copyright   CardGate B.V.
 * @link        https://www.cardgate.com
 */
namespace Cardgate\Payment\Model\CardgateClient\resources {

    /**
     * CardGate resource object.
     */
    class Subscriptions extends Base
    {

        /**
         * This method can be used to retrieve subscription details.
         * @param $sSubscriptionId_ The subscription identifier.
         * @param array $aDetails_ Array that gets filled with additional subscription details.
         * @return \Cardgate\Payment\Model\CardgateClient\Subscription
         * @throws \Cardgate\Payment\Model\CardgateClient\Exception
         * @access public
         * @api
         */
        public function get($sSubscriptionId_, &$aDetails_ = [])
        {
            if (! is_string($sSubscriptionId_)) {
                throw new \Cardgate\Payment\Model\CardgateClient\Exception('Subscription.Id.Invalid', 'invalid subscription id: ' . $sSubscriptionId_);
            }

            $sResource = "subscription/{$sSubscriptionId_}/";

            $aResult = $this->_oClient->doRequest($sResource, null, 'GET');

            if (empty($aResult['subscription'])) {
                throw new \Cardgate\Payment\Model\CardgateClient\Exception('Subscription.Details.Invalid', 'invalid subscription data returned');
            }

            if (count($aDetails_) > 0) {
                $aDetails_ = array_merge($aDetails_, $aResult['subscription']);
            }

            $oSubscription = new \Cardgate\Payment\Model\CardgateClient\Subscription(
                $this->_oClient,
                (int) $aResult['subscription']['site_id'],
                (int) $aResult['subscription']['period'],
                $aResult['subscription']['period_type'],
                (int) $aResult['subscription']['period_price']
            );
            $oSubscription->setId($aResult['subscription']['nn_id']);
            if (! empty($aResult['subscription']['description'])) {
                $oSubscription->setDescription($aResult['subscription']['description']);
            }
            if (! empty($aResult['subscription']['reference'])) {
                $oSubscription->setReference($aResult['subscription']['reference']);
            }
            if (! empty($aResult['subscription']['start_date'])) {
                $oSubscription->setStartDate($aResult['subscription']['start_date']);
            }
            if (! empty($aResult['subscription']['end_date'])) {
                $oSubscription->setEndDate($aResult['subscription']['end_date']);
            }
            // TODO: map other subscription fields? method_id can't be used in client::Method currently...
            /*
            if ( ! empty( $aResult['subscription']['code'] ) ) {
                $oSubscription->setCode( $aResult['subscription']['code'] );
            }
            if ( ! empty( $aResult['subscription']['payment_type_id'] ) ) {
                $oSubscription->setPaymentMethod( $aResult['subscription']['payment_type_id'] );
            }
            if ( ! empty( $aResult['subscription']['last_payment_date'] ) ) {
                $oSubscription->setPaymentMethod( $aResult['subscription']['last_payment_date'] );
            }
            */

            return $oSubscription;
        }

        /**
         * This method can be used to create a new subscription.
         * @param int $iSiteId_ Site id to create the subscription for.
         * @param int $iPeriod_ The period length of the subscription.
         * @param string $sPeriodType_ The period type of the subscription (e.g. day, week, month, year).
         * @param int $iPeriodAmount_ The period amount of the subscription in cents.
         * @param string $sCurrency_ Currency (ISO 4217)
         * @return \Cardgate\Payment\Model\CardgateClient\Subscription
         * @throws \Cardgate\Payment\Model\CardgateClient\Exception
         * @access public
         * @api
         */
        public function create($iSiteId_, $iPeriod_, $sPeriodType_, $iPeriodAmount_, $sCurrency_ = 'EUR')
        {
            return new \Cardgate\Payment\Model\CardgateClient\Subscription($this->_oClient, $iSiteId_, $iPeriod_, $sPeriodType_, $iPeriodAmount_, $sCurrency_);
        }
    }

}
