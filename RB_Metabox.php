<?php
class RB_Metabox extends RB_Form_Field_Controller{
    public $meta_id;
    public $render_nonce = true;
    public $settings = array(
        'admin_page'	=> 'post',
        'context'		=> 'advanced',
        'priority'		=> 'default',
        'classes'		=> '',
    );

    public function __construct($id, $meta_id = '', $options = array()) {
        if( is_array($meta_id) )
            $options = $meta_id;

        parent::__construct($id, $value, $options);
        $this->meta_id = $this->settings['meta_id'] = !is_array($meta_id) ? $meta_id : $id;

        $this->register_metabox();
    }

    public function register_metabox(){
        add_action( 'load-post.php', array($this, 'metabox_setup') );
        add_action( 'load-post-new.php', array($this, 'metabox_setup') );
    }

    public function metabox_setup(){
        /* Add meta boxes on the 'add_meta_boxes' hook. */
        add_action( 'add_meta_boxes', array($this, 'add_metabox') );
        /* Save post meta on the 'save_post' hook. */
        add_action( 'save_post', array($this, 'save_metabox'), 10, 2 );
    }

    /* Creates the metabox to be displayed on the post editor screen. */
    public function add_metabox(){
        extract( $this->settings );
        add_meta_box( $this->id, $title, array($this, 'render_metabox'), $admin_page, $context, $priority);
        $this->add_metabox_classes();
    }

    public function render_metabox($post){
        $this->value = get_post_meta( $post->ID, $this->meta_id, true );
        $this->render();
    }

    public function save_metabox( $post_id, $post ) {

        // /* Verify the nonce before proceeding. */
        // if ( !isset( $_POST[$this->id . '_nonce'] ) || !wp_verify_nonce( $_POST[$this->id . '_nonce'], basename( __FILE__ ) ) )
        //     return $post_id;

        //JSONS Values in the $_POST get scaped quotes. That makes json_decode
        //not recognize the content as jsons. THE PROBLEM is that it also eliminates
        //th the '\' in the values of the JSON.
        $_POST = array_map( 'stripslashes_deep', $_POST );

        $new_meta_value = null;

        if( $this->is_repeater() ){
            $new_meta_value = array();
            if( isset($_POST[$this->id]) ){
                if( $this->is_group() )
                    $new_meta_value = json_decode($_POST[$this->id], true);
                else
                    $new_meta_value = json_decode($_POST[$this->id], true);
                echo "New value: "; print_r($_POST[$this->id]);
                echo "<br>";
            }
        }
        //If a group of inputs controls were used
        else if( $this->is_group() ){
            print_r( "Group json: " . $_POST[$this->id]); echo "<br>";
            $new_meta_value = array();
            if( isset($_POST[$this->id]) )
                $new_meta_value = json_decode($_POST[$this->id], true);
        }
        //If a single input control was used
        else{
            /* Get the posted data */
            $new_meta_value = ( isset( $_POST[$this->id] ) ?  $_POST[$this->id] : '' );
        }
        /* Get the meta key. */
        $meta_key = $this->meta_id;

        /* Get the meta value of the custom field key. */
        $meta_value = get_post_meta( $post_id, $meta_key, true );

        if( $this->id == 'dfgdfg' ){
            //print_r($_POST[$this->id]);
            echo "<br>";
            echo $meta_key;
            echo "<br>";
            print_r($new_meta_value);
            echo "<br>";echo "<br>";
            err();
        }

        /* If a new meta value was added and there was no previous value, add it. */
        if ( $new_meta_value && !$meta_value )
            add_post_meta( $post_id, $meta_key, $new_meta_value, true );

        /* If the new meta value does not match the old value, update it. */
        elseif ( $new_meta_value && $new_meta_value != $meta_value )
            update_post_meta( $post_id, $meta_key, $new_meta_value );

        /* If there is no new meta value but an old value exists, delete it. */
        elseif ( (!$new_meta_value || empty($new_meta_value)) && $meta_value )
            delete_post_meta( $post_id, $meta_key, $meta_value );

    }

    public function add_metabox_classes(){
        /**
         * {post_type_name}     The name of the post type
         * {metabox_id}         The ID attribute of the metabox
         *
         * @param   array   $classes    The current classes on the metabox
         * @return  array               The modified classes on the metabox
        */
        if( is_array($this->settings['classes']) ){
            add_filter( "postbox_classes_{$this->settings['admin_page']}_{$this->id}", function( $classes = array() ){
                foreach ( $this->settings['classes'] as $class ) {
                    if ( ! in_array( $class, $classes ) ) {
                        $classes[] = sanitize_html_class( $class );
                    }
                }
                return $classes;
            });
        }
    }
}
