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

require_once dirname(__FILE__) . "/../../TwoFactorKeyModel.php";

class AdminTwoFactorConfigurationController extends ModuleAdminController
{
    public const CONTROLLER_NAME = 'AdminTwoFactorConfiguration';

    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
        $this->id_lang = $this->context->language->id;
        $this->default_form_language = $this->context->language->id;
    }

    public function initContent()
    {
        $template = $this->context->smarty->createTemplate(
            _PS_MODULE_DIR_ . 'xip2fa/views/templates/admin/user_setup.tpl',
            $this->context->smarty
        );

        $template->assign($this->getTemplateViewVars());

        $generated = Tools::getValue("generated");

        if ($generated) {
            $appname = Configuration::get('PS_SHOP_NAME');
            $url = "https://www.authenticatorApi.com/pair.aspx" .
                "?AppName=Prestashop2FA" .
                "&AppInfo=$appname" .
                "&SecretCode=$generated";
            $template->assign([
                'generated' => $url,
            ]);
        }

        $privateCode = TwoFactorKeyModel::getPrivateCode($this->context->employee->id);

        $template->assign([
            'isConfigured' => !!$privateCode,
            'revokeURL' => $this->context->link->getAdminLink(
                self::CONTROLLER_NAME,
                true,
                [],
                array('revoke' => 1)
            ),
            'generateURL' => $this->context->link->getAdminLink(
                self::CONTROLLER_NAME,
                true,
                [],
                array('generate' => 1)
            ),
        ]);

        $this->context->smarty->assign([
            'content' => $template->fetch(),
        ]);
    }

    public function postProcess()
    {
        $employeeId = $this->context->employee->id;
        if (Tools::getValue('revoke')) {
            $obj = TwoFactorKeyModel::getModelForEmployee($employeeId);
            if ($obj) {
                $obj->delete();
            }
            return;
        }

        if (Tools::getValue('generate')) {
            $newKey = sha1(microtime(true) . mt_rand(10000, 90000));
            $obj = new TwoFactorKeyModel();
            $obj->id_employee = $employeeId;
            $obj->private_code = $newKey;
            $obj->add();
            Tools::redirect(
                $this->context->link->getAdminLink(
                    self::CONTROLLER_NAME,
                    true,
                    [],
                    array('generated' => $newKey)
                )
            );
            return;
        }
    }
}
