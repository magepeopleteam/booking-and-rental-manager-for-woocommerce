//==========Slider=================//
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
		//let per_page_item = parent.data('show-item');
		//per_page_item = per_page_item > 1 ? per_page_item : 1;
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
//=========================================//