<?php
/**
 * Function to generate property types dropdown options
 * Add to your theme's functions.php
 */
function get_property_types_options() {
    $property_types = get_terms( array(
        'taxonomy' => 'property_type',
        'hide_empty' => false,
    ) );
    
    $options = '';
    
    if ( !empty( $property_types ) && !is_wp_error( $property_types ) ) {
        foreach ( $property_types as $type ) {
            $selected = '';
            if ( isset( $_GET['property_type'] ) && $_GET['property_type'] === $type->slug ) {
                $selected = 'selected';
            }
            $options .= '<option value="' . esc_attr( $type->slug ) . '" ' . $selected . '>' . esc_html( $type->name ) . '</option>';
        }
    }
    
    return $options;
}

// Register a Bricks element for the property filter form
function register_bricks_property_filter() {
    add_filter( 'bricks/elements/properties_html', function( $properties ) {
        $properties['property_filter_form'] = array(
            'name' => 'Property Filter Form',
            'template' => get_template_directory() . '/template-parts/property-filter-form.php',
            'category' => 'custom',
        );
        return $properties;
    } );
}
add_action( 'init', 'register_bricks_property_filter' );
