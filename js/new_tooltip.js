/**
 * Created with JetBrains PhpStorm.
 * User: Администратор
 * Date: 23.08.14
 * Time: 15:39
 * To change this template use File | Settings | File Templates.
 */
function new_tooltip()
{

    var targets = $( '[rel=tooltip]' ),
        target  = false,
        tooltip = false,
        title   = false;

    targets.bind( 'mouseenter', function()
    {

        target  = $( this );
        var tip     = target.attr( 'tooltip_source' );
        var l = $('.tooltip_sasha').length;

        if ( l )
            $('.tooltip_sasha').remove();

        tooltip = $( '<div class="tooltip_sasha"></div>' );

        if( !tip || tip == '' )
            return false;

        //target.removeAttr( 'tooltip_source' );
        tooltip.css( 'opacity', 0 )
            .html( $( '#' + tip ).html() )
            .appendTo( 'body' );

        var init_tooltip = function()
        {
            if( $( window ).width() < tooltip.outerWidth() * 1.5 )
                tooltip.css( 'max-width', $( window ).width() / 2 );
            else
                tooltip.css( 'max-width', 700 );

            var pos_left = target.offset().left + ( target.outerWidth() / 2 ) - ( tooltip.outerWidth() / 2 ),
                pos_top  = target.offset().top - tooltip.outerHeight() - 20;

            if( pos_left < 0 )
            {
                pos_left = target.offset().left + target.outerWidth() / 2 - 20;
                tooltip.addClass( 'left' );
            }
            else
                tooltip.removeClass( 'left' );

            if( pos_left + tooltip.outerWidth() > $( window ).width() )
            {
                pos_left = target.offset().left - tooltip.outerWidth() + target.outerWidth() / 2 + 20;
                tooltip.addClass( 'right' );
            }
            else
                tooltip.removeClass( 'right' );

            if( pos_top < 0 )
            {
                var pos_top  = target.offset().top + target.outerHeight();
                tooltip.addClass( 'top' );
            }
            else
                tooltip.removeClass( 'top' );

            tooltip.css( { left: pos_left, top: pos_top } )
                .animate( { top: '+=10', opacity: 1 }, 20 );
        };

        init_tooltip();
        $( window ).resize( init_tooltip );

        var remove_tooltip = function()
        {
            tooltip.animate( { top: '-=10', opacity: 0 }, 20, function()
            {
                $( this ).remove();
            });

            target.attr( 'tooltip_source', tip );
        };

        target.bind( 'mouseleave', remove_tooltip );
        tooltip.bind( 'click', remove_tooltip );
    });
}