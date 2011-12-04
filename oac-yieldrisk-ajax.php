<?php
// Load WP necessities
$wp_root = explode('wp-content', __FILE__);
$wp_root = $wp_root[0];


if( file_exists( $wp_root.'wp-load.php' ) ) {
	require_once( $wp_root.'wp-load.php' );
}

// Are you a proper ajax call?
//if( ! isset( $_SERVER['HTTP_X_REQUESTED_WITH']) ) { die( "<img src=\"./kp.jpg\"><p>Nothing to see here!</p>" ); }
//if( $_SERVER['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest" ) {
	if( isset( $_REQUEST['route'] ) ) {
		switch( $_REQUEST['route'] ) {
      // Yes, this is hacky until we get more data.
	    case 'getPlantingDates':
	      $json = OACYieldRiskAjax::getPlantingDates();
	      echo $json;
	      break;
		  case 'getData':
			  $json = OACYieldRiskAjax::getData( $_REQUEST['variety'], $_REQUEST['loc'], $_REQUEST['phase'] );
			  echo json_encode( $json );
			  break;
		}
  }
//} else {
//	die( "<img src=\"./kp.jpg\"><p>Nothing to see here!</p>" );
//}

class OACYieldRiskAjax {
  public static function getPlantingDates() {
    global $wpdb;
    $colors = array('#006666', '#D34C1D', '#000066', '#990000', '#2E6D8B', '#5E3946', '#6600CC', '#A97D5D', '#A64E74', '#D706AD', '#868686');
    $return = '';
    $query = 'SELECT DISTINCT planting_date_long FROM yieldrisk_phenology_table;';
    $results = $wpdb->get_results( $wpdb->prepare( $query ), ARRAY_A );
    for( $i = 0; $i < count( $results ); $i++ ) {
      $return .= '<li><div class="div_input" style="background-color: '.$colors[$i].'"><input type="checkbox" name="planting_date" value="'.($i+1).'" '.($i == 0 ? 'checked' : '').'></div><div class="div_input_text">'.$results[$i]['planting_date_long'].'</div></li>';
    }
    return $return;
  }
  
  public static function getData( $variety, $location, $phase ) {
    global $wpdb;
    $data_query = 'SELECT * FROM yieldrisk_yield_data WHERE variety=%s AND location_id=%s AND climate_id=%d';
    $results = $wpdb->get_results( $wpdb->prepare( $data_query, array( $variety, $location, $phase ) ), ARRAY_A );
    $return = array('data' => array(), 'html' => '');
    foreach( $results as $row ) {
      $temp = array();
      switch( $row['Bin'] ) {
        case '1':
          $temp['category'] = __( 'Low Yield', 'oac_yieldtool' );
          break;
        case '2':
          $temp['category'] = __( 'Median Yield', 'oac_yieldtool' );       
          break;
        case '3':
          $temp['category'] = __( 'High Yield', 'oac_yieldtool' );
          break;
      }
      for( $i=1; $i < 10; $i++ ) {
        $temp['Day_'.$i] = $row['Day_'.$i];
      }
      $return['data'][] = $temp;
    }
    $phenology_query = 'SELECT planting_date_long, flowering_range, maturity_range FROM yieldrisk_phenology_table WHERE variety=%s AND location_id=%s AND climate_id=%d';
    $results = $wpdb->get_results( $wpdb->prepare( $phenology_query, array( $variety, $location, $phase ) ), ARRAY_A );
    foreach( $results as $row ) {
      $return['html'] .= '<tr><td>'.$row['planting_date_long'].'</td><td>'.$row['flowering_range'].'</td><td>'.$row['maturity_range'].'</td></tr>'."\n";
    }
    return $return;
  }
}