<?php
/**
 * Copyright (c) 2024 CardGate B.V.
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
     * CuroPayments recurring resource object.
     *
     * Wraps the recurring entry point:
     *   - test: https://secure-staging.curopayments.net/rest/v1/curo/recurring/
     *   - live: https://secure.curopayments.net/rest/v1/curo/recurring/
     *
     * @see https://curo.readme.io/reference/createrecurringtransaction
     */
    class Recurring extends Base
    {

        /**
         * Create a recurring transaction off of an authorizing (mandate) transaction.
         *
         * @param string $sTransactionId_ Transaction ID of the authorizing payment (used as mandate, <= 12 chars).
         * @param int $iAmount_ The amount payable in cents (no decimal separator).
         * @param string $sCurrency_ Currency in which payment should be effected (ISO-4217, <= 3 chars).
         * @param string $sReference_ Unique merchant identification for the transaction (<= 255 chars).
         * @param string $sDescription_ Transaction description presented on the bank statement (<= 128 chars).
         * @return array The decoded API result.
         * @throws \Cardgate\Payment\Model\CardgateClient\Exception
         */
        public function create($sTransactionId_, $iAmount_, $sCurrency_, $sReference_ = '', $sDescription_ = '')
        {
            if (! is_string($sTransactionId_)
                || '' === $sTransactionId_
                || strlen($sTransactionId_) > 12
            ) {
                throw new \Cardgate\Payment\Model\CardgateClient\Exception(
                    'Recurring.TransactionId.Invalid',
                    'invalid mandate transaction id: ' . $sTransactionId_
                );
            }
            if (! is_integer($iAmount_)
                || $iAmount_ <= 0
            ) {
                throw new \Cardgate\Payment\Model\CardgateClient\Exception(
                    'Recurring.Amount.Invalid',
                    'invalid recurring amount: ' . $iAmount_
                );
            }
            if (! is_string($sCurrency_)
                || strlen($sCurrency_) > 3
            ) {
                throw new \Cardgate\Payment\Model\CardgateClient\Exception(
                    'Recurring.Currency.Invalid',
                    'invalid currency: ' . $sCurrency_
                );
            }

            $aData = [
                'transaction_id' => $sTransactionId_,
                'amount'         => $iAmount_,
                'currency_id'    => $sCurrency_,
            ];
            if ('' !== (string) $sReference_) {
                $aData['reference'] = substr((string) $sReference_, 0, 255);
            }
            if ('' !== (string) $sDescription_) {
                $aData['description'] = substr((string) $sDescription_, 0, 128);
            }

            return $this->_oClient->doRequest('recurring/', $aData, 'POST');
        }
    }

}
