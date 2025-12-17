/**
 * TDW Laboratory - Carousel Functions
 * Homepage Slideshow & Partner Carousel
 */

$(document).ready(function () {
    // ====================================
    // Homepage Slideshow functionality
    // ====================================
    let currentSlide = 0;
    const slides = $(".slide");
    const slideCount = slides.length;

    if (slideCount > 0) {
        // Show first slide
        slides.eq(0).addClass("active");

        // Auto-advance slideshow every 5 seconds
        setInterval(function () {
            nextSlide();
        }, 5000);

        // Next slide function
        function nextSlide() {
            slides.eq(currentSlide).removeClass("active");
            currentSlide = (currentSlide + 1) % slideCount;
            slides.eq(currentSlide).addClass("active");
        }

        // Previous slide function
        function prevSlide() {
            slides.eq(currentSlide).removeClass("active");
            currentSlide = (currentSlide - 1 + slideCount) % slideCount;
            slides.eq(currentSlide).addClass("active");
        }

        // Manual controls
        $(".slide-next").on("click", function () {
            nextSlide();
        });

        $(".slide-prev").on("click", function () {
            prevSlide();
        });
    }

    // ====================================
    // Partners Carousel - Infinite Loop
    // ====================================
    (function initPartnersCarousel() {
        const carousel = $("#partners-carousel");
        const prevBtn = $("#partners-prev");
        const nextBtn = $("#partners-next");

        if (!carousel.length || !prevBtn.length || !nextBtn.length) return;

        const originalItems = carousel.find(".partner-carousel-item").toArray();
        const totalItems = originalItems.length;
        const itemsPerView = 3;
        let currentIndex = itemsPerView; // Start at the first real item (after clones)
        let isTransitioning = false;
        let autoPlayInterval;

        if (totalItems <= itemsPerView) {
            // Hide arrows if not enough items
            prevBtn.hide();
            nextBtn.hide();
            return;
        }

        // Clone items for infinite loop effect
        // Clone last itemsPerView items and prepend
        for (let i = totalItems - itemsPerView; i < totalItems; i++) {
            const clone = $(originalItems[i]).clone();
            carousel.prepend(clone);
        }

        // Clone first itemsPerView items and append
        for (let i = 0; i < itemsPerView; i++) {
            const clone = $(originalItems[i]).clone();
            carousel.append(clone);
        }

        // Update carousel position with or without transition
        function updateCarousel(withTransition) {
            if (withTransition) {
                carousel.css("transition", "transform 0.5s ease-in-out");
            } else {
                carousel.css("transition", "none");
            }
            const offset = -(currentIndex * (100 / itemsPerView));
            carousel.css("transform", "translateX(" + offset + "%)");
        }

        // Initialize position
        updateCarousel(false);

        function nextSlide() {
            if (isTransitioning) return;
            isTransitioning = true;
            currentIndex++;
            updateCarousel(true);

            // Check if we're at a clone, reset to real item
            setTimeout(function () {
                if (currentIndex >= totalItems + itemsPerView) {
                    currentIndex = itemsPerView;
                    updateCarousel(false);
                }
                isTransitioning = false;
            }, 500); // Match transition duration
        }

        function prevSlide() {
            if (isTransitioning) return;
            isTransitioning = true;
            currentIndex--;
            updateCarousel(true);

            // Check if we're at a clone, reset to real item
            setTimeout(function () {
                if (currentIndex < itemsPerView) {
                    currentIndex = totalItems + itemsPerView - 1;
                    updateCarousel(false);
                }
                isTransitioning = false;
            }, 500); // Match transition duration
        }

        function startAutoPlay() {
            autoPlayInterval = setInterval(nextSlide, 3000); // Change every 3 seconds
        }

        function stopAutoPlay() {
            clearInterval(autoPlayInterval);
        }

        // Event listeners
        nextBtn.on("click", function () {
            stopAutoPlay();
            nextSlide();
            startAutoPlay();
        });

        prevBtn.on("click", function () {
            stopAutoPlay();
            prevSlide();
            startAutoPlay();
        });

        // Pause on hover
        carousel.on("mouseenter", stopAutoPlay);
        carousel.on("mouseleave", startAutoPlay);

        // Start auto-play
        startAutoPlay();
    })();
});
