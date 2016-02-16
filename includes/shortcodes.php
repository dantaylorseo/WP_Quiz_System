<?php

add_shortcode( 'quiz', 'display_quiz_shortcode' ); 

function display_quiz_shortcode( $atts ) {
    $a = shortcode_atts( array(
        'id'    => '',
        'title' => '',
        'ajax'  => true
    ), $atts );
    
    

    if( $a['id'] != '' ) {
        $output = '<form id="lms-quiz-'.$a['id'].'">';
        $output .= '<input type="hidden" value="'.$a['id'].'" name="quiz-id">';
        $output .= '<input type="hidden" value="ajax_lms_quiz_submit" name="action">';
        $data = get_post_meta( $a['id'], '_lms_quiz_questions', true );
        $questionno = 0;
        $questions = count($data);
        $i = 0;
        foreach ( $data as $question ) {
            $answerno = 0;
            if( isset( $question['image'] ) && $question['image'] != '' ) {
                $image      = wp_get_attachment_image_src( $question['image'], 'full' );
                $imgsrc     = $image[0];
            } else {
                $imgsrc     = '';
            }

            $thisq = $questionno + 1;
            $percentcomplete = number_format(($thisq/$questions)*100,0);

            $output .= '<div class="lms-quiz-question '. ( $questionno == 0 ? 'active' : '' ) .'" id="question-'.$questionno.'">';
            $output .= '<p><strong>Question '.$thisq.' of '.$questions.' ('.$percentcomplete.'%)</strong></p>';
            if( $imgsrc != '' ) $output .= '<img class="lms_quiz_image" src="'.$imgsrc.'">';
            $output .= '<h2>'.$question['question'].'</h2>';
            $output .= '<ul class="lms_quiz_list">';
            foreach( $question['answers'] as $answer ) {
                $output .= '<li><input id="question'.$i.'" name="lms_quiz_answer[question]['.$questionno.']" type="radio" value="'.$answerno.'"><label for="question'.$i.'">'.$answer['answer'].'</label></li>';  
                $answerno++; 
                $i++;
            }
            $questionno++;
            $output .= '</ul>';
            if( $questionno == $questions ) {
                $output .= '<button type="button" class="lms-finish_quiz">Finish Quiz</button>';
            } else {
                $output .= '<button type="button" class="lms-next-question" data-target="#question-'.$questionno.'">Next Question</button>';
            }
            $output .= '</div>';
            
        }
        $output .= '</form>';
        return $output;
    }
}