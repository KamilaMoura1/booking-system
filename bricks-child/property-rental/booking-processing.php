<?php
/**
 * Booking validation and processing
 */

// Process the booking form submission
add_action('rwmb_frontend_after_process', 'prefix_process_booking_form', 10, 2);
function prefix_process_booking_form($config, $form_data) {
    // Only process our booking form
    if ($config['id'] !== 'booking-form') {
        return;
    }
    
    // Get form data
    $property_id = isset($form_data['property_id']) ? intval($form_data['property_id']) : 0;
    $property_post_id = isset($form_data['property_post_id']) ? intval($form_data['property_post_id']) : 0;
    $check_in = isset($form_data['check_in']) ? sanitize_text_field($form_data['check_in']) : '';
    $check_out = isset($form_data['check_out']) ? sanitize_text_field($form_data['check_out']) : '';
    $adults = isset($form_data['adults']) ? intval($form_data['adults']) : 1;
    $children = isset($form_data['children']) ? intval($form_data['children']) : 0;
    $babies = isset($form_data['babies']) ? intval($form_data['babies']) : 0;
    $first_name = isset($form_data['first_name']) ? sanitize_text_field($form_data['first_name']) : '';
    $last_name = isset($form_data['last_name']) ? sanitize_text_field($form_data['last_name']) : '';
    $email = isset($form_data['email']) ? sanitize_email($form_data['email']) : '';
    $phone = isset($form_data['phone']) ? sanitize_text_field($form_data['phone']) : '';
    $special_requests = isset($form_data['special_requests']) ? sanitize_textarea_field($form_data['special_requests']) : '';
    
    // If property_post_id is not set, use property_id as the post ID
    if (!$property_post_id) {
        $property_post_id = $property_id;
    }
    
    // Validate property exists
    if (!$property_post_id || get_post_type($property_post_id) !== 'property') {
        wp_die(__('Invalid property selected.', 'textdomain'));
    }
    
    // Validate dates
    if (!$check_in || !$check_out) {
        wp_die(__('Please select valid dates for your booking.', 'textdomain'));
    }
    
    $check_in_date = date_create($check_in);
    $check_out_date = date_create($check_out);
    
    if (!$check_in_date || !$check_out_date || $check_in_date >= $check_out_date) {
        wp_die(__('Invalid date range selected.', 'textdomain'));
    }
    
    // Calculate number of nights
    $interval = date_diff($check_in_date, $check_out_date);
    $nights = $interval->days;
    
    // Validate minimum nights
    $min_days = intval(rwmb_meta('property_min_days', array(), $property_post_id));
    
    if ($nights < $min_days) {
        wp_die(sprintf(__('Minimum stay for this property is %d nights.', 'textdomain'), $min_days));
    }
    
    // Validate guest count
    $max_adults = intval(rwmb_meta('property_max_adults', array(), $property_post_id));
    $max_children = intval(rwmb_meta('property_max_children', array(), $property_post_id));
    $max_babies = intval(rwmb_meta('property_max_babies', array(), $property_post_id));
    
    if ($adults > $max_adults || $children > $max_children || $babies > $max_babies) {
        wp_die(__('The number of guests exceeds the property capacity.', 'textdomain'));
    }
    
    // Check if property is available for selected dates
    if (!is_property_available($property_post_id, $check_in, $check_out)) {
        wp_die(__('Property is not available for the selected dates.', 'textdomain'));
    }
    
    // Calculate total price including guest fee
    $total = calculate_booking_total($property_post_id, $check_in, $check_out, $adults, $children, $babies);
    
    // Create a new booking post
    $booking_id = wp_insert_post(array(
        'post_title'     => sprintf(__('Booking for %s by %s %s', 'textdomain'), get_the_title($property_post_id), $first_name, $last_name),
        'post_status'    => 'publish',
        'post_type'      => 'booking',
    ));
    
    if (is_wp_error($booking_id)) {
        wp_die($booking_id->get_error_message());
    }
    
    // Format dates consistently in Y-m-d format
    $formatted_check_in = date('Y-m-d', strtotime($check_in));
    $formatted_check_out = date('Y-m-d', strtotime($check_out));
    
    // Save all the booking meta
    update_post_meta($booking_id, 'booking_property_id', $property_post_id); // Use property_post_id here
    update_post_meta($booking_id, 'booking_first_name', $first_name);
    update_post_meta($booking_id, 'booking_last_name', $last_name);
    update_post_meta($booking_id, 'booking_email', $email);
    update_post_meta($booking_id, 'booking_phone', $phone);
    update_post_meta($booking_id, 'booking_check_in', $formatted_check_in);
    update_post_meta($booking_id, 'booking_check_out', $formatted_check_out);
    update_post_meta($booking_id, 'booking_adults', $adults);
    update_post_meta($booking_id, 'booking_children', $children);
    update_post_meta($booking_id, 'booking_babies', $babies);
    update_post_meta($booking_id, 'booking_special_requests', $special_requests);
    update_post_meta($booking_id, 'booking_nights', $nights);
    update_post_meta($booking_id, 'booking_total', $total);
    update_post_meta($booking_id, 'booking_status', 'pending');
    update_post_meta($booking_id, 'booking_payment_status', 'pending');
    
    // Store the booking ID in a session for the payment page
    if (!session_id()) {
        session_start();
    }
    $_SESSION['current_booking_id'] = $booking_id;
    
    // Store booking details in session for persistence
    $_SESSION['booking_property_id'] = $property_post_id; // Use property_post_id here
    $_SESSION['booking_check_in'] = $formatted_check_in;
    $_SESSION['booking_check_out'] = $formatted_check_out;
    $_SESSION['booking_adults'] = $adults;
    $_SESSION['booking_children'] = $children;
    $_SESSION['booking_babies'] = $babies;
    
    // If we're using the redirect confirmation method, add the booking ID to the URL
    if ($config['confirmation']['type'] === 'redirect') {
        $config['confirmation']['url'] = str_replace('{ID}', $booking_id, $config['confirmation']['url']);
    }
}

/**
 * Check if a property is available for the selected dates
 */
function is_property_available( $property_id, $check_in, $check_out ) {
    // Convert to proper date format
    $check_in_date = date( 'Y-m-d', strtotime( $check_in ) );
    $check_out_date = date( 'Y-m-d', strtotime( $check_out ) );
    
    // Get unavailable dates from property meta
    $unavailable_dates = rwmb_meta( 'property_unavailable_dates', array(), $property_id );
    
    if ( !empty( $unavailable_dates ) ) {
        foreach ( $unavailable_dates as $unavailable_period ) {
            $start_date = $unavailable_period['from'];
            $end_date = $unavailable_period['to'];
            
            // Check if dates overlap
            if ( 
                ($check_in_date >= $start_date && $check_in_date <= $end_date) || 
                ($check_out_date >= $start_date && $check_out_date <= $end_date) || 
                ($check_in_date <= $start_date && $check_out_date >= $end_date) 
            ) {
                return false;
            }
        }
    }
    
    // Check existing bookings
    $existing_bookings = get_posts( array(
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
                'value' => array( 'pending', 'confirmed' ),
                'compare' => 'IN',
            ),
        ),
    ) );
    
    if ( !empty( $existing_bookings ) ) {
        foreach ( $existing_bookings as $booking ) {
            $booking_check_in = get_post_meta( $booking->ID, 'booking_check_in', true );
            $booking_check_out = get_post_meta( $booking->ID, 'booking_check_out', true );
            
            $booking_check_in_date = date( 'Y-m-d', strtotime( $booking_check_in ) );
            $booking_check_out_date = date( 'Y-m-d', strtotime( $booking_check_out ) );
            
            // Check if dates overlap
            if ( 
                ($check_in_date >= $booking_check_in_date && $check_in_date < $booking_check_out_date) || 
                ($check_out_date > $booking_check_in_date && $check_out_date <= $booking_check_out_date) || 
                ($check_in_date <= $booking_check_in_date && $check_out_date >= $booking_check_out_date) 
            ) {
                return false;
            }
        }
    }
    
    return true;
}

/**
 * Calculate the total price for a booking
 */
function calculate_booking_total( $property_id, $check_in, $check_out, $adults, $children, $babies ) {
    // Get property price
    $price_per_night = floatval( rwmb_meta( 'property_price', array(), $property_id ) );
    
    // Calculate number of nights
    $check_in_date = date_create( $check_in );
    $check_out_date = date_create( $check_out );
    $interval = date_diff( $check_in_date, $check_out_date );
    $nights = $interval->days;
    
    // Calculate base price
    $base_price = $price_per_night * $nights;
    
    // Add guest fee (3€ per day per guest)
    $guest_fee = ($adults + $children) * 3 * $nights;
    
    // Calculate total
    $total = $base_price + $guest_fee;
    
    // Apply any additional logic for total calculation here
    // For example, seasonal pricing, discounts, etc.
    
    return $total;
}

/**
 * Pre-fill the booking form with values from URL - Complete rewrite with direct approach
 */
add_filter('rwmb_frontend_field_value', 'prefix_pre_fill_booking_form', 10, 4);
function prefix_pre_fill_booking_form($value, $field, $form_data, $post_id) {
    // Only for our booking form
    if (!isset($form_data['id']) || $form_data['id'] !== 'booking-form') {
        return $value;
    }
    
    // Direct mapping of field IDs to GET parameters
    switch ($field['id']) {
        case 'property_id':
            return isset($_GET['property_id']) ? intval($_GET['property_id']) : $value;
            
        case 'check_in':
            if (isset($_GET['check_in']) && !empty($_GET['check_in'])) {
                // Ensure proper format for date field
                return date('Y-m-d', strtotime($_GET['check_in']));
            }
            return $value;
            
        case 'check_out':
            if (isset($_GET['check_out']) && !empty($_GET['check_out'])) {
                // Ensure proper format for date field
                return date('Y-m-d', strtotime($_GET['check_out']));
            }
            return $value;
            
        case 'adults':
            return isset($_GET['adults']) ? intval($_GET['adults']) : $value;
            
        case 'children':
            return isset($_GET['children']) ? intval($_GET['children']) : $value;
            
        case 'babies':
            return isset($_GET['babies']) ? intval($_GET['babies']) : $value;
            
        default:
            return $value;
    }
}

/**
 * Add property details to the booking page
 */
add_action( 'rwmb_frontend_before_form', 'prefix_show_property_details' );
function prefix_show_property_details( $config ) {
    // Only for our booking form
    if ( $config['id'] !== 'booking-form' ) {
        return;
    }
    
    // Check if property ID is provided
    if ( !isset( $_GET['property_id'] ) ) {
        // Try to get from session
        if ( !session_id() ) {
            session_start();
        }
        
        if ( !isset( $_SESSION['booking_property_id'] ) ) {
            return;
        }
        
        $property_id = intval( $_SESSION['booking_property_id'] );
    } else {
        $property_id = intval( $_GET['property_id'] );
    }
    
    // Get dates and guest counts from URL or session
if (PHP_SESSION_NONE === session_status()) {
    // Try to start the session only if it's not already started
    @session_start();
}
    
    // Check URL parameters first, then session variables as fallback
    $check_in = isset( $_GET['check_in'] ) && !empty( $_GET['check_in'] ) 
        ? sanitize_text_field( $_GET['check_in'] ) 
        : ( isset( $_SESSION['booking_check_in'] ) ? $_SESSION['booking_check_in'] : '' );
        
    $check_out = isset( $_GET['check_out'] ) && !empty( $_GET['check_out'] ) 
        ? sanitize_text_field( $_GET['check_out'] ) 
        : ( isset( $_SESSION['booking_check_out'] ) ? $_SESSION['booking_check_out'] : '' );
        
    $adults = isset( $_GET['adults'] ) && !empty( $_GET['adults'] ) 
        ? intval( $_GET['adults'] ) 
        : ( isset( $_SESSION['booking_adults'] ) ? intval( $_SESSION['booking_adults'] ) : 1 );
        
    $children = isset( $_GET['children'] ) && !empty( $_GET['children'] ) 
        ? intval( $_GET['children'] ) 
        : ( isset( $_SESSION['booking_children'] ) ? intval( $_SESSION['booking_children'] ) : 0 );
        
    $babies = isset( $_GET['babies'] ) && !empty( $_GET['babies'] ) 
        ? intval( $_GET['babies'] ) 
        : ( isset( $_SESSION['booking_babies'] ) ? intval( $_SESSION['booking_babies'] ) : 0 );
    
    // Ensure dates are properly formatted
    if ( !empty( $check_in ) && strtotime( $check_in ) ) {
        $check_in = date( 'Y-m-d', strtotime( $check_in ) );
    }
    
    if ( !empty( $check_out ) && strtotime( $check_out ) ) {
        $check_out = date( 'Y-m-d', strtotime( $check_out ) );
    }
    
    // Calculate nights and total
    $nights = 0;
    $total = 0;
    
    if ( $check_in && $check_out ) {
        $check_in_date = date_create( $check_in );
        $check_out_date = date_create( $check_out );
        
        if ( $check_in_date && $check_out_date ) {
            $interval = date_diff( $check_in_date, $check_out_date );
            $nights = $interval->days;
            $total = calculate_booking_total( $property_id, $check_in, $check_out, $adults, $children, $babies );
        }
    }
    
    // Display property details
    ?>
    <div class="booking-property-details">
        <h2><?php echo get_the_title( $property_id ); ?></h2>
        
        <?php
        // Get the first image from the gallery
        $gallery_images = rwmb_meta( 'property_gallery', array( 'size' => 'thumbnail' ), $property_id );
        $featured_image = '';
        
        if ( !empty( $gallery_images ) ) {
            $first_image = reset( $gallery_images );
            $featured_image = $first_image['url'];
        } elseif ( has_post_thumbnail( $property_id ) ) {
            $featured_image = get_the_post_thumbnail_url( $property_id, 'thumbnail' );
        }
        ?>
        
        <?php if ( !empty( $featured_image ) ) : ?>
            <div class="property-image">
                <img src="<?php echo esc_url( $featured_image ); ?>" alt="<?php echo esc_attr( get_the_title( $property_id ) ); ?>">
            </div>
        <?php endif; ?>
        
        <div class="booking-details">
            <?php if ( !empty( $check_in ) && strtotime( $check_in ) ) : ?>
                <div class="booking-detail">
                    <span class="detail-label"><?php _e( 'Check-in', 'textdomain' ); ?></span>
                    <span class="detail-value"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $check_in ) ); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ( !empty( $check_out ) && strtotime( $check_out ) ) : ?>
                <div class="booking-detail">
                    <span class="detail-label"><?php _e( 'Check-out', 'textdomain' ); ?></span>
                    <span class="detail-value"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $check_out ) ); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ( $nights > 0 ) : ?>
                <div class="booking-detail">
                    <span class="detail-label"><?php _e( 'Nights', 'textdomain' ); ?></span>
                    <span class="detail-value"><?php echo esc_html( $nights ); ?></span>
                </div>
            <?php endif; ?>
            
            <div class="booking-detail">
                <span class="detail-label"><?php _e( 'Guests', 'textdomain' ); ?></span>
                <span class="detail-value">
                    <?php
                    $guests_text = sprintf( _n( '%d Adult', '%d Adults', $adults, 'textdomain' ), $adults );
                    
                    if ( $children > 0 ) {
                        $guests_text .= ', ' . sprintf( _n( '%d Child', '%d Children', $children, 'textdomain' ), $children );
                    }
                    
                    if ( $babies > 0 ) {
                        $guests_text .= ', ' . sprintf( _n( '%d Baby', '%d Babies', $babies, 'textdomain' ), $babies );
                    }
                    
                    echo esc_html( $guests_text );
                    ?>
                </span>
            </div>
            
            <?php if ( $total > 0 ) : ?>
                <div class="booking-detail booking-total">
                    <span class="detail-label"><?php _e( 'Total Price', 'textdomain' ); ?></span>
                    <span class="detail-value">€<?php echo number_format( $total, 2 ); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <style>
    .booking-property-details {
        background: #f5f5f5;
        padding: 20px;
        margin-bottom: 30px;
        border-radius: 8px;
    }
    
    .booking-property-details h2 {
        margin-top: 0;
        margin-bottom: 15px;
    }
    
    .property-image {
        margin-bottom: 15px;
    }
    
    .property-image img {
        max-width: 100%;
        height: auto;
        border-radius: 4px;
    }
    
    .booking-details {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    .booking-detail {
        padding: 10px;
        background: white;
        border-radius: 4px;
    }
    
    .booking-total {
        grid-column: 1 / -1;
        background: #e6f7ff;
        font-weight: bold;
    }
    
    .detail-label {
        display: block;
        font-size: 0.9em;
        color: #666;
    }
    
    .detail-value {
        display: block;
        font-weight: bold;
        font-size: 1.1em;
    }
    
    @media (max-width: 768px) {
        .booking-details {
            grid-template-columns: 1fr;
        }
    }
    </style>
    <?php
}


// Add this function to your theme's functions.php or your plugin file
add_action('wp_footer', 'debug_booking_parameters');
function debug_booking_parameters() {
    if (!isset($_GET['property_id'])) {
        return;
    }
    
    // Only show this when accessing the booking page with parameters
    echo '<!-- DEBUG: URL Parameters -->';
    echo '<!-- property_id: ' . esc_html($_GET['property_id']) . ' -->';
    
    if (isset($_GET['check_in'])) {
        echo '<!-- check_in: ' . esc_html($_GET['check_in']) . ' -->';
        echo '<!-- check_in formatted: ' . esc_html(date('Y-m-d', strtotime($_GET['check_in']))) . ' -->';
    }
    
    if (isset($_GET['check_out'])) {
        echo '<!-- check_out: ' . esc_html($_GET['check_out']) . ' -->';
        echo '<!-- check_out formatted: ' . esc_html(date('Y-m-d', strtotime($_GET['check_out']))) . ' -->';
    }
    
    if (isset($_GET['adults'])) {
        echo '<!-- adults: ' . esc_html($_GET['adults']) . ' -->';
    }
    
    if (isset($_GET['children'])) {
        echo '<!-- children: ' . esc_html($_GET['children']) . ' -->';
    }
    
    echo '<!-- END DEBUG -->';
}