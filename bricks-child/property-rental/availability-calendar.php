<?php
/**
 * Add availability calendar to property pages
 */

// Enqueue scripts and styles
add_action( 'wp_enqueue_scripts', 'prefix_enqueue_calendar_scripts' );
function prefix_enqueue_calendar_scripts() {
    if ( is_singular( 'property' ) ) {
        wp_enqueue_style( 'fullcalendar-css', 'https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css' );
        wp_enqueue_script( 'moment-js', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js', array(), '2.29.1', true );
        wp_enqueue_script( 'fullcalendar-js', 'https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js', array( 'jquery', 'moment-js' ), '3.10.2', true );
        
        // Add inline script for calendar initialization
        wp_add_inline_script( 'fullcalendar-js', 'jQuery(document).ready(function($) {
            $("#property-availability-calendar").fullCalendar({
                header: {
                    left: "prev,next today",
                    center: "title",
                    right: "month,basicWeek"
                },
                editable: false,
                eventColor: "#e74c3c",
                events: propertyEvents
            });
        });', 'after' );
    }
}

// Add availability calendar to property single template
add_action( 'prefix_after_property_content', 'prefix_add_availability_calendar' );
function prefix_add_availability_calendar() {
    if ( !is_singular( 'property' ) ) {
        return;
    }
    
    $property_id = get_the_ID();
    
    // Get unavailable dates
    $unavailable_dates = rwmb_meta( 'property_unavailable_dates', array(), $property_id );
    
    // Get booked dates from bookings
    $booked_dates = prefix_get_property_booked_dates( $property_id );
    
    // Merge unavailable and booked dates
    $all_unavailable_dates = array();
    
    if ( !empty( $unavailable_dates ) && is_array( $unavailable_dates ) ) {
        foreach ( $unavailable_dates as $period ) {
            $all_unavailable_dates[] = array(
                'title' => __( 'Unavailable', 'textdomain' ),
                'start' => $period['from'],
                'end' => date( 'Y-m-d', strtotime( $period['to'] . ' +1 day' ) ), // Add 1 day to include the end date
                'color' => '#e74c3c'
            );
        }
    }
    
    if ( !empty( $booked_dates ) ) {
        foreach ( $booked_dates as $booking ) {
            $all_unavailable_dates[] = array(
                'title' => __( 'Booked', 'textdomain' ),
                'start' => $booking['check_in'],
                'end' => date( 'Y-m-d', strtotime( $booking['check_out'] . ' +1 day' ) ), // Add 1 day to include the end date
                'color' => '#e74c3c'
            );
        }
    }
    
    // Convert to JSON for JavaScript
    $events_json = json_encode( $all_unavailable_dates );
    
    // Output calendar container and script
    ?>
    <div class="property-availability">
        <h2><?php _e( 'Availability Calendar', 'textdomain' ); ?></h2>
        <div id="property-availability-calendar"></div>
    </div>
    
    <script>
    var propertyEvents = <?php echo $events_json; ?>;
    </script>
    
    <style>
    .property-availability {
        margin-top: 40px;
        margin-bottom: 40px;
    }
    
    #property-availability-calendar {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .fc-day-grid-event {
        padding: 5px !important;
    }
    </style>
    <?php
}

/**
 * Get booked dates for a property from existing bookings
 */
function prefix_get_property_booked_dates( $property_id ) {
    $booked_dates = array();
    
    $bookings = get_posts( array(
        'post_type' => 'booking',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'booking_property_id',
                'value' => $property_id,
            ),
            array(
                'key' => 'booking_status',
                'value' => array( 'confirmed', 'pending' ),
                'compare' => 'IN',
            ),
        ),
    ) );
    
    if ( !empty( $bookings ) ) {
        foreach ( $bookings as $booking ) {
            $check_in = get_post_meta( $booking->ID, 'booking_check_in', true );
            $check_out = get_post_meta( $booking->ID, 'booking_check_out', true );
            
            if ( $check_in && $check_out ) {
                $booked_dates[] = array(
                    'check_in' => date( 'Y-m-d', strtotime( $check_in ) ),
                    'check_out' => date( 'Y-m-d', strtotime( $check_out ) ),
                );
            }
        }
    }
    
    return $booked_dates;
}
