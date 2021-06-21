<div class="bootstrap">


    <div class="panel panel-default col-lg-12 col-xs-12">
        <div class="panel-heading">
            {l s='Two factor authentication configuration' d='Modules.Xip_2fa.Admin'}
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            {if isset($generated)}
                <h1>
                    {l s='Please scan this qr code in Google Authenticator App: ' d='Modules.Xip_2fa.Admin'}
                </h1>
                {$generated}
            {else}
                {if $isConfigured}
                    <h1>
                        {l s='You already have 2FA authentication configured!' d='Modules.Xip_2fa.Admin'}
                    </h1>
                    <a href="{$revokeURL}" class="btn btn-warning">
                        {l s='Revoke!' d='Modules.Xip_2fa.Admin'}
                    </a>
                {else}
                    <h1>
                        {l s='Click the button below to configure two-factor authentication' d='Modules.Xip_2fa.Admin'}
                    </h1>
                    <a href="{$generateURL}" class="btn btn-success">
                        {l s='Generate 2FA code' d='Modules.Xip_2fa.Admin'}
                    </a>
                {/if}
            {/if}

        </div>

    </div>

</div>