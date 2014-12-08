if ( (typeof jQuery === 'undefined') && !window.jQuery ) {
    document.write(unescape("%3Cscript type='text/javascript' src='//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js'%3E%3C/script%3E%3Cscript type='text/javascript'%3EjQuery.noConflict();%3C/script%3E"));
} else {
    if((typeof jQuery === 'undefined') && window.jQuery) {
        jQuery = window.jQuery;
    } else if((typeof jQuery !== 'undefined') && !window.jQuery) {
        window.jQuery = jQuery;
    }
}

function uloginCallback(token){
    jQuery.ajax({
        url: '/ulogin/login',
        type: 'POST',
        dataType: 'json',
        cache: false,
        data: {token: token},
        success: function (data) {
            switch (data.answerType) {
                case 'error':
                    uloginMessage(data.title, data.msg, data.answerType);
                    break;
                case 'success':
                    if (jQuery('.ulogin_accounts').length > 0){
                        adduLoginNetworkBlock(data.networks, data.title, data.msg);
                    } else {
                        location.reload();
                    }
                    break;
                case 'verify':
                    // Верификация аккаунта
                    uLogin.mergeAccounts(token);
                    uloginMessage(data.title, data.msg, data.answerType);
                    break;
                case 'merge':
                    // Синхронизация аккаунтов
                    uLogin.mergeAccounts(token, data.existIdentity);
                    uloginMessage(data.title, data.msg, data.answerType);
                    break;
            }
        }
    });
}

function uloginMessage(title, msg, answerType) {
    if (title == '' && msg == '') { return; }
    var ulogin_messages_module = jQuery('.ulogin_messages_module');
    if (ulogin_messages_module.length == 0) { return; }

    var mess = (title != '') ? title + '<br>' : '';
    mess += (msg != '') ? msg : '';

    var class_msg = 'message_';
    if (jQuery.inArray(answerType, ['error','success']) >= 0) {
        class_msg += answerType;
    } else {
        class_msg += 'info';
    }

    mess = '<div class="' + class_msg + '">' + mess + '</div>';

    var ulogin_messages = ulogin_messages_module.find('.ulogin_messages_modulebody');

    ulogin_messages.addClass('sess_messages');
    ulogin_messages.append(mess);
    ulogin_messages_module.show();
}

function uloginDeleteAccount(network){
    jQuery.ajax({
        url: '/ulogin/delete_account',
        type: 'POST',
        dataType: 'json',
        cache: false,
        data: {network: network},
        error: function (data, textStatus, errorThrown) {
            alert('Не удалось выполнить запрос');
        },
        success: function (data) {
            switch (data.answerType) {
                case 'error':
                    uloginMessage(data.title, data.msg, 'error');
                    break;
                case 'success':
                    var accounts = jQuery('.ulogin_accounts'),
                        nw = accounts.find('[data-ulogin-network='+network+']');
                    if (nw.length > 0) nw.hide();

                    if (accounts.find('.ulogin_provider:visible').length == 0) {
                        var delete_str = jQuery('.ulogin_form').find('.delete_str');
                        if (delete_str.length > 0) delete_str.hide();
                    }

                    uloginMessage(data.title, data.msg, 'success');
                    break;
            }
        }
    });
}


function adduLoginNetworkBlock(networks, title, msg) {
    var uAccounts = jQuery('.ulogin_accounts');

    console.log(networks);

    uAccounts.each(function(){
        for (var uid in networks) {
            var network = networks[uid],
                uNetwork = jQuery(this).find('[data-ulogin-network='+network+']');

            if (uNetwork.length == 0) {
                var onclick = '';
                if (jQuery(this).hasClass('can_delete')) {
                    onclick = ' onclick="uloginDeleteAccount(\'' + network + '\')"';
                }
                jQuery(this).append(
                    '<div data-ulogin-network="' + network + '" class="ulogin_provider big_provider ' + network + '_big"' + onclick + '></div>'
                );
                uloginMessage(title, msg, 'success');
            } else {
                if (uNetwork.is(':hidden')) {
                    uloginMessage(title, msg, 'success');
                }
                uNetwork.show();
            }
        }

        var uFrom = uAccounts.parent(),
            delete_str = uFrom.find('.delete_str');
        if (delete_str.length > 0) delete_str.show();

    });
}