<?php


use Symfony\Component\HttpClient\HttpClient;

class AdminTwoFactorConfigurationController extends ModuleAdminController
{
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

        $privateCode = $this->getPrivateCode($this->context->employee->id);

        $generated = Tools::getValue("generated");


        if ($generated) {

            $appname = Configuration::get('PS_SHOP_NAME');
            $url = "https://www.authenticatorApi.com/pair.aspx?AppName=Prestashop2FA&AppInfo=$appname&SecretCode=$generated";
            $res = HttpClient::create()->request('GET', $url);

            $template->assign([
                'generated' => $res->getContent(),
            ]);
        }

        $template->assign([
            'isConfigured' => !!$privateCode,
            'revokeURL' => $this->context->link->getAdminLink('AdminTwoFactorConfiguration', true, [], array('revoke' => 1)),
            'generateURL' => $this->context->link->getAdminLink('AdminTwoFactorConfiguration', true, [], array('generate' => 1)),
        ]);

        $this->context->smarty->assign([
            'content' => $template->fetch(),
        ]);

    }

    public function postProcess()
    {
        $employeeId = $this->context->employee->id;
        if (Tools::getValue('revoke')) {
            $sql = new DbQuery();
            $sql->type('DELETE');
            $sql->from('xip_2fa');
            $sql->where('id_employee = ' . $employeeId);
            Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);
            return;
        }

        if (Tools::getValue('generate')) {
            $newKey = sha1(microtime(true) . mt_rand(10000, 90000));
            Db::getInstance(_PS_USE_SQL_SLAVE_)->execute('INSERT INTO `' . _DB_PREFIX_ . 'xip_2fa`(id_employee, private_code) VALUES(' . $employeeId . ', "' . $newKey . '")');
            Tools::redirect($this->context->link->getAdminLink('AdminTwoFactorConfiguration', true, [], array('generated' => $newKey)));
            return;
        }
    }


    private function getPrivateCode(int $employeeId)
    {
        $sql = new DbQuery();
        $sql->select('x.private_code');
        $sql->from('xip_2fa', 'x');
        $sql->where('x.id_employee = ' . $employeeId);

        $ret = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        if (!$ret) return false;
        return $ret['private_code'];
    }
}