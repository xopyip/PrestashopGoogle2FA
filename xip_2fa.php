<?php
/**
 * 2007-2021 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2021 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

use PrestaShop\PrestaShop\Core\Util\InternationalizedDomainNameConverter;
use Symfony\Component\HttpClient\HttpClient;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Xip_2fa extends Module
{
    private $IDNConverter;

    public function __construct()
    {
        $this->name = 'xip_2fa';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'XopyIP';
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('XIP 2FA');
        $this->description = $this->l('Two factor authentication');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

        $this->IDNConverter = new InternationalizedDomainNameConverter();
    }


    public function install()
    {
        include(dirname(__FILE__) . '/sql/install.php');

        return parent::install() &&
            $this->registerHook('actionAdminLoginControllerSetMedia') &&
            $this->registerHook('actionAdminLoginControllerLoginBefore') &&
            $this->installTab();
    }

    public function uninstall()
    {

        include(dirname(__FILE__) . '/sql/uninstall.php');

        return parent::uninstall() && $this->uninstallTab();
    }

    public function enable($force_all = false)
    {
        return parent::enable($force_all) &&
            $this->installTab();
    }

    public function disable($force_all = false)
    {
        return parent::disable($force_all) &&
            $this->uninstallTab();
    }

    public function installTab()
    {
        $tab = new Tab();
        $tab->class_name = 'AdminTwoFactorConfiguration';
        $tab->module = 'xip_2fa';
        $tab->name[1] = '2FA Configuration';
        $tab->id_parent = 0;
        $tab->active = 1;
        $tab->icon = 'security';
        return $tab->save();
    }

    public function uninstallTab()
    {
        $id_tab = (int)Tab::getIdFromClassName('TwoFactorConfiguration');
        $tab = new Tab($id_tab);

        if (Validate::isLoadedObject($tab)) $tab->delete();
        $id_tab = (int)Tab::getIdFromClassName('AdminTwoFactorConfiguration');
        $tab = new Tab($id_tab);

        return Validate::isLoadedObject($tab) && $tab->delete();

    }

    public function hookActionAdminLoginControllerSetMedia()
    {
        $this->context->controller->addJS($this->_path . 'views/js/login.js');
    }


    public function hookActionAdminLoginControllerLoginBefore()
    {
        $code = trim(Tools::getValue('auth_code'));

        $email = $this->IDNConverter->emailToUtf8(trim(Tools::getValue('email')));

        $employee = $this->getEmployee($email);
        if (!$employee) {
            return;
        }

        $private_code = $this->getPrivateCode($employee);

        if (!$private_code) {
            return;
        }
        if (strlen($code) !== 6) {
            $this->context->controller->errors[] = 'Wrong 2FA code!';
            $this->context->employee->logout();
            return;
        }

        $url = "https://www.authenticatorApi.com/Validate.aspx?Pin=$code&SecretCode=$private_code";
        $res = HttpClient::create()->request('GET', $url);

        if ($res->getContent() !== "True") {
            $this->context->controller->errors[] = 'Wrong 2FA code!';
            $this->context->employee->logout();
        }

    }

    private function getEmployee($email)
    {
        if (!Validate::isEmail($email)) {
            die(Tools::displayError());
        }

        $sql = new DbQuery();
        $sql->select('e.*');
        $sql->from('employee', 'e');
        $sql->where('e.`email` = \'' . pSQL($email) . '\'');
        $sql->where('e.`active` = 1');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
    }

    private function getPrivateCode(array $employee)
    {
        $sql = new DbQuery();
        $sql->select('x.private_code');
        $sql->from('xip_2fa', 'x');
        $sql->where('x.id_employee = ' . ($employee['id_employee']));

        $ret = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        if (!$ret) return false;
        return $ret['private_code'];
    }
}
