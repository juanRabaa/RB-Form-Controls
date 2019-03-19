<?php

//Double list control
wp_enqueue_style( 'rb-text-list-control', plugin_dir_url(__FILE__) . 'css/rb-doublelist-control.css' );
wp_enqueue_script( 'rb-double-list-control', plugin_dir_url(__FILE__) . 'js/rb-doublelist-control.js', array('jquery'), true );
