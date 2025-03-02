<?php

/**
 * WooCommerce integration for property bookings
 */

// Create payment page
function prefix_create_payment_page()
{
    // Create a page for the payment process if it doesn't exist
    $payment_page = get_page_by_path('payment');

    if (!$payment_page) {
        wp_insert_post(array(
            'post_title'     => __('Complete Payment', 'textdomain'),
            'post_content'   => '[property_booking_payment]',
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'post_name'      => 'payment',
        ));
    }
}
register_activation_hook(__FILE__, 'prefix_create_payment_page');

// Register shortcode for payment page
add_shortcode('property_booking_payment', 'prefix_booking_payment_shortcode');
function prefix_booking_payment_shortcode()
{
    // Check if booking ID is provided
    if (!isset($_GET['booking_id'])) {
        return '<div class="error-message">' . __('Invalid booking.', 'textdomain') . '</div>';
    }

    $booking_id = intval($_GET['booking_id']);

    // Verify booking exists and is pending
    $booking = get_post($booking_id);

    if (!$booking || $booking->post_type !== 'booking') {
        return '<div class="error-message">' . __('Invalid booking.', 'textdomain') . '</div>';
    }

    $booking_status = get_post_meta($booking_id, 'booking_status', true);
    $payment_status = get_post_meta($booking_id, 'booking_payment_status', true);

    if ($booking_status !== 'pending' || $payment_status !== 'pending') {
        return '<div class="error-message">' . __('This booking has already been processed.', 'textdomain') . '</div>';
    }

    // Get booking details
    $property_id = get_post_meta($booking_id, 'booking_property_id', true);
    $first_name = get_post_meta($booking_id, 'booking_first_name', true);
    $last_name = get_post_meta($booking_id, 'booking_last_name', true);
    $email = get_post_meta($booking_id, 'booking_email', true);
    $check_in = get_post_meta($booking_id, 'booking_check_in', true);
    $check_out = get_post_meta($booking_id, 'booking_check_out', true);
    $adults = get_post_meta($booking_id, 'booking_adults', true);
    $children = get_post_meta($booking_id, 'booking_children', true);
    $nights = get_post_meta($booking_id, 'booking_nights', true);
    $total = get_post_meta($booking_id, 'booking_total', true);

    // Add to cart and redirect to checkout
    if (isset($_GET['create_order']) && $_GET['create_order'] == 1) {
        // Create WooCommerce order
        if (function_exists('wc_create_order')) {
            // Empty cart
            WC()->cart->empty_cart();

            // Create a product on-the-fly for this booking
            $order = wc_create_order();

            // Get or create a product for this property booking
            $product_id = prefix_get_booking_product($property_id);

            if (!$product_id) {
                return '<div class="error-message">' . __('Error creating booking product.', 'textdomain') . '</div>';
            }

            // Add item to order with booking details stored as item meta
            $item_id = $order->add_product(wc_get_product($product_id), 1, array(
                'subtotal' => $total,
                'total' => $total,
                'name' => sprintf(
                    __('Booking for %s (%s to %s)', 'textdomain'),
                    get_the_title($property_id),
                    date_i18n(get_option('date_format'), strtotime($check_in)),
                    date_i18n(get_option('date_format'), strtotime($check_out))
                ),
            ));

            // Store booking details as item meta
            if ($item_id) {
                wc_add_order_item_meta($item_id, '_booking_check_in', $check_in);
                wc_add_order_item_meta($item_id, '_booking_check_out', $check_out);
                wc_add_order_item_meta($item_id, '_booking_adults', $adults);
                wc_add_order_item_meta($item_id, '_booking_children', $children);
                wc_add_order_item_meta($item_id, '_booking_nights', $nights);
                wc_add_order_item_meta($item_id, '_booking_property_id', $property_id);
            }

            // Set customer data
            $order->set_address(array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
            ), 'billing');

            // Set order meta to link to the booking
            $order->update_meta_data('_booking_id', $booking_id);
            $order->update_meta_data('_booking_check_in', $check_in);
            $order->update_meta_data('_booking_check_out', $check_out);
            $order->update_meta_data('_booking_adults', $adults);
            $order->update_meta_data('_booking_children', $children);

            // Calculate totals
            $order->calculate_totals();

            // Save the order
            $order->save();

            // Update booking with order ID
            update_post_meta($booking_id, 'booking_order_id', $order->get_id());

            // Redirect to checkout
            wp_redirect($order->get_checkout_payment_url());
            exit;
        } else {
            return '<div class="error-message">' . __('WooCommerce is not active.', 'textdomain') . '</div>';
        }
    }

    // Display booking summary and payment button
    ob_start();
?>
    <div class="booking-summary">
        <h2><?php _e('Booking Summary', 'textdomain'); ?></h2>

        <div class="booking-property">
            <h3><?php echo get_the_title($property_id); ?></h3>

            <?php
            // Get the first image from the gallery
            $gallery_images = rwmb_meta('property_gallery', array('size' => 'medium'), $property_id);
            $featured_image = '';

            if (!empty($gallery_images)) {
                $first_image = reset($gallery_images);
                $featured_image = $first_image['url'];
            } elseif (has_post_thumbnail($property_id)) {
                $featured_image = get_the_post_thumbnail_url($property_id, 'medium');
            }
            ?>

            <?php if (!empty($featured_image)) : ?>
                <div class="property-image">
                    <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr(get_the_title($property_id)); ?>">
                </div>
            <?php endif; ?>
        </div>

        <div class="booking-details">
            <div class="booking-dates">
                <div class="booking-detail">
                    <span class="detail-label"><?php _e('Check-in', 'textdomain'); ?></span>
                    <span class="detail-value"><?php echo date_i18n(get_option('date_format'), strtotime($check_in)); ?></span>
                </div>

                <div class="booking-detail">
                    <span class="detail-label"><?php _e('Check-out', 'textdomain'); ?></span>
                    <span class="detail-value"><?php echo date_i18n(get_option('date_format'), strtotime($check_out)); ?></span>
                </div>

                <div class="booking-detail">
                    <span class="detail-label"><?php _e('Duration', 'textdomain'); ?></span>
                    <span class="detail-value"><?php printf(_n('%d Night', '%d Nights', $nights, 'textdomain'), $nights); ?></span>
                </div>
            </div>

            <div class="booking-guests">
                <div class="booking-detail">
                    <span class="detail-label"><?php _e('Guests', 'textdomain'); ?></span>
                    <span class="detail-value">
                        <?php
                        $guests_text = sprintf(_n('%d Adult', '%d Adults', $adults, 'textdomain'), $adults);

                        if ($children > 0) {
                            $guests_text .= ', ' . sprintf(_n('%d Child', '%d Children', $children, 'textdomain'), $children);
                        }

                        echo esc_html($guests_text);
                        ?>
                    </span>
                </div>
            </div>

            <div class="booking-customer">
                <div class="booking-detail">
                    <span class="detail-label"><?php _e('Name', 'textdomain'); ?></span>
                    <span class="detail-value"><?php echo esc_html("$first_name $last_name"); ?></span>
                </div>

                <div class="booking-detail">
                    <span class="detail-label"><?php _e('Email', 'textdomain'); ?></span>
                    <span class="detail-value"><?php echo esc_html($email); ?></span>
                </div>
            </div>
        </div>

        <div class="booking-price-breakdown">
            <h3><?php _e('Price Breakdown', 'textdomain'); ?></h3>

            <?php
            // Calculate price breakdown
            $price_per_night = floatval(rwmb_meta('property_price', array(), $property_id));
            $base_price = $price_per_night * $nights;
            $guest_fee = ($adults + intval($children)) * 3 * $nights;
            ?>

            <div class="price-item">
                <span class="price-label"><?php printf(__('Accommodation (%s x %d nights)', 'textdomain'), '€' . number_format($price_per_night, 2), $nights); ?></span>
                <span class="price-value">€<?php echo number_format($base_price, 2); ?></span>
            </div>

            <div class="price-item">
                <span class="price-label"><?php printf(__('Guest fee (%d guests x €3 x %d nights)', 'textdomain'), $adults + intval($children), $nights); ?></span>
                <span class="price-value">€<?php echo number_format($guest_fee, 2); ?></span>
            </div>

            <div class="price-item price-total">
                <span class="price-label"><?php _e('Total', 'textdomain'); ?></span>
                <span class="price-value">€<?php echo number_format($total, 2); ?></span>
            </div>
        </div>

        <div class="booking-actions">
            <a href="<?php echo esc_url(add_query_arg(array('booking_id' => $booking_id, 'create_order' => 1))); ?>" class="proceed-to-payment"><?php _e('Proceed to Payment', 'textdomain'); ?></a>
        </div>
    </div>

    <style>
        .booking-summary {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 8px;
        }

        .booking-summary h2 {
            margin-top: 0;
            margin-bottom: 20px;
            text-align: center;
        }

        .booking-property {
            margin-bottom: 30px;
            text-align: center;
        }

        .booking-property h3 {
            margin-top: 0;
        }

        .property-image {
            margin-top: 10px;
        }

        .property-image img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .booking-details {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .booking-dates,
        .booking-guests,
        .booking-customer {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .booking-detail {
            background: white;
            padding: 15px;
            border-radius: 4px;
        }

        .detail-label {
            display: block;
            font-size: 0.9em;
            color: #666;
            margin-bottom: 5px;
        }

        .detail-value {
            display: block;
            font-weight: bold;
        }

        .booking-price-breakdown {
            background: white;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 30px;
        }

        .booking-price-breakdown h3 {
            margin-top: 0;
            margin-bottom: 15px;
        }

        .price-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .price-total {
            font-weight: bold;
            font-size: 1.2em;
            border-top: 2px solid #ddd;
            border-bottom: none;
            padding-top: 15px;
            margin-top: 5px;
        }

        .booking-actions {
            text-align: center;
        }

        .proceed-to-payment {
            display: inline-block;
            background: #0073aa;
            color: white;
            padding: 15px 30px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1em;
        }

        .proceed-to-payment:hover {
            background: #005177;
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .booking-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
<?php

    return ob_get_clean();
}

/**
 * Get or create a product for the property booking
 */
function prefix_get_booking_product($property_id)
{
    global $wpdb;

    // Check if the product exists
    $product_id = $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM $wpdb->postmeta 
        WHERE meta_key = '_booking_property_id' AND meta_value = %d",
        $property_id
    ));

    if ($product_id) {
        return $product_id;
    }

    // If product doesn't exist, create it
    if (function_exists('wc_get_product_object')) {
        $product = new WC_Product_Simple();

        $product->set_name(sprintf(__('Booking for %s', 'textdomain'), get_the_title($property_id)));
        $product->set_status('publish');
        $product->set_catalog_visibility('hidden');
        $product->set_price(0);
        $product->set_regular_price(0);
        $product->set_sold_individually(true);

        // Save the product
        $product_id = $product->save();

        // Link to property
        update_post_meta($product_id, '_booking_property_id', $property_id);

        return $product_id;
    }

    return false;
}

/**
 * Process WooCommerce order completion
 */
add_action('woocommerce_order_status_completed', 'prefix_process_booking_order_completed');
function prefix_process_booking_order_completed($order_id)
{
    $order = wc_get_order($order_id);

    // Check if order has a booking
    $booking_id = $order->get_meta('_booking_id');

    if (!$booking_id) {
        return;
    }

    // Get booking details from order meta - prefer this over retrieving from booking to ensure consistency
    $check_in = $order->get_meta('_booking_check_in');
    $check_out = $order->get_meta('_booking_check_out');
    $adults = $order->get_meta('_booking_adults');
    $children = $order->get_meta('_booking_children');

    // Fallback to booking meta if order meta is missing
    if (empty($check_in) || empty($check_out)) {
        $check_in = get_post_meta($booking_id, 'booking_check_in', true);
        $check_out = get_post_meta($booking_id, 'booking_check_out', true);
    }

    if (empty($adults) || empty($children)) {
        $adults = get_post_meta($booking_id, 'booking_adults', true);
        $children = get_post_meta($booking_id, 'booking_children', true);
    }

    // Update booking status
    update_post_meta($booking_id, 'booking_status', 'confirmed');
    update_post_meta($booking_id, 'booking_payment_status', 'paid');

    // Get property ID
    $property_id = get_post_meta($booking_id, 'booking_property_id', true);

    // Add to property's unavailable dates
    $unavailable_dates = rwmb_meta('property_unavailable_dates', array(), $property_id);

    if (!is_array($unavailable_dates)) {
        $unavailable_dates = array();
    }

    $unavailable_dates[] = array(
        'from' => date('Y-m-d', strtotime($check_in)),
        'to' => date('Y-m-d', strtotime($check_out)),
    );

    update_post_meta($property_id, 'property_unavailable_dates', $unavailable_dates);

    // Send confirmation email
    prefix_send_booking_confirmation_email($booking_id);
}

/**
 * Send booking confirmation email
 */
function prefix_send_booking_confirmation_email($booking_id)
{
    $booking = get_post($booking_id);

    if (!$booking || $booking->post_type !== 'booking') {
        return;
    }

    $property_id = get_post_meta($booking_id, 'booking_property_id', true);
    $first_name = get_post_meta($booking_id, 'booking_first_name', true);
    $last_name = get_post_meta($booking_id, 'booking_last_name', true);
    $email = get_post_meta($booking_id, 'booking_email', true);
    $check_in = get_post_meta($booking_id, 'booking_check_in', true);
    $check_out = get_post_meta($booking_id, 'booking_check_out', true);
    $adults = get_post_meta($booking_id, 'booking_adults', true);
    $children = get_post_meta($booking_id, 'booking_children', true);
    $total = get_post_meta($booking_id, 'booking_total', true);

    $to = $email;
    $subject = sprintf(__('Your booking for %s is confirmed', 'textdomain'), get_the_title($property_id));

    $message = sprintf(__('Dear %s,', 'textdomain'), $first_name . ' ' . $last_name) . "\n\n";
    $message .= sprintf(__('Your booking for %s has been confirmed.', 'textdomain'), get_the_title($property_id)) . "\n\n";
    $message .= __('Booking Details:', 'textdomain') . "\n";
    $message .= sprintf(__('Check-in: %s', 'textdomain'), date_i18n(get_option('date_format'), strtotime($check_in))) . "\n";
    $message .= sprintf(__('Check-out: %s', 'textdomain'), date_i18n(get_option('date_format'), strtotime($check_out))) . "\n";
    $message .= sprintf(__('Guests: %d Adults, %d Children', 'textdomain'), $adults, $children) . "\n";
    $message .= sprintf(__('Total Amount: €%s', 'textdomain'), number_format($total, 2)) . "\n\n";
    $message .= __('Thank you for your booking!', 'textdomain') . "\n\n";
    $message .= get_bloginfo('name');

    $headers = array('Content-Type: text/plain; charset=UTF-8');

    wp_mail($to, $subject, $message, $headers);

    // Also send a notification to the admin
    $admin_email = get_option('admin_email');
    $admin_subject = sprintf(__('New booking for %s', 'textdomain'), get_the_title($property_id));

    $admin_message = sprintf(__('New booking (ID: %d) has been confirmed:', 'textdomain'), $booking_id) . "\n\n";
    $admin_message .= sprintf(__('Property: %s', 'textdomain'), get_the_title($property_id)) . "\n";
    $admin_message .= sprintf(__('Customer: %s', 'textdomain'), $first_name . ' ' . $last_name) . "\n";
    $admin_message .= sprintf(__('Email: %s', 'textdomain'), $email) . "\n";
    $admin_message .= sprintf(__('Check-in: %s', 'textdomain'), date_i18n(get_option('date_format'), strtotime($check_in))) . "\n";
    $admin_message .= sprintf(__('Check-out: %s', 'textdomain'), date_i18n(get_option('date_format'), strtotime($check_out))) . "\n";
    $admin_message .= sprintf(__('Guests: %d Adults, %d Children', 'textdomain'), $adults, $children) . "\n";
    $admin_message .= sprintf(__('Total Amount: €%s', 'textdomain'), number_format($total, 2)) . "\n\n";
    $admin_message .= sprintf(__('View Booking: %s', 'textdomain'), admin_url('post.php?post=' . $booking_id . '&action=edit'));

    wp_mail($admin_email, $admin_subject, $admin_message, $headers);
}

/**
 * Add custom email actions to WooCommerce
 */
add_filter('woocommerce_email_actions', 'prefix_add_booking_email_actions');
function prefix_add_booking_email_actions($actions)
{
    $actions[] = 'prefix_booking_confirmed';
    return $actions;
}