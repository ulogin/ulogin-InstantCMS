<div class="ulogin_form">

    {if $uloginid}
        <div id="{$id}" data-uloginid="{$uloginid}" data-ulogin="redirect_uri={$redirect};callback={$callback}"></div>
    {else}
        <div id="{$id}" data-ulogin="display=panel;fields=first_name,last_name,email;providers=vkontakte,odnoklassniki,mailru,facebook;hidden=other;redirect_uri={$redirect};callback={$callback}"></div>
    {/if}

</div>