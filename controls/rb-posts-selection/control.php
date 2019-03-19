<?php

class RB_Post_Selector extends RB_Metabox_Control{
    public $options = array(
        'class'         => '',
        'option_none'   => 'None',
        'post_type'     => 'post',
    );

    public function __construct($value, $settings) {
         parent::__construct($value, $settings);
         $this->options = wp_parse_args( $settings, $this->options );
    }

    public function get_dropdown(){
        extract($this->settings);
        extract($this->options);

        /*Dropdown generation*/
        $dropdown = "<select
            name='$this->id'
            class='$class rb-tax-value'
            rb-control-value
        >";
        $dropdown .= '<option value=""'.selected($this->value, $post->ID, false).'>'.__( $option_none ).'</option>';
        $posts = get_posts(array(
            'posts_per_page'       	=> -1,
            'orderby'               => 'title',
            'order'                 => 'ASC',
            'post_type'             => $post_type,
        ));
        foreach ( $posts as $post ){
            $dropdown .= '<option value="'. $post->ID .'"'.selected($this->value, $post->ID, false).'>'.$post->post_title.'</option>';
        }

        $dropdown .= "</select>";

        return $dropdown;
    }

    public function render_content(){
        $this->print_control_header();
        ?>
        <div class="rb-post-selection">
            <?php echo $this->get_dropdown();  ?>
        </div>
        <?php
    }
}
