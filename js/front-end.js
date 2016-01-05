jQuery(document).ready(function($) {

	$('.lms-next-question').click(function(e) {
		e.preventDefault();

		var target = $(this).data('target');
		$('.lms-quiz-question').removeClass('active');

		$(target).addClass('active');
	});

	$('.lms-finish_quiz').click(function(e) {
		e.preventDefault();

		var data = $(this).parent().parent().serialize();

		console.log(data);

		$.post(ajax_object.ajax_url, data, function(response) {
			$('.lms-quiz-question').removeClass('active');
			$('.lms-quiz-question:last').after(response);
		}, 'html');

	});

});