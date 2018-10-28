<?php
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
            wp_enqueue_script( 'jQuery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js', '3',false );
        wp_enqueue_style( 'rb-form-fields-css', plugin_dir_url(__FILE__) . 'style.css' );
        wp_enqueue_script( 'rb-controls-values-manager', plugin_dir_url(__FILE__) . 'js/rb-controls.js', array('jquery'), true );
        //Collapsibles
        wp_enqueue_style( 'rb-collapsible', plugin_dir_url(__FILE__) . 'css/rb-collapsible.css' );
        wp_enqueue_script( 'rb-collapsible', plugin_dir_url(__FILE__) . 'js/rb-collapsible.js', array('jquery'), true );
        wp_enqueue_script( 'rb-media-control', plugin_dir_url(__FILE__) . 'js/rb-media-control.js', array('jquery'), true );
        //Sortabe jQuery UI
        wp_enqueue_script( 'jquery-ui-sortable', plugin_dir_url(__FILE__) . 'js/libs/jquery-ui-1.12.1.custom', array('jquery'), true );

        //Gallery control
        wp_enqueue_style( 'rb-gallery-control', plugin_dir_url(__FILE__) . 'css/rb-gallery-control.css' );
        wp_enqueue_script( 'rb-gallery-control', plugin_dir_url(__FILE__) . 'js/rb-gallery-control.js', array('jquery'), true );
    }
    add_action( 'admin_enqueue_scripts', 'rb_form_fields_scripts' );

    //CUSTOMIZER SCRIPTS
    function rb_customizer_scripts($wp_customize) {
        wp_enqueue_script( 'jQuery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js', true );
        wp_enqueue_script( 'rb-customizer-values-manager', plugin_dir_url(__FILE__) . 'js/customizerControlsValuesManager.js', array('jQuery'), true );
    }
    add_action( 'customize_controls_enqueue_scripts', 'rb_customizer_scripts' );

    // =========================================================================
    //
    // =========================================================================
    require_once plugin_dir_path(__FILE__) . '/RB_Form_Field_Controller.php';
    require_once plugin_dir_path(__FILE__) . '/RB_Form_Field_Controls.php';
    require_once plugin_dir_path(__FILE__) . '/RB_Metabox.php';
    require_once plugin_dir_path(__FILE__) . '/RB_Taxonomy_Meta.php';

    function require_rb_customizer_field_control(){
        if( !class_exists('RB_Customizer_Field_Control') )
            require plugin_dir_path(__FILE__) . '/RB_Customizer_Control.php';
    }

    function rb_customizer_field_register($wp_customize) {
        require_rb_customizer_field_control();

    }
    add_action( 'customize_register', 'rb_customizer_field_register', 1 );

}
