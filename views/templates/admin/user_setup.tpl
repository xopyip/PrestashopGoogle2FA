{*
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
*}

<div class="bootstrap">


    <div class="panel panel-default col-lg-12 col-xs-12">
        <div class="panel-heading">
            {l s='Two factor authentication configuration' d='Modules.Xip2fa.User_setup' mod='xip2fa'}
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            {if isset($generated)}
                <h1>
                    {l s='Please scan this qr code in Google Authenticator App: ' d='Modules.Xip2fa.User_setup' mod='xip2fa'}
                </h1>
                <iframe src="{$generated|escape:'htmlall':'UTF-8'}" style="width: 350px;height: 350px;border: 0;"></iframe>
            {else}
                {if $isConfigured}
                    <h1>
                        {l s='You already have 2FA authentication configured!' d='Modules.Xip2fa.User_setup' mod='xip2fa'}
                    </h1>
                    <a href="{$revokeURL|escape:'htmlall':'UTF-8'}" class="btn btn-warning">
                        {l s='Revoke!' d='Modules.xip2fa.Admin' mod='xip2fa'}
                    </a>
                {else}
                    <h1>
                        {l s='Click the button below to configure two-factor authentication' d='Modules.Xip2fa.User_setup' mod='xip2fa'}
                    </h1>
                    <a href="{$generateURL|escape:'htmlall':'UTF-8'}" class="btn btn-success">
                        {l s='Generate 2FA code' d='Modules.Xip2fa.User_setup' mod='xip2fa'}
                    </a>
                {/if}
            {/if}

        </div>

    </div>

</div>