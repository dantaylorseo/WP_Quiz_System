<?php
/**
 * Plugin Name: LMS Quiz System
 * Plugin URI: 
 * Description: 
 * Version: 1.0b
 * Author: Dan Taylor
 * Author URI: 
 * License:  GPLv2 or later
 */
 
 
// Extension directory
define("LMS_QUIZ_SYSTEM_DIR", WP_PLUGIN_DIR."/".basename( dirname( __FILE__ ) ) );
define("LMS_QUIZ_SYSTEM_URL", plugins_url( '', __FILE__ ) );

require_once( LMS_QUIZ_SYSTEM_DIR . "/includes/post_types.php" );
require_once( LMS_QUIZ_SYSTEM_DIR . "/includes/shortcodes.php" );

if( is_admin() ) {
	require_once( LMS_QUIZ_SYSTEM_DIR . "/classes/lms-quiz-system.class.php" );
}