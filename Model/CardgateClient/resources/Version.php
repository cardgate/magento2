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
     * Version instance.
     *
     * @method Version setPlatformName( \string $sName_ ) Sets the platform name.
     * @method string getPlatformName() Returns the platform name.
     * @method bool hasPlatformName() Checks for existence of platform name.
     * @method Version unsetPlatformName() Unsets the platform name.
     *
     * @method Version setPlatformVersion( \string $sVersion_ ) Sets the platform version.
     * @method string getPlatformVersion() Returns the platform version.
     * @method bool hasPlatformVersion() Checks for existence of platform version.
     * @method Version unsetPlatformVersion() Unsets the platform version.
     *
     * @method Version setPluginName( \string $sName_ ) Sets the plugin name.
     * @method string getPluginName() Returns the plugin name.
     * @method bool hasPluginName() Checks for existence of plugin name.
     * @method Version unsetPluginName() Unsets the plugin name.
     *
     * @method Version setPluginVersion( \string $sVersion_ ) Sets the plugin version.
     * @method string getPluginVersion() Returns the plugin version.
     * @method bool hasPluginVersion() Checks for existence of plugin version.
     * @method Version unsetPluginVersion() Unsets the plugin version.
     */
    class Version extends \Cardgate\Payment\Model\CardgateClient\Entity
    {

        /**
         * @ignore
         * @internal The methods these fields expose are configured in the class phpdoc.
         */
        static $_aFields = [
            'PlatformName'        => 'platform_name',
            'PlatformVersion'    => 'platform_version',
            'PluginName'        => 'plugin_name',
            'PluginVersion'        => 'plugin_version',
        ];
    }

}
