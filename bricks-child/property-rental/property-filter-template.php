<?php
/**
 * Template part for property filter form
 * Save as /template-parts/property-filter-form.php
 */
?>
<div class="property-search-form">
  <form method="GET" action="<?php echo esc_url( get_post_type_archive_link( 'property' ) ); ?>">
    <div class="search-filters">
      <div class="date-filter">
        <label for="check_in"><?php _e( 'Check-in Date', 'textdomain' ); ?></label>
        <input type="date" id="check_in" name="check_in" value="<?php echo isset( $_GET['check_in'] ) ? esc_attr( $_GET['check_in'] ) : ''; ?>" required>
      </div>
      
      <div class="date-filter">
        <label for="check_out"><?php _e( 'Check-out Date', 'textdomain' ); ?></label>
        <input type="date" id="check_out" name="check_out" value="<?php echo isset( $_GET['check_out'] ) ? esc_attr( $_GET['check_out'] ) : ''; ?>" required>
      </div>
      
      <div class="guests-filter">
        <label for="adults"><?php _e( 'Adults', 'textdomain' ); ?></label>
        <select id="adults" name="adults">
          <?php
          $selected_adults = isset( $_GET['adults'] ) ? intval( $_GET['adults'] ) : 1;
          for ( $i = 1; $i <= 6; $i++ ) {
              printf(
                  '<option value="%d" %s>%d</option>',
                  $i,
                  selected( $selected_adults, $i, false ),
                  $i
              );
          }
          ?>
        </select>
      </div>
      
      <div class="guests-filter">
        <label for="children"><?php _e( 'Children', 'textdomain' ); ?></label>
        <select id="children" name="children">
          <?php
          $selected_children = isset( $_GET['children'] ) ? intval( $_GET['children'] ) : 0;
          for ( $i = 0; $i <= 4; $i++ ) {
              printf(
                  '<option value="%d" %s>%d</option>',
                  $i,
                  selected( $selected_children, $i, false ),
                  $i
              );
          }
          ?>
        </select>
      </div>
      
      <div class="property-type-filter">
        <label for="property_type"><?php _e( 'Property Type', 'textdomain' ); ?></label>
        <select id="property_type" name="property_type">
          <option value=""><?php _e( 'All Types', 'textdomain' ); ?></option>
          <?php echo get_property_types_options(); ?>
        </select>
      </div>
      
      <div class="submit-button">
        <button type="submit"><?php _e( 'Search Properties', 'textdomain' ); ?></button>
      </div>
    </div>
  </form>
</div>

<style>
.property-search-form {
  background: #f5f5f5;
  padding: 20px;
  border-radius: 5px;
  margin-bottom: 30px;
}

.search-filters {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
}

.date-filter, .guests-filter, .property-type-filter {
  flex: 1;
  min-width: 150px;
}

.submit-button {
  display: flex;
  align-items: flex-end;
}

label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}

input, select {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

button {
  background: #0073aa;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
}

button:hover {
  background: #005177;
}

@media (max-width: 768px) {
  .search-filters {
    flex-direction: column;
  }
}
</style>
