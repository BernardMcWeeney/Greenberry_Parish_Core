/**
 * Parish Core - Hero Slider
 * Lightweight, performant slider with touch support
 */
(function () {
	'use strict';

	function ParishSlider(element) {
		this.slider = element;
		this.track = element.querySelector('.parish-slider__track');
		this.slides = Array.prototype.slice.call(element.querySelectorAll('.parish-slider__slide'));
		this.dots = Array.prototype.slice.call(element.querySelectorAll('.parish-slider__dot'));
		this.prevBtn = element.querySelector('.parish-slider__arrow--prev');
		this.nextBtn = element.querySelector('.parish-slider__arrow--next');

		this.currentIndex = 0;
		this.slideCount = this.slides.length;
		this.isAnimating = false;
		this.autoplayTimer = null;
		this.touchStartX = 0;
		this.touchEndX = 0;

		// Settings from data attributes
		this.autoplay = element.dataset.autoplay === 'true';
		this.autoplaySpeed = parseInt(element.dataset.speed, 10) || 5000;
		this.transitionSpeed = parseInt(element.dataset.transition, 10) || 1000;
		this.pauseOnHover = element.dataset.pauseHover === 'true';

		this.init();
	}

	ParishSlider.prototype.init = function () {
		if (this.slideCount <= 1) {
			return;
		}

		this.bindEvents();
		this.preloadImages();

		if (this.autoplay) {
			this.startAutoplay();
		}

		// Set transition speed via CSS custom property
		this.slider.style.setProperty('--slider-transition-speed', this.transitionSpeed + 'ms');
	};

	ParishSlider.prototype.bindEvents = function () {
		var self = this;

		// Arrow navigation
		if (this.prevBtn) {
			this.prevBtn.addEventListener('click', function () {
				self.prev();
			});
		}
		if (this.nextBtn) {
			this.nextBtn.addEventListener('click', function () {
				self.next();
			});
		}

		// Dot navigation
		this.dots.forEach(function (dot, index) {
			dot.addEventListener('click', function () {
				self.goTo(index);
			});
		});

		// Keyboard navigation
		this.slider.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowLeft') {
				self.prev();
			} else if (e.key === 'ArrowRight') {
				self.next();
			}
		});

		// Touch events
		this.slider.addEventListener(
			'touchstart',
			function (e) {
				self.handleTouchStart(e);
			},
			{ passive: true }
		);
		this.slider.addEventListener(
			'touchend',
			function (e) {
				self.handleTouchEnd(e);
			},
			{ passive: true }
		);

		// Pause on hover
		if (this.pauseOnHover && this.autoplay) {
			this.slider.addEventListener('mouseenter', function () {
				self.stopAutoplay();
			});
			this.slider.addEventListener('mouseleave', function () {
				self.startAutoplay();
			});
		}

		// Pause when not visible (Intersection Observer)
		this.observeVisibility();

		// Handle reduced motion preference
		this.handleReducedMotion();
	};

	ParishSlider.prototype.preloadImages = function () {
		// Preload next slide's image for smoother transitions
		this.slides.forEach(function (slide) {
			var imageDiv = slide.querySelector('.parish-slider__image');
			if (imageDiv) {
				var bgImage = imageDiv.style.backgroundImage;
				if (bgImage && bgImage !== 'none') {
					var url = bgImage.replace(/url\(['"]?([^'"]+)['"]?\)/, '$1');
					var img = new Image();
					img.src = url;
				}
			}
		});
	};

	ParishSlider.prototype.observeVisibility = function () {
		if (!('IntersectionObserver' in window)) {
			return;
		}

		var self = this;
		var observer = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						if (self.autoplay) {
							self.startAutoplay();
						}
					} else {
						self.stopAutoplay();
					}
				});
			},
			{ threshold: 0.5 }
		);

		observer.observe(this.slider);
	};

	ParishSlider.prototype.handleReducedMotion = function () {
		var self = this;
		var mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');

		if (mediaQuery.matches) {
			this.autoplay = false;
			this.stopAutoplay();
			this.slider.style.setProperty('--slider-transition-speed', '0ms');
		}

		mediaQuery.addEventListener('change', function (e) {
			if (e.matches) {
				self.autoplay = false;
				self.stopAutoplay();
				self.slider.style.setProperty('--slider-transition-speed', '0ms');
			}
		});
	};

	ParishSlider.prototype.handleTouchStart = function (e) {
		this.touchStartX = e.changedTouches[0].screenX;
	};

	ParishSlider.prototype.handleTouchEnd = function (e) {
		this.touchEndX = e.changedTouches[0].screenX;
		this.handleSwipe();
	};

	ParishSlider.prototype.handleSwipe = function () {
		var threshold = 50;
		var diff = this.touchStartX - this.touchEndX;

		if (Math.abs(diff) < threshold) {
			return;
		}

		if (diff > 0) {
			this.next();
		} else {
			this.prev();
		}
	};

	ParishSlider.prototype.prev = function () {
		if (this.isAnimating) {
			return;
		}

		var newIndex = (this.currentIndex - 1 + this.slideCount) % this.slideCount;
		this.goTo(newIndex);
	};

	ParishSlider.prototype.next = function () {
		if (this.isAnimating) {
			return;
		}

		var newIndex = (this.currentIndex + 1) % this.slideCount;
		this.goTo(newIndex);
	};

	ParishSlider.prototype.goTo = function (index) {
		if (this.isAnimating || index === this.currentIndex) {
			return;
		}

		var self = this;
		this.isAnimating = true;

		// Update slides
		this.slides[this.currentIndex].classList.remove('is-active');
		this.slides[index].classList.add('is-active');

		// Update dots
		if (this.dots.length) {
			this.dots[this.currentIndex].classList.remove('is-active');
			this.dots[index].classList.add('is-active');
		}

		this.currentIndex = index;

		// Reset animation lock after transition
		setTimeout(function () {
			self.isAnimating = false;
		}, this.transitionSpeed);

		// Restart autoplay timer
		if (this.autoplay) {
			this.stopAutoplay();
			this.startAutoplay();
		}

		// Dispatch event for external listeners
		var event;
		if (typeof CustomEvent === 'function') {
			event = new CustomEvent('slidechange', {
				detail: { index: this.currentIndex },
			});
		} else {
			event = document.createEvent('CustomEvent');
			event.initCustomEvent('slidechange', true, true, { index: this.currentIndex });
		}
		this.slider.dispatchEvent(event);
	};

	ParishSlider.prototype.startAutoplay = function () {
		if (this.autoplayTimer) {
			return;
		}

		var self = this;
		this.autoplayTimer = setInterval(function () {
			self.next();
		}, this.autoplaySpeed);
	};

	ParishSlider.prototype.stopAutoplay = function () {
		if (this.autoplayTimer) {
			clearInterval(this.autoplayTimer);
			this.autoplayTimer = null;
		}
	};

	ParishSlider.prototype.destroy = function () {
		this.stopAutoplay();
	};

	// Initialize all sliders on the page
	function initSliders() {
		var sliders = document.querySelectorAll('.parish-slider');
		sliders.forEach(function (el) {
			if (!el.parishSlider) {
				el.parishSlider = new ParishSlider(el);
			}
		});
	}

	// Initialize on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initSliders);
	} else {
		initSliders();
	}

	// Expose for external use
	window.ParishSlider = ParishSlider;
})();
