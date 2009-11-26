/*
 * @author Xeon Xai 2009.11.25
 */

document.observe('dom:loaded', function() {
    $$('.box_toggle').each(function(item) {
        Event.observe(item, 'click', function(event){
            Event.element(event).toggleClassName('close').toggleClassName('open').ancestors('.navtitle')[0].nextSiblings()[0].toggle();
        });
    });

    $$('.navtitle').each(function(item) {
        Event.observe(item, 'click', function(event){

            if (typeof(Event.element(event).descendants()[0]) != 'undefined') {
                Event.element(event).descendants()[0].toggleClassName('close').toggleClassName('open').ancestors('.navtitle')[0].nextSiblings()[0].toggle();
            }
        });
    });
});
