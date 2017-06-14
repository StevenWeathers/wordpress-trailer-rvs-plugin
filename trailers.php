<?php
/*
Plugin Name: Trailer RVS Custom Post Types
Description: Custom Post Types for "Trailer & RVs Website" website.
Author: Steven Weathers
Author URI: http://www.stevenweathers.com
Version: 1.0.0
*/

/**
 * Add Options for the Trailer Importer
 */
// importer ignore options
$trailer_importer_ignore_options = array();
$trailer_importer_ignore_options['trailer_type'] = 'COMPANY VEHICLE,RENTAL UNIT';
$trailer_importer_ignore_options['trailer_brand'] = '';
$trailer_importer_ignore_options['trailer_designation'] = 'RENTALS';
$trailer_importer_ignore_options['trailer_stocknums'] = '';
add_option('trailer_import_ignores', $trailer_importer_ignore_options);

// importer xml file options
$trailer_importer_xml_options = array();
$trailer_importer_xml_options['xml_file_path'] = 'data/';
$trailer_importer_xml_options['xml_file_prefix'] = 'Web_Inventory_';
$trailer_importer_xml_options['xml_file_dateformat'] = 'Y-m-d';
add_option('trailer_import_xmlfile', $trailer_importer_xml_options);

// trailer display options
$trailer_importer_display_options = array();
$trailer_importer_display_options['inquire_form_id'] = '1';
$trailer_importer_display_options['slider_id'] = '1';
add_option('trailer_import_display', $trailer_importer_display_options);

// rv options
$rv_importer_options = array();
$rv_importer_options['rv_types'] = 'MOTORHOME,TRAVEL TRAILER,FIFTH WHEEL,TOY HAULER';
add_option('rv_import', $rv_importer_options);

// rv display options
$rv_importer_display_options = array();
$rv_importer_display_options['rv_inquire_form_id'] = '1';
$rv_importer_display_options['rv_slider_id'] = '1';
add_option('rv_import_display', $rv_importer_display_options);

// rv importer ignore options
$rv_importer_ignore_options = array();
$rv_importer_ignore_options['rv_type'] = 'RENTAL UNIT';
$rv_importer_ignore_options['rv_brand'] = '';
$rv_importer_ignore_options['rv_designation'] = 'RENTALS';
$rv_importer_ignore_options['rv_stocknums'] = '';
add_option('rv_import_ignores', $rv_importer_ignore_options);


if ( !function_exists('inches_to_readable') )
{
  function inches_to_readable($inches, $show_inches = TRUE)
  {
    $feet = floor($inches / 12) . "'";
    if ($show_inches)
    {
      $inches = ($feet * 12 - $inches) . '"';
      return "$feet $inches";
    }

    return "$feet";
  }
}

if ( !function_exists('create_trailer_slug'))
{
  function create_trailer_slug($string){
    $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
    return strtolower($slug);
  }
}

/**
 * function to inject trailer into WordPress
 * @param  Array $child the trailer data
 */
function inject_trailer($child) {
    // Get Trailer Ignores
    $trailer_import_options = get_option( 'trailer_import_ignores' );
    $trailer_ignores = array();
    $trailer_ignores['types'] = explode( ',', strtolower($trailer_import_options['trailer_type']) );
    $trailer_ignores['brands'] = explode( ',', strtolower($trailer_import_options['trailer_brand']) );
    $trailer_ignores['designations'] = explode( ',', strtolower($trailer_import_options['trailer_designation']) );
    $trailer_ignores['stocks'] = explode( ',', strtolower($trailer_import_options['trailer_stocknums']) );

    $ignore_stocks = $trailer_ignores['stocks'];
    $ignore_brands = $trailer_ignores['brands'];
    $ignore_types = $trailer_ignores['types'];
    $ignore_designations = $trailer_ignores['designations'];

    // Determine Trailer vs RV type
    $rv_import_options = get_option( 'rv_import' );
    $rv_types = explode( ',', strtolower($rv_import_options['rv_types']) );
    // Get RV Ignores
    $rv_import_options = get_option( 'rv_import_ignores' );
    $rv_ignores = array();
    $rv_ignores['types'] = explode( ',', strtolower($rv_import_options['rv_type']) );
    $rv_ignores['brands'] = explode( ',', strtolower($rv_import_options['rv_brand']) );
    $rv_ignores['designations'] = explode( ',', strtolower($rv_import_options['rv_designation']) );
    $rv_ignores['stocks'] = explode( ',', strtolower($rv_import_options['rv_stocknums']) );

    $post_type = 'trailer';
    $inventory_type_term = 'trailer-type';
    $inventory_manufacturer_term = 'trailer-brand';
    $inventory_brand_term = 'trailer-brand';

    if ( in_array(strtolower($child->type), $rv_types) ) {
      $post_type = 'rv';
      $inventory_type_term = 'rv-type';
      $inventory_manufacturer_term = 'rv-brand';
      $inventory_brand_term = 'rv-brand';

      $ignore_stocks = $rv_ignores['stocks'];
      $ignore_brands = $rv_ignores['brands'];
      $ignore_types = $rv_ignores['types'];
      $ignore_designations = $rv_ignores['designations'];
    }

    if ( (!empty($child->stock_number) && !in_array(strtolower($child->stock_number), $ignore_stocks)) && (!empty($child->type) && !in_array(strtolower($child->type), $ignore_types)) && !in_array(strtolower($child->manufacturer), $ignore_brands) && !in_array(strtolower($child->designation), $ignore_designations) ) {
        // Initialize the page ID to -1. This indicates no action has been taken.
        $post_id = -1;

        $stock_number = $child->stock_number;
        $brand = ucwords(strtolower($child->brand));
        $brand_slug = create_trailer_slug($brand);
        $manufacturer = ucwords(strtolower($child->manufacturer));
        $manufacturer_slug = create_trailer_slug($manufacturer);
        $year = $child->model_year;
        $type = ucwords(strtolower($child->type));
        $type_slug = create_trailer_slug($child->type);
        if ($child->designation) { $condition = ucwords(strtolower($child->designation)); }
        if ($child->model) { $model = $child->model; }
        if ($child->status) { $status = ucwords(strtolower($child->status)); }
        if ($child->special_web_price && $child->special_web_price != 0) { $web_price = $child->special_web_price; }
        if ($child->base_list) { $msrp = $child->base_list; }

        $specs = array();
        if ($child->Spec) {
          foreach($child->Spec as $spec)
            {
              $specs[ucwords(strtolower($spec->spec_desc))] = ucwords(strtolower($spec->spec_detail));
            }
        }
        if ($child->interior_color) $specs['Interior Color'] = $child->interior_color;
        if ($child->exterior_color) $specs['Exterior Color'] = $child->exterior_color;

        // handle trailer length if it exists
        $length = 0;
        if ($child->length)
        {
          $length = $child->length;
          $matches = array();
          $matcher = preg_match('/(\d*)\'\s?(\d*)"/', $length, $matches);

          if (count($matches) == 3)
          {
              $feet = $matches[1];
              $inches = $matches[2];
              $length = ($feet * 12) + $inches;
          }
          else
          {
              $length = ( intval($length) * 12 );
          }
        }

        if ($length > 0)
        {
            $length = inches_to_readable($length, FALSE);
            $title = $year." ".$manufacturer." ".$length." ".$type;
        }
        else
        {
            $title = $year." ".$manufacturer." ".$type;
        }

        $slug = create_trailer_slug($stock_number." ".$title);

        $args = array(
            'post_type' => $post_type,
            'meta_query' => array(
                array(
                    'key' => 'ids_stock_number',
                    'value' => $stock_number.'',
                    'compare' => '='
                )
            ),
            'post_status' => 'publish',
            'posts_per_page' => -1
        );

        // If the page doesn't already exist, then create it
        $my_posts = new WP_Query( $args );

        if( !$my_posts->have_posts() ) {
        $new_trailer = array(
            'comment_status'  =>  'closed',
            'ping_status'   =>  'closed',
            'post_author'   =>  '1',
            'post_name'     =>  $slug,
            'post_title'    =>  $title,
            'post_status'   =>  'publish',
            'post_type'     =>  $post_type
        );

      // Set the post ID so that we know the post was created successfully
      $post_id = wp_insert_post($new_trailer);

      // add the custom field data
      if ($post_id) {
        if ($stock_number) { add_post_meta( $post_id, 'ids_stock_number', ''.$stock_number); }
        if ($year) {
                    add_post_meta( $post_id, 'ids_year', ''.$year);
                }
        if ($condition) {
          if ($condition == "Consignment") {
            $condition = "Used";
          }
          add_post_meta( $post_id, 'ids_condition', ''.$condition);
        }
            if ($model) {
                    add_post_meta( $post_id, 'ids_model', ''.$model);
                }
            if ($status) { add_post_meta( $post_id, 'ids_status', ''.$status); }
            if ($web_price) { add_post_meta( $post_id, 'ids_price', ''.$web_price); }
            if ($msrp) {
                    add_post_meta( $post_id, 'ids_msrp', ''.$msrp);
                } else {
                    add_post_meta( $post_id, 'ids_msrp', '0.00');
                }
            if ($length && $length > 0) {
                    add_post_meta( $post_id, 'ids_length', ''.$length);
                } else {
                    add_post_meta( $post_id, 'ids_length', "0'");
                }

            // Import Trailer Specs
            if (!empty($specs)){
              $specCount = 0;

              foreach($specs as $specKey => $specValue)
              {
                if ($specValue) {
                  add_post_meta( $post_id, 'web_spec_'. $specCount .'_name', ''.$specKey );
                  add_post_meta( $post_id, 'web_spec_'. $specCount .'_value', ''.$specValue );

                  ++$specCount;
                }
              }

              $field_key = "";
              if ($post_type == "rv")
              {
                $field_key = "field_52825b8609ef1";
              } else if ($post_type == 'trailer')
              {
                $field_key = "field_52825b8609ef1";
              }

              if (!empty($field_key))
              {
                $spec_value = get_field($field_key, $post_id);
                update_field($field_key, $spec_value, $post_id );
              }

              add_post_meta ( $post_id, 'web_spec', ''.$specCount ); // add the total count for repeater field
            }

            // set the trailer type
            if ( !get_term_by( 'slug', $type_slug, $inventory_type_term ) ){
              $type_term_id = wp_insert_term($type, $inventory_type_term, $args = array(
                'slug' => $type_slug,
              ));
            } else {
              $type_retrive_term = get_term_by( 'slug', $type_slug, $inventory_type_term );
              $type_term_id = ($type_retrive_term) ? $type_retrive_term->term_id : false ;

              // set any parent terms if they exist
              $type_parent_term = get_term( $type_retrive_term->parent, $inventory_type_term );
              if ($type_parent_term)
              {
                wp_set_post_terms( $post_id, $type_parent_term->term_id, $inventory_type_term, true );
              }
            }
            if ($type_term_id) {
              wp_set_post_terms( $post_id, $type_term_id, $inventory_type_term, true );
            }

            // set the trailer manufacturer
            if ( !get_term_by( 'slug', $manufacturer_slug, $inventory_manufacturer_term ) ){
              // create trailer manufacturer term
              $manufacturer_term_id = wp_insert_term($manufacturer, $inventory_manufacturer_term, $args = array(
                'slug' => $manufacturer_slug,
              ));
            } else {
              // retrieve trailer manufacturer term
              $manufacturer_retrive_term = get_term_by( 'slug', $manufacturer_slug, $inventory_manufacturer_term );
              $manufacturer_term_id = ($manufacturer_retrive_term) ? $manufacturer_retrive_term->term_id : false ;

              // set any parent terms if they exist
              $manufacturer_parent_term = get_term( $manufacturer_retrive_term->parent, $inventory_manufacturer_term );
              if ($manufacturer_parent_term)
              {
                wp_set_post_terms( $post_id, $manufacturer_parent_term->term_id, $inventory_manufacturer_term, true );
              }
            }
            if ($manufacturer_term_id) {
              wp_set_post_terms( $post_id, $manufacturer_term_id, $inventory_manufacturer_term, true );
            }

            // set the trailer brand if different from manufacturer
            if ($brand != $manufacturer){
                // set the trailer brand
                if ( !get_term_by( 'slug', $brand_slug, $inventory_brand_term ) ){
                  // create trailer brand term
                  $brand_term_id = wp_insert_term($brand, $inventory_brand_term, $args = array(
                    'slug' => $brand_slug,
                  ));
                } else {
                  // retrieve trailer brand term
                  $brand_retrive_term = get_term_by( 'slug', $brand_slug, $inventory_brand_term );
                  $brand_term_id = ($brand_retrive_term) ? $brand_retrive_term->term_id : false ;

                  // set any parent terms if they exist
                  $brand_parent_term = get_term( $brand_retrive_term->parent, $inventory_brand_term );
                  if ($brand_parent_term)
                  {
                    wp_set_post_terms( $post_id, $brand_parent_term->term_id, $inventory_brand_term, true );
                  }
                }
                if ($brand_term_id) {
                  wp_set_post_terms( $post_id, $brand_term_id, $inventory_brand_term, true );
                }
            }
      }
    } else if ( $my_posts->have_posts() ) {
      // Arbitrarily use -2 to indicate that the page with the title already exists
      $post_id = $my_posts->posts[0]->ID;

      // update the custom field data
      if ($post_id) {
        if ($year) { update_post_meta( $post_id, 'ids_year', ''.$year); } // consider for taxonomy
        if ($condition) {
          if ($condition == "Consignment") {
            $condition = "Used";
          }
          update_post_meta( $post_id, 'ids_condition', ''.$condition);
        }
        if ($model) { update_post_meta( $post_id, 'ids_model', ''.$model); } // consider for taxonomy

        if ($status) {
          // get current status
          $current_status = get_post_meta($post_id, 'ids_status', true);
          // determine if status has gone from available to sold
          if ( strpos(strtolower($status), 'sold') !== FALSE && strpos(strtolower($current_status), "sold") === FALSE )
          {
            // set sold date as today
            $sold_date = date('Y-m-d');
            // mark as sold!
            add_post_meta( $post_id, 'ids_sold_date', ''.$sold_date);
          }
          // update status
          update_post_meta( $post_id, 'ids_status', ''.$status);
        }

        if ($web_price) { update_post_meta( $post_id, 'ids_price', ''.$web_price); }
        if ($msrp) {
                update_post_meta( $post_id, 'ids_msrp', ''.$msrp);
            } else {
                update_post_meta( $post_id, 'ids_msrp', '0');
            }
        if ($length && $length > 0) {
                update_post_meta( $post_id, 'ids_length', ''.$length);
            } else {
                update_post_meta( $post_id, 'ids_length', "0'");
            }

        // set the trailer type
        if ( !get_term_by( 'slug', $type_slug, $inventory_type_term ) ){
          $type_term_id = wp_insert_term($type, $inventory_type_term, $args = array(
            'slug' => $type_slug,
          ));
        } else {
          $type_retrive_term = get_term_by( 'slug', $type_slug, $inventory_type_term );
          $type_term_id = ($type_retrive_term) ? $type_retrive_term->term_id : false ;

          // set any parent terms if they exist
          $type_parent_term = get_term( $type_retrive_term->parent, $inventory_type_term );
          if ($type_parent_term)
          {
            wp_set_post_terms( $post_id, $type_parent_term->term_id, $inventory_type_term, true );
          }
        }
        if ($type_term_id) {
          wp_set_post_terms( $post_id, $type_term_id, $inventory_type_term, true );
        }

        // set the trailer manufacturer
        if ( !get_term_by( 'slug', $manufacturer_slug, $inventory_manufacturer_term ) ){
          // create trailer manufacturer term
          $manufacturer_term_id = wp_insert_term($manufacturer, $inventory_manufacturer_term, $args = array(
            'slug' => $manufacturer_slug,
          ));
        } else {
          // retrieve trailer manufacturer term
          $manufacturer_retrive_term = get_term_by( 'slug', $manufacturer_slug, $inventory_manufacturer_term );
          $manufacturer_term_id = ($manufacturer_retrive_term) ? $manufacturer_retrive_term->term_id : false ;

          // set any parent terms if they exist
          $manufacturer_parent_term = get_term( $manufacturer_retrive_term->parent, $inventory_manufacturer_term );
          if ($manufacturer_parent_term)
          {
            wp_set_post_terms( $post_id, $manufacturer_parent_term->term_id, $inventory_manufacturer_term, true );
          }
        }
        if ($manufacturer_term_id) {
          wp_set_post_terms( $post_id, $manufacturer_term_id, $inventory_manufacturer_term, true );
        }

        // set the trailer brand if different from manufacturer
        if ($brand != $manufacturer){
            // set the trailer brand
            if ( !get_term_by( 'slug', $brand_slug, $inventory_brand_term ) ){
              // create trailer brand term
              $brand_term_id = wp_insert_term($brand, $inventory_brand_term, $args = array(
                'slug' => $brand_slug,
              ));
            } else {
              // retrieve trailer brand term
              $brand_retrive_term = get_term_by( 'slug', $brand_slug, $inventory_brand_term );
              $brand_term_id = ($brand_retrive_term) ? $brand_retrive_term->term_id : false ;

              // set any parent terms if they exist
              $brand_parent_term = get_term( $brand_retrive_term->parent, $inventory_brand_term );
              if ($brand_parent_term)
              {
                wp_set_post_terms( $post_id, $brand_parent_term->term_id, $inventory_brand_term, true );
              }
            }
            if ($brand_term_id) {
              wp_set_post_terms( $post_id, $brand_term_id, $inventory_brand_term, true );
            }
        }

        // Update Trailer Specs
        if (!empty($specs)){
          $specCount = 0;

          foreach($specs as $specKey => $specValue)
          {
            if ($specValue) {
              update_post_meta( $post_id, 'web_spec_'. $specCount .'_name', ''.$specKey );
              update_post_meta( $post_id, 'web_spec_'. $specCount .'_value', ''.$specValue );

              ++$specCount;
            }
          }

          $field_key = "";
          if ($post_type == "rv")
          {
            $field_key = "field_52825b8609ef1";
          } else if ($post_type == 'trailer')
          {
            $field_key = "field_52825b8609ef1";
          }

          if (!empty($field_key))
          {
            $spec_value = get_field($field_key, $post_id);
            update_field($field_key, $spec_value, $post_id );
          }

          update_post_meta ( $post_id, 'web_spec', ''.$specCount ); // add the total count for repeater field
        }
      } // trailer exists post id
    } // trailer exists
  } else {
    // invalid trailer data
    // do nothing
  }
} // inject trailer

function import_trailers(){
  $trailer_import_xml_options = get_option( 'trailer_import_xmlfile' );

  $xml_dir = ABSPATH.$trailer_import_xml_options['xml_file_path'];
    $file_prefix = $trailer_import_xml_options['xml_file_prefix'];
    $date_format = $trailer_import_xml_options['xml_file_dateformat'];

    $xml_trailer_stocks = array();

    if ($handle = opendir($xml_dir))
    {
        $entries = array();
        $date = date( $date_format, current_time( 'timestamp', 0 ) );

        while (FALSE !== ($entry = readdir($handle)))
        {
            if ($entry != '.' && $entry != '..')
            {
                if (preg_match("/{$file_prefix}{$date}_(\d+).XML/", $entry, $matches) > 0)
                {
                    $entries[$entry] = $matches[1];
                }
            }
        }

        if (count($entries) > 0)
        {
            $latest = max($entries);

            // process each trailer in the XML for importing or updating
            $xml = simplexml_load_file($xml_dir.$file_prefix.$date."_".$latest.".XML");
      foreach ($xml->children() as $trailer) {
        // import or update the trailer
        inject_trailer($trailer);
        // add trailer stock number to array of trailers in xml
        array_push($xml_trailer_stocks, $trailer->stock_number);
      }

      // compare trailers in wordpress with xml to delete old trailers
      $all_trailers_args = array(
        'post_type' => 'trailer',
        'post_status' => 'publish',
        'posts_per_page' => -1
      );

      $all_trailers = get_posts( $all_trailers_args );
      foreach ($all_trailers as $wp_trailer)
      {
        // determine if the trailer by Stock Number is in the XML file
        // if not send it to trash in wordpress
        $wp_trailer_stock_number = get_post_meta($wp_trailer->ID, 'ids_stock_number', true);

        if ( !in_array($wp_trailer_stock_number, $xml_trailer_stocks) )
        {
          // trash the trailer, don't perm delete incase bringing trailer back is wanted
          // @todo add option for trailer stock numbers not to delete
          wp_trash_post($wp_trailer->ID);
        }
      }

      // compare rv in wordpress with xml to delete old rv
      $all_rvs_args = array(
        'post_type' => 'rv',
        'post_status' => 'publish',
        'posts_per_page' => -1
      );

      $allrvs = get_posts( $all_rvs_args );
      foreach ($allrvs as $wp_rv)
      {
        // determine if the trailer by Stock Number is in the XML file
        // if not send it to trash in wordpress
        $wp_rv_stock_number = get_post_meta($wp_rv->ID, 'ids_stock_number', true);

        if ( !in_array($wp_rv_stock_number, $xml_trailer_stocks) )
        {
          // trash the rv, don't perm delete incase bringing rv back is wanted
          // @todo add option for rv stock numbers not to delete
          wp_trash_post($wp_rv->ID);
        }
      }
    } else {
            // no xml files for today, do nothing
        }
    } else {
      // no xml directory as set found, do nothing
    }
}
add_action('import_trailers','import_trailers');

function create_trailer_feed_ctt(){
    $xml_dir = ABSPATH.'/trailer-feeds/';
    // gather trailer data
    $trailerCount = 0;
    $all_trailers_args = array(
        'post_type' => 'trailer',
        'post_status' => 'publish',
        'posts_per_page' => -1
    );
    $all_trailers = get_posts( $all_trailers_args );
    $total_trailers = count($all_trailers);
    // create the XML file contents
    $xml = "<ROWSET>\n\t";
    foreach ($all_trailers as $wp_trailer) {
        $wp_trailer_stock_number = get_post_meta($wp_trailer->ID, 'ids_stock_number', true);
        $trailer_brands = wp_get_post_terms( $wp_trailer->ID, 'trailer-brand' );
        if ( count($trailer_brands) ){
            $manufacturer = $trailer_brands[0]->name;
        } else {
            $manufacturer = "Other";
        }
        $trailer_types = wp_get_post_terms( $wp_trailer->ID, 'trailer-type', true );
        if ( count($trailer_types) ){
            $trailer_type = $trailer_types[0]->name;
        } else {
            $trailer_type = "Other";
        }

        $trailerCount = $trailerCount+1;

        $xml .= "<ROW NUM=\"". $trailerCount ."\">\n\t\t";
            $xml .= "<UNIQUE_ID>".$wp_trailer_stock_number."</UNIQUE_ID>\n\t\t";
            $xml .= "<CLASS/>\n\t\t";
            $xml .= "<MANUFACTURER>".$manufacturer."</MANUFACTURER>\n\t\t";
            $xml .= "<MODEL>".get_post_meta( $wp_trailer->ID, 'ids_model', true )."</MODEL>\n\t\t";
            $xml .= "<YEAR>".get_post_meta( $wp_trailer->ID, 'ids_year', true )."</YEAR>\n\t\t";
            $xml .= "<PRICE>\n\t\t";
                if (get_post_meta( $wp_trailer->ID, 'ids_price', true ) && get_post_meta( $wp_trailer->ID, 'ids_price', true ) != '0.00') {
                    $xml .= number_format(get_post_meta( $wp_trailer->ID, 'ids_price', true )) . "\n\t\t";
                } else {
                    $xml .= number_format(get_post_meta( $wp_trailer->ID, 'ids_msrp', true )) . "\n\t\t";
                }
            $xml .= "</PRICE>\n\t\t";
            $xml .= "<DESCRIPTION/>\n\t\t";
            $xml .= "<DEALER_CITY>Concord</DEALER_CITY>\n\t\t";
            $xml .= "<DEALER_STATE>NC</DEALER_STATE>\n\t\t";
            $xml .= "<DEALER_ZIP>28027</DEALER_ZIP>\n\t\t";
            $xml .= "<DEALER_PHONE>800-895-3276</DEALER_PHONE>\n\t\t";
            // trailer photo's here
            $trailer_photo_count = 0;
            $trailer_photos = get_post_meta( $wp_trailer->ID, 'web_photos', true );
            if ($trailer_photos){
                $trailer_photo_count = count($trailer_photos);
            }
            if ($trailer_photo_count > 0) {
               $photoCount = 0;
               foreach ($trailer_photos as $photo){
                $photoCount = $photoCount+1;
                $xml .= "<PHOTO".$photoCount.">".wp_get_attachment_url( $photo )."</PHOTO".$photoCount.">\n\t\t";
               }
            }
            $xml .= "<CATEGORY>TRAILERS</CATEGORY>\n\t\t";
            $xml .= "<TYPE>".$trailer_type."</TYPE>\n\t\t";
            $xml .= "<STOCK_NUMBER>".$wp_trailer_stock_number."</STOCK_NUMBER>\n\t\t";
            $xml .= "<AXLES/>\n\t\t";
            $xml .= "<ENGINE_MANUFACTURER/>\n\t\t";
            $xml .= "<ENGINE_MODEL/>\n\t\t";
            $xml .= "<FUEL_TYPE/>\n\t\t";
            $xml .= "<HORSEPOWER/>\n\t\t";
            $xml .= "<MILEAGE/>\n\t\t";
            $xml .= "<REAR_AXLES/>\n\t\t";
            $xml .= "<TRANSMISSION_MAKE/>\n\t\t";
            $xml .= "<TRANSMISSION_SPEED/>\n\t\t";
            $xml .= "<VIN_NUMBER/>\n\t\t";
            $xml .= "<AD_DETAIL_URL>".get_permalink( $wp_trailer->ID )."</AD_DETAIL_URL>\n\t";
        if ($trailerCount === $total_trailers) {
            $xml .= "</ROW>\n";
        } else {
            $xml .= "</ROW>\n\t";
        }
    }
    $xml .= "</ROWSET>";
    $xmlobj = new SimpleXMLElement($xml);
    // save the XML file with overwrite
    $xmlobj->asXML($xml_dir."commercial-truck-trader.xml");
}
add_action('create_trailer_feed_ctt','create_trailer_feed_ctt');

/* This function is executed when the user activates the plugin */
function trvtrailers_activation(){
    Trailer();
    TrailerTypes();
    TrailerBrands();

    RV();
    RVTypes();
    RVBrands();

    wp_schedule_event(time(), 'hourly', 'import_trailers');
    wp_schedule_event(time(), 'hourly', 'create_trailer_feed_ctt');

    // ATTENTION: This is *only* done during plugin activation hook in this example!
    // You should *NEVER EVER* do this on every page load!!
    flush_rewrite_rules();
}
/* This function is executed when the user deactivates the plugin */
function trvtrailers_deactivation(){
    wp_clear_scheduled_hook('import_trailers');
    wp_clear_scheduled_hook('create_trailer_feed_ctt');

    // ATTENTION: This is *only* done during plugin activation hook in this example!
    // You should *NEVER EVER* do this on every page load!!
    flush_rewrite_rules();
}

/* The activation hook is executed when the plugin is activated. */
register_activation_hook(__FILE__,'trvtrailers_activation');
/* The deactivation hook is executed when the plugin is deactivated */
register_deactivation_hook(__FILE__,'trvtrailers_deactivation');

// Trailer Custom Post Type
if ( ! function_exists('Trailer') ) {
  // Register Custom Post Type
  function Trailer() {

    $labels = array(
      'name'                => _x( 'Trailers', 'Post Type General Name', 'text_domain' ),
      'singular_name'       => _x( 'Trailer', 'Post Type Singular Name', 'text_domain' ),
      'menu_name'           => __( 'Trailers', 'text_domain' ),
      'parent_item_colon'   => __( 'Parent Trailer:', 'text_domain' ),
      'all_items'           => __( 'All Trailers', 'text_domain' ),
      'view_item'           => __( 'View Trailer', 'text_domain' ),
      'add_new_item'        => __( 'Add New Trailer', 'text_domain' ),
      'add_new'             => __( 'New Trailer', 'text_domain' ),
      'edit_item'           => __( 'Edit Trailer', 'text_domain' ),
      'update_item'         => __( 'Update Trailer', 'text_domain' ),
      'search_items'        => __( 'Search trailers', 'text_domain' ),
      'not_found'           => __( 'No trailers found', 'text_domain' ),
      'not_found_in_trash'  => __( 'No trailers found in Trash', 'text_domain' ),
    );
    $rewrite = array(
      'slug'                => 'trailers',
      'with_front'          => true,
      'pages'               => true,
      'feeds'               => true,
    );
    $args = array(
      'label'               => __( 'trailer', 'text_domain' ),
      'description'         => __( 'Trailer information pages', 'text_domain' ),
      'labels'              => $labels,
      'supports'            => array( 'title', 'editor', 'thumbnail', 'comments', 'trackbacks', 'revisions', 'custom-fields', 'page-attributes', 'post-formats', ),
      'taxonomies'          => array( 'trailer-type', 'trailer-brand' ),
      'hierarchical'        => false,
      'public'              => true,
      'show_ui'             => true,
      'show_in_menu'        => true,
      'show_in_nav_menus'   => true,
      'show_in_admin_bar'   => true,
      'menu_position'       => 5,
      'menu_icon'           => '',
      'can_export'          => true,
      'has_archive'         => true,
      'exclude_from_search' => false,
      'publicly_queryable'  => true,
      'rewrite'             => $rewrite,
      'capability_type'     => 'post',
    );
    register_post_type( 'trailer', $args );

  }

  // Hook into the 'init' action
  add_action( 'init', 'Trailer', 0 );
}

// Trailer Types
if ( ! function_exists('TrailerTypes') ) {
  // Register Custom Taxonomy
  function TrailerTypes()  {

    $labels = array(
      'name'                       => _x( 'Trailer Types', 'Taxonomy General Name', 'text_domain' ),
      'singular_name'              => _x( 'Trailer Type', 'Taxonomy Singular Name', 'text_domain' ),
      'menu_name'                  => __( 'Trailer Type', 'text_domain' ),
      'all_items'                  => __( 'All Trailer Types', 'text_domain' ),
      'parent_item'                => __( 'Parent Trailer Type', 'text_domain' ),
      'parent_item_colon'          => __( 'Parent Trailer Type:', 'text_domain' ),
      'new_item_name'              => __( 'New Trailer Type Name', 'text_domain' ),
      'add_new_item'               => __( 'Add New Trailer Type', 'text_domain' ),
      'edit_item'                  => __( 'Edit Trailer Type', 'text_domain' ),
      'update_item'                => __( 'Update Trailer Type', 'text_domain' ),
      'separate_items_with_commas' => __( 'Separate trailer types with commas', 'text_domain' ),
      'search_items'               => __( 'Search trailer types', 'text_domain' ),
      'add_or_remove_items'        => __( 'Add or remove trailer types', 'text_domain' ),
      'choose_from_most_used'      => __( 'Choose from the most used trailer types', 'text_domain' ),
    );
    $args = array(
      'labels'                     => $labels,
      'hierarchical'               => true,
      'public'                     => true,
      'show_ui'                    => true,
      'show_admin_column'          => true,
      'show_in_nav_menus'          => true,
      'show_tagcloud'              => true,
    );
    register_taxonomy( 'trailer-type', 'trailer', $args );

  }

  // Hook into the 'init' action
  add_action( 'init', 'TrailerTypes', 0 );
}

// Trailer Brands
if ( ! function_exists('TrailerBrands') ) {
  // Register Custom Taxonomy
  function TrailerBrands()  {

    $labels = array(
      'name'                       => _x( 'Trailer Brands', 'Taxonomy General Name', 'text_domain' ),
      'singular_name'              => _x( 'Trailer Brand', 'Taxonomy Singular Name', 'text_domain' ),
      'menu_name'                  => __( 'Trailer Brand', 'text_domain' ),
      'all_items'                  => __( 'All Trailer Brands', 'text_domain' ),
      'parent_item'                => __( 'Parent Trailer Brand', 'text_domain' ),
      'parent_item_colon'          => __( 'Parent Trailer Brand:', 'text_domain' ),
      'new_item_name'              => __( 'New Trailer Brand Name', 'text_domain' ),
      'add_new_item'               => __( 'Add New Trailer Brand', 'text_domain' ),
      'edit_item'                  => __( 'Edit Trailer Brand', 'text_domain' ),
      'update_item'                => __( 'Update Trailer Brand', 'text_domain' ),
      'separate_items_with_commas' => __( 'Separate trailer brands with commas', 'text_domain' ),
      'search_items'               => __( 'Search trailer brands', 'text_domain' ),
      'add_or_remove_items'        => __( 'Add or remove trailer brands', 'text_domain' ),
      'choose_from_most_used'      => __( 'Choose from the most used trailer brands', 'text_domain' ),
    );
    $args = array(
      'labels'                     => $labels,
      'hierarchical'               => true,
      'public'                     => true,
      'show_ui'                    => true,
      'show_admin_column'          => true,
      'show_in_nav_menus'          => true,
      'show_tagcloud'              => true,
    );
    register_taxonomy( 'trailer-brand', 'trailer', $args );

  }

  // Hook into the 'init' action
  add_action( 'init', 'TrailerBrands', 0 );
}

// RV Custom Post Type
if ( ! function_exists('RV') ) {
  // Register Custom Post Type
  function RV() {

    $labels = array(
      'name'                => _x( 'RVs', 'Post Type General Name', 'text_domain' ),
      'singular_name'       => _x( 'RV', 'Post Type Singular Name', 'text_domain' ),
      'menu_name'           => __( 'RVs', 'text_domain' ),
      'parent_item_colon'   => __( 'Parent RV:', 'text_domain' ),
      'all_items'           => __( 'All RVs', 'text_domain' ),
      'view_item'           => __( 'View RV', 'text_domain' ),
      'add_new_item'        => __( 'Add New RV', 'text_domain' ),
      'add_new'             => __( 'New RV', 'text_domain' ),
      'edit_item'           => __( 'Edit RV', 'text_domain' ),
      'update_item'         => __( 'Update RV', 'text_domain' ),
      'search_items'        => __( 'Search RVs', 'text_domain' ),
      'not_found'           => __( 'No RVs found', 'text_domain' ),
      'not_found_in_trash'  => __( 'No RVs found in Trash', 'text_domain' ),
    );
    $rewrite = array(
      'slug'                => 'rvs',
      'with_front'          => true,
      'pages'               => true,
      'feeds'               => true,
    );
    $args = array(
      'label'               => __( 'rv', 'text_domain' ),
      'description'         => __( 'RV information pages', 'text_domain' ),
      'labels'              => $labels,
      'supports'            => array( 'title', 'editor', 'thumbnail', 'comments', 'trackbacks', 'revisions', 'custom-fields', 'page-attributes', 'post-formats', ),
      'taxonomies'          => array( 'rv-type', 'rv-brand' ),
      'hierarchical'        => false,
      'public'              => true,
      'show_ui'             => true,
      'show_in_menu'        => true,
      'show_in_nav_menus'   => true,
      'show_in_admin_bar'   => true,
      'menu_position'       => 5,
      'menu_icon'           => '',
      'can_export'          => true,
      'has_archive'         => true,
      'exclude_from_search' => false,
      'publicly_queryable'  => true,
      'rewrite'             => $rewrite,
      'capability_type'     => 'post',
    );
    register_post_type( 'rv', $args );

  }

  // Hook into the 'init' action
  add_action( 'init', 'RV', 0 );
}

// RV Types
if ( ! function_exists('RVTypes') ) {
  // Register Custom Taxonomy
  function RVTypes()  {

    $labels = array(
      'name'                       => _x( 'RV Types', 'Taxonomy General Name', 'text_domain' ),
      'singular_name'              => _x( 'RV Type', 'Taxonomy Singular Name', 'text_domain' ),
      'menu_name'                  => __( 'RV Type', 'text_domain' ),
      'all_items'                  => __( 'All RV Types', 'text_domain' ),
      'parent_item'                => __( 'Parent RV Type', 'text_domain' ),
      'parent_item_colon'          => __( 'Parent RV Type:', 'text_domain' ),
      'new_item_name'              => __( 'New RV Type Name', 'text_domain' ),
      'add_new_item'               => __( 'Add New RV Type', 'text_domain' ),
      'edit_item'                  => __( 'Edit RV Type', 'text_domain' ),
      'update_item'                => __( 'Update RV Type', 'text_domain' ),
      'separate_items_with_commas' => __( 'Separate RV types with commas', 'text_domain' ),
      'search_items'               => __( 'Search RV types', 'text_domain' ),
      'add_or_remove_items'        => __( 'Add or remove RV types', 'text_domain' ),
      'choose_from_most_used'      => __( 'Choose from the most used RV types', 'text_domain' ),
    );
    $args = array(
      'labels'                     => $labels,
      'hierarchical'               => true,
      'public'                     => true,
      'show_ui'                    => true,
      'show_admin_column'          => true,
      'show_in_nav_menus'          => true,
      'show_tagcloud'              => true,
    );
    register_taxonomy( 'rv-type', 'rv', $args );

  }

  // Hook into the 'init' action
  add_action( 'init', 'RVTypes', 0 );
}

// RV Brands
if ( ! function_exists('RVBrands') ) {
  // Register Custom Taxonomy
  function RVBrands()  {

    $labels = array(
      'name'                       => _x( 'RV Brands', 'Taxonomy General Name', 'text_domain' ),
      'singular_name'              => _x( 'RV Brand', 'Taxonomy Singular Name', 'text_domain' ),
      'menu_name'                  => __( 'RV Brand', 'text_domain' ),
      'all_items'                  => __( 'All RV Brands', 'text_domain' ),
      'parent_item'                => __( 'Parent RV Brand', 'text_domain' ),
      'parent_item_colon'          => __( 'Parent RV Brand:', 'text_domain' ),
      'new_item_name'              => __( 'New RV Brand Name', 'text_domain' ),
      'add_new_item'               => __( 'Add New RV Brand', 'text_domain' ),
      'edit_item'                  => __( 'Edit RV Brand', 'text_domain' ),
      'update_item'                => __( 'Update RV Brand', 'text_domain' ),
      'separate_items_with_commas' => __( 'Separate RV brands with commas', 'text_domain' ),
      'search_items'               => __( 'Search RV brands', 'text_domain' ),
      'add_or_remove_items'        => __( 'Add or remove RV brands', 'text_domain' ),
      'choose_from_most_used'      => __( 'Choose from the most used RV brands', 'text_domain' ),
    );
    $args = array(
      'labels'                     => $labels,
      'hierarchical'               => true,
      'public'                     => true,
      'show_ui'                    => true,
      'show_admin_column'          => true,
      'show_in_nav_menus'          => true,
      'show_tagcloud'              => true,
    );
    register_taxonomy( 'rv-brand', 'rv', $args );

  }

  // Hook into the 'init' action
  add_action( 'init', 'RVBrands', 0 );
}

// register Trailer_Filters widget
require_once(plugin_dir_path( __FILE__ ).'/widgets/trailer-filters.php');
function register_trailer_filters() {
    register_widget( 'Trailer_Filters' );
}
add_action( 'widgets_init', 'register_trailer_filters' );

// register Trailer_By_Stock widget
require_once(plugin_dir_path( __FILE__ ).'/widgets/trailer-by-stock.php');
function register_trailer_by_stock() {
    register_widget( 'Trailer_By_Stock' );
}
add_action( 'widgets_init', 'register_trailer_by_stock' );

// register RV_Filters widget
require_once(plugin_dir_path( __FILE__ ).'/widgets/rv-filters.php');
function register_rv_filters() {
    register_widget( 'RV_Filters' );
}
add_action( 'widgets_init', 'register_rv_filters' );

// register RV_By_Stock widget
require_once(plugin_dir_path( __FILE__ ).'/widgets/rv-by-stock.php');
function register_rv_by_stock() {
    register_widget( 'RV_By_Stock' );
}
add_action( 'widgets_init', 'register_rv_by_stock' );

// trailer templates
function trailer_templates( $template ) {
    $post_types = array( 'trailer' );

    if ( is_post_type_archive( $post_types ) && ! file_exists( get_stylesheet_directory() . '/archive-trailer.php' ) ) {
      $template = plugin_dir_path( __FILE__ ).'/templates/archive-trailer.php';
    }
    if ( is_singular( $post_types ) && ! file_exists( get_stylesheet_directory() . '/single-trailer.php' ) ) {
      $template = plugin_dir_path( __FILE__ ).'/templates/single-trailer.php';
    }

    return $template;
}
add_filter( 'template_include', 'trailer_templates' );

// rv templates
function rv_templates( $template ) {
    $post_types = array( 'rv' );

    if ( is_post_type_archive( $post_types ) && ! file_exists( get_stylesheet_directory() . '/archive-rv.php' ) ) {
      $template = plugin_dir_path( __FILE__ ).'/templates/archive-rv.php';
    }
    if ( is_singular( $post_types ) && ! file_exists( get_stylesheet_directory() . '/single-rv.php' ) ) {
      $template = plugin_dir_path( __FILE__ ).'/templates/single-rv.php';
    }

    return $template;
}
add_filter( 'template_include', 'rv_templates' );


/**
 * Provide a Settings Page for this Trailer Import Plugin
 * Some options for example are the XML File info
 * and What Trailers not to import by type, brand, designation, or Stock Number.
 */
class TrailerSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin',
            'Trailer Import',
            'manage_options',
            'trailer-setting-admin',
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        $this->options = get_option( 'trailer_import_ignores' );
        $this->xml_options = get_option( 'trailer_import_xmlfile' );
        $this->display_options = get_option( 'trailer_import_display' );
        $this->rv_display_options = get_option( 'rv_import_display' );
        $this->rv_ignore_options = get_option( 'rv_import_ignores' );
        $this->rv_import_options = get_option( 'rv_import' );

        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>Trailer Import Settings</h2>
            <div>
                <form method="post" action="options-general.php?page=trailer-setting-admin">
                    <h3>Manually Run Trailer Import</h3>
                    <p class="description">
                        In the event that an emergency or immediate trailer import update is needed<br />
                        Run the <strong>Server Job</strong> to build the XML data file and push it to the WebServer<br />
                        Then after that is done, click the below button to run the WordPress Trailer XML Import manually.
                    </p>
                    <input type="hidden" name="run_import" value="true">
                    <p><input type="submit" class="button-primary" value="Run Trailer XML Import" /></p>
                </form>
            </div>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'trailer_option_group' );
                settings_fields( 'rv_option_group' );
                do_settings_sections( 'trailer-setting-admin' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        if( $_POST['run_import'] == true && is_admin() ){
            // run trailer import manually
            import_trailers();
            function trailer_import_manual_notice() {
                ?>
                <div class="updated">
                    <p>Manual Trailer Import Executed!</p>
                </div>
                <?php
            }
            add_action( 'admin_notices', 'trailer_import_manual_notice' );
        }
        register_setting(
            'trailer_option_group', // Option group
            'trailer_import_ignores', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        register_setting(
            'trailer_option_group', // Option group
            'trailer_import_xmlfile', // Option name
            array( $this, 'sanitize_xml' ) // Sanitize
        );

        register_setting(
            'trailer_option_group', // Option group
            'trailer_import_display', // Option name
            array( $this, 'sanitize_display' ) // Sanitize
        );

        add_settings_section(
            'trailer_import_display_section', // ID
            'Display Trailers Settings', // Title
            array( $this, 'display_section_info' ), // Callback
            'trailer-setting-admin' // Page
        );

        add_settings_field(
            'inquire_form_id', // Inquire Form ID
            'Inquire Form ID', // Title
            array( $this, 'inquire_form_id_callback' ), // Callback
            'trailer-setting-admin', // Page
            'trailer_import_display_section' // Section
        );

        add_settings_field(
            'slider_id', // Inquire Form ID
            'Trailer Photos Slider ID', // Title
            array( $this, 'slider_id_callback' ), // Callback
            'trailer-setting-admin', // Page
            'trailer_import_display_section' // Section
        );

        add_settings_section(
            'trailer_import_ignore_section', // ID
            'Trailers to Ignore on Import', // Title
            array( $this, 'print_section_info' ), // Callback
            'trailer-setting-admin' // Page
        );

        add_settings_field(
            'trailer_type', // Trailer Type
            'Trailer Types to Ignore', // Title
            array( $this, 'trailer_type_callback' ), // Callback
            'trailer-setting-admin', // Page
            'trailer_import_ignore_section' // Section
        );

        add_settings_field(
            'trailer_brand', // Trailer Brand
            'Trailer Brands to Ignore', // Title
            array( $this, 'trailer_brand_callback' ), // Callback
            'trailer-setting-admin', // Page
            'trailer_import_ignore_section' // Section
        );

        add_settings_field(
            'trailer_designation', // Trailer Designation
            'Trailer Designations to Ignore', // Title
            array( $this, 'trailer_designation_callback' ), // Callback
            'trailer-setting-admin', // Page
            'trailer_import_ignore_section' // Section
        );

        add_settings_field(
            'trailer_stocknums', // Trailer Designation
            'Trailer Stock Numbers to Ignore', // Title
            array( $this, 'trailer_stocknums_callback' ), // Callback
            'trailer-setting-admin', // Page
            'trailer_import_ignore_section' // Section
        );

        add_settings_section(
            'trailer_import_xmlfile_section', // ID
            'XML File Import Settings', // Title
            array( $this, 'xml_section_info' ), // Callback
            'trailer-setting-admin' // Page
        );

        add_settings_field(
            'xml_file_path_callback', // XML File Path
            'XML File Path', // Title
            array( $this, 'xml_file_path_callback' ), // Callback
            'trailer-setting-admin', // Page
            'trailer_import_xmlfile_section' // Section
        );

        add_settings_field(
            'xml_file_prefix', // XML File Prefix
            'XML File Prefix', // Title
            array( $this, 'xml_file_prefix_callback' ), // Callback
            'trailer-setting-admin', // Page
            'trailer_import_xmlfile_section' // Section
        );

        add_settings_field(
            'xml_file_dateformat', // XML File Prefix
            'XML File Date Format', // Title
            array( $this, 'xml_file_dateformat_callback' ), // Callback
            'trailer-setting-admin', // Page
            'trailer_import_xmlfile_section' // Section
        );

        // RVs
        register_setting(
            'rv_option_group', // Option group
            'rv_import', // Option name
            array( $this, 'sanitize_rv' ) // Sanitize
        );

        register_setting(
            'rv_option_group', // Option group
            'rv_import_ignores', // Option name
            array( $this, 'sanitize_rv_ignores' ) // Sanitize
        );

        register_setting(
            'rv_option_group', // Option group
            'rv_import_display', // Option name
            array( $this, 'sanitize_display_rv' ) // Sanitize
        );

        add_settings_section(
            'rv_import_display_section', // ID
            'Display RV\'s Settings', // Title
            array( $this, 'rv_display_section_info' ), // Callback
            'trailer-setting-admin' // Page
        );

        add_settings_field(
            'rv_types', // RV Import Types
            'RV Import Types', // Title
            array( $this, 'rv_types_callback' ), // Callback
            'trailer-setting-admin', // Page
            'rv_import_display_section' // Section
        );

        add_settings_field(
            'rv_inquire_form_id', // Inquire Form ID
            'RV Inquire Form ID', // Title
            array( $this, 'rv_inquire_form_id_callback' ), // Callback
            'trailer-setting-admin', // Page
            'rv_import_display_section' // Section
        );

        add_settings_field(
            'rv_slider_id', // Inquire Form ID
            'RV Photos Slider ID', // Title
            array( $this, 'rv_slider_id_callback' ), // Callback
            'trailer-setting-admin', // Page
            'rv_import_display_section' // Section
        );

        add_settings_section(
            'rv_import_ignore_section', // ID
            'RV\'s to Ignore on Import', // Title
            array( $this, 'rv_print_section_info' ), // Callback
            'trailer-setting-admin' // Page
        );

        add_settings_field(
            'rv_type', // RV Type
            'RV Types to Ignore', // Title
            array( $this, 'rv_type_callback' ), // Callback
            'trailer-setting-admin', // Page
            'rv_import_ignore_section' // Section
        );

        add_settings_field(
            'rv_brand', // RV Brand
            'RV Brands to Ignore', // Title
            array( $this, 'rv_brand_callback' ), // Callback
            'trailer-setting-admin', // Page
            'rv_import_ignore_section' // Section
        );

        add_settings_field(
            'rv_designation', // RV Designation
            'RV Designations to Ignore', // Title
            array( $this, 'rv_designation_callback' ), // Callback
            'trailer-setting-admin', // Page
            'rv_import_ignore_section' // Section
        );

        add_settings_field(
            'rv_stocknums', // RV Designation
            'RV Stock Numbers to Ignore', // Title
            array( $this, 'rv_stocknums_callback' ), // Callback
            'trailer-setting-admin', // Page
            'rv_import_ignore_section' // Section
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();

        if( isset( $input['trailer_type'] ) )
            $new_input['trailer_type'] = sanitize_text_field( $input['trailer_type'] );

        if( isset( $input['trailer_brand'] ) )
            $new_input['trailer_brand'] = sanitize_text_field( $input['trailer_brand'] );

        if( isset( $input['trailer_designation'] ) )
            $new_input['trailer_designation'] = sanitize_text_field( $input['trailer_designation'] );

        if( isset( $input['trailer_stocknums'] ) )
            $new_input['trailer_stocknums'] = sanitize_text_field( $input['trailer_stocknums'] );

        return $new_input;
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize_rv( $input )
    {
        $new_input = array();

        if( isset( $input['rv_types'] ) )
            $new_input['rv_types'] = sanitize_text_field( $input['rv_types'] );

        return $new_input;
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize_rv_ignores( $input )
    {
        $new_input = array();

        if( isset( $input['rv_type'] ) )
            $new_input['rv_type'] = sanitize_text_field( $input['rv_type'] );

        if( isset( $input['rv_brand'] ) )
            $new_input['rv_brand'] = sanitize_text_field( $input['rv_brand'] );

        if( isset( $input['rv_designation'] ) )
            $new_input['rv_designation'] = sanitize_text_field( $input['rv_designation'] );

        if( isset( $input['rv_stocknums'] ) )
            $new_input['rv_stocknums'] = sanitize_text_field( $input['rv_stocknums'] );

        return $new_input;
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize_xml( $input )
    {
        $new_input = array();

        if( isset( $input['xml_file_path'] ) )
            $new_input['xml_file_path'] = sanitize_text_field( $input['xml_file_path'] );

        if( isset( $input['xml_file_prefix'] ) )
            $new_input['xml_file_prefix'] = sanitize_text_field( $input['xml_file_prefix'] );

        if( isset( $input['xml_file_dateformat'] ) )
            $new_input['xml_file_dateformat'] = sanitize_text_field( $input['xml_file_dateformat'] );

        return $new_input;
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize_display( $input )
    {
        $new_input = array();

        if( isset( $input['inquire_form_id'] ) )
            $new_input['inquire_form_id'] = sanitize_text_field( $input['inquire_form_id'] );

        if( isset( $input['slider_id'] ) )
            $new_input['slider_id'] = sanitize_text_field( $input['slider_id'] );

        return $new_input;
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize_display_rv( $input )
    {
        $new_input = array();

        if( isset( $input['rv_inquire_form_id'] ) )
            $new_input['rv_inquire_form_id'] = sanitize_text_field( $input['rv_inquire_form_id'] );

        if( isset( $input['rv_slider_id'] ) )
            $new_input['rv_slider_id'] = sanitize_text_field( $input['rv_slider_id'] );

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        // print 'Trailers to Ignore on Import';
    }

    /**
     * Print the Section text
     */
    public function xml_section_info()
    {
        print '<div>These settings are used to determine the xml file to import.  Be careful if making any changes you could break the import process.</div>';
    }

    /**
     * Print the Section text
     */
    public function display_section_info()
    {
        print '<div>Settings in this section control the elements used on trailer display.</div>';
    }

    /**
     * Print the RV Section text
     */
    public function rv_display_section_info()
    {
        print '<div>Settings in this section control the elements used on RV display.</div>';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function trailer_type_callback()
    {
        printf(
            '<input type="text" id="trailer_type" name="trailer_import_ignores[trailer_type]" value="%s" />',
            isset( $this->options['trailer_type'] ) ? esc_attr( $this->options['trailer_type']) : ''
        );
        printf(
          '<p class="description">Comma seperated list of trailer types from IDS not to import.<br />E.g. COMPANY VEHICLE,RENTAL UNIT</p>'
      );
    }

    public function trailer_brand_callback()
    {
        printf(
            '<input type="text" id="trailer_brand" name="trailer_import_ignores[trailer_brand]" value="%s" />',
            isset( $this->options['trailer_brand'] ) ? esc_attr( $this->options['trailer_brand']) : ''
        );
        printf(
          '<p class="description">Comma seperated list of trailer brands from IDS not to import.<br />E.g. PACE, FEATHERLITE</p>'
      );
    }

    public function trailer_designation_callback()
    {
        printf(
            '<input type="text" id="trailer_designation" name="trailer_import_ignores[trailer_designation]" value="%s" />',
            isset( $this->options['trailer_designation'] ) ? esc_attr( $this->options['trailer_designation']) : ''
        );
        printf(
          '<p class="description">Comma seperated list of trailer designations from IDS not to import.<br />E.g. RENTALS,NEW</p>'
      );
    }

    public function trailer_stocknums_callback()
    {
        printf(
            '<input type="text" id="trailer_stocknums" name="trailer_import_ignores[trailer_stocknums]" value="%s" />',
            isset( $this->options['trailer_stocknums'] ) ? esc_attr( $this->options['trailer_stocknums']) : ''
        );
        printf(
          '<p class="description">Comma seperated list of trailer Stock Numbers\'s from IDS not to import.<br />E.g. 1,2,3</p>'
      );
    }

    public function xml_file_path_callback()
    {
        printf(
            '<input type="text" id="xml_file_path" name="trailer_import_xmlfile[xml_file_path]" value="%s" />',
            isset( $this->xml_options['xml_file_path'] ) ? esc_attr( $this->xml_options['xml_file_path']) : ''
        );
        printf(
          '<p class="description">The xml folder filepath relative to the WordPress root directory.<br />E.g. data/</p>'
      );
    }

    public function xml_file_prefix_callback()
    {
        printf(
            '<input type="text" id="xml_file_prefix" name="trailer_import_xmlfile[xml_file_prefix]" value="%s" />',
            isset( $this->xml_options['xml_file_prefix'] ) ? esc_attr( $this->xml_options['xml_file_prefix']) : ''
        );
        printf(
          '<p class="description">The xml file prefix is the filename up to the date code.<br />E.g. Web_Inventory_#124#_</p>'
      );
    }

    public function xml_file_dateformat_callback()
    {
        printf(
            '<input type="text" id="xml_file_dateformat" name="trailer_import_xmlfile[xml_file_dateformat]" value="%s" />',
            isset( $this->xml_options['xml_file_dateformat'] ) ? esc_attr( $this->xml_options['xml_file_dateformat']) : ''
        );
        printf(
          '<p class="description">The xml file dateformat is a PHP date format that follows the xml file prefix.<br />E.g. Y-m-d/</p>'
      );
    }

    public function inquire_form_id_callback()
    {
        printf(
            '<input type="text" id="inquire_form_id" name="trailer_import_display[inquire_form_id]" value="%s" />',
            isset( $this->display_options['inquire_form_id'] ) ? esc_attr( $this->display_options['inquire_form_id']) : ''
        );
        printf(
            '<p class="description">The Gravity Forms ID of the Inquire Form for Trailer Detail Page.<br />E.g. 1</p>'
        );
    }

    public function slider_id_callback()
    {
        printf(
            '<input type="text" id="slider_id" name="trailer_import_display[slider_id]" value="%s" />',
            isset( $this->display_options['slider_id'] ) ? esc_attr( $this->display_options['slider_id']) : ''
        );
        printf(
            '<p class="description">The Royal Slider ID of the Photo Slider for Trailer Detail Page.<br />E.g. 1</p>'
        );
    }

    public function rv_inquire_form_id_callback()
    {
        printf(
            '<input type="text" id="rv_inquire_form_id" name="rv_import_display[rv_inquire_form_id]" value="%s" />',
            isset( $this->rv_display_options['rv_inquire_form_id'] ) ? esc_attr( $this->rv_display_options['rv_inquire_form_id']) : ''
        );
        printf(
            '<p class="description">The Gravity Forms ID of the Inquire Form for RV Detail Page.<br />E.g. 1</p>'
        );
    }

    public function rv_slider_id_callback()
    {
        printf(
            '<input type="text" id="rv_slider_id" name="rv_import_display[rv_slider_id]" value="%s" />',
            isset( $this->rv_display_options['rv_slider_id'] ) ? esc_attr( $this->rv_display_options['rv_slider_id']) : ''
        );
        printf(
            '<p class="description">The Royal Slider ID of the Photo Slider for RV Detail Page.<br />E.g. 1</p>'
        );
    }

    public function rv_type_callback()
    {
        printf(
            '<input type="text" id="rv_type" name="rv_import_ignores[rv_type]" value="%s" />',
            isset( $this->rv_ignore_options['rv_type'] ) ? esc_attr( $this->rv_ignore_options['rv_type']) : ''
        );
        printf(
          '<p class="description">Comma seperated list of RV types from IDS not to import.<br />E.g. RENTAL UNIT</p>'
      );
    }

    public function rv_brand_callback()
    {
        printf(
            '<input type="text" id="rv_brand" name="rv_import_ignores[rv_brand]" value="%s" />',
            isset( $this->rv_ignore_options['rv_brand'] ) ? esc_attr( $this->rv_ignore_options['rv_brand']) : ''
        );
        printf(
          '<p class="description">Comma seperated list of RV brands from IDS not to import.<br />E.g. AEROLITE</p>'
      );
    }

    public function rv_designation_callback()
    {
        printf(
            '<input type="text" id="rv_designation" name="rv_import_ignores[rv_designation]" value="%s" />',
            isset( $this->rv_ignore_options['rv_designation'] ) ? esc_attr( $this->rv_ignore_options['rv_designation']) : ''
        );
        printf(
          '<p class="description">Comma seperated list of RV designations from IDS not to import.<br />E.g. RENTALS,NEW</p>'
      );
    }

    public function rv_stocknums_callback()
    {
        printf(
            '<input type="text" id="rv_stocknums" name="rv_import_ignores[rv_stocknums]" value="%s" />',
            isset( $this->rv_ignore_options['rv_stocknums'] ) ? esc_attr( $this->rv_ignore_options['rv_stocknums']) : ''
        );
        printf(
          '<p class="description">Comma seperated list of RV Stock Numbers\'s from IDS not to import.<br />E.g. 1,2,3</p>'
      );
    }

    public function rv_types_callback()
    {
        printf(
            '<input type="text" id="rv_types" name="rv_import[rv_types]" value="%s" />',
            isset( $this->rv_import_options['rv_types'] ) ? esc_attr( $this->rv_import_options['rv_types']) : ''
        );
        printf(
          '<p class="description">Comma seperated list of RV Type\'s from IDS to import.<br />E.g. MOTORHOME,TRAVEL TRAILER,FIFTH WHEEL,TOY HAULER</p>'
      );
    }
}

if( is_admin() )
    $trailer_settings_page = new TrailerSettingsPage();
