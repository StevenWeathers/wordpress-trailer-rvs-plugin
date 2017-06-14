<h1><?php post_type_archive_title(); ?></h1>
<div class="trailer-listing">
  <?php
    while ( have_posts() ) : the_post();
      echo '<div class="trailer row">';
        echo '<div class="col-md-3 trailer-thumb">';
          if ( get_field("ids_sold_date") && strpos(strtolower(get_field( "ids_status" )),'sold') !== false ) {
                        echo "<img src=\"". get_bloginfo( 'template_directory' ) ."/assets/img/preview_sold_141.png\" width=\"100%\" />";
          } else {
            if ( has_post_thumbnail() ) { // check if the post has a Post Thumbnail assigned to it.
              the_post_thumbnail('thumbnail');
            } else {
              echo "<img src=\"". get_bloginfo( 'template_directory' ) ."/assets/img/preview_detail_empty.png\" width=\"100%\" />";
            }
          }
        echo '</div>';
        echo '<div class="col-md-9">';
          echo '<div class="trailer-header">';
            echo '<div class="trailer-title">';
              echo '<h3><a href="'.get_post_permalink().'">'.get_the_title().'</a></h3>';
            echo '</div>';
          echo '</div>';
          echo '<div class="row trailer-content">';
            echo '<div class="col-md-8 trailer-info">';
              echo '<ul>';
                echo '<li class="trailer-type">';
                  echo 'Type&nbsp;&nbsp;&nbsp;'. get_the_term_list( $post->ID, 'trailer-type', '', ', ', '' ) .'';
                echo '</li>';
                echo '<li class="trailer-model">';
                  echo 'Model&nbsp;&nbsp;&nbsp;<a>'.get_field( "ids_model" ).'</a>';
                echo '</li>';
                echo '<li class="trailer-status">';
                          $trailer_status = get_field( "ids_status" );
                  if (strpos(strtolower($trailer_status),'sold') !== false){
                    echo 'Status&nbsp;&nbsp;&nbsp;<span class="status-sold">'.'Sold'.'</span>';
                  } elseif ($trailer_status == 'Available') {
                    echo 'Status&nbsp;&nbsp;&nbsp;<span class="status-available"><i class="glyphicon glyphicon-ok"></i>&nbsp;&nbsp;' . $trailer_status . '</span>';
                  } else {
                    echo 'Status&nbsp;&nbsp;&nbsp;<span class="status-normal">' . $trailer_status . '</span>';
                  }
                echo '</li>';
                echo '<li class="trailer-stock-number">';
                  echo 'Stock #&nbsp;&nbsp;&nbsp;'. get_field( "ids_stock_number" );
                echo '</li>';
              echo '</ul>';
            echo '</div>'; // trailer-info
            echo '<div class="col-md-4 trailer-actions">';
              echo '<div class="trailer-price">';
                echo '<span class="l-label">Our Price</span>&nbsp;&nbsp;';
                echo '<span class="l-value">';
                  $price = get_field( "ids_price" );

                  if ($price && $price != '0.00') {
                    echo '<span>$'.number_format(floatval($price), 2).'</span>';
                  } else {
                    echo '<a href="/contact?topic=sales&stock-number='. get_field( "ids_stock_number" ) .'" rel="nofollow">Contact Us</a>';
                  }
                echo '</span>';
              echo '</div>'; // trailer-price
              echo '<div class="trailer-msrp">';
                echo '<del>MSRP <span class="l-value">$'.get_field( "ids_msrp" ).'</span></del>';
              echo '</div>'; // trailer-msrp
              echo '<a class="btn btn-primary" href="'.get_post_permalink().'">View Details</a>';
            echo '</div>'; // trailer-actions
          echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '<div class="clearfix"></div>';
    endwhile;
    wp_pagenavi();
  ?>
</div>
