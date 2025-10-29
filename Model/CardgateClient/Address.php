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
namespace Cardgate\Payment\Model\CardgateClient {

	/**
	 * Address instance.
	 *
	 * @method Address setFirstName( string $sFirstName_ ) Sets the first name.
	 * @method string getFirstName() Returns the first name.
	 * @method bool hasFirstName() Checks for existence of first name.
	 * @method Address unsetFirstName() Unsets the first name.
	 *
	 * @method Address setInitials( string $sInitials_ ) Sets the initials.
	 * @method string getInitials() Returns the initials.
	 * @method bool hasInitials() Checks for the existence of initials.
	 * @method Address unsetInitials() Unsets the initials.
	 *
	 * @method Address setLastName( string $sLastName_ ) Sets the last name.
	 * @method string getLastName() Returns the last name.
	 * @method bool hasLastName() Checks for the existence of last name.
	 * @method Address unsetLastName() Unsets the last name.
	 *
	 * @method string getGender() Returns the gender.
	 * @method bool hasGender() Checks for the existence of gender.
	 * @method Address unsetGender() Unsets the gender.
	 *
	 * @method string getDayOfBirth() Returns the day of birth.
	 * @method bool hasDayOfBirth() Checks for existence of day of birth.
	 * @method Address unsetDayOfBirth() Unsets the day of birth.
	 *
	 * @method Address setCompany( string $sCompany_ ) Sets the company.
	 * @method string getCompany() Returns the company.
	 * @method bool hasCompany() Checks for the existence of company.
	 * @method Address unsetCompany() Unsets the company.
	 *
	 * @method Address setAddress( string $sAddress_ ) Sets the address.
	 * @method string getAddress() Returns the address.
	 * @method bool hasAddress() Checks for the existence of address.
	 * @method Address unsetAddress() Unsets the address.
	 *
	 * @method Address setCity( string $sCity_ ) Sets the city.
	 * @method string getCity() Returns the city.
	 * @method bool hasCity() Checks for the existence of city.
	 * @method Address unsetCity() Unsets the city.
	 *
	 * @method Address setState( string $sState_ ) Sets the state.
	 * @method string getState() Returns the state.
	 * @method bool hasState() Checks for the existence of state.
	 * @method Address unsetState() Unsets the state.
	 *
	 * @method Address setZipCode( string $sZipCode_ ) Sets the zipcode.
	 * @method string getZipCode() Returns the zipcode.
	 * @method bool hasZipCode() Checks for existence of zipcode.
	 * @method Address unsetZipCode() Unsets the zipcode.
	 *
	 * @method string getCountry() Returns the country.
	 * @method bool hasCountry() Checks for existence of country.
	 * @method Address unsetCountry() Unsets the country.
	 */
	final class Address extends Entity {

		/**
		 * @ignore
		 * @internal The methods these fields expose are configured in the class phpdoc.
		 */
		static $_aFields = [
			'FirstName'  => 'firstname',
			'Initials'   => 'initials',
			'LastName'   => 'lastname',
			'Gender'     => 'gender',
			'DayOfBirth' => 'dob',
			'Company'    => 'company',
			'Address'    => 'address',
			'City'       => 'city',
			'State'      => 'state',
			'ZipCode'    => 'zipcode',
			'Country'    => 'country_id'
		];

		/**
		 * Sets the gender.
		 * @param string $sGender_ The gender to set.
		 * @return Address Returns this, makes the call chainable.
		 * @throws Exception
		 * @access public
		 * @api
		 */
		function setGender( $sGender_ ) {
			if (
				! is_string( $sGender_ )
				|| strlen( $sGender_ ) != 1
			) {
				throw new Exception( 'Address.Gender.Invalid', 'invalid gender: ' . $sGender_ );
			}
			return parent::setGender( $sGender_ );
		}

		/**
		 * Sets the day of birth.
		 * @param string $sDayOfBirth_ The day of birth to set.
		 * @return Address Returns this, makes the call chainable.
		 * @throws Exception
		 * @access public
		 * @api
		 */
		function setDayOfBirth( $sDayOfBirth_ ) {
			if (
				! is_string( $sDayOfBirth_ )
				|| FALSE == ( $iDayOfBirthUnix = strtotime( $sDayOfBirth_ ) )
			) {
				throw new Exception( 'Address.DayOfBirth.Invalid', 'invalid day of birth: ' . $sDayOfBirth_ );
			}
			return parent::setDayOfBirth( strftime( '%m/%d/%Y', $iDayOfBirthUnix ) );
		}

		/**
		 * Sets the country.
		 * @param string $sCountry_ The country to set (ISO 3166-1 alpha-2).
		 * @return Address Returns this, makes the call chainable.
		 * @throws Exception
		 * @access public
		 * @api
		 */
		function setCountry( $sCountry_ ) {
			if (
				! is_string( $sCountry_ )
				|| strlen( $sCountry_ ) != 2
			) {
				throw new Exception( 'Address.Country.Invalid', 'invalid country: ' . $sCountry_ );
			}
			return parent::setCountry( $sCountry_ );
		}

	}

}
