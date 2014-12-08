<?php
if(!defined('VALID_CMS_ADMIN')) { die('ACCESS DENIED'); }

//------------------------------------------------------------------//
$inCore = cmsCore::getInstance();

$cfg = $inCore->loadComponentConfig('ulogin');

$id  = cmsCore::request('id', 'int');

$opt = cmsCore::request('opt', 'str');

if ($opt == 'saveconfig') {
	$cfg = array();
	$cfg['uloginid'] = cmsCore::request( 'uloginid', 'str', '' );
	$cfg['group_id'] = cmsCore::request( 'group_id', 'int', '' );

	$inCore->saveComponentConfig( 'ulogin', $cfg );

	cmsCore::addSessionMessage($_LANG['AD_CONFIG_SAVE_SUCCESS'], 'success');

	cmsCore::redirect('?view=components&do=config&id='.$id.'&opt=config');
}

$toolmenu[] = array(
	'icon'=>'save.gif',
	'title'=>$_LANG['SAVE'],
	'link'=>'javascript:document.optform.submit();'
);
$toolmenu[] = array(
	'icon'=>'cancel.gif',
	'title'=>$_LANG['CANCEL'],
	'link'=>'?view=components'
);

cpToolMenu($toolmenu);

$model = new cms_model_ulogin();

$groups = $model->getGroups();

$group_id = !empty($cfg['group_id']) ? $cfg['group_id'] : $model->getDefaultGroupId();

?>

<div class="proptable" style="padding: 5px;">
	<p><a href="http://ulogin.ru" target="_blank">uLogin</a> — это инструмент, который позволяет пользователям получить единый доступ к различным Интернет-сервисам без необходимости повторной регистрации,
		а владельцам сайтов — получить дополнительный приток клиентов из социальных сетей и популярных порталов (Google, Яндекс, Mail.ru, ВКонтакте, Facebook и др.)</p>

	<p>Чтобы создать свой виджет для входа на сайт достаточно зайти в Личный Кабинет (ЛК) на сайте <a href="http://ulogin.ru/lk.php" target="_blank">uLogin</a>,
		добавить свой сайт к списку "Мои сайты" и на вкладке "Виджеты" добавить новый виджет. Вы можете редактировать свой виджет самостоятельно.</p>

	<p><b style="color: red;">Важно! </b>Для успешной работы плагина необходимо включить в обязательных полях профиля поле <b>Еmail</b> в Личном кабинете uLogin.</p><br/>

	<p>Здесь Вы можете указать значение параметра "<b>uLogin ID</b>" Ваших виджетов, а также указать группу для новых, регистрирующихся через uLogin, пользователей.</p>
</div>

<form action="index.php?view=components&amp;do=config&amp;id=<?php echo $id;?>" method="post" name="optform">
	<input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
	<table width="100%" border="0" cellpadding="10" cellspacing="0" class="proptable">
		<tr>
			<td width="400">Значение поля <b>uLogin ID</b>
				<span class="hinttext"></span></td>
			<td valign="top"><input id="uloginid" name="uloginid" type="text" value="<?php echo $cfg['uloginid'];?>"/></td>
		</tr>
		<tr>
			<td>Группа для новых пользователей<br />
				<span class="hinttext"></span></td>
			<td valign="top">
				<select id="group_id" name="group_id">
					<?php
					foreach ($groups as $group) {
						$s = '';
						if ($group->id == $group_id) { $s = 'selected'; }
						echo "<option value='{$group->id}' {$s}>{$group->title}</option>";
					}
					?>
				</select>
			</td>
		</tr>
	</table>
	<p>
		<input name="opt" type="hidden" value="saveconfig" />
		<input name="save" type="submit" id="save" value="<?php echo $_LANG['SAVE']; ?>" />
		<input name="back" type="button" id="back" value="<?php echo $_LANG['CANCEL']; ?>" onclick="window.location.href='index.php?view=components';"/>
	</p>
</form>
