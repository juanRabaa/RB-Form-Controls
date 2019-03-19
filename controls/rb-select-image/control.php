<?php

class RB_Select_Image extends RB_Metabox_Control{

    public function render_content(){
        extract($this->settings);

        ?>
        <div class="rb-image-selection-control">
            <?php if(is_array( $options )): ?>
                <div class="images-options-container">
                <?php foreach($options as $image_data): ?>
                    <div class="image-option" <?php echo $this->get_container_style_attr(); ?>>
                        <input
                            rb-control-value
                            class="image-option-input"
                            type="radio"
                            value="<?php echo esc_attr( $image_data["value"] ); ?>"
                            id="<?php echo esc_attr($id); ?>"
                            name="<?php echo esc_attr($id); ?>"
                            <?php checked( $this->value, $image_data["value"] ); ?>
                            aria-label="<?php echo $image_data["title"] ?>"
                            title="<?php echo $image_data["title"] ?>"
                        />
                        <div class="image-selection-image" <?php echo $this->get_image_style_attr($image_data); ?>></div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    public function get_image_style_attr( $image_data ){
        $sty_attr = 'style=\'background-image: url("'.$image_data["src"].'");';

        if($this->settings['height']){
            $height = esc_attr($this->settings['height']);
            $sty_attr .= "height: $height;";
        }

        $sty_attr .= "'";
        return $sty_attr;
    }

    public function get_container_style_attr(){
        $sty_attr = "";

        if( $this->settings['max_width'] || $this->settings['min_width'] ){
            $sty_attr = 'style="';
            if($this->settings['max_width']){
                $max_width = esc_attr($this->settings['max_width']);
                $sty_attr .= "max-width: $max_width;";
            }
            if($this->settings['min_width']){
                $min_width = esc_attr($this->settings['min_width']);
                $sty_attr .= "min-width: $min_width;";
            }
            $sty_attr .= '"';
        }
        return $sty_attr;
    }

}
