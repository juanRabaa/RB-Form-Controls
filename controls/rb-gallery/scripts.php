<?php

//Gallery control
wp_enqueue_style( 'rb-gallery-control', plugin_dir_url(__FILE__) . 'css/rb-gallery-control.css' );
wp_enqueue_script( 'rb-gallery-control', plugin_dir_url(__FILE__) . 'js/rb-gallery-control.js', array('jquery'), true );
