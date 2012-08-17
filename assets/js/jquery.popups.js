/**
 * Created with JetBrains PhpStorm.
 * User: antonio
 * Date: 8/13/12
 * Time: 9:38 PM
 * To change this template use File | Settings | File Templates.
 */
var simplePrompt = function(options) {
    var modal = jQuery('<div class="modal fade simplePrompt"></div>'),
        header = jQuery('<div class="modal-header"></div>'),
        body = jQuery('<div class="modal-body"></div>').css('overflow','hidden').appendTo(modal),
        foot = jQuery('<div class="modal-footer"></div>').appendTo(modal),
        keyup;

    if (options.headerHtml)
    {
        header.html(options.headerHtml).prependTo(modal);
    }
    if (options.width)
    {
        modal.css('width',options.width);
        var n = Number(/\d+/.exec(options.width));
        if (n) modal.css('margin-left', options.width.replace(n, -n/2));
    }
    if (options.height)
    {
        modal.css('height',options.height);
    }
    if (options.messageHtml) {
        body.html(options.messageHtml);
    }
    else {
        body.text(options.message).css('white-space', 'pre-line');
    }

    for (var i=0, len=options.buttons.length; i<len; i++) {
        var b = jQuery('<a class="btn" href="#"></a>').html(options.buttons[i].label).prependTo(foot);
        if (options.buttons[i].callback) b.click(options.buttons[i].callback);
        if (options.buttons[i].href) {
            b.attr('href', options.buttons[i].href).click(function() { window.location.href = this.href });
        } else {
            b.click(function() {
                modal.modal('hide');
                jQuery(document).unbind('keyup', keyup);
                return false;
            });
        }
        if (options.buttons[i]['default']) {
            var default_button = b.addClass('primary');
            jQuery(document).one('keyup', keyup = function(e) {
                if (e.which == 13) {
                    default_button.click();
                }
            });
        }
    }

    return modal.bind('shown', function() {
        var maxZ = 0;
        jQuery('.modal').each(function(i, el) {
            var z = jQuery(el).css('z-index');
            if (z > maxZ) maxZ = z;
        });
        jQuery(this).css('z-index', maxZ+2)
            .prev('.modal-backdrop').css('z-index', maxZ+1).unbind('click');
    }).bind('hidden', function() {
            jQuery(this).remove();
        }).modal({ backdrop: true, keyboard: false, show: true });
};

jQuery(function($){
    $('body').delegate('.load-in-modal', 'click', function() {
        var $modal = $('#modal');
        if(!$modal.length)
        {
            $modal = $('<div/>').prop('id','modal');
            $('body').append($modal);
        }
        $modal.hide();
        var w = $(this).data('modal-width');
        if (w) {
            $modal.css('width', w);
            var n = Number(/\d+/.exec(w));
            if (n) m.css('margin-left', w.replace(n, -n/2));
        } else $modal.css({ width: '', 'margin-left': '' });

        $modal.html(
            '<div class="modal-header"><a href="#" class="close">x</a><h3>Loading</h3></div>' +
            '<div class="modal-body"><img src="/images/loading.gif" /></div>')
            .load(this.href, function(){
                if($.browser.msie) {
                    $modal.find('[placeholder]').blur();
                }
            }).modal('show');
        return false;
    });
});