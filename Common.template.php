<?php

/* @ Rebus89 theme */
/*	@ Blocthemes 2020	*/
/*	@	SMF 2.0.x	*/

function a_boardindex($board, $category_id = '')
{
	global $context, $scripturl, $settings, $options, $modSettings, $txt;

	echo '
		<ul class="reset">
			<li id="board_', $board['id'], '" class="b_icon' , $board['is_redirect'] ? ' b_redirect' : '' , '">';
	
	if (!empty($context['a_avatars'][$board['last_post']['member']['id']]))
		echo '
				<a href="', $board['last_post']['member']['href'], '" class="avatar" style="background-image: url(', $context['a_avatars'][$board['last_post']['member']['id']], ');">&nbsp;</a>';
	else
		echo '
				<a href="', $board['last_post']['member']['href'], '"  class="no_avatar">' , (substr($board['last_post']['member']['name'],0,1)) , '</a>';
	echo '
			</li>
			<li class="b_subject">
				<a href="', ($board['is_redirect'] || $context['user']['is_guest'] ? $board['href'] : $scripturl . '?action=unread;board=' . $board['id'] . '.0;children'), '">';

	// If the board or children is new, show an indicator.
	if ($board['new'] || $board['children_new'])
		echo '
					<span class="board_icon ', $board['new'] ? 'new' : 'sub', '" title="', $txt['new_posts'], '"></span>';

	echo '	</a>
				<a href="', $board['href'], '">', $board['name'], '</a>
			</li>
			<li class="b_description">', $board['description'], '</li>
			<li class="b_moderators' , !empty($board['moderators']) ? ' has_items' : '', '">';

	// Show the "Moderators: ". Each has name, href, link, and id. (but we're gonna use link_moderators.)
	if (!empty($board['moderators']))
		echo '
				', count($board['moderators']) === 1 ? $txt['moderator'] : $txt['moderators'], ': ', implode(', ', $board['link_moderators']);

	echo '
			</li>
			<li class="b_stats">
				', comma_format($board['posts']), ' <span>', $board['is_redirect'] ? $txt['redirects'] : $txt['posts'], $board['is_redirect'] ? '' : '</span> | ' . comma_format($board['topics']) . ' <span>' . $txt['board_topics'], '</span>
			</li>
			<li class="b_last">';

	if (!empty($board['last_post']['id']))
		echo '
				' , $board['last_post']['member']['link'] , ' ' , $txt['in'] , ' ' , $board['last_post']['link'] , ' <span class="b_time">' , $board['last_post']['time'] , '</span>';

	echo '
			</li>
			<li class="b_sub' , !empty($board['children']) ? ' has_items' : '', '">';

	if (!empty($board['children']))
	{
		$children = array();

		foreach ($board['children'] as $child)
		{
			if (!$child['is_redirect'])
				$child['link'] = '<a href="' . $child['href'] . '" ' . ($child['new'] ? 'class="board_new_posts" ' : '') . 'title="' . ($child['new'] ? $txt['new_posts'] : $txt['old_posts']) . ' (' . $txt['board_topics'] . ': ' . comma_format($child['topics']) . ', ' . $txt['posts'] . ': ' . comma_format($child['posts']) . ')">' . $child['name'] . ($child['new'] ? '</a> <a ' . ($child['new'] ? 'class="new_posts" ' : '') . 'href="' . $scripturl . '?action=unread;board=' . $child['id'] . '" title="' . $txt['new_posts'] . ' (' . $txt['board_topics'] . ': ' . comma_format($child['topics']) . ', ' . $txt['posts'] . ': ' . comma_format($child['posts']) . ')"><span class="new_posts"></span>' : '') . '</a>';
			else
				$child['link'] = '<a href="' . $child['href'] . '" title="' . comma_format($child['posts']) . ' ' . $txt['redirects'] . '">' . $child['name'] . '</a>';

			// Has it posts awaiting approval?
			if ($child['can_approve_posts'] && ($child['unapproved_posts'] || $child['unapproved_topics']))
				$child['link'] .= ' <a href="' . $scripturl . '?action=moderate;area=postmod;sa=' . ($child['unapproved_topics'] > 0 ? 'topics' : 'posts') . ';brd=' . $child['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" title="' . sprintf($txt['unapproved_posts'], $child['unapproved_topics'], $child['unapproved_posts']) . '" class="moderation_link"><i class="icon i-alert"></i></a>';

			$children[] = $child['link'];
		}
		echo '
				<span>', $txt['parent_boards'], ': </span>', implode(', ', $children);
	}
	echo '
			</li>
		</ul>';
	
}

function a_topic($topic, $check = false)
{
	global $context, $scripturl, $settings, $options, $modSettings, $txt;

	// fix the pagelinks
	if(!empty($topic['pages']))
	{
		$fixed = str_replace(array('&#171;','&#187;'),array('',''),$topic['pages']);
		$topic['pages'] = $fixed;
	}

	// add some type classes!	
	$class = '';
	if($topic['is_sticky'])
		$class .= '<span class="stick"></span>';
	if($topic['is_locked'])
		$class .= '<span class="lock"></span>';

	if($context['can_approve_posts'] && $topic['unapproved_posts'])
		$class .= '<span class="unapp"></span>';
	
	$type = '';
	if($topic['is_posted_in'])
		$type .= '<span class="own"></span>';
	if($topic['is_poll'])
		$type .= '<span class="poll"></span>';

	$hot = '';
	if($topic['is_hot'])
		$hot ='<span class="hot"></span>';
	if($topic['is_very_hot'])
		$hot ='<span class="veryhot"></span>';

	echo '
		<ul class="reset">
			<li class="b_icon">' , $class;
	
	if (!empty($context['a_avatars'][$topic['first_post']['member']['id']]))
		echo '
				<a href="', $topic['first_post']['member']['href'], '" class="avatar" style="background-image: url(', $context['a_avatars'][$topic['first_post']['member']['id']], ');">&nbsp;</a>';
	else
		echo '
				<a href="', $topic['first_post']['member']['href'], '"  class="no_avatar">' , (substr($topic['first_post']['member']['name'],0,1)) , '</a>';
	echo '
			</li>
			<li class="b_subject">';

	// Is this topic new? (assuming they are logged in!)
	if ($topic['new'] && $context['user']['is_logged'])
		echo '
				<a href="', $topic['new_href'], '" title="', $txt['new'], '" ><span class="board_icon sub"></span></span>';

	echo		$topic['first_post']['link'], '
			</li>
			<li class="b_description"> </li>
			<li class="b_moderators"></li>
			<li class="b_stats">', $topic['views'], ' ' , $txt['views'] , '</li>
			<li class="b_last">
				' , $topic['last_post']['member']['link'], ' ' , $txt['in'], ' ', $topic['last_post']['link'], ' <span class="b_time">', $topic['last_post']['time'], '</span>
			</li>
			<li class="b_sub has_items">
				<span>', $topic['replies'], ' ' , $txt['replies'], $hot, $type, '</span>
				<span class="moderation">';

	// Show the quick moderation options?
	if (!empty($context['can_quick_mod']) || $check)
	{
		if ($options['display_quick_mod'] == 1 || $check)
			echo '
					<input type="checkbox" name="topics[]" value="', $topic['id'], '" class="input_check" />';
		else
		{
			// Check permissions on each and show only the ones they are allowed to use.
			if ($topic['quick_mod']['remove'])
				echo '
					<a href="', $scripturl, '?action=quickmod;board=', $context['current_board'], '.', $context['start'], ';actions[', $topic['id'], ']=remove;', $context['session_var'], '=', $context['session_id'], '" onclick="return confirm(\'', $txt['quickmod_confirm'], '\');"><span class="icon-trash" title="', $txt['remove_topic'], '"></span></a>';

			if ($topic['quick_mod']['lock'])
				echo '
					<a href="', $scripturl, '?action=quickmod;board=', $context['current_board'], '.', $context['start'], ';actions[', $topic['id'], ']=lock;', $context['session_var'], '=', $context['session_id'], '" onclick="return confirm(\'', $txt['quickmod_confirm'], '\');"><span class="icon-lock" title="', $txt['set_lock'], '"></span></a>';

			if ($topic['quick_mod']['sticky'])
				echo '
					<a href="', $scripturl, '?action=quickmod;board=', $context['current_board'], '.', $context['start'], ';actions[', $topic['id'], ']=sticky;', $context['session_var'], '=', $context['session_id'], '" onclick="return confirm(\'', $txt['quickmod_confirm'], '\');"><span class="icon-pin-outline" title="', $txt['set_sticky'], '"></span></a>';

			if ($topic['quick_mod']['move'])
				echo '
					<a href="', $scripturl, '?action=movetopic;board=', $context['current_board'], '.', $context['start'], ';topic=', $topic['id'], '.0"><span class="icon-forward-outline" title="', $txt['move_topic'], '"></span></a>';
		}
	}
	echo '	</span>
				<span class="pagelinks smaller">' , $topic['pages'], '</span>
			</li>
		</ul>';
}

function a_quickbuttons($message)
{
	global $context, $options, $scripturl, $settings, $txt;

	echo '
	<menu class="pagesection">
		<span class="qubs">';

	// Maybe we can approve it, maybe we should?
	if ($message['can_approve'])
		echo '
			<a href="', $scripturl, '?action=moderate;area=postmod;sa=approve;topic=', $context['current_topic'], '.', $context['start'], ';msg=', $message['id'], ';', $context['session_var'], '=', $context['session_id'], '">
					', $txt['approve'], '
				</a>
			';

	// Can they reply? Have they turned on quick reply?
	if ($context['can_quote'] && !empty($options['display_quick_reply']))
		echo '
			<a href="', $scripturl, '?action=post;quote=', $message['id'], ';topic=', $context['current_topic'], '.', $context['start'], ';last_msg=', $context['topic_last_message'], '" onclick="return oQuickReply.quote(', $message['id'], ');">', $txt['quote'], '</a>';
	// So... quick reply is off, but they *can* reply?
	elseif ($context['can_quote'])
		echo '
			<a href="', $scripturl, '?action=post;quote=', $message['id'], ';topic=', $context['current_topic'], '.', $context['start'], ';last_msg=', $context['topic_last_message'], '">', $txt['quote'], '</a>';

	// Can the user modify the contents of this post?
	if ($message['can_modify'])
		echo '
			<a href="', $scripturl, '?action=post;msg=', $message['id'], ';topic=', $context['current_topic'], '.', $context['start'], '">', $txt['modify'], '</a>';

	// How about... even... remove it entirely?!
	if ($message['can_remove'])
		echo '
			<a href="', $scripturl, '?action=deletemsg;topic=', $context['current_topic'], '.', $context['start'], ';msg=', $message['id'], ';', $context['session_var'], '=', $context['session_id'], '" onclick="return confirm(\'', $txt['remove_message'], '?\');">', $txt['remove'], '</a>';

	// What about splitting it off the rest of the topic?
	if ($context['can_split'] && !empty($context['real_num_replies']))
		echo '
			<a href="', $scripturl, '?action=splittopics;topic=', $context['current_topic'], '.0;at=', $message['id'], '">', $txt['split'], '</a>';

	// Can we restore topics?
	if ($context['can_restore_msg'])
		echo '
			<a href="', $scripturl, '?action=restoretopic;msgs=', $message['id'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['restore_message'], '</a>';


	// Can the user modify the contents of this post?  Show the modify inline image.
	if ($message['can_modify'])
		echo '
			<a id="mod_anchor_', $message['id'], '"><span class="icon-edit iconbig" id="modify_button_', $message['id'], '" onclick="oQuickModify.modifyMsg(\'', $message['id'], '\')"></span></a>';

	echo '
		</span>
	</menu>';
}

function a_message($message)
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	echo '
		<ul class="reset">
			<li class="b_icon">' , $class;
	
	if (!empty($message['member']['avatar']['href']))
		echo '
				<a href="', $message['member']['href'], '" class="avatar' , !empty($message['member']['online']['is_online']) ? ' online-beacon' : '' , '" style="background-image: url(', $message['member']['avatar']['href'], ');">&nbsp;</a>';
	else
		echo '
				<a href="', $message['member']['href'], '"  class="no_avatar' , !empty($message['member']['online']['is_online']) ? ' online-beacon' : '' , '">' , (substr($message['member']['name'],0,1)) , '</a>';

	echo '
				<ol class="reset member_area">
					<li class="member">', $message['member']['link'], '</li>';
	// Don't show these things for guests.
	if (!$message['member']['is_guest'])
	{
		// Show how many posts they have made.
		if (!isset($context['disabled_fields']['posts']))
			echo '
					<li class="postcount">', $message['member']['posts'], '</li>';
		// Show the post group if and only if they have no other group or the option is on, and they are in a post group.
		if ((empty($settings['hide_post_group']) || $message['member']['group'] == '') && $message['member']['post_group'] != '')
			echo '
					<li class="postgroup">', $message['member']['post_group'], '</li>';
		
		echo '
					<li class="stars">', $message['member']['group_stars'], '</li>';

	}
	// Show the member's primary group (like 'Administrator') if they have one.
	if (!empty($message['member']['group']))
		echo '
					<li class="membergroup">', $message['member']['group'], '</li>';

	// Show the member's custom title, if they have one.
	if (!empty($message['member']['title']))
		echo '
					<li class="title">', $message['member']['title'], '</li>';

	echo '
				</ol>
			</li>

			<li class="b_subject" id="subject_', $message['id'], '"><a href="', $message['href'], '" rel="nofollow">', $message['subject'], '</a></li>
			<li class="b_description">
				<section class="post">';

	if (!$message['approved'] && $message['member']['id'] != 0 && $message['member']['id'] == $context['user']['id'])
		echo '
					<p class="approve_post information">', $txt['post_awaiting_approval'], '</p>';
	echo '
					<div class="inner" id="msg_', $message['id'], '">', $message['body'], '</div>
				</section>
			</li>
			<li class="b_stats">' , $message['time'] , '</li>
			<li class="b_last">
				' , a_quickbuttons($message) , '
				' , a_attachments($message) , '
			</li>
			<li class="b_sub' , !empty($message['member']['signature']) ? ' has_items' : '' , '">
				' , !empty($message['member']['signature']) ? '<div class="signature">'.$message['member']['signature'].'</div>' : '' , '
			</li>
		</ul>';
}

function a_attachments($message)
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;
	
	// Assuming there are attachments...
	if (!empty($message['attachment']))
	{
		echo '
					<ul id="msg_', $message['id'], '_footer" class="atts">';

		$last_approved_state = 1;
		foreach ($message['attachment'] as $attachment)
		{
			echo '
						<li class="attach_grid">
							<div class="imgs">';

			if ($attachment['is_image'])
			{
				if ($attachment['thumbnail']['has_thumb'])
					echo '
								<a href="#att' , $attachment['id'] , '">
									<img id="thumb' , $attachment['id'] , '" class="a_block" src="', $attachment['thumbnail']['href'], '" alt="', $attachment['name'], '" />
								</a>
								<div id="att' , $attachment['id'] , '" class="modal superlayer">
									<a href="#thumb' , $attachment['id'] , '"><span class="circular2">X</span></a>
									<a href="' . $attachment['href'] . ';image" target="_blank"><span class="circular2"><span class="icon-eye-outline"></span></span></a>
									<a href="' . $attachment['href'] . '"><span class="circular2"><span class="icon-download-outline"></span></span></a>
									
									<img src="', $attachment['href'], ';image" class="modal-content" style="width: ' , $attachment['real_width'] , 'px; " alt="*">
									<div class="caption">' , $attachment['name'], '</div>
								</div>
							';
				else
					echo '
								<img  class="a_block" src="' . $attachment['href'] . ';image" alt="" />';
			}
			
			echo '		</div>
							<div class="texts">
								<a class="a_name" href="' . $attachment['href'] . '">' . $attachment['name'] . '</a>';

			if (!$attachment['is_approved'] && $context['can_approve'])
				echo '
								<a href="', $scripturl, '?action=attachapprove;sa=approve;aid=', $attachment['id'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['approve'], '</a>]&nbsp;|&nbsp;[<a href="', $scripturl, '?action=attachapprove;sa=reject;aid=', $attachment['id'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['delete'], '</a>';

			// Show a special box for unapproved attachments...
			if ($attachment['is_approved'] != $last_approved_state)
			{
				$last_approved_state = 0;
				echo '
								<span>', $txt['attach_awaiting_approve'];

				if ($context['can_approve'])
					echo '		<a href="', $scripturl, '?action=attachapprove;sa=all;mid=', $message['id'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['approve_all'], '</a>';

				echo '
								</span';
			}
			echo '
								<span>
									', $attachment['size'], ($attachment['is_image'] ? ', ' . $attachment['real_width'] . 'x' . $attachment['real_height'] . '<br>' . $txt['attach_viewed'] : ' - ' . $txt['attach_downloaded']) . ' ' . $attachment['downloads'] . ' ' . $txt['attach_times'] . '.
								</span>
							</div>
						</li>';
		}

		echo '
					</ul>';
	}
}



function old_message($message)
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

		// Show the message anchor and a "new" anchor if this message is new.
		if ($message['id'] != $context['first_message'])
			echo '
			<a id="msg', $message['id'], '"></a>', $message['first_new'] ? '<a id="new"></a>' : '';

		echo '
			<div class="', $message['approved'] ? 'normal_window' : 'approve_window', '">
				<div class="a_message">';

		// Show information about the poster of this message.
		echo '
					<div class="poster">
						<h3 class="floatleft">';

		// Show online and offline buttons?
		if (!empty($modSettings['onlineEnable']) && !$message['member']['is_guest'])
			echo '
							', $context['can_send_pm'] ? '<a href="' . $message['member']['online']['href'] . '" title="' . $message['member']['online']['label'] . '">' : '', !empty($message['member']['online']['is_online']) ? '<span class="icon-micro-new"></span>' : '' , $context['can_send_pm'] ? '</a>' : '';

		// Show a link to the member's profile.
		echo '
							', $message['member']['link'], '
						</h3>';

		if (!$message['member']['is_guest'])
		{
			// Show avatars, images, etc.?
			if (!empty($settings['show_user_images']) && empty($options['show_no_avatars']) && !empty($message['member']['avatar']['image']))
				echo '
						<a class="mavatar mobile floatright" href="', $scripturl, '?action=profile;u=', $message['member']['id'], '" style="background-image: url(', $message['member']['avatar']['href'], ');"></a>';
		}
		echo '
						<ul class="clear desktop reset smalltext" id="msg_', $message['id'], '_extra_info">';

		// Show the member's custom title, if they have one.
		if (!empty($message['member']['title']))
			echo '
							<li class="desktop title">', $message['member']['title'], '</li>';

		// Show the member's primary group (like 'Administrator') if they have one.
		if (!empty($message['member']['group']))
			echo '
							<li class="membergroup">', $message['member']['group'], '</li>';

		// Don't show these things for guests.
		if (!$message['member']['is_guest'])
		{
			// Show the post group if and only if they have no other group or the option is on, and they are in a post group.
			if ((empty($settings['hide_post_group']) || $message['member']['group'] == '') && $message['member']['post_group'] != '')
				echo '
							<li class="desktop postgroup">', $message['member']['post_group'], '</li>';
			echo '
							<li class="desktop stars">', $message['member']['group_stars'], '</li>';

			// Show avatars, images, etc.?
			if (!empty($settings['show_user_images']) && empty($options['show_no_avatars']) && !empty($message['member']['avatar']['image']))
				echo '
							<li>
								<a class="mavatar" href="', $scripturl, '?action=profile;u=', $message['member']['id'], '" style="background-image: url(', $message['member']['avatar']['href'], ');"></a>
							</li>';

			// Show how many posts they have made.
			if (!isset($context['disabled_fields']['posts']))
				echo '
							<li class="desktop postcount">', $txt['member_postcount'], ': ', $message['member']['posts'], '</li>';

			// Is karma display enabled?  Total or +/-?
			if ($modSettings['karmaMode'] == '1')
				echo '
							<li class="desktop karma"><span class="icon-heart"></span>', $message['member']['karma']['good'] - $message['member']['karma']['bad'], '</li>';
			elseif ($modSettings['karmaMode'] == '2')
				echo '
							<li class="desktop karma"><span class="icon-heart"></span> +', $message['member']['karma']['good'], '/-', $message['member']['karma']['bad'], '</li>';

			// Is this user allowed to modify this member's karma?
			if ($message['member']['karma']['allow'])
				echo '
							<li class="desktop karma_allow">
								<a href="', $scripturl, '?action=modifykarma;sa=applaud;uid=', $message['member']['id'], ';topic=', $context['current_topic'], '.' . $context['start'], ';m=', $message['id'], ';', $context['session_var'], '=', $context['session_id'], '"><span class="icon-thumbs-up circle green"></span></a>
								<a href="', $scripturl, '?action=modifykarma;sa=smite;uid=', $message['member']['id'], ';topic=', $context['current_topic'], '.', $context['start'], ';m=', $message['id'], ';', $context['session_var'], '=', $context['session_id'], '"><span class="icon-thumbs-down circle red"></span></a>
							</li>';

			// Show the member's gender icon?
			if (!empty($settings['show_gender']) && $message['member']['gender']['image'] != '' && !isset($context['disabled_fields']['gender']))
				echo '
							<li class="desktop gender">', $txt['gender'], ': ', $message['member']['gender']['image'], '</li>';

			// Show their personal text?
			if (!empty($settings['show_blurb']) && $message['member']['blurb'] != '')
				echo '
							<li class="desktop blurb">', $message['member']['blurb'], '</li>';

			// Any custom fields to show as icons?
			if (!empty($message['member']['custom_fields']))
			{
				$shown = false;
				foreach ($message['member']['custom_fields'] as $custom)
				{
					if ($custom['placement'] != 1 || empty($custom['value']))
						continue;
					if (empty($shown))
					{
						$shown = true;
						echo '
							<li class="im_icons desktop  flexlist">
								<ul>';
					}
					echo '
									<li>', $custom['value'], '</li>';
				}
				if ($shown)
					echo '
								</ul>
							</li>';
			}

			// This shows the popular messaging icons.
			if ($message['member']['has_messenger'] && $message['member']['can_view_profile'])
				echo '
							<li class="desktop im_icons flexlist">
								<ul class="reset flexlist">
									', !empty($message['member']['icq']['link']) ? '<li><a class="button_submit buts" href="' . $message['member']['icq']['href'] . '">ICQ</a></li>' : '', '
									', !empty($message['member']['msn']['link']) ? '<li><a class="button_submit buts" href="' . $message['member']['msn']['href'] . '">MSN</a></li>' : '', '
									', !empty($message['member']['aim']['link']) ? '<li><a class="button_submit buts" href="' . $message['member']['aim']['href'] . '">AIM</a></li>' : '', '
									', !empty($message['member']['yim']['link']) ? '<li><a class="button_submit buts" href="' . $message['member']['yim']['href'] . '">YIM</a></li>' : '', '
								</ul>
							</li>';

			// Show the profile, website, email address, and personal message buttons.
			if ($settings['show_profile_buttons'])
			{
				echo '
							<li class="desktop profile">
								<ul class="flexlist">';
				// Don't show the profile button if you're not allowed to view the profile.
				if ($message['member']['can_view_profile'])
					echo '
									<li><a href="', $message['member']['href'], '"><span class="icon-user-outline" title="' , $txt['view_profile'], '"></span></a></li>';

				// Don't show an icon if they haven't specified a website.
				if ($message['member']['website']['url'] != '' && !isset($context['disabled_fields']['website']))
					echo '
									<li><a href="', $message['member']['website']['url'], '" title="' . $message['member']['website']['title'] . '" target="_blank" class="new_win"><span class="icon-home-outline"></span></a></li>';

				// Don't show the email address if they want it hidden.
				if (in_array($message['member']['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')))
					echo '
									<li><a href="', $scripturl, '?action=emailuser;sa=email;msg=', $message['id'], '" rel="nofollow"><span class="icon-mail"></span></a></li>';

				// Since we know this person isn't a guest, you *can* message them.
				if ($context['can_send_pm'])
					echo '
									<li><a href="', $scripturl, '?action=pm;sa=send;u=', $message['member']['id'], '" title="', $message['member']['online']['is_online'] ? $txt['pm_online'] : $txt['pm_offline'], '"><span class="icon-comment"></span></a></li>';

				echo '
								</ul>
							</li>';
			}

			// Any custom fields for standard placement?
			if (!empty($message['member']['custom_fields']))
			{
				foreach ($message['member']['custom_fields'] as $custom)
					if (empty($custom['placement']) || empty($custom['value']))
						echo '
							<li class="desktop custom">', $custom['title'], ': ', $custom['value'], '</li>';
			}

			// Are we showing the warning status?
			if ($message['member']['can_see_warning'])
				echo '
							<li class="desktop warning">', $context['can_issue_warning'] ? '<a href="' . $scripturl . '?action=profile;area=issuewarning;u=' . $message['member']['id'] . '">' : '', '<span class="icon-warning-empty" title="', $txt['user_warn_' . $message['member']['warning_status']], '"></span>', $context['can_issue_warning'] ? '</a>' : '', '<span class="warn_', $message['member']['warning_status'], '">', $txt['warn_' . $message['member']['warning_status']], '</span></li>';
		}
		// Otherwise, show the guest's email.
		elseif (!empty($message['member']['email']) && in_array($message['member']['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')))
			echo '
							<li class="desktop email"><a href="', $scripturl, '?action=emailuser;sa=email;msg=', $message['id'], '" rel="nofollow">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . '" />' : $txt['email']), '</a></li>';

		// Done with the information about the poster... on to the post itself.
		echo '
						</ul>
					</div>
					<div class="postarea">
						<div class="flow_hidden">
							<h4 id="subject_', $message['id'], '" class="mobile headerpost">
								<a href="', $message['href'], '" rel="nofollow">', $message['subject'], ' <small class="floatright">' , $message['time'], '</small></a>
							</h4>
							<div class="keyinfo desktop">
								<div class="messageicon">
									<img src="', $settings['images_url'] .'/post/svg/'. $message['icon'] . '.svg" alt=""', $message['can_modify'] ? ' id="msg_icon_' . $message['id'] . '"' : '', ' />
								</div>
								<h5 id="subject_', $message['id'], '">
									<a href="', $message['href'], '" rel="nofollow">', $message['subject'], '</a>
								</h5>
							</div>
							<div class="keyinfotext smalltext desktop">&#171; <strong>', !empty($message['counter']) ? $txt['reply_noun'] . ' #' . $message['counter'] : '', ' ', $txt['on'], ':</strong> ', $message['time'], ' &#187;</div>
							<div class="" id="msg_', $message['id'], '_quick_mod"></div>
						</div>';

		// Ignoring this user? Hide the post.
		if ($ignoring)
			echo '
						<div id="msg_', $message['id'], '_ignored_prompt" class="information">
							', $txt['ignoring_user'], '
							<a href="#" id="msg_', $message['id'], '_ignored_link" style="display: none;">', $txt['show_ignore_user_post'], '</a>
						</div>';

		// Show the post itself, finally!
		echo '
						<div class="post">';

		if (!$message['approved'] && $message['member']['id'] != 0 && $message['member']['id'] == $context['user']['id'])
			echo '
							<div class="approve_post information">
								', $txt['post_awaiting_approval'], '
							</div>';
		echo '
							<div class="inner" id="msg_', $message['id'], '"', '>', $message['body'], '</div>
						</div>';


		// Assuming there are attachments...
		if (!empty($message['attachment']))
		{
			echo '
						<div id="msg_', $message['id'], '_footer" class="attachments smalltext" style="padding-bottom: 1rem; columns: ' , ($modSettings['attachmentThumbWidth'] + 20) , 'px;">';

			$last_approved_state = 1;
			foreach ($message['attachment'] as $attachment)
			{
				echo '
							<div class="a_attach">';
				// Show a special box for unapproved attachments...
				if ($attachment['is_approved'] != $last_approved_state)
				{
					$last_approved_state = 0;
					echo '
								<div class="information">
									', $txt['attach_awaiting_approve'];

					if ($context['can_approve'])
						echo ':	<a href="', $scripturl, '?action=attachapprove;sa=all;mid=', $message['id'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['approve_all'], '</a>';

					echo '
								</div';
				}

				if ($attachment['is_image'])
				{
					if ($attachment['thumbnail']['has_thumb'])
						echo '
								<a href="#att' , $attachment['id'] , '">
									<img id="thumb' , $attachment['id'] , '" class="a_block" src="', $attachment['thumbnail']['href'], '" alt="', $attachment['name'], '" />
								</a>
								<div id="att' , $attachment['id'] , '" class="modal">
									<a href="#thumb' , $attachment['id'] , '"><span class="close">&times;</span></a>
									<a href="' . $attachment['href'] . ';image" target="_blank"><span class="ddload icon-picture"></span></a>
									<a href="' . $attachment['href'] . '"><span class="dsload icon-download-outline"></span></a>
									<img src="', $attachment['href'], ';image" class="modal-content" alt="*">
									<div class="caption">' , $attachment['name'], '</div>
								</div>
								';
					else
						echo '
								<img  class="a_block" src="' . $attachment['href'] . ';image" alt="" width="' . $attachment['width'] . '" height="' . $attachment['height'] . '"/>';
				}
				else
					echo '
								<a href="' . $attachment['href'] . '"><span class="a_block a_filetype">' , substr($attachment['name'],strlen($attachment['name'])-3,3) , ' <span class="icon-download-outline transient"></span></span>
								</a>';
				echo '
								<a class="a_block a_name" href="' . $attachment['href'] . '">' . $attachment['name'] . '</a>';

				if (!$attachment['is_approved'] && $context['can_approve'])
					echo '
								<a  class="a_block" href="', $scripturl, '?action=attachapprove;sa=approve;aid=', $attachment['id'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['approve'], '</a>]&nbsp;|&nbsp;[<a href="', $scripturl, '?action=attachapprove;sa=reject;aid=', $attachment['id'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['delete'], '</a>';

				echo '
								<div>', $attachment['size'], ($attachment['is_image'] ? ', ' . $attachment['real_width'] . 'x' . $attachment['real_height'] . ' - ' . $txt['attach_viewed'] : ' - ' . $txt['attach_downloaded']) . ' ' . $attachment['downloads'] . ' ' . $txt['attach_times'] . '.</div>
							</div>';
			}

			echo '
						</div>';
		}

		// If this is the first post, (#0) just say when it was posted - otherwise give the reply #.
		if ($message['can_approve'] || $context['can_reply'] || $message['can_modify'] || $message['can_remove'] || $context['can_split'] || $context['can_restore_msg'])
			echo '
							<ul class="reset smalltext quickbuttons">';

		// Maybe we can approve it, maybe we should?
		if ($message['can_approve'])
			echo '
								<li class="approve_button button_submit buts">
									<a href="', $scripturl, '?action=moderate;area=postmod;sa=approve;topic=', $context['current_topic'], '.', $context['start'], ';msg=', $message['id'], ';', $context['session_var'], '=', $context['session_id'], '">
										', $txt['approve'], '
									</a>
								</li>';

		// Can they reply? Have they turned on quick reply?
		if ($context['can_quote'] && !empty($options['display_quick_reply']))
			echo '
								<li class="quote_button button_submit buts"><a href="', $scripturl, '?action=post;quote=', $message['id'], ';topic=', $context['current_topic'], '.', $context['start'], ';last_msg=', $context['topic_last_message'], '" onclick="return oQuickReply.quote(', $message['id'], ');">', $txt['quote'], '</a></li>';

		// So... quick reply is off, but they *can* reply?
		elseif ($context['can_quote'])
			echo '
								<li class="quote_button button_submit buts"><a href="', $scripturl, '?action=post;quote=', $message['id'], ';topic=', $context['current_topic'], '.', $context['start'], ';last_msg=', $context['topic_last_message'], '">', $txt['quote'], '</a></li>';

		// Can the user modify the contents of this post?
		if ($message['can_modify'])
			echo '
								<li class="modify_button  button_submit buts"><a href="', $scripturl, '?action=post;msg=', $message['id'], ';topic=', $context['current_topic'], '.', $context['start'], '">', $txt['modify'], '</a></li>';

		// How about... even... remove it entirely?!
		if ($message['can_remove'])
			echo '
								<li class="remove_button button_submit buts"><a href="', $scripturl, '?action=deletemsg;topic=', $context['current_topic'], '.', $context['start'], ';msg=', $message['id'], ';', $context['session_var'], '=', $context['session_id'], '" onclick="return confirm(\'', $txt['remove_message'], '?\');">', $txt['remove'], '</a></li>';

		// What about splitting it off the rest of the topic?
		if ($context['can_split'] && !empty($context['real_num_replies']))
			echo '
								<li class="split_button button_submit buts"><a href="', $scripturl, '?action=splittopics;topic=', $context['current_topic'], '.0;at=', $message['id'], '">', $txt['split'], '</a></li>';

		// Can we restore topics?
		if ($context['can_restore_msg'])
			echo '
								<li class="restore_button button_submit buts"><a href="', $scripturl, '?action=restoretopic;msgs=', $message['id'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['restore_message'], '</a></li>';

		// Show a checkbox for quick moderation?
		if (!empty($options['display_quick_mod']) && $options['display_quick_mod'] == 1 && $message['can_remove'])
			echo '
								<li class="inline_mod_check floatright" style="display: none;" id="in_topic_mod_check_', $message['id'], '"></li>';

		// Can the user modify the contents of this post?  Show the modify inline image.
		if ($message['can_modify'])
			echo '
								<li class="button_submit buts">
									<span class="icon-edit iconbig" id="modify_button_', $message['id'], '" style="cursor: pointer; " onclick="oQuickModify.modifyMsg(\'', $message['id'], '\')"></span>
								</li>';

		if ($message['can_approve'] || $context['can_reply'] || $message['can_modify'] || $message['can_remove'] || $context['can_split'] || $context['can_restore_msg'])
			echo '
							</ul>';
		echo '
					</div>
				</div>
				<div>';

		if ($settings['show_modify'] && !empty($message['modified']['name']))
			echo '
					<div class="smalltext modified information" id="modified_', $message['id'], '">
						<span class=""></span> <em>', $txt['last_edit'], ': ', $message['modified']['time'], ' ', $txt['by'], ' ', $message['modified']['name'], '</em>
					</div>';

		echo '
					<div class="smalltext reportlinks information">';

		// Maybe they want to report this post to the moderator(s)?
		if ($context['can_report_moderator'])
			echo '
						<a href="', $scripturl, '?action=reporttm;topic=', $context['current_topic'], '.', $message['counter'], ';msg=', $message['id'], '">', $txt['report_to_mod'], '</a> &nbsp;';

		// Can we issue a warning because of this post?  Remember, we can't give guests warnings.
		if ($context['can_issue_warning'] && !$message['is_message_author'] && !$message['member']['is_guest'])
			echo '
						<a href="', $scripturl, '?action=profile;area=issuewarning;u=', $message['member']['id'], ';msg=', $message['id'], '">
							<img src="', $settings['images_url'], '/warn.gif" alt="', $txt['issue_warning_post'], '" title="', $txt['issue_warning_post'], '" />
						</a>';
		echo '
						<span class="icon-flow-split"></span>';

		// Show the IP to this user for this post - because you can moderate?
		if ($context['can_moderate_forum'] && !empty($message['member']['ip']))
			echo '
						<a href="', $scripturl, '?action=', !empty($message['member']['is_guest']) ? 'trackip' : 'profile;area=tracking;sa=ip;u=' . $message['member']['id'], ';searchip=', $message['member']['ip'], '">', $message['member']['ip'], '</a> <a href="', $scripturl, '?action=helpadmin;help=see_admin_ip" onclick="return reqWin(this.href);" class="help">(?)</a>';
		// Or, should we show it because this is you?
		elseif ($message['can_see_ip'])
			echo '
						<a href="', $scripturl, '?action=helpadmin;help=see_member_ip" onclick="return reqWin(this.href);" class="help">', $message['member']['ip'], '</a>';
		// Okay, are you at least logged in?  Then we can show something about why IPs are logged...
		elseif (!$context['user']['is_guest'])
			echo '
						<a href="', $scripturl, '?action=helpadmin;help=see_member_ip" onclick="return reqWin(this.href);" class="help">', $txt['logged'], '</a>';
		// Otherwise, you see NOTHING!
		else
			echo '
						', $txt['logged'];

		echo '
					</div>';

		// Are there any custom profile fields for above the signature?
		if (!empty($message['member']['custom_fields']))
		{
			$shown = false;
			foreach ($message['member']['custom_fields'] as $custom)
			{
				if ($custom['placement'] != 2 || empty($custom['value']))
					continue;
				if (empty($shown))
				{
					$shown = true;
					echo '
					<div class="custom_fields_above_signature">
						<ul class="reset nolist">';
				}
				echo '
							<li>', $custom['value'], '</li>';
			}
			if ($shown)
				echo '
						</ul>
					</div>';
		}

		// Show the member's signature?
		if (!empty($message['member']['signature']) && empty($options['show_no_signatures']) && $context['signature_enabled'])
			echo '
					<div class="signature" id="msg_', $message['id'], '_signature"><div style="">', $message['member']['signature'], '</div></div>';

		echo '
				</div>
			</div>
		</div>';
}

?>
