<?php

class RB_Form_Single_Field{
    public $id;
    public $settings = array();
    public $type = 'RB_Input_Control';
    public $value = '';
    public $control_settings;


    public function __construct($id, $value, $control_settings, $type = 'RB_Input_Control', $settings = array()) {
        $this->id = $id;
        $this->value = $value;
        $this->control_settings = $control_settings;
        $this->control_settings['id'] = $this->id;
        $this->type = $type;

        $this->settings = wp_parse_args($this->settings, $settings);

    }

    public function render(){
        if( class_exists($this->type) && method_exists($this->type, 'render_content') ){
            ?>
            <div class="rb-form-control-single-field rb-form-control">
                <div class="rb-collapsible-body control-content">
                <?php
                $this->print_action_controls();
                $renderer = new $this->type( $this->value, $this->control_settings);
                $renderer->print_control();
                ?>
                </div>
            </div>
            <?php
        }
    }

    //Retruns the value of one of the repeater settings
    public function get_setting( $name ){
        $setting = '';
        if( isset($this->settings[$name]) )
            $setting = $this->settings[$name];
        return $setting;
    }


    public function print_action_controls(){
        $action_controls = $this->get_setting('action_controls');
        if( is_array($action_controls) && !empty($action_controls) ):
        ?>
            <div class="action-controls">
                <?php if( in_array('delete_button', $action_controls) ): ?>
                <div class="delete-button">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <?php endif; ?>
            </div>
        <?php
        endif;
    }
}


class RB_Form_Group_Field{
    public $id;
    public $settings = array();
    public $value = array();
    public $controls;
    public $collapsible = false;

    /**
    * @param array $value
    *   Controls values.
    *   array( $control_id => $control_value, ... )
    * @param array $controls
    *   Controls information. One value for each control.
    *   array( $control_id => $control_settings, ... )
    */
    public function __construct($id, $value, $controls, $settings = array()) {
        $this->id = $id;
        $this->value = $value;
        $this->controls = $controls;
        $this->settings = wp_parse_args($this->settings, $settings);
        $this->collapsible = $settings['collapsible'] ? $settings['collapsible'] : false;
    }

    public function render(){
        $collapsible_class = $this->collapsible ? 'rb-collapsible' : '';
        $value_is_set = is_array($this->value) && !empty($this->value);

        ?>
        <div data-id="<?php echo $this->id; ?>" class="rb-form-control-field-group rb-form-control <?php echo $collapsible_class; ?>">
            <?php $this->print_group_value_input(); ?>
            <?php
                if( $this->collapsible )
                    $this->print_collapsible_header();
            ?>
            <div class="rb-collapsible-body control-content">
                <?php
                    if( !$this->collapsible && is_array($action_controls) )
                        $this->print_action_controls( $action_controls );
                ?>
                <div class="controls">
                <?php
                foreach($this->controls as $control_ID => $control){
                    $control_settings = $control;
                    $control_settings['id'] = $this->get_input_id($control_ID);
                    $control_value = $value_is_set ? $this->value[$control_ID] : '';
                    $control_type = $control['type'] ? $control['type'] : 'RB_Input_Control';

                    if( $control_settings['repeater'] ){
                        $repeater_settings = is_array($control_settings['repeater']) ? $control_settings['repeater'] : array();
                        $field_controller = new RB_Form_Repeater_Field($control_settings['id'], $control_value, array($control), $items_settings = array(
                            'collapsible'   => true,
                        ), $repeater_settings);
                    }
                    else{
                        $field_controller = new RB_Form_Single_Field($control_settings['id'], $control_value, $control_settings, $control_type);
                    }

                    $class = $this->get_setting('field_classes') . ' ' . $control['field_class'];
                    ?><div class="group-control-single <?php echo $class; ?>" data-id="<?php echo $control_ID; ?>"><?php
                        $field_controller->render();
                    ?></div><?php
                }
                ?>
                </div>
            </div>
        </div>
        <?php
    }

    public function get_input_id($control_id){
        return $this->id . '-' . $control_id;
    }

    public function print_group_value_input(){
        ?>
        <input type="hidden" rb-control-group-value name="<?php echo $this->id; ?>" value="<?php echo esc_attr(json_encode($this->value)); ?>"></input>
        <?php
    }

    public function get_setting( $name ){
        $setting = '';
        if( isset($this->settings[$name]) )
            $setting = $this->settings[$name];
        return $setting;
    }

    // =============================================================================
    //
    // =============================================================================
    public function print_action_controls(){
        $action_controls = $this->get_setting('action_controls');
        if( is_array($action_controls) && !empty($action_controls) ):
        ?>
            <div class="action-controls">
                <?php if( in_array('delete_button', $action_controls) ): ?>
                <div class="delete-button">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <?php endif; ?>
            </div>
        <?php
        endif;
    }
    // =========================================================================
    // COLLAPSIBLE
    // =========================================================================
    public function print_collapsible_header( $options = array() ){
        $defaults = array(
            'title' => $this->settings['title'] ? $this->settings['title'] : 'Item',
            'link'  => '',
        );
        $settings = $defaults;
        if( is_array($this->collapsible) )
            $settings = array_merge($settings, $this->collapsible);

        ?>
        <div class="rb-collapsible-header container">
            <h1 data-title="<?php echo $settings['title']; ?>" class="title"><?php echo $settings['title']; ?></h1>
            <?php $this->print_action_controls(); ?>
        </div>
        <?php
    }
}

class RB_Form_Repeater_Field{
    public $id;
    public $settings = array();
    public $value = array();
    public $controls = array();
    public $items_settings = array();

    public function __construct($id, $value, $controls, $items_settings = array(), $settings = array()) {
        $this->id = $id;
        $this->value = $value;
        $this->controls = $controls;
        $this->items_settings = $items_settings;
        $this->settings = wp_parse_args($this->settings, $settings);
    }

    public function render(){
        ?>
        <div class="rb-form-control-repeater" data-id="<?php echo $this->id; ?>" data-type="<?php echo $this->get_repeater_type(); ?>" <?php echo $this->get_dinamic_title_attr(); ?>
        <?php echo $this->get_base_title_attr(); ?>>
            <div class="empty-control">
                <?php $this->print_item('(__COUNTER_PLACEHOLDER)', ''); ?>
            </div>
            <!-- REPEATER VALUE -->
            <input type="hidden" rb-control-repeater-value name="<?php echo $this->id; ?>" value="<?php echo esc_attr(json_encode($this->value)); ?>"></input>
            <!-- NONCE -->
            <?php if($this->render_nonce) wp_nonce_field( basename( __FILE__ ), $this->id . '_nonce' ); ?>
            <!-- REPEATER CONTROLS -->
            <div class="controls" <?php echo $this->get_accordion_attr(); ?>>
            <?php
            $this->item_index = 1;
            if(is_array($this->value)){
                foreach($this->value as $value){
                    $this->print_item($this->item_index, $value);
                    $this->item_index++;
                }
            }
            //There is not a value to work on
            else{
                //prints an empty first item
                $this->print_item('1', '');
            }
            ?>
            </div>
            <!-- End controls -->
            <?php $this->print_empty_message(); ?>
            <!-- End empty message -->
            <div class="repeater-add-button">
                <i class="add-button fas fa-plus"></i>
            </div>
        </div>
        <?php
    }

    public function get_item_as_string($item_index, $item_value){
        ob_start();
        $this->print_item($item_index, $item_value);
        return ob_get_clean();
    }

    public function print_item($item_index, $item_value){
        $renderer = null;
        $item_id = $this->id  . '__' . $item_index;

        if( $this->is_group() ){
            $renderer = new RB_Form_Group_Field($item_id, $item_value, $this->controls, array(
                'title'             => str_replace('($n)',$item_index,$this->get_setting('item_title')),
                'collapsible'       => $this->get_items_setting('collapsible'),
                'action_controls'   => array('delete_button'),
            ));
        }
        else{
            $control_settings = reset($this->controls);//First item in the controls array
            $control_type = $control_settings['type'] ? $control_settings['type'] : 'RB_Input_Control';
            $renderer = new RB_Form_Single_Field($item_id, $item_value, $control_settings, $control_type, array(
                //'title'             => str_replace('($n)',$this->item_index,$this->get_setting('item_title')),
                'action_controls'   => array('delete_button'),
            ));
        }

        if($renderer)
            $renderer->render();
    }
    // =========================================================================
    // ATTRIBUTES
    // =========================================================================
    public function get_accordion_attr(){
        return $this->get_setting('accordion') ? 'data-rb-accordion' : '';
    }

    public function get_dinamic_title_attr(){
        return $this->get_setting('title_link') ? 'data-title-link="'. $this->settings['title_link'] . '"' : '';
    }

    public function get_base_title_attr(){
        return $this->get_setting('item_title') ? 'data-base-title="'.$this->settings['item_title'].'"' : '';
    }

    // =========================================================================
    //
    // =========================================================================
    public function print_empty_message(){
        ?>
        <div class="rb-repeater-empty-message">
            <?php
            $message = $this->get_setting('empty_message');
            $message = $message ? $message : 'Click on the button below to start adding content';
            //If the message is a function
            if( is_callable($message) )
                $message($message);
            //If the message is a string
            else if ( is_string($message) ):
            ?>
                <p><?php echo $message; ?></p>
            <?php
            endif;
            ?>
        </div>
        <?php
    }
    // =========================================================================
    // INFORMATION GETTERS
    // =========================================================================
    function get_repeater_type(){
        return $this->is_group() ? 'group' : 'single';
    }

    //If the repeater is items are groups on inputs
    public function is_group(){
        return is_array($this->controls) && count($this->controls) > 1;
    }

    //Returns the value of one of the items settings
    public function get_items_setting( $name ){
        $setting = isset($this->items_settings[$name]) ? $this->items_settings[$name] : '';
        return $setting;
    }

    //Retruns the value of one of the repeater settings
    public function get_setting( $name ){
        $setting = '';
        if( isset($this->settings[$name]) )
            $setting = $this->settings[$name];
        return $setting;
    }

}




// =============================================================================
//
// =============================================================================
class RB_Form_Field_Controller{
    public $id;
    public $type;
    public $value;
    public $render_nonce = false;
    public $title;
    public $settings = array(
        'title'	        => '',
        'label'			=> '',
        'classes'		=> '',
        'type'          => 'RB_Input_Control',
        'input_type'    => 'text',
        'item_title'    => 'Item',
    );

    public function __construct($id, $value = '', $options = array()) {
        $this->settings = array_merge($this->settings, $options);
        $this->id = $this->settings['id'] = $id;
        $this->controls = $this->settings['controls'];
        $this->type = $this->settings['type'];
        $this->title = $this->settings['title'];
        $this->collapsible = $this->settings['collapsible'];
        $this->value = $value;
    }

    //Renders the controller accordingly to the settings passed
    public function render(){
        if( $this->is_repeater() ){
            $this->render_repeater();
        }
        else{
            if( $this->render_nonce )
                wp_nonce_field( basename( __FILE__ ), $this->id . '_nonce' );

            if($this->is_group())
                $this->render_group();
            else
                $this->render_single();
        }
    }

    //Render the control when only one was provided
    public function render_single($args = array()){
        $control_settings = reset($this->controls);//First item in the controls array
        $control_settings['id'] = $control_id;
        $control_type = $control_settings['type'] ? $control_settings['type'] : 'RB_Input_Control';
        $single_field = new RB_Form_Single_Field($this->id, $this->value, $control_settings, $control_type);
        $single_field->render();
    }

    //Renders the controls in the controls array
    public function render_group($args = array()){
        if( is_array($this->settings['controls']) ){
            $group_settings = is_array($args['group_settings']) ? $args['group_settings'] : array();
            $group = new RB_Form_Group_Field($this->id, $this->value, $this->settings['controls'], $group_settings);
            $group->render();
        }
    }

    //Renders a repeater of controls
    public function render_repeater($args = array()){
        $repeater = new RB_Form_Repeater_Field($this->id, $this->value, $this->controls, $items_settings = array(
            'collapsible'   => true,
        ), $this->settings);

        $repeater->render();
    }

    // =========================================================================
    //
    // =========================================================================
    public function is_group(){
        return is_array($this->controls) && count($this->controls) > 1;
    }

    public function is_repeater(){
        return $this->settings['repeater'] == true;
    }

    // =========================================================================
    // COLLAPSIBLE
    // =========================================================================
    public function get_collapsible_type(){
        $type = 'accordion';
        if( is_array($this->collapsible) ){
            switch($this->collapsible['type']){
                case 'common': $type = 'common'; break;
            }
        }
        return $type;
    }
}
