<?php

add_action( 'init', 'register_post_types' );

function register_post_types() {
        
    $quiz = array(
        'labels'              => array(
            'name'               => _x( 'Quiz', 'post type general name' ),
            'singular_name'      => _x( 'Quiz', 'post type singular name' ),
            'menu_name'          => _x( 'Quiz', 'admin menu' ),
            'name_admin_bar'     => _x( 'Quiz', 'add new on admin bar' ),
            'add_new'            => _x( 'Add New Quiz', 'book' ),
            'add_new_item'       => __( 'Add New Quiz' ),
            'new_item'           => __( 'New Quiz' ),
            'edit_item'          => __( 'Edit Quiz' ),
            'view_item'          => __( 'View Quiz' ),
            'all_items'          => __( 'All Quizzes' ),
            'search_items'       => __( 'Search Quizzes' ),
            'parent_item_colon'  => __( 'Parent Quiz:' ),
            'not_found'          => __( 'No quizzes found.' ),
            'not_found_in_trash' => __( 'No quizzes found in Trash.' )
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
            'name'               => _x( 'Submissions', 'post type general name' ),
            'singular_name'      => _x( 'Submission', 'post type singular name' ),
            'menu_name'          => _x( 'Submissions', 'admin menu' ),
            'name_admin_bar'     => _x( 'Submissions', 'add new on admin bar' ),
            'add_new'            => _x( 'Add New Submission', 'book' ),
            'add_new_item'       => __( 'Add New Submissions' ),
            'new_item'           => __( 'New Submission' ),
            'edit_item'          => __( 'Edit Submission' ),
            'view_item'          => __( 'View Submission' ),
            'all_items'          => __( 'All Submissions' ),
            'search_items'       => __( 'Search Submissions' ),
            'parent_item_colon'  => __( 'Parent Submission:' ),
            'not_found'          => __( 'No submissions found.' ),
            'not_found_in_trash' => __( 'No submissions found in Trash.' )
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