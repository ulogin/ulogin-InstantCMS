<?php
/******************************************************************************/
//                                                                            //
//                             InstantCMS v1.10                               //
//                        http://www.instantcms.ru/                           //
//                                                                            //
//                   written by InstantCMS Team, 2007-2012                    //
//                produced by InstantSoft, (www.instantsoft.ru)               //
//                                                                            //
//                        LICENSED BY GNU/GPL v2                              //
//                                                                            //
/******************************************************************************/

    if(!defined('VALID_CMS')) { die('ACCESS DENIED'); }
    $inUser = cmsUser::getInstance();
	$inPage = cmsPage::getInstance();

    $mod_count['top']     = $inPage->countModules('top');
    $mod_count['sidebar'] = $inPage->countModules('sidebar');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <!-- HEAD !-->
    <?php $inPage->printHead(); ?>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<script src="http://ulogin.ru/js/ulogin.js"></script>
    <?php if($inUser->is_admin){ ?>
        <script src="/admin/js/modconfig.js" type="text/javascript"></script>
        <script src="/templates/_default_/js/nyromodal.js" type="text/javascript"></script>
        <link href="/templates/_default_/css/modconfig.css" rel="stylesheet" type="text/css" />
        <link href="/templates/_default_/css/nyromodal.css" rel="stylesheet" type="text/css" />
    <?php } ?>
    <link href="/templates/_default_/css/reset.css" rel="stylesheet" type="text/css" />
    <link href="/templates/_default_/css/text.css" rel="stylesheet" type="text/css" />
    <link href="/templates/_default_/css/960.css" rel="stylesheet" type="text/css" />
    <link href="/templates/_default_/css/styles.css" rel="stylesheet" type="text/css" />
</head>

<body>
<?php if (cmsConfig::getConfig('siteoff') && $inUser->is_admin) { ?>
<div style="margin:4px; padding:5px; border:solid 1px red; background:#FFF; position: fixed;opacity: 0.8; z-index:999"><strong style="color:red">Сайт отключен.</strong> Только администраторы видят его содержимое.</div>
<?php } ?>
    <div id="wrapper">

        <div id="header">
            <div class="container_12">
                <div class="grid_3">
                    <div id="sitename"><a href="/"></a></div>
                </div>
                <div class="grid_9">
                    <?php if (!$inUser->id){ ?>
                        <div class="mod_user_menu">
					     	 <div style="float: right; margin: 24px 0 0 10px;"><?php cmsCore::callEvent('ULOGIN_BUTTON_SMALL', array()); ?></div>
                            <span class="register"><a href="/registration">Регистрация</a></span>
                            <span class="login"><a href="/login">Вход</a></span>
                        </div>
                    <?php } else { ?>
                        <?php $inPage->printModules('header'); ?>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div id="page">

            <div class="container_12" id="topmenu">
                <div class="grid_12">
                    <?php $inPage->printModules('topmenu'); ?>
                </div>
            </div>

            <?php if ($mod_count['top']){ ?>
            <div class="clear"></div>

            <div id="topwide" class="container_12">
                <div class="grid_12" id="topmod"><?php $inPage->printModules('top'); ?></div>
            </div>
            <?php } ?>

                <div id="pathway" class="container_12">
                    <div class="grid_12"><?php $inPage->printPathway('&rarr;'); ?></div>
                </div>

            <div class="clear"></div>

            <div id="mainbody" class="container_12">
                <div id="main" class="<?php if ($mod_count['sidebar']) { ?>grid_8<?php } else { ?>grid_12<?php } ?>">
                    <?php $inPage->printModules('maintop'); ?>

                    <?php $messages = cmsCore::getSessionMessages(); ?>
                    <?php if ($messages) { ?>
                    <div class="sess_messages">
                        <?php foreach($messages as $message){ ?>
                            <?php echo $message; ?>
                        <?php } ?>
                    </div>
                    <?php } ?>

                    <?php if($inPage->page_body){ ?>
                        <div class="component">
                             <?php $inPage->printBody(); ?>
                        </div>
                    <?php } ?>
                    <?php $inPage->printModules('mainbottom'); ?>
                </div>
                <?php if ($mod_count['sidebar']) { ?>
                    <div class="grid_4" id="sidebar"><?php $inPage->printModules('sidebar'); ?></div>
                <?php } ?>
            </div>

        </div>

    </div>

    <div id="footer">
        <div class="container_12">
            <div class="grid_8">
                <div id="copyright"><?php $inPage->printSitename(); ?> &copy; <?php echo date('Y'); ?></div>
            </div>
            <div class="grid_4 foot_right">
                <a href="http://www.instantcms.ru/" title="Работает на InstantCMS" target="_blank">
                    <img src="/templates/_default_/images/b88x31.gif" border="0"/>
                </a>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function(){
            $('#topmenu .menu li').hover(
                function() {
                    $(this).find('ul:first').show();
                    $(this).find('a:first').addClass("hover");
                },
                function() {
                    $(this).find('ul:first').hide();
                    $(this).find('a:first').removeClass("hover");
                }
            );
        });
    </script>

</body>

</html>