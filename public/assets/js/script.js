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
  $("#filter-domain, #filter-status, #filter-supervisor").on(
    "change",
    function () {
      const domain = $("#filter-domain").val();
      const status = $("#filter-status").val();
      const supervisor = $("#filter-supervisor").length
        ? $("#filter-supervisor").val()
        : "all";

      // Show loading state
      $("#projects-grid").html('<div class="loading">Chargement...</div>');

      // AJAX request to filter projects
      $.ajax({
        url: BASE_URL + "index.php",
        method: "GET",
        data: {
          controller: "Project",
          action: "filter",
          domain: domain,
          status: status,
          supervisor: supervisor,
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
    }
  );

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
        : BASE_URL + "assets/img/project-placeholder.jpg";

      const funding = project.type_financement
        ? escapeHtml(project.type_financement)
        : "N/A";

      const membersCount =
        project.membres_count !== undefined && project.membres_count !== null
          ? parseInt(project.membres_count, 10)
          : null;

      const membersLabel =
        membersCount !== null && !Number.isNaN(membersCount)
          ? membersCount + " membre" + (membersCount > 1 ? "s" : "")
          : "N/A";

      const managerName =
        project.responsable_prenom && project.responsable_nom
          ? project.responsable_prenom + " " + project.responsable_nom
          : project.responsable_nom || "N/A";

      html += `
                <div class="card">
                    <img src="${imageUrl}" alt="${escapeHtml(
        project.titre
      )}" onerror="this.src='${BASE_URL}assets/img/project-placeholder.jpg'">
                    <div class="card-body">
                        <h3 class="card-title">${escapeHtml(project.titre)}</h3>
                        <div class="card-meta">
                            <span><strong>Domaine:</strong> ${escapeHtml(
                              project.domaine
                            )}</span>
                            <span><strong>Responsable:</strong> ${escapeHtml(
                              managerName
                            )}</span>
                            <span><strong>Financement:</strong> ${funding}</span>
                            <span><strong>Membres:</strong> ${escapeHtml(
                              membersLabel
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
                        <a href="${BASE_URL}index.php?controller=Project&action=detail&id=${
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

  // ====================================
  // Homepage Upcoming Events Pagination
  // ====================================
  function updateEventsPagerState(page, totalPages) {
    if (!$("#events-prev").length || !$("#events-next").length) return;
    $("#events-page-label").text(page);
    $("#events-total-label").text(totalPages);
    $("#events-prev").prop("disabled", page <= 1);
    $("#events-next").prop("disabled", page >= totalPages);
  }

  function renderUpcomingEvents(events) {
    let html = "";

    events.forEach(function (event) {
      const imageUrl = event.image_url
        ? BASE_URL + escapeHtml(event.image_url)
        : BASE_URL + "assets/img/event-placeholder.jpg";

      const when = event.date_event
        ? new Date(String(event.date_event).replace(" ", "T"))
        : null;
      const dateLabel = when
        ? String(when.getDate()).padStart(2, "0") +
          "/" +
          String(when.getMonth() + 1).padStart(2, "0") +
          "/" +
          when.getFullYear() +
          " " +
          String(when.getHours()).padStart(2, "0") +
          ":" +
          String(when.getMinutes()).padStart(2, "0")
        : "";

      html += `
        <div class="card">
          <img src="${imageUrl}" alt="${escapeHtml(
        event.titre
      )}" onerror="this.src='${BASE_URL}assets/img/event-placeholder.jpg'">
          <div class="card-body">
            <h3 class="card-title">${escapeHtml(event.titre)}</h3>
            <div class="card-meta">
              <span><strong>Date:</strong> ${escapeHtml(dateLabel)}</span>
              ${
                event.lieu
                  ? `<span><strong>Lieu:</strong> ${escapeHtml(
                      event.lieu
                    )}</span>`
                  : ""
              }
            </div>
            <div class="mb-2">
              <span class="badge badge-primary">${escapeHtml(event.type)}</span>
            </div>
            <p class="card-text">${escapeHtml(
              truncate(event.description, 120)
            )}...</p>
            <a href="${BASE_URL}index.php?controller=Event&action=view&id=${
        event.id_event
      }" class="btn btn-primary">Lire la suite</a>
          </div>
        </div>
      `;
    });

    $("#upcoming-events-grid").html(html);
  }

  function loadUpcomingEventsPage(targetPage) {
    const grid = $("#upcoming-events-grid");
    if (!grid.length) return;

    // Get all events from data attribute
    let allEvents = grid.data("all-events");

    // Parse JSON if it's a string
    if (typeof allEvents === "string") {
      try {
        allEvents = JSON.parse(allEvents);
      } catch (e) {
        console.error("Failed to parse events data:", e);
        return;
      }
    }

    if (!allEvents || !Array.isArray(allEvents)) {
      console.error("No events data found or invalid format");
      return;
    }

    const perPage = parseInt(grid.data("per-page"), 10) || 3;
    const totalEvents = allEvents.length;
    const totalPages = Math.ceil(totalEvents / perPage);

    // Validate target page
    const page = Math.max(1, Math.min(targetPage, totalPages));

    // Calculate slice indices
    const startIndex = (page - 1) * perPage;
    const endIndex = startIndex + perPage;
    const pageEvents = allEvents.slice(startIndex, endIndex);

    // Disable buttons during transition
    $("#events-prev, #events-next").prop("disabled", true);

    // Fade out
    grid.addClass("fade-out");

    // Wait for fade-out, then update content
    setTimeout(function () {
      if (pageEvents.length > 0) {
        renderUpcomingEvents(pageEvents);
      } else {
        grid.html('<div class="no-results">Aucun événement trouvé</div>');
      }

      // Update data attributes
      grid.data("page", page);
      grid.data("total-pages", totalPages);

      // Fade in
      setTimeout(function () {
        grid.removeClass("fade-out");
        updateEventsPagerState(page, totalPages);
      }, 50);
    }, 50);
  }

  if ($("#upcoming-events-grid").length) {
    const initialPage =
      parseInt($("#upcoming-events-grid").data("page"), 10) || 1;
    const initialTotalPages =
      parseInt($("#upcoming-events-grid").data("total-pages"), 10) || 1;
    updateEventsPagerState(initialPage, initialTotalPages);

    $("#events-prev").on("click", function (e) {
      e.preventDefault();
      if ($(this).prop("disabled")) return;

      const current =
        parseInt($("#upcoming-events-grid").data("page"), 10) || 1;
      const newPage = Math.max(1, current - 1);

      if (newPage !== current) {
        loadUpcomingEventsPage(newPage);
      }
    });

    $("#events-next").on("click", function (e) {
      e.preventDefault();
      if ($(this).prop("disabled")) return;

      const current =
        parseInt($("#upcoming-events-grid").data("page"), 10) || 1;
      const total =
        parseInt($("#upcoming-events-grid").data("total-pages"), 10) || 1;
      const newPage = Math.min(total, current + 1);

      if (newPage !== current) {
        loadUpcomingEventsPage(newPage);
      }
    });
  }

  // ====================================
  // Teams Filters (client-side)
  // ====================================
  function applyTeamFilters() {
    const teamId = $("#team-filter-team").val() || "all";
    const grade = $("#team-filter-grade").val() || "all";
    const sort = $("#team-sort").val() || "name";

    $(".team-section").each(function () {
      const section = $(this);
      const sectionTeamId = String(section.data("team-id") || "");

      // Team visibility
      if (teamId !== "all" && sectionTeamId !== teamId) {
        section.hide();
        return;
      }
      section.show();

      // Member filtering
      const members = section.find(".member-card");
      members.each(function () {
        const card = $(this);
        const memberGrade = String(card.data("grade") || "");

        const gradeOk = grade === "all" ? true : memberGrade === grade;
        card.toggle(gradeOk);
      });

      // Sorting (visible members only)
      const container = section.find(".team-members");
      const visibleCards = container.find(".member-card:visible").get();
      visibleCards.sort(function (a, b) {
        const an = String($(a).data("name") || "").toLowerCase();
        const bn = String($(b).data("name") || "").toLowerCase();
        if (sort === "name") return an.localeCompare(bn);

        const ag = String($(a).data("grade") || "").toLowerCase();
        const bg = String($(b).data("grade") || "").toLowerCase();
        return ag.localeCompare(bg) || an.localeCompare(bn);
      });
      container.append(visibleCards);
    });
  }

  if ($("#team-filter-team").length) {
    $("#team-filter-team, #team-filter-grade, #team-sort").on(
      "change",
      applyTeamFilters
    );
    applyTeamFilters();
  }

  // ====================================
  // Publications AJAX Filtering + Pagination
  // ====================================
  function updatePubPagerState(page, totalPages) {
    if (!$("#pub-prev").length || !$("#pub-next").length) return;
    $("#pub-page-label").text(page);
    $("#pub-total-label").text(totalPages);
    $("#pub-prev").prop("disabled", page <= 1);
    $("#pub-next").prop("disabled", page >= totalPages);
  }

  function renderPublications(pubs) {
    let html = "";

    pubs.forEach(function (pub) {
      const dateLabel = pub.date_publication
        ? new Date(pub.date_publication).toLocaleDateString("fr-FR")
        : "N/A";

      html += `
        <div class="card">
          <div class="card-body">
            <h3 class="card-title">${escapeHtml(pub.titre)}</h3>
            <div class="card-meta">
              <span><strong>Date:</strong> ${escapeHtml(dateLabel)}</span>
              <span><strong>Type:</strong> ${escapeHtml(pub.type)}</span>
              ${
                pub.domaine
                  ? `<span><strong>Domaine:</strong> ${escapeHtml(
                      pub.domaine
                    )}</span>`
                  : ""
              }
            </div>
            ${
              pub.auteurs
                ? `<p class="card-text"><strong>Auteurs:</strong> ${escapeHtml(
                    pub.auteurs
                  )}</p>`
                : ""
            }
            ${
              pub.doi
                ? `<p class="card-text"><strong>DOI:</strong> ${escapeHtml(
                    pub.doi
                  )}</p>`
                : ""
            }
            ${
              pub.resume
                ? `<p class="card-text"><strong>Résumé:</strong> ${escapeHtml(
                    truncate(pub.resume, 220)
                  )}...</p>`
                : ""
            }
            ${
              pub.lien_pdf
                ? `<a href="${escapeHtml(
                    pub.lien_pdf
                  )}" target="_blank" class="btn btn-primary">Télécharger PDF</a>`
                : ""
            }
          </div>
        </div>
      `;
    });

    $("#publications-grid").html(html);
  }

  function collectPubFilters() {
    const grid = $("#publications-grid");
    const team = grid.data("team");
    return {
      q: $("#pub-search").val() || "",
      year: $("#pub-filter-year").val() || "all",
      author: $("#pub-filter-author").val() || "all",
      type: $("#pub-filter-type").val() || "all",
      domain: $("#pub-filter-domain").val() || "all",
      sort: $("#pub-sort").val() || "date_desc",
      team: team || "all",
    };
  }

  function loadPublicationsPage(targetPage) {
    const grid = $("#publications-grid");
    if (!grid.length) return;

    const perPage = parseInt(grid.data("per-page"), 10) || 6;
    const filters = collectPubFilters();

    grid.html('<div class="loading">Chargement...</div>');

    $.ajax({
      url: BASE_URL + "index.php",
      method: "GET",
      data: {
        controller: "Publication",
        action: "filter",
        page: targetPage,
        perPage: perPage,
        q: filters.q,
        year: filters.year,
        author: filters.author,
        type: filters.type,
        domain: filters.domain,
        sort: filters.sort,
        team: filters.team,
      },
      dataType: "json",
      success: function (response) {
        if (!response || !response.success) {
          grid.html(
            '<div class="no-results">Erreur lors du chargement des publications</div>'
          );
          return;
        }

        const pubs = response.data || [];
        const pagination = response.pagination || {};
        const page = pagination.page || targetPage;
        const totalPages = pagination.totalPages || 1;

        grid.data("page", page);
        grid.data("total-pages", totalPages);

        if (pubs.length > 0) {
          renderPublications(pubs);
        } else {
          grid.html('<div class="no-results">Aucune publication trouvée</div>');
        }

        updatePubPagerState(page, totalPages);
      },
      error: function () {
        grid.html(
          '<div class="no-results">Erreur lors du chargement des publications</div>'
        );
      },
    });
  }

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

  if ($("#publications-grid").length) {
    const initialPage = parseInt($("#publications-grid").data("page"), 10) || 1;
    const initialTotalPages =
      parseInt($("#publications-grid").data("total-pages"), 10) || 1;
    updatePubPagerState(initialPage, initialTotalPages);

    const reload = function () {
      loadPublicationsPage(1);
    };

    $(
      "#pub-filter-year, #pub-filter-author, #pub-filter-type, #pub-filter-domain, #pub-sort"
    ).on("change", reload);

    $("#pub-search").on("input", debounce(reload, 300));

    $("#pub-prev").on("click", function () {
      const current = parseInt($("#publications-grid").data("page"), 10) || 1;
      loadPublicationsPage(Math.max(1, current - 1));
    });

    $("#pub-next").on("click", function () {
      const current = parseInt($("#publications-grid").data("page"), 10) || 1;
      const total =
        parseInt($("#publications-grid").data("total-pages"), 10) || 1;
      loadPublicationsPage(Math.min(total, current + 1));
    });
  }

  // ====================================
  // Presentation Page - Collapsible Introduction Toggle
  // ====================================
  if ($("#toggleIntroBtn").length) {
    $("#toggleIntroBtn").on("click", function (e) {
      e.preventDefault();
      const wrapper = $(".intro-content-wrapper");
      const btn = $(this);
      const text = btn.find(".toggle-text");

      if (wrapper.hasClass("collapsed")) {
        wrapper.removeClass("collapsed").addClass("expanded");
        text.text("Voir moins de détails");
        btn.addClass("expanded");
      } else {
        wrapper.removeClass("expanded").addClass("collapsed");
        text.text("Voir plus de détails");
        btn.removeClass("expanded");
      }
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
