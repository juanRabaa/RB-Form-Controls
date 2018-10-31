 (function($){ 

    $(document).ready(function(){
        function collapsibleIsOpen($collapsible){
            return $collapsible.hasClass('active');
        }

        function animationStarts($collapsible){
            $collapsible.addClass('animating');
        }

        function animationOver($collapsible){
            $collapsible.removeClass('animating');
        }

        function closeCollapsible($collapsible){
            var $body = $collapsible.children('.rb-collapsible-body');

            animationStarts($collapsible)
            $body.stop().slideUp(200, function(){
                $collapsible.removeClass('active');
                $collapsible.removeClass('animating');
            });
        }

        function openCollapsible($collapsible){
            var $body = $collapsible.children('.rb-collapsible-body');

            $collapsible.addClass('opening');
            animationStarts($collapsible);
            $body.stop().slideDown({
                duration: 200,
                start: function () {
                    $(this).css('display', 'flex');
                },
                complete: function () {
                    animationOver($collapsible);
                    $collapsible.removeClass('opening');
                    $collapsible.addClass('active');
                    $body.height('auto');
                },
            });
        }

        function toggleCollapsible($collapsible){
            if( collapsibleIsOpen($collapsible) )
                closeCollapsible($collapsible);
            else
                openCollapsible($collapsible);
        }

        function activateAccordion($accordion, $collapsible){
            if( !$collapsible.hasClass('animating') ){
                var $siblings = $collapsible.siblings('.rb-collapsible');
                if( !$siblings.hasClass('animating')){
                    toggleCollapsible($collapsible);
                    $siblings.each(function(){
                        if( collapsibleIsOpen($(this)) )
                            closeCollapsible($(this));
                    });
                }
            }
        }

        $(document).on('click', '.rb-collapsible .rb-collapsible-header', function(){
            var $collapsible = $(this).closest('.rb-collapsible');
            var $accordion = $collapsible.parent('[data-rb-accordion]');
            if($accordion.length > 0)
                activateAccordion($accordion, $collapsible);
            else
                toggleCollapsible($collapsible);
        })
    });

})(jQuery);
