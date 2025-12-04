//======================================================Price Format==============//

//=======================================================Loader==============//
function dLoader(target) {
	if (target.find('div[class*="dLoader"]').length < 1) {
		target.addClass('pRelative').append('<div class="dLoader"><span class="fas fa-spinner fa-pulse"></span></div>');
	}
}


function dLoaderBody() {
	let body = jQuery('body');
	if (body.find('div[class*="dLoader"]').length < 1) {
		body.addClass('noScroll').append('<div class="dLoader pFixed"><span class="fas fa-spinner fa-pulse"></span></div>');
	}
}



function dLoaderRemove(target = jQuery('body')) {
	target.removeClass('noScroll');
	target.removeClass('pRelative').find('div[class*="dLoader"]').remove();
}
function placeholderLoader(target) {
	target.addClass('placeholderLoader');
}
function placeholderLoaderRemove(target) {
	target.each(function () {
		target.removeClass('placeholderLoader');
	})
}
//======================================================Page Scroll==============//
function pageScrollTo(target) {
	jQuery('html, body').animate({
		scrollTop: target.offset().top -= 150
	}, 1000);
}
//====================================================Load Date picker==============//
function mp_load_date_picker(parent = jQuery('.mpStyle')) {
	parent.find(".date_type.hasDatepicker").each(function () {
		jQuery(this).removeClass('hasDatepicker').attr('id', '').removeData('datepicker').unbind();
	}).promise().done(function () {
		parent.find(".date_type").datepicker({
			dateFormat: js_date_format,
			//showButtonPanel: true,
			autoSize: true,
			changeMonth: true,
			changeYear: true,
			yearRange: '1900:' + (new Date().getFullYear() + 10), // from 1900 to 10 years ahead
			onSelect: function (dateString, data) {
				let date = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
				jQuery(this).closest('label').find('input[type="hidden"]').val(date).trigger('change');
			},
		});
	});
}

//=====================================================Load initial=================//
(function ($) {
	"use strict";
	$(document).ready(function () {
		mp_load_date_picker();
		//$('.mp_select2').select2({});
	});
}(jQuery));
//====================================================================Load Bg Image=================//
function loadBgImage() {
	jQuery('body').find('div.mpStyle [data-bg-image]:visible').each(function () {
		let target = jQuery(this);
		if (target.closest('.sliderAllItem').length === 0) {
			let width = target.outerWidth();
			let height = target.outerHeight();
			if (target.css('background-image') === 'none' || width === 0 || height === 0) {
				let bg_url = target.data('bg-image');
				if (!bg_url || bg_url.width === 0 || bg_url.width === 'undefined') {
					bg_url = mp_empty_image_url;
				}
				mp_resize_bg_image_area(target, bg_url);
				target.css('background-image', 'url("' + bg_url + '")').promise().done(function () {
					dLoaderRemove(jQuery(this));
				});
			}
		}
	});
	jQuery('body').find('div.mpStyle .sliderAllItem').each(function () {
		let target = jQuery(this);
		mpwem_slider_resize(target)
	});
	return true;
}
function mpwem_slider_resize(target) {
	let all_height = [];
	let totalHeight = 0;
	let imgCount = 0;
	let main_div_width = target.innerWidth();
	//console.log(main_div_width);
	let item_count = target.find('.sliderItem').length;
	target.find('[data-bg-image]').each(function () {
		let width = jQuery(this).outerWidth();
		let height = jQuery(this).outerHeight();
		// if (jQuery(this).css('background-image') === 'none' || width === 0 || height === 0) {
		let bg_url = jQuery(this).data('bg-image');
		if (!bg_url || bg_url.width === 0 || bg_url.width === 'undefined') {
			bg_url = mp_empty_image_url;
		}
		let imgWidth = jQuery(this).data('width');
		let imgHeight = jQuery(this).data('height');
		all_height.push(imgHeight);
		totalHeight = totalHeight + (imgHeight * main_div_width) / imgWidth;
		imgCount++;
		if (imgCount === item_count) {
			let slider_height_type = target.closest('.superSlider').find('input[name="slider_height_type"]').val();
			let height_content = totalHeight / imgCount;
			if (slider_height_type === 'min') {
				height_content = Math.min(...all_height);
				target.find('.sliderItem').css({"min-height": height_content});
				target.find('.sliderItem').css({"max-height": height_content});
			} else if (slider_height_type === 'max') {
				height_content = Math.max(...all_height);
				target.find('.sliderItem').css({"min-height": height_content});
				target.find('.sliderItem').css({"max-height": height_content});
			} else {
				target.find('.sliderItem').css({"min-height": height_content});
				target.find('.sliderItem').css({"max-height": height_content});
			}
			target.css({"max-height": height_content});
			target.siblings('.sliderShowcase').css({"max-height": height_content});
		}
		jQuery(this).css('background-image', 'url("' + bg_url + '")').promise().done(function () {
			dLoaderRemove(jQuery(this));
		});
		// }
	});
}
function mp_resize_bg_image_area(target, bg_url) {
	let tmpImg = new Image();
	tmpImg.src = bg_url;
	jQuery(tmpImg).one('load', function () {
		let imgWidth = tmpImg.width;
		let imgHeight = tmpImg.height;
		let height = target.outerWidth() * imgHeight / imgWidth;
		target.css({"min-height": height});
	});
}
(function ($) {
	let bg_image_load = false;
	$(document).ready(function () {
		$('body').find('div.mpStyle [data-bg-image]').each(function () {
			dLoader($(this));
		});
		$(window).on('load', function () {
			load_initial();
		});
		if (!bg_image_load) {
			load_initial();
			$(document).scroll(function () {
				load_initial();
			});
		}
	});

	$(window).on('load , resize', function () {
		$('body').find('div.mpStyle [data-bg-image]:visible').each(function () {
			let target = $(this);
			if (target.closest('.sliderAllItem').length === 0) {
				let bg_url = target.data('bg-image');
				if (!bg_url || bg_url.width === 0 || bg_url.width === 'undefined') {
					bg_url = mp_empty_image_url;
				}
				mp_resize_bg_image_area(target, bg_url);
			}
		});
		jQuery('body').find('div.mpStyle .sliderAllItem:visible').each(function () {
			let target = jQuery(this);
			mpwem_slider_resize(target)
		});
	});
	function load_initial() {
		if (!bg_image_load) {
			if (loadBgImage()) {
				bg_image_load = true;
				placeholderLoaderRemove($('.mpStyle.placeholderLoader'))
			}
		}
	}
}(jQuery));
//=============================================================================Change icon and text=================//
function content_icon_change(currentTarget) {
	let openIcon = currentTarget.data('open-icon');
	let closeIcon = currentTarget.data('close-icon');
	if (openIcon || closeIcon) {
		currentTarget.find('[data-icon]').toggleClass(closeIcon).toggleClass(openIcon);
	}
}
function content_text_change(currentTarget) {
	let openText = currentTarget.data('open-text');
	openText = openText ? openText.toString() : '';
	let closeText = currentTarget.data('close-text');
	closeText = closeText ? closeText : '';
	if (openText || closeText) {
		let text = currentTarget.find('[data-text]').html();
		text = text ? text.toString() : ''
		if (text !== openText) {
			currentTarget.find('[data-text]').html(openText);
		} else {
			currentTarget.find('[data-text]').html(closeText);
		}
	}
}
function content_class_change(currentTarget) {
	let clsName = currentTarget.data('add-class');
	if (clsName) {
		if (currentTarget.find('[data-class]').length > 0) {
			currentTarget.find('[data-class]').toggleClass(clsName);
		} else {
			currentTarget.toggleClass(clsName);
		}
	}
}

function mp_all_content_change($this) {
	loadBgImage();
	content_class_change($this);
	content_icon_change($this);
	content_text_change($this);
}

//==============================================================================Qty inc dec================//


(function ($) {
	"use strict";
	$(document).on("click", "div.mpStyle .decQty ,div.mpStyle .incQty", function () {
		let current = $(this);
		let target = current.closest('.qtyIncDec').find('input');
		let currentValue = parseInt(target.val()) || 0;
		let min = parseInt(target.attr('min')) || 0;
		let max = parseInt(target.attr('max')) || 999;
		let minQty = parseInt(target.attr('data-min-qty')) || 0;
		let value;
		target.parents('.qtyIncDec').find('.incQty , .decQty').removeClass('mpDisabled');
		if (current.hasClass('incQty')) {
			// Increment logic
			if (currentValue === 0 && minQty > 0) {
				// Jump from 0 to min_qty if min_qty is set
				value = minQty;
			} else {
				// Normal increment
				value = currentValue + 1;
			}
		} else {
			// Decrement logic
			if (currentValue <= minQty && minQty > 0) {
				// If at min_qty, go to 0
				value = 0;
			} else {
				// Normal decrement but not below 0
				value = Math.max(0, currentValue - 1);
			}
		}
		// Enforce max limit
		if (value > max) {
			value = max;
			target.parents('.qtyIncDec').find('.incQty').addClass('mpDisabled');
		}
		// Disable decrement button at 0
		if (value === 0) {
			target.parents('.qtyIncDec').find('.decQty').addClass('mpDisabled');
		}
		// Disable increment button at max
		if (value >= max) {
			target.parents('.qtyIncDec').find('.incQty').addClass('mpDisabled');
		}
		target.val(value).trigger('change').trigger('input');
	});
}(jQuery));


//============================================================================Tabs================//
(function ($) {
	"use strict";
	function active_next_tab(parent, targetTab) {
		parent.height(parent.height());
		let tabsContent = parent.find('.tabsContentNext:first');
		let target_tabContent = tabsContent.children('[data-tabs-next="' + targetTab + '"]');
		let index = target_tabContent.index() + 1;
		let num_of_tab = parent.find('.tabListsNext:first').children('[data-tabs-target-next]').length;
		let i = 1;
		for (i; i <= num_of_tab; i++) {
			let target_tab = parent.find('.tabListsNext:first').children('[data-tabs-target-next]:nth-child(' + i + ')');
			if (i <= index) {
				target_tab.addClass('active');
			} else {
				target_tab.removeClass('active');
			}
			if (i === index - 1) {
				mp_all_content_change(target_tab);
			}
		}
		if (index < 2 && num_of_tab > index) {
			parent.find('.nextTab_next').slideDown('fast');
			parent.find('.nextTab_prev').slideUp('fast');
		} else if (num_of_tab === index) {
			parent.find('.nextTab_next').slideUp('fast');
			parent.find('.nextTab_prev').slideDown('fast');
		} else {
			parent.find('.nextTab_next').slideDown('fast');
			parent.find('.nextTab_prev').slideDown('fast');
		}
		target_tabContent.slideDown(350);
		tabsContent.children('[data-tabs-next].active').slideUp(350).removeClass('active').promise().done(function () {
			target_tabContent.addClass('active').promise().done(function () {
				pageScrollTo(tabsContent);
				parent.height('auto').promise().done(function () {
					loadBgImage();
					mp_sticky_management();
					dLoaderRemove(parent);
				});
			});
		});
	}
	$(document).on('click', '.mpStyle .mpTabsNext .nextTab_prev_link', function () {
		let parent = $(this).closest('.mpTabsNext');
		if (parent.find('[data-tabs-target-next].active').length > 1) {
			parent.find('.nextTab_prev').trigger('click');
		}
	});
	$(document).on('click', '.mpStyle .mpTabsNext .nextTab_next', function () {
		let parent = $(this).closest('.mpTabsNext');
		let target = parent.find('.tabListsNext:first');
		let num_of_tab = target.children('[data-tabs-target-next].active').length + 1;
		let targetTab = target.children('[data-tabs-target-next]:nth-child(' + num_of_tab + ')').data('tabs-target-next');
		active_next_tab(parent, targetTab);
	});
	$(document).on('click', '.mpStyle .mpTabsNext .nextTab_prev', function () {
		let parent = $(this).closest('.mpTabsNext');
		let target = parent.find('.tabListsNext:first');
		let num_of_tab = target.children('[data-tabs-target-next].active').length - 1;
		let targetTab = target.children('[data-tabs-target-next]:nth-child(' + num_of_tab + ')').data('tabs-target-next');
		active_next_tab(parent, targetTab);
	});
	$(document).ready(function () {

		$('.mpStyle .mpTabsNext').each(function () {
			let parent = $(this);
			if (parent.find('[data-tabs-target-next].active').length < 1) {
				dLoader(parent);
				let tabLists = parent.find('.tabListsNext:first');
				let targetTab = tabLists.find('[data-tabs-target-next]').first().data('tabs-target-next')
				active_next_tab(parent, targetTab);
			}
		});
	});
	$(document).on('click', '.mpStyle [data-tabs-target]', function () {
		if (!$(this).hasClass('active')) {
			let tabsTarget = $(this).data('tabs-target');
			let parent = $(this).closest('.mpTabs');
			parent.height(parent.height());
			let tabLists = $(this).closest('.tabLists');
			let tabsContent = parent.find('.tabsContent:first');
			tabLists.find('[data-tabs-target].active').each(function () {
				$(this).removeClass('active').promise().done(function () {
					mp_all_content_change($(this))
				});
			});
			$(this).addClass('active').promise().done(function () {
				mp_all_content_change($(this))
			});
			tabsContent.children('[data-tabs="' + tabsTarget + '"]').slideDown(350);
			tabsContent.children('[data-tabs].active').slideUp(350).removeClass('active').promise().done(function () {
				tabsContent.children('[data-tabs="' + tabsTarget + '"]').addClass('active').promise().done(function () {
					//dLoaderRemove(tabsContent);
					loadBgImage();
					parent.height('auto');
				});
			});
		}
	});
}(jQuery));
//======================================================================Collapse=================//
(function ($) {
	"use strict";
	$(document).on('click', '.mpStyle [data-collapse-target]', function () {
		let currentTarget = $(this);
		let target_id = currentTarget.data('collapse-target');
		let close_id = currentTarget.data('close-target');
		let target = $('[data-collapse="' + target_id + '"]');
		if (target_close(close_id, target_id) && collapse_close_inside(currentTarget) && target_collapse(target, currentTarget)) {
			mp_all_content_change(currentTarget);
		}
	});
	$(document).on('change', '.mpStyle select[data-collapse-target]', function () {
		let currentTarget = $(this);
		let value = currentTarget.val();
		currentTarget.find('option').each(function () {
			if ($(this).attr('data-option-target-multi')) {
				let target_ids = $(this).data('option-target-multi');
				target_ids = target_ids.toString().split(" ");
				target_ids.forEach(function (target_id) {
					let target = $('[data-collapse="' + target_id + '"]');
					target.slideUp(350).removeClass('mActive');
				});
			} else {
				let target_id = $(this).data('option-target');
				let target = $('[data-collapse="' + target_id + '"]');
				target.slideUp('fast').removeClass('mActive');
			}
		}).promise().done(function () {
			currentTarget.find('option').each(function () {
				let current_value = $(this).val();
				if (current_value === value) {
					if ($(this).attr('data-option-target-multi')) {
						let target_ids = $(this).data('option-target-multi');
						target_ids = target_ids.toString().split(" ");
						target_ids.forEach(function (target_id) {
							let target = $('[data-collapse="' + target_id + '"]');
							target.slideDown(350).removeClass('mActive');
						});
					} else {
						let target_id = $(this).data('option-target');
						let target = $('[data-collapse="' + target_id + '"]');
						target.slideDown(350).removeClass('mActive');
					}
				}
			});
		});
	});
	function target_close(close_id, target_id) {
		$('body').find('[data-close="' + close_id + '"]:not([data-collapse="' + target_id + '"])').slideUp(250);
		return true;
	}
	function target_collapse(target, $this) {
		if ($this.is('[type="radio"]')) {
			target.slideDown(250);
		} else {
			target.each(function () {
				$(this).slideToggle(250).toggleClass('mActive');
			});
		}
		return true;
	}

}(jQuery));
//=====================================================================Group Check box==========//
(function ($) {
	"use strict";
	$(document).on('click', '.mpStyle .groupCheckBox .customCheckboxLabel', function () {
		let parent = $(this).closest('.groupCheckBox');
		let value = '';
		let separator = ',';
		parent.find(' input[type="checkbox"]').each(function () {
			if ($(this).is(":checked")) {
				let currentValue = $(this).attr('data-checked');
				value = value + (value ? separator : '') + currentValue;
			}
		}).promise().done(function () {
			parent.find('input[type="hidden"]').val(value);
		});
	});

}(jQuery));


//==============================================================Modal / Popup==========//
(function ($) {
	"use strict";
	$(document).on('click', '.mpStyle [data-target-popup]', function () {
		let target = $(this).attr('data-active-popup', '').data('target-popup');
		$('body').addClass('noScroll').find('[data-popup="' + target + '"]').addClass('in').promise().done(function () {
			loadBgImage();
			return true;
		});
	});
	$(document).on('click', 'div.mpPopup  .popupClose', function () {
		$(this).closest('[data-popup]').removeClass('in');
		$('body').removeClass('noScroll').find('[data-active-popup]').removeAttr('data-active-popup');
		return true;
	});
}(jQuery));
//==============================================================Slider=================//
(function ($) {
	"use strict";
	//=================initial call============//
	$('.superSlider').each(function () {
		sliderItemActive($(this), 1);
	});
	//==============Slider===================//
	$(document).on('click', '.superSlider [data-slide-target]', function () {
		if (!$(this).hasClass('activeSlide')) {
			let activeItem = $(this).data('slide-target');
			let parent = $(this).closest('.superSlider');
			sliderItemActive(parent, activeItem);
			parent.find('[data-slide-target]').removeClass('activeSlide');
			$(this).addClass('activeSlide');
		}
	});
	$(document).on('click', '.superSlider .iconIndicator', function () {
		let parent = $(this).closest('.superSlider');
		let activeItem = parseInt(parent.find('.sliderAllItem').first().find('.sliderItem.activeSlide').data('slide-index'));
		if ($(this).hasClass('nextItem')) {
			++activeItem;
		} else {
			--activeItem;
		}
		sliderItemActive(parent, activeItem);
	});
	function sliderItemActive(parent, activeItem) {
		let itemLength = parent.find('.sliderAllItem').first().find('[data-slide-index]').length;
		let currentItem = getSliderItem(parent, activeItem);
		let activeCurrent = parseInt(parent.find('.sliderAllItem').first().find('.sliderItem.activeSlide').data('slide-index'));
		let i = 1;
		for (i; i <= itemLength; i++) {
			let target = parent.find('.sliderAllItem').first().find('[data-slide-index="' + i + '"]').first();
			if (i < currentItem && currentItem !== 1) {
				sliderClassControl(target, currentItem, activeCurrent, 'prevSlider', 'nextSlider');
			}
			if (i === currentItem) {
				parent.find('.sliderAllItem').first().find('[data-slide-index="' + currentItem + '"]').removeClass('prevSlider nextSlider').addClass('activeSlide');
			}
			if (i > currentItem && currentItem !== itemLength) {
				sliderClassControl(target, currentItem, activeCurrent, 'nextSlider', 'prevSlider');
			}
			if (i === itemLength && itemLength > 1) {
				if (currentItem === 1) {
					target = parent.find('.sliderAllItem').first().find('[data-slide-index="' + itemLength + '"]');
					sliderClassControl(target, currentItem, activeCurrent, 'prevSlider', 'nextSlider');
				}
				if (currentItem === itemLength) {
					target = parent.find('.sliderAllItem').first().find('[data-slide-index="1"]');
					sliderClassControl(target, currentItem, activeCurrent, 'nextSlider', 'prevSlider');
				}
			}
		}
	}
	function sliderClassControl(target, currentItem, activeCurrent, add_class, remove_class) {
		if (target.hasClass('activeSlide')) {
			if (currentItem > activeCurrent) {
				target.removeClass('activeSlide').addClass(add_class);
			} else {
				target.removeClass('activeSlide').removeClass(remove_class).addClass(add_class);
			}
		} else if (target.hasClass(remove_class)) {
			target.removeClass(remove_class).delay(600).addClass(add_class);
		} else {
			if (!target.hasClass(add_class)) {
				target.addClass(add_class);
			}
		}
	}
	function getSliderItem(parent, activeItem) {
		let itemLength = parent.find('.sliderAllItem').first().find('[data-slide-index]').length;
		activeItem = activeItem < 1 ? itemLength : activeItem;
		activeItem = activeItem > itemLength ? 1 : activeItem;
		return activeItem;
	}
	//popup
	$(document).on('click', '.superSlider [data-target-popup]', function () {
		let target = $(this).data('target-popup');
		let activeItem = $(this).data('slide-index');
		$('body').addClass('noScroll').find('[data-popup="' + target + '"]').addClass('in').promise().done(function () {
			sliderItemActive($(this), activeItem);
			loadBgImage();
		});
	});
	$(document).on('click', '.superSlider .popupClose', function () {
		$(this).closest('[data-popup]').removeClass('in');
		$('body').removeClass('noScroll');
	});
}(jQuery));
