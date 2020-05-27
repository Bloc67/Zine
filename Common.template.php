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

?>
