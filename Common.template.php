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
				<span class="board_icon sub"></span>';

	echo		$topic['first_post']['link'], '
			</li>
			<li class="b_description"> </li>
			<li class="b_moderators"></li>
			<li class="b_stats">', $topic['views'], ' ' , $txt['views'] , '</li>
			<li class="b_last">
				' , $topic['last_post']['member']['link'], ' ' , $txt['in'], ' ', $topic['last_post']['link'], ' <span class="b_time">', $topic['last_post']['time'], '</span>
			</li>
			<li class="b_sub has_items">
				<span>', $hot, $topic['replies'], ' ' , $txt['replies'], '</span>
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

?>
