<?php

/* @ Rebus89 theme */
/*	@ Blocthemes 2020	*/
/*	@	SMF 2.0.x	*/

/* function to add submenu on this page */
function template_section_menu() 
{ 
	global $txt, $settings, $context;

	// check to see if we want subpage or not
	$context['a_active'] = 'messages';
	if(isset($_GET['ispoll'])) 
		$context['a_active'] = 'poll';
	if(isset($_GET['isevent'])) 
		$context['a_active'] = 'event';

	echo '
	<menu class="section_menu">
		<ul class="reset">
			<li data-section="#a_display"' , $context['a_active']=='messages' ? ' class="active"' : '' , '>' , $txt['a_messages'] , '</li>
			' , 	$context['is_poll'] ? '<li data-section="#a_poll"' . ($context['a_active']=='poll' ? ' class="active"' : '') . '>' . $txt['a_poll'] . '</li>' : '' ,'
			' , 	!empty($context['linked_calendar_events']) ? '<li data-section="#a_linked_events"' . ($context['a_active']=='event' ? ' class="active"' : '') . '>' . $txt['a_calendar_linked_events'] . '</li>' : '' ,'
		</ul>
	</menu>'; 
}

function template_topic_info($hide = false) 
{
	global $txt, $context;

	echo '
		<div>', $txt['read'], ' ', $context['num_views'], ' ', $txt['times'], '</div>';

	if(!$hide)
		echo '
		<p>
			' , $context['is_sticky'] ? '<span class="icon-pin-outline red"></span><small>'. $txt['sticky_topic'].'</small>' : '' , '
			' , $context['is_locked'] ? '<span class="icon-lock blue"></span><small>'. $txt['locked_topic'].'</small>' : '' , '
			' , $context['is_locked'] ? '<span class="icon-lock blue"></span><small>'. $txt['locked_topic'].'</small>' : '' , '
		</p>';
	
	if (!empty($settings['display_who_viewing']))
	{
		echo '
		<div class="information2">';
		
		if ($settings['display_who_viewing'] == 1)
			echo count($context['view_members']), ' ', count($context['view_members']) === 1 ? $txt['who_member'] : $txt['members'];
		else
			echo empty($context['view_members_list']) ? '0 ' . $txt['members'] : implode(', ', $context['view_members_list']) . ((empty($context['view_num_hidden']) or $context['can_moderate_forum']) ? '' : ' (+ ' . $context['view_num_hidden'] . ' ' . $txt['hidden'] . ')');
		
		echo $txt['who_and'], $context['view_num_guests'], ' ', $context['view_num_guests'] == 1 ? $txt['guest'] : $txt['guests'], $txt['who_viewing_board'], '
		</div>';
	}

	if ($context['report_sent'] && !$hide)
		echo '
		<div class="information2" id="profile_success">', $txt['report_sent'], '</div>';
}

function template_main()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// Does this topic have some events linked to it?
	if (!empty($context['linked_calendar_events']))
	{
		echo '
	<div id="a_linked_events" class="m_sections' , $context['a_active']=='event' ? ' active"' : '" style="display: none;"', '>
		<h3>', $txt['calendar_linked_events'], '</h3>
		<div class="a_display_info">
			', template_topic_info(true), '
		</div>
		<dl>';

		foreach ($context['linked_calendar_events'] as $event)
			echo '
			<dt>', $event['title'],' ', ($event['can_edit'] ? '<a href="' . $event['modify_href'] . '"><span class="icon-cog-outline" title="' . $txt['modify'] . '"></span></a> ' : ''), '</dt>
			<dd>', $event['start_date'], ($event['start_date'] != $event['end_date'] ? ' - ' . $event['end_date'] : ''), '</dd>';

		echo '
		</dl>	
	</div>';
	}

	// Is this topic also a poll?
	if ($context['is_poll'])
	{
		echo '
	<div id="a_poll" class="m_sections' , $context['a_active']=='poll' ? ' active"' : '" style="display: none;"', '>
		<h3><span class="icon-chart-bar grey"></span> ', $context['poll']['question'], $context['poll']['is_locked'] ? ' <span class="icon-lock"></span>' : '' , '</h3>
		<div class="a_display_info">
			', template_topic_info(true), '
		</div>';

		// Are they not allowed to vote but allowed to view the options?
		if ($context['poll']['show_results'] || !$context['allow_vote'])
		{
			echo '
		<dl class="options">';

			// Show each option with its corresponding percentage bar.
			foreach ($context['poll']['options'] as $option)
			{
				echo '
			<dt class="', $option['voted_this'] ? ' voted' : '', '"><span class="text">', $option['option'], '</span></dt>
			<dd class="', $option['voted_this'] ? ' voted' : '', '">';

				if ($context['allow_poll_view'])
					echo '
				<span class="barchart"><span style="width: ', $option['percent'] , '%;"></span></span>
				<span class="percentage">', $option['votes'], ' (', $option['percent'], '%)</span>';
				else
					echo '
				<span></span><span></span>';

				echo '
			</dd>';
			}

			echo '
		</dl>';

			if ($context['allow_poll_view'])
				echo '
		<p><strong>', $txt['poll_total_voters'], ':</strong> ', $context['poll']['total_votes'], '</p>';
		}
		// They are allowed to vote! Go to it!
		else
		{
			echo '
		<form action="', $scripturl, '?action=vote;topic=', $context['current_topic'], '.', $context['start'], ';poll=', $context['poll']['id'], ';ispoll" method="post" accept-charset="', $context['character_set'], '">';

			// Show a warning if they are allowed more than one option.
			if ($context['poll']['allowed_warning'])
				echo '
			<p class="information">', $context['poll']['allowed_warning'], '</p>';

			echo '
			<ul class="reset options" id="polloptions">';

			// Show each option with its button - a radio likely.
			foreach ($context['poll']['options'] as $option)
				echo '
				<li class="middletext"><span>', $option['vote_button'], '</span><label for="', $option['id'], '">', $option['option'], '</label></li>';

			echo '
			</ul>
			<div id="pollmoderation">
				<input type="submit" value="', $txt['poll_vote'], '" class="button_submit" />
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			</div>
		</form>
			';
		}
		// Build the poll moderation button array.
		$poll_buttons = array(
			'vote' => array('test' => 'allow_return_vote', 'text' => 'poll_return_vote', 'image' => 'poll_options.gif', 'lang' => true, 'url' => $scripturl . '?topic=' . $context['current_topic'] . '.' . $context['start'].';ispoll','active' => true),
			'results' => array('test' => 'show_view_results_button', 'text' => 'poll_results', 'image' => 'poll_results.gif', 'lang' => true, 'url' => $scripturl . '?topic=' . $context['current_topic'] . '.' . $context['start'] . ';viewresults;ispoll', 'active' => true),
			'change_vote' => array('test' => 'allow_change_vote', 'text' => 'poll_change_vote', 'image' => 'poll_change_vote.gif', 'lang' => true, 'url' => $scripturl . '?action=vote;topic=' . $context['current_topic'] . '.' . $context['start'] . ';poll=' . $context['poll']['id'] . ';ispoll;' . $context['session_var'] . '=' . $context['session_id']),
			'lock' => array('test' => 'allow_lock_poll', 'text' => (!$context['poll']['is_locked'] ? 'poll_lock' : 'poll_unlock'), 'image' => 'poll_lock.gif', 'lang' => true, 'url' => $scripturl . '?action=lockvoting;topic=' . $context['current_topic'] . '.' . $context['start'] . ';ispoll;' . $context['session_var'] . '=' . $context['session_id']),
			'edit' => array('test' => 'allow_edit_poll', 'text' => 'poll_edit', 'image' => 'poll_edit.gif', 'lang' => true, 'url' => $scripturl . '?action=editpoll;topic=' . $context['current_topic'] . '.' . $context['start']),
			'remove_poll' => array('test' => 'can_remove_poll', 'text' => 'poll_remove', 'image' => 'admin_remove_poll.gif', 'lang' => true, 'custom' => 'onclick="return confirm(\'' . $txt['poll_remove_warn'] . '\');"', 'url' => $scripturl . '?action=removepoll;topic=' . $context['current_topic'] . '.' . $context['start'] . ';' . $context['session_var'] . '=' . $context['session_id']),
		);

		echo '
		<menu class="pagesection fullspan right">', template_button_strip($poll_buttons, 'right'), '	</menu>';
		
		// Is the clock ticking?
		if (!empty($context['poll']['expire_time']))
			echo '
		<p class="information2"><strong>', ($context['poll']['is_expired'] ? $txt['poll_expired_on'] : $txt['poll_expires_on']), ':</strong> ', $context['poll']['expire_time'], '</p>';

		echo '
	</div>
</div>';
	}

	echo '
<article id="a_display" class="m_sections' , $context['a_active']=='messages' ? ' active"' : '" style="display: none;"', '>
	<h3 id="top"><span id="t_subject">' , $context['subject'] , '</span><span class="', !empty($context['page_index']) ? 'pagelinks">'. $context['page_index'].'</span>' : '"> </span>' , '</h3>
	<div class="a_display_info">
		', template_topic_info(false), '
	</div>
	<div id="a_messages">
		<a id="msg', $context['first_message'], '"></a>', $context['first_new_message'] ? '<a id="new"></a>' : '';


	// Build the normal button array.
	$normal_buttons = array(
		'reply' => array('test' => 'can_reply', 'text' => 'reply', 'image' => 'reply.gif', 'lang' => true, 'url' => $scripturl . '?action=post;topic=' . $context['current_topic'] . '.' . $context['start'] . ';last_msg=' . $context['topic_last_message'], 'active' => true),
		'add_poll' => array('test' => 'can_add_poll', 'text' => 'add_poll', 'image' => 'add_poll.gif', 'lang' => true, 'url' => $scripturl . '?action=editpoll;add;topic=' . $context['current_topic'] . '.' . $context['start']),
		'notify' => array('test' => 'can_mark_notify', 'text' => $context['is_marked_notify'] ? 'unnotify' : 'notify', 'image' => ($context['is_marked_notify'] ? 'un' : '') . 'notify.gif', 'lang' => true, 'custom' => 'onclick="return confirm(\'' . ($context['is_marked_notify'] ? $txt['notification_disable_topic'] : $txt['notification_enable_topic']) . '\');"', 'url' => $scripturl . '?action=notify;sa=' . ($context['is_marked_notify'] ? 'off' : 'on') . ';topic=' . $context['current_topic'] . '.' . $context['start'] . ';' . $context['session_var'] . '=' . $context['session_id']),
		'mark_unread' => array('test' => 'can_mark_unread', 'text' => 'mark_unread', 'image' => 'markunread.gif', 'lang' => true, 'url' => $scripturl . '?action=markasread;sa=topic;t=' . $context['mark_unread_time'] . ';topic=' . $context['current_topic'] . '.' . $context['start'] . ';' . $context['session_var'] . '=' . $context['session_id']),
		'send' => array('test' => 'can_send_topic', 'text' => 'send_topic', 'image' => 'sendtopic.gif', 'lang' => true, 'url' => $scripturl . '?action=emailuser;sa=sendtopic;topic=' . $context['current_topic'] . '.0'),
		'print' => array('text' => 'print', 'image' => 'print.gif', 'lang' => true, 'custom' => 'rel="new_win nofollow"', 'url' => $scripturl . '?action=printpage;topic=' . $context['current_topic'] . '.0'),
	);

	// Allow adding new buttons easily.
	call_integration_hook('integrate_display_buttons', array(&$normal_buttons));

	//		<div class="button_submit buts is_icon">', $context['previous_next'], '</div>

	echo '
		<menu class="pagesection">
			' , !empty($modSettings['topbottomEnable']) ? $context['menu_separator'] . ' <a id="go_down" class="button_submit buts" href="#bot">' . $txt['go_down'] . '</a>' : '', '
			', template_button_strip($normal_buttons, 'right'), '
		</menu>
		<form action="', $scripturl, '?action=quickmod2;topic=', $context['current_topic'], '.', $context['start'], '" method="post" accept-charset="', $context['character_set'], '" name="quickModForm" id="quickModForm" style="margin: 0;" onsubmit="return oQuickModify.bInEditMode ? oQuickModify.modifySave(\'' . $context['session_id'] . '\', \'' . $context['session_var'] . '\') : false">
			<section class="forumstyle">';

	$ignoredMsgs = array();
	$removableMessageIDs = array();
	$alternate = false;

	// Get all the messages...
	while ($message = $context['get_message']())
	{
		$ignoring = false;
		$alternate = !$alternate;
		if ($message['can_remove'])
			$removableMessageIDs[] = $message['id'];

		// Are we ignoring this message?
		if (!empty($message['is_ignored']))
		{
			$ignoring = true;
			$ignoredMsgs[] = $message['id'];
		}
		
		a_message($message);
	}

	echo '
			</section>
		</form>
		<menu class="pagesection">
			' , !empty($modSettings['topbottomEnable']) ? $context['menu_separator'] . ' <a id="bot" class="button_submit buts" href="#top">' . $txt['go_up'] . '</a>' : '', '
			', template_button_strip($normal_buttons, 'right'), '
		</menu>
	</div>
	<h3 id="bottom_h3"><span id="next_links">', $context['previous_next'], '</span><span class="', !empty($context['page_index']) ? 'pagelinks">'. $context['page_index'].'</span>' : '"> </span>' , '</h3>';

	$mod_buttons = array(
		'move' => array('active' => true, 'test' => 'can_move', 'text' => 'move_topic', 'image' => 'admin_move.gif', 'lang' => true, 'url' => $scripturl . '?action=movetopic;topic=' . $context['current_topic'] . '.0'),
		'delete' => array('test' => 'can_delete', 'text' => 'remove_topic', 'image' => 'admin_rem.gif', 'lang' => true, 'custom' => 'onclick="return confirm(\'' . $txt['are_sure_remove_topic'] . '\');"', 'url' => $scripturl . '?action=removetopic2;topic=' . $context['current_topic'] . '.0;' . $context['session_var'] . '=' . $context['session_id']),
		'lock' => array('test' => 'can_lock', 'text' => empty($context['is_locked']) ? 'set_lock' : 'set_unlock', 'image' => 'admin_lock.gif', 'lang' => true, 'url' => $scripturl . '?action=lock;topic=' . $context['current_topic'] . '.' . $context['start'] . ';' . $context['session_var'] . '=' . $context['session_id']),
		'sticky' => array('test' => 'can_sticky', 'text' => empty($context['is_sticky']) ? 'set_sticky' : 'set_nonsticky', 'image' => 'admin_sticky.gif', 'lang' => true, 'url' => $scripturl . '?action=sticky;topic=' . $context['current_topic'] . '.' . $context['start'] . ';' . $context['session_var'] . '=' . $context['session_id']),
		'merge' => array('test' => 'can_merge', 'text' => 'merge', 'image' => 'merge.gif', 'lang' => true, 'url' => $scripturl . '?action=mergetopics;board=' . $context['current_board'] . '.0;from=' . $context['current_topic']),
		'calendar' => array('test' => 'calendar_post', 'text' => 'calendar_link', 'image' => 'linktocal.gif', 'lang' => true, 'url' => $scripturl . '?action=post;calendar;msg=' . $context['topic_first_message'] . ';topic=' . $context['current_topic'] . '.0'),
	);

	// Restore topic. eh?  No monkey business.
	if ($context['can_restore_topic'])
		$mod_buttons[] = array('icon' => array('icon-right-open','icon-reply-outline'),'text' => 'restore_topic', 'image' => '', 'lang' => true, 'url' => $scripturl . '?action=restoretopic;topics=' . $context['current_topic'] . ';' . $context['session_var'] . '=' . $context['session_id']);

	// Allow adding new mod buttons easily.
	call_integration_hook('integrate_mod_buttons', array(&$mod_buttons));

	echo '
	<div id="a_display_bottom">
		<menu class="pagesection">
			<div id="display_jump_to">&nbsp;</div>
			', template_button_strip($mod_buttons, 'bottom', array('id' => 'moderationbuttons_strip')), '
		</menu>';

	if ($context['can_reply'] && !empty($options['display_quick_reply']))
	{
		echo '
		<a id="quickreply"></a>
		<h3 class="header_name clear">
			 ', $txt['quick_reply'], '
		</h3>
		<div id="quickReplyOptions">
			<div class="roundframe">
				<p class="smalltext lefttext">', $txt['quick_reply_desc'], '</p>
					', $context['is_locked'] ? '<p class="alert smalltext">' . $txt['quick_reply_warning'] . '</p>' : '',
					$context['oldTopicError'] ? '<p class="alert smalltext">' . sprintf($txt['error_old_topic'], $modSettings['oldTopicDays']) . '</p>' : '', '
					', $context['can_reply_approved'] ? '' : '<em>' . $txt['wait_for_approval'] . '</em>', '
					', !$context['can_reply_approved'] && $context['require_verification'] ? '<br />' : '', '
					<form action="', $scripturl, '?board=', $context['current_board'], ';action=post2" method="post" accept-charset="', $context['character_set'], '" name="postmodify" id="postmodify" onsubmit="submitonce(this);" style="margin: 0;">
						<input type="hidden" name="topic" value="', $context['current_topic'], '" />
						<input type="hidden" name="subject" value="', $context['response_prefix'], $context['subject'], '" />
						<input type="hidden" name="icon" value="xx" />
						<input type="hidden" name="from_qr" value="1" />
						<input type="hidden" name="notify" value="', $context['is_marked_notify'] || !empty($options['auto_notify']) ? '1' : '0', '" />
						<input type="hidden" name="not_approved" value="', !$context['can_reply_approved'], '" />
						<input type="hidden" name="goback" value="', empty($options['return_to_post']) ? '0' : '1', '" />
						<input type="hidden" name="last_msg" value="', $context['topic_last_message'], '" />
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
						<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />';

			// Guests just need more.
			if ($context['user']['is_guest'])
				echo '
						<strong>', $txt['name'], ':</strong> <input type="text" name="guestname" value="', $context['name'], '" size="25" class="input_text" tabindex="', $context['tabindex']++, '" />
						<strong>', $txt['email'], ':</strong> <input type="text" name="email" value="', $context['email'], '" size="25" class="input_text" tabindex="', $context['tabindex']++, '" /><br />';

			// Is visual verification enabled?
			if ($context['require_verification'])
				echo '
						<strong>', $txt['verification'], ':</strong>', template_control_verification($context['visual_verification_id'], 'quick_reply'), '<br />';

			echo '
						<div class="quickReplyContent">
							<textarea style="width: 100%; height: 10rem;" name="message" tabindex="', $context['tabindex']++, '"></textarea>
						</div>
						<div class="righttext padding">
							<input type="submit" name="post" value="', $txt['post'], '" onclick="return submitThisOnce(this);" accesskey="s" tabindex="', $context['tabindex']++, '" class="button_submit" />
							<input type="submit" name="preview" value="', $txt['preview'], '" onclick="return submitThisOnce(this);" accesskey="p" tabindex="', $context['tabindex']++, '" class="button_submit" />';

			if ($context['show_spellchecking'])
				echo '
							<input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'postmodify\', \'message\');" tabindex="', $context['tabindex']++, '" class="button_submit" />';

			echo '
						</div>
					</form>
				</div>
			</div>
		</div>';
	}
	else
		echo '
		<br class="clear" />';

	echo '
	</div>
</article>';

	if ($context['show_spellchecking'])
		echo '
<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>
<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/spellcheck.js"></script>';

	echo '
<script type="text/javascript" src="' . $settings['theme_url'] . '/scripts/topic.js"></script>
<script type="text/javascript"><!-- // --><![CDATA[';

	if (!empty($options['display_quick_reply']))
		echo '
	var oQuickReply = new QuickReply({
		bDefaultCollapsed: ', !empty($options['display_quick_reply']) && $options['display_quick_reply'] == 2 ? 'false' : 'true', ',
		iTopicId: ', $context['current_topic'], ',
		iStart: ', $context['start'], ',
		sScriptUrl: smf_scripturl,
		sImagesUrl: "', $settings['images_url'], '",
		sContainerId: "quickReplyOptions",
		sImageId: "quickReplyExpand",
		sImageCollapsed: "collapse.gif",
		sImageExpanded: "expand.gif",
		sJumpAnchor: "quickreply"
	});';

	if (!empty($options['display_quick_mod']) && $options['display_quick_mod'] == 1 && $context['can_remove_post'])
		echo '
	var oInTopicModeration = new InTopicModeration({
		sSelf: \'oInTopicModeration\',
		sCheckboxContainerMask: \'in_topic_mod_check_\',
		aMessageIds: [\'', implode('\', \'', $removableMessageIDs), '\'],
		sSessionId: \'', $context['session_id'], '\',
		sSessionVar: \'', $context['session_var'], '\',
		sButtonStrip: \'moderationbuttons\',
		sButtonStripDisplay: \'moderationbuttons_strip\',
		bUseImageButton: false,
		bCanRemove: ', $context['can_remove_post'] ? 'true' : 'false', ',
		sRemoveButtonLabel: \'', $txt['quickmod_delete_selected'], '\',
		sRemoveButtonImage: \'delete_selected.gif\',
		sRemoveButtonConfirm: \'', $txt['quickmod_confirm'], '\',
		bCanRestore: ', $context['can_restore_msg'] ? 'true' : 'false', ',
		sRestoreButtonLabel: \'', $txt['quick_mod_restore'], '\',
		sRestoreButtonImage: \'restore_selected.gif\',
		sRestoreButtonConfirm: \'', $txt['quickmod_confirm'], '\',
		sFormId: \'quickModForm\'
	});';

	echo '
	if (\'XMLHttpRequest\' in window)
	{
		var oQuickModify = new QuickModify({
			sScriptUrl: smf_scripturl,
			bShowModify: ', $settings['show_modify'] ? 'true' : 'false', ',
			iTopicId: ', $context['current_topic'], ',
			sTemplateBodyEdit: ', JavaScriptEscape('
				<div id="quick_edit_body_container" style="width: 90%">
					<div id="error_box" style="padding: 4px;" class="error"></div>
					<textarea class="editor" name="message" rows="12" style="' . ($context['browser']['is_ie8'] ? 'width: 635px; max-width: 100%; min-width: 100%' : 'width: 100%') . '; margin-bottom: 10px;" tabindex="' . $context['tabindex']++ . '">%body%</textarea><br />
					<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '" />
					<input type="hidden" name="topic" value="' . $context['current_topic'] . '" />
					<input type="hidden" name="msg" value="%msg_id%" />
					<div class="righttext">
						<input type="submit" name="post" value="' . $txt['save'] . '" tabindex="' . $context['tabindex']++ . '" onclick="return oQuickModify.modifySave(\'' . $context['session_id'] . '\', \'' . $context['session_var'] . '\');" accesskey="s" class="button_submit" />&nbsp;&nbsp;' . ($context['show_spellchecking'] ? '<input type="button" value="' . $txt['spell_check'] . '" tabindex="' . $context['tabindex']++ . '" onclick="spellCheck(\'quickModForm\', \'message\');" class="button_submit" />&nbsp;&nbsp;' : '') . '<input type="submit" name="cancel" value="' . $txt['modify_cancel'] . '" tabindex="' . $context['tabindex']++ . '" onclick="return oQuickModify.modifyCancel();" class="button_submit" />
					</div>
				</div>'), ',
			sTemplateSubjectEdit: ', JavaScriptEscape('<input type="text" style="width: 90%;" name="subject" value="%subject%" size="80" maxlength="80" tabindex="' . $context['tabindex']++ . '" class="input_text" />'), ',
			sTemplateBodyNormal: ', JavaScriptEscape('%body%'), ',
			sTemplateSubjectNormal: ', JavaScriptEscape('<a href="' . $scripturl . '?topic=' . $context['current_topic'] . '.msg%msg_id%#msg%msg_id%" rel="nofollow">%subject%</a>'), ',
			sTemplateTopSubject: ', JavaScriptEscape('%subject%'), ',
			sErrorBorderStyle: ', JavaScriptEscape('1px solid red'), '
		});

		aJumpTo[aJumpTo.length] = new JumpTo({
			sContainerId: "display_jump_to",
			sJumpToTemplate: "<label class=\"smalltext\" for=\"%select_id%\">', $context['jump_to']['label'], ':<" + "/label> %dropdown_list%",
			iCurBoardId: ', $context['current_board'], ',
			iCurBoardChildLevel: ', $context['jump_to']['child_level'], ',
			sCurBoardName: "', $context['jump_to']['board_name'], '",
			sBoardChildLevelIndicator: "==",
			sBoardPrefix: "=> ",
			sCatSeparator: "-----------------------------",
			sCatPrefix: "",
			sGoButtonLabel: "', $txt['go'], '"
		});
	}';

	if (!empty($ignoredMsgs))
	{
		echo '
	var aIgnoreToggles = new Array();';

		foreach ($ignoredMsgs as $msgid)
		{
			echo '
	aIgnoreToggles[', $msgid, '] = new smc_Toggle({
		bToggleEnabled: true,
		bCurrentlyCollapsed: true,
		aSwappableContainers: [
			\'msg_', $msgid, '_extra_info\',
			\'msg_', $msgid, '\',
			\'msg_', $msgid, '_footer\',
			\'msg_', $msgid, '_quick_mod\',
			\'modify_button_', $msgid, '\',
			\'msg_', $msgid, '_signature\'

		],
		aSwapLinks: [
			{
				sId: \'msg_', $msgid, '_ignored_link\',
				msgExpanded: \'\',
				msgCollapsed: ', JavaScriptEscape($txt['show_ignore_user_post']), '
			}
		]
	});';
		}
	}

	echo '
// ]]></script>';
}

?>
