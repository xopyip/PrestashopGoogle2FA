<?php
/**
 * Copyright 2021 XopyIP
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS
 * OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @author    XopyIP <mateusz.baluch@wp.pl>
 * @copyright 2021 XopyIP
 * @license   https://opensource.org/licenses/MIT
 */

class TwoFactorKeyModel extends ObjectModel
{
    public $id;
    public $id_employee;
    public $private_code;

    public static $definition = [
        'table' => 'xip2fa',
        'primary' => 'id',
        'multilang' => false,
        'fields' => array(
            'id_employee' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'private_code' => [
                'type' => self::TYPE_STRING,
                'size' => 40
            ],
        )
    ];


    public static function getModelForEmployee($employeeID)
    {
        $collection = new PrestaShopCollection("TwoFactorKeyModel");
        $collection->where('id_employee', '=', $employeeID);
        return $collection->getFirst();
    }

    public static function getPrivateCode($employeeID)
    {
        $first = self::getModelForEmployee($employeeID);
        if (!$first) {
            return false;
        }
        return $first->private_code;
    }
}
