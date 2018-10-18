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
        $this->type = $type;
        $this->settings = wp_parse_args($this->settings, $settings);
    }

    public function render_control(){
        if( class_exists($this->type) && method_exists($this->type, 'render_content') )
            $renderer = new $this->type( $this->value, $this->control_settings);
    }

    public function render(){
        $this->render_control();
    }
}

class RB_Form_Group_Field{
    /**
    * @param array $value
    *   Controls values.
    *   array( $control_id => $control_value, ... )
    * @param array $controls_settings
    *   Controls information. One value for each control.
    *   array( $control_id => $control_settings, ... )
    */
    public function __construct($id, $value, $controls_settings, $settings = array()) {
        $this->id = $id;
        $this->value = $value;
        $this->controls_settings = $control_settings;
        $this->settings = wp_parse_args($this->settings, $settings);
    }

    public function render(){
        $value = $this->value;
        $value_is_set = is_array($value) && !empty($value);

        $this->print_field_value_input();

        foreach( $this->controls_settings as $control_id => $control_settings ){
            $control_group_id = $this->control_id($control_id);
            $control_value = $value_is_set ? $value[$control_ID] : '';
            $control_type = $control_settings['type'] ? $control_settings['type'] : 'RB_Input_Control';
            //USAR EL RB_Form_Single_Field O SOLO EL CONTROL????
            $control_form_field = new RB_Form_Single_Field( $control_group_id, $control_value, $control_settings, $control_type );
            $control_form_field->render_control();
        }
    }

    public function control_id($control_id){
        return $this->id . '-' . $control_id;
    }

    public function print_field_value_input(){
        ?>
        <input type="hidden" rb-control-group-value name="<?php echo $this->id; ?>" value="<?php echo esc_attr(json_encode($this->value)); ?>"></input>
        <?php
    }
}

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

    //Render the control when only one was provided
    public function content_render($args = array()){
        $value = isset($args['value']) ? $args['value'] : $this->value;
        $control_id = isset($args['id']) ? $args['id'] : $this->id;

        $control = reset($this->controls);//First item in the controls array
        $settings = array_merge($this->settings, $control);
        $settings['id'] = $control_id;
        $control_type = $control['type'] ? $control['type'] : 'RB_Input_Control';
        $renderer = new $control_type($value, $settings);

        ?>
        <div class="rb-form-control-single-field rb-form-control">
            <div class="collapsible-body control-content">
            <?php
            $this->print_action_controls( $args['action_controls'] );
            $renderer->print_control();
            ?>
            </div>
        </div>
        <?php
    }

    //Renders the controls in the controls array
    public function render_group($args = array()){
        $value = isset($args['value']) ? $args['value'] : $this->value;
        $prefix_id = isset($args['prefix_id']) ? $args['prefix_id'] : $this->id;
        $collapsible_class = $this->collapsible ? 'rb-collapsible' : '';

        $value_is_set = is_array($value) && !empty($value);
        $controls = $this->settings['controls'];
        if( is_array($controls) ){
            ?>
            <div data-id="<?php echo $prefix_id; ?>" class="rb-form-control-field-group rb-form-control <?php echo $collapsible_class; ?>">
                <input type="hidden" rb-control-group-value name="<?php echo $prefix_id; ?>" value="<?php echo esc_attr(json_encode($value)); ?>"></input>
                <?php
                    if( $this->collapsible )
                        $this->print_collapsible_header( array( 'action_controls' => $args['action_controls'] ) );
                ?>
                <div class="collapsible-body control-content">
                    <?php
                        if( !$this->collapsible )
                            $this->print_action_controls( $args['action_controls'] );
                    ?>
                    <div class="controls">
                    <?php
                    foreach($controls as $control_ID => $control){
                        $settings = array_merge($this->settings, $control);
                        $settings['id'] = $prefix_id . '-' . $control_ID;
                        $control_value = $value_is_set ? $value[$control_ID] : '';
                        $control_type = $control['type'] ? $control['type'] : 'RB_Input_Control';
                        $renderer = new $control_type($control_value, $settings);

                        ?><div class="group-control-single" data-id="<?php echo $control_ID; ?>"><?php
                            $renderer->print_control();
                        ?></div><?php
                    }
                    ?>
                    </div>
                </div>
            </div><?php
        }
    }

    public function render_repeater($args = array()){
        $value = isset($args['value']) ? $args['value'] : $this->value;
        $repeater_type = $this->is_group() ? 'group' : 'single';
        $item_title = isset($args['item_title']) ? $args['item_title'] : $this->item_title;
        $this->collapsible = isset($this->collapsible) ? $this->collapsible : true;
        $collapsible_type = $this->get_collapsible_type();
        $accordion_attr = $collapsible_type == 'accordion' ? 'data-rb-accordion' : '';
        $dinanmic_title_attr = $this->settings['title_link'] ? 'data-title-link="'. $this->settings['title_link'] . '"' : '';
        $base_title_attr = $this->settings['item_title'] ? 'data-base-title="'.$this->settings['item_title'].'"' : '';
        $this->item_index = '(__COUNTER_PLACEHOLDER)';

        ?>
        <div class="rb-form-control-repeater" data-id="<?php echo $this->id; ?>" data-type="<?php echo $repeater_type; ?>" <?php echo $dinanmic_title_attr; ?>
            <?php echo $base_title_attr; ?>
            data-control="<?php echo esc_attr($this->ob_get_control(
                array(
                    'value'            => '',
                    'action_controls'  => array( 'delete_button' ),
                    'prefix_id'        => $this->id  . '__(__COUNTER_PLACEHOLDER)',
                    'id'               => $this->id  . '__(__COUNTER_PLACEHOLDER)',
            ))); ?>">
            <!-- REPEATER VALUE -->
            <input type="hidden" rb-control-repeater-value name="<?php echo $this->id; ?>" value="<?php echo esc_attr(json_encode($value)); ?>"></input>
            <?php
                if($this->render_nonce)
                    wp_nonce_field( basename( __FILE__ ), $this->id . '_nonce' );
            ?>
            <!-- REPEATER CONTROLS -->
            <div class="controls" <?php echo $accordion_attr; ?>>
        <?php
        $this->item_index = 1;
        if(is_array($value)){

            if( $this->is_group() ){
                foreach($value as $group_value){
                    $this->render_group(array(
                        'value'             => $group_value,
                        'prefix_id'         => $this->id  . '__' . $count, // id__1, id__2, id__3, ...
                        'action_controls'   => array( 'delete_button' ),
                    ));
                    $this->item_index++;
                }
            }
            else{
                foreach($value as $control_value){
                    $this->content_render(array(
                        'value'             => $control_value,
                        'id'                => $this->id  . '__' . $count,
                        'action_controls'   => array( 'delete_button' ),
                    ));
                    $this->item_index++;
                }
            }
        }
        //There is not a value to work on
        else{
            echo $this->ob_get_control( array(
                'action_controls' => array('delete_button'),
                'prefix_id'       => $this->id  . '__' . 1,
                'id'       => $this->id  . '__' . 1,
            ));
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
                $this->content_render();
        }
    }

    public function is_group(){
        return is_array($this->controls) && count($this->controls) > 1;
    }

    public function is_repeater(){
        return $this->settings['repeater'] == true;
    }


    public function get_setting( $name ){
        $setting = '';
        if( isset($this->settings[$name]) )
            $setting = $this->settings[$name];
        return $setting;
    }

    //Returns the control as a string
    public function ob_get_control( $args = array() ){
        ob_start();
        if($this->is_group())
            $this->render_group( $args );
        else
            $this->content_render( $args );
        return ob_get_clean();
    }

    public function print_action_controls( $action_controls ){
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

    public function print_empty_message(){
        ?>
        <div class="rb-repeater-empty-message">
            <?php
            $message = $this->get_setting('empty_message');
            $message = $message ? $message : 'Click on the button below to start adding content :)';
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

    public function print_collapsible_header( $options = array() ){
        $defaults = array(
            'title' => $this->settings['item_title'] ? $this->settings['item_title'] : 'Item',
            'link'  => '',
        );
        $settings = $defaults;
        if( is_array($this->collapsible) )
            $settings = array_merge($settings, $this->collapsible);

        if( $this->is_repeater() && $this->item_index )
            $settings['title']  = str_replace('($n)',$this->item_index,$settings['title']);
        ?>
        <div class="collapsible-header container">
            <h1 data-title="<?php echo $settings['title']; ?>" class="title"><?php echo $settings['title']; ?></h1>
            <?php $this->print_action_controls( $options['action_controls'] ); ?>
        </div>
        <?php
    }
}
