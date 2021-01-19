(function () {
    /* get scroll width */
    var $outer = $('<div>').css({visibility: 'hidden', width: 100, overflow: 'scroll'}).appendTo('body'),
        widthWithScroll = $('<div>').css({width: '100%'}).appendTo($outer).outerWidth();
    $outer.remove();
    var scrollWidth = 100 - widthWithScroll;

    var last = null, current = null;

    if ($('html').hasClass('responsive-layout')) {
        /* add resize event */
        $(window).resize(function () {
            var width = $(window).width() + scrollWidth;
            if (width <= 760) {
                current = 'mobile';
            } else if (width <= 980) {
                current = 'tablet';
            } else {
                current = 'desktop';
            }
            if (last !== current) {
                last = current;
                switch (current) {
                case 'mobile':
                    Journal.onMobile();
                    break;
                case 'tablet':
                    Journal.onTablet();
                    break;
                case 'desktop':
                    Journal.onDesktop();
                    break;
                }
            }
        });
    } else {
        Journal.onDesktop();
    }
}());

$('.journal-header-compact .button-search').on('click', function(e){
    $('.journal-header-compact .journal-search').addClass('search-on-top');
    if (!window['__searchIsOpened']) {
        e.stopImmediatePropagation();
        $('.journal-header-compact .button-search button').css('pointer-events','auto');
        $('.journal-header-compact .journal-search input').show();
    }
    window['__searchIsOpened'] = !window['__searchIsOpened'];
});

$('*').click(function(e){
    if($(e.target).is($('.journal-search input'))){
        return;
    }
    $('.journal-header-compact .journal-search').removeClass('search-on-top');
    $('.journal-header-compact .journal-search input').hide();
    $('.journal-header-compact .button-search button').css('pointer-events','none');
    window['__searchIsOpened'] = false;
});

$('html[dir="ltr"] .journal-header-compact .mega-menu').css('left', -$('.journal-header-compact .journal-logo').width());
$('html[dir="rtl"] .journal-header-compact .mega-menu').css('right', -$('.journal-header-compact .journal-logo').width());

(function () {
    var t = null;
    $(window).resize(function () {
        $('html[dir="ltr"] .journal-header-compact .mega-menu').css('left', -$('.journal-header-compact .journal-logo').width());
        $('html[dir="rtl"] .journal-header-compact .mega-menu').css('right', -$('.journal-header-compact .journal-logo').width());
        clearTimeout(t);
        t = setTimeout(function () {
            $('.drop-down > ul a').unbind('mouseenter').mouseenter(function () {
                var $current = $(this).parent();
                var $dropdown = $(this).parent().parent().parent();
                var $next = $('>ul', $current);
                if ($next.length) {
                    if (!$current.hasClass('left') && $current.width() + $next.offset().left > $(window).width()) {
                        $current.addClass('left');
                        $('ul', $current).each(function() {
                            var $this = $(this);
                            $this.css('left', -$this.width());
                        });
                    }
                }
            });
            $('#cart').removeClass('active');
            $('.mobile-trigger').removeClass('menu-open');
            Journal.itemsEqualHeight();
        }, 300);
    });
}());

$(window).load(function() {
    $("img.lazy").lazy({
        bind: 'event',
        visibleOnly: false,
        effect: "fadeIn",
        effectTime: 250
    });

    $('#search input[name=\'search\']').parent().find('button').off('click').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var url = $('base').attr('href') + 'index.php?route=product/search';

        var value = $('header #search input[name=\'search\']').val();

        if (value) {
            url += '&search=' + encodeURIComponent(value);
        }

        if (Journal.searchInDescription) {
            url += '&description=true';
        }

        location = url;
    });
});

(function () {
    var $menu = $('.android.tablet .super-menu > li > a');
    if ($menu.length) {
        $menu.click(function (e) {
            $menu.not(this).removeClass("clicked");
            $(this).toggleClass("clicked");
            if ($(this).hasClass("clicked")) {
                event.preventDefault();
            }
        });
    }

    $('.mobile-menu a:not([href]), .mobile-menu a[href="javascript:;"]').live('click', function () {
        $(this).parent().find('> .mobile-plus').trigger('click');
    });

    if (Journal.isMobile) {
        $('.tablet:not(.mobile-menu-on-tablet) .super-menu li:has(ul), .tablet:not(.mobile-menu-on-tablet) .super-menu li:has(.mega-menu), .flyout li:has(ul), .flyout li:has(.mega-menu), .journal-header .links li:has(ul)').each(function () {
            var curItem = false;

            $(this).on('click', function (e) {
                var item = $(this);
                if (item[0] != curItem[0]) {
                    e.preventDefault();
                    curItem = item;
                }

                var $dd = $('+ ul.top-dropdown', item.children());

                if (!$dd.hasClass('dd-left') && !$dd.hasClass('dd-right')) {
                    if ($dd.offset().left + $dd.width() > $(window).width()) {
                        $dd.css('margin-left', -$dd.offset().left - $dd.width() + $(window).width());
                        $dd.addClass('dd-right');
                    } else {
                        $dd.removeClass('dd-right');
                    }

                    if ($dd.offset().left < 0) {
                        $dd.css('margin-left', -$dd.offset().left);
                        $dd.addClass('dd-left');
                    } else {
                        $dd.removeClass('dd-left');
                    }
                }
            });

            $(document).on('click touchstart MSPointerDown', function (e) {
                var resetItem = true,
                    parents = $(e.target).parents();

                for (var i = 0; i < parents.length; i++)
                    if (parents[i] == curItem[0])
                        resetItem = false;

                if (resetItem)
                    curItem = false;
            });
        });

        document.ontouchmove = function (e) {
            if ($('html').hasClass('has-popup')) {
                //e.preventDefault();
            }
        }
    }

    $('.modal').appendTo($('body'));
}());

// quick checkout trigger loading off
if ($('html').hasClass('quick-checkout-page')) {
	$(document).ajaxComplete(function (event, xhr, settings) {
		if (settings.url.indexOf('payment/') !== -1) {
			var json = xhr.responseJSON;

			try {
				if (json.error) {
					triggerLoadingOff();
				}
			} catch (e) {
				console.warn(e);
			}
		}
	});
}

console.log("hello from init js");