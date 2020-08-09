<?php

/* @ Zine theme */
/*	@ Blocthemes 2020	*/
/*	@	SMF 2.0.x	*/

// Initialize the template... mainly little settings.
function template_init()
{
	global $context, $settings, $options, $txt;

	$settings['use_default_images'] = 'never';
	$settings['doctype'] = 'xhtml';
	$settings['theme_version'] = '2.0';
	$settings['use_tabs'] = true;
	$settings['use_buttons'] = true;
	$settings['separate_sticky_lock'] = true;
	$settings['strict_doctype'] = false;
	$settings['message_index_preview'] = true;
	$settings['require_theme_strings'] = true;
	$settings['show_member_bar'] = true;
	$settings['qubs_counter'] = 1;
}

// The main sub template above the content.
function template_html_above()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	loadtemplate('Common');

	// Show right to left and the character set for ease of translating.
	echo '
<!DOCTYPE html>
<html', $context['right_to_left'] ? ' dir="rtl"' : '', '>
<head>
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/index', $context['theme_variant'], '.css?v102" />
	<link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;900&family=Roboto:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">	
	';

	// RTL languages require an additional stylesheet.
	if ($context['right_to_left'])
		echo '
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/rtl.css" />';

	// Here comes the JavaScript bits!
	echo '
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>

	<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/script.js?fin20"></script>
	<script type="text/javascript" src="', $settings['theme_url'], '/scripts/theme.js?fin20"></script>
	<script type="text/javascript"><!-- // --><![CDATA[
		var smf_theme_url = "', $settings['theme_url'], '";
		var smf_default_theme_url = "', $settings['default_theme_url'], '";
		var smf_images_url = "', $settings['images_url'], '";
		var smf_scripturl = "', $scripturl, '";
		var smf_iso_case_folding = ', $context['server']['iso_case_folding'] ? 'true' : 'false', ';
		var smf_charset = "', $context['character_set'], '";', $context['show_pm_popup'] ? '
		var fPmPopup = function ()
		{
			if (confirm("' . $txt['show_personal_messages'] . '"))
				window.open(smf_prepareScriptUrl(smf_scripturl) + "action=pm");
		}
		addLoadEvent(fPmPopup);' : '', '
		var ajax_notification_text = "', $txt['ajax_in_progress'], '";
		var ajax_notification_cancel_text = "', $txt['modify_cancel'], '";
	// ]]></script>';

	echo '
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
	<meta name="description" content="', $context['page_title_html_safe'], '" />', !empty($context['meta_keywords']) ? '
	<meta name="keywords" content="' . $context['meta_keywords'] . '" />' : '', '
	<title>', $context['page_title_html_safe'], '</title>';

	// Please don't index these Mr Robot.
	if (!empty($context['robot_no_index']))
		echo '
	<meta name="robots" content="noindex" />';

	// Present a canonical url for search engines to prevent duplicate content in their indices.
	if (!empty($context['canonical_url']))
		echo '
	<link rel="canonical" href="', $context['canonical_url'], '" />';

	// Show all the relative links, such as help, search, contents, and the like.
	echo '
	<link rel="help" href="', $scripturl, '?action=help" />
	<link rel="search" href="', $scripturl, '?action=search" />
	<link rel="contents" href="', $scripturl, '" />';

	// If RSS feeds are enabled, advertise the presence of one.
	if (!empty($modSettings['xmlnews_enable']) && (!empty($modSettings['allow_guestAccess']) || $context['user']['is_logged']))
		echo '
	<link rel="alternate" type="application/rss+xml" title="', $context['forum_name_html_safe'], ' - ', $txt['rss'], '" href="', $scripturl, '?type=rss;action=.xml" />';

	// If we're viewing a topic, these should be the previous and next topics, respectively.
	if (!empty($context['current_topic']))
		echo '
	<link rel="prev" href="', $scripturl, '?topic=', $context['current_topic'], '.0;prev_next=prev" />
	<link rel="next" href="', $scripturl, '?topic=', $context['current_topic'], '.0;prev_next=next" />';

	// If we're in a board, or a topic for that matter, the index will be the board's index.
	if (!empty($context['current_board']))
		echo '
	<link rel="index" href="', $scripturl, '?board=', $context['current_board'], '.0" />';

	// Output any remaining HTML headers. (from mods, maybe?)
	echo $context['html_headers'];

	echo '
</head>
<body id="a_body">';
}

function template_body_above()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
<section id="header_section">
	<header id="top_header"' , !empty($context['show_login_bar']) ? ' class="guests"' : '' , '>
		<h1 id="main_title"><a href="', $scripturl, '">' , $context['forum_name'] , '</a></h1>
		<div id="main_menu">' , template_menu() , '</div>
		<div class="user_menu">' , template_head_user() , '</div>
	</header>
</section>
<section id="linktree_section">' , theme_linktree() , '</section>

<section id="content_section" class="maxwidth">
	' , function_exists('template_section_menu') ? template_section_menu() : '' , '
	<main id="content_main">';

	// fix the pagelinks
	if(!empty($context['page_index']))
	{
		$fixed = str_replace(array('[',']'),array('',''),$context['page_index']);
		$context['page_index'] = $fixed;
	}
}

function template_body_below()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	</main>
</section>

<section id="footer_section">
	<footer id="bottom_footer">
		<span>', theme_copyright(),'</span>';

	// Show the load time?
	if ($context['show_load_time'])
		echo '
		<small>', $txt['page_created'], $context['load_time'], $txt['seconds_with'], $context['load_queries'], $txt['queries'], '</small>';

	echo '
		<small><a href="https://github.com/blocthemes/Zine" target="_blank">Zine theme by Bloc</small>
	</footer>
</section>';
}

function template_html_below()
{
	echo '
</body></html>';
}

function template_head_user()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// If the user is logged in, display stuff like their name, new messages, etc.
	if ($context['user']['is_logged'])
	{
		
		echo '
				<ul class="reset mob horiz_menu circular">';
		
		if (!empty($context['user']['avatar']))
			echo '
					<li><a href="' , $scripturl , '?action=profile" class="mavatar" style="background-image: url(', $context['user']['avatar']['href'], '"></a></li>';
		else
			echo '
					<li class="red"><a href="' , $scripturl , '?action=profile"  class="mavatar">' , substr($context['user']['name'],0,1) , '</a></li>';

		echo '
					<li class="unr"><a href="', $scripturl, '?action=unread" title="' , $txt['a_unread'] , '">', substr($txt['a_unread'],0,1), '</a></li>
					<li class="rep"><a href="', $scripturl, '?action=unreadreplies" title="' ,$txt['a_replies'] , '">', substr($txt['a_replies'],0,1), '</a></li>';

		// Is the forum in maintenance mode?
		if ($context['in_maintenance'] && $context['user']['is_admin'])
			echo '
					<li class="notice" title="', $txt['a_maintain'], '"><a id="maintain">' , substr($txt['a_maintain'],0,1) , '</a></li>';

		// Are there any members waiting for approval?
		if (!empty($context['unapproved_members']))
			echo '
					<li class="unapp"><a href="', $scripturl, '?action=admin;area=viewmembers;sa=browse;type=approve">', $context['unapproved_members']  , '</a></li>';

		if (!empty($context['open_mod_reports']) && $context['show_open_reports'])
			echo '
					<li class="openm"><a href="', $scripturl, '?action=moderate;area=reports">', $context['open_mod_reports'], '</a></li>';

		echo '
				</ul>';
	}
	// Otherwise they're a guest - this time ask them to either register or login - lazy bums...
	elseif (!empty($context['show_login_bar']))
	{
		echo '
				<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/sha1.js"></script>
				<form id="guest_form" action="', $scripturl, '?action=login2" method="post" accept-charset="', $context['character_set'], '" ', empty($context['disable_login_hashing']) ? ' onsubmit="hashLoginPassword(this, \'' . $context['session_id'] . '\');"' : '', '>
					<div class="onerow">
						<input type="text" name="user" class="input_text" />
						<input type="password" name="passwrd" class="input_text input_password" />
						<select name="cookielength" class="input_select">
							<option value="60">', $txt['one_hour'], '</option>
							<option value="1440">', $txt['one_day'], '</option>
							<option value="10080">', $txt['one_week'], '</option>
							<option value="43200">', $txt['one_month'], '</option>
							<option value="-1" selected="selected">', $txt['forever'], '</option>
						</select>
						<input type="submit" value="', $txt['login'], '" class="button_submit" />
					</div>

					<div class="info">', $txt['quick_login_dec'], '</div>';

		if (!empty($modSettings['enableOpenID']))
			echo '
					<input type="text" name="openid_identifier" id="openid_url" class="input_text openid_login" />';

		echo '
					<input type="hidden" name="hash_passwrd" value="" /><input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>';
	}
}
function template_head_news()
{
	global $context, $settings, $txt;

	// Show a random news item? (or you could pick one from news_lines...)
	if (!empty($settings['enable_news']) && !empty($context['random_news_line']))
		echo '
				<h2>', $txt['news'], ': </h2>
				<p>', $context['random_news_line'], '</p>';
}
function template_head_search()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
				<form id="search_form" action="', $scripturl, '?action=search2" method="post" accept-charset="', $context['character_set'], '">
					<div class="multi_set">
						<input type="text" name="search" value="" class="input_text multi_start" />
						<input type="submit" name="submit" value="', $txt['search'], '" class="button_submit multi_end" />
					</div>
					<input type="hidden" name="advanced" value="0" />';

	// Search within current topic?
	if (!empty($context['current_topic']))
		echo '
					<input type="hidden" name="topic" value="', $context['current_topic'], '" />';
	// If we're on a certain board, limit it to this board ;).
	elseif (!empty($context['current_board']))
		echo '
					<input type="hidden" name="brd[', $context['current_board'], ']" value="', $context['current_board'], '" />';

	echo '	</form>';

}


// Show a linktree. This is that thing that shows "My Community | General Category | General Discussion"..
function theme_linktree($force_show = false)
{
	global $context, $settings, $options, $shown_linktree;

	echo '
		<span id="open-amenu" class="icon-menu"></span>';

	// If linktree is empty, just return - also allow an override.
	if (empty($context['linktree']) || (!empty($context['dont_default_linktree']) && !$force_show))
		return;

	echo '
		<ul class="reset">';

	// Each tree item has a URL and name. Some may have extra_before and extra_after.
	foreach ($context['linktree'] as $link_num => $tree)
	{
		echo '
			<li', ($link_num == count($context['linktree']) - 1) ? ' class="last"' : '', '>';

		// Show something before the link?
		if (isset($tree['extra_before']))
			echo $tree['extra_before'];

		// Show the link, including a URL if it should have one.
		echo isset($tree['url']) ? '
				<a href="' . $tree['url'] . '"><span>' . $tree['name'] . '</span></a>' : '<span>' . $tree['name'] . '</span>';
		
		// Show something after the link...?
		if (isset($tree['extra_after']))
			echo $tree['extra_after'];

		echo '
			</li>';
	}
	echo '
		</ul>';

	$shown_linktree = true;
}

// Show the menu up top. Something like [home] [help] [profile] [logout]...
function template_menu()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
	<nav class="navigation horiz_menu">
		<input class="toggle" type="checkbox" id="more" aria-hidden="true" tabindex="-1"/>
		<div class="navigation__inner">
			<ul class="navigation__list">';

	foreach ($context['menu_buttons'] as $act => $button)
	{
		$button = str_replace(array('[',']'),array('<span class="circular2">','</span>'),$button);
		echo '
				<li id="button_', $act, '" class="navigation__item' , !empty($button['sub_buttons']) ? ' subs' : '' , $button['active_button'] ? ' current' : '','">
					<a class="', $button['active_button'] ? 'active ' : '', 'navigation__link" href="', $button['href'], '"', isset($button['target']) ? ' target="' . $button['target'] . '"' : '', '>
						<span class="', !empty($button['sub_buttons']) ? 'parent ' : '' , isset($button['is_last']) ? 'last ' : '', 'firstlevel">', $button['title'], '</span>
					</a>';
		if (!empty($button['sub_buttons']))
		{
			echo '
					<ul>';

			foreach ($button['sub_buttons'] as $childbutton)
			{
				echo '
						<li>
							<a href="', $childbutton['href'], '"', isset($childbutton['target']) ? ' target="' . $childbutton['target'] . '"' : '', '>
								<span', isset($childbutton['is_last']) ? ' class="last"' : '', '>', $childbutton['title'], !empty($childbutton['sub_buttons']) ? '...' : '', '</span>
							</a>
						</li>';
			}
				echo '
					</ul>';
		}
		echo '
				</li>';
	}

	echo '
			</ul>
			<div class="navigation__toggle">
				<label class="navigation__link transition200ms" for="more" aria-hidden="true">&nbsp;</label>
			</div>
		</div>
	</nav>';
}

// Generate a strip of buttons.
function template_button_strip($button_strip, $direction = 'top', $strip_options = array())
{
	global $settings, $context, $txt, $scripturl;

	if (!is_array($strip_options))
		$strip_options = array();

	// List the buttons in reverse order for RTL languages.
	if ($context['right_to_left'])
		$button_strip = array_reverse($button_strip, true);

	// Create the buttons...
	$buttons = array();
	foreach ($button_strip as $key => $value)
	{
		if (!isset($value['test']) || !empty($context[$value['test']]))
		{
			$buttons[] = '
				<a' . (isset($value['id']) ? ' id="button_strip_' . $value['id'] . '"' : '') . ' class="button_strip_' . $key . (isset($value['active']) ? ' active' : ' bs hide') . '" href="' . $value['url'] . '"' . (isset($value['custom']) ? ' ' . $value['custom'] : '') . '>' . $txt[$value['text']] . '</a>';
		}
	}

	// No buttons? No button strip either.
	if (empty($buttons))
		return;

	echo '		
	<span class="qubs" id="qubs' , $settings['qubs_counter'] , '">', implode('', $buttons), '</span>';
	
	$settings['qubs_counter']++;
}

function get_avatars($ids)
{
	global $context, $smcFunc,$db_prefix, $user_profile, $scripturl, $modSettings, $settings, $boardurl, $image_proxy_enabled, $image_proxy_secret;
	
	if(empty($ids))
		return;
	else
		$i = implode(',',array_keys($ids));

	$request = $smcFunc['db_query']('','
		SELECT mem.id_member, 
		IFNULL(a.id_attach, 0) AS id_attach, a.filename, a.attachment_type, mem.avatar AS avatar
		FROM {db_prefix}members AS mem
		LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = mem.id_member)
		WHERE mem.id_member IN (' . $i . ')'
	);
	$context['a_avatars'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{		
		$avatar = $row['avatar'] == '' ? ($row['id_attach'] > 0 ? (empty($row['attachment_type']) ? $scripturl . '?action=dlattach;attach=' . $row['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) : '') : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']);
		if ($image_proxy_enabled && stripos($avatar, 'http://') !== false)
			$avatar = strtr($boardurl, array('http://' => 'https://')) . '/proxy.php?request=' . urlencode($avatar) . '&hash=' . md5($avatar . $image_proxy_secret);
		
		if (!empty($avatar))
			$context['a_avatars'][$row['id_member']] = $avatar;
	}
	$smcFunc['db_free_result']($request);
	return;
}

function fix_pageindex($c)
{
	$fixed = str_replace(array('[',']'),array('',''),$c);
	return $fixed;
}


?>