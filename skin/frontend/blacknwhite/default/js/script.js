/* Login ajax */
function ajaxLogin(ajaxUrl, clear){
	if(clear == true){
		clearHolder();
		jQuery(".ajax-box-overlay").removeClass('loaded');
	}
	jQuery("body").append("<div id='login-holder' />");
	if(!jQuery(".ajax-box-overlay").length){
		jQuery("#login-holder").after('<div class="ajax-box-overlay"><i class="load" /></div>');
		jQuery(".ajax-box-overlay").fadeIn('medium');
	}
	function overlayResizer(){
		jQuery(".ajax-box-overlay").css('height', jQuery(window).height());
	}
	overlayResizer();
	jQuery(window).resize(function(){overlayResizer()});
	
	jQuery.ajax({
		url: ajaxUrl,
		cache: false
	}).done(function(html){
		setTimeout(function(){
			jQuery("#login-holder").html(html).animate({
				opacity: 1,
				top: '100px'
			}, 500 );
			jQuery(".ajax-box-overlay").addClass('loaded');
			clearAll();
		}, 500);
	});
	
	var clearAll = function(){
		jQuery("#login-holder .close-button").on('click', function(){
			jQuery(".ajax-box-overlay").fadeOut('medium', function(){
				jQuery(this).remove();
			});
			clearHolder();
		});
	}
	function clearHolder(){
		jQuery("#login-holder").animate({
			opacity: 0,
			top: 0
		  }, 500, function() {
			jQuery(this).remove();
		});
	}
}

/* Top Cart */
function topCart(){
	jQuery('.top-cart .block-title a').on('click', function(){
		jQuery(this).parent().toggleClass('active');
		jQuery('#topCartContent').slideToggle(500).toggleClass('active')
		return false;
	});
}
/* Top Cart */

/* Wishlist Block Slider */
function wishlist_slider(){
  jQuery('#wishlist-slider .es-carousel').iosSlider({
	responsiveSlideWidth: true,
	snapToChildren: true,
	desktopClickDrag: true,
	infiniteSlider: false,
	navNextSelector: '#wishlist-slider .next',
	navPrevSelector: '#wishlist-slider .prev'
  });
}
 
function wishlist_set_height(){
	var wishlist_height = 0;
	jQuery('#wishlist-slider .es-carousel li').each(function(){
	 if(jQuery(this).height() > wishlist_height){
	  wishlist_height = jQuery(this).height();
	 }
	})
	jQuery('#wishlist-slider .es-carousel').css('min-height', wishlist_height+2);
}
if(jQuery('#wishlist-slider').length){
  whs_first_set = true;
  wishlist_slider();
}
/* Wishlist Block Slider */

 /* page title */
function titleDivider(){
	setTimeout(function(){
		jQuery('.widget-title, #footer .footer-block-title, .block-layered-nav .filter-label, aside.sidebar .block-title, header.rating-title, .box-reviews .rating-subtitle, .block-related .block-title, .product-options-title, .cart-blocks-title, #login-holder .page-title, .op_block_title, .quick-view-title').each(function(){
			title_container_width = jQuery(this).width();
			title_width = jQuery(this).find('h1, h2, h3, strong').innerWidth();
			divider_width = ((title_container_width - title_width-7)/2);	
			full_divider_width = (title_container_width - title_width-2);
			if ((jQuery(this).hasClass('widget-title')) || (jQuery(this).hasClass('filter-label')) || (jQuery(this).hasClass('block-title')) || (jQuery(this).hasClass('rating-title')) || (jQuery(this).hasClass('rating-subtitle')) || (jQuery(this).hasClass('cart-blocks-title')) || (jQuery(this).hasClass('page-title')) || (jQuery(this).hasClass('op_block_title')) || (jQuery(this).hasClass('quick-view-title'))) {
				if (divider_width > 15) {
					if(!jQuery(this).find('.right-divider').length){
						jQuery(this).append('<div class="right-divider" />');
					}
					jQuery(this).find('.right-divider').css('width', divider_width);
				} else {
					jQuery(this).find('.right-divider').remove();
				}
				if (divider_width > 15) {
					if(!jQuery(this).find('.left-divider').length) {
						jQuery(this).prepend('<div class="left-divider" />');
					}
					jQuery(this).find('.left-divider').css('width', divider_width);
				} else {
					jQuery(this).find('.left-divider').remove();
				}
			} else {
				if(!jQuery(this).find('.right-divider').length) {
					jQuery(this).append('<div class="right-divider" />');
				}
				jQuery(this).find('.right-divider').css('width', full_divider_width);
			}
		});
	}, 250);
}

jQuery(window).load(function() {
	
	/* Fix for IE */
    	if(navigator.userAgent.indexOf('IE')!=-1 && jQuery.support.noCloneEvent){
			jQuery.support.noCloneEvent = true;
		}
	/* End fix for IE */

	/* More Views Slider */
	if(jQuery('#more-views-slider').length){
		jQuery('#more-views-slider').iosSlider({
		   responsiveSlideWidth: true,
		   snapToChildren: true,
		   desktopClickDrag: true,
		   infiniteSlider: false,
		   navSlideSelector: '.sliderNavi .naviItem',
		   navNextSelector: '.more-views .next',
		   navPrevSelector: '.more-views .prev'
		 });
	}
	 function more_view_set_height(){
		if(jQuery('#more-views-slider').length){
			var more_view_height = 0;
			jQuery('#more-views-slider li a').each(function(){
			 if(jQuery(this).height() > more_view_height){
			  more_view_height = jQuery(this).height();
			 }
			})
			jQuery('#more-views-slider').css('min-height', more_view_height+2);
		}
	 }
	 /* More Views Slider */
	 
	 /* More Views Slider 2 */
	if(jQuery('#more-views-slider-2').length){
		jQuery('#more-views-slider-2').iosSlider({
		   responsiveSlideWidth: false,
		   snapToChildren: true,
		   desktopClickDrag: true,
		   infiniteSlider: false,
		   navNextSelector: '#more-views-slider-2 .next',
		   navPrevSelector: '#more-views-slider-2 .prev'
		 });
	}
	function more_view2_set_height(){
		if(jQuery('#more-views-slider-2.slider-on').length){
			more_view_height2 = 0;
			jQuery('#more-views-slider-2 li a').each(function(){
				if(jQuery(this).height() > more_view_height2){
					more_view_height2 = jQuery(this).height();
				}
			})
			jQuery('#more-views-slider-2.slider-on').css('min-height', more_view_height2+2);
		}
	 }
	function indexManager(className){
		startIndex = className.indexOf('-')+1;
		index = className.slice(startIndex);
		index = parseFloat(index);
		jQuery('#more-views-slider-2').iosSlider('goToSlide', index);
	}
	if(jQuery('#more-views-slider-2.slider-on')){
		jQuery('.more-views-container ul li').each(function(index){
			jQuery(this).addClass('item-'+(index+1));
		});
		jQuery('.more-views-container ul li a').on('click', function(){
			indexManager(jQuery(this).parent().attr('class'));
		});
	}
	/* More Views Slider 2 */

	 /* Related Block Slider */
	  if(jQuery('#block-related-slider').length) {
	  jQuery('#block-related-slider').iosSlider({
		   responsiveSlideWidth: true,
		   snapToChildren: true,
		   desktopClickDrag: true,
		   infiniteSlider: false,
		   navSlideSelector: '.sliderNavi .naviItem',
		   navNextSelector: '.block-related .next',
		   navPrevSelector: '.block-related .prev'
		 });
	 } 
	 
	 function related_set_height(){
		var related_height = 0;
		jQuery('#block-related-slider li.item').each(function(){
		 if(jQuery(this).height() > related_height){
		  related_height = jQuery(this).height();
		 }
		})
		jQuery('#block-related-slider').css('min-height', related_height+2);
	}
	 /* Related Block Slider */
	 
   /* Layered Navigation Accorion */
  if (jQuery('#layered_navigation_accordion').length) {
    jQuery('.filter-label').each(function(){
        jQuery(this).toggle(function(){
            jQuery(this).addClass('closed').next().slideToggle(200);
        },function(){
            jQuery(this).removeClass('closed').next().slideToggle(200);
        })
    });
  }
  /* Layered Navigation Accorion */


  /* Product Collateral Accordion */
  if (jQuery('#collateral-accordion').length) {
	  jQuery('#collateral-accordion > div.box-collateral').not(':first').hide();  
	  jQuery('#collateral-accordion > h2').click(function() {
		jQuery(this).parent().find('h2').removeClass('active');
		jQuery(this).addClass('active');
		
	    var nextDiv = jQuery(this).next();
	    var visibleSiblings = nextDiv.siblings('div:visible');
	 
	    if (visibleSiblings.length ) {
	      visibleSiblings.slideUp(300, function() {
	        nextDiv.slideToggle(500);
	      });
	    } else {
	       nextDiv.slideToggle(300, function(){
				if(!nextDiv.is(":visible")){
					jQuery(this).prev().removeClass('active');
				}
		   });
	    }
	  });
  }
  /* Product Collateral Accordion */

  /* My Cart Accordion */
  if (jQuery('#cart-accordion').length) {
	  jQuery('#cart-accordion > div.accordion-content').hide();	  
	  
	  jQuery('#cart-accordion > h3.accordion-title').wrapInner('<span/>').click(function(){
	  
		var accordion_title_check_flag = false;
		if(jQuery(this).hasClass('active')){accordion_title_check_flag = true;}
		jQuery('#cart-accordion > h3.accordion-title').removeClass('active');
		if(accordion_title_check_flag == false){
			jQuery(this).toggleClass('active');
	    }
		
		var nextDiv = jQuery(this).next();
	    var visibleSiblings = nextDiv.siblings('div:visible');
	 
	    if (visibleSiblings.length ) {
	      visibleSiblings.slideUp(300, function() {
	        nextDiv.slideToggle(500);
	      });
	    } else {
	       nextDiv.slideToggle(300);
	    }
		
	  });
	  
	  
  }
  /* My Cart Accordion */
  
  /* Coin Slider */

	/* Fancybox */
	if (jQuery.fn.fancybox) {
		jQuery('.fancybox').fancybox();
	}
	/* Fancybox */
	
	/* Zoom */
	if (jQuery('#zoom').length) {
		jQuery('.cloud-zoom, .cloud-zoom-gallery').CloudZoom();
  	}
	/* Zoom */

	/* Responsive */
	var responsiveflag = false;
	var topSelectFlag = false;
	var menu_type = jQuery('#nav').attr('class');
	
	function mobile_menu(mode){
		switch(mode)
		 {
		 case 'animate':
		   if(!jQuery('.nav-container').hasClass('mobile')){
				jQuery(".nav-container").addClass('mobile');
				jQuery('.nav-container > ul').slideUp('fast');
				jQuery('.menu-button').removeClass('active');
				jQuery('.menu-button').on('click', function(){
					jQuery(this).toggleClass('active');
					jQuery('.nav-container > ul').slideToggle('medium');
				});
			   jQuery('.nav-container > ul a').each(function(){
					if(jQuery(this).next('ul').length || jQuery(this).next('div.menu-wrapper').length){
						jQuery(this).before('<span class="menu-item-button"><i class="fa fa-plus"></i><i class="fa fa-minus"></i></span>')
						jQuery(this).next('ul').slideUp('fast');
						jQuery(this).prev('.menu-item-button').on('click', function(){
							jQuery(this).toggleClass('active');
							jQuery(this).nextAll('ul, div.menu-wrapper').slideToggle('medium');
						});
					}
			   });
		   }
		   break;
		 default:
				jQuery(".nav-container").removeClass('mobile');
				jQuery('.menu-button').off();
				jQuery('.nav-container > ul').slideDown('fast');
				jQuery('.nav-container .menu-item-button').each(function(){
					jQuery(this).nextAll('ul').slideDown('fast');
					jQuery(this).remove();
				});
				jQuery('.nav-container .menu-wrapper').slideUp('fast');
		 }
	}
	
	var WishlistLink = jQuery('.top-link-wishlist');
	WishlistLinkParent = WishlistLink.parent();
	WishlistLink.prepend('<i class="fa fa-heart-o"></i>');
	jQuery('.top-cart').after(WishlistLink);
	WishlistLinkParent.remove();
	jQuery('.wishlist-items').appendTo(WishlistLink);
	
	/* Wide Menu Top */
	function WideMenuTop() {
		if (jQuery(document.body).width() > 767) {
			jQuery('.nav-wide li .menu-wrapper').each(function() {
				var WideMenuItemHeight = jQuery(this).parent().height();
				jQuery(this).css('top', (WideMenuItemHeight + 4));
			});
		} else {
			jQuery('.nav-wide li .menu-wrapper').css('top', 'auto');
		}
	}
	
	/* Mobile Top Links */
	function mobileTopLinks() {
		var topLinks = jQuery('header#header .quick-access');
		if(jQuery(document.body).width() < 767) {
			if(!jQuery(topLinks).find('.fa-key').length){
				jQuery(topLinks).find('.top-link-login').prepend('<i class="fa fa-key"></i>');
			}
			jQuery('.top-cart').before(topLinks);
		} else {
			jQuery(topLinks).find('.top-link-login i').remove();
			jQuery(topLinks).prependTo('.header-top-right');
		}
	}
	
	/* Mobile Cart Remove Link */
	function MCRemoveLink() {
		if(jQuery(document.body).width() < 767) {
			jQuery('.cart-table .product-name').each(function(){
				var titleHeight = jQuery(this).position().top;
				var removeLink = jQuery(this).parent().parent().parent().find('.remove .btn-remove2');
				jQuery(removeLink).css('top', titleHeight - 35);
			});
		}
	}
	
	function toDo(){
		if (jQuery(document.body).width() < 767 && responsiveflag == false){
			/* Top Menu Select */
			if(topSelectFlag == false){
				jQuery('.nav-container .sbSelector').wrapInner('<span />').prepend('<span />');
				topSelectFlag = true;
			}
			jQuery('.nav-container .sbOptions li a').on('click', function(){
				if(!jQuery('.nav-container .sbSelector span').length){
					jQuery('.nav-container .sbSelector').wrapInner('<span />').prepend('<span />');
				}
			});
			/* //Top Menu Select */
			responsiveflag = true;
		}
		else if (jQuery(document.body).width() > 767){
			responsiveflag = false;
		}
	}
	function replacingClass () {

	   if (jQuery(document.body).width() < 480) { //Mobile
			mobile_menu('animate');
	   }
	   if (jQuery(document.body).width() > 479 && jQuery(document.body).width() < 768) { //iPhone
			mobile_menu('animate');
	   }  
		if (jQuery(document.body).width() > 767 && jQuery(document.body).width() < 977){ //Tablet
			mobile_menu('animate');
	    }
		if (jQuery(document.body).width() > 977 && jQuery(document.body).width() < 1279){ //Tablet
			mobile_menu('reset');
	    }
		if (jQuery(document.body).width() > 1279){ //Extra Large
			mobile_menu('reset');
		}
	}
	replacingClass();
	toDo();
	more_view_set_height();
	more_view2_set_height();
	wishlist_set_height();
	related_set_height();
	titleDivider();
	WideMenuTop();
	mobileTopLinks();
	MCRemoveLink();
	jQuery(window).resize(function(){toDo(); replacingClass(); more_view_set_height(); more_view2_set_height(); wishlist_set_height(); related_set_height(); titleDivider(); WideMenuTop(); mobileTopLinks(); MCRemoveLink();});
	/* Responsive */
	
	/* Top Menu */
	function menuHeight2 () {
		var menu_min_height = 0;
		jQuery('#nav li.tech').css('height', 'auto');
		jQuery('#nav li.tech').each(function(){
			if(jQuery(this).height() > menu_min_height){
				menu_min_height = jQuery(this).height();
			}
		});		
		jQuery('#nav li.tech').each(function(){
			jQuery(this).css('height', menu_min_height + 'px');
		});
	}
	
	/* Top Selects */
	function option_class_add(items, is_selector){
		jQuery(items).each(function(){
			if(is_selector){
				jQuery(this).removeAttr('class'); 
				jQuery(this).addClass('sbSelector');
			}			
			stripped_string = jQuery(this).html().replace(/(<([^>]+)>)/ig,"");
			RegEx=/\s/g;
			stripped_string=stripped_string.replace(RegEx,"");
			jQuery(this).addClass(stripped_string.toLowerCase());
			if(is_selector){
				tags_add();
			}
		});
	}
	option_class_add('.sbOptions li a, .sbSelector', false);
	jQuery('.sbOptions li a').on('click', function(){
		option_class_add('sbSelector', true);
	});	
	function tags_add(){
		jQuery('.sbSelector').each(function(){
			if(!jQuery(this).find('span.text').length){
				jQuery(this).wrapInner('<span class="text" />').append('<span />').find('span:last').wrapInner('<span />');
			}
		});
	}
	tags_add();
	/* //Top Selects */
	
	
	if (jQuery('body').hasClass('retina-ready')) {
		/* Mobile Devices */
		if((navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i)) || (navigator.userAgent.match(/iPad/i)) || (navigator.userAgent.match(/Android/i))){
			
			/* Mobile Devices Class */
			jQuery('body').addClass('mobile-device');
			
			/* Menu */
			jQuery(".nav-container:not('.mobile') #nav li").on({
	            click: function (){
	                if ( !jQuery(this).hasClass('touched') && jQuery(this).children('ul').length ){
						jQuery(this).addClass('touched');
						clearTouch(jQuery(this));
						return false;
	                }
	            }
	        });
			
			/* Clear Touch Function */
			function clearTouch(handlerObject){
				jQuery('body').on('click', function(){
					handlerObject.removeClass('touched closed');
					if(handlerObject.parent().attr('id') == 'categories-accordion'){
						handlerObject.children('ul').slideToggle(200);
					}
					jQuery('body').off();
				});
				handlerObject.click(function(event){
					event.stopPropagation();
				});
				handlerObject.parent().click(function(){
					handlerObject.removeClass('touched');
				});
				handlerObject.siblings().click(function(){
					handlerObject.removeClass('touched');
				});
			}
			
			var mobileDevice = true;
		}else{
			var mobileDevice = false;
		}

		//images with custom attributes
		if (pixelRatio > 1) {
			function brandsWidget(){
				brands = jQuery('ul.brands li a img');
				brands.each(function(){
					jQuery(this).css('width', jQuery(this).width()/2);
				});
			}
			function logoResize(){
				jQuery('header#header h2.logo, footer#footer .footer-logo').each(function(){
					jQuery(this).attr('style', '');
					jQuery(this).find('img').attr('style', '');
					defaultStart = true;
					if(jQuery(this).hasClass('footer-logo')){
						jQuery(this).css('position', 'absolute');
						if(jQuery(this).parent().width() < jQuery(this).width()){
							jQuery(this).find('img').css('width', jQuery(this).parent().width() - (jQuery(this).parent().width()*0.15));
							defaultStart = false;
						}
					}
					if(defaultStart){
						jQuery(this).find('img').css('width', (jQuery(this).find('img').width()/2));
					}
					if(!jQuery(this).hasClass('small_logo')){
						jQuery(this).css({
							'position': 'static',
							'opacity': '1'
						});
					}
				});
			}
			logoResize();
			brandsWidget();
			jQuery(window).resize(function(){
				logoResize();
			});
		}
	}
	
	
	/* Categories Accorion */
	if (jQuery('#categories-accordion').length){
		jQuery('#categories-accordion li.parent ul').before('<div class="btn-cat"><i class="fa fa-plus-square-o"></i><i class="fa fa-minus-square-o"></i></div>');
		jQuery('#categories-accordion li.level-top:not(.parent) > a').before('<i class="fa fa-square-o"></i>');
		if(mobileDevice == true){
			jQuery('#categories-accordion li.parent').each(function(){
				jQuery(this).on({
					click: function (){
						if(!jQuery(this).hasClass('touched')){
							jQuery(this).addClass('touched closed').children('ul').slideToggle(200);
							clearTouch(jQuery(this));
							return false;
						}
					}
				});
			});
		}else{
			jQuery('#categories-accordion li.parent .btn-cat').each(function(){
				jQuery(this).toggle(function(){
					jQuery(this).addClass('closed').next().slideToggle(200);
					jQuery(this).prev().addClass('closed');
				},function(){
					jQuery(this).removeClass('closed').next().slideToggle(200);
					jQuery(this).prev().removeClass('closed');
				})
			});
		}
	}
	/* Categories Accorion */
	
	/* Menu Wide */
	if(jQuery('#nav-wide').length){
		jQuery('#nav-wide li.level-top').mouseenter(function(){
			jQuery(this).addClass('over');
		});
		jQuery('#nav-wide li.level-top').mouseleave(function(){
			jQuery(this).removeClass('over');
		});
		jQuery('.nav-wide#nav-wide .menu-wrapper').each(function(){
			jQuery(this).children('div.alpha.omega:first').addClass('first');
		});
	}
	
});
var pixelRatio = !!window.devicePixelRatio ? window.devicePixelRatio : 1;
jQuery(document).ready(function(){
	
	if (jQuery('body').hasClass('retina-ready')) {
		if (pixelRatio > 1) {
			jQuery('img[data-srcX2]').each(function(){
				jQuery(this).attr('src',jQuery(this).attr('data-srcX2'));
			});
		}
	}
	
	/* Selects */
	jQuery(".form-currency select, .form-language select, .store-switcher select, .toolbar select").selectbox();
	
	
/* Messages button */
	if(jQuery('ul.messages').length){
		jQuery('ul.messages > li').each(function(){
			switch (jQuery(this).attr('class')){
				case 'success-msg':
				messageIcon = '<i class="fa fa-check" />';
				break;
				case 'error-msg':
				messageIcon = '<i class="fa fa-times" />';
				break;
				case 'note-msg':
				messageIcon = '<i class="fa fa-exclamation" />';
				break;
				case 'notice-msg':
				messageIcon = '<i class="fa fa-exclamation" />';
				break;
			}
			jQuery(this).prepend('<div class="messages-close-btn" />', messageIcon);
			jQuery('ul.messages .messages-close-btn').on('click', function(){
				jQuery('ul.messages').remove();
			});
		});
	}
	if(jQuery('.content_bottom').length){
		jQuery('.content_bottom button#find-us').click(function() {
			jQuery('.content_bottom').toggleClass('active');
			if(jQuery('.content_bottom').hasClass('hide')){
				jQuery('.content_bottom').removeClass('hide');
			}else{
				setTimeout(function(){
					jQuery('.content_bottom').addClass('hide');
				}, 500);
			}
		});
	}
	
	/* floating header */
	if(jQuery('body').hasClass('floating-header')){
		var headerHeight = jQuery('#header').height();
		jQuery(window).scroll(function(){
			if(jQuery(this).scrollTop() >= headerHeight ){
				if(!jQuery('#header').hasClass('floating')){
					jQuery('body').css('padding-top', headerHeight);
					jQuery('#header').addClass('floating');
					jQuery('#header').slideDown('fast');
				}
			}
			if(jQuery(this).scrollTop() < headerHeight ){
				if(jQuery('#header').hasClass('floating')){
					jQuery('body').attr('style', '');
					jQuery('#header').removeClass('floating');
					jQuery('#header').attr('style', '');
				}
			}
		});
	}

	if(jQuery('span.hover-image').length){
		jQuery('span.hover-image').parent().addClass('hover-exists');
	}
	if(jQuery('header#header .form-language .sbHolder').length) {
		jQuery('header#header .form-language').addClass('select');
	}
	
	/* Labels height */
	jQuery('span.label-new').each(function(){
		var labelNewWidth = jQuery(this).outerWidth();
		jQuery(this).css({
			'height' : labelNewWidth,
			'line-height' : labelNewWidth + 'px'
		});
	});
	jQuery('span.label-sale').each(function(){
		var labelSaleWidth = jQuery(this).outerWidth();
		jQuery(this).css({
			'height' : labelSaleWidth,
			'line-height' : labelSaleWidth + 'px'
		});
	});
	/***  ***/
	jQuery('.contacts-footer-content input, .contacts-footer-content textarea, #header .form-search input').each(function(){
		jQuery(this).focusin(function(){
			jQuery(this).parent().addClass('focus');
		});
	});
	jQuery('.contacts-footer-content input, .contacts-footer-content textarea, #header .form-search input').each(function(){
		jQuery(this).focusout(function(){
			jQuery(this).parent().removeClass('focus');
		});
	});
	
	/* Header Customer Block */
	if(jQuery('#header .customer-name').length){
		var CustName = jQuery('#header .customer-name');
		CustName.next().hide();
		CustName.click(function(){
			CustName.next().slideToggle();
		});
	}
});

jQuery(document).ready(function(){
        jQuery('input[type="radio"]').click(function(){
            if(jQuery(this).attr("value")==""){
                jQuery(".pass").show();
                
            }
            if(jQuery(this).attr("value")=="guest"){
                 jQuery(".pass").hide();
            }
            
        });
    });
	
/* check pin code*/

jQuery("input[name='pincode']").keypress(function(e){
		if(e.which==13){
		check_pincode();	
		return false;	
		}
	}); 
	
function check_pincode(){
	var pincode=jQuery("input[name='pincode']").val();	
	if(pincode!=""){
	if(pincode.length==6){
		jQuery("#pp-pincode-loader").show();	
	setTimeout(
	jQuery.ajax({
			url:"http://www.netakart.com//pin_info/check.php?pincode="+pincode,
			cache:false,
			dataType: "json",
			success:function(html){
				
				if((html[0] != 0)){
					jQuery(".pincode_msg").html('<span class="pincode-msg pincode-success">Available.</span>');	
					jQuery(".pincode_msg").show();
					jQuery("input[name='pincode']").removeClass('input_error');

				}
				
				else{
					jQuery(".pincode_msg").html("<span class='pincode-msg pincode-invalid'>We don't ship to this address. Please enter another area PIN code from where you can collect your product. Else check back later.</span>");
					jQuery(".pincode_msg").show();	
					jQuery("input[name='pincode']").addClass('input_error');
					jQuery("input[name='pincode']").focus();
					jQuery(".pincode_success_continue").html('');	
					jQuery(".pincode_success_continue").hide();
					
				}
				jQuery("#pp-pincode-loader").hide();
			}
	}),1000);
	}
	else{
		jQuery(".pincode_msg").html("<span class='pincode-msg pincode-invalid'>This PIN doesn't exist, please enter a valid area PIN code.</span>");	
		jQuery(".pincode_msg").show();	
		jQuery(".pincode_success_continue").html('');	
					jQuery(".pincode_success_continue").hide();
	}
	} else {
		jQuery(".pincode_msg").html("<span class='pincode-msg pincode-invalid'>Please enter valid area PIN code. Help us ship to your location.</span>");	
		jQuery(".pincode_msg").show();	
		jQuery(".pincode_success_continue").html('');	
					jQuery(".pincode_success_continue").hide();
	}
}


jQuery(document).ready(function() {

	jQuery("#login-email").blur(function() { // when focus out
		var CEmail = jQuery(this).val();
		
		
		jQuery(".email-not-exists").hide();
		jQuery(".email-exists").hide();
		
		
		if (validateEmail(CEmail)) {
		
		jQuery("#pp-pincode-loader").show();
		
		var form_data = {
			action: 'check_username',
			username: CEmail
		};
		jQuery.ajax({
			type: "POST",
			url: "http://www.netakart.com/check-email.php",
			data: form_data,
			success: function(result) {
				//jQuery("#message").html(result);
				if(result == 1){
				jQuery(".email-exists").show();
				jQuery(".email-not-exists").hide();
				}
				else if(CEmail != ""){
				jQuery(".email-not-exists").show();
				jQuery(".email-exists").hide();
				}
				jQuery("#pp-pincode-loader").hide();
			}
		});
		
		
		}
		else if(CEmail != ""){
			jQuery(".email-exists").show();
			jQuery(".email-not-exists").hide();
			jQuery("#pp-pincode-loader").hide();
		}

	});

});

function validateEmail(sEmail) {
var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;;
if (filter.test(sEmail)) {
return true;
}
else {
return false;
}
}


/*var ajax = getHTTPObject();

function getHTTPObject()
{
	var xmlhttp;
	if (window.XMLHttpRequest) {
	  // code for IE7+, Firefox, Chrome, Opera, Safari
	  xmlhttp=new XMLHttpRequest();
	} else if (window.ActiveXObject) {
	  // code for IE6, IE5
	  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	} else {
	  //alert("Your browser does not support XMLHTTP!");
	}
	return xmlhttp;
}

function updateCityState()
{
	if (ajax)
	{
		var zipValue = document.getElementById("billing:postcode").value;
		
		if(zipValue)
		{
			var url = "http://www.netakart.com/dev/city-state.php";
			var param = "?zip=" + escape(zipValue);

			ajax.open("GET", url + param, true);
			ajax.onreadystatechange = handleAjax;
			ajax.send(null);
		}
	}
}
function handleAjax()
{
	if (ajax.readyState == 4)
	{
		
		var citystatearr = ajax.responseText.split(",");
		console.log(citystatearr);
		var city = document.getElementById('billing:city');
		var state = document.getElementById('billing:region');

		city.value = citystatearr[0];
		state.value = citystatearr[1];
	}
}*/

function updateCityState(){
	
	var zipValue = document.getElementById("billing:postcode").value;
	var regPostcode = /^([0-9]){6}$/;
	if(zipValue){
		if(regPostcode.test(zipValue) == false)
    	{
		    jQuery("#pinerr").html("This pincode is not valid");
			return false;
   		}
		else {
			
	jQuery('span.loading').show();
	jQuery("#pinerr").html('');
	jQuery.ajax({
			type: "POST",
			url: "http://www.netakart.com/dev/city-state.php?zip="+zipValue,
			data:zipValue,
			dataType: "json",
			success: function(result) {
				jQuery('span.loading').hide();
				if(result !== false){
					
				document.getElementById('billing:city').value = result['city'];
				document.getElementById('billing:region').value = result['state'];
				} else {
					document.getElementById('billing:city').value = '';
					document.getElementById('billing:region').value = '';
					jQuery("#pinerr").html("This pincode is not serviceable");
					return false;
				}
			}
		});
		}
	} else {
		jQuery("#pinerr").html("This pincode is not serviceable");
	}
}


