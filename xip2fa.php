<?php
/**
 * Copyright 2021 XopyIP
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
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

require_once dirname(__FILE__) . "/TwoFactorKeyModel.php";

use PrestaShop\PrestaShop\Core\Util\InternationalizedDomainNameConverter;
use Symfony\Component\HttpClient\HttpClient;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Xip2Fa extends Module
{
    private $IDNConverter;

    public function __construct()
    {
        $this->name = 'xip2fa';
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
        $tab->module = 'xip2fa';
        $tab->name[1] = '2FA Configuration';
        $tab->id_parent = 0;
        $tab->active = 1;
        $tab->icon = 'security';
        return $tab->save();
    }

    public function uninstallTab()
    {
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

        $employee = (new Employee())->getByEmail($email);
        if (!$employee) {
            return;
        }

        $private_code = TwoFactorKeyModel::getPrivateCode($employee->id);

        if (!$private_code) {
            return;
        }

        if (Tools::strlen($code) !== 6) {
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
}
