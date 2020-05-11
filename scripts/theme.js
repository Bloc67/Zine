// Section menu
$(document).on('click', '.section_menu li', function() {
	$('.m_sections').fadeOut(50);
	$('.section_menu li').removeClass('active');
	$($(this).attr('data-section')).fadeIn(100);
	$(this).addClass('active');
});

// Boardindex category switch menu
$(document).on('click', '.category_list li', function() {
	$('.category').fadeOut(5);
	$('.category_list li').removeClass('active');
	$($(this).attr('data-section')).fadeIn(20);
	$(this).addClass('active');
});

// transform help links into AJAX modals
$(document).ready(function() {
	$("a.help").prop('onclick', null);
	$('a.help').click(function(e) {
        e.preventDefault();
		var mLink = $(this).attr("href");
		$.get(mLink,function(response){ 
			$('#help_modal').html(response); 
	   });	
		$('#help_modal').show(0);
		return false;
	});
});

// close the help modal window
$(document).on('click', '#help_modal_close', function() {
	$('#help_modal').hide(0);
});

// admin menu hide/show on mobile
$(document).on('click', '#close-amenu', function() {
	$('#amenu').toggleClass('hidden');
});
$(document).on('click', '#open-amenu', function() {
	$('#amenu').toggleClass('hidden');
});

// news advance
$(document).on('click', '#close-amenu', function() {
	$('#amenu').toggleClass('hidden');
});

