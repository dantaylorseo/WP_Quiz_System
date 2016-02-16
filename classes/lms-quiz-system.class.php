<?php
class LMS_Quiz_System {

    private static $instance = null;

    private $admin_icon = '';

    var $textdomain;
    
    /**
     * Creates or returns an instance of this class.
     */
    public static function get_instance() {
        // If an instance hasn't been created and set to $instance create an instance and set it to $instance.
        if (null == self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }
    
    public function __construct(){

        $this->actions();
        $this->filters();

    }

    public function actions() {

        // Scripts
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        // Meta boxes
        add_action( 'add_meta_boxes', array( $this, 'quiz_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_quiz_questions_meta' ), 10, 3 ) ;
        add_action( 'add_meta_boxes', array( $this, 'submission_meta_boxes' ) );
        // Admin menus
        add_action( 'admin_menu', array( $this, 'admin_menus' ) );

        // Ajax
        add_action( 'wp_ajax_ajax_lms_quiz_submit', array( $this, 'ajax_lms_quiz_submit' ) );
        add_action( 'wp_ajax_nopriv_ajax_lms_quiz_submit', array( $this, 'ajax_lms_quiz_submit' ) );
        
    }

    static public function ajax_lms_quiz_submit() {
        $data     = get_post_meta( $_POST['quiz-id'], '_lms_quiz_questions', true );
        $settings = get_post_meta( $_POST['quiz-id'], '_lms_quiz_settings', true );
        $score = 0;
        $total = 0;

        $submission_answers = array();

        foreach ( $_POST['lms_quiz_answer']['question'] as $key=>$value ) {
            $score = $score + $data[$key]['answers'][$value]['score'];
            $max = 0;

            $submission_answers[$key] = array(
                'question'  => $data[$key]['question'],
                'answer'    => $data[$key]['answers'][$value]['answer'],
                'score'     => $data[$key]['answers'][$value]['score']
            );

            foreach ( $data[$key]['answers'] as $score1 ) {
                if ( $score1['score'] > $max ) {
                    $max = $score1['score'];
                }
            }
            $total = $total + $max;
        }

        $submission_data = array (
            'user'      => get_current_user_id( ), //returns 0 when not logged in
            'answers'   => $submission_answers,
            'ip'        => ( isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '' ),
            'email'     => ( isset( $_POST['email'] ) ? $_POST['email'] : '' ),
            'quiz'      => $_POST['quiz-id'],
            'score'     => $score
        );

        self::add_submission( $submission_data );

        $results_page_content = stripslashes( $settings['results_page'] );

        $results_page_content = str_replace( '{score}', $score, $results_page_content );
        $results_page_content = str_replace( '{total}', $total, $results_page_content );

        echo '<div class="lms-quiz-question active">';        
        echo wpautop($results_page_content);
        echo '</div>';
        wp_die();
    }

    static private function add_submission( $data ) {

        $post_data = array(
          'post_title'      => 'New submission',
          'post_status'     => 'publish',
          'post_type'       => 'lms_quiz_submission',
          'post_author'     => $data['user'],
        );
        
        $post_id = wp_insert_post( $post_data );

        if ( $post_id != 0 ) {

            update_post_meta( $post_id, '_lms_quiz_submission_data', $data );

        }

    }

    static private function filters() {

    }

    static public function admin_enqueue_scripts() {
        wp_enqueue_script( 'lms-quiz-admin', LMS_QUIZ_SYSTEM_URL .'/js/admin.js', array('jquery', 'jquery-ui-core', 'jquery-ui-sortable', 'jquery-ui-datepicker', 'jquery-ui-draggable', 'jquery-ui-droppable', 'dashboard' ) );
        wp_enqueue_style( 'lms-quiz-admin-style', LMS_QUIZ_SYSTEM_URL .'/css/admin.css' );
    }


    static public function admin_menus() {
        add_submenu_page('edit.php?post_type=lms_quiz', 'Submissions', 'Submissions', 'manage_options', 'edit.php?post_type=lms_quiz_submission' );
    }

    static public function submission_meta_boxes() {
        add_meta_box(
            'lms_quiz_submission_meta',
            __( 'Submission Data' ),
            array( self::get_instance(), 'submission_meta_boxes_callback' ),
            'lms_quiz_submission',
            'advanced',
            'core'
        );
    }

    static public function submission_meta_boxes_callback( $post ) {
        $data = get_post_meta( $post->ID, '_lms_quiz_submission_data', true ); 
        $user_info = get_userdata( $data['user'] );
    ?>
        <table class="form-table">
            <tr>
                <th>Quiz</th>
                <td>
                    <a href="<?php echo admin_url( '/post.php?post='.$data['quiz'].'&action=edit' ); ?>">
                        <?php echo get_the_title( $data['quiz'] ); ?> (<?php echo $data['quiz']; ?>)
                    </a>
                </td>
            </tr>
            <?php if( $data['user'] != 0 ) : ?>
                <tr>
                    <th>User</th>
                    <td>
                        <a href="<?php echo admin_url( '/user-edit.php?user_id='.$data['user'] ); ?>"><?php echo $user_info->user_login; ?> (<?php echo $data['user']; ?>)</a>
                    </td>
                </tr>
            <?php endif; ?>
            <tr>
                <th>User IP</th>
                <td>
                    <?php echo $data['ip']; ?>
                </td>
            </tr>
            <tr>
                <th>Email</th>
                <td>
                    <?php 
                        if( $data['user'] != 0 ) {
                            echo $user_info->user_email; 
                         } else {
                            echo ( $data['email'] != '' ? $data['email'] : 'Not set' );
                         }
                     ?>
                </td>
            </tr>
            <tr>
                <th>Total Score</th>
                <td>
                    <?php echo $data['score']; ?>
                </td>
            </tr>
        </table>
        <h3>Answers</h3>
        <table class="form-table">
            <tr>
                <th></th>
                <th>Question</th>
                <th>Answer</th>
                <th>Score</th>
            </tr>
                <?php 
                    for ( $i = 0; $i<count( $data['answers'] ); $i++ ) {
                        $q = $i + 1;
                        echo '
                        <tr>
                            <th>Question '.$q.'</th>
                            <td>'.$data['answers'][$i]['question'].'</td>
                            <td>'.$data['answers'][$i]['answer'].'</td>
                            <td>'.$data['answers'][$i]['score'].'</td>
                        </tr>';
                    } 
                ?>
            <tr>
                <th colspan="3">Total</th>
                <td><?php echo $data['score']; ?></td>
            </tr>
        </table>
    <?php 
    }

    static public function quiz_meta_boxes() {
        add_meta_box(
            'lms_quiz_question_meta',
            __( 'Quiz Questions' ),
            array( self::get_instance(), 'quiz_meta_boxes_callback' ),
            'lms_quiz',
            'advanced',
            'core'
        );

        add_meta_box(
            'lms_quiz_settings_meta',
            __( 'Quiz Settings' ),
            array( self::get_instance(), 'quiz_settings_meta_boxes_callback' ),
            'lms_quiz',
            'advanced',
            'core'
        );
    }

    static public function quiz_settings_meta_boxes_callback( $post ) {
        $data = get_post_meta( $post->ID, '_lms_quiz_settings', true ); 
        if ( isset( $data['results_page'] ) && $data['results_page'] ) {
            $results_page_content = stripslashes( $data['results_page'] );
        } else {
            $results_page_content = "You scored {score} out of {total}";
        }
    ?>
        <table class="form-table">
            <tr>
                <th>Public?</th>
                <td>
                    <select name="lms_quiz_settings[public]">
                        <option value="0" <?php selected( $data['public'], 0 ); ?>>Public</option>
                        <option value="1" <?php selected( $data['public'], 1 ); ?>>Registered Users</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Allow Resubmission?</th>
                <td>
                    <select name="lms_quiz_settings[resubmission]">
                        <option value="0" <?php selected( $data['resubmission'], 0 ); ?>>No</option>
                        <option value="1" <?php selected( $data['resubmission'], 1 ); ?>>Yes</option>
                    </select>
                </td>
            </tr>
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

    static public function quiz_meta_boxes_callback( $post ) {
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

        if ( isset( $data[0] ) ) {

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
                                            
                                if ( isset( $question['image'] ) && $question['image'] != '' ) {
                                    $image      = wp_get_attachment_image_src( $question['image'], 'full' );
                                    $preview    = $image[0];
                                    $imgsrc     = $question['image'];
                                } else {
                                    $preview = '';
                                    $imgsrc = '';
                                }
                            ?>
                            <?php if ( $preview != '' ) { ?><img src="<?php echo $preview; ?>" style="width: 100%; height: auto;"><?php } ?>
                        </div>
                        <input type="hidden" name="lms_quiz_question[<?php echo $questions; ?>][image]'; ?>" value="<?php echo $imgsrc; ?>" id="box<?php echo $questions; ?>_image">
                        <?php 
                            if ( $imgsrc == '' ) { 
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
                                        <input type="number" value="<?php echo $answer['score']; ?>" name="lms_quiz_question[<?php echo $questions; ?>][answers][<?php echo $answers; ?>][score]" class="score regular-text" placeholder="Score">
                                    </td>
                                    <td>
                                        <button type="button" class="delete_quiz_answer">Delete Answer</button>
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
                        <th>Image</th>
                        <td>
                            <div id="box<?php echo $questions; ?>_preview" class="lms_quiz_image_preview">
                        </div>
                        <input type="hidden" name="lms_quiz_question[0][image]'; ?>" value="<?php echo $imgsrc; ?>" id="box<?php echo $questions; ?>_image">
                        <?php 
                            submit_button( 'Upload', 'primary upload_media_box', 'upload-box1', false, array( 'rel' => 'box'.$questions ) );
                        ?>
                        </td>
                    </tr>
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
                                        <input type="number" value="" name="lms_quiz_question[<?php echo $questions; ?>][answers][0][score]" class="score regular-text" placeholder="Score">
                                    </td>
                                    <td>
                                        <button type="button" class="delete_quiz_answer">Delete Answer</button>
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

    static public function save_quiz_questions_meta( $post_id, $post, $update ) {

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
        if ( isset( $_POST['lms_quiz_question'] ) ) {
            update_post_meta( $post_id, '_lms_quiz_questions', $_POST['lms_quiz_question'] );   
        }

        if ( isset( $_POST['lms_quiz_settings'] ) ) {
            update_post_meta( $post_id, '_lms_quiz_settings', $_POST['lms_quiz_settings'] );   
        }
        
    }
}
LMS_Quiz_System::get_instance();