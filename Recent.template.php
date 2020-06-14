<?php

/*	@ Bloc 2019										*/
/*	@	SMF 2.0.x										*/

function template_main()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
	<div id="recent" class="main_section">
		<div class="cat_bar">
			<h3 class="catbg">
				<span class="ie6_header floatleft"><img src="', $settings['images_url'], '/post/xx.gif" alt="" class="icon" />',$txt['recent_posts'],'</span>
			</h3>
		</div>
		<div class="pagesection">
			<span>', $txt['pages'], ': ', $context['page_index'], '</span>
		</div>';

	foreach ($context['posts'] as $post)
	{
		echo '
			<div class="', $post['alternate'] == 0 ? 'windowbg' : 'windowbg2', ' core_posts">
				<span class="topslice"><span></span></span>
				<div class="content">
					<div class="counter">', $post['counter'], '</div>
					<div class="topic_details">
						<h5>', $post['board']['link'], ' / ', $post['link'], '</h5>
						<span class="smalltext">&#171;&nbsp;', $txt['last_post'], ' ', $txt['by'], ' <strong>', $post['poster']['link'], ' </strong> ', $txt['on'], '<em> ', $post['time'], '</em>&nbsp;&#187;</span>
					</div>
					<div class="list_posts">', $post['message'], '</div>
				</div>';

		if ($post['can_reply'] || $post['can_mark_notify'] || $post['can_delete'])
			echo '
				<div class="quickbuttons_wrap">
					<ul class="reset smalltext quickbuttons">';

		// If they *can* reply?
		if ($post['can_reply'])
			echo '
						<li class="reply_button"><a href="', $scripturl, '?action=post;topic=', $post['topic'], '.', $post['start'], '"><span>', $txt['reply'], '</span></a></li>';

		// If they *can* quote?
		if ($post['can_quote'])
			echo '
						<li class="quote_button"><a href="', $scripturl, '?action=post;topic=', $post['topic'], '.', $post['start'], ';quote=', $post['id'], '"><span>', $txt['quote'], '</span></a></li>';

		// Can we request notification of topics?
		if ($post['can_mark_notify'])
			echo '
						<li class="notify_button"><a href="', $scripturl, '?action=notify;topic=', $post['topic'], '.', $post['start'], '"><span>', $txt['notify'], '</span></a></li>';

		// How about... even... remove it entirely?!
		if ($post['can_delete'])
			echo '
						<li class="remove_button"><a href="', $scripturl, '?action=deletemsg;msg=', $post['id'], ';topic=', $post['topic'], ';recent;', $context['session_var'], '=', $context['session_id'], '" onclick="return confirm(\'', $txt['remove_message'], '?\');"><span>', $txt['remove'], '</span></a></li>';

		if ($post['can_reply'] || $post['can_mark_notify'] || $post['can_delete'])
			echo '
					</ul>
				</div>';

		echo '
				<span class="botslice clear"><span></span></span>
			</div>';

	}

	echo '
		<div class="pagesection">
			<span>', $txt['pages'], ': ', $context['page_index'], '</span>
		</div>
	</div>';
}

function template_unread()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	$showCheckboxes = !empty($options['display_quick_mod']) && $options['display_quick_mod'] == 1 && $settings['show_mark_read'];

	$ids = array();

	// get the avatars
	if(!empty($context['topics']))
	{
		foreach ($context['topics'] as $topic)
		{
			$ids[$topic['first_post']['member']['id']] = $topic['first_post']['member']['id'];
		}
	}
	get_avatars($ids);

	echo '
<div id="a_maside" class="m_sections" style="display: none;">
	<h3>' , $txt['a_maside'] , '</h3>
	<div class="a_messageindex_info">
		<ul class="reset category_list">
			<li data-section="#category_',$category['id'],'">', $category['name'], '</li>
		</ul>
	</div>';

	echo '
</div>
<article id="a_messageindex" class="m_sections active">
	<h3><span>' , $txt['unread_topics_visit'] ,' </span><span class="', !empty($context['topics']) ? 'pagelinks">'. $context['page_index'].'</span>' : '"> </span>' , '</h3>
	<div class="a_categories">
		<ul class="reset category_list">
			<li><a href="', $scripturl, '?action=unread">' , !isset($_GET['all']) ? '<strong>' : '' ,  $txt['unread_topics_visit'] , !isset($_GET['all']) ? '</strong>' : '' , '</a></li>
			<li><a href="', $scripturl, '?action=unread;all">' , isset($_GET['all']) ? '<strong>' : '' ,  $txt['unread_topics_all'] , isset($_GET['all']) ? '</strong>' : '' , '</a></li>
			<li><a href="', $scripturl, '?action=unreadreplies">' ,  $txt['unread_replies'] , '</a></li>
		</ul>
	</div>
	<div id="a_topics">';

	if ($settings['show_mark_read'])
	{
		// Generate the button strip.
		$mark_read = array(
			'markread' => array('text' => !empty($context['no_board_limits']) ? 'mark_as_read' : 'mark_read_short', 'image' => 'markread.gif', 'lang' => true, 'url' => $scripturl . '?action=markasread;sa=' . (!empty($context['no_board_limits']) ? 'all' : 'board' . $context['querystring_board_limits']) . ';' . $context['session_var'] . '=' . $context['session_id']),
		);

		if ($showCheckboxes)
			$mark_read['markselectread'] = array(
				'text' => 'quick_mod_markread',
				'image' => 'markselectedread.gif',
				'lang' => true,
				'url' => 'javascript:document.quickModForm.submit();',
			);
	}

	if (!empty($context['topics']))
	{
		echo '
		<menu class="pagesection">
			' , !empty($modSettings['topbottomEnable']) && !empty($context['topics']) ? $context['menu_separator'] . ' <a id="a_go_down" href="#bot">' . $txt['go_down'] . '</a>' : '', '
			', template_button_strip($mark_read, 'right'), '
		</menu>';

		if ($showCheckboxes)
			echo '
		<form action="', $scripturl, '?action=quickmod" method="post" accept-charset="', $context['character_set'], '" name="quickModForm" id="quickModForm" style="margin: 0;">
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="hidden" name="qaction" value="markread" />
			<input type="hidden" name="redirect_url" value="action=unread', (!empty($context['showing_all_topics']) ? ';all' : ''), $context['querystring_board_limits'], '" />';

		echo '
			<div id="messageindex">';

		// Are there actually any topics to show?
		if ($showCheckboxes)
		{
			// Show a "select all" box for quick moderation?
			if (!empty($context['can_quick_mod']) && $options['display_quick_mod'] == 1)
				echo '
				<input type="checkbox" onclick="invertAll(this, this.form, \'topics[]\');" class="input_check floatright" />';
		}
	
		echo '
				<section class="forumstyle">';

		foreach ($context['topics'] as $topic)
		{
			a_topic($topic, $showCheckboxes, true);
		}
		echo '
				</section>
				<a id="bot"></a>
			</div>';

		// Finish off the form - again.
	if ($showCheckboxes)
		echo '
		</form>';

		if (!empty($context['topics']) && !$context['showing_all_topics'])
			$mark_read['readall'] = array('text' => 'unread_topics_all', 'image' => 'markreadall.gif', 'lang' => true, 'url' => $scripturl . '?action=unread;all' . $context['querystring_board_limits'], 'active' => true);

		echo '
		<menu class="pagesection">
			' , 	!empty($mark_read) ? template_button_strip($mark_read, 'right') : '' , '
		</menu>';
	}
	echo '
	</div>
	<div class="pagesection" id="pageindex_below">
		', !empty($context['topics']) ? '<div class="pagelinks">'. $context['page_index']. '</div>' : '' , '
	</div>
</article>';

}

function template_replies()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	$ids = array();

	// get the avatars
	if(!empty($context['topics']))
	{
		foreach ($context['topics'] as $topic)
		{
			$ids[$topic['first_post']['member']['id']] = $topic['first_post']['member']['id'];
		}
	}
	get_avatars($ids);

	echo '
<div id="a_maside" class="m_sections" style="display: none;">
	<h3>' , $txt['unread_replies'] , '</h3>
	<div class="a_messageindex_info">
		', template_board_info(true), '
	</div>';

	echo '
</div>
<article id="a_messageindex" class="m_sections active">
	<h3><span></span><span class="', !empty($context['topics']) ? 'pagelinks">'. $context['page_index'].'</span>' : '"> </span>' , '</h3>
	<div class="a_messageindex_info">
		', template_board_info(false), '
	</div>
	<div id="a_topics">';

	if ($settings['show_mark_read'])
	{
		// Generate the button strip.
		$mark_read = array(
			'markread' => array('text' => !empty($context['no_board_limits']) ? 'mark_as_read' : 'mark_read_short', 'image' => 'markread.gif', 'lang' => true, 'url' => $scripturl . '?action=markasread;sa=' . (!empty($context['no_board_limits']) ? 'all' : 'board' . $context['querystring_board_limits']) . ';' . $context['session_var'] . '=' . $context['session_id']),
		);

		if ($showCheckboxes)
			$mark_read['markselectread'] = array(
				'text' => 'quick_mod_markread',
				'image' => 'markselectedread.gif',
				'lang' => true,
				'url' => 'javascript:document.quickModForm.submit();',
			);
	}

	if (!$context['no_topic_listing'])
	{
		echo '
		<menu class="pagesection">
			' , !empty($modSettings['topbottomEnable']) && !empty($context['topics']) ? $context['menu_separator'] . ' <a id="a_go_down" href="#bot">' . $txt['go_down'] . '</a>' : '', '
			', template_button_strip($mark_read, 'right'), '
		</menu>';

		$showCheckboxes = !empty($options['display_quick_mod']) && $options['display_quick_mod'] == 1 && $settings['show_mark_read'];

		if ($showCheckboxes)
			echo '
		<form action="', $scripturl, '?action=quickmod" method="post" accept-charset="', $context['character_set'], '" name="quickModForm" id="quickModForm" style="margin: 0;">
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="hidden" name="qaction" value="markread" />
			<input type="hidden" name="redirect_url" value="action=unread', (!empty($context['showing_all_topics']) ? ';all' : ''), $context['querystring_board_limits'], '" />';

		echo '
			<div id="messageindex">';

		// Are there actually any topics to show?
		if ($showCheckboxes)
		{
			// Show a "select all" box for quick moderation?
			if (!empty($context['can_quick_mod']) && $options['display_quick_mod'] == 1)
				echo '
				<input type="checkbox" onclick="invertAll(this, this.form, \'topics[]\');" class="input_check floatright" />';
		}
		// No topics.... just say, "sorry bub".
		else
			echo '
				<h3 class="information2"><strong>', $txt['msg_alert_none'], '</strong></h3>';

		echo '
				<section class="forumstyle">';

		foreach ($context['topics'] as $topic)
		{
			a_topic($topic);
		}
		echo '
				</section>
				<a id="bot"></a>
			</div>';

		// Finish off the form - again.
	if ($showCheckboxes)
		echo '
		</form>';

		if (!empty($context['topics']) && !$context['showing_all_topics'])
			$mark_read['readall'] = array('text' => 'unread_topics_all', 'image' => 'markreadall.gif', 'lang' => true, 'url' => $scripturl . '?action=unread;all' . $context['querystring_board_limits'], 'active' => true);

		echo '
		<menu class="pagesection">
			' , 	!empty($mark_read) ? template_button_strip($normal_buttons, 'right') : '' , '
			<p id="message_index_jump_to">&nbsp;</p>
		</menu>';
	}
	echo '
	</div>
	<div class="pagesection" id="pageindex_below">
		', !empty($context['topics']) ? '<div class="pagelinks">'. $context['page_index']. '</div>' : '' , '
	</div>
</article>';


	
}

?>