<?php
/**
 * Adds RV_By_Stock widget.
 */
class RV_By_Stock extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    function __construct() {
        parent::__construct(
            'rv_by_stock', // Base ID
            __('Find by Stock Number', 'text_domain'), // Name
            array( 'description' => __( 'A form to Find RV by Stock Number', 'text_domain' ), ) // Args
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
      $filtered_rv_stock_num = $_GET['stock-number'];
    }

        echo $args['before_widget'];

    echo '<div class="panel panel-default">';
      echo '<div class="panel-heading">';
        echo '<h3 class="panel-title">'.$title.'</h3>';
      echo '</div>';
      // begin search form
      echo '<div class="panel-body">';
        echo '<form action="'.$filter_form_url.'" method="get" role="form">';
          echo '<div class="row">';
            echo '<div class="col-md-8">';
              if ( isset($filtered_rv_stock_num) && !empty($filtered_rv_stock_num)) {
                echo '<input type="text" name="stock-number" value="'.$filtered_rv_stock_num.'" class="form-control" placeholder="Stock Number..."/>';
              } else {
                echo '<input type="text" name="stock-number" value="" class="form-control" placeholder="Stock Number..."/>';
              }
            echo '</div>';
            echo '<div class="col-md-4">';
              echo '<button type="submit" class="btn btn-primary btn-block">Search</button>';
            echo '</div>';
          echo '</div>';
        echo '</form>';
      echo '</div>';
      // end search form
    echo '</div>'; // stock search panel

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
            $title = __( 'Search by Stock Number', 'text_domain' );
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

} // class RV_By_Stock
