(function($){

    // =========================================================================
    // AUX
    // =========================================================================
    function getInputValue( $input ){
        var value = '';
        if( $input.attr('type') == 'checkbox' )
            value = $input.is(':checked');
        else
            value = $input.val();

        if( $input.attr('rb-json') )
            value = JSON.parse(value);

        return value;
    }

    // =========================================================================
    // CONTROLS MANAGER
    // =========================================================================

    // =========================================================================
    // Manages the value of a repeater type control field
    // =========================================================================
    var repeaterType = {
        getValue: function($panel){
            var finalValue = [];

            if( this.isGroupRepeater($panel) ){
                var $inputs = $panel.find('[rb-control-group-value]');
                $inputs.each(function(){
                    var $groupPanel = $(this).closest('.rb-form-control-field-group');
                    var groupValue = JSON.parse(groupType.getValue($groupPanel));
                    //console.log(groupValue);
                    finalValue.push(groupValue);
                });

                finalValue = JSON.stringify(finalValue);
            }
            else{
                var $singlePanels = $panel.find('.rb-form-control-single-field');
                $singlePanels.each(function(){
                    var value = singleType.getValue($(this));
                    finalValue.push(value);
                });

                finalValue = JSON.stringify(finalValue);
            }

            //console.log(finalValue);
            return finalValue;
        },
        getValueInput: function($panel){
            return $panel.children('[rb-control-repeater-value]');
        },
        updateValue: function($panel){
            var newValue = this.getValue($panel);
            var $valInput = this.getValueInput($panel);
            $valInput.val(newValue).trigger('change');
        },
        isGroupRepeater: function($panel){
            return $panel.attr('data-type') == 'group';
        },
        getGroupBaseID: function($panel){
            return $panel.attr('data-id');
        },
        generateNewField: function($panel){
            var baseControlHtml = $panel.attr('data-control');

            var $controlsContainer = $panel.children('.controls');
            var $controls = $controlsContainer.children('.rb-form-control');

            var newControlIndex = $controls.length > 0 ? $controls.length + 1 : 1;
            var repeaterID = $panel.attr('data-id');

            var finalControlHtml = baseControlHtml.replace(/\(__COUNTER_PLACEHOLDER\)/g, newControlIndex);
            var $controlHtml = $(finalControlHtml);

            //If the empty repeater message is showing, hide it
            if( this.isEmpty($panel) )
                $panel.find('.rb-repeater-empty-message').slideUp();

            //Insert new control
            $controlHtml.appendTo($controlsContainer);

            //Animate insertion
            setTimeout(function(){
                $controlHtml.css('display', 'none');
                $controlHtml.slideDown(200);
            }, 1);
        },
        deleteField: function($panel, $field){
            var repeaterManager = this;

            if( $panel.children('.controls').find('.rb-form-control').length == 1 )
                $panel.find('.rb-repeater-empty-message').slideDown();

            $field.slideUp(200, function(){
                $field.remove();
                repeaterManager.updateControlsIds($panel);
                repeaterManager.updateControlsTitle($panel);
            });
        },
        updateControlsIds: function($panel){
            var repeaterID = $panel.attr('data-id');
            var $controlsContainer = $panel.children('.controls');
            var $controls = $controlsContainer.children('.rb-form-control');
            var isGroupRepeater = this.isGroupRepeater($panel);

            if( !isGroupRepeater ){
                $controls.each(function(index){
                    var newID = repeaterID + '__' + (index + 1);
                    var $controlValue = $(this).find('[rb-control-value]');

                    if( $controlValue.length )
                        $controlValue.attr('name', newID);
                });
            }
            else{
                $controls.each(function(index){
                    var newID = repeaterID + '__' + (index + 1);
                    var $controlGroupValue = $(this).find('[rb-control-group-value]').first();
                    var $singleControls = $(this).find('.group-control-single');

                    $(this).attr('data-id', newID);

                    if( $controlGroupValue.length )
                        $controlGroupValue.attr('name', newID);

                    $singleControls.each(function( index ){
                        var singleID = $(this).attr('data-id');
                        var $inputValue = $(this).find('[rb-control-value]');
                        $inputValue.attr('name', newID + '-' + singleID);
                    });
                });
            }
        },
        //Updates the title of a single group
        updateGroupTitle: function( $panel, $group ){
            var titleLink = $panel.attr('data-title-link');
            var baseTitle = $panel.attr('data-base-title');

            var newTitle = '';
            var $title = $group.find('[data-title]');

            if($title.length){
                if(titleLink){
                    var $linkedControl = $group.find('[data-id='+titleLink+']');
                    console.log($linkedControl);
                    if( $linkedControl.length ){
                        var $valueInput = $linkedControl.find('[rb-control-value]').first();
                        if( $valueInput.length ){
                            var controlValue = getInputValue($valueInput);
                            if( controlValue != '' )
                                newTitle = controlValue;
                        }
                    }
                }
            }
            if( newTitle == ''){
                console.log($group);
                newTitle = baseTitle.replace("($n)", $group.index() + 1 );
            }

            $title.text(newTitle);

        },
        //Updates all the titles
        updateControlsTitle: function($panel){
            var titleLink = $panel.attr('data-title-link');
            var baseTitle = $panel.attr('data-base-title');

            var $controlsContainer = $panel.children('.controls');
                var $controls = $controlsContainer.children('.rb-form-control');

            $controls.each(function(index){
                var newTitle = '';
                var $title = $(this).closest('.rb-form-control').find('[data-title]');

                if($title.length){
                    if(titleLink){
                        var $linkedControl = $(this).find('[data-id='+titleLink+']');
                        if( $linkedControl.length ){
                            var $valueInput = $linkedControl.find('[rb-control-value]').first();
                            if( $valueInput.length ){
                                var controlValue = getInputValue($valueInput);
                                if( controlValue != '' )
                                    newTitle = controlValue;
                            }
                        }
                    }
                }
                if( newTitle == ''){
                    newTitle = baseTitle.replace("($n)", index + 1);
                }

                $title.text(newTitle);
            });
        },
        isEmpty: function($panel){
            return $panel.children('.controls').find('.rb-form-control');
        },
    }

    // =========================================================================
    // Manages the value of a group type control field
    // =========================================================================
    var groupType = {
        getValue: function($panel){
            var finalValue = {};

            var $inputs = $panel.find('[rb-control-value]');
            var groupID = this.getGroupBaseID($panel);

            $inputs.each(function(){
                var key = $(this).attr('name').replace(groupID + '-','');
                finalValue[key] = getInputValue($(this));
                console.log(key, finalValue[key]);
            });

            console.log(JSON.stringify(finalValue));

            return JSON.stringify(finalValue);
        },
        getValueInput: function($panel){
            return $panel.children('[rb-control-group-value]');
        },
        updateValue: function($panel){
            var newValue = this.getValue($panel);
            var $valInput = this.getValueInput($panel);
            $valInput.val(newValue).trigger('change');
        },
        isGroup: function($panel){
            return $panel.hasClass('rb-form-control-field-group');
        },
        getGroupBaseID: function($panel){
            return $panel.attr('data-id');
        },

    }

    // =========================================================================
    // Manages the value of a single type control field
    // =========================================================================
    var singleType = {
        getValue: function($panel){
            var finalValue = '';

            if( this.isSingle($panel) ){
                var $input = $panel.find('[rb-control-value]').first();
                if( $input.length != 0 )
                    finalValue = getInputValue($input);
            }

            return finalValue;
        },
        isSingle: function($panel){
            return $panel.hasClass('rb-form-control-single-field');
        },
    }


    // =========================================================================
    // EVENTS
    // =========================================================================


    $(document).ready(function(){

        // =============================================================================
        // GROUP VALUE UPDATE
        // =============================================================================
        $(document).on('input change', '.rb-form-control-field-group [rb-control-value]', function(){
            $panel = $(this).closest('.rb-form-control-field-group');
            console.log($(this));
            if($panel.length != 0){
                groupType.updateValue($panel);
            }
        });

        // =========================================================================
        // REPEATER VALUE UPDATE
        // =========================================================================
        //When it is a groups repeater
        $(document).on('change input', '.rb-form-control-repeater [rb-control-group-value]', function(){
            var $panel = $(this).closest('.rb-form-control-repeater');
            if($panel.length != 0)
                repeaterType.updateValue($panel);
        });
        //When it is a single input repeater
        $(document).on('change input', '.rb-form-control-repeater [rb-control-value]', function(){
            var $panel = $(this).closest('.rb-form-control-repeater');
            var isGroupRepeater = repeaterType.isGroupRepeater($panel);

            //We have to check if it is a group repeater, as both single and groups uses rb-control-value
            if($panel.length != 0 && !isGroupRepeater)
                repeaterType.updateValue($panel);
        });

        // =====================================================================
        // DINAMIC TITLE
        // =====================================================================
        $(document).on('change input', '.rb-form-control-repeater[data-title-link] [rb-control-value]', function(){

            var $panel = $(this).closest('.rb-form-control-repeater');
            var linkID = $panel.attr('data-title-link');
            var $control = $(this).closest('.group-control-single[data-id="'+linkID+'"]');
            //console.log($panel, linkID, $control);
            if( $control.length ){
                var $group = $control.closest('.rb-form-control-field-group');
                if( $group.length )
                    repeaterType.updateGroupTitle($panel, $group);
            }

        });

        $('.rb-form-control-repeater[data-title-link]').each(function(){
            var $panel = $(this).closest('.rb-form-control-repeater');
            repeaterType.updateControlsTitle($panel);
        });

        // =====================================================================
        // ADD ITEM
        // =====================================================================
        $(document).on('click', '.rb-form-control-repeater > .repeater-add-button > i', function(){
            var $panel = $(this).closest('.rb-form-control-repeater');
            repeaterType.generateNewField($panel);
            repeaterType.updateValue($panel);
        });

        // =====================================================================
        // REMOVE ITEM
        // =====================================================================
        $(document).on('click', '.rb-form-control-repeater > .controls > .rb-form-control .action-controls > .delete-button,  .rb-form-control-repeater > .controls > .rb-form-control > .collapsible-header > .action-controls > .delete-button',
        function(event){
            console.log(event);
            event.stopPropagation();
            var $panel = $(this).closest('.rb-form-control-repeater');
            var $fieldItem = $(this).closest('.rb-form-control');
            repeaterType.deleteField($panel, $fieldItem);
            repeaterType.updateValue($panel);
        });

        // =====================================================================
        // SORTING
        // =====================================================================
        $( ".rb-form-control-repeater .controls" ).sortable({
            update: function(ev, ui){
                var $panel = ui.item.closest('.rb-form-control-repeater');
                repeaterType.updateValue($panel);
                repeaterType.updateControlsIds($panel);
                repeaterType.updateControlsTitle($panel);
            },
        });

    });


})(jQuery);
