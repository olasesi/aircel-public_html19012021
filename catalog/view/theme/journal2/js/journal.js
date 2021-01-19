var Journal = {};

Journal.isMobile = $('html').hasClass('mobile') || $('html').hasClass('tablet');
Journal.isOC2 = $('html').hasClass('oc2');
Journal.isOC23 = $('html').hasClass('oc23');
Journal.isRTL = $('html').is('[dir="rtl"]');
Journal.isFlexboxSupported = true;
Journal.mobileMenuOnTablet = $('html').hasClass('mobile-menu-on-tablet');
Journal.notificationTimer = 1500;
Journal.quickviewText = 'Quickview';
Journal.BASE_HREF = null;
Journal.updatePrice = false;
Journal.scrollToTop = true;

Journal.init = function () {
    /* firefox dropdown menu width fix */
    $('.journal-menu .drop-down').each(function () {
        $('ul', this).css('min-width', $(this).width());
    });

    /* currency dropdown */
    var $c = $('.journal-currency .dropdown-menu');
    $c.css({
        'left': '50%',
        'margin-left': '-' + $c.width() / 2 + 'px'
    });
    $('.product-grid-item .image > a').prepend('<div class="p-over p-grid-over"> </div>');
    $('.product-list-item .image > a').prepend('<div class="p-over p-list-over"> </div>');

    /* */
    $('#top-modules .hide-on-mobile, #bottom-modules .hide-on-mobile').each(function () {
        $(this).parent().addClass('hide-on-mobile');
    });
};

Journal.setupMenu = function (type) {
    var $supermenu = $('.super-menu > li');
    var $mobiletrigger = $('.mobile-trigger');
    var $mobileplus = $('.mobile-menu > li .mobile-plus');
    var $megamenu_category_item = $('.mega-menu-categories .mega-menu-item li');

    try { $('.super-menu')[0].style.removeProperty('display'); } catch (e) { }

    /* unbind all events */
    $supermenu.unbind('mouseenter').unbind('mouseleave').removeProp('hoverIntent_t').removeProp('hoverIntent_s');
    $mobiletrigger.unbind('click');
    $mobileplus.unbind('click');
    $megamenu_category_item.unbind('hover');

    if (type === 'mobile') {
        jQuery._data($('.mobile-menu')[0], 'olddisplay', 'block');
        //$('.super-menu').css('display','none');
        /* setup mobile trigger */
        $mobiletrigger.toggle(function () {
            $('.mobile-menu').stop(true, true).slideDown(250);
            $(this).addClass('menu-open');
        }, function () {
            $('.mobile-menu').stop(true, true).slideUp(150);
            $(this).removeClass('menu-open');
        });

        /* setup mobile plus */
        $mobileplus.toggle(function () {
            $('> ul, > div', $(this).parent()).stop(true, true).slideDown(250);
            $(this).html('<svg height="18" viewBox="0 0 11 18" width="18" aria-label="chevron-previous" class="" name="chevron-previous"><path d="M9.74 14.612a1.496 1.496 0 0 1 0 2.16c-.602.58-1.566.569-2.134-.013L.662 10.073a1.508 1.508 0 0 1-.46-1.08c0-.41.163-.793.46-1.08l6.944-6.686a1.543 1.543 0 0 1 2.134 0 1.496 1.496 0 0 1 0 2.16L3.906 9.005l5.834 5.607zm-.725.728a.479.479 0 0 0-.021-.02l.02.02z" fill="#FFF" fill-rule="nonzero"></path></svg>').parent().addClass('menu-open');
        }, function () {
            $('> ul, > div', $(this).parent()).stop(true, true).slideUp(150);
            $(this).html('<svg height="18" viewBox="0 0 9 16" width="18" xmlns="http://www.w3.org/2000/svg" aria-label="chevron-next-outline" class="" name="chevron-next-outline"><path d="M.659.65a.532.532 0 0 0 0 .75l6.583 6.593-6.583 6.606a.532.532 0 0 0 0 .75.527.527 0 0 0 .747 0L8.349 8.38a.519.519 0 0 0 .155-.375.54.54 0 0 0-.155-.375L1.406.662A.516.516 0 0 0 .659.65z" fill="#fff" fill-rule="evenodd"></path></svg>').parent().removeClass('menu-open');
        
            
            
            
            
            
        });

    }

    /* set desktop events */
    if (type === 'tablet' || type === 'desktop') {
        jQuery._data($('.mobile-menu')[0], 'olddisplay', 'table');
        //$('.super-menu').css('display','table');
        $supermenu.hoverIntent(function () {
            var self = this;
            $('> ul, > div', this).hide();
            $('> ul, > div', this).stop(true, true).slideDown(250, function () {
                $('img.lazy', self).lazy({
                    bind: 'event',
                    visibleOnly: false,
                    effect: "fadeIn",
                    effectTime: 250
                });
            });
        }, function () {
            $('> ul, > div', this).stop(true, true).slideUp(150);
        });

        /* change image on hover subcategories */
        $megamenu_category_item.hover(function () {
            $(this).closest('.mega-menu-item').find('img').attr('src', $(this).attr('data-image'));
        }, function () {
            var $img = $(this).closest('.mega-menu-item').find('img');
            $img.attr('src', $img.attr('data-default-src'));
        });
    }
};

Journal.enableStickyHeader = function (padding) {
    var header = $('.header');
    Journal.stickyHeaderHeight = $('header').outerHeight();
    var lastScroll = 0;

    $('.header-notice').each(function() {
        Journal.stickyHeaderHeight += $(this).outerHeight();
    });

    header.css('top', '-' + (Journal.stickyHeaderHeight) + 'px');

    $(window).scroll(function() {
        var currentScrollPosition = $(window).scrollTop();

        if ($(window).scrollTop() > 1 * (Journal.stickyHeaderHeight) ) {

            $('body').addClass('is-sticky').css('padding-top', Journal.stickyHeaderHeight + padding);

            header.css('top', 0);

            if (currentScrollPosition < lastScroll) {
                header.css('top', '-' + (Journal.stickyHeaderHeight) + 'px');
                $('.tablet .is-sticky .mega-menu').hide();
            }
			if(Journal.hasStickyScroll) {
				lastScroll = currentScrollPosition;
			}

        } else {
            $('body').removeClass('is-sticky').css('padding-top', padding);
        }
    });
};

// Equal height rows solution - http://codepen.io/micahgodbolt/pen/FgqLc
Journal.equalHeight = function ($container, item, padding) {
    if (!$container.hasClass('isotope-element')) {
        console.warn('equal height on ', $container);
    }

    var currentTallest = 0,
        currentRowStart = 0,
        rowDivs = [],
        $el,
        currentDiv,
        topPosition = 0;
    padding = padding || 0;
    $container.each(function () {
        try {
            $el = item ? $(this).find(item) : $(this);
            $el.height('auto');
            topPosition = $el.position().top;
            if (currentRowStart !== topPosition) {
                for (currentDiv = 0; currentDiv < rowDivs.length; currentDiv++) {
                    rowDivs[currentDiv].height(currentTallest);
                }
                rowDivs = []; // empty the array
                currentRowStart = topPosition;
                currentTallest = $el.actual('height');
                rowDivs.push($el);
            } else {
                rowDivs.push($el);
                currentTallest = (currentTallest < $el.actual('height')) ? $el.actual('height') : currentTallest;
            }
            for (currentDiv = 0; currentDiv < rowDivs.length; currentDiv++) {
                rowDivs[currentDiv].height(currentTallest + padding);
            }
        } catch (e) { }
    });
};

Journal.itemsEqualHeight = function () {
    if (Journal.isFlexboxSupported) {
        return;
    }

    /* footer columns equal height */
    Journal.equalHeight($('footer .column'));

    /* menu items equal height */
    $('.mega-menu').addClass('dummy-hide');
    $('.mega-menu-categories').each(function () {
        Journal.equalHeight($(this).find('.mega-menu-item'));
    });
    $('.mega-menu-products').each(function () {
        Journal.equalHeight($(this).find('.mega-menu-item'), '.name');
    });
    $('.mega-menu').removeClass('dummy-hide');

    /* products */
    $('.product-grid, .box-product, .mega-menu-products').each(function () {
        Journal.equalHeight($(this).find('.product-grid-item'), '.name');
        Journal.equalHeight($(this).find('.product-grid-item'), '.cart');
        Journal.equalHeight($(this).find('.product-grid-item'), '.price');
    });

    /* refine category name */
    Journal.equalHeight($(".refine-images .refine-image"), '.refine-category-name');

    /* cms blocks */
    $('.box.cms-blocks').each(function () {
        var $this = $(this);
        Journal.equalHeight($this.find('.cms-block'), '.block-content');
    });

};

Journal.changeProductImage = function (thumb, image, index) {
    image = image || thumb;

    var $image = $('#image');

    $image.attr('src', image).attr('data-src-index', index);
    $image.parent().attr('href', image);

    if ($image.data('imagezoom')) {
        $image.attr('data-largeimg', image);
        $image.data('imagezoom').changeImage(image, image);
        $('.zm-viewer ').attr('data-src-index', index);
    }
};

Journal.enableCloudZoom = function (type) {
    var $image = $('#image');

    $image.ImageZoom({
        showDescription: false,
        bigImageSrc: $image.attr('data-largeimg'),
        type: type,
        offset: [0, 0],
        zoomSize: [$image.width(), $image.height()]
    });

    $(window).resize(function () {
        if (Journal.cloudZoomTimeout) {
            clearTimeout(Journal.cloudZoomTimeout);
        }

        Journal.cloudZoomTimeout = setTimeout(function () {
            if ($image.data('imagezoom')) {
                $image.data('imagezoom').changeZoomSize($image.width(), $image.height());
            }
        }, 100);
    });
};
// changed from click to hover
Journal.productPage = function () {
    var $a = $('.product-info .image-additional a');
    $a.hover(function (e) {
        e.preventDefault();
        var thumb = $(this).find('img').attr('src');
        var image = $(this).attr('href');
        Journal.changeProductImage(thumb, image, $a.index($(this)));
        return false;
    });
};

Journal.productPageGallery = function () {
    if (!$('html').hasClass('quickview')) {
        $('.product-info .image-gallery').lightGallery({
            download: false,
            actualSize: false,
            hideBarsDelay: Journal.galleryBarsDelay,
            zoom: Journal.galleryZoom,
            thumbnail: Journal.galleryThumb,
            showThumbByDefault: Journal.galleryThumbHide,
            thumbWidth: Journal.galleryThumbWidth,
            thumbContHeight: Journal.galleryThumbHeight,
            thumbMargin: Journal.galleryThumbSpacing
        });

        $('#lg-intense-zoom').live('click', function () {
            Intense($('.lg-item.lg-current img')[0], {invertInteractionDirection: 'ontouchstart' in window});
        });

        $('.zm-viewer').live('click', function () {
            $('.product-info .image-gallery a.swipebox').eq($('#image').attr('data-src-index') || 0).click();
            return false;
        });

        $('#image').parent().click(function () {
            $('.product-info .image-gallery a.swipebox').eq($('#image').attr('data-src-index') || 0).click();
            return false;
        });
        $('.gallery-text').click(function () {
            $('.product-info .image-gallery a.swipebox').first().click();
            return false;
        });
    }
};

Journal.updateProductPrice = function () {
    $.ajax({
        url: 'index.php?route=journal2/ajax/price',
        type: 'post',
        data: $('.product-info input[type=\'text\'], .product-info input[type=\'hidden\'], .product-info input[type=\'radio\']:checked, .product-info input[type=\'checkbox\']:checked, .product-info select, .product-info textarea'),
        dataType: 'json',
        success: function (json) {
            $('.product-info .price .price-old, .product-info .price .product-price').html(json.price);
            $('.product-info .price .price-new').html(json.special);
            $('.product-info .price .price-tax').html(json.tax);
            $('.description .journal-stock').removeClass('outofstock').removeClass('instock').addClass(json.cls).html(json.stock);
            $('.product-info .price .reward small').html(json.points);
            if (Journal.isOC2) {
                $('.product-info .discounts').each(function (index) {
                    $(this).html(json['discounts'][index]);
                });
            } else {
                var html = '';
                $.each(json['discounts'], function (index, discount) {
                    html += discount + '<br />';
                });
                $('.discount').html(html);
            }
        }
    });
};

Journal.enableProductOptions = function () {
    if (!$('html').hasClass('product-page')) {
        return;
    }

    /* change image for select options */
    $('.product-info .options:not(.push-select) .option select').change(function () {
        Journal.updateProductPrice();
    });

    /* change image for checkbox and radio options */
    $('.product-info .options:not(.push-radio) .option input[type="radio"]').click(function () {
        Journal.updateProductPrice();
    });

    $('.product-info .options:not(.push-checkbox) .option input[type="checkbox"]').click(function () {
        Journal.updateProductPrice();
    });
};

Journal.showNotification = function (message, image, buttons) {
    $.pnotify.defaults.history = false;
    var $temp = $('<div>' + message + '</div>');
    var href = $temp.find('a').first().attr('href');
    var $img = image ? '<a href="' + href + '"><img src="' + image + '" alt="" /></a>' : '';
    var $title = $temp.find('a').last().prev();
    var timeout = Journal.notificationTimer;
    var $$ = $;
    $$.pnotify({
        title: $title.html(),
        delay: parseInt(timeout, 10),
        text: $img + message + (buttons ? Journal.NOTIFICATION_BUTTONS : ''),
        type: 'success',
        history: false
    });

    $('.ui-pnotify-text a').die('click touchend').live('click touchend', function () {
        var el = $(this);
        var link = el.attr('href');
        location = link;
    });

    return true;
};

Journal.enableQuickView = function () {
    $('.quickview-button').remove();
    $('.product-wrapper .image, .product-list-item .image').each(function () {
        var $quickview = $('<div class="quickview-button"><a class="button hint--top" data-hint="' + Journal.quickviewText + '"><i class="button-left-icon"></i><span class="button-cart-text">' + Journal.quickviewText + '</span><i class="button-right-icon"></i></a></div>');
        $(this).prepend($quickview);
        var $quickbtn = $quickview.find('a');
        var $parent = $(this).closest('.product-list-item');
        if ($parent.length === 0) {
            $parent = $(this).closest('.product-wrapper');
        }
        var $cart_btn = $parent.find('.cart .button');
        var productId = $cart_btn.attr('onclick') || $cart_btn.attr('data-clk');
        if (productId) {
			productId = productId.match(/\d+/);
			if (productId && productId[0]) {
				productId = productId[0];
				productId = productId.replace("'", "");
                productId = parseInt(productId, 10);
                $quickbtn.attr('href', 'index.php?route=journal2/quickview&pid=' + productId);
                $quickbtn.magnificPopup({
                    preloader: true,
                    tLoading: '',
                    type: 'iframe',
                    mainClass: 'quickview',
                    removalDelay: 200,
                    callbacks: {
                        open: function(item) {
                            $('html').addClass('noscroll');
                        },
                        close: function(item) {
                            $('html').removeClass('noscroll');
                        }
                    }
                });
            }
        }
    });
};

Journal.openPopup = function (module_id, product_id) {
    product_id = product_id || undefined;
    $.magnificPopup.open({
        items: {
            src: 'index.php?route=module/journal2_popup/show&module_id=' + module_id + (product_id ? '&product_id=' + product_id : '')
        },
        closeOnContentClick: false,
        closeOnBgClick: false,
        mainClass: 'quickview',
        type: 'ajax',
        removalDelay: 200,
        callbacks: {
            beforeClose: function () {
                $('.mfp-content').addClass('popup-close');
            },
            open: function(item) {
                $('html').addClass('has-popup');
            },
            close: function(item) {
                $('html').removeClass('has-popup');
            },
            parseAjax: function(response) {
                try {
                    if (response.data.indexOf('class="g-recaptcha"')) {
                        $('head').append('<script src="https://www.google.com/recaptcha/api.js"></script>');
                    }
                    eval($('<div>' + response.data + '</div>').find('script[type="text/html"]').text());
                } catch (e) { }
            }
        }
    }, 0);
};

Journal.searchAutoComplete = function () {
    $('#search input').autocomplete2({
        appendTo: '.journal-search > div',
        width: '100%',
        serviceUrl: 'index.php?route=journal2/search',
        deferRequestBy: 500,
        paramName: 'search',
        onSelect: function (suggestion) {
            if (suggestion.url) {
                location = suggestion.url;
            } else {
                return false;
            }
        },
        transformResult: function (response) {
            response = $.parseJSON(response);
            var suggestions = $.map(response.results, function (dataItem) {
                return { value: dataItem.name, data: dataItem, image: dataItem.image, price: dataItem.price, special: dataItem.special, url: dataItem.url,  };
            });
            if (response['categories']) {
                if (response['categories'].length > 3) {
                    loname0 = response['categories'][0]['name'].toLowerCase().replace(/\s+/g, '-');
                    loname1 = response['categories'][1]['name'].toLowerCase().replace(/\s+/g, '-');
                    loname2 = response['categories'][2]['name'].toLowerCase().replace(/\s+/g, '-');
                    loname3 = response['categories'][3]['name'].toLowerCase().replace(/\s+/g, '-');
                    
                    suggestions.unshift({
                        category: true,
                        id: response['categories'][0]['category_id'],
                        name: response['categories'][0]['name'],
                        image: response['categories'][0]['image'],
                        lowname: loname0,
                        url: response['view_more_url'],
                        total: response['categories'][0]['total']
                    }, {
                        category: true,
                        id: response['categories'][1]['category_id'],
                        name: response['categories'][1]['name'],
                        image: response['categories'][1]['image'],
                        lowname: loname1,
                        total: response['categories'][1]['total'],
                        url: response['view_more_url']
                    }, {
                        category: true,
                        id: response['categories'][2]['category_id'],
                        name: response['categories'][2]['name'],
                        image: response['categories'][2]['image'],
                        lowname: loname2,
                        total: response['categories'][2]['total'],
                        url: response['view_more_url']
                    }, {
                        category: true,
                        id: response['categories'][3]['category_id'],
                        name: response['categories'][3]['name'],
                        image: response['categories'][3]['image'],
                        lowname: loname3,
                        total: response['categories'][3]['total'],
                        url: response['view_more_url']
                    })
                
                } else {
                  try{    
                    loname0 = response['categories'][0]['name'].toLowerCase().replace(/\s+/g, '-');
                    loname1 = response['categories'][1]['name'].toLowerCase().replace(/\s+/g, '-');
                    loname2 = response['categories'][2]['name'].toLowerCase().replace(/\s+/g, '-');
                    
                    suggestions.unshift({
                        category: true,
                        id: response['categories'][0]['category_id'],
                        name: response['categories'][0]['name'],
                        image: response['categories'][0]['image'],
                        lowname: loname0,
                        url: response['view_more_url'],
                        total: response['categories'][0]['total']
                    }, {
                        category: true,
                        id: response['categories'][1]['category_id'],
                        name: response['categories'][1]['name'],
                        image: response['categories'][1]['image'],
                        lowname: loname1,
                        total: response['categories'][1]['total'],
                        url: response['view_more_url']
                    }, {
                        category: true,
                        id: response['categories'][2]['category_id'],
                        name: response['categories'][2]['name'],
                        image: response['categories'][2]['image'],
                        lowname: loname2,
                        total: response['categories'][2]['total'],
                        url: response['view_more_url']
                    })
                  }catch(ex){}
                }
                
                
            }
            
            if (response['view_more_url']) {
                suggestions.push({
                    view_more: true,
                    value: response['view_more_text'],
                    url: response['view_more_url']
                });
            } else {
                suggestions.push({
                    view_more: true,
                    value: response['view_more_text'],
                    url: response['view_more_url']
                });
            }
            return {
                suggestions: suggestions
            };
        },
        formatResult: function (suggestion, currentValue) {
                var entry = $('#search input').val();
            // var sugg =  '<a href="https://www.obejor.com.ng/index.php?route=product/search&search='+ entry +'&description=true#/'+suggestion["lowname"]+'c'+suggestion["id"]+'/sort=p.sort_order/order=ASC/limit=35/minPrice=1/maxPrice=9999999'+'">';
            var sugg =  '<a href="https://www.obejor.com.ng/index.php?route=product/search&search='+ entry +'&description=true#/'+suggestion["lowname"]+'c'+suggestion["id"]+'">';
            if (suggestion['category']) {
                // returning category image to the result
                // if (suggestion["image"]) {
                //     sugg += '<span class="p-image xs-33 sm-33 md-33 lg-33 xl-33"><img src="https://www.obejor.com.ng/image/' + suggestion["image"] + '" /></span>';
                // }
                //  sugg +=  '<h5>' +  entry  + ' in ' + suggestion["name"] + '   (' +suggestion["total"] +' results' + ')' + ' </h5></a>';
                 sugg +=  '<div class="name"><p class="q">' + entry +'</p><span class="in">'+ 'in ' + '</span>'+ suggestion["name"] +'</div><p class="res">'+ '(' + suggestion["total"] +' results' + ')'  +'</p>';
                return sugg;
            }
            
            if (suggestion['view_more']) {
                if (suggestion['url']) {
                    return '<a class="view-more-link" href="' + suggestion['url'] + '">' + suggestion['value'] + '</a>';
                } else {
                    return '<a class="no-results">' + suggestion['value'] + '</a>';
                }

            } else {
//                var reEscape = new RegExp('(\\' + ['/', '.', '*', '+', '?', '|', '(', ')', '[', ']', '{', '}', '\\'].join('|\\') + ')', 'g'),
//                    pattern = '(' + currentValue.replace(reEscape, '\\$1') + ')',
//                    name = suggestion.value.replace(new RegExp(pattern, 'gi'), '<strong>$1<\/strong>');
                var name = suggestion.value;
                var html = '<a href="' + suggestion.url + '">';
                if (suggestion.image) {
                    html += '<span class="p-image xs-33 sm-33 md-33 lg-33 xl-33"><img src="' + suggestion.image + '" /></span>';
                }
                html += '<span class="p-name xs-66 sm-66 md-66 lg-66 xl-66"><span>' + name + '</span>';
                if (suggestion.price) {
                    if (suggestion.special) {
                        html += '<span class="p-price xs-66 sm-66 md-66 lg-66 xl-66"><span class="price-old">' + suggestion.price + '</span><span class="price-new">' + suggestion.special + '</span></span>';
                    } else {
                        html += '<span class="p-price xs-66 sm-66 md-66 lg-66 xl-66">' + suggestion.price + '</span>';
                    }
                }
                html += '</span>';
                html += '<div class="clearfix"> </div>';
                html += '</a>';
                return html;
            }
        }
    });
};

Journal.onMobileOrTablet = function () {
    /* add click events on language and currency */
    $('#currency .btn-group, #language .btn-group').unbind('click').unbind('hoverIntent').click(function () {
        $('#currency .btn-group, #language .btn-group').not($(this)).removeClass('visible').find('ul').fadeOut(150);
        $(this).toggleClass('visible');
        if ($(this).hasClass('visible')) {
            $(this).find('ul').fadeIn(150);
        } else {
            $(this).find('ul').fadeOut(150);
        }
    });

    /* ajax cart */
    $('#cart').die('mouseleave').die('mouseover').die('mouseleave').die('click');
    $('#cart > .heading a').die('mouseleave').die('mouseover').die('mouseleave').die('click');
    $('#cart > .heading a').live('click', function () {
        if (!$("#cart").hasClass('active')) {
            if (!Journal.isOC2) {
                $('#cart').load('index.php?route=module/cart #cart > *');
            }
            $('#cart').addClass('active');
        } else {
            $('#cart').removeClass('active');
        }
    });
};

Journal.onMobile = function () {
    Journal.setupMenu('mobile');
    Journal.onMobileOrTablet();

    /* add journal-mobile class to html */
    $('html').addClass('journal-mobile').removeClass('journal-desktop');

    Journal.itemsEqualHeight();

    $('.collapse-footer-columns .column > h3').on('click', function () {
        var $this = $(this);

        if ($this.hasClass('column-open')) {
            $this.next('div').slideUp(200);
            $this.removeClass('column-open');
        } else {
            $this.next('div').slideDown(200);
            $this.addClass('column-open');
        }
    });
};

Journal.onTablet = function () {
    Journal.setupMenu(Journal.mobileMenuOnTablet ? 'mobile' : 'tablet');
    Journal.onMobileOrTablet();

    /* add journal-desktop class to html*/
    $('html').removeClass('journal-mobile').addClass('journal-desktop');

    Journal.itemsEqualHeight();
};

Journal.onDesktop = function () {
    Journal.setupMenu('desktop');
    /* add journal-desktop class to html*/
    $('html').removeClass('journal-mobile').addClass('journal-desktop');

    /* hover on currency */
    $('#currency .btn-group, #language .btn-group').unbind('click').unbind('hoverIntent').hoverIntent(function () {
        $(this).find('ul').fadeIn(150);
    },  function () {
        $(this).find('ul').fadeOut(150);
    });

    /* ajax cart event*/
    $('#cart > .heading a').die('mouseleave').die('mouseover').die('mouseleave').die('click');
    $('#cart').die('mouseleave').die('mouseover').die('mouseleave').die('click');
    $('#cart').live('mouseover', function () {
        if (!$("#cart").hasClass('active')) {
            if (!Journal.isOC2) {
                $('#cart').load('index.php?route=module/cart #cart > *');
            }
            $('#cart').addClass('active');
            $('#cart').live('mouseleave', function () {
                $(this).removeClass('active');
            });
        }
    });

    Journal.itemsEqualHeight();

    $('.collapse-footer-columns .column > h3').off('click');
};

Journal.enableSideBlocks = function () {
    $('.side-block-block.side-block-left .side-block-icon').hoverIntent(function () {
        var $parent = $(this).parent();
        var $content = $parent.find('.side-block-content');
        if (!$content.hasClass('content-loaded')) {
            $content.load($content.attr('data-url')).addClass('content-loaded');
        }
        $parent.stop().animate({'left' : '0px'});
    });

    $('.side-block-block.side-block-left .side-block-content').mouseleave(function () {
        var x = $(this).parent().width();
        $(this).parent().stop().animate({'left': -x});
    });

    $('.side-block-block.side-block-right .side-block-icon').hoverIntent(function () {
        var $parent = $(this).parent();
        var $content = $parent.find('.side-block-content');
        if (!$content.hasClass('content-loaded')) {
            $content.load($content.attr('data-url')).addClass('content-loaded');
        }
        $parent.stop().animate({'right' : '0px'});
    });

    $('.side-block-block.side-block-right .side-block-content').mouseleave(function () {
        var y = $(this).parent().width();
        $(this).parent().stop().animate({'right' : -y});
    });
};

Journal.enableSelectOptionAsButtonsList = function () {
    $('.push-select .option.option-select, .push-checkbox .option.option-checkbox, .push-radio .option.option-radio, .push-image .option.option-image').each(function () {
        var $option = $(this);
        var html = '';
        html += '<ul>';
        if ($option.hasClass('option-checkbox') || $option.hasClass('option-radio')) {
            $option.find('input').each(function () {
                var $this = $(this);
                if ($this.val()) {
                    var $label = Journal.isOC2 ? $this.closest('label') : $('label[for="' + $this.attr('id') + '"]');
                    if (Journal.isOC23 && $option.parent().hasClass('push-image')) {
                        var $img = $this.parent().find('img');
                        if ($img.length) {
                            $option.addClass('option-image');
                            html += '<li class="hint--top" data-hint="' + $img.attr('alt') + '" data-value="' + $this.val() + '"><span>' + ($img.clone().wrap('<p />').parent()).html() + '</span></li>';
                        } else {
                            html += '<li data-value="' + $this.val() + '"><span>' + $label.text().trim() + '</span></li>';
                        }
                    } else {
                        html += '<li data-value="' + $this.val() + '"><span>' + $label.text().trim() + '</span></li>';
                    }
                }
            });
        } else if ($option.hasClass('option-select')) {
            $option.find('option').each(function () {
                var $this = $(this);
                if ($this.val()) {
                    html += '<li data-value="' + $this.val() + '"><span>' + $this.text().trim() + '</span></li>';
                }
            });
        } else if ($option.hasClass('option-image')) {
            $option.find(Journal.isOC2 ? '.radio' : 'tr').each(function () {
                var $this = $(this);
                var $input = $this.find('input');
                var $img = Journal.isOC2 ? ($this.find('img').clone().wrap('<p />').parent()) : $this.find('label');
                html += '<li class="hint--top" data-hint="' + $this.find('img').attr('alt') + '" data-value="' + $input.val() + '"><span>' + $img.html() + '</span></li>';
            });
        }
        html += '</ul>';
        $option.append($(html));
    });

    $('.option-select li, .option-checkbox li, .option-radio li, .option-image li').click(function () {
        var $this = $(this);
        var $option = $this.closest('.option');

        /* trigger change on corresponding option */
        var $input = $option.find('[value="' + $this.attr('data-value') + '"]');

        if ($option.hasClass('option-select')) {
            var $select = $input.parent();
            var val = $select.val() == $this.attr('data-value') ? '' : $this.attr('data-value');
            $select.val(val);
            $select.trigger('change');
        } else {
            $input.trigger('click');
        }

        /* add class to selected options */
        if ($option.hasClass('option-select')) {
            $option.find('[data-value]').removeClass('selected');
            $option.find('[data-value="' + $option.find('select').val() + '"]').addClass('selected');
        } else {
            $option.find('input').each(function() {
                var $el = $(this);
                var val = $(this).val();
                if ($el.is(':checked')) {
                    $option.find('[data-value="' + val + '"]').addClass('selected');
                } else {
                    $option.find('[data-value="' + val + '"]').removeClass('selected');
                }
            });
        }

        if (Journal.updatePrice) {
            Journal.updateProductPrice();
        }
    });
};

Journal.newsletter = function ($module) {
    $module.find('.newsletter-email').attr('disabled', true);
    $.post('index.php?route=account/newsletter/subscribe', { email: $module.find('.newsletter-email').val()},function(response){},'json');
    $.post('index.php?route=module/journal2_newsletter/subscribe', { email: $module.find('.newsletter-email').val(), agree: $module.find('input[type="checkbox"]:checked').val() }, function (response) {
        $module.find('.newsletter-email').removeAttr('disabled');
        if (response.status === 'error' && response.unsubscribe) {
            if (confirm(response.message)) {
                $.post('index.php?route=module/journal2_newsletter/unsubscribe', { email: $module.find('.newsletter-email').val() }, function (response) {
                    alert(response.message);
                    $module.find('.newsletter-email').val('');
                }, 'json');
            }
        } else {
            alert(response.message);
            $module.find('.newsletter-email').val('');
            $('.mfp-container .journal-popup').length && $.magnificPopup.close();
        }
        $module.find('.newsletter-email').focus();
    }, 'json');
};

Journal.contact = function ($module) {
    var $button = $module.find('.journal-popup-footer .button');
    if ($button.hasClass('disabled')) return false;
    $button.addClass('disabled');
    var $form = $module.find('form');
    var data = $form.serializeArray();
    data.push({
        name: 'url',
        value: document.URL
    });
    $form.find('img').css('opacity', '0.2');
    $.post('index.php?route=module/journal2_popup/contact', data, function (response) {
        $button.removeClass('disabled');
        if (response.status === 'error') {
            $form.find('input[name="captcha"]').val('');
            var src = $form.find('img').attr('src');
            $form.find('img').attr('src', src).css('opacity', '1');
            $form.find('.has-error').removeClass('has-error');
            $.each(response.error, function (key) {
                if (key === 'g-recaptcha') {
                    $form.find('.g-recaptcha').addClass('has-error');
                } else {
                    $form.find('[name="' + key +'"]').addClass('has-error');
                }
            });
        }
        if (response.status === 'success') {
            alert(response.message);
            $form[0].reset();
            $.magnificPopup.close();
        }
    }, 'json');
    return false;
};

if (Journal.isOC2) {
    cart.add = function(product_id, quantity) {
        if ($('.hide-cart .outofstock a[onclick="cart.add(\'' + product_id + '\');"]').length) {
            return false;
        }

        $.ajax({
            url: 'index.php?route=checkout/cart/add',
            type: 'post',
            data: 'product_id=' + product_id + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
            dataType: 'json',
            beforeSend: function() {
                $('#cart > button > a > span').button('loading');
            },
            complete: function() {
                $('#cart > button > a > span').button('reset');
            },
            success: function(json) {
                if (json['redirect']) {
                    location = json['redirect'];
                }

                if (json['success']) {
                    if (!Journal.showNotification(json['success'], json['image'], true)) {
                        $('.alert, .text-danger').remove();

                        $('#content').parent().before('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + json['success'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                    }

                    setTimeout(function () {
                        $('#cart-total').html(json['total']);
                    }, 100);

                    if (Journal.scrollToTop) {
                        $('html, body').animate({ scrollTop: 0 }, 'slow');
                    }

                    $('#cart ul').load('index.php?route=common/cart/info ul li');
                }
            }
        });
    };

    cart.update = function(key, quantity) {
        $.ajax({
            url: 'index.php?route=checkout/cart/edit',
            type: 'post',
            data: 'key=' + key + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
            dataType: 'json',
            beforeSend: function() {
                $('#cart > button > a > span').button('loading');
            },
            complete: function() {
                $('#cart > button > a > span').button('reset');
            },
            success: function(json) {
                setTimeout(function () {
                    $('#cart-total').html(json['total']);
                }, 100);

                if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
                    location = 'index.php?route=checkout/cart';
                } else {
                    $('#cart ul').load('index.php?route=common/cart/info ul li');
                }
            }
        });
    };

    cart.remove = function(key) {
        $.ajax({
            url: 'index.php?route=checkout/cart/remove',
            type: 'post',
            data: 'key=' + key,
            dataType: 'json',
            beforeSend: function() {
                $('#cart > button > a > span').button('loading');
            },
            complete: function() {
                $('#cart > button > a > span').button('reset');
            },
            success: function(json) {
                setTimeout(function () {
                    $('#cart-total').html(json['total']);
                }, 100);

                if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
                    location = 'index.php?route=checkout/cart';
                } else {
                    $('#cart ul').load('index.php?route=common/cart/info ul li');
                }
            }
        });
    };

    voucher.remove = function(key) {
        $.ajax({
            url: 'index.php?route=checkout/cart/remove',
            type: 'post',
            data: 'key=' + key,
            dataType: 'json',
            beforeSend: function() {
                $('#cart > button > a > span').button('loading');
            },
            complete: function() {
                $('#cart > button > a > span').button('reset');
            },
            success: function(json) {
                // Need to set timeout otherwise it wont update the total
                setTimeout(function () {
                    $('#cart-total').html(json['total']);
                }, 100);

                if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
                    location = 'index.php?route=checkout/cart';
                } else {
                    $('#cart ul').load('index.php?route=common/cart/info ul li');
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }
}

function addToCart(product_id, quantity) {
    if ($('.hide-cart .outofstock a[onclick="addToCart(\'' + product_id + '\');"]').length) {
        return false;
    }

    quantity = parseInt(quantity, 10) || 1;

    if (Journal.isOC2) {
        return cart.add(product_id, quantity);
    }

    $.ajax({
        url: 'index.php?route=checkout/cart/add',
        type: 'post',
        data: 'product_id=' + product_id + '&quantity=' + quantity,
        dataType: 'json',
        success: function(json) {
            $('.success, .warning, .attention, .information, .error').remove();

            if (json['redirect']) {
                location = json['redirect'];
            }

            if (json['success']) {
                if (!Journal.showNotification(json['success'], json['image'], true)) {
                    $('#notification').html('<div class="success" style="display: none;">' + json['success'] + '<img src="catalog/view/theme/default/image/close.png" alt="" class="close" /></div>');
                }

                $('.success').fadeIn('slow');

                if (Journal.scrollToTop) {
                    $('html, body').animate({ scrollTop: 0 }, 'slow');
                }

                $('#cart-total').html(json['total']);
            }
        }
    });
}

if (Journal.isOC2) {
    wishlist.add = function(product_id) {
        $.ajax({
            url: 'index.php?route=account/wishlist/add',
            type: 'post',
            data: 'product_id=' + product_id,
            dataType: 'json',
            success: function(json) {
                if (json['success']) {
                    if (!Journal.showNotification(json['success'], json['image'])) {
                        $('.alert').remove();

                        $('#content').parent().before('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + json['success'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                    }
                }

                if (json['info']) {
                    if (!Journal.showNotification(json['info'], json['image'])) {
                        $('#content').parent().before('<div class="alert alert-info"><i class="fa fa-info-circle"></i> ' + json['info'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                    }
                }

                try {
                    $('.wishlist-total .product-count').html(json['total'].match(/\(\d+\)/g)[0].replace('(', '').replace(')', ''));
                } catch (e) { }

                if (Journal.scrollToTop) {
                    $('html, body').animate({ scrollTop: 0 }, 'slow');
                }
            }
        });
    }
}

function addToWishList(product_id) {
    if (Journal.isOC2) {
        return wishlist.add(product_id);
    }

    $.ajax({
        url: 'index.php?route=account/wishlist/add',
        type: 'post',
        data: 'product_id=' + product_id,
        dataType: 'json',
        success: function(json) {
            $('.success, .warning, .attention, .information').remove();

            if (json['success']) {
                if (!Journal.showNotification(json['success'], json['image'])) {
                    $('#notification').html('<div class="success" style="display: none;">' + json['success'] + '<img src="catalog/view/theme/default/image/close.png" alt="" class="close" /></div>');
                }
                $('.success').fadeIn('slow');

                try {
                    $('.wishlist-total .product-count').html(json['total'].match(/\(\d+\)/g)[0].replace('(', '').replace(')', ''));
                } catch (e) { }

                if (Journal.scrollToTop) {
                    $('html, body').animate({ scrollTop: 0 }, 'slow');
                }
            }
        }
    });
}

if (Journal.isOC2) {
    compare.add = function(product_id) {
        $.ajax({
            url: 'index.php?route=product/compare/add',
            type: 'post',
            data: 'product_id=' + product_id,
            dataType: 'json',
            success: function(json) {
                if (json['success']) {
                    if (!Journal.showNotification(json['success'], json['image'])) {
                        $('.alert').remove();

                        $('#content').parent().before('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + json['success'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                    }

                    $('#compare-total').html(json['total']);
                    $('.compare-total .product-count').html(json['total'].match(/\(\d+\)/g)[0].replace('(', '').replace(')', ''));

                    if (Journal.scrollToTop) {
                        $('html, body').animate({ scrollTop: 0 }, 'slow');
                    }
                }
            }
        });
    }
}

function addToCompare(product_id) {
    if (Journal.isOC2) {
        return compare.add(product_id);
    }

    $.ajax({
        url: 'index.php?route=product/compare/add',
        type: 'post',
        data: 'product_id=' + product_id,
        dataType: 'json',
        success: function(json) {
            $('.success, .warning, .attention, .information').remove();

            if (json['success']) {
                if (!Journal.showNotification(json['success'], json['image'])) {
                    $('#notification').html('<div class="success" style="display: none;">' + json['success'] + '<img src="catalog/view/theme/default/image/close.png" alt="" class="close" /></div>');
                }
                $('.success').fadeIn('slow');

                try {
                    $('#compare-total').html(json['total']);
                    $('.compare-total .product-count').html(json['total'].match(/\(\d+\)/g)[0].replace('(', '').replace(')', ''));
                } catch (e) { }

                if (Journal.scrollToTop) {
                    $('html, body').animate({ scrollTop: 0 }, 'slow');
                }
            }
        }
    });
}

Journal.SuperFilter = {};

Journal.SuperFilter.firstLoad = true;

Journal.SuperFilter.init = function ($parent) {
    this.$parent = $parent;

    /* collapse */
    $('.filter-collapse .box-heading').live('click', function () {
        $(this).closest('.box').toggleClass('is-collapsed');
    });
    // $('.filter-collapse-open .journal-sf .box-heading').toggle(function(){
    //     $(this).next('div').slideUp(200);
    //     $(this).addClass('section-closed');
    // },function(){
    //     $(this).next('div').slideDown(200);
    //     $(this).removeClass('section-closed');
    // });
    //
    // $('.filter-collapse-closed .journal-sf .box-heading').toggle(function(){
    //     $(this).next('div').slideDown(200);
    //     $(this).addClass('section-open');
    // },function(){
    //     $(this).next('div').slideUp(200);
    //     $(this).removeClass('section-open');
    // });
    //
    // $('.filter-collapse-closed-mobile .journal-sf .box-heading').toggle(function(){
    //     $(this).next('div').slideDown(200);
    //     $(this).addClass('section-open');
    // },function(){
    //     $(this).next('div').slideUp(200);
    //     $(this).removeClass('section-open');
    // });

    /* reset button event */
    $parent.find('.sf-reset').live('click', function () {
        Journal.SuperFilter.reset($parent);
    });

    /* checkbox events */
    $parent.find('input[type="checkbox"]').live('click', function () {
        var $box = $(this).closest('.box');
        if ($box.hasClass('sf-single')) {
            $box.find('input[type="checkbox"]').not($(this)).removeAttr('checked');
        }
        setTimeout(function () { Journal.SuperFilter.filter($parent); }, 1);
    });

    /* address change event*/
    $.address.change(function (e) {
        if (!Journal.SuperFilter.firstLoad || (Journal.SuperFilter.firstLoad && ($(location).attr('hash').replace('#/', '').replace('#', '')))) {
            Journal.SuperFilter.doFilter($parent, e.value);
        }
        Journal.SuperFilter.firstLoad = false;
    });

    $(function () {
        /* sort order and limit */
        $('.sort select, .limit select').removeAttr('onchange').live('change', function (e) {
            Journal.SuperFilter.filter($parent);
            return false;
        });

        /* pagination change */
        $('.pagination').removeClass('hide');
        $('.pagination .links a').live('click', function (e) {
            $parent.find('.sf-page').val($(this).attr('href').split('page=')[1]);
            Journal.SuperFilter.filter($parent);
            $('html, body').animate({scrollTop: 0}, 700);
            return false;
        });
    });

    /* render initial price slider */
    Journal.SuperFilter.priceSlider($parent);
};

Journal.SuperFilter.reset = function ($parent) {
    var location = window.location.href;
    location = location.split("#");
    window.location.href = location[0];
};

Journal.SuperFilter.filter = function ($parent, ret) {
    var filters = {};
    /* get selected filters */
    $parent.find('.box').not('.sf-tags').not('.sf-availability').find('input:checked').each(function () {
        var name = $(this).attr('name');
        filters[name] = filters[name] || {
                name: name,
                group: name.replace(/\D/g, ''),
                filters: []
            };
        filters[name].filters.push({
            keyword: $(this).attr('data-keyword'),
            value: $(this).val()
        });
    });

    /* build url */
    var url_parts = [];
    for (var i in filters) {
        var keywords = [];
        var values = [];
        var type = filters[i].name[0];
        var group = filters[i].group;
        for (var j in filters[i].filters) {
			if (typeof filters[i].filters[j] === 'function') continue;
            keywords.push(filters[i].filters[j].keyword);
            values.push(filters[i].filters[j].value);
        }
        url_parts.push(keywords.join(',') + '-' + (group ? '' + type + group + '-v' : type) + values.join(','));
    }

    /* add tags */
    var tags = [];
    $parent.find('.sf-tags').find('input:checked').each(function () {
        tags.push($(this).val());
    });
    if (tags.length) {
        url_parts.push(tags.join(',') + '-tags');
    };

    /* add availability */
    var availability = [];
    $parent.find('.sf-availability').find('input:checked').each(function () {
        availability.push($(this).val());
    });
    if (availability.length) {
        url_parts.push('availability=' + availability.join(','));
    };

    /* add sort order */
    if ($('.sort').length > 0) {
        var value = $('.sort select option:selected').val().split('sort=')[1].split('&');
        url_parts.push('sort=' + value[0]);
        url_parts.push('order=' + value[1].replace('order=', ''));
    }

    /* add limit */
    if ($('.limit').length > 0) {
        url_parts.push('limit=' + $('.limit select option:selected').text());
    }

    /* add price */
    if ($parent.find('.sf-price').length > 0) {
        if ($parent.find('.sf-price').hasClass('sf-input')) {
            url_parts.push('minPrice=' + $parent.find('.min-price').val());
            url_parts.push('maxPrice=' + $parent.find('.max-price').val());
        } else {
            var $price_slider = $parent.find('.sf-price');
            var minPrice = $parent.find('.sf-price .slider').attr('data-min') || $parent.find('.sf-price .slider').attr('data-min-value');
            var maxPrice = $parent.find('.sf-price .slider').attr('data-max') || $parent.find('.sf-price .slider').attr('data-max-value');
            if(minPrice && maxPrice && (minPrice != $price_slider.attr('data-min-price') || maxPrice != $price_slider.attr('data-max-price'))){
                url_parts.push('minPrice=' + minPrice);
                url_parts.push('maxPrice=' + maxPrice);
            }
        }
    }

    /* add pagination */
    var page = $parent.find('.sf-page').val();
    if (page) {
        url_parts.push('page=' + page);
    }

    if (ret) {
        return url_parts.join('/');
    }

    /* change hash value */
    $.address.value(url_parts.join('/'));
};

Journal.SuperFilter.collapsed = {};

Journal.SuperFilter.doFilter = function ($parent, url) {
    /* post data */
    var data = {
        filters         : url,
        oc_route        : $parent.attr('data-route'),
        path            : $parent.attr('data-path'),
        full_path       : $parent.attr('data-full_path'),
        manufacturer_id : $parent.attr('data-manufacturer'),
        search          : $parent.attr('data-search'),
        tag             : $parent.attr('data-tag')
    };

    if ($parent.attr('data-route') === 'product/search') {
        data.category_id = $parent.attr('data-category_id');
        data.sub_category = $parent.attr('data-sub_category');
    }

    if ($("input#description").length) {
        data.description  = $("input#description").is(':checked') ? 1 : 0;
    };
    
    
    // items and pagination were hidden on page load, thereby creating a blur effect. comenting out code that enables this effect--Emmanuel 12/06/20

    /* hide pagination */
    // $('.pagination').addClass('hide');

    /* hide elements */
    // $('.sf-loader').remove();
    // $('.main-products.product-list, .main-products.product-grid').append('<div class="sf-loader"><span>' + $parent.attr('data-loading-text') + '</span></div>');

    /* filters */
    var isCollapsible = $parent.hasClass('filter-collapse');

    if (isCollapsible) {
        $('.filter-collapse .box').each(function () {
            Journal.SuperFilter.collapsed[$(this).attr('data-id')] = $(this).hasClass('is-collapsed');
        });
    }

    $.ajax({
        url: $parent.attr('data-filters-action'),
        type: 'get',
        async: false,
        data: data,
        success: function (response) {
            $parent.html($(response.replace(/\n/g, " ")).html());
            Journal.SuperFilter.setFilters($parent, url);
            if (isCollapsible) {
                if (Journal.SuperFilter.firstLoad) {
                    $parent.find('input:checked').each(function () {
                        Journal.SuperFilter.collapsed[$(this).closest('.box').attr('data-id')] = false;
                    });

                    if (url.search('minPrice') !== -1 || url.search('maxPrice') !== -1) {
                        Journal.SuperFilter.collapsed['price'] = false;
                    }
                }

                for (var i in Journal.SuperFilter.collapsed) {
                    if (Journal.SuperFilter.collapsed[i] === true) {
                        $('.box[data-id="' + i + '"]').addClass('is-collapsed');
                    }

                    if (Journal.SuperFilter.collapsed[i] === false) {
                        $('.box[data-id="' + i + '"]').removeClass('is-collapsed');
                    }
                }
            }
        }
    });

    /* products */
    $.ajax({
        url: $parent.attr('data-products-action'),
        type: 'get',
        data: data,
        success: function (response) {
            var $html = $('<div>' + response.replace(/\n/g, " ") + '</div>');

            $(".main-products.product-list, .main-products.product-grid").html($html.find('.product-list').html());

            if ($(".product-list").length == 0 &&  $(".product-grid").length == 0) {
                $("#content .content:eq(1)").replaceWith($html.html());
            };
            if ($(".pagination").length > 0) {
                $(".pagination").html($html.find('.pagination').html());
            }else{
                $(".pagination").after($html.find('.pagination').html());
                $(".pagination").after($html.find('.pagination').html());
            }

            $('.pagination').removeClass('hide');

            setTimeout(function(){
                if(Journal.quickViewStatus){
                    Journal.enableQuickView();
                }
                $('.main-products .product-grid-item .image > a').prepend('<div class="p-over p-grid-over"> </div>');
                $('.main-products .product-list-item .image > a').prepend('<div class="p-over p-list-over"> </div>');
            }, 1);

            Journal.SuperFilter.setNavigation();

            if ($html.find('.product-filter').attr('data-countdown') !== 'never') {
                Journal.enableCountdown();
            }

            if (Journal.SuperFilter.$ias) {
                Journal.SuperFilter.$ias.destroy();
                Journal.infiniteScroll();
            }
        }
    });
};

Journal.SuperFilter.setFilters = function ($parent, url) {
    var categoryPattern = /-c(((\d+)(,*))+)$/;
    var manufacturerPattern = /-m(((\d+)(,*))+)$/;
    var attributePattern = /-a(\d*)-v/;
    var optionPattern = /-o(\d*)-v/;
    var filterPattern = /-f(\d*)-v/;
    var tagsPattern = /(.+)-tags/;
    var availabilityPattern = /availability=(.+)/;

    var sort = null;
    var order = null;
    var minPrice = Math.floor(parseFloat($parent.find('.sf-price').attr('data-min-price')));
    var maxPrice = Math.ceil(parseFloat($parent.find('.sf-price').attr('data-max-price')));

    $.each(url.split('/'), function (index, part) {
        /* categories */
        if (categoryPattern.test(part)) {
            var values = part.split(categoryPattern);
            $.each(values[1].split(','), function (i, value) {
                $parent.find('.sf-category input[value="' + value + '"]').attr('checked', 'checked');
            });
            return;
        }

        /* manufacturers */
        if (manufacturerPattern.test(part)) {
            var values = part.split(manufacturerPattern);
            $.each(values[1].split(','), function (i, value) {
                $parent.find('.sf-manufacturer input[value="' + value + '"]').attr('checked', 'checked');
            });
            return;
        }

        /* attributes */
        if (attributePattern.test(part)) {
            var values = part.split(attributePattern);
            $.each(values[2].split(','), function (i, value) {
                $parent.find('.sf-attribute-' + values[1] + ' input[value="' + value + '"]').attr('checked', 'checked');
            });
            return;
        }

        /* options */
        if (optionPattern.test(part)) {
            var values = part.split(optionPattern);
            $.each(values[2].split(','), function (i, value) {
                $parent.find('.sf-option-' + values[1] + ' input[value="' + value + '"]').attr('checked', 'checked');
            });
            return;
        }

        /* filters */
        if (filterPattern.test(part)) {
            var values = part.split(filterPattern);
            $.each(values[2].split(','), function (i, value) {
                $parent.find('.sf-filter-' + values[1] + ' input[value="' + value + '"]').attr('checked', 'checked');
            });
            return;
        }

        /* tags */
        if (tagsPattern.test(part)) {
            var values = part.split(tagsPattern);
            $.each(values[1].split(','), function (i, value) {
                $parent.find('.sf-tags input[value="' + value + '"]').attr('checked', 'checked');
            });
            return;
        }

        if (availabilityPattern.test(part)) {
            var values = part.split(availabilityPattern);
            $.each(values[1].split(','), function (i, value) {
                $parent.find('.sf-availability input[value="' + value + '"]').attr('checked', 'checked');
            });
            return;
        }

        /* limit */
        if (part.indexOf('limit=') !== -1) {
            $('.limit select option[value$="' + part + '"]').attr('selected', 'selected');
            return;
        }

        /* sort */
        if (part.indexOf('sort=') !== -1) {
            sort = part;
            return;
        };

        /* order */
        if (part.indexOf('order=') !== -1) {
            order = part;
            return;
        };

        /* min price */
        if (part.indexOf('minPrice=') !== -1) {
            minPrice = part.replace('minPrice=', '');
            return;
        }

        /* max price */
        if (part.indexOf('maxPrice=') !== -1) {
            maxPrice = part.replace('maxPrice=', '');
            return;
        }
    });

    /* check sort order select */
    $('.sort select option[value$="' + sort + "&" + order +'"]').attr('selected', 'selected');

    /* set slider values */
    if (minPrice && maxPrice) {
        if ($parent.find('.sf-price').hasClass('sf-input')) {
            $parent.find('.min-price').val(minPrice);
            $parent.find('.max-price').val(maxPrice);
        } else {
            $parent.find('.slider').attr('data-min-value', minPrice);
            $parent.find('.slider').attr('data-max-value', maxPrice);
        }
    }

    setTimeout(function () {
        Journal.SuperFilter.priceSlider($parent);
    }, 0);

    /* add selected class */
    $parent.find('input:checked').each(function () {
        $(this).closest('label').addClass('sf-checked');
    });
};

Journal.SuperFilter.collision = function ($div1, $div2) {
    if (!$div1.length || $div2.length) return false;
    var x1 = $div1.offset().left;
    var w1 = 40;
    var r1 = x1 + w1;
    var x2 = $div2.offset().left;
    var w2 = 40;
    var r2 = x2 + w2;

    return !(r1 < x2 || x1 > r2);
}

Journal.SuperFilter.price = function ($parent, value) {
    var currency_left = $parent.attr('data-currency-left');
    var currency_right = $parent.attr('data-currency-right');
    var currency_decimal = $parent.attr('data-currency-decimal');
    var currency_thousand = $parent.attr('data-currency-thousand');
    value = value.toString().replace('.', currency_decimal).replace(/\B(?=(\d{3})+(?!\d))/g, currency_thousand);
    return currency_left ? (currency_left + value) : (value + currency_right);
}

Journal.SuperFilter.priceSlider = function ($parent) {
    if ($parent.find('.sf-price').hasClass('sf-input')) {
        $parent.find('.price-filter-button').click(function() {
            Journal.SuperFilter.filter($parent);
        });
        $parent.find('.min-price,.max-price').keydown(function(e) {
            if (e.keyCode == 13) {
                Journal.SuperFilter.filter($parent);
            }
        });
        return;
    }


    $parent.find('.slider').slider({
        isRTL: Journal.isRTL,
        range: true,
        min: Math.floor(parseFloat($parent.find('.sf-price').attr('data-min-price'))),
        max: Math.ceil(parseFloat($parent.find('.sf-price').attr('data-max-price'))),
        values: [parseFloat($parent.find('.slider').attr('data-min-value')), parseFloat($parent.find('.slider').attr('data-max-value'))],
        slide: function (event, ui) {
            $('.ui-slider-handle:eq(0) .price-range-min').html(Journal.SuperFilter.price($parent, ui.values[0]));
            $('.ui-slider-handle:eq(1) .price-range-max').html(Journal.SuperFilter.price($parent, ui.values[1]));
            $('.price-range-both').html('<i>' + Journal.SuperFilter.price($parent, ui.values[0]) + ' - </i>' + Journal.SuperFilter.price($parent, ui.values[1]));

            if (ui.values[0] == ui.values[1]) {
                $('.price-range-both i').css('display', 'none');
            } else {
                $('.price-range-both i').css('display', 'inline');
            }

            if (Journal.SuperFilter.collision($('.price-range-min'), $('.price-range-max')) == true) {
                $('.price-range-min, .price-range-max').css('opacity', '0');
                $('.price-range-both').css('display', 'block');
            } else {
                $('.price-range-min, .price-range-max').css('opacity', '1');
                $('.price-range-both').css('display', 'none');
            }
        },
        change: function (event, ui) {
            $(this).attr('data-min', ui.values[0]);
            $(this).attr('data-max', ui.values[1]);
            Journal.SuperFilter.filter($parent);
        },
        create: function () {
            $('.ui-slider-range').append('<span class="price-range-both value"><i>' + Journal.SuperFilter.price($parent, $parent.find('.slider').slider('values', 0 )) + ' - </i>' + Journal.SuperFilter.price($parent, $parent.find('.slider').slider('values', 1 )) + '</span>');
            $('.ui-slider-handle:eq(0)').append('<span class="price-range-min value">' + Journal.SuperFilter.price($parent, $parent.find('.slider').slider('values', 0 )) + '</span>');
            $('.ui-slider-handle:eq(1)').append('<span class="price-range-max value">' + Journal.SuperFilter.price($parent, $parent.find('.slider').slider('values', 1 )) + '</span>');
        }
    });

    if ( $('.price-range-min').html() === $('.price-range-max').html() ) {
        $('.price-range-both i').css('display', 'none');
    } else {
        $('.price-range-both i').css('display', 'inline');
    }

    if (Journal.SuperFilter.collision($('.price-range-min'), $('.price-range-max')) == true) {
        $('.price-range-min, .price-range-max').css('opacity', '0');
        $('.price-range-both').css('display', 'block');
    } else {
        $('.price-range-min, .price-range-max').css('opacity', '1');
        $('.price-range-both').css('display', 'none');
    }
};

Journal.SuperFilter.setNavigation = function () {
    /* sort and limit events */
    $('.sort select, .limit select').removeAttr('onchange');

    /* display product grid */
    Journal.applyView();
};

Journal.SuperFilter.getFilterParams = function() {
    var $parent = Journal.SuperFilter.$parent;

    var data = {
        filters         : Journal.SuperFilter.filter($parent, true),
        oc_route        : $parent.attr('data-route'),
        path            : $parent.attr('data-path'),
        full_path       : $parent.attr('data-full_path'),
        manufacturer_id : $parent.attr('data-manufacturer'),
        search          : $parent.attr('data-search'),
        tag             : $parent.attr('data-tag')
    };

    if ($parent.attr('data-route') === 'product/search') {
        data.category_id = $parent.attr('data-category_id');
        data.sub_category = $parent.attr('data-sub_category');
    }

    if ($("input#description").length) {
        data.description  = $("input#description").is(':checked') ? 1 : 0;
    }

    return data;
};

Journal.countdown = function ($elem, date) {
    $elem.countdown({
        date: date,
        render: function (date) {
            return $(this.el).html("<span>"
                + date.days + " <div>" + Journal.COUNTDOWN.DAYS + "</div></span> <span>"
                + (this.leadingZeros(date.hours)) + " <div>" + Journal.COUNTDOWN.HOURS + "</div></span> <span> "
                + (this.leadingZeros(date.min)) + " <div>" + Journal.COUNTDOWN.MINUTES + "</div></span> <span> "
                + (this.leadingZeros(date.sec)) + " <div>" + Journal.COUNTDOWN.SECONDS + "</div> </span>");
        }
    });
};

Journal.enableCountdown = function () {
    $('.main-products > div').each(function () {
        var $new = $(this).find('.price-new');
        if ($new.length && $new.attr('data-end-date')) {
            $(this).find('.image').append('<div class="countdown"></div>');
        }
        Journal.countdown($(this).find('.countdown'), $new.attr('data-end-date'));
    });
};

Journal.blogSearch = function ($element, url) {
    $element.find('a').click(function () {
        var term = encodeURIComponent($element.find('input').val());
        if (!term) return false;
        parent.window.location = url + term;
    });
    $element.find('input').keydown(function (e) {
        if (e.keyCode == 13) {
            var term = encodeURIComponent($element.find('input').val());
            if (!term) return false;
            parent.window.location = url + term;
        }
    });
};

Journal.gridView = function () {
    var classes = $('.main-products').removeClass('product-list').addClass('product-grid').attr('data-grid-classes');
    $('.main-products .product-grid-item, .main-products .product-list-item').each(function () {
        $(this).attr('class','product-grid-item ' + classes);
    });
    $('.product-thumb').addClass('product-wrapper');
    if(!Journal.isFlexboxSupported) {
        Journal.equalHeight($(".product-thumb.product-wrapper"), '.description');
    }
    $('.display .grid-view').addClass('active');
    $('.display .list-view').removeClass('active');
    localStorage.setItem('display', 'grid');
    //$(".main-products img.lazy").lazy({
    //    bind: 'event',
    //    visibleOnly: false,
    //    effect: "fadeIn",
    //    effectTime: 250
    //});
};

Journal.listView = function () {
    $('.main-products').removeClass('product-grid').addClass('product-list')
    $('.main-products .product-grid-item, .main-products .product-list-item').each(function () {
        $(this).attr('class','product-list-item xs-100 sm-100 md-100 lg-100 xl-100');
    });
    $('.product-thumb').removeClass('product-wrapper');
    $('.display .grid-view').removeClass('active');
    $('.display .list-view').addClass('active');
    localStorage.setItem('display', 'list');
    //$(".main-products img.lazy").lazy({
    //    bind: 'event',
    //    visibleOnly: false,
    //    effect: "fadeIn",
    //    effectTime: 250
    //});
};

Journal.applyView = function (default_view) {
    if (Journal.isOC2) {
        var current_view = localStorage.getItem('display') || default_view;
        if (current_view == 'list') {
            Journal.listView();
        } else {
            Journal.gridView();
        }
        $('ul.pagination').removeClass('pagination');
    } else {
        display($.totalStorage('display') || default_view);
    }
    if(!Journal.isFlexboxSupported) {
        Journal.equalHeight($(".main-products .product-wrapper"), '.name');
    }
};

//Add sequential classes to menu items

$(document).ready(function(){
    $('.super-menu > li').each(function(i) {
        $(this).addClass('main-menu-item-'+(i+1));
    });

    $('.journal-links .links > a').each(function(i) {
        $(this).addClass('top-menu-item-'+(i+1));
    });

    $('.journal-secondary .links > a').each(function(i) {
        $(this).addClass('secondary-menu-item-'+(i+1));
    });
    $('.btn').click(function(){
        $('.tooltip').css('visibility','hidden');
    });
});

Journal.infiniteScroll = function () {
    var next = Journal.isOC2 ? "li.active + li a" : "b + a";

    if (!$('.pagination').find(next).length) {
        return;
    }

    Journal.SuperFilter.$ias = $.ias({
        container: ".main-products",
        item: "> div",
        pagination: ".pagination",
        next: next
    });

    Journal.SuperFilter.$ias.extension(new IASTriggerExtension({
        text: Journal.infiniteScrollLoadMoreItemsText,
        html: '<span class="ias-button"><a class="ias-trigger ias-trigger-next button"><span>{text}</span></a></span>',
        offset: Journal.infiniteScrollLoadMoreItemsOffset
    }));

    Journal.SuperFilter.$ias.extension(new IASNoneLeftExtension({
        text: Journal.infiniteScrollNoMoreItemsText
    }));

    Journal.SuperFilter.$ias.on('load', function (event) {
        if ($('.journal-sf').length) {
            var page = event.url.split('page=')[1];
            var params = Journal.SuperFilter.getFilterParams();
            params.filters += '/page=' + page;
            event.url = Journal.SuperFilter.$parent.attr('data-products-action') + '&' + $.param(params);
        }
        $('.main-products.product-list, .main-products.product-grid').append('<span class="ias-loader"><span><i class="fa fa-spin fa-spinner"></i>' + Journal.infiniteScrollLoadingText + '</span></span>');
    });

    Journal.SuperFilter.$ias.on('loaded', function (data) {
        $('.pagination').html($(data).find('.pagination'));
    });

    Journal.SuperFilter.$ias.on('render', function (items) {
        $(items).attr('class', $('.main-products > .product-grid-item, .main-products > .product-list-item').attr('class'));

        $(items).each(function () {
            if (Journal.isOC2) {
                if (localStorage.getItem('display') == 'list') {
                    $(this).find('.product-thumb').removeClass('product-wrapper');

                } else {
                    $(this).find('.product-thumb').addClass('product-wrapper');
                }
            } else {
                if ($.totalStorage('display') === 'grid') {
                    $(this).html('<div class="product-wrapper">' + $(this).html() + '</div>')
                    $(this).find('.caption, .button-group').wrapAll('<div class="product-details"/>')
                }
            }

            var $img = $(this).find('img.lazy');
            $img.attr('src', $img.attr('data-src'));
        });
    });

    Journal.SuperFilter.$ias.on('rendered', function (items) {
        $('.ias-loader').remove();

        if(Journal.quickViewStatus) {
            Journal.enableQuickView();
        }

        if (Journal.hasCountdownEnabled) {
            Journal.enableCountdown();
        }
    });
};


// Code by tobecci
//
$('#search input[name=\'search\']').parent().find('button').on('click', function() {
var url = $('base').attr('href') + 'index.php?route=product/search';

var value = $('header #search input[name=\'search\']').val();

if (value) {
url += '&search=' + encodeURIComponent(value);
}

location = url;
});

var url = $('base').attr('href') + 'index.php?route=product/search';
//to
//var url = $('base').attr('href') + '/product-search';
