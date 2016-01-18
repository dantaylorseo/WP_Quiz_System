<?php
class LMS_Quiz_System {

	private $admin_icon = '';
    var $textdomain;
    
	public function __construct(){

        $this->textdomain = "LMS_Quiz_System_TextDomain";
		$this->actions();
		$this->filters();

	}

	private function actions() {

		// Init
		add_action( 'init', array( $this, 'register_post_types' ) );
        add_action( 'init', array( $this, 'register_shortcodes' ) );
		// Scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Meta boxes
		add_action( 'add_meta_boxes', array( $this, 'quiz_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_quiz_questions_meta' ), 10, 3 ) ;
		// Admin menus
		add_action('admin_menu', array($this, 'admin_menus'));

        // Ajax
        add_action('wp_ajax_ajax_lms_quiz_submit', array($this, 'ajax_lms_quiz_submit'));
        add_action('wp_ajax_nopriv_ajax_lms_quiz_submit', array($this, 'ajax_lms_quiz_submit'));
		
	}

    public function ajax_lms_quiz_submit() {
        $data     = get_post_meta( $_POST['quiz-id'], '_lms_quiz_questions', true );
        $settings = get_post_meta( $_POST['quiz-id'], '_lms_quiz_settings', true );
        
        $score = 0;
        $total = 0;

        foreach( $_POST['lms_quiz_answer']['question'] as $key=>$value ) {
            $score = $score + $data[$key]['answers'][$value]['score'];
            $max = 0;
            foreach( $data[$key]['answers'] as $score1 ) {
                if( $score1['score'] > $max ) {
                    $max = $score1['score'];
                }
            }
            $total = $total + $max;
        }
        $results_page_content = stripslashes( $settings['results_page'] );

        $results_page_content = str_replace( '{score}', $score, $results_page_content );
        $results_page_content = str_replace( '{total}', $total, $results_page_content );

        echo '<div class="lms-quiz-question active">';        
        echo wpautop($results_page_content);
        echo '</div>';
        wp_die();
    }

	private function filters() {

	}

	public function admin_enqueue_scripts() {
		wp_enqueue_script( 'lms-quiz-admin', LMS_QUIZ_SYSTEM_URL .'/js/admin.js', array('jquery', 'jquery-ui-core', 'jquery-ui-sortable', 'jquery-ui-datepicker', 'jquery-ui-draggable', 'jquery-ui-droppable', 'dashboard' ) );
		wp_enqueue_style( 'lms-quiz-admin-style', LMS_QUIZ_SYSTEM_URL .'/css/admin.css' );
	}

	public function enqueue_scripts() {
        wp_enqueue_script( 'lms-quiz-front-end', LMS_QUIZ_SYSTEM_URL .'/js/front-end.js', array('jquery') );
        wp_localize_script( 'lms-quiz-front-end', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

        wp_enqueue_style( 'lms-quiz-front-end-style', LMS_QUIZ_SYSTEM_URL .'/css/front-end.css' );
	}

	public function admin_menus() {
        add_submenu_page('edit.php?post_type=lms_quiz', 'Submissions', 'Submissions', 'manage_options', 'edit.php?post_type=lms_quiz_submission' );
	}

	public function register_post_types() {
        
        $quiz = array(
            'labels'              => array(
                'name'               => _x( 'Quiz', 'post type general name', $this->textdomain ),
                'singular_name'      => _x( 'Quiz', 'post type singular name', $this->textdomain ),
                'menu_name'          => _x( 'Quiz', 'admin menu', $this->textdomain ),
                'name_admin_bar'     => _x( 'Quiz', 'add new on admin bar', $this->textdomain ),
                'add_new'            => _x( 'Add New Quiz', 'book', $this->textdomain ),
                'add_new_item'       => __( 'Add New Quiz', $this->textdomain ),
                'new_item'           => __( 'New Quiz', $this->textdomain ),
                'edit_item'          => __( 'Edit Quiz', $this->textdomain ),
                'view_item'          => __( 'View Quiz', $this->textdomain ),
                'all_items'          => __( 'All Quizzes', $this->textdomain ),
                'search_items'       => __( 'Search Quizzes', $this->textdomain ),
                'parent_item_colon'  => __( 'Parent Quiz:', $this->textdomain ),
                'not_found'          => __( 'No quizzes found.', $this->textdomain ),
                'not_found_in_trash' => __( 'No quizzes found in Trash.', $this->textdomain )
            ),
            'public'             => false,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'exclude_from_search'=> true,
            'menu_position'      => 27,
            'menu_icon'          => 'dashicons-clipboard',
            'supports'           => array( 'title' ),
        );

		$submission = array(
            'labels'              => array(
                'name'               => _x( 'Submissions', 'post type general name', $this->textdomain ),
                'singular_name'      => _x( 'Submission', 'post type singular name', $this->textdomain ),
                'menu_name'          => _x( 'Submissions', 'admin menu', $this->textdomain ),
                'name_admin_bar'     => _x( 'Submissions', 'add new on admin bar', $this->textdomain ),
                'add_new'            => _x( 'Add New Submission', 'book', $this->textdomain ),
                'add_new_item'       => __( 'Add New Submissions', $this->textdomain ),
                'new_item'           => __( 'New Submission', $this->textdomain ),
                'edit_item'          => __( 'Edit Submission', $this->textdomain ),
                'view_item'          => __( 'View Submission', $this->textdomain ),
                'all_items'          => __( 'All Submissions', $this->textdomain ),
                'search_items'       => __( 'Search Submissions', $this->textdomain ),
                'parent_item_colon'  => __( 'Parent Submission:', $this->textdomain ),
                'not_found'          => __( 'No submissions found.', $this->textdomain ),
                'not_found_in_trash' => __( 'No submissions found in Trash.', $this->textdomain )
            ),
            'public'             => false,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'query_var'          => true,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'exclude_from_search'=> true,
            'menu_position'      => 27,
            'menu_icon'          => 'dashicons-clipboard',
            'supports'           => array( 'title' ),
        );

		register_post_type( 'lms_quiz', $quiz );
		register_post_type( 'lms_quiz_submission', $submission );
	}

    public function register_shortcodes() {
       add_shortcode( 'quiz', array( $this, 'display_quiz_shortcode' ) ); 
    }

    public function display_quiz_shortcode( $atts ) {
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
	public function quiz_meta_boxes() {
		add_meta_box(
            'lms_quiz_question_meta',
            __( 'Quiz Questions', $this->textdomain ),
            array( $this, 'quiz_meta_boxes_callback' ),
            'lms_quiz',
            'advanced',
            'core'
        );

        add_meta_box(
            'lms_quiz_settings_meta',
            __( 'Quiz Settings', $this->textdomain ),
            array( $this, 'quiz_settings_meta_boxes_callback' ),
            'lms_quiz',
            'advanced',
            'core'
        );
	}

    public function quiz_settings_meta_boxes_callback( $post ) {
        $data = get_post_meta( $post->ID, '_lms_quiz_settings', true ); 
        if( isset( $data['results_page'] ) && $data['results_page'] ) {
            $results_page_content = stripslashes( $data['results_page'] );
        } else {
            $results_page_content = "You scored {score} out of {total}";
        }
    ?>
        <table class="form-table">
            <tr>
                <th>Results Page Template</th>
                <td class="form-field">
                    <textarea name="lms_quiz_settings[results_page]" rows="10" width="100%"><?php echo $results_page_content; ?></textarea>
                    <p class="description">Tags you can use: {score}, {total}, {quiz_link}</p>
                </td>
            </tr>
        </table>
    <?php 
    }

	public function quiz_meta_boxes_callback( $post ) {
		wp_nonce_field( 'quiz_meta_data', 'quiz_meta_nonce' );
        $data = get_post_meta( $post->ID, '_lms_quiz_questions', true );
        wp_enqueue_media();
        $questions = 0;
        $shortcode = htmlspecialchars('[quiz id="'.get_the_ID().'" title="'.get_the_title(get_the_ID()).'"]');
        echo '<div class="lms_quiz_shortcode_box">
            <p>To embed this quiz on a page or post use the following shortcode:</p>
            <input type="text" class="large-text" value="'.$shortcode.'">
        </div>';
        echo '<div>';

        if( isset( $data[0] ) ) {

        foreach( $data as $question ) {
            $answers = 0;
    ?>
        
    	<div class="lms_quiz_question_box">
            <button type="button" class="quiz_move_up">&#9650;</button>
            <button type="button" class="quiz_move_down">&#9660;</button> 
            <button class="delete_quiz_question">Delete Question</button>
            
    		<div class="lms_quiz_question_box_header">
    			<h2>Question <span class="lms_quiz_question_no"><?php echo $questions + 1; ?></span> <span class="lms_quiz_question_box_head_question"><?php echo $question['question']; ?></span></h2>
    		</div>
    		<div class="lms_quiz_question_box_inner">
	    		<table class="form-table">
                    <tr>
                        <th>Image</th>
                        <td>
                            <div id="box<?php echo $questions; ?>_preview" class="lms_quiz_image_preview">
                            <?php
                                            
                                if( isset( $question['image'] ) && $question['image'] != '' ) {
                                    $image      = wp_get_attachment_image_src( $question['image'], 'full' );
                                    $preview    = $image[0];
                                    $imgsrc     = $question['image'];
                                } else {
                                    $preview = '';
                                    $imgsrc = '';
                                }
                            ?>
                            <?php if( $preview != '' ) { ?><img src="<?php echo $preview; ?>" style="width: 100%; height: auto;"><?php } ?>
                        </div>
                        <input type="hidden" name="lms_quiz_question[<?php echo $questions; ?>][image]'; ?>" value="<?php echo $imgsrc; ?>" id="box<?php echo $questions; ?>_image">
                        <?php 
                            if( $imgsrc == '' ) { 
                                submit_button( 'Upload', 'primary upload_media_box', 'upload-box1', false, array( 'rel' => 'box'.$questions ) );
                            } else { 
                                submit_button( 'Change', 'primary upload_media_box', 'upload-box'.$questions, false, array( 'rel' => 'box'.$questions ) );
                                submit_button( 'Delete', 'delete delete_quiz_image', 'delete-box'.$questions, false, array( 'rel' => 'box'.$questions ) );
                            }
                             
                        ?>
                        </td>
                    </tr>
	    			<tr>
		    			<th>Question</th>
		    			<td><input type="text" name="lms_quiz_question[<?php echo $questions; ?>][question]" class="regular-text" value="<?php echo $question['question']; ?>" placeholder="Question"></td>
	    			</tr>
	    			<tr>
		    			<th>Answers</th>
		    			<td>
		    				<table class="lms_quiz_answers_table">
                            <?php foreach( $question['answers'] as $answer ) { ?>
		    					<tr class="lms_quiz_answer_clone">
		    						<td>
                                        <input type="text" value="<?php echo $answer['answer']; ?>" name="lms_quiz_question[<?php echo $questions; ?>][answers][<?php echo $answers; ?>][answer]" class="answer regular-text" placeholder="Answer">
                                    </td>
		    						<td>
                                        <input type="text" value="<?php echo $answer['score']; ?>" name="lms_quiz_question[<?php echo $questions; ?>][answers][<?php echo $answers; ?>][score]" class="score regular-text" placeholder="Score">
                                    </td>
		    					</tr>
                            <?php 
                                $answers ++;
                                } ?>
		    				</table>
		    				<button type="button" class="button button-secondary lms_quiz_add_answer">Add Answer</button>
		    			</td>
	    			</tr>
	    		</table>
	    	</div>
    	</div>
    <?php $questions++; } } else { ?>
        <div class="lms_quiz_question_box">
            <button type="button" class="quiz_move_up">&#9650;</button>
            <button type="button" class="quiz_move_down">&#9660;</button> 
            <button class="delete_quiz_question">Delete Question</button>
            <div class="lms_quiz_question_box_header">
                <h2>Question <span>1</span></h2>
            </div>
            <div class="lms_quiz_question_box_inner">
                <table class="form-table">
                    <tr>
                        <th>Question</th>
                        <td><input type="text" name="lms_quiz_question[<?php echo $questions; ?>][question]" class="regular-text" value="" placeholder="Question"></td>
                    </tr>
                    <tr>
                        <th>Answers</th>
                        <td>
                            <table class="lms_quiz_answers_table">
                            
                                <tr class="lms_quiz_answer_clone">
                                    <td>
                                        <input type="text" value="" name="lms_quiz_question[<?php echo $questions; ?>][answers][0][answer]" class="answer regular-text" placeholder="Answer">
                                    </td>
                                    <td>
                                        <input type="text" value="" name="lms_quiz_question[<?php echo $questions; ?>][answers][0][score]" class="score regular-text" placeholder="Score">
                                    </td>
                                </tr>
                            
                            </table>
                            <button type="button" class="button button-secondary lms_quiz_add_answer">Add Answer</button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    <?php } ?>
    </div>
    	<button type="button" class="button button-secondary lms_quiz_add_question">Add Question</button>
    <?php
	}

	public function save_quiz_questions_meta( $post_id, $post, $update ) {

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
            return $post_id;

        if ( 'auto-draft' == $post->post_status ) {
            return $post_id;
        }

        if ( 'lms_quiz' == $_POST['post_type'] ) {

            if ( ! current_user_can( 'edit_page', $post_id ) )
                return $post_id;
    
        } else {
            return $post_id;
        }

		update_post_meta( $post_id, '_lms_quiz_questions', $_POST['lms_quiz_question'] );
        update_post_meta( $post_id, '_lms_quiz_settings', $_POST['lms_quiz_settings'] );
	}
}
$lms_quiz_system = new LMS_Quiz_System();