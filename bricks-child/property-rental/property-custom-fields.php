<?php
/**
 * Register custom fields for Property CPT
 */
add_filter( 'rwmb_meta_boxes', 'prefix_register_property_meta_boxes' );

function prefix_register_property_meta_boxes( $meta_boxes ) {
    // Property Gallery
    $meta_boxes[] = array(
        'title'      => __( 'Property Gallery', 'textdomain' ),
        'id'         => 'property-gallery',
        'post_types' => array( 'property' ),
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => array(
            array(
                'name'             => __( 'Gallery', 'textdomain' ),
                'id'               => 'property_gallery',
                'type'             => 'image_advanced',
                'max_file_uploads' => 20,
                'desc'             => __( 'Upload multiple images for the property gallery', 'textdomain' ),
            ),
        ),
    );
    
    // Main Features
    $meta_boxes[] = array(
        'title'      => __( 'Main Features', 'textdomain' ),
        'id'         => 'property-features',
        'post_types' => array( 'property' ),
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => array(
            array(
                'name'    => __( 'Main Features', 'textdomain' ),
                'id'      => 'property_main_features',
                'type'    => 'checkbox_list',
                'options' => array(
                    'wifi'         => __( 'WiFi', 'textdomain' ),
                    'parking'      => __( 'Parking', 'textdomain' ),
                    'pool'         => __( 'Swimming Pool', 'textdomain' ),
                    'ac'           => __( 'Air Conditioning', 'textdomain' ),
                    'heating'      => __( 'Heating', 'textdomain' ),
                    'tv'           => __( 'TV', 'textdomain' ),
                    'kitchen'      => __( 'Kitchen', 'textdomain' ),
                    'washer'       => __( 'Washer', 'textdomain' ),
                    'dryer'        => __( 'Dryer', 'textdomain' ),
                    'elevator'     => __( 'Elevator', 'textdomain' ),
                ),
            ),
        ),
    );
    
    // Type of Accommodation
    $meta_boxes[] = array(
        'title'      => __( 'Accommodation Type', 'textdomain' ),
        'id'         => 'property-type',
        'post_types' => array( 'property' ),
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => array(
            array(
                'name'    => __( 'Type of Accommodation', 'textdomain' ),
                'id'      => 'property_accommodation_type',
                'type'    => 'radio',
                'options' => array(
                    'house'        => __( 'House', 'textdomain' ),
                    'apartment'    => __( 'Apartment', 'textdomain' ),
                    'villa'        => __( 'Villa', 'textdomain' ),
                    'cottage'      => __( 'Cottage', 'textdomain' ),
                    'cabin'        => __( 'Cabin', 'textdomain' ),
                ),
            ),
        ),
    );
    
    // Other Features
    $meta_boxes[] = array(
        'title'      => __( 'Other Features', 'textdomain' ),
        'id'         => 'property-other-features',
        'post_types' => array( 'property' ),
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => array(
            array(
                'name'    => __( 'Other Features', 'textdomain' ),
                'id'      => 'property_other_features',
                'type'    => 'checkbox_list',
                'options' => array(
                    'garden'       => __( 'Garden', 'textdomain' ),
                    'balcony'      => __( 'Balcony', 'textdomain' ),
                    'terrace'      => __( 'Terrace', 'textdomain' ),
                    'pet_friendly' => __( 'Pet Friendly', 'textdomain' ),
                    'sea_view'     => __( 'Sea View', 'textdomain' ),
                    'mountain_view'=> __( 'Mountain View', 'textdomain' ),
                    'bbq'          => __( 'BBQ', 'textdomain' ),
                    'fireplace'    => __( 'Fireplace', 'textdomain' ),
                ),
            ),
        ),
    );
    
    // House Rules
    $meta_boxes[] = array(
        'title'      => __( 'House Rules', 'textdomain' ),
        'id'         => 'property-rules',
        'post_types' => array( 'property' ),
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => array(
            array(
                'name'    => __( 'House Rules', 'textdomain' ),
                'id'      => 'property_house_rules',
                'type'    => 'checkbox_list',
                'options' => array(
                    'no_smoking'   => __( 'No Smoking', 'textdomain' ),
                    'no_parties'   => __( 'No Parties', 'textdomain' ),
                    'no_pets'      => __( 'No Pets', 'textdomain' ),
                    'quiet_hours'  => __( 'Quiet Hours', 'textdomain' ),
                ),
            ),
        ),
    );
    
    // Guests, Beds, Bathrooms
    $meta_boxes[] = array(
        'title'      => __( 'Property Details', 'textdomain' ),
        'id'         => 'property-details',
        'post_types' => array( 'property' ),
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => array(
            array(
                'name' => __('Property Post ID', 'textdomain'),
                'id' => 'property_post_id',
                'type' => 'hidden',
            ),
            array(
                'name'    => __( 'Property ID', 'textdomain' ),
                'id'      => 'property_id',
                'type'    => 'text',
                'desc'    => __( 'Unique identifier for the property', 'textdomain' ),
            ),
            array(
                'name'    => __( 'Max Adults', 'textdomain' ),
                'id'      => 'property_max_adults',
                'type'    => 'number',
                'min'     => 0,
                'step'    => 1,
            ),
            array(
                'name'    => __( 'Max Children', 'textdomain' ),
                'id'      => 'property_max_children',
                'type'    => 'number',
                'min'     => 0,
                'step'    => 1,
            ),
            array(
                'name'    => __( 'Max Babies', 'textdomain' ),
                'id'      => 'property_max_babies',
                'type'    => 'number',
                'min'     => 0,
                'step'    => 1,
            ),
            array(
                'name'    => __( 'Number of Beds', 'textdomain' ),
                'id'      => 'property_beds',
                'type'    => 'number',
                'min'     => 0,
                'step'    => 1,
            ),
            array(
                'name'    => __( 'Number of Bathrooms', 'textdomain' ),
                'id'      => 'property_bathrooms',
                'type'    => 'number',
                'min'     => 0,
                'step'    => 0.5, // Allow half bathrooms
            ),
            array(
                'name'    => __( 'Minimum Days for Reservation', 'textdomain' ),
                'id'      => 'property_min_days',
                'type'    => 'number',
                'min'     => 1,
                'step'    => 1,
                'std'     => 1, // Default value
            ),
            array(
                'name'    => __( 'Price per Night (€)', 'textdomain' ),
                'id'      => 'property_price',
                'type'    => 'number',
                'min'     => 0,
                'step'    => 0.01,
            ),
        ),
    );
    
    // Availability Calendar
    $meta_boxes[] = array(
        'title'      => __( 'Property Availability', 'textdomain' ),
        'id'         => 'property-availability',
        'post_types' => array( 'property' ),
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => array(
            array(
                'name'    => __( 'Unavailable Dates', 'textdomain' ),
                'id'      => 'property_unavailable_dates',
                'type'    => 'date_range',
                'multiple'=> true,
                'desc'    => __( 'Select dates when the property is unavailable', 'textdomain' ),
            ),
        ),
    );
    
    return $meta_boxes;
}
