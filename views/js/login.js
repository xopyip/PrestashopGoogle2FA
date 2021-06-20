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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/
$(document).ready(() => {
    $("form#login_form > .form-group:nth-of-type(2)").after("<div class=\"form-group\">" +
        "<label class=\"control-label\" for=\"passwd\">" +
        "2FA code" +
        "</label>" +
        "<input name=\"auth_code\" type=\"password\" id=\"auth_code\" class=\"form-control\" value=\"\" tabindex=\"3\" placeholder=\" 2FA code\">" +
        "</div>");

    //https://stackoverflow.com/questions/995183/how-to-allow-only-numeric-0-9-in-html-inputbox-using-jquery/995193#995193
    $("#auth_code").on("input keydown keyup mousedown mouseup select contextmenu drop", function() {
        if (/^\d*$/.test(this.value)) {
            this.oldValue = this.value;
            this.oldSelectionStart = this.selectionStart;
            this.oldSelectionEnd = this.selectionEnd;
        } else if (this.hasOwnProperty("oldValue")) {
            this.value = this.oldValue;
            this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
        } else {
            this.value = "";
        }
    });

    //todo: try to do it in more generic way, because this implementation will break when prestashop change login form
    window.doAjaxLogin = function(redirect) {
        $('#error').hide();
        $('#login_form').fadeIn('slow', function() {
            $.ajax({
                type: "POST",
                headers: { "cache-control": "no-cache" },
                url: "index.php" + '?rand=' + new Date().getTime(),
                async: true,
                dataType: "json",
                data: {
                    ajax: "1",
                    token: "",
                    controller: "AdminLogin",
                    submitLogin: "1",
                    passwd: $('#passwd').val(),
                    email: $('#email').val(),
                    redirect: redirect,
                    stay_logged_in: $('#stay_logged_in:checked').val(),
                    auth_code: $('#auth_code').val()
                },
                beforeSend: function() {
                    feedbackSubmit();
                    l.start();
                },
                success: function(jsonData) {
                    if (jsonData.hasErrors) {
                        displayErrors(jsonData.errors);
                        l.stop();
                    } else {
                        window.location.assign(jsonData.redirect);
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    l.stop();
                    $('#error').html('<h3>TECHNICAL ERROR:</h3><p>Details: Error thrown: ' + XMLHttpRequest + '</p><p>Text status: ' + textStatus + '</p>').removeClass('hide');
                    $('#login_form').fadeOut('slow');
                }
            });
        });
    }
})

