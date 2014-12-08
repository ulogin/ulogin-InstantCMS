{foreach item=message from=$messages}
    <div class='message_{$message.class}'>{$message.msg}</div>
{/foreach}