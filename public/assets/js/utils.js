/**
 * TDW Laboratory - Utility Functions
 * Helpers, Mobile Menu, Password Toggle, Form Validation
 */

$(document).ready(function () {
    // ====================================
    // Password Toggle Functionality
    // ====================================
    $("#togglePassword").on("click", function () {
        const passwordField = $("#password");
        const btn = $(this);
        const img = btn.find("img.eye-icon");
        const showSrc = btn.data("show");
        const hideSrc = btn.data("hide");

        if (passwordField.attr("type") === "password") {
            passwordField.attr("type", "text");
            img.attr("src", hideSrc);
            btn.attr("aria-label", "Hide password");
            img.attr("alt", "Hide password");
        } else {
            passwordField.attr("type", "password");
            img.attr("src", showSrc);
            btn.attr("aria-label", "Show password");
            img.attr("alt", "Show password");
        }
    });

    // ====================================
    // Form Validation (Login, etc.)
    // ====================================
    $("form").on("submit", function (e) {
        const form = $(this);
        let isValid = true;

        // Check required fields
        form.find("[required]").each(function () {
            if ($(this).val().trim() === "") {
                isValid = false;
                $(this).css("border-color", "#e74c3c");
            } else {
                $(this).css("border-color", "#dee2e6");
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert(LANG.fill_required_fields);
        }
    });

    // ====================================
    // Mobile Menu Toggle
    // ====================================
    $(".mobile-menu-toggle").on("click", function () {
        $(".nav-menu").toggleClass("active");
    });

    // ====================================
    // Smooth Scroll to Section
    // ====================================
    $('a.scroll-to-section[href^="#"]').on("click", function (e) {
        e.preventDefault();
        const target = $(this).attr("href");
        const $target = $(target);

        if ($target.length) {
            $("html, body").animate(
                {
                    scrollTop: $target.offset().top - 80, // 80px offset for header
                },
                800
            );
        }
    });
});

// ====================================
// Global Helper Functions
// ====================================

/**
 * Debounce function to limit rapid function calls
 */
function debounce(fn, wait) {
    let t = null;
    return function () {
        const args = arguments;
        clearTimeout(t);
        t = setTimeout(function () {
            fn.apply(null, args);
        }, wait);
    };
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    if (text === null || text === undefined) return "";
    text = String(text);
    const map = {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;",
    };
    return text.replace(/[&<>"']/g, function (m) {
        return map[m];
    });
}

/**
 * Truncate text to specified length
 */
function truncate(text, length) {
    if (!text) return "";
    if (text.length <= length) return text;
    return text.substr(0, length) + "...";
}
