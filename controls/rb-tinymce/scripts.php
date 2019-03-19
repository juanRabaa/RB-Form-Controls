<?php

//Tinymce Editor Control
wp_enqueue_script( 'rb-tinymce-editor-control', plugin_dir_url(__FILE__) . 'js/rb-tinymce-editor-control.js', array('jquery'), true );
wp_enqueue_style( 'rb-tinymce-editor-control', plugin_dir_url(__FILE__) . 'css/rb-tinymce-editor-control.css' );
