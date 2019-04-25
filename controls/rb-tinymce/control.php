<?php

class RB_tinymce_control extends RB_Metabox_Control{

    public function render_content(){
        extract($this->settings);
        if( $label ): ?>
        <label for="<?php echo "asdasd"; ?>"><?php echo $label; ?></label>
        <br />
        <?php endif;?>
        <div class="rb-tinymce-control">
            <div class="editor-placeholder">
                <span class="editor-open-button">Editar</span>
            </div>
            <div class="tinymce-editor-container">
        		<!-- <div class="rb-control-panel-title-container controls-bar">
            		<i class="fas fa-chevron-circle-left rb-control-panel-close-button close-button"></i>
            		<h5 class="rb-control-panel-title"></h5>
        		</div> -->
        		<div class="media-button">Insert multimedia</div>
        		<div class="rb-tinymce-editor" id="<?php echo esc_attr( $this->id ); ?>">
                    <?php echo esc_html($value); ?>
        		</div>
                <input placeholder-value type="hidden" value="<?php echo esc_attr($this->value); ?>"></input>
    		</div>
        </div>
        <?php
    }
    
}