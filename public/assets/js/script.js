/**
 * TDW Laboratory - Main JavaScript
 * jQuery-based interactions
 */

$(document).ready(function () {
  // ====================================
  // Slideshow functionality
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
  // Project AJAX Filtering
  // ====================================
  $("#filter-domain, #filter-status").on("change", function () {
    const domain = $("#filter-domain").val();
    const status = $("#filter-status").val();

    // Show loading state
    $("#projects-grid").html('<div class="loading">Chargement...</div>');

    // AJAX request to filter projects
    $.ajax({
      url: "index.php",
      method: "GET",
      data: {
        controller: "Project",
        action: "filter",
        domain: domain,
        status: status,
      },
      dataType: "json",
      success: function (response) {
        if (response.success && response.data.length > 0) {
          renderProjects(response.data);
        } else {
          $("#projects-grid").html(
            '<div class="no-results">Aucun projet trouvé</div>'
          );
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", error);
        $("#projects-grid").html(
          '<div class="no-results">Erreur lors du chargement des projets</div>'
        );
      },
    });
  });

  // Render projects from JSON data
  function renderProjects(projects) {
    let html = "";

    projects.forEach(function (project) {
      const statusClass =
        project.statut === "en cours"
          ? "badge-primary"
          : project.statut === "terminé"
          ? "badge-success"
          : "badge-warning";

      const imageUrl = project.image_url
        ? project.image_url
        : "assets/img/project-placeholder.jpg";

      html += `
                <div class="card">
                    <img src="${imageUrl}" alt="${escapeHtml(
        project.titre
      )}" onerror="this.src='assets/img/project-placeholder.jpg'">
                    <div class="card-body">
                        <h3 class="card-title">${escapeHtml(project.titre)}</h3>
                        <div class="card-meta">
                            <span><strong>Domaine:</strong> ${escapeHtml(
                              project.domaine
                            )}</span>
                            <span><strong>Responsable:</strong> ${escapeHtml(
                              project.responsable_nom || "N/A"
                            )}</span>
                        </div>
                        <div class="mb-2">
                            <span class="badge ${statusClass}">${escapeHtml(
        project.statut
      )}</span>
                        </div>
                        <p class="card-text">${escapeHtml(
                          truncate(project.description, 100)
                        )}</p>
                        <a href="index.php?controller=Project&action=detail&id=${
                          project.id_project
                        }" class="btn btn-primary">Voir les détails</a>
                    </div>
                </div>
            `;
    });

    $("#projects-grid").html(html);
  }

  // Helper: Escape HTML to prevent XSS
  function escapeHtml(text) {
    if (!text) return "";
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

  // Helper: Truncate text
  function truncate(text, length) {
    if (!text) return "";
    if (text.length <= length) return text;
    return text.substr(0, length) + "...";
  }

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
      alert("Veuillez remplir tous les champs obligatoires");
    }
  });

  // ====================================
  // Mobile Menu Toggle (if needed)
  // ====================================
  $(".mobile-menu-toggle").on("click", function () {
    $(".nav-menu").toggleClass("active");
  });
});
