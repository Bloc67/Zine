// Collapsible fieldsets, pure candy
$(document).on('click', '.section_menu li', function() {
	$('.m_sections').removeClass('active');
	$('.section_menu li').removeClass('active');
	$($(this).attr('data-section')).addClass('active');
	$(this).addClass('active');
});

