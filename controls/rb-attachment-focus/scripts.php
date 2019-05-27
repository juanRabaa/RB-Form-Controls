<?php

//Double list control
wp_enqueue_style( 'rb-attachment-focus-control', plugin_dir_url(__FILE__) . 'css/rb-attachment-focus-control.css' );
wp_enqueue_script( 'rb-attachment-focus-control', plugin_dir_url(__FILE__) . 'js/rb-attachment-focus-control.js', array('jquery'), true );
