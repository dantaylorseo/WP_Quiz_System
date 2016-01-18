jQuery(document).ready( function($) {

	function reorder_questions() {
		var countquestions = 0;
		
		$.each( $('.lms_quiz_question_box'), function(elem) {
			var questno = countquestions + 1;
			$.each( $(this).find('input'), function(elem2) {
				var name = $(this).attr('name');
				var newname = name.replace( /lms_quiz_question\[([0-9]+)\]/, 'lms_quiz_question['+countquestions+']');
				$(this).attr('name', newname);
			});
			$(this).find('h2 span.lms_quiz_question_no').html(questno);
			countquestions = countquestions + 1;
		});
	}

	var questionbox = '<div class="lms_quiz_question_box">' +
			'<button type="button" class="quiz_move_up">&#9650;</button>' +
            '<button type="button" class="quiz_move_down">&#9660;</button>' + 
            '<button class="delete_quiz_question">Delete Question</button>' +
    		'<div class="lms_quiz_question_box_header">' +
    			'<h2>Question <span class="lms_quiz_question_no">1</span></h2>' +
    		'</div>' +
    		'<div class="lms_quiz_question_box_inner">' +
	    		'<table class="form-table">' +
	    			'<tr>' +
                        '<th>Image</th>' +
                        '<td>' +
                            '<div id="box3_preview" class="lms_quiz_image_preview">' +
                                '<img src="https://placehold.it/500x250/ffffff/cccccc/?textsize=120&amp;text=no+image+uploaded" style="width: 100%; height: auto;">'+
                        	'</div>' +
                        	'<input type="hidden" name="lms_quiz_question[0][image]" value="" id="box3_image">' +
                        	'<input type="submit" name="upload-box1" id="upload-box1" class="button button-primary upload_media_box" value="Upload" rel="box3">'+
                        '</td>'+
                    '</tr>' +
	    			'<tr>' +
		    			'<th>Question</th>' +
		    			'<td><input type="text" name="lms_quiz_question[0][question]" class="regular-text" value="" placeholder="Question"></td>' +
	    			'</tr>' +
	    			'<tr>' +
		    			'<th>Answers</th>' +
		    			'<td>' +
		    				'<table class="lms_quiz_answers_table">' +
		    					'<tr class="lms_quiz_answer_clone">' +
		    						'<td>' +
                                        '<input type="text" value="" name="lms_quiz_question[0][answers][0][answer]" class="answer regular-text" placeholder="Answer">' +
                                    '</td>' +
		    						'<td>' +
                                        '<input type="number" value="" name="lms_quiz_question[0][answers][0][score]" class="score regular-text" placeholder="Score">' +
                                    '</td>' +
		    					'</tr>' +
		    				'</table>' +
		    				'<button type="button" class="button button-secondary lms_quiz_add_answer">Add Answer</button>' +
		    			'</td>' +
	    			'</tr>' +
	    		'</table>' +
	    	'</div>' +
    	'</div>';
    	console.log(questionbox);
	//var $questionbox      = $('.lms_quiz_question_box:last');
	var $questionboxclone = $($.parseHTML(questionbox));
	var questions = 1;

	var $answer      = $('.lms_quiz_answer_clone:last');
	var $answerclone = $answer.clone();

	$('.lms_quiz_add_question').live( 'click', function(e) {
		e.preventDefault();
		
		var $newbox = $questionboxclone.clone();

		//console.log( $questionboxclone );

		$newbox.find('input[type=text]').val('');

		var questions = $('.lms_quiz_question_box').length
		
		$.each( $newbox.find('.upload_media_box'), function( elem ) {
			var name = $(this).attr('rel');
			var newname = name.replace( /box([0-9]+)/, 'box'+questions);
			$(this).attr('rel', newname);
		});

		$.each( $newbox.find('input[type=hidden]'), function( elem ) {
			var name = $(this).attr('id');
			var newname = name.replace( /box([0-9]+)/, 'box'+questions);
			$(this).attr('id', newname);
		});

		$.each( $newbox.find('.lms_quiz_image_preview'), function(elem) {
			var id = $(this).attr('id');
			var newid = id.replace( /box([0-9]+)/, 'box'+questions);
			$(this).attr('id', newid);
		});

		$.each( $newbox.find('input'), function( elem ) {
			var name = $(this).attr('name');
			var newname = name.replace( /lms_quiz_question\[([0-9]+)\]/, 'lms_quiz_question['+questions+']');
			$(this).attr('name', newname);
		});


		questions = parseInt(questions) + 1;
		
		$newbox.find('h2 span.lms_quiz_question_no').html(questions);
		$('.lms_quiz_question_box_inner').hide();
		$('.lms_quiz_add_question').before($newbox);
		$('.lms_quiz_question_box_inner:last').show();
	});

	$('.lms_quiz_add_answer').live( 'click', function(e) {
		e.preventDefault();
		var $clickedlink = $(this);
		var $linkparent  = $clickedlink.parent();
		var inputs = $linkparent.find('input.answer').length;

		var $newanswer = $linkparent.find('.lms_quiz_answer_clone:last');
		var $newanswerclone = $newanswer.clone();

		$.each( $newanswerclone.find('input'), function( elem ) {
			
			$(this).val('');

			var name = $(this).attr('name');
			var newname = name.replace( /\[answers\]\[([0-9]+)\]/, function(n) { return n++ });

			var newname = name.replace( /\[answers\]\[\d+\]/, function(attr) {
				return attr.replace(/\d+/, function(val) { return parseInt(inputs) });
			});
			console.log( newname );

			$(this).attr('name', newname);
		});

		$newanswer.after($newanswerclone);
	});
	
	$('.lms_quiz_question_box_header').live( 'click', function(e) {
		$('.lms_quiz_question_box_inner').hide();
		$(this).parent().find('.lms_quiz_question_box_inner').toggle();	
	});

	$('.lms_quiz_question_box_inner').last().show();

	var clicked, imgurl, inputclass, imageclass, file_frame, attachment;

	$('.upload_media_box').live( 'click', function(e) {
		clicked = $(this);
		inputclass = clicked.attr('rel');
		e.preventDefault();

		// If the media frame already exists, reopen it.
		if ( file_frame ) {
		  file_frame.open();
		  return;
		}
	
		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
		  title: $( this ).data( 'uploader_title' ),
		  button: {
			text: $( this ).data( 'uploader_button_text' ),
		  },
		  multiple: false  // Set to true to allow multiple files to be selected
		});
	
		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {
		  // We set multiple to false so only get one image from the uploader
		  attachment = file_frame.state().get('selection').first().toJSON();
	
		  $('#'+inputclass+'_preview img').attr( 'src', attachment.url );
		  $('#'+inputclass+'_image').val( attachment.id );
		});
	
		// Finally, open the modal
		file_frame.open();
	});

	$('.delete_quiz_image').live( 'click', function(e) {
		e.preventDefault();
		var target = $(this).attr('rel');

		$('#'+target+'_preview img').attr( 'src', 'https://placehold.it/500x250/ffffff/cccccc/?txtsize=60&text=no+image+uploaded' );
		$('#'+target+'_image').val('');

	});

	$('.delete_quiz_question').live( 'click', function(e) {
		e.preventDefault();
		$(this).parent().remove();
		reorder_questions();
		
	});

	$('.quiz_move_up').live( 'click', function(e) {
		e.preventDefault();

		if( $(this).parent().index() != 0 ) {
			var clone = $(this).parent().clone();
			
			$(this).parent().prev('.lms_quiz_question_box').before(clone);
			$(this).parent().remove();

			reorder_questions();

			$('.lms_quiz_question_box_inner').hide();
			$(clone).find('.lms_quiz_question_box_inner').toggle();	
		}
	});

	$('.quiz_move_down').live( 'click', function(e) {
		e.preventDefault();

		var length = $('.lms_quiz_question_box').length - 1;

		if( $(this).parent().index() != length ) {
			var clone = $(this).parent().clone();
			
			$(this).parent().next('.lms_quiz_question_box').after(clone);
			$(this).parent().remove();

			reorder_questions();

			$('.lms_quiz_question_box_inner').hide();
			$(clone).find('.lms_quiz_question_box_inner').toggle();	
		}
	});

});