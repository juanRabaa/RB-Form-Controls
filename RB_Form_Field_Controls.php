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
    public $settings = array(
        'label'         => '',
        'description'   => '',
    );
    //Forces the control value to be of a certain type
    public $strict_type;

    public function __construct($value, $settings) {
        $this->value = $value;
        $this->settings = array_merge($this->settings, $settings);
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
        ?>
        <div class="control-header">
            <?php $this->print_label(); ?>
            <?php $this->print_description(); ?>
        </div>
        <?php
    }

    public function print_description(){
        $description = $this->settings['description'];
        if($description):
        ?> <p class="control-description"><?php echo $description; ?></p> <?php
        endif;
    }

    public function print_label( $for = '' ){
        $for = $for ? $for : $this->id;
        $label = $this->settings['label'];
        if($label):
        ?> <label class="control-title" for="<?php echo $for; ?>"><?php echo $label; ?></label> <?php
        endif;
    }
}
