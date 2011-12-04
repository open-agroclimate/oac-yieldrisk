<?php
/*
 * Plugin Name: OAC: Yield Risk Tool
 * Version: 1.0
 * Plugin URI: http://open.agroclimate.org/downloads/
 * Description: TODO
 * Author: The Open AgroClimate Project
 * Author URI: http://open.agroclimate.org/
 * License: BSD Modified
 */

class OACYieldRiskAdmin {
    public function oac_yieldrisk_admin_init() {
        $plugin_dir = basename( dirname( __FILE__ ) );
        load_plugin_textdomain( 'oac_yieldrisk', null, $plugin_dir . '/languages' );
        if( !current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permission to access this page.', 'oac_yieldrisk' ) );
        }
        // Anything else needed to be run (like POST or GET redirection)
        // wp_scoper_admin_action_handler();
    }

    public function oac_yieldrisk_admin_menu() {
        add_submenu_page( 'oac_menu', __( 'Yield Risk Tool', 'oac_yieldrisk' ), __( 'Yield Risk Tool', 'oac_yieldrisk' ), 'manage_options', 'oac_yieldrisk_handle', array( 'OACYieldRiskAdmin', 'oac_yieldrisk_admin_page' ) );
    }

    public function oac_yieldrisk_admin_page() {
    ?>
        <div class="wrap">
        <?php screen_icon( 'tools' ); ?>
        <h2><?php _e( 'Yield Risk Tool Settings', 'oac_yieldrisk' ); ?></h2>
        <p>Information</p>
        <p>More information here</p>
        </div>
    <?php
    }


    public function oac_yieldrisk_install_harness() {
        OACBase::init();
        //wp_scoper_admin_setup_scopes( 'location', __FILE__ );
    }

    public function oac_nationalstats_uninstall_harness() {
        OACBase::init();
        //wp_scoper_admin_cleanup_scopes( __FILE__ );
    }
} // class OACYieldRiskAdmin

class OACYieldRisk {
    private static $location_scope = null;
    private static $crop_scope = null;
    private static $plugin_url = '';

    public static function initialize() {
        OACBase::init();
        $plugin_dir = basename( dirname( __FILE__ ) );
        self::$plugin_url = plugins_url( '', __FILE__ );
        load_plugin_textdomain( 'oac_yieldrisk', null, $plugin_dir . '/languages' );
        $lfilter = null;
        $lfilters = get_option( 'oac_scope_filters', null );
        if( $lfilters !== null ) $lfilter = array_key_exists( 'location_yieldrisk', $lfilters ) ? $lfilters['location_yieldrisk'] : null;
        self::$location_scope = new WPScoper( 'location', $lfilter );
        self::$crop_scope = new WPScoper( 'cropvariety' );
    }


    public static function ui_panel()    {
      $output  = '<div id="yieldrisk-ui-container" class="oac-ui-container">';
      $output .= '<div id="oac-user-input-panel" class="oac-user-input">';
      $output .= '<input type="hidden" name="ajax-handler" id="ajax-handler" value="'.self::$plugin_url.'/oac-yieldrisk-ajax.php">';
      $output .= '<img src="'.self::$plugin_url.'/css/Soya.png"  id="cropImg" class="cropImg">';
      $output .= '<div id="crop-container"><label for="crop">'.__( 'Select Crop', 'oac_yieldrisk' ).'</label>';
      $output .= self::$crop_scope->generateDDL( '' );
      $output .= '  <label for="variety">'.__( 'Select Variety', 'oac_yieldrisk' ).'</label>';
      $output .= self::$crop_scope->generateDDL( '0' );
      $output .= '</div>';
      $output .= '<div id="location-container"><label for="location">'.__( 'Select Location', 'oac_yieldrisk' ).'</label>';
      $output .= self::$location_scope->generateNestedDDL( '' );
      $output .= '</div>';
      $output .= '<div id="soil-container"><label for="soil">'.__( 'Select Soil', 'oac_yieldrisk' ).'</label>';
      $output .= '  <select id="soil" name="soil" class="oac-input oac-select">';
      $output .= '    <option value="Arcilloso fino">Arcilloso fino</option>';
      $output .= '</select></div>';
      $output .= '<div id="irrigation-container"><label for="irrigation">'.__( 'Irrigation', 'oac_yieldrisk' ).'</label>';
      $output .= '  <input type="radio" name="irrgation" class="oac-input oac-radio" value="1" disabled><span class="small-text">'.__( 'Irrigated', 'oac_yieldrisk' ).'</span><input type="radio" class="oac-input oac-radio" name="irrigation" value="0" checked><span class="small-text">'.__( 'Rainfed', 'oac_yieldrisk' ).'</span>';
      $output .= '</div>';
      $output .= '<div id="nitrogen-container"><label for="nitrogen">'.__( 'Select Nitrogen', 'oac_yieldrisk' ).'</label>';
      $output .= '  <select id="nitrogen" name="nitrogen" class="oac-input oac-select">';
      $output .= '    <option value="0 kg/ha">0 kg/ha</option>';
      $output .= '</select></div>';
      $output .= OACBASE::display_enso_selector();
      //$output .= '<div><input type="button" id="compare-phases" value="'.__( 'Compare Climate Phases', 'oac_yieldrisk' ).'"></div>';
      $output .= '</div>';
      $output .= '<div id="oac-output-panel" class="oac-output">';
      $output .= '<div class="label_top">'.__( 'Current Climate Phase', 'oac_yieldrisk' ).': <strong id="current-display-indicator"></strong></div>';
      $output .= '<div id="chart-container"><div id="graph"></div><div id="planting-dates-container">';
      $output .= '<label for="planting-dates">'.__( 'Planting Dates', 'oac_yieldrisk' ).'</label>';
      $output .= '<ul id="planting-dates-list"></ul></div></div>';
      $output .= '<div id="phenology-container">';
      $output .= '<label for="phenology-table">'.__( 'Phenology Table', 'oac_yieldrisk' ).'</label>';
      $output .= '<table id="phenology-table"><thead>';
      $output .= '<tr><th>'.__( 'Planting Date', 'oac_yieldrisk' ).'</th>';
      $output .= '<th>'.__( 'Flowering Period', 'oac_yieldrisk' ).'</th>';
      $output .= '<th>'.__( 'Maturity Period', 'oac_yieldrisk' ).'</th>';
      $output .= '</thead><tbody id="phenology-table-data"></tbody></table>';
      $output .= '</div></div>';
      return $output;
    }
    
    public static function output() {
        $output = self::ui_panel();
        return $output;
    }

    public static function hijack_header() {
        global $post;
        global $is_IE;
        $regex = get_shortcode_regex();
        preg_match('/'.$regex.'/s', $post->post_content, $matches);
        if ((isset( $matches[2])) && ($matches[2] == 'oac_yieldrisk')) {
            wp_enqueue_style( 'oac-yieldrisk', plugins_url( 'css/oac-yieldrisk.css', __FILE__ ), array( 'oacbase' ) );
            wp_register_script( 'oac-yieldrisk', plugins_url( 'js/oac-yieldrisk.js', __FILE__ ),
                array( 'yui', 'oac-base' /* other dependencies here */ ),
                false, true
            );
            wp_enqueue_script( 'oac-yieldrisk' );
            add_action( 'wp_head', array( 'OACBase', 'ie_conditionals' ), 3 );
        }
    }
}

// WordPress Hooks and Actions
register_activation_hook( __FILE__, array( 'OACYieldRiskAdmin', 'oac_yieldrisk_install_harness' ) );
register_deactivation_hook( __FILE__, array( 'OACYieldRiskAdmin', 'oac_yieldrisk_uninstall_harness' ) );
if( is_admin() ) {
    add_action( 'admin_menu', array( 'OACYieldRiskAdmin', 'oac_yieldrisk_admin_menu' ) );
    add_action( 'admin_init', array( 'OACYieldRiskAdmin', 'oac_yieldrisk_admin_init' ) );
} else {
    // Add front-end specific actions/hooks here
    add_action( 'init', array( 'OACYieldRisk', 'initialize' ) );
    add_action( 'template_redirect', array( 'OACYieldRisk', 'hijack_header' ) );
    add_shortcode('oac_yieldrisk', array( 'OACYieldRisk', 'output' ) );
}
// Add all non-specific actions/hooks here
