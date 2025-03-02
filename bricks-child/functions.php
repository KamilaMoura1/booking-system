<?php 
/**
 * Register/enqueue custom scripts and styles
 */
add_action( 'wp_enqueue_scripts', function() {
	// Enqueue your files on the canvas & frontend, not the builder panel. Otherwise custom CSS might affect builder)
	if ( ! bricks_is_builder_main() ) {
		wp_enqueue_style( 'bricks-child', get_stylesheet_uri(), ['bricks-frontend'], filemtime( get_stylesheet_directory() . '/style.css' ) );
	}
} );

/**
 * Register custom elements
 */
add_action( 'init', function() {
  $element_files = [
    __DIR__ . '/elements/title.php',
  ];

  foreach ( $element_files as $file ) {
    \Bricks\Elements::register_element( $file );
  }
}, 11 );

/**
 * Add text strings to builder
 */
add_filter( 'bricks/builder/i18n', function( $i18n ) {
  // For element category 'custom'
  $i18n['custom'] = esc_html__( 'Custom', 'bricks' );

  return $i18n;
} );

/**
 * Enqueue Property Rental System Files
 * 
 * This code loads all the necessary files for the property rental system
 * from the property-rental folder in the child theme.
 */
function property_rental_enqueue_files() {
  // Define the base directory for our property rental files
  $property_rental_dir = get_stylesheet_directory() . '/property-rental/';
  
  // Array of files to include, in the correct order for dependencies
  $files = array(
      'create-property-cpt.php',              // Register custom post types
      'property-custom-fields.php',           // Set up property fields with Metabox
      'property-query-filter.php',            // Add property filtering functionality
      'property-types-dropdown.php',          // Handle property type dropdown
      'booking-form-meta.php',                // Set up the booking form
      'booking-processing.php',               // Handle booking validation and processing
      'woocommerce-integration.php',          // WooCommerce payment integration
      'availability-calendar.php',            // Property availability calendar
  );
  
  // Include each file if it exists
  foreach ($files as $file) {
      $file_path = $property_rental_dir . $file;
      if (file_exists($file_path)) {
          require_once $file_path;
      }
  }
}

// Execute the function to include all files
add_action('after_setup_theme', 'property_rental_enqueue_files');

// Add this to your functions.php or main plugin file
add_action('wp_footer', 'prefix_add_form_prefill_script');
function prefix_add_form_prefill_script() {
    // Only add this on pages with the booking form
    if (!isset($_GET['property_id'])) {
        return;
    }
    
    ?>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    
    // Get the property_id from URL
    if (urlParams.has('property_id')) {
        const propertyId = urlParams.get('property_id');
        
        // Set both fields with the same property ID value
        setFieldValue('property_id', propertyId);
        setFieldValue('property_post_id', propertyId);
    }
    
    // Set other fields
    if (urlParams.has('check_in')) {
        setFieldValue('check_in', urlParams.get('check_in'));
    }
    
    if (urlParams.has('check_out')) {
        setFieldValue('check_out', urlParams.get('check_out'));
    }
    
    if (urlParams.has('adults')) {
        setFieldValue('adults', urlParams.get('adults'));
    }
    
    if (urlParams.has('children')) {
        setFieldValue('children', urlParams.get('children'));
    }
    
    if (urlParams.has('babies')) {
        setFieldValue('babies', urlParams.get('babies'));
    }
    
    // Helper function to set field value
    function setFieldValue(fieldId, value) {
        const field = document.getElementById(fieldId) || 
                      document.querySelector(`[name="${fieldId}"]`) ||
                      document.querySelector(`[name="rwmb-${fieldId}"]`);
        
        if (field) {
            // Set the value
            field.value = value;
            
            // Trigger change event for any dependent fields
            const event = new Event('change', { bubbles: true });
            field.dispatchEvent(event);
            
            console.log(`Set ${fieldId} to ${value}`);
        } else {
            console.log(`Field ${fieldId} not found`);
        }
    }
});
    </script>
    <?php
}