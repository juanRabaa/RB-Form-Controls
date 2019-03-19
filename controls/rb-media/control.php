<?php

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
