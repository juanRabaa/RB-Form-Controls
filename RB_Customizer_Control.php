<?php
class RB_Customizer_Field_Control extends WP_Customize_Control{
    public $controls;

    public function __construct($manager, $id, $args = array()){
        parent::__construct($manager, $id, $args);
        $this->options = $args;
        $this->options['meta_id'] = $id;
    }

    public function get_input_link(){
        ob_start();
        $this->link();
        return ob_get_clean();
    }

    public function sanitazed_value(){
        $value = $this->value();
        //If is group
        if( is_array($this->controls) && count($this->controls) > 1 ){
            $value = json_decode($value, true);
        }

        return $value;
    }

    public function render_content(){
        $controller = new RB_Form_Field_Controller($this->id, $this->sanitazed_value(), $this->options);
        ?>
        <div class="rb-customizer-control">
            <?php $controller->render(); ?>
            <input rb-customizer-control-value type='hidden' <?php $this->link(); ?>>
        </div>
        <?php
    }
}
