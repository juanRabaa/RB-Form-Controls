<?php
// =============================================================================
// CONTROLS
// =============================================================================
/* Para que un control funcione correctamente, debe tener la function render_content($value, $settings)
/* $value => metabox value
/* $settings => configuracion del control
/* Tiene que tener un input donde se guarde el valor a guardar con las siguientas caracteristicas:
/* <input name="<?php echo $settings->id; ?>" value="<?php echo esc_attr($settings->value); ?>"></input>
*/

abstract class RB_Metabox_Control{
    public $id;
    public $value;
    public $settings;

    public function __construct($value, $settings) {
        $this->value = $value;
        $this->settings = $settings;
        $this->id = $settings['id'];
    }

    //Wraps the content of the control and renders it.
    public function print_control(){
        ?><div class="rb-wp-control"><?php $this->render_content(); ?></div><?php
    }

    //The method that renders the control. Should be overriden by the children
    abstract public function render_content();

    //Prints the control descriptions.
    public function print_control_header(){
        $label = $this->settings['label'];
        $description = $this->settings['description'];
        if( $label || $description ):?>
            <div class="control-header">
                <?php if( $label ): ?>
                <label class="control-title" for="<?php echo $this->settings['id']; ?>"><?php echo $label; ?></label>
                <?php endif; ?>
                <?php if($description): ?>
                <p class="control-description"><?php echo $this->settings['description']; ?></p>
                <?php endif; ?>
            </div>
        <?php endif;
    }
}

// =============================================================================
// BASIC CONTROLS
// =============================================================================
class RB_tinymce_control extends RB_Metabox_Control{
    public function render_content(){
        extract($this->settings);
        if( $label ): ?>
        <label for="<?php echo $id; ?>"><?php echo $label; ?></label>
        <br />
        <?php endif;
        wp_editor( esc_attr( $this->value ), $id, array(
            'wpautop'       => true,
            'media_buttons' => false,
            'textarea_name' => $id,
            'textarea_rows' => 10,
            'teeny'         => true
        ));
    }
}

class RB_Input_Control extends RB_Metabox_Control{
    public $input_render;
    public $id;
    public $value;
    public $input_type;
    public $choices;

    public function render_content(){
        extract($this->settings);
        $this->value = esc_attr($this->value);
        $this->choices = $choices;
        $this->input_type = $input_type;
        $this->option_none = $option_none;

        if( $input_type ):
            if( $label ): ?>
            <?php $this->print_control_header(); ?>
            <?php endif;?>
            <div class="rb-inputs-control">
                <?php $this->render_the_input(); ?>
            </div>
        <?php
        else:
        ?>
        <p> A type must be assign to the control </p>
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
            case 'checkbox': $this->input_render = array($this, 'render_checkbox_input'); break;
            case 'number': $this->input_render = array($this, 'render_number_input'); break;
            case 'select': $this->input_render = array($this, 'render_select_input'); break;
            default: $this->input_render = false; break;
        }
    }

    public function render_text_input(){
        ?><input type="text" rb-control-value name="<?php echo $this->id; ?>" value="<?php echo $this->value; ?>"></input><?php
    }

    public function render_number_input(){
        ?><input type="number" rb-control-value name="<?php echo $this->id; ?>" value="<?php echo $this->value; ?>"></input><?php
    }

    public function render_checkbox_input(){
        $checked_attr = $this->value ? 'checked' : '';
        ?><input type="checkbox" rb-control-value name="<?php echo $this->id; ?>" value="<?php echo $this->value; ?>" <?php echo $checked_attr; ?>></input><?php
    }

    public function render_select_input(){
        //$choices = array( $value => $title, ...)
        //$option_none = array($value, $title)
        if( is_array($this->choices) && !empty($this->choices) ): ?>
        <select rb-control-value name="<?php echo $this->id; ?>">
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
}

class RB_doublelist_control extends RB_Metabox_Control{

    public function render_content(){
        extract($this->settings);
        if( $label ): ?>
        <?php $this->print_control_header(); ?>
        <?php endif;?>
        <div class="rb-double-list-control" data-empty-item="<?php echo esc_attr( $this->get_empty_input() ); ?>">
            <ul class="items">
                <?php
                    $items = null;
                    if( $this->value ){
                        $items = json_decode($this->value, true);
                    }

                    if( !isset($items) ){
                        $this->print_inputs_pair();
                    }
                    else{
                        foreach($items as $item){
                            $this->print_inputs_pair($item['name'], $item['value']);
                        }
                    }
                ?>
            </ul>
            <div class="add-item-button-container">
                <span class="add-item dashicons dashicons-plus-alt"></span>
            </div>
            <input rb-control-value name="<?php echo $id; ?>" type="hidden" value="<?php echo esc_attr($this->value); ?>"></input>
        </div>
        <?php
    }

    public function get_empty_input(){
        ob_start();
        $this->print_inputs_pair();
        return ob_get_clean();
    }

    public function print_inputs_pair($name = "", $value = ""){
        ?>
        <li class="item">
            <div class="handle">
                <div></div>
            </div>
            <div class="content">
                <div class="name">
                    <input value="<?php echo $name; ?>" type="text">
                </div>
                <div class="value">
                    <input value="<?php echo $value; ?>" type="text">
                </div>
            </div>
            <div class="delete-button">
                <span class="dashicons dashicons-trash"></span>
            </div>
        </li>
        <?php
    }
}

class RB_Images_Gallery_Control extends RB_Metabox_Control{

    public function render_content(){
        $attachments_ids_csv = $this->value;
        $attachments_ids = $attachments_ids_csv ? str_getcsv($attachments_ids_csv) : array();
        extract($this->settings);
        ?>
        <?php $this->print_control_header(); ?>
        <div class="rb-tax-images rb-tax-images-control">
            <input rb-control-value class="rb-tax-value"  name="<?php echo $id; ?>" type="hidden" value="<?php echo esc_attr($attachments_ids_csv); ?>"></input>
            <div class="rb-tax-images-boxes">
                <?php
                if ($attachments_ids):
                    foreach($attachments_ids as $attachment_id ):
                        $attachment = wp_get_attachment( $attachment_id );
                ?>
                <div class="rb-tax-image rb-gallery-box" rel="<?php echo $attachment_id; ?>" style="background-image: url(<?php echo $attachment['thumbnail']; ?>);">
                    <i class="fas fa-times rb-remove"></i>
                </div>
                <?php
                    endforeach;
                endif;
                ?>
            </div>
            <div class="rb-tax-add rb-gallery-box">
                <i class="fas fa-plus rb-add"></i>
            </div>
        </div>
        <?php
    }
}


class RB_Media_Control extends RB_Metabox_Control{

    public function render_content(){
        extract($this->settings);
        ?>
        <?php $this->print_control_header(); ?>
        <div class="inputs-generator-inputs-holder">
            <div class="input-wp-media-image-holder">
                <img class="input-image-src" src="<?php echo esc_attr($this->value); ?>">
                <div class="input-image-placeholder">
                <p> Select an image </p>
                </div>
                <input rb-control-value class="rb-tax-value rb-sub-input"  name="<?php echo $id; ?>" type="hidden" value="<?php echo esc_attr($this->value); ?>"></input>
            </div>
            <div class="remove-image-button"><i class="fas fa-times" title="Remove image"></i></div>
        </div>
        <?php
    }
}
