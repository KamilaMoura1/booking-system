<?php
/**
 * Template for displaying property listings
 * This is a guide for how to set up your Bricks template
 */
?>
<div class="property-listings">
  <?php if ( have_posts() ) : ?>
    <div class="properties-grid">
      <?php while ( have_posts() ) : the_post(); ?>
        <?php
        // Get property details
        $property_id = get_the_ID();
        $price = rwmb_meta( 'property_price', array(), $property_id );
        $beds = rwmb_meta( 'property_beds', array(), $property_id );
        $baths = rwmb_meta( 'property_bathrooms', array(), $property_id );
        $max_adults = rwmb_meta( 'property_max_adults', array(), $property_id );
        $max_children = rwmb_meta( 'property_max_children', array(), $property_id );
        $min_days = rwmb_meta( 'property_min_days', array(), $property_id );
        
        // Get the first image from the gallery
        $gallery_images = rwmb_meta( 'property_gallery', array( 'size' => 'large' ), $property_id );
        $featured_image = '';
        
        if ( !empty( $gallery_images ) ) {
            $first_image = reset( $gallery_images );
            $featured_image = $first_image['url'];
        } elseif ( has_post_thumbnail() ) {
            $featured_image = get_the_post_thumbnail_url( $property_id, 'large' );
        }
        
        // Get the accommodation type
        $accommodation_type = rwmb_meta( 'property_accommodation_type', array(), $property_id );
        
        // Get main features
        $main_features = rwmb_meta( 'property_main_features', array(), $property_id );
        ?>
        <div class="property-card">
          <div class="property-image">
            <?php if ( !empty( $featured_image ) ) : ?>
              <img src="<?php echo esc_url( $featured_image ); ?>" alt="<?php the_title_attribute(); ?>">
            <?php endif; ?>
          </div>
          
          <div class="property-details">
            <h3 class="property-title">
              <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h3>
            
            <div class="property-meta">
              <span class="property-price"><?php printf( __( '€%s per night', 'textdomain' ), number_format( $price, 2 ) ); ?></span>
              <span class="property-type"><?php echo esc_html( ucfirst( $accommodation_type ) ); ?></span>
            </div>
            
            <div class="property-specs">
              <span class="beds"><?php printf( _n( '%s Bed', '%s Beds', $beds, 'textdomain' ), $beds ); ?></span>
              <span class="baths"><?php printf( _n( '%s Bath', '%s Baths', $baths, 'textdomain' ), $baths ); ?></span>
              <span class="guests"><?php printf( __( 'Up to %s Guests', 'textdomain' ), $max_adults + $max_children ); ?></span>
            </div>
            
            <div class="property-features">
              <?php if ( !empty( $main_features ) && is_array( $main_features ) ) : ?>
                <ul class="features-list">
                  <?php foreach ( $main_features as $feature ) : ?>
                    <li><?php echo esc_html( ucfirst( $feature ) ); ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </div>
            
            <div class="property-actions">
              <a href="<?php the_permalink(); ?>" class="view-details"><?php _e( 'View Details', 'textdomain' ); ?></a>
              <?php if ( isset( $_GET['check_in'] ) && isset( $_GET['check_out'] ) ) : ?>
                <a href="<?php echo esc_url( add_query_arg( array(
                  'property_id' => $property_id,
                  'check_in' => $_GET['check_in'],
                  'check_out' => $_GET['check_out'],
                  'adults' => isset( $_GET['adults'] ) ? $_GET['adults'] : 1,
                  'children' => isset( $_GET['children'] ) ? $_GET['children'] : 0,
                ), get_permalink( get_page_by_path( 'booking' ) ) ) ); ?>" class="book-now"><?php _e( 'Book Now', 'textdomain' ); ?></a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
    
    <div class="pagination">
      <?php
      the_posts_pagination( array(
        'mid_size' => 2,
        'prev_text' => __( 'Previous', 'textdomain' ),
        'next_text' => __( 'Next', 'textdomain' ),
      ) );
      ?>
    </div>
    
  <?php else : ?>
    <div class="no-properties">
      <p><?php _e( 'No properties available for the selected dates and criteria.', 'textdomain' ); ?></p>
    </div>
  <?php endif; ?>
</div>

<style>
.properties-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 30px;
}

.property-card {
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
  transition: transform 0.3s, box-shadow 0.3s;
}

.property-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.property-image img {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.property-details {
  padding: 15px;
}

.property-title {
  margin-top: 0;
  margin-bottom: 10px;
}

.property-meta {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}

.property-price {
  font-weight: bold;
  color: #0073aa;
}

.property-specs {
  display: flex;
  gap: 15px;
  margin-bottom: 15px;
  color: #666;
}

.property-features ul {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  padding: 0;
  margin: 0 0 15px 0;
  list-style: none;
}

.property-features li {
  background: #f0f0f0;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.9em;
}

.property-actions {
  display: flex;
  gap: 10px;
}

.property-actions a {
  display: inline-block;
  padding: 8px 15px;
  border-radius: 4px;
  text-decoration: none;
  text-align: center;
}

.view-details {
  background: #f0f0f0;
  color: #333;
  flex: 1;
}

.book-now {
  background: #0073aa;
  color: white;
  flex: 1;
}

.book-now:hover {
  background: #005177;
}

.pagination {
  margin-top: 30px;
  text-align: center;
}

.no-properties {
  text-align: center;
  padding: 50px 0;
}

@media (max-width: 768px) {
  .properties-grid {
    grid-template-columns: 1fr;
  }
}
</style>
