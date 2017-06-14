<?php while (have_posts()) : the_post(); ?>
  <?php $web_special = get_field('web_special'); ?>

  <div class="trailer-detail" itemscope itemtype="http://schema.org/Product">
    <div class="row trailer-title-row">
      <div class="col-md-8 span8">
        <h1 class="dl-title trailer-title" itemprop="name"><?php the_title(); ?></h1>
      </div>
      <div class="col-md-4 span4">
        <strong>Online Price</strong><br />
        <h3>
          <span itemprop="offers" itemscope itemtype="http://schema.org/Offer">
              <?php if(get_field( "web_price" ) && get_field( "web_price" ) != '0.00') : ?>
                <strong style="color: red;">*</strong><span class="label label-price" itemprop="price">$<?php echo number_format(floatval(get_field( "web_price" )), 2); ?></span>
                <meta itemprop="priceCurrency" content="USD" />
              <?php elseif (get_field( "ids_price" ) && get_field( "ids_price" ) != '0.00') : ?>
                <strong style="color: red;">*</strong><span class="label label-price" itemprop="price">$<?php echo number_format(floatval(get_field( "ids_price" )), 2); ?></span>
                <meta itemprop="priceCurrency" content="USD" />
              <?php else : ?>
                <a href="/contact?topic=sales&stock-number=<?php the_field( "ids_stock_number" ); ?>" rel="nofollow">Contact Us for Best Price</a>
                <meta itemprop="price" content="$<?php echo get_field( "ids_msrp" ); ?>" />
                <meta itemprop="priceCurrency" content="USD" />
              <?php endif; ?>
          </span>

          <?php if ($web_special == 'c') : ?>
            &nbsp;&nbsp;<span class="label label-danger">Clearance</span>
          <?php elseif ($web_special == 'pr') : ?>
            &nbsp;&nbsp;<span class="label label-warning">Price Reduced</span>
          <?php elseif ($web_special == 'osn') : ?>
            &nbsp;&nbsp;<span class="label label-success">On Sale Now</span>
          <?php elseif ($web_special == 'bp') : ?>
            &nbsp;&nbsp;<span class="label label-danger">Blowout Price</span>
          <?php elseif ($web_special == 'sp') : ?>
            &nbsp;&nbsp;<span class="label label-primary">Special Price</span>
          <?php else : ?>
          <?php endif; ?>
        </h3>
      </div>
    </div>

    <?php if ( get_field("ids_sold_date") && strpos(strtolower(get_field( "ids_status" )),'sold') !== false ) : ?>
            <div class="alert alert-success alert-status-sold">
              <p>
                    <strong>This trailer was sold on <?php echo date('F j, Y', strtotime(get_field("ids_sold_date"))); ?></strong><br />
                    If you are interested in a trailer like this one feel free to use the form below to contact us or browse our site.
                </p>
            </div>
        <?php elseif( strpos(strtolower(get_field( "ids_status" )),'sold') !== false ) : ?>
          <div class="alert alert-success alert-status-sold">
              <p>
                    <strong>This trailer was sold</strong><br />
                    If you are interested in a trailer like this one feel free to use the form below to contact us or browse our site.
                </p>
            </div>
        <?php endif; ?>
    <link itemprop="url" href="<?php echo get_post_permalink(); ?>" />
    <div class="row detail-hero">
        <div class="col-md-8 span8">
            <div class="trailer-photos">
                <div id="detail-photos">
                  <?php
                    $image_ids = get_field('web_photos', false, false);
                    if (!empty($image_ids)) {
                      $trailer_import_display_options = get_option( 'trailer_import_display' );
                      //$shortcode = '[gallery royalslider="'.$trailer_import_display_options['slider_id'].'" ids="' . implode(',', $image_ids) . '"]';
                      $shortcode = '[soliloquy_dynamic id="custom-trailer-detail" images="'.implode(',', $image_ids).'"]';
                      echo do_shortcode( $shortcode );
                    } else {
                      if ( strpos(strtolower(get_field("ids_status")), 'on order') !== false && strpos(strtolower(get_field( "ids_status" )),'sold') === false) {
                          echo "<img src=\"". get_bloginfo( 'template_directory' ) ."/assets/img/preview_on_order.png\" width=\"100%\" />";
                        } else {
                          echo "<img src=\"". get_bloginfo( 'template_directory' ) ."/assets/img/preview_detail_empty.png\" width=\"100%\" />";
                        }
                    }
                  ?>
                </div>
            </div>
        </div>
        <div class="col-md-4 span4">
            <ul class="list-unstyled">
            <li class="row" style="border-bottom: 1px solid #eaeaea; line-height: 28px;">
              <div class="col-md-4 span4" style="color: #646464; font-weight: bold;">MSRP</div>
              <div class="col-md-8 span8 trailer-msrp">$<?php echo number_format(floatval(get_field( "ids_msrp" )), 2); ?></div>
            </li>
            <li class="row" style="border-bottom: 1px solid #eaeaea; line-height: 28px;">
              <div class="col-md-4 span4" style="color: #646464; font-weight: bold;">Condition</div>
              <div class="col-md-8 span8"><?php the_field( "ids_condition" ); ?></div>
            </li>
            <li class="row" style="border-bottom: 1px solid #eaeaea; line-height: 28px;">
              <div class="col-md-4 span4" style="color: #646464; font-weight: bold;">Status</div>
              <div class="col-md-8 span8">
                  <?php
                    $trailer_status = get_field( "ids_status" );
                    if (strpos(strtolower($trailer_status),'sold') !== false){
                      echo '<div class="status-sold">'.'Sold'.'</div>';
                    } elseif ($trailer_status == 'Available') {
                      echo '<div class="status-available"><i class="glyphicon glyphicon-ok"></i>&nbsp;&nbsp;' . $trailer_status . '</div>';
                    } else {
                      echo '<div class="status-normal">' . $trailer_status . '</div>';
                    }
                  ?>
              </div>
            </li>
          </ul>
          <h3 class="dl-title">Specifications</h3>
          <?php if( get_field('web_floor_plan') ): ?>
            <div style="margin-bottom: 15px;">
              <a href="<?php the_field('web_floor_plan'); ?>" target="_blank" class="btn btn-primary btn-hollow btn-block btn-lg">Download Floor Plan for this Unit</a>
            </div>
          <?php endif; ?>
          <ul class="list-unstyled">
            <li class="row" style="border-bottom: 1px solid #eaeaea; line-height: 28px;">
              <div class="col-md-4 span4" style="color: #646464; font-weight: bold;">Stock #</div>
              <div class="col-md-8 span8" id="stockNumber" itemprop="productID"><?php the_field( "ids_stock_number" ); ?></div>
            </li>
            <li class="row" style="border-bottom: 1px solid #eaeaea; line-height: 28px;">
              <div class="col-md-4 span4" style="color: #646464; font-weight: bold;">Brand</div>
              <div class="col-md-8 span8" itemprop="manufacturer" itemscope itemtype="http://schema.org/Organization">
                    <span itemprop="name"><?php echo get_the_term_list( $post->ID, 'trailer-brand', '', ', ', '' ); ?></span>
              </div>
            </li>
            <li class="row" style="border-bottom: 1px solid #eaeaea; line-height: 28px;">
              <div class="col-md-4 span4" style="color: #646464; font-weight: bold;">Model</div>
              <div class="col-md-8 span8" itemprop="model"><a href="#model-slug-here"><?php the_field( "ids_model" ); ?></a></div>
            </li>
            <li class="row" style="border-bottom: 1px solid #eaeaea; line-height: 28px;">
              <div class="col-md-4 span4" style="color: #646464; font-weight: bold;">Year</div>
              <div class="col-md-8 span8" itemprop="releaseDate"><?php the_field( "ids_year" ); ?></div>
            </li>
            <li class="row" style="border-bottom: 1px solid #eaeaea; line-height: 28px;">
              <div class="col-md-4 span4" style="color: #646464; font-weight: bold;">Type</div>
              <div class="col-md-8 span8"><?php echo get_the_term_list( $post->ID, 'trailer-type', '', ', ', '' ); ?></div>
            </li>
              <?php
                $specLengthExists = false;
                $webSpecs = get_field('web_spec');

                if ( $webSpecs && gettype($webSpecs) === 'array') {
                  foreach( $webSpecs as $webSpec) {
                    if ( $webSpec['name'] === 'Length' || $webSpec['name'] === 'length' ) {
                      $specLengthExists = true;
                    }
                  }
                }
              ?>
              <?php if( get_field( "ids_length" ) && get_field( "ids_length" ) !== '0' && $specLengthExists === false ): ?>
                <li class="row" style="border-bottom: 1px solid #eaeaea; line-height: 28px;">
                <div class="col-md-4 span4" style="color: #646464; font-weight: bold;">Length</div>
                <div class="col-md-8 span8"><?php the_field( "ids_length" ); ?></div>
                </li>
              <?php endif; ?>

              <?php if( get_field( "web_number_of_horses" ) ): ?>
                <li class="row" style="border-bottom: 1px solid #eaeaea; line-height: 28px;">
                <div class="col-md-4 span4" style="color: #646464; font-weight: bold;">Number of Horses</div>
                <div class="col-md-8 span8"><?php the_field( "web_number_of_horses" ); ?></div>
                </li>
              <?php endif; ?>

              <?php if(get_field('web_spec')): ?>
                <?php while(has_sub_field('web_spec')): ?>
                  <li class="row" style="border-bottom: 1px solid #eaeaea; line-height: 28px;">
                  <div class="col-md-4 span4" style="color: #646464; font-weight: bold;"><?php the_sub_field('name'); ?></div>
                  <div class="col-md-8 span8"><?php the_sub_field('value'); ?></div>
                  </li>
                <?php endwhile; ?>
              <?php endif; ?>
          </ul>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="col-md-6 span6">
          <div itemprop="description" class="trailer-description">
              <?php the_content(); ?>
            </div>

            <div class="well trade-in-well">
              <h2>What's my truck, trailer, or RV worth?</h2><br />
              <h3>We Accept Trades!</h3><br />
              <p>Trailer RVs has an exclusive trade-in program. Most dealerships will not accept trades, but here at Trailer RVs, our trade-in program is what sets us apart from the competition. Bring your unit by and our team of trailer and RV specialists will assist you in an appraisal making sure you get top dollar for your unit.<br /><br />
              <a class="btn btn-primary" role="button" href="/sell/">Learn More about our Trade-In Program</a></p>
            </div>
        </div>
        <div class="col-md-6 span6">
            <div class="l-inquire">
              <h3>QUESTIONS ABOUT THIS TRAILER?</h3>
                <?php
                  $trailer_import_display_options = get_option( 'trailer_import_display' );
                  gravity_form($trailer_import_display_options['inquire_form_id'], false, false, false, array( 'stock_number' => get_field( "ids_stock_number" ), 'trailer_title' => get_the_title($post->ID), 'trailer_brand' => get_the_term_list( $post->ID, 'trailer-brand', '', ', ', '' ), 'trailer_model' => get_field('ids_model'), 'trailer_year' => get_field('ids_year') ), true, 12);
                ?>
            </div>
        </div>
    </div>
    <div class="row">
      <?php
        $trailer_disclaimer = of_get_option('trailers_detail_disclaimer', '');
        if ( $trailer_disclaimer ) {
          echo '<div class="trailer-disclaimers col-xs-12 col-sm-12 col-md-12" style="margin-top: 40px; color: #666">' . of_get_option('trailers_detail_disclaimer') . '</div>';
        }
      ?>
    </div>
  </div>
<?php endwhile; ?>
