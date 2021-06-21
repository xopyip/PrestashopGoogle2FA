<?php
require_once dirname(__FILE__)."/../../TwoFactorKeyModel.php";

use Symfony\Component\HttpClient\HttpClient;

class AdminTwoFactorConfigurationController extends ModuleAdminController
{

    const CONTROLLER_NAME = 'AdminTwoFactorConfiguration';

    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
        $this->id_lang = $this->context->language->id;
        $this->default_form_language = $this->context->language->id;

    }

    public function initContent()
    {
        $template = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . 'xip_2fa/views/templates/admin/user_setup.tpl', $this->context->smarty);

        $template->assign($this->getTemplateViewVars());

        $generated = Tools::getValue("generated");

        if ($generated) {
            $appname = Configuration::get('PS_SHOP_NAME');
            $url = "https://www.authenticatorApi.com/pair.aspx?AppName=Prestashop2FA&AppInfo=$appname&SecretCode=$generated";
            $res = HttpClient::create()->request('GET', $url);

            $template->assign([
                'generated' => $res->getContent(),
            ]);
        }

        $privateCode = TwoFactorKeyModel::getPrivateCode($this->context->employee->id);

        $template->assign([
            'isConfigured' => !!$privateCode,
            'revokeURL' => $this->context->link->getAdminLink(self::CONTROLLER_NAME, true, [], array('revoke' => 1)),
            'generateURL' => $this->context->link->getAdminLink(self::CONTROLLER_NAME, true, [], array('generate' => 1)),
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
            if($obj){
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
            Tools::redirect($this->context->link->getAdminLink(self::CONTROLLER_NAME, true, [], array('generated' => $newKey)));
            return;
        }
    }

}