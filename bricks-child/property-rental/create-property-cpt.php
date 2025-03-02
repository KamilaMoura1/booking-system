<?php
/**
 * Register Property Custom Post Type using Metabox
 */
add_filter( 'rwmb_meta_boxes', 'prefix_register_property_post_type' );

function prefix_register_property_post_type( $meta_boxes ) {
    // Register Property CPT
    if ( ! post_type_exists( 'property' ) ) {
        $labels = array(
            'name'                  => _x( 'Properties', 'Post type general name', 'textdomain' ),
            'singular_name'         => _x( 'Property', 'Post type singular name', 'textdomain' ),
            'menu_name'             => _x( 'Properties', 'Admin Menu text', 'textdomain' ),
            'name_admin_bar'        => _x( 'Property', 'Add New on Toolbar', 'textdomain' ),
            'add_new'               => __( 'Add New', 'textdomain' ),
            'add_new_item'          => __( 'Add New Property', 'textdomain' ),
            'new_item'              => __( 'New Property', 'textdomain' ),
            'edit_item'             => __( 'Edit Property', 'textdomain' ),
            'view_item'             => __( 'View Property', 'textdomain' ),
            'all_items'             => __( 'All Properties', 'textdomain' ),
            'search_items'          => __( 'Search Properties', 'textdomain' ),
            'not_found'             => __( 'No properties found.', 'textdomain' ),
            'not_found_in_trash'    => __( 'No properties found in Trash.', 'textdomain' ),
            'featured_image'        => __( 'Property Featured Image', 'textdomain' ),
            'set_featured_image'    => __( 'Set featured image', 'textdomain' ),
            'remove_featured_image' => __( 'Remove featured image', 'textdomain' ),
            'use_featured_image'    => __( 'Use as featured image', 'textdomain' ),
        );
        
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'property' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-admin-home',
            'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
        );
        
        register_post_type( 'property', $args );
    }
    
    // Register Booking CPT
    if ( ! post_type_exists( 'booking' ) ) {
        $labels = array(
            'name'                  => _x( 'Bookings', 'Post type general name', 'textdomain' ),
            'singular_name'         => _x( 'Booking', 'Post type singular name', 'textdomain' ),
            'menu_name'             => _x( 'Bookings', 'Admin Menu text', 'textdomain' ),
            'name_admin_bar'        => _x( 'Booking', 'Add New on Toolbar', 'textdomain' ),
            'add_new'               => __( 'Add New', 'textdomain' ),
            'add_new_item'          => __( 'Add New Booking', 'textdomain' ),
            'new_item'              => __( 'New Booking', 'textdomain' ),
            'edit_item'             => __( 'Edit Booking', 'textdomain' ),
            'view_item'             => __( 'View Booking', 'textdomain' ),
            'all_items'             => __( 'All Bookings', 'textdomain' ),
            'search_items'          => __( 'Search Bookings', 'textdomain' ),
            'not_found'             => __( 'No bookings found.', 'textdomain' ),
            'not_found_in_trash'    => __( 'No bookings found in Trash.', 'textdomain' ),
        );
        
        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'booking' ),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-calendar-alt',
            'supports'           => array( 'title' ),
        );
        
        register_post_type( 'booking', $args );
    }
    
    return $meta_boxes;
}

// Register custom taxonomies for properties (optional)
function prefix_register_property_taxonomies() {
    // Property Type Taxonomy
    $labels = array(
        'name'              => _x( 'Property Types', 'taxonomy general name', 'textdomain' ),
        'singular_name'     => _x( 'Property Type', 'taxonomy singular name', 'textdomain' ),
        'search_items'      => __( 'Search Property Types', 'textdomain' ),
        'all_items'         => __( 'All Property Types', 'textdomain' ),
        'parent_item'       => __( 'Parent Property Type', 'textdomain' ),
        'parent_item_colon' => __( 'Parent Property Type:', 'textdomain' ),
        'edit_item'         => __( 'Edit Property Type', 'textdomain' ),
        'update_item'       => __( 'Update Property Type', 'textdomain' ),
        'add_new_item'      => __( 'Add New Property Type', 'textdomain' ),
        'new_item_name'     => __( 'New Property Type Name', 'textdomain' ),
        'menu_name'         => __( 'Property Types', 'textdomain' ),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'property-type' ),
    );

    register_taxonomy( 'property_type', array( 'property' ), $args );
}
add_action( 'init', 'prefix_register_property_taxonomies', 0 );
