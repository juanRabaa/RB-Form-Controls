(function($){

    $(document).ready(function(){
        $(document).on('click', '.input-wp-media-image-holder', function(e) {
            e.stopPropagation();
            e.preventDefault();
            if(this.hasAttribute("media-open"))
                return;
            var $button = $(this);
			$button.attr('media-open', '');
            console.log( $(this));
            var $controlPanel = $(this).closest(".customize-control-multiple-inputs");
            var $input_field = $(this).find("input");
            var $image_holder = $(this).find(".input-image-src");
            var custom_uploader = wp.media.frames.file_frame = wp.media({
                title: 'Add Image',
                button: {
                    text: 'Add Image',
                },
                multiple: false
            });
            custom_uploader.on('select', function() {
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                console.log(attachment.url);
                $input_field.val(attachment.url).trigger('input');
                //updateValue( $controlPanel );
                $image_holder.attr('src', attachment.url );
                $button.removeAttr('media-open');
            });
            custom_uploader.open();
        });

        $(document).on('click', '.inputs-generator-inputs-holder .remove-image-button i', function( event ){
            event.stopPropagation();
            emptyImageInput( $(this).closest('.inputs-generator-inputs-holder') );
        });

        function emptyImageInput( $inputHolder ){
    		var $controlPanel = $inputHolder.closest(".customize-control-multiple-inputs");
    		var $image = $inputHolder.find('.input-image-src');
    		var $input = $inputHolder.find('input');
    		$image.attr('src','');
    		$input.val('').trigger('input');
    		console.log($controlPanel);
    		updateValue( $controlPanel );
    	}
    });

})(jQuery);
