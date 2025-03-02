<?php
/**
 * Add function to filter properties by availability and guests
 * Add this to your theme's functions.php or custom plugin
 */
function filter_properties_by_availability( $query ) {
    if ( !is_admin() && $query->is_main_query() && is_post_type_archive( 'property' ) ) {
        
        // Check if filter parameters are set
        if ( isset( $_GET['check_in'] ) && isset( $_GET['check_out'] ) ) {
            $check_in = sanitize_text_field( $_GET['check_in'] );
            $check_out = sanitize_text_field( $_GET['check_out'] );
            
            // Convert to proper date format
            $check_in_date = date('Y-m-d', strtotime($check_in));
            $check_out_date = date('Y-m-d', strtotime($check_out));
            
            // Get all properties
            $all_properties = get_posts( array(
                'post_type' => 'property',
                'posts_per_page' => -1,
                'fields' => 'ids',
            ) );
            
            $available_properties = array();
            
            // Check each property for availability
            foreach ( $all_properties as $property_id ) {
                $unavailable_dates = rwmb_meta( 'property_unavailable_dates', array(), $property_id );
                $is_available = true;
                
                if ( !empty( $unavailable_dates ) ) {
                    foreach ( $unavailable_dates as $unavailable_period ) {
                        // Ensure dates are properly formatted and valid
                        $start_date = isset($unavailable_period['from']) ? $unavailable_period['from'] : '';
                        $end_date = isset($unavailable_period['to']) ? $unavailable_period['to'] : '';
                        
                        // Skip if dates are empty
                        if (empty($start_date) || empty($end_date)) {
                            continue;
                        }
                        
                        // Convert to timestamp for reliable comparison
                        $start_timestamp = strtotime($start_date);
                        $end_timestamp = strtotime($end_date);
                        $check_in_timestamp = strtotime($check_in_date);
                        $check_out_timestamp = strtotime($check_out_date);
                        
                        // Check if dates overlap using timestamp comparison
                        if ( 
                            ($check_in_timestamp >= $start_timestamp && $check_in_timestamp <= $end_timestamp) || 
                            ($check_out_timestamp >= $start_timestamp && $check_out_timestamp <= $end_timestamp) || 
                            ($check_in_timestamp <= $start_timestamp && $check_out_timestamp >= $end_timestamp) 
                        ) {
                            $is_available = false;
                            break;
                        }
                    }
                }
                
                // Check minimum days requirement
                $min_days = intval( rwmb_meta( 'property_min_days', array(), $property_id ) );
                $booking_days = intval( ( strtotime( $check_out_date ) - strtotime( $check_in_date ) ) / ( 60 * 60 * 24 ) );
                
                if ( $booking_days < $min_days ) {
                    $is_available = false;
                }
                
                if ( $is_available ) {
                    // Check guests capacity
                    if ( isset( $_GET['adults'] ) ) {
                        $adults = intval( $_GET['adults'] );
                        $max_adults = intval( rwmb_meta( 'property_max_adults', array(), $property_id ) );
                        
                        if ( $adults > $max_adults ) {
                            $is_available = false;
                        }
                    }
                    
                    if ( isset( $_GET['children'] ) ) {
                        $children = intval( $_GET['children'] );
                        $max_children = intval( rwmb_meta( 'property_max_children', array(), $property_id ) );
                        
                        if ( $children > $max_children ) {
                            $is_available = false;
                        }
                    }
                }
                
                if ( $is_available ) {
                    $available_properties[] = $property_id;
                }
            }
            
            if ( empty( $available_properties ) ) {
                $available_properties = array( 0 ); // No properties available
            }
            
            $query->set( 'post__in', $available_properties );
        }
        
        // Handle property type filtering
        if ( isset( $_GET['property_type'] ) && !empty( $_GET['property_type'] ) ) {
            $property_type = sanitize_text_field( $_GET['property_type'] );
            
            $tax_query = array(
                array(
                    'taxonomy' => 'property_type',
                    'field'    => 'slug',
                    'terms'    => $property_type,
                ),
            );
            
            $query->set( 'tax_query', $tax_query );
        }
    }
}
add_action( 'pre_get_posts', 'filter_properties_by_availability' );