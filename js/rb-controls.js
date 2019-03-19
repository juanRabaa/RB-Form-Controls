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
                var $inputs = $panel.children('.controls').find('[rb-control-group-value]');
                $inputs.each(function(){
                    var $groupPanel = $(this).closest('.rb-form-control-field-group');
                    var groupValue = JSON.parse(groupType.getValue($groupPanel));
                    //console.log(groupValue);
                    finalValue.push(groupValue);
                });

                finalValue = JSON.stringify(finalValue);
            }
            else{
                var $singlePanels = $panel.children('.controls').find('.rb-form-control-single-field');
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
            if( fieldsController.isCustomizerControl($panel) )
                fieldsController.updateCustomizer($panel, newValue);
        },
        isGroupRepeater: function($panel){
            return $panel.attr('data-type') == 'group';
        },
        getGroupBaseID: function($panel){
            return $panel.attr('data-id');
        },
        getEmptyControl: function($panel){
            return $panel.children('.empty-control').children('.rb-form-control');
        },
        getEmptyControlClone: function($panel, newIndex){
            var $emptyControlClone = this.getEmptyControl($panel).clone();
            var $tempDiv = $('<div>');
            $tempDiv.append($emptyControlClone).html(function(i, oldHTML) {
                return oldHTML.replace(/\(__COUNTER_PLACEHOLDER\)/g, newIndex);
            });
            $emptyControlClone = $tempDiv.children('.rb-form-control');
            return $emptyControlClone;
        },
        generateNewField: function($panel){
            var baseControlHtml = $panel.attr('data-control');

            var $controlsContainer = $panel.children('.controls');
            var $controls = $controlsContainer.children('.rb-form-control');

            var newControlIndex = $controls.length > 0 ? $controls.length + 1 : 1;
            var $newControl = this.getEmptyControlClone($panel, newControlIndex);
            console.log($newControl);
            //If the empty repeater message is showing, hide it
            if( this.isEmpty($panel) )
                $panel.find('.rb-repeater-empty-message').slideUp();

            //Insert new control
            $newControl.appendTo($controlsContainer);

            //Animate insertion
            setTimeout(function(){
                $newControl.css('display', 'none');
                $newControl.slideDown(200);
            }, 1);
        },
        deleteField: function($panel, $field, cb){
            var repeaterManager = this;

            if( $panel.children('.controls').children('.rb-form-control').length == 1 )
                $panel.find('.rb-repeater-empty-message').slideDown();

            $field.slideUp(200, function(){
                $field.remove();
                repeaterManager.updateControlsIds($panel);
                repeaterManager.updateControlsTitle($panel);
                if(cb)
                    cb();
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
                    //console.log($linkedControl);
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
            //console.log($controls);
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
                if( newTitle == '' && baseTitle ){
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
            var isInRepeater = $panel.closest('.rb-form-control-repeater').length != 0;
            var $inputs = $panel.find('[rb-control-value], [rb-control-repeater-value]');
            //We remove the inputs inside a repeater, because we only want the repeater whole value
            $inputs = $inputs.filter( function(){
                var $inputParentRepeater = $(this).closest('.rb-form-control-repeater');
                //If the field is inside a repeater
                if( $inputParentRepeater.length ){
                    var repeaterIsInsideGroup = $inputParentRepeater.closest('.rb-form-control-field-group').length != 0;
                    //If the repeater is inside the group (is a field), and the input is not a repeater value
                    if(repeaterIsInsideGroup && $(this).attr('rb-control-repeater-value') === undefined)
                        return false;
                }
                return true;
            });

            var groupID = this.getGroupBaseID($panel);

            $inputs.each(function(){
                var key = $(this).attr('name').replace(groupID + '-','');
                var value = getInputValue($(this));
                //If it is a repeater, we remove the stringify to avoid a doble conversion
                if($(this).attr('rb-control-repeater-value') != undefined)
                    value = JSON.parse(value);

                //console.log(value);
                finalValue[key] = value;
                //console.log(key, finalValue[key]);
            });

            //console.log(finalValue);
            //console.log(JSON.stringify(finalValue));

            return JSON.stringify(finalValue);
        },
        getValueInput: function($panel){
            return $panel.children('[rb-control-group-value]');
        },
        updateValue: function($panel){
            var newValue = this.getValue($panel);
            var $valInput = this.getValueInput($panel);
            $valInput.val(newValue).trigger('change');
            if( fieldsController.isCustomizerControl($panel) )
                fieldsController.updateCustomizer($panel, newValue);
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
        isTopLevel: function($panel){
            return ( $panel.closest('.rb-form-control-field-group').length == 0 && $panel.closest('.rb-form-control-repeater').length == 0 );
        }
    }

    // =============================================================================
    // GENERAL METHODS
    // =============================================================================
    var fieldsController =  {
        updateCustomizer: function($panel, value){
            if( !($customizerPanel = this.isCustomizerControl($panel)) )
                return;

            let $valueInput = $customizerPanel.children('[rb-customizer-control-value]');
            //console.log($valueInput, value);
            if( $valueInput.length )
                $valueInput.val(value).trigger('change');
        },
        isCustomizerControl: function($panel){
            return this.isTopLevel($panel) && $panel.closest('.rb-customizer-control') ? $panel.closest('.rb-customizer-control') : false;
        },
        isTopLevel: function($panel){
            return ( $panel.parent().closest('.rb-form-control-field-group').length == 0 && $panel.parent().closest('.rb-form-control-repeater').length == 0 );
        },
    }
    // =========================================================================
    // EVENTS
    // =========================================================================


    $(document).ready(function(){

        // =============================================================================
        // CUSTOMIZER SINGLE VALUE UPDATE
        // =============================================================================
        $(document).on('input change', '.rb-customizer-control [rb-control-value]', function(){
            $panel = $(this).closest('.rb-form-control-single-field');
            //console.log($(this));
            if($panel.length != 0){
                fieldsController.updateCustomizer($panel, singleType.getValue($panel));
            }
        });

        // =============================================================================
        // GROUP VALUE UPDATE
        // =============================================================================
        $(document).on('input change', '.rb-form-control-field-group :not(.rb-form-control-repeater) [rb-control-value], .rb-form-control-field-group [rb-control-repeater-value]', function(){
            $panel = $(this).closest('.rb-form-control-field-group');
            //console.log($(this));
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
            //console.log('update single value');
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

        setTimeout(function(){
            $('.rb-form-control-repeater[data-title-link]').each(function(){
                var $panel = $(this).closest('.rb-form-control-repeater');
                repeaterType.updateControlsTitle($panel);
            });
        }, 0);


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
        $(document).on('click', '.rb-form-control-repeater > .controls > .rb-form-control .action-controls > .delete-button,  .rb-form-control-repeater > .controls > .rb-form-control > .rb-collapsible-header > .action-controls > .delete-button',
        function(event){
            //console.log(event);
            event.stopPropagation();
            var $panel = $(this).closest('.rb-form-control-repeater');
            var $fieldItem = $(this).closest('.rb-form-control');
            repeaterType.deleteField($panel, $fieldItem, function(){
                repeaterType.updateValue($panel);
            });
        });

        // =====================================================================
        // SORTING
        // =====================================================================

        //It doesnt works on customizer if not pushed out of the regular flow with timeout
        setTimeout(function(){
            $( ".rb-form-control-repeater .controls" ).sortable({
                revert: 100,
                refreshPositions: true,
                scroll: true,
                forcePlaceholderSize: true,
                //handle: ".rb-collapsible-header",
                update: function(ev, ui){
                    let $panel = ui.item.closest('.rb-form-control-repeater');
                    repeaterType.updateValue($panel);
                    repeaterType.updateControlsIds($panel);
                    repeaterType.updateControlsTitle($panel);
                },
                start: function( event, ui ) {
                    ui.placeholder.height(ui.helper.outerHeight());
                    // if( RBCollapsibleMaster.collapsibleIsOpen(ui.item) )
                    //     RBCollapsibleMaster.closeCollapsible(ui.item);
                },
                sort: function( event, ui ) {
                    //ui.placeholder.height(ui.helper.outerHeight());
                }
            });
        }, 0);
    });


})(jQuery);
