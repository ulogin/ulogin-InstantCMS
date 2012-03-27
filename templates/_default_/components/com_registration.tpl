{* ================================================================================ *}
{* ============================ Форма регистрации ================================= *}
{* ================================================================================ *}

<div class="con_heading">{$LANG.REGISTRATION}</div>

{if $cfg.is_on}

    {if $cfg.reg_type == 'invite' && !$correct_invite}

        <p style="margin-bottom:15px; font-size: 14px">{$LANG.INVITES_ONLY}</p>

        {if $msg}<p style="color:red;margin-bottom: 10px">{$msg}</p>{/if}

        <form id="regform" name="regform" method="post" action="/registration">
        <table cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td><strong>{$LANG.INVITE_CODE}:</strong></td>
                <td style="padding-left:15px">
                    <input type="text" name="invite_code" class="text-input" value="" style="width:270px"/>
                </td>
                <td style="padding-left:5px">
                    <input type="submit" name="show_invite" value="{$LANG.SHOW_INVITE}" />
                </td>
            </tr>
        </table>
        </form>

    {else}

        {add_js file='components/registration/js/check.js'}

        {if $msg}<p style="color:red">{$msg}</p>{/if}

        <form id="regform" name="regform" method="post" action="/registration">
            <table width="100%" border="0" align="center" cellpadding="5" cellspacing="0">
                <tr>
                    <td width="269" valign="top" class="">
                        <div><strong>{$LANG.LOGIN}:</strong></div>
                        <div><small>{$LANG.USED_FOR_AUTH}<br/>{$LANG.ONLY_LAT_SYMBOLS}</small></div>
                    </td>
                    <td valign="top" class="">
                        <input name="login" id="logininput" class="text-input" type="text" size="30" value="{$login|escape:'html'}" onchange="checkLogin()" autocomplete="off"/>
                        <span class="regstar">*</span>
                        <div id="logincheck"></div>
                    </td>
                    <td rowspan="9" valign="top">

                        <div class="lf_title">Регистрация через социальные сети</div>

                                    <p style="margin:15px 0">
                                        Если у Вас есть регистрация в других социальных сетях или аккаунт OpenID, то Вы можете войти на сайт без регистрации.
                                    </p>

                                    {php}cmsCore::callEvent('ULOGIN_BUTTON', array());{/php}

                    </td>
                </tr>
                {if $cfg.name_mode == 'nickname'}
                    <tr>
                        <td valign="top" class="" width="269">
                            <div><strong>{$LANG.NICKNAME}:</strong></div>
                            <small>{$LANG.NICKNAME_TEXT}</small>
                        </td>
                        <td valign="top" class="">
                            <input name="nickname" id="nickinput" class="text-input" type="text" size="30" value="{$nickname|escape:'html'}" />
                            <span class="regstar">*</span>
                        </td>
                    </tr>
                {else}
                    <tr>
                        <td valign="top" class="">
                            <div><strong>{$LANG.NAME}:</strong></div>
                        </td>
                        <td valign="top" class="">
                            <input name="realname1" id="realname1" class="text-input" type="text" size="30" value="{$realname1|escape:'html'}" />
                            <span class="regstar">*</span>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="">
                            <div><strong>{$LANG.SURNAME}:</strong></div>
                        </td>
                        <td valign="top" class="">
                            <input name="realname2" id="realname2" class="text-input" type="text" size="30" value="{$realname2|escape:'html'}" />
                            <span class="regstar">*</span>
                        </td>
                    </tr>
                {/if}
                <tr>
                    <td valign="top" class=""><strong>{$LANG.PASS}:</strong></td>
                    <td valign="top" class="">
                        <input name="pass" id="pass1input" class="text-input" type="password" size="30" onchange="{literal}$('#passcheck').html('');{/literal}"/>
                        <span class="regstar">*</span>
                    </td>
                </tr>
                <tr>
                    <td valign="top" class=""><strong>{$LANG.REPEAT_PASS}: </strong></td>
                    <td valign="top" class="">
                        <input name="pass2" id="pass2input" class="text-input" type="password" size="30" onchange="checkPasswords()" />
                        <span class="regstar">*</span>
                        <div id="passcheck"></div>
                    </td>
                </tr>
                <tr>
                    <td valign="top" class="">
                        <div><strong>{$LANG.EMAIL}:</strong></div>
                        <div><small>{$LANG.NOPUBLISH_TEXT}</small></div>
                    </td>
                    <td valign="top" class="">
                        <input name="email" type="text" class="text-input" size="30" value="{$email}"/>
                        <span class="regstar">*</span>
                    </td>
                </tr>
                {if $cfg.ask_icq}
                    <tr>
                        <td valign="top" class=""><strong>ICQ:</strong></td>
                        <td valign="top" class="">
                            <input name="icq" type="text" class="text-input" id="icq" value="{$icq}" size="30"/>
                        </td>
                    </tr>
                {/if}
                {if $cfg.ask_birthdate}
                    <tr>
                        <td valign="top" class="">
                            <div><strong>{$LANG.BIRTH}:</strong></div>
                            <div><small>{$LANG.NOPUBLISH_TEXT}</small></div>
                        </td>
                        <td valign="top" class="">{php}$inCore=cmsCore::getInstance(); echo $inCore->getDateForm('birthdate'){/php}</td>
                    </tr>
                {/if}
                <tr>
                    <td valign="top" class="">
                        <div><strong>{$LANG.SECUR_SPAM}: </strong></div>
                        <div><small>{$LANG.SECUR_SPAM_TEXT}</small></div>
                    </td>
                    <td valign="top" class="">
                        {php}echo cmsPage::getCaptcha();{/php}
                    </td>
                </tr>
                <tr>
                    <td valign="top" class="">&nbsp;</td>
                    <td valign="top" class="">
                        <input name="do" type="hidden" value="register" />
                        <input name="save" type="submit" id="save" value="{$LANG.REGISTRATION}" />
                    </td>
                </tr>
            </table>
        </form>

    {/if}

{else}

    <div style="margin-top:10px">{$cfg.offmsg}</div>

{/if}

