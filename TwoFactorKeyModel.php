<?php

class TwoFactorKeyModel extends ObjectModel
{
    public $id;
    public $id_employee;
    public $private_code;

    public static $definition = [
        'table' => 'xip_2fa',
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


    public static function getModelForEmployee($employeeID){
         $collection = new PrestaShopCollection("TwoFactorKeyModel");
         $collection->where('id_employee', '=', $employeeID);
         return $collection->getFirst();

    }

    public static function getPrivateCode($employeeID){
         $first = self::getModelForEmployee($employeeID);
         if(!$first){
             return false;
         }
        return $first->private_code;
    }
}