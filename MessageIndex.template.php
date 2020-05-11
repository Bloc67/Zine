<?php

/* @ Rebus89 theme */
/*	@ Blocthemes 2020	*/
/*	@	SMF 2.0.x	*/

/* function to add submenu on this page */
function template_section_menu() 
{ 
	global $txt, $settings;

	echo '
	<menu class="section_menu">
		<ul class="reset">
			<li data-section="#a_messageindex" class="active">' , $txt['topics'] , '</li>
			<li data-section="#a_maside">' , $txt['parent_boards'] , '</li>
		</ul>
	</menu>'; 
}

function template_board_info($hide_description = false)
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt, $board_info;

	if(!empty($context['description']) && !$hide_description)
		echo $context['description'];

	if(!$hide_description)
		echo '
		<dl>
			<dt>' , $txt['a_category'] , '</dt>
			<dd>' , $board_info['cat']['name'] , '</dd>
			<dt>' , $txt['topics'] , '</dt>
			<dd>' , $board_info['num_topics'] , '</dd>
		</dl>';

	if (!empty($settings['display_who_viewing']))
	{
		echo '
		<div class="information2' , $hide_description ? ' noborder' : '' , '">';
		if ($settings['display_who_viewing'] == 1)
			echo count($context['view_members']), ' ', count($context['view_members']) === 1 ? $txt['who_member'] : $txt['members'];
		else
			echo empty($context['view_members_list']) ? '0 ' . $txt['members'] : implode(', ', $context['view_members_list']) . ((empty($context['view_num_hidden']) or $context['can_moderate_forum']) ? '' : ' (+ ' . $context['view_num_hidden'] . ' ' . $txt['hidden'] . ')');
		echo $txt['who_and'], $context['view_num_guests'], ' ', $context['view_num_guests'] == 1 ? $txt['guest'] : $txt['guests'], $txt['who_viewing_board'], '
		</div>';
	}

	// If this person can approve items and we have some awaiting approval tell them.
	if (!empty($context['unapproved_posts_message']))
	{
		echo '
		<div class="information">
			<span class="alert">!</span> ', $context['unapproved_posts_message'], '
		</div>';
	}
}

function template_main()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	$ids = array();
	// get the avatars
	if (!empty($context['boards']) && (!empty($options['show_children']) || $context['start'] == 0))
	{
		foreach ($category['boards'] as $board)
		{
			$ids[$board['last_post']['member']['id']] = $board['last_post']['member']['id'];
		}
	}
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
	<h3>' , $context['name'] , ' - ' , $txt['parent_boards'] , '</h3>
	<div class="a_messageindex_info">
		', template_board_info(true), '
	</div>';

	if (!empty($context['boards']) && (!empty($options['show_children']) || $context['start'] == 0))
	{
		echo '
	<div class="a_boards">
		<section class="forum_category">';

		foreach ($context['boards'] as $board)
		{
			a_boardindex($board);
		}
		echo '
		</section>
	</div>';
	}

	
	echo '
</div>
<article id="a_messageindex" class="m_sections active">
	<h3><span>' , $context['name'] , '</span><span class="', !$context['no_topic_listing'] && !empty($context['topics']) ? 'pagelinks">'. $context['page_index'].'</span>' : '"> </span>' , '</h3>
	<div class="a_messageindex_info">
		', template_board_info(false), '
	</div>
	<div id="a_topics">';

	// Create the button set...
	$normal_buttons = array(
		'new_topic' => array('test' => 'can_post_new', 'text' => 'new_topic', 'image' => 'new_topic.gif', 'lang' => true, 'url' => $scripturl . '?action=post;board=' . $context['current_board'] . '.0', 'active' => true),
		'post_poll' => array('test' => 'can_post_poll', 'text' => 'new_poll', 'image' => 'new_poll.gif', 'lang' => true, 'url' => $scripturl . '?action=post;board=' . $context['current_board'] . '.0;poll'),
		'notify' => array('test' => 'can_mark_notify', 'text' => $context['is_marked_notify'] ? 'unnotify' : 'notify', 'image' => ($context['is_marked_notify'] ? 'un' : ''). 'notify.gif', 'lang' => true, 'custom' => 'onclick="return confirm(\'' . ($context['is_marked_notify'] ? $txt['notification_disable_board'] : $txt['notification_enable_board']) . '\');"', 'url' => $scripturl . '?action=notifyboard;sa=' . ($context['is_marked_notify'] ? 'off' : 'on') . ';board=' . $context['current_board'] . '.' . $context['start'] . ';' . $context['session_var'] . '=' . $context['session_id']),
		'markasread' => array('text' => 'mark_read_short', 'image' => 'markread.gif', 'lang' => true, 'url' => $scripturl . '?action=markasread;sa=board;board=' . $context['current_board'] . '.0;' . $context['session_var'] . '=' . $context['session_id']),
	);

	// They can only mark read if they are logged in and it's enabled!
	if (!$context['user']['is_logged'] || !$settings['show_mark_read'])
		unset($normal_buttons['markasread']);

	// Allow adding new buttons easily.
	call_integration_hook('integrate_messageindex_buttons', array(&$normal_buttons));

	if (!$context['no_topic_listing'])
	{
		echo '
		<menu class="pagesection">
			' , !empty($modSettings['topbottomEnable']) && !empty($context['topics']) ? $context['menu_separator'] . ' <a id="a_go_down" href="#bot">' . $txt['go_down'] . '</a>' : '', '
			', template_button_strip($normal_buttons, 'right');

		// Are there actually any topics to show?
		if (!empty($context['topics']))
				echo '
			<select id="a_sorting" onchange="document.location.href=this.value">
				<option value="', $scripturl, '?board=', $context['current_board'], '.', $context['start'], ';sort=subject', $context['sort_by'] == 'subject' && $context['sort_direction'] == 'up' ? ';desc' : '', '"' , $context['sort_by'] == 'subject' ? ' selected' : '' , '>', $txt['subject'], $context['sort_by'] == 'subject' ? ' [' . $context['sort_direction'] . ']' : '', '</option>
				<option value="', $scripturl, '?board=', $context['current_board'], '.', $context['start'], ';sort=starter', $context['sort_by'] == 'starter' && $context['sort_direction'] == 'up' ? ';desc' : '', '"' , $context['sort_by'] == 'starter' ? ' selected' : '' , '>', $txt['started_by'], $context['sort_by'] == 'starter' ? ' [' . $context['sort_direction'] . ']' : '', '</option>
				<option value="', $scripturl, '?board=', $context['current_board'], '.', $context['start'], ';sort=replies', $context['sort_by'] == 'replies' && $context['sort_direction'] == 'up' ? ';desc' : '', '"' , $context['sort_by'] == 'replies' ? ' selected' : '' , '>', $txt['replies'], $context['sort_by'] == 'replies' ? ' [' . $context['sort_direction'] . ']' : '', '</option>
				<option value="', $scripturl, '?board=', $context['current_board'], '.', $context['start'], ';sort=views', $context['sort_by'] == 'views' && $context['sort_direction'] == 'up' ? ';desc' : '', '"' , $context['sort_by'] == 'views' ? ' selected' : '' , '>', $txt['views'], $context['sort_by'] == 'views' ? ' [' . $context['sort_direction'] . ']' : '', '</option>
				<option value="', $scripturl, '?board=', $context['current_board'], '.', $context['start'], ';sort=last_post', $context['sort_by'] == 'last_post' && $context['sort_direction'] == 'up' ? ';desc' : '', '" ' , $context['sort_by'] == 'last_post' ? ' selected' : '' , '>', $txt['last_post'], $context['sort_by'] == 'last_post' ? ' [' . $context['sort_direction'] . ']' : '', '</option>
			</select>';

		echo '
		</menu>';

		// If Quick Moderation is enabled start the form.
		if (!empty($context['can_quick_mod']) && $options['display_quick_mod'] > 0 && !empty($context['topics']))
			echo '
		<form action="', $scripturl, '?action=quickmod;board=', $context['current_board'], '.', $context['start'], '" method="post" accept-charset="', $context['character_set'], '" class="clear" name="quickModForm" id="quickModForm">';

		echo '
			<div id="messageindex">';

		// Are there actually any topics to show?
		if (!empty($context['topics']))
		{
			// Show a "select all" box for quick moderation?
			if (!empty($context['can_quick_mod']) && $options['display_quick_mod'] == 1)
				echo '
				<input type="checkbox" onclick="invertAll(this, this.form, \'topics[]\');" class="input_check floatright" />';
		}
		// No topics.... just say, "sorry bub".
		else
			echo '
				<h3><strong>', $txt['msg_alert_none'], '</strong></h3>';

		echo '
				<section class="forum_category">';

		foreach ($context['topics'] as $topic)
		{
			a_topic($topic);
		}
		echo '
				</section>';
		
		if (!empty($context['can_quick_mod']) && $options['display_quick_mod'] == 1 && !empty($context['topics']))
		{
			echo '
				<div class="moderation_bar">
					<select class="qaction" name="qaction"', $context['can_move'] ? ' onchange="this.form.moveItTo.disabled = (this.options[this.selectedIndex].value != \'move\');"' : '', '>
						<option value="">--------</option>', $context['can_remove'] ? '
						<option value="remove">' . $txt['quick_mod_remove'] . '</option>' : '', $context['can_lock'] ? '
						<option value="lock">' . $txt['quick_mod_lock'] . '</option>' : '', $context['can_sticky'] ? '
						<option value="sticky">' . $txt['quick_mod_sticky'] . '</option>' : '', $context['can_move'] ? '
						<option value="move">' . $txt['quick_mod_move'] . ': </option>' : '', $context['can_merge'] ? '
						<option value="merge">' . $txt['quick_mod_merge'] . '</option>' : '', $context['can_restore'] ? '
						<option value="restore">' . $txt['quick_mod_restore'] . '</option>' : '', $context['can_approve'] ? '
						<option value="approve">' . $txt['quick_mod_approve'] . '</option>' : '', $context['user']['is_logged'] ? '
						<option value="markread">' . $txt['quick_mod_markread'] . '</option>' : '', '
					</select>';

			// Show a list of boards they can move the topic to.
			if ($context['can_move'])
			{
					echo '
					<select class="qaction" id="moveItTo" name="move_to" disabled="disabled">';

					foreach ($context['move_to_boards'] as $category)
					{
						echo '
						<optgroup label="', $category['name'], '">';
						foreach ($category['boards'] as $board)
							echo '	
							<option value="', $board['id'], '"', $board['selected'] ? ' selected="selected"' : '', '>', $board['child_level'] > 0 ? str_repeat('==', $board['child_level'] - 1) . '=&gt;' : '', ' ', $board['name'], '</option>';
						echo '
						</optgroup>';
					}
					echo '
					</select>';
			}

			echo '
					<input type="submit" value="', $txt['quick_mod_go'], '" onclick="return document.forms.quickModForm.qaction.value != \'\' &amp;&amp; confirm(\'', $txt['quickmod_confirm'], '\');" class="button_submit qaction" />
				</div>';
		}
		echo '
				<a id="bot"></a>
			</div>';

		// Finish off the form - again.
		if (!empty($context['can_quick_mod']) && $options['display_quick_mod'] > 0 && !empty($context['topics']))
			echo '
			<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '" />
		</form>';

		echo '
		<div class="pagesection" style="margin-top: 5px;">
		' , !empty($modSettings['topbottomEnable']) && !empty($context['topics']) ? '<a href="#a_messageindex" id="a_go_up">' . $txt['go_up'] . '</a>' : '', template_button_strip($normal_buttons, 'right'), '
		', !empty($context['topics']) ? '<div class="pagelinks">'. $context['page_index']. '</div>' : '' , '
		</div>';
	}

	echo '
		<div id="topic_icons">
			<div class="description">
				<p id="message_index_jump_to">&nbsp;</p>

				<script type="text/javascript"><!-- // --><![CDATA[
					if (typeof(window.XMLHttpRequest) != "undefined")
						aJumpTo[aJumpTo.length] = new JumpTo({
							sContainerId: "message_index_jump_to",
							sJumpToTemplate: "<label class=\"smalltext\" for=\"%select_id%\">', $context['jump_to']['label'], ':<" + "/label> %dropdown_list%",
							iCurBoardId: ', $context['current_board'], ',
							iCurBoardChildLevel: ', $context['jump_to']['child_level'], ',
							sCurBoardName: "', $context['jump_to']['board_name'], '",
							sBoardChildLevelIndicator: "==",
							sBoardPrefix: "=> ",
							sCatSeparator: "-----------------------------",
							sCatPrefix: "",
							sGoButtonLabel: "', $txt['quick_mod_go'], '"
						});
				// ]]></script>
			</div>
		</div>
	</div>
</article>';

	// Javascript for inline editing.
	echo '
<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/topic.js"></script>
<script type="text/javascript"><!-- // --><![CDATA[

	// Hide certain bits during topic edit.
	hide_prefixes.push("lockicon", "stickyicon", "pages", "newicon");

	// Use it to detect when we\'ve stopped editing.
	document.onclick = modify_topic_click;

	var mouse_on_div;
	function modify_topic_click()
	{
		if (in_edit_mode == 1 && mouse_on_div == 0)
			modify_topic_save("', $context['session_id'], '", "', $context['session_var'], '");
	}

	function modify_topic_keypress(oEvent)
	{
		if (typeof(oEvent.keyCode) != "undefined" && oEvent.keyCode == 13)
		{
			modify_topic_save("', $context['session_id'], '", "', $context['session_var'], '");
			if (typeof(oEvent.preventDefault) == "undefined")
				oEvent.returnValue = false;
			else
				oEvent.preventDefault();
		}
	}

	// For templating, shown when an inline edit is made.
	function modify_topic_show_edit(subject)
	{
		// Just template the subject.
		setInnerHTML(cur_subject_div, \'<input type="text" name="subject" value="\' + subject + \'" size="60" style="width: 95%;" maxlength="80" onkeypress="modify_topic_keypress(event)" class="input_text" /><input type="hidden" name="topic" value="\' + cur_topic_id + \'" /><input type="hidden" name="msg" value="\' + cur_msg_id.substr(4) + \'" />\');
	}

	// And the reverse for hiding it.
	function modify_topic_hide_edit(subject)
	{
		// Re-template the subject!
		setInnerHTML(cur_subject_div, \'<a href="', $scripturl, '?topic=\' + cur_topic_id + \'.0">\' + subject + \'<\' +\'/a>\');
	}

// ]]></script>';
}

?>
