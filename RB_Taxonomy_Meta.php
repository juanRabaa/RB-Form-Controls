<?php
//EJ:
// new RB_Taxonomy_Form_Field('jijijiererererere345345345', array(
// 	'title'			=> __('Información del producto', 'test-test'),
// 	'column'		=> array('rere', 'Test'),
// 	'controls'		=> array(
// 		'weight'		=> array(
// 			'label'			=> 'Cantidad de contenido',
// 			'input_type'	=> 'number',
// 		),
// 		'size'		=> array(
// 			'label'			=> 'Tamaño',
// 			'type'			=> 'RB_Gallery_Control',
// 			'input_type'	=> 'number',
// 		),
// 	)
// ));
class RB_Taxonomy_Form_Field extends RB_Form_Field_Controller{
    public $terms;
    public $render_nonce = true;
    public $add_form = false;
    public $settings = array(
        'admin_page'	=> 'post',
        'context'		=> 'advanced',
        'priority'		=> 'default',
        'classes'		=> '',
        'terms'         => array('post_tag'),
    );

    public function __construct($id, $meta_id = '', $options = array()) {
        if( is_array($meta_id) )
            $options = $meta_id;

        parent::__construct($id, $value, $options);
        $this->meta_id = $this->settings['meta_id'] = !is_array($meta_id) ? $meta_id : $id;
        $this->terms = $this->settings['terms'];

        $this->register_form_field();
    }

    // =========================================================================
    // REGISTER
    // =========================================================================
    public function register_form_field(){
        $this->add_form_actions();
        $this->add_table_column_actions();
    }

    // =========================================================================
    // ADD AND EDIT FORM ACTIONS
    // =========================================================================
    protected function add_form_actions(){
        foreach( $this->terms as $term_slug){
            add_action( $term_slug . "_edit_form_fields", array($this, 'term_edit_form_fields_row') );
            if( $this->add_form )
                add_action( $term_slug . "_add_form_fields", array($this, 'term_add_form_fields_container') );
        }
        add_action('edited_term', array($this, 'save_extra_term_fields'), 10, 2);
        add_action("created_term", array($this, 'save_extra_term_fields') );
    }

    //Displays the table row for the edit form
    public function term_edit_form_fields_row($term_obj){
        $this->update_value($term_obj);
        ?>
        <tr class="form-field rb-tax-form-field">
            <th scope="row" valign="top"><label for="<?php echo $this->id; ?>"><?php _e( $this->title ); ?></label></th>
            <td>
                <?php $this->term_edit_form_fields_container($term_obj); ?>
            </td>
        </tr>
        <?php
    }

    //Renders the control
    public function term_edit_form_fields_container($term_obj){
        $this->update_value($term_obj);
        ?>
        <div class="rb-tax-field">
            <?php parent::render(); ?>
        </div>
        <?php
    }

    //Displays the control on the add term form
    public function term_add_form_fields_container($term_obj){
        $this->update_value( $term_obj );
        ?>
        <div class="form-field add-form-field <?php echo $this->term_add_container_class; ?>">
            <label for="tag-description"><?php echo $this->title; ?></label>
            <?php $this->term_add_form_fields($term_obj); ?>
        </div>
        <?php
    }

    // =============================================================================
    // COLUMN FILTERS
    // =============================================================================

    // GETTERS
    // =========================================================================
    protected function get_column_name(){
        if( $this->settings['column'] && is_array( $this->settings['column'] ) && $this->settings['column'][1] )
            return $this->settings['column'][1];
        return false;
    }

    protected function get_column_id(){
        if( $this->settings['column'] && is_array( $this->settings['column'] ) && $this->settings['column'][0] )
            return $this->settings['column'][0];
        return false;
    }

    // FILTERS
    // =========================================================================
    protected function add_table_column_actions(){
        if( $this->get_column_id() ){
            foreach( $this->terms as $term_slug){
                add_filter('manage_edit-'.$term_slug.'_columns', array($this, 'manage_taxonomy_columns') );
                add_filter('manage_'.$term_slug.'_custom_column', array($this, 'manage_taxonomy_columns_fields_container') , 10, 3);
            }
        }
    }

    /*Adds the column of this control to the taxonomies list*/
    public function manage_taxonomy_columns($columns){
        return array_merge( $columns, array( $this->get_column_id() =>  $this->get_column_name() ) );
    }

    /*Wraps the control column content*/
    public function manage_taxonomy_columns_fields_container($deprecated, $column_id, $term_id){
        if ( $column_id == $this->get_column_id() ):
            $this->update_value($term_obj);
        ?>
            <div class="rb-taxonomy-column-content <?php echo $this->column_class; ?>">
            <?php
                if ($column_id == $this->get_column_id() ){
                    $this->manage_taxonomy_columns_fields($deprecated, $column_id, $term_id);
                }
            ?>
            </div>
        <?php
        endif;
    }

    // =============================================================================
    // SAVE
    // =============================================================================
    public function save_extra_term_fields( $term_id ) {

        /* Verify the nonce before proceeding. */
        // if ( !isset( $_POST[$this->id . '_nonce'] ) || !wp_verify_nonce( $_POST[$this->id . '_nonce'], basename( __FILE__ ) ) )
        //     return $term_id;

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
            $new_meta_value = array();
            if( isset($_POST[$this->id]) )
                $new_meta_value = json_decode($_POST[$this->id], true);
        }
        //If a single input control was used
        else{
            /* Get the posted data */
            $new_meta_value = ( isset( $_POST[$this->id] ) ?  $_POST[$this->id] : ’ );
        }
        /* Get the meta key. */
        $meta_key = $this->meta_id;

        /* Get the meta value of the custom field key. */
        $meta_value = get_term_meta( $term_id, $meta_key, true );

        if( $this->id == 'testet3434343234234' ){
            print_r($_POST[$this->id]);
            echo "<br>";
            echo $meta_key;
            echo "<br>";
            print_r($new_meta_value);
            //err();
        }

        /* If a new meta value was added and there was no previous value, add it. */
        if ( $new_meta_value && ’ == $meta_value )
            add_term_meta( $term_id, $meta_key, $new_meta_value, true );

        /* If the new meta value does not match the old value, update it. */
        elseif ( $new_meta_value && $new_meta_value != $meta_value )
            update_term_meta( $term_id, $meta_key, $new_meta_value );

        /* If there is no new meta value but an old value exists, delete it. */
        elseif ( (’ == $new_meta_value || empty($new_meta_value)) && $meta_value )
            delete_term_meta( $term_id, $meta_key, $meta_value );
    }
    // =========================================================================
    // METHODS
    // =========================================================================
    protected function update_value($term){
        $term_id;
        if(is_object($term))
            $term_id = $term->term_id;
        else
            $term_id = $term;
        $this->value = get_term_meta($term_id, $this->id, true);
    }

    // =========================================================================
    // EXTENDABLE METHODS
    // =========================================================================
    public function term_add_form_fields($term_obj){
        $this->term_edit_form_fields_container($term_obj);
    }
    public function manage_taxonomy_columns_fields($deprecated, $column_id, $term_id){
        echo $this->value;
    }

}
