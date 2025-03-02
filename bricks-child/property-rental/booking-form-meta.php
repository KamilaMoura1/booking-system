<?php
/**
 * Register booking form with Metabox
 */
add_filter( 'rwmb_meta_boxes', 'prefix_register_booking_form' );

function prefix_register_booking_form( $meta_boxes ) {
    $meta_boxes[] = array(
        'title'      => __( 'Booking Form', 'textdomain' ),
        'id'         => 'booking-form',
        'type'       => 'form',
        'post_type'  => 'mb-form',
        'fields'     => array(
            array(
                'name' => __('Property Post ID', 'textdomain'),
                'id' => 'property_post_id',
                'type' => 'hidden',
            ),
            array(
                'name'    => __( 'Property', 'textdomain' ),
                'id'      => 'property_id',
                'type'    => 'hidden',
            ),
            array(
                'name'    => __( 'First Name', 'textdomain' ),
                'id'      => 'first_name',
                'type'    => 'text',
                'required' => true,
            ),
            array(
                'name'    => __( 'Last Name', 'textdomain' ),
                'id'      => 'last_name',
                'type'    => 'text',
                'required' => true,
            ),
            array(
                'name'    => __( 'Email', 'textdomain' ),
                'id'      => 'email',
                'type'    => 'email',
                'required' => true,
            ),
            array(
                'name'    => __( 'Phone', 'textdomain' ),
                'id'      => 'phone',
                'type'    => 'text',
                'required' => true,
            ),
            array(
                'name'    => __( 'Check-in Date', 'textdomain' ),
                'id'      => 'check_in',
                'type'    => 'date',
                'required' => true,
                'readonly' => true,
            ),
            array(
                'name'    => __( 'Check-out Date', 'textdomain' ),
                'id'      => 'check_out',
                'type'    => 'date',
                'required' => true,
                'readonly' => true,
            ),
            array(
                'name'    => __( 'Adults', 'textdomain' ),
                'id'      => 'adults',
                'type'    => 'number',
                'required' => true,
                'readonly' => true,
            ),
            array(
                'name'    => __( 'Children', 'textdomain' ),
                'id'      => 'children',
                'type'    => 'number',
                'required' => false,
                'readonly' => true,
            ),
            array(
                'name'    => __( 'Babies', 'textdomain' ),
                'id'      => 'babies',
                'type'    => 'number',
                'required' => false,
                'readonly' => true,
            ),
            array(
                'name'    => __( 'Special Requests', 'textdomain' ),
                'id'      => 'special_requests',
                'type'    => 'textarea',
            ),
        ),
        'submit_button' => __( 'Proceed to Payment', 'textdomain' ),
        'confirmation'  => array(
            'type'      => 'redirect',
            'url'       => home_url( '/payment/?booking_id={ID}' ),
        ),
    );
    
    // Also register meta boxes for the Booking CPT
    $meta_boxes[] = array(
        'title'      => __( 'Booking Details', 'textdomain' ),
        'id'         => 'booking-details',
        'post_types' => array( 'booking' ),
        'fields'     => array(
            array(
                'name'    => __( 'Property', 'textdomain' ),
                'id'      => 'booking_property_id',
                'type'    => 'post',
                'post_type' => 'property',
                'required' => true,
            ),
            array(
                'name'    => __( 'First Name', 'textdomain' ),
                'id'      => 'booking_first_name',
                'type'    => 'text',
                'required' => true,
            ),
            array(
                'name'    => __( 'Last Name', 'textdomain' ),
                'id'      => 'booking_last_name',
                'type'    => 'text',
                'required' => true,
            ),
            array(
                'name'    => __( 'Email', 'textdomain' ),
                'id'      => 'booking_email',
                'type'    => 'email',
                'required' => true,
            ),
            array(
                'name'    => __( 'Phone', 'textdomain' ),
                'id'      => 'booking_phone',
                'type'    => 'text',
                'required' => true,
            ),
            array(
                'name'    => __( 'Check-in Date', 'textdomain' ),
                'id'      => 'booking_check_in',
                'type'    => 'date',
                'required' => true,
            ),
            array(
                'name'    => __( 'Check-out Date', 'textdomain' ),
                'id'      => 'booking_check_out',
                'type'    => 'date',
                'required' => true,
            ),
            array(
                'name'    => __( 'Adults', 'textdomain' ),
                'id'      => 'booking_adults',
                'type'    => 'number',
                'required' => true,
            ),
            array(
                'name'    => __( 'Children', 'textdomain' ),
                'id'      => 'booking_children',
                'type'    => 'number',
                'required' => false,
            ),
            array(
                'name'    => __( 'Babies', 'textdomain' ),
                'id'      => 'booking_babies',
                'type'    => 'number',
                'required' => false,
            ),
            array(
                'name'    => __( 'Special Requests', 'textdomain' ),
                'id'      => 'booking_special_requests',
                'type'    => 'textarea',
            ),
            array(
                'name'    => __( 'Total Amount (€)', 'textdomain' ),
                'id'      => 'booking_total',
                'type'    => 'number',
                'required' => true,
                'step'    => 0.01,
            ),
            array(
                'name'    => __( 'Status', 'textdomain' ),
                'id'      => 'booking_status',
                'type'    => 'select',
                'options' => array(
                    'pending'   => __( 'Pending', 'textdomain' ),
                    'confirmed' => __( 'Confirmed', 'textdomain' ),
                    'cancelled' => __( 'Cancelled', 'textdomain' ),
                    'completed' => __( 'Completed', 'textdomain' ),
                ),
                'required' => true,
                'std'      => 'pending',
            ),
            array(
                'name'    => __( 'Payment Status', 'textdomain' ),
                'id'      => 'booking_payment_status',
                'type'    => 'select',
                'options' => array(
                    'pending'   => __( 'Pending', 'textdomain' ),
                    'paid'      => __( 'Paid', 'textdomain' ),
                    'failed'    => __( 'Failed', 'textdomain' ),
                    'refunded'  => __( 'Refunded', 'textdomain' ),
                ),
                'required' => true,
                'std'      => 'pending',
            ),
            array(
                'name'    => __( 'WooCommerce Order ID', 'textdomain' ),
                'id'      => 'booking_order_id',
                'type'    => 'text',
            ),
        ),
    );
    
    return $meta_boxes;
}