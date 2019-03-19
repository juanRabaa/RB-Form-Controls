<?php

define('RB_FORMS_FIELDS_MASTER_DIR', plugin_dir_path(__FILE__));

function rb_get_controls_path(){
    return glob(RB_FORMS_FIELDS_MASTER_DIR . 'controls/*', GLOB_ONLYDIR);
}

function rb_require_all_controls(){
    $directories = rb_get_controls_path();
    foreach( $directories as $directory ){
        $control_path = "$directory/control.php";
        if( file_exists( $control_path ) )
            require_once $control_path;
    }
}

function rb_require_all_controls_scripts(){
    $directories = rb_get_controls_path();
    foreach( $directories as $directory ){
        $scripts_path = "$directory/scripts.php";
        if( file_exists( $scripts_path ) )
            require_once $scripts_path;
    }
}

if( is_admin() ){
    add_action( 'current_screen', function(){
        $screen = get_current_screen();
        if( $screen && $screen->taxonomy )
            wp_enqueue_media();
    } );

    //ADMIN SCRIPTS
    function rb_form_fields_scripts() {
        $screen = get_current_screen();
        if( $screen && $screen->taxonomy )
            wp_enqueue_script( 'jQuery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js', false );

        // =====================================================================
        // COMMONS
        // =====================================================================
        
        //Collapsibles
        wp_enqueue_style( 'rb-collapsible', plugin_dir_url(__FILE__) . 'css/rb-collapsible.css' );
        wp_enqueue_script( 'rb-collapsible', plugin_dir_url(__FILE__) . 'js/rb-collapsible.js', array('jquery'), true );
        //Sortabe jQuery UI
        wp_enqueue_script( 'jquery-ui-sortable', plugin_dir_url(__FILE__) . 'js/libs/jquery-ui-1.12.1.custom', array('jquery'), true );
        //Main
        wp_enqueue_style( 'rb-form-fields-css', plugin_dir_url(__FILE__) . 'style.css' );
        wp_enqueue_script( 'rb-controls-values-manager', plugin_dir_url(__FILE__) . 'js/rb-controls.js', array('jquery'), true );
        //Font Aweasome
        wp_enqueue_style( "fontawesome", 'https://use.fontawesome.com/releases/v5.4.1/css/all.css', true );

        // =====================================================================
        // NATIVE CONTROLS SCRIPTS
        // =====================================================================
        rb_require_all_controls_scripts();
    }
    add_action( 'admin_enqueue_scripts', 'rb_form_fields_scripts' );


    // =========================================================================
    //
    // =========================================================================
    require_once plugin_dir_path(__FILE__) . '/RB_Form_Field_Controller.php';
    require_once plugin_dir_path(__FILE__) . '/RB_Form_Field_Controls.php';
    rb_require_all_controls();
    require_once plugin_dir_path(__FILE__) . '/RB_Metabox.php';
    require_once plugin_dir_path(__FILE__) . '/RB_Taxonomy_Meta.php';

}

// =============================================================================
// CUSTOMIZER
// =============================================================================
//CUSTOMIZER SCRIPTS
function rb_customizer_scripts($wp_customize) {
    //wp_enqueue_script( 'jQuery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js', true );
    //wp_enqueue_script( 'rb-customizer-values-manager', plugin_dir_url(__FILE__) . 'js/customizerControlsValuesManager.js', array(), true );
}
add_action( 'customize_controls_enqueue_scripts', 'rb_customizer_scripts' );

function rb_customizer_field_register($wp_customize) {
    require_once plugin_dir_path(__FILE__) . '/RB_Form_Field_Controller.php';
    require plugin_dir_path(__FILE__) . '/RB_Customizer_Control.php';
}
add_action( 'customize_register', 'rb_customizer_field_register' );
