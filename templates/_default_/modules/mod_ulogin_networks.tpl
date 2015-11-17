<div class="ulogin_form">

    {if $add_str}
        <div class="add_str">{$add_str}</div>
    {/if}

    {if $uloginid}
        <div id="{$id}" data-uloginid="{$uloginid}" data-ulogin="redirect_uri={$redirect};callback={$callback}"></div>
    {else}
        <div id="{$id}" data-ulogin="display=panel;fields=first_name,last_name,email,photo,photo_big;providers=vkontakte,odnoklassniki,mailru,facebook;hidden=other;redirect_uri={$redirect};callback={$callback}"></div>
    {/if}

    {if $delete_str}
        <div class="delete_str"{$hide_delete_str}>{$delete_str}</div>
    {/if}

    {assign var="ulogin_accounts" value=""}

    <div class="ulogin_accounts can_delete">
        {foreach item=network from=$networks}
            <div data-ulogin-network='{$network}'
                 class="ulogin_provider big_provider {$network}_big"
                 onclick="uloginDeleteAccount('{$network}')"></div>
        {/foreach}
    </div><div style="clear:both"></div>

</div>