<?php
/**
 * Template for displaying a single property
 * This is a guide for how to set up your Bricks template
 */
?>
<div class="single-property-container">
  <div class="property-header">
    <h1 class="property-title"><?php the_title(); ?></h1>
    
    <?php
    $property_id = get_the_ID();
    $property_type = rwmb_meta( 'property_accommodation_type', array(), $property_id );
    $price = rwmb_meta( 'property_price', array(), $property_id );
    ?>
    
    <div class="property-meta">
      <span class="property-type"><?php echo esc_html( ucfirst( $property_type ) ); ?></span>
      <span class="property-price"><?php printf( __( '€%s per night', 'textdomain' ), number_format( $price, 2 ) ); ?></span>
    </div>
  </div>
  
  <div class="property-gallery">
    <?php
    $gallery_images = rwmb_meta( 'property_gallery', array( 'size' => 'large' ), $property_id );
    
    if ( !empty( $gallery_images ) ) :
    ?>
      <div class="gallery-container">
        <div class="gallery-main">
          <img src="<?php echo esc_url( reset( $gallery_images )['url'] ); ?>" alt="<?php the_title_attribute(); ?>" id="main-gallery-image">
        </div>
        
        <div class="gallery-thumbnails">
          <?php foreach ( $gallery_images as $image ) : ?>
            <div class="thumbnail" onclick="document.getElementById('main-gallery-image').src='<?php echo esc_url( $image['url'] ); ?>'">
              <img src="<?php echo esc_url( $image['url'] ); ?>" alt="<?php echo esc_attr( $image['alt'] ); ?>">
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
  
  <div class="property-content-sidebar">
    <div class="property-main-content">
      <div class="property-description">
        <h2><?php _e( 'Description', 'textdomain' ); ?></h2>
        <?php the_content(); ?>
      </div>
      
      <div class="property-details">
        <h2><?php _e( 'Property Details', 'textdomain' ); ?></h2>
        
        <?php
        $beds = rwmb_meta( 'property_beds', array(), $property_id );
        $baths = rwmb_meta( 'property_bathrooms', array(), $property_id );
        $max_adults = rwmb_meta( 'property_max_adults', array(), $property_id );
        $max_children = rwmb_meta( 'property_max_children', array(), $property_id );
        $max_babies = rwmb_meta( 'property_max_babies', array(), $property_id );
        $min_days = rwmb_meta( 'property_min_days', array(), $property_id );
        ?>
        
        <div class="details-grid">
          <div class="detail-item">
            <span class="detail-label"><?php _e( 'Beds', 'textdomain' ); ?></span>
            <span class="detail-value"><?php echo esc_html( $beds ); ?></span>
          </div>
          
          <div class="detail-item">
            <span class="detail-label"><?php _e( 'Bathrooms', 'textdomain' ); ?></span>
            <span class="detail-value"><?php echo esc_html( $baths ); ?></span>
          </div>
          
          <div class="detail-item">
            <span class="detail-label"><?php _e( 'Max Adults', 'textdomain' ); ?></span>
            <span class="detail-value"><?php echo esc_html( $max_adults ); ?></span>
          </div>
          
          <div class="detail-item">
            <span class="detail-label"><?php _e( 'Max Children', 'textdomain' ); ?></span>
            <span class="detail-value"><?php echo esc_html( $max_children ); ?></span>
          </div>
          
          <div class="detail-item">
            <span class="detail-label"><?php _e( 'Max Babies', 'textdomain' ); ?></span>
            <span class="detail-value"><?php echo esc_html( $max_babies ); ?></span>
          </div>
          
          <div class="detail-item">
            <span class="detail-label"><?php _e( 'Minimum Stay', 'textdomain' ); ?></span>
            <span class="detail-value"><?php printf( _n( '%s Night', '%s Nights', $min_days, 'textdomain' ), $min_days ); ?></span>
          </div>
        </div>
      </div>
      
      <div class="property-features">
        <h2><?php _e( 'Features', 'textdomain' ); ?></h2>
        
        <?php
        $main_features = rwmb_meta( 'property_main_features', array(), $property_id );
        $other_features = rwmb_meta( 'property_other_features', array(), $property_id );
        
        if ( !empty( $main_features ) && is_array( $main_features ) ) :
        ?>
          <div class="features-section">
            <h3><?php _e( 'Main Features', 'textdomain' ); ?></h3>
            <ul class="features-list">
              <?php foreach ( $main_features as $feature ) : ?>
                <li><?php echo esc_html( ucfirst( $feature ) ); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
        
        <?php if ( !empty( $other_features ) && is_array( $other_features ) ) : ?>
          <div class="features-section">
            <h3><?php _e( 'Other Features', 'textdomain' ); ?></h3>
            <ul class="features-list">
              <?php foreach ( $other_features as $feature ) : ?>
                <li><?php echo esc_html( ucfirst( $feature ) ); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
      </div>
      
      <div class="property-rules">
        <h2><?php _e( 'House Rules', 'textdomain' ); ?></h2>
        
        <?php
        $house_rules = rwmb_meta( 'property_house_rules', array(), $property_id );
        
        if ( !empty( $house_rules ) && is_array( $house_rules ) ) :
        ?>
          <ul class="rules-list">
            <?php foreach ( $house_rules as $rule ) : ?>
              <li><?php echo esc_html( ucfirst( $rule ) ); ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
    
    <div class="property-sidebar">
      <div class="booking-form-container">
        <h3><?php _e( 'Book This Property', 'textdomain' ); ?></h3>
        
        <form class="property-booking-form" method="GET" action="<?php echo esc_url( get_permalink( get_page_by_path( 'booking' ) ) ); ?>">
          <input type="hidden" name="property_id" value="<?php echo esc_attr( $property_id ); ?>">
          
          <div class="form-group">
            <label for="check_in"><?php _e( 'Check-in Date', 'textdomain' ); ?></label>
            <input type="date" id="check_in" name="check_in" required>
          </div>
          
          <div class="form-group">
            <label for="check_out"><?php _e( 'Check-out Date', 'textdomain' ); ?></label>
            <input type="date" id="check_out" name="check_out" required>
          </div>
          
          <div class="form-group">
            <label for="adults"><?php _e( 'Adults', 'textdomain' ); ?></label>
            <select id="adults" name="adults">
              <?php for ( $i = 1; $i <= $max_adults; $i++ ) : ?>
                <option value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></option>
              <?php endfor; ?>
            </select>
          </div>
          
          <div class="form-group">
            <label for="children"><?php _e( 'Children', 'textdomain' ); ?></label>
            <select id="children" name="children">
              <option value="0">0</option>
              <?php for ( $i = 1; $i <= $max_children; $i++ ) : ?>
                <option value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></option>
              <?php endfor; ?>
            </select>
          </div>
          
          <div class="form-group">
            <label for="babies"><?php _e( 'Babies', 'textdomain' ); ?></label>
            <select id="babies" name="babies">
              <option value="0">0</option>
              <?php for ( $i = 1; $i <= $max_babies; $i++ ) : ?>
                <option value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></option>
              <?php endfor; ?>
            </select>
          </div>
          
          <div class="form-actions">
            <button type="submit" class="book-now-button"><?php _e( 'Check Availability', 'textdomain' ); ?></button>
          </div>
        </form>
        
        <div class="booking-notes">
          <p><?php printf( __( 'Minimum stay: %s nights', 'textdomain' ), $min_days ); ?></p>
          <p><?php _e( 'Prices may vary during high season', 'textdomain' ); ?></p>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.single-property-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.property-header {
  margin-bottom: 30px;
}

.property-title {
  margin-bottom: 10px;
}

.property-meta {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
}

.property-price {
  font-size: 1.2em;
  font-weight: bold;
  color: #0073aa;
}

.property-type {
  background: #f0f0f0;
  padding: 5px 10px;
  border-radius: 4px;
}

.gallery-container {
  margin-bottom: 30px;
}

.gallery-main {
  margin-bottom: 10px;
}

.gallery-main img {
  width: 100%;
  height: 500px;
  object-fit: cover;
  border-radius: 8px;
}

.gallery-thumbnails {
  display: flex;
  gap: 10px;
  overflow-x: auto;
  padding-bottom: 10px;
}

.thumbnail {
  cursor: pointer;
  flex: 0 0 100px;
}

.thumbnail img {
  width: 100px;
  height: 70px;
  object-fit: cover;
  border-radius: 4px;
  transition: opacity 0.3s;
}

.thumbnail img:hover {
  opacity: 0.8;
}

.property-content-sidebar {
  display: flex;
  gap: 30px;
}

.property-main-content {
  flex: 2;
}

.property-sidebar {
  flex: 1;
}

.property-description,
.property-details,
.property-features,
.property-rules {
  margin-bottom: 30px;
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 15px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  background: #f5f5f5;
  padding: 10px;
  border-radius: 4px;
}

.detail-label {
  font-size: 0.9em;
  color: #666;
}

.detail-value {
  font-weight: bold;
  font-size: 1.1em;
}

.features-section {
  margin-bottom: 20px;
}

.features-list,
.rules-list {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
  padding: 0;
  list-style: none;
}

.features-list li,
.rules-list li {
  padding: 8px;
  background: #f5f5f5;
  border-radius: 4px;
}

.booking-form-container {
  background: #f5f5f5;
  padding: 20px;
  border-radius: 8px;
  position: sticky;
  top: 20px;
}

.property-booking-form .form-group {
  margin-bottom: 15px;
}

.property-booking-form label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}

.property-booking-form input,
.property-booking-form select {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.book-now-button {
  width: 100%;
  background: #0073aa;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1.1em;
}

.book-now-button:hover {
  background: #005177;
}

.booking-notes {
  margin-top: 15px;
  font-size: 0.9em;
  color: #666;
}

@media (max-width: 768px) {
  .property-content-sidebar {
    flex-direction: column;
  }
  
  .gallery-main img {
    height: 300px;
  }
  
  .features-list,
  .rules-list {
    grid-template-columns: 1fr;
  }
}
</style>

<script>
// Simple JavaScript for date validation
document.addEventListener('DOMContentLoaded', function() {
  const checkInInput = document.getElementById('check_in');
  const checkOutInput = document.getElementById('check_out');
  const minDays = <?php echo esc_js( $min_days ); ?>;
  
  if(checkInInput && checkOutInput) {
    // Set min date to today
    const today = new Date().toISOString().split('T')[0];
    checkInInput.min = today;
    
    checkInInput.addEventListener('change', function() {
      // Set checkout min date to checkin date + min days
      if(checkInInput.value) {
        const checkInDate = new Date(checkInInput.value);
        checkInDate.setDate(checkInDate.getDate() + parseInt(minDays));
        checkOutInput.min = checkInDate.toISOString().split('T')[0];
        
        // If current checkout is before new minimum, update it
        if(checkOutInput.value && new Date(checkOutInput.value) < checkInDate) {
          checkOutInput.value = checkOutInput.min;
        }
      }
    });
  }
});
</script>
