<?php

class RB_Input_Control extends RB_Metabox_Control{
    public $input_render;
    public $id;
    public $value;
    public $input_type;
    public $choices;

    public function __construct($value, $settings) {
        parent::__construct($value, $settings);
        if( $this->settings['input_type'] == 'checkbox' )
            $this->strict_type = 'bool';
    }

    public function render_content(){
        extract($this->settings);
        $this->value = esc_attr($this->value);
        $this->choices = $choices;
        $this->input_type = $input_type;
        $this->option_none = $option_none;

        if( $input_type ):
            if( $label && $this->input_type != 'checkbox' )
                $this->print_control_header();
            ?>
            <div class="rb-inputs-control">
                <?php $this->render_the_input(); ?>
            </div>
        <?php
        else:
        ?>
        <p> A type must be assign to the control (input_type is not set) </p>
        <?php
        endif;
    }

    public function render_the_input(){
        $this->select_input();
        if( is_array($this->input_render) ){
            call_user_func($this->input_render);
        }
    }

    public function select_input(){
        switch($this->input_type){
            case 'text': $this->input_render = array($this, 'render_text_input'); break;
            case 'textarea': $this->input_render = array($this, 'render_textarea_input'); break;
            case 'checkbox': $this->input_render = array($this, 'render_checkbox_input'); break;
            case 'number': $this->input_render = array($this, 'render_number_input'); break;
            case 'select': $this->input_render = array($this, 'render_select_input'); break;
            case 'datetime-local': $this->input_render = array($this, 'render_datetime_input'); break;
            default: $this->input_render = false; break;
        }
    }

    public function render_text_input(){
        ?><input type="text" rb-control-value name="<?php echo $this->id; ?>" value="<?php echo $this->value; ?>"></input><?php
    }

    public function render_textarea_input(){
        $rows = $this->rows ? esc_attr($this->rows) : 5;
        ?><textarea type="textarea" rows="<?php echo $rows; ?>" rb-control-value name="<?php echo $this->id; ?>" value="<?php echo $this->value; ?>"></textarea><?php
    }

    public function render_number_input(){
        ?><input type="number" rb-control-value name="<?php echo $this->id; ?>" value="<?php echo $this->value; ?>"></input><?php
    }

    public function render_checkbox_input(){
        $this->sanitaze_checkbox_value();
        $checked_attr = $this->value ? 'checked' : '';
        ?>
        <label>
            <input type="hidden" rb-control-value name="<?php echo $this->id; ?>" value="<?php echo $this->value; ?>">
            <input type="checkbox" <?php echo $checked_attr; ?> onclick="this.previousElementSibling.value=1-this.previousElementSibling.value">
            <span><?php echo $this->settings['label']; ?></span>
        </label>
        <?php
    }

    public function render_datetime_input(){
        ?><input type="datetime-local" rb-control-value name="<?php echo $this->id; ?>" value="<?php echo $this->value; ?>"></input><?php
    }

    public function render_select_input(){
        //$choices = array( $value => $title, ...)
        //$option_none = array($value, $title)
        if( is_array($this->choices) && !empty($this->choices) ): ?>
        <select class="browser-default" rb-control-value name="<?php echo $this->id; ?>">
            <?php if( is_array($this->option_none) && !empty($this->option_none) ): ?>
                <option value="<?php echo $this->option_none[0]; ?>"><?php echo $this->option_none[1]; ?></option>
            <?php else: ?>
                <option value=""></option>
            <?php endif; ?>
            <?php
                foreach($this->choices as $value => $title):
                    $selected_attr = $value == $this->value ? 'selected' : '';
            ?>
                <option value="<?php echo $value; ?>" <?php echo $selected_attr; ?>><?php echo $title; ?></option>
            <?php endforeach; ?>
        </select>
        <?php
        else:?>
        <p>No choices were given for the selection control</p>
        <?php endif;
    }

    public function sanitaze_checkbox_value(){
        if( !isset($this->value) || $this->value == '' || $this->value == 'false' || $this->value == '0' )
            $this->value = false;
        else
            $this->value = true;
    }
}
