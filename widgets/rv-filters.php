<?php
/**
 * Adds RV_Filters widget.
 */
class RV_Filters extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'rv_filters', // Base ID
			__('Filter RVs', 'text_domain'), // Name
			array( 'description' => __( 'A form to filter RVs', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
    $filter_form_url = apply_filters( 'widget_form_url', $instance['form_url'] );

    if (!empty($_GET)){
      $filtered_rv_brand = $_GET['rv-brand'];
      $filtered_rv_type = $_GET['rv-type'];
      $filtered_rv_condition = $_GET['rv-condition'];
      $filtered_rv_sort_by = $_GET['rv-sort-by'];
      $filtered_rv_year_from = $_GET['rv-year-from'];
      $filtered_rv_year_to = $_GET['rv-year-to'];
    }

    $queried_object = get_queried_object();

    if ( $queried_object->taxonomy == 'rv-type' ) {
      $filtered_rv_type = $queried_object->slug;
    }

    if ( $queried_object->taxonomy == 'rv-brand' ) {
      $filtered_rv_brand = $queried_object->slug;
    }

    if ( is_page_template('template-rv-new.php') ) {
      $filtered_rv_condition = 'new';
    }

    if ( is_page_template('template-rv-used.php') ) {
      $filtered_rv_condition = 'used';
    }

    if ( is_page_template('template-rvs-type-used.php') ) {
      $used_type_id = get_field('rv_type', get_queried_object()->ID);
      $used_type = get_term( $used_type_id, 'rv-type' );

      $filtered_rv_condition = 'used';
      $filtered_rv_type = $used_type->slug;
    }

    if ( is_page_template('template-rvs-brand-type.php') ) {
      $brand_type_condition_page_id = get_queried_object()->ID;
      $brand_type_brand_id = get_field('rv_brand', $brand_type_condition_page_id);
      $brand_type_type_id = get_field('rv_type', $brand_type_condition_page_id);

      $filtered_rv_brand = get_term( $brand_type_brand_id, 'rv-brand' )->slug;
      $filtered_rv_type =  get_term( $brand_type_type_id, 'rv-type' )->slug;
      $filtered_rv_condition = strtolower(get_field('rv_condition', $brand_type_condition_page_id));
    }

    $rv_brands = get_terms(
      array(
        'rv-brand',
      ),
      array(
        'orderby'       => 'name',
        'order'         => 'ASC',
        'hide_empty'    => true,
        'fields'        => 'all',
        'hierarchical'  => true
      )
    );

    $rv_types = get_terms(
      array(
        'rv-type',
      ),
      array(
        'orderby'       => 'name',
        'order'         => 'ASC',
        'hide_empty'    => true,
        'fields'        => 'all',
        'hierarchical'  => true
      )
    );

    global $wpdb;

    $rv_conditions = $wpdb->get_col("SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key = 'ids_condition'" );
    $rv_years = $wpdb->get_col("SELECT DISTINCT $wpdb->postmeta.meta_value FROM $wpdb->postmeta INNER JOIN $wpdb->posts ON $wpdb->posts.ID=$wpdb->postmeta.post_id WHERE $wpdb->postmeta.meta_key = 'ids_year' AND $wpdb->posts.post_type = 'rv' ORDER BY ($wpdb->postmeta.meta_value+0) ASC");

		echo $args['before_widget'];

		echo '<div class="panel panel-default">';
        echo '<div class="panel-heading">';
          echo '<h3 class="panel-title">'.$title.'</h3>';
        echo '</div>';

        echo '<div class="panel-body">';
          echo '<form action="'.$filter_form_url.'" method="GET" role="form" class="form-horizontal">';

            echo '<div class="form-group">';
              echo '<label for="" class="col-md-3 control-label">Type</label>';
              echo '<div class="col-md-9">';
                echo '<select name="rv-type" class="form-control">';
                  echo '<option value="">All Types</option>';
                  foreach ($rv_types as $type) {
                    if ($filtered_rv_type && !empty($filtered_rv_type) && $filtered_rv_type == $type->slug) {
                      echo '<option value="'.$type->slug.'" selected="selected">'.$type->name.'</option>';
                    } else {
                      echo '<option value="'.$type->slug.'">'.$type->name.'</option>';
                    }
                  }
                echo '</select>';
              echo '</div>';
            echo '</div>';

            echo '<div class="form-group">';
              echo '<label for="" class="col-md-3 control-label">Brand</label>';
              echo '<div class="col-md-9">';
                echo '<select name="rv-brand" class="form-control">';
                  echo '<option value="">All Brands</option>';
                  foreach ($rv_brands as $brand) {
                    if ($filtered_rv_brand && !empty($filtered_rv_brand) && $filtered_rv_brand == $brand->slug) {
                      echo '<option value="'.$brand->slug.'" selected="selected">'.$brand->name.'</option>';
                    } else {
                      echo '<option value="'.$brand->slug.'">'.$brand->name.'</option>';
                    }
                  }
                echo '</select>';
              echo '</div>';
            echo '</div>';

            echo '<div class="form-group">';
              echo '<label for="" class="col-md-3 control-label">Condition</label>';
              echo '<div class="col-md-9">';
                echo '<select name="rv-condition" class="form-control">';
                  echo '<option value="">New and Used</option>';
                  foreach ($rv_conditions as $condition)
                  {
                    if ($filtered_rv_condition && !empty($filtered_rv_condition) && $filtered_rv_condition == strtolower($condition)) {
                      echo '<option value="'.strtolower($condition).'" selected="selected">'.$condition.'</option>';
                    } else {
                      echo '<option value="'.strtolower($condition).'">'.$condition.'</option>';
                    }
                  }
                echo '</select>';
              echo '</div>';
            echo '</div>';

            echo '<div class="form-group">';
              echo '<label for="" class="col-md-3 control-label">Year\'s</label>';
              echo '<div class="col-md-9">';
                echo '<div class="row">';
                  echo '<div class="col-md-6 col-xs-6">';
                    echo '<select name="rv-year-from" class="form-control">';
                      echo '<option value="">From</option>';
                      foreach ($rv_years as $year_from)
                      {
                        if ( (($filtered_rv_year_from && !empty($filtered_rv_year_from) || $filtered_rv_year_from === '0')) && $filtered_rv_year_from === $year_from) {
                          echo '<option value="'.str_replace("'", "", $year_from).'" selected="selected">'.$year_from.'</option>';
                        } else {
                          echo '<option value="'.str_replace("'", "", $year_from).'">'.$year_from.'</option>';
                        }
                      }
                    echo '</select>';
                  echo '</div>';
                  echo '<div class="col-md-6 col-xs-6">';
                    echo '<select name="rv-year-to" class="form-control">';
                      echo '<option value="">To</option>';
                      foreach ($rv_years as $year_to)
                      {
                        if ( (($filtered_rv_year_to && !empty($filtered_rv_year_to) || $filtered_rv_year_to === '0')) && $filtered_rv_year_to === $year_to) {
                          echo '<option value="'.str_replace("'", "", $year_to).'" selected="selected">'.$year_to.'</option>';
                        } else {
                          echo '<option value="'.str_replace("'", "", $year_to).'">'.$year_to.'</option>';
                        }
                      }
                    echo '</select>';
                  echo '</div>';
                echo '</div>';
                echo '<p class="help-block">You must select both a <strong>From</strong> and a <strong>To</strong>.</p>';
              echo '</div>';
            echo '</div>';

            echo '<div class="form-group">';
              echo '<label for="" class="col-md-3 control-label">Sort By</label>';
              echo '<div class="col-md-9">';
                echo '<select name="rv-sort-by" class="form-control">';
                  if ( !isset($filtered_rv_sort_by) ){
                    echo '<option value="" selected="selected">Unsorted</option>';
                  } else {
                    echo '<option value="">Unsorted</option>';
                  }

                  // MSRP
                  if ( isset($filtered_rv_sort_by) && ($filtered_rv_sort_by == '' || $filtered_rv_sort_by == 'msrp-low-high')  ) {
                    echo '<option value="msrp-low-high" selected="selected">MSRP low to high</option>';
                  } else {
                    echo '<option value="msrp-low-high">MSRP low to high</option>';
                  }
                  if ($filtered_rv_sort_by && !empty($filtered_rv_sort_by) && $filtered_rv_sort_by == 'msrp-high-low') {
                    echo '<option value="msrp-high-low" selected="selected">MSRP high to low</option>';
                  } else {
                    echo '<option value="msrp-high-low">MSRP high to low</option>';
                  }

                  // Years
                  if ($filtered_rv_sort_by && !empty($filtered_rv_sort_by) && $filtered_rv_sort_by == 'year-oldest') {
                    echo '<option value="year-oldest" selected="selected">Year oldest to newest</option>';
                  } else {
                    echo '<option value="year-oldest">Year oldest to newest</option>';
                  }
                  if ($filtered_rv_sort_by && !empty($filtered_rv_sort_by) && $filtered_rv_sort_by == 'year-newest') {
                    echo '<option value="year-newest" selected="selected">Year newest to oldest</option>';
                  } else {
                    echo '<option value="year-newest">Year newest to oldest</option>';
                  }
                echo '</select>';
              echo '</div>';
            echo '</div>';

            echo '<div class="form-group">';
              echo '<div class="col-md-9 col-md-offset-3">';
                echo '<div class="row">';
                echo '<div class="col-xs-6">';
                  echo '<button type="submit" class="btn btn-primary btn-block">Filter</button>';
                echo '</div>';
                echo '<div class="col-xs-6">';
                  echo '<button type="reset" class="btn btn-block btn-link">Reset</button>';
                echo '</div>';
                echo '</div>';
              echo '</div>';
            echo '</div>';
          echo '</form>'; // filter form
        echo '</div>'; // panel content
      echo '</div>'; // filter form panel

		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Filter RVs', 'text_domain' );
		}
    if ( isset( $instance[ 'form_url' ] ) ) {
      $form_url = $instance[ 'form_url' ];
    }
    else {
      $form_url = __( '/filter-rvs', 'text_domain' );
    }
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
    <p>
    <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Form URL:' ); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'form_url' ); ?>" name="<?php echo $this->get_field_name( 'form_url' ); ?>" type="text" value="<?php echo esc_attr( $form_url ); ?>">
    </p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
    $instance['form_url'] = ( ! empty( $new_instance['form_url'] ) ) ? strip_tags( $new_instance['form_url'] ) : '';

		return $instance;
	}

} // class RV_Filters
