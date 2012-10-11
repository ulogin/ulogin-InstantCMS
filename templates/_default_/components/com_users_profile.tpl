{* ================================================================================ *}
{* ========================= œÓÙËÎ¸ ÔÓÎ¸ÁÓ‚‡ÚÂÎˇ ================================= *}
{* ================================================================================ *}

{add_js file='includes/jquery/tabs/jquery.ui.min.js'}
{add_js file="components/users/js/profile.js"}
{add_css file='includes/jquery/tabs/tabs.css'}					

{literal}
	<script type="text/javascript">
		$(document).ready(function(){
			$("#profiletabs > ul#tabs").tabs();
		});
	</script>
{/literal}

<div id="usertitle">

    <div id="user_ratings">
        <div class="karma" title="{$LANG.KARMA}" id="u_karma">
            {if $usr.karma_int >= 0}
                <div class="value-positive">{$usr.karma}</div>
            {else}
                <div class="value-negative">{$usr.karma}</div>
            {/if}
        </div>
        <div class="rating" title="{$LANG.RATING}">
            <div class="value">{$usr.user_rating}</div>
        </div>
    </div>

    <div class="user_group_name">
        <div class="{$usr.group_alias}">{$usr.grp}</div>
    </div>

    <div class="con_heading" id="nickname">
        {$usr.nickname}
    </div>
    {* {if $cfg.showgroup}<div class="usr_group" style="float:right">{$usr.grp}</div>{/if} *}
    {* {if $usr.banned}<div style="color:red;padding:10px;">{$LANG.USER_IN_BANLIST}</div>{/if} *}
</div>

<div class="usr_status_bar">
    <div class="usr_status_text" {if !$usr.status_text}style="display:none"{/if}>
        <span>{$usr.status_text}</span>
        <span class="usr_status_date" >// {$usr.status_date} {$LANG.BACK}</span>
    </div>
    {if $myprofile || $is_admin}
        <div class="usr_status_link"><a href="javascript:" onclick="setStatus({$usr.id})">{$LANG.CHANGE_STATUS}</a></div>
    {/if}
</div>

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top:14px">
	<tr>
		<td width="200" valign="top">
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td align="center" valign="middle">
                        <div class="usr_avatar">
                            {$usr.avatar}
                        </div>

                        {if $usr.is_new_friends}
							<div class="usr_friends_query">{$usr.new_friends}</div>
				  		{/if}

						{if $is_auth}
{* ===============================ÏÂÌ˛ ‚ ÔÓÙËÎÂ================================================= *}
							<div id="usermenu" style="">
                            <div class="usr_profile_menu">
							<table cellpadding="0" cellspacing="6" ><tr>

							{if !$myprofile}
                                <tr>
                                    <td><img src="/templates/_default_/images/icons/profile/message.png" border="0"/></td>
                                    <td><a href="/users/{$usr.id}/sendmessage.html" title="{$LANG.WRITE_MESS}">{$LANG.WRITE_MESS}</a></td>
                                </tr>
							{/if}

                            {if !$myprofile}
                            	{if !$usr.isfriend}
                                    <tr>
                                        {if !$usr.isfriend_not_add}
                                        <td><img src="/templates/_default_/images/icons/profile/friends.png" border="0"/></td>
                                        <td><a href="/users/{$usr.id}/friendship.html" title="{$LANG.ADD_TO_FRIEND}">{$LANG.ADD_TO_FRIEND}</a></td>
                                        {else}
                                        <td><img src="/templates/_default_/images/icons/profile/nofriends.png" border="0"/></td>
                                        <td><a href="/users/{$usr.id}/nofriends.html" title="{$LANG.STOP_FRIENDLY}">{$LANG.STOP_FRIENDLY}</a></td>
                                        {/if}
                                    </tr>
                                {else}
                                <tr>
                                    <td><img src="/templates/_default_/images/icons/profile/nofriends.png" border="0"/></td>
                                    <td><a href="/users/{$usr.id}/nofriends.html" title="{$LANG.STOP_FRIENDLY}">{$LANG.STOP_FRIENDLY}</a></td>
                                </tr>
                                {/if}
                            {/if}
                         	{if $myprofile}
                            	{if $cfg.sw_msg}
                                <tr>
                                    <td><img src="/templates/_default_/images/icons/profile/message.png" border="0"/></td>
                                    <td><a href="/users/{$usr.id}/messages.html" title="{$LANG.MY_MESS}">{$LANG.MY_MESS}</a></td>
                                </tr>
                                {/if}
                                {if $usr.can_add_foto}
                                <tr>
                                    <td><img src="/templates/_default_/images/icons/profile/addphoto.png" border="0"/></td>
                                    <td><a href="/users/{$usr.id}/addphoto.html" title="{$LANG.ADD_PHOTO}">{$LANG.ADD_PHOTO}</a></td>
                                </tr>
                                {/if}
                                <tr>
                                    <td><img src="/templates/_default_/images/icons/profile/avatar.png" border="0"/></td>
                                    <td><a href="/users/{$usr.id}/avatar.html" title="{$LANG.SET_AVATAR}">{$LANG.SET_AVATAR}</a></td>
                                </tr>
								{if $usr.invites_count}
                                <tr>
                                    <td><img src="/templates/_default_/images/icons/profile/invites.png" border="0"/></td>
                                    <td><a href="/users/invites.html" title="{$LANG.MY_INVITES}">{$LANG.MY_INVITES}</a> {$usr.invites_count}</td>
                                </tr>
								{/if}
                                <tr>
                                    <td><img src="/templates/_default_/images/icons/profile/edit.png" border="0"/></td>
                                    <td><a href="/users/{$usr.id}/editprofile.html" title="{$LANG.CONFIG_PROFILE}">{$LANG.MY_CONFIG}</a></td>
                                </tr>
                            {/if}
                            {if $is_admin && !$myprofile}
                            <tr>
                                <td><img src="/templates/_default_/images/icons/profile/edit.png" border="0"/></td>
                                <td><a href="/users/{$usr.id}/editprofile.html" title="{$LANG.CONFIG_PROFILE}">{$LANG.CONFIG_PROFILE}</a></td>
                            </tr>
                            {/if}
                            <tr>
                                <td><img src="/templates/_default_/images/icons/profile/karma.png" border="0"/></td>
                                <td><a href="/users/{$usr.id}/karma.html" title="{$LANG.KARMA_HISTORY}">{$LANG.KARMA_HISTORY}</a></td>
                            </tr>
							{if !$myprofile}
                            	{if $is_admin}
                                	{if !$usr.banned}
                                    <tr>
                                        <td><img src="/templates/_default_/images/icons/profile/award.png" border="0"/></td>
                                        <td><a href="/users/{$usr.id}/giveaward.html" title="{$LANG.TO_AWARD}">{$LANG.TO_AWARD}</a></td>
                                    </tr>
                                    <tr>
                                        <td><img src="/templates/_default_/images/icons/profile/ban.png" border="0"/></td>
                                        <td><a href="/admin/index.php?view=userbanlist&do=add&to={$usr.id}" title="{$LANG.TO_BANN}">{$LANG.TO_BANN}</a></td>
                                    </tr>
                                    {/if}
                                <tr>
                                    <td><img src="/templates/_default_/images/icons/profile/delprofile.png" border="0"/></td>
                                    <td><a href="/users/{$usr.id}/delprofile.html" title="{$LANG.DEL_PROFILE}">{$LANG.DEL_PROFILE}</a></td>
                                </tr>
                                {/if}
                         	{/if}

                            </table></div>
                            </div>
{* ================================================================================ *}
						{/if}                        
					</td>
				</tr>
			</table>
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td>

                        <div id="user_profile_url">
                            <div>{$LANG.LINK_TO_THIS_PAGE}:</div>
                            <a href="{$usr.profile_link}" title="{$usr.nickname}">{$usr.profile_link}</a>
                        </div>

					</td>
				</tr>
			</table>
	    </td>
    	<td valign="top" style="padding-left:10px">	
			<div id="profiletabs">
				<ul id="tabs"> 
					<li><a href="#upr_profile"><span>{$LANG.PROFILE}</span></a></li>
					{if $myprofile && $cfg.sw_feed}
						<li><a href="#upr_feed"><span>{$LANG.FEED}</span></a></li>
					{/if}
                    {if $myprofile}
                        <li><a href="#upr_ulogin"><span>uLogin</span></a></li>
                    {/if}
					{if $cfg.sw_clubs}
						<li><a href="#upr_clubs"><span>{$LANG.CLUBS}</span></a></li>
					{/if}
                    {if $cfg.sw_awards}
                        <li><a href="#upr_awards"><span>{$LANG.AWARDS}</span></a></li>
                    {/if}
                    {foreach key=id item=plugin from=$plugins}
                        <li><a href="#upr_{$plugin.name}"><span>{$plugin.title}</span></a></li>
                    {/foreach}
				</ul> 
				
				{* ============================== «¿ À¿ƒ ¿ π1 ============================================== *}
				<div id="upr_profile">
					<div class="user_profile_data">
					
						<div class="field">
							<div class="title">{$LANG.STATUS}:</div>
							<div class="value">{$usr.status}</div>
						</div>						
						<div class="field">
							<div class="title">{$LANG.LAST_VISIT}:</div>
							<div class="value">{$usr.flogdate}</div>
						</div>						
						<div class="field">
							<div class="title">{$LANG.DATE_REGISTRATION}:</div>
							<div class="value">
                                {$usr.fregdate}
                            </div>
						</div>
                        {if $usr.inv_login}
                            <div class="field">
                                <div class="title">{$LANG.INVITED_BY}:</div>
                                <div class="value">
                                    <a href="{profile_url login=$usr.inv_login}">{$usr.inv_nickname}</a>
                                </div>
                            </div>
                        {/if}
                        {if $usr.city}
						<div class="field">
							<div class="title">{$LANG.CITY}:</div>
                            <div class="value"><a href="/users/city/{$usr.cityurl}">{$usr.city}</a></div>
						</div>
                        {/if}
						
						{if $usr.showbirth && $usr.birthdate}
						<div class="field">
							<div class="title">{$LANG.BIRTH}:</div>
							<div class="value">{$usr.birthdate}</div>
						</div>
						{/if}
						
						{if $usr.gender}
						<div class="field">
							<div class="title">{$LANG.SEX}:</div>
							<div class="value">{$usr.gender}</div>
						</div>
						{/if}
						
						{if $usr.showicq && $usr.icq}
						<div class="field">
							<div class="title">ICQ:</div>
							<div class="value">{$usr.icq}</div>
						</div>
						{/if}				
						
						{if $usr.showmail}
							{add_js file='includes/jquery/jquery.nospam.js'}
							<div class="field">
								<div class="title">E-mail:</div>
								<div class="value"><a href="#" rel="{$usr.email|NoSpam}" class="email">{$usr.email}</a></div>
							</div>
							{literal}
								<script>						
										$('.email').nospam({ replaceText: true });
								</script>
							{/literal}			
						{/if}				

                        {if $cfg.sw_comm}
						<div class="field">
							<div class="title">{$LANG.COMMENTS}:</div>
							<div class="value">{$usr.comments_count}
                                {if $usr.comments_count}<a href="/users/{$usr.id}/comments.html" title="{$LANG.READ}">&rarr;</a>{/if}
                            </div>
						</div>
                        {/if}

                        {if $cfg.sw_forum}
						<div class="field">
							<div class="title">{$LANG.MESS_IN_FORUM}:</div>
							<div class="value">{$usr.forum_count}
                                {if $usr.forum_count}<a href="/users/{$usr.id}/forumposts.html" title="{$LANG.READ}">&rarr;</a>{/if}
                            </div>
						</div>
                        {/if}
						
						<div class="field">
							<div class="title">{$LANG.HOBBY} ({$LANG.TAGSS}):</div>
							<div class="value">{$usr.description}</div>
						</div>					
					</div>
					
					<div>
						{if $cfg.privforms}
							{$usr.privforms}
						{/if}												

                        <div class="usr_profile_block">
                            <div class="usr_wall_header">
                                {if !$myprofile}
                                    {$LANG.USER_CONTENT}
                                {else}
                                    {$LANG.MY_CONTENT}
                                {/if}
                            </div>
                            <div id="usr_links">
                                {if $cfg.sw_blogs}
                                    {if $usr.blog_link}
                                        <div id="usr_blog">
                                            {$usr.blog_link}
                                        </div>
                                    {/if}
                                {/if}
                                {if $cfg.sw_files}
                                    <div id="usr_files">
                                        <a href="/users/{$usr.id}/files.html">{$LANG.FILES}</a> <sup>{$usr.files_count}</sup>
                                    </div>
                                {/if}
                                {if $cfg.sw_board}
                                    <div id="usr_board">
                                        <a href="/users/{$usr.id}/board.html">{$LANG.ADVS}</a> <sup>{$usr.board_count}</sup>
                                    </div>
                                {/if}
                            </div>
                        </div>

                        {if $usr.albums}
                            <div class="usr_albums_block usr_profile_block">
                                {if $usr.albums_total > $usr.albums_show}
                                    <div class="float_bar">
                                        <a href="/users/{$usr.id}/photoalbum.html">{$LANG.ALL_ALBUMS}</a> ({$usr.albums_total})
                                    </div>
                                {/if}
                                <div class="usr_wall_header">
                                    {if !$myprofile}
                                        {$LANG.USER_PHOTOS}
                                    {else}
                                        {$LANG.MY_PHOTOS}
                                    {/if}
                                </div>
                                <ul class="usr_albums_list">
                                    {foreach key=key item=album from=$usr.albums}
                                        <li>
                                            <div class="usr_album_thumb">
                                                <a href="/users/{$usr.login}/photos/{$album.type}{$album.id}.html" title="{$album.title}">
                                                    <img src="{$album.imageurl}" width="64" height="64" border="0" alt="{$album.title}" />
                                                </a>
                                            </div>
                                            <div class="usr_album">
                                                <div class="link">
                                                    <a href="/users/{$usr.login}/photos/{$album.type}{$album.id}.html">{$album.title}</a>
                                                </div>
                                                <div class="count">{$album.photos_count|spellcount:$LANG.PHOTO:$LANG.PHOTO2:$LANG.PHOTO10}</div>
                                                <div class="date">{$album.pubdate}</div>
                                            </div>
                                        </li>
                                    {/foreach}
                                 </ul>
                            </div>
                        {/if}

                        {if $usr.friends_total}
                            <div class="usr_friends_block usr_profile_block">
                                {if $usr.friends_total > 6}
                                    <div class="float_bar">
                                        <a href="/users/{$usr.id}/friendlist.html">{$LANG.ALL_FRIENDS}</a> ({$usr.friends_total})
                                    </div>
                                {/if}
                                <div class="usr_wall_header">
                                    {if !$myprofile}
                                        {$LANG.USER_FRIENDS}
                                    {else}
                                        {$LANG.MY_FRIENDS}
                                    {/if}
                                </div>
                                {assign var="col" value="1"}
                                <table width="" cellpadding="5" cellspacing="0" border="0" class="usr_friends_list" align="left">
                                  {foreach key=tid item=friend from=$usr.friends}
                                  {if $col==1}<tr>{/if}
                                            <td align="center" valign="top">
                                                <div class="usr_friend_cell">
                                                    <div align="center"><a class="friend_link" href="{profile_url login=$friend.login}">{$friend.nickname}</a></div>
                                                    <div align="center"><a href="{profile_url login=$friend.login}">{$friend.avatar}</a></div>
                                                    <div align="center">{$friend.flogdate}</div>
                                                </div>
                                            </td>
                                              
                                      {if $col==6} </tr> {assign var="col" value="1"} {else} {math equation="x + 1" x=$col assign="col"} {/if}
                                  {/foreach}
                                  {if $col>1}<td colspan="{math equation="x - y + 1" x=$col y=$maxcols}">&nbsp;</td></tr>{/if}
                                </table>
                            </div>
                        {/if}

						{if $cfg.sw_wall}
							<div class="usr_wall usr_profile_block">
								<div class="usr_wall_header">
                                    {$LANG.USER_WALL}
                                    <div class="usr_wall_addlink" style="float:right">
                                        <a href="#addwall" id="addlink" onclick="{literal}$('div#addwall').slideToggle();$('.usr_wall_addlink').toggle();$('.wall_message').focus();{/literal}">
                                            <span>{$LANG.WRITE_ON_WALL}</span>
                                        </a>
                                    </div>
                                </div>
								<div id="addwall" style="display:none">{$usr.addwall_html}</div>
								<div class="usr_wall_body" style="clear:both">
                                    <div class="wall_body">{$usr.wall_html}</div>
                                </div>
							</div>
						{/if}
					</div>
				</div>
				
				{* ============================== «¿ À¿ƒ ¿ π2 ============================================== *}
				{if $myprofile && $cfg.sw_feed}
					<div id="upr_feed">
                       {if $usr.actions}
                            <div class="actions_list">
                                {foreach key=aid item=action from=$usr.actions}
                                    <div class="action_entry act_{$action.name}">
                                        <div class="action_date{if $action.is_new} is_new{/if}">{$action.pubdate} {$LANG.BACK}</div>
                                        <div class="action_title">
                                            <a href="{$action.user_url}" class="action_user">{$action.user_nickname}</a>
                                            {if $action.message}
                                                {$action.message}{if $action.description}:{/if}
                                            {else}
                                                {if $action.description}
                                                    &rarr; {$action.description}
                                                {/if}
                                            {/if}
                                        </div>
                                        {if $action.message}
                                            {if $action.description}
                                                <div class="action_details">{$action.description}</div>
                                            {/if}
                                        {/if}
                                    </div>
                                {/foreach}
                            </div>
                       {else}
                            <p>{$LANG.FEED_DESC}</p>
                            <p>{$LANG.FEED_EMPTY_TEXT}</p>
                       {/if}
					</div>	
				{/if}

                {* ============================== ULOGIN SYNC ============================================== *}
                {if $myprofile}
                    <div id="upr_ulogin">
                        <div class="user_profile_data">
                            {php}cmsCore::callEvent('ULOGIN_SYNC_PANEL', array());{/php}
                        </div>
                    </div>
                {/if}

				{* ============================== «¿ À¿ƒ ¿ π5 ============================================== *}
				{if $cfg.sw_clubs}
					<div id="upr_clubs">
						{if $usr.clubs}
							{if sizeof($usr.clubs.member)}
								<div class="usr_clubs">
									<span class="label">{$LANG.CONSIST}:</span>
									{foreach key=tid item=club from=$usr.clubs.member}
										<a class="usr_club_link" href="/clubs/{$club.id}">{$club.title}</a>
									{/foreach}
								</div>
							{/if}
							{if sizeof($usr.clubs.moder)}
								<div class="usr_clubs">
									<span class="label">{$LANG.MODERATE}:</span>
									{foreach key=tid item=club from=$usr.clubs.moder}
										<a class="usr_club_link" href="/clubs/{$club.id}">{$club.title}</a>
									{/foreach}
								</div>
							{/if}
							{if sizeof($usr.clubs.admin)}
								<div class="usr_clubs">
									<span class="label">{$LANG.ADMINING}:</span>
									{foreach key=tid item=club from=$usr.clubs.admin}
										<a class="usr_club_link" href="/clubs/{$club.id}">{$club.title}</a>
									{/foreach}
								</div>
							{/if}													
						{else}
                            {if !$myprofile}
                                <p><strong>{$usr.nickname}</strong> {$LANG.USET_NOT_IN_CLUBS}</p>
                            {else}
                                <p>{$LANG.YOU_NOT_IN_CLUBS}</p>
                            {/if}
						{/if}
					</div>
				{/if}
				
				{* ============================== «¿ À¿ƒ ¿ π6 ============================================== *}

                {if $cfg.sw_awards}
					<div id="upr_awards">
						<div class="awards_list_link">
							<a href="/users/awardslist.html">{$LANG.HOW_GET_AWARD}</a>
						</div>
						{if sizeof($usr.awards_html)}
							{$usr.awards_html}
						{/if}
					</div>
                {/if}

                {foreach key=id item=plugin from=$plugins}
                    <div id="upr_{$plugin.name}">{$plugin.html}</div>
                {/foreach}

			</div>						
	</td>
  </tr>
</table>
