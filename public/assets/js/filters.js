/**
 * TDW Laboratory - Filter Functions
 * AJAX Filtering, Pagination, Project/Publication/Team/Members Filters
 */

$(document).ready(function () {
    // ====================================
    // Generic Pagination State Management
    // ====================================
    function updatePagerState(
        prevBtnId,
        nextBtnId,
        pageLabelId,
        totalLabelId,
        page,
        totalPages
    ) {
        const prevBtn = $(prevBtnId);
        const nextBtn = $(nextBtnId);
        if (!prevBtn.length || !nextBtn.length) return;

        $(pageLabelId).text(page);
        $(totalLabelId).text(totalPages);
        prevBtn.prop("disabled", page <= 1);
        nextBtn.prop("disabled", page >= totalPages);
    }

    /**
     * Generic function to load paginated data via AJAX
     */
    function loadPaginatedData(options) {
        const {
            url,
            data,
            container,
            renderCallback,
            pagerCallback,
            errorMessage,
            emptyMessage,
        } = options;

        container.html('<div class="loading">' + LANG.loading + "</div>");

        $.ajax({
            url: url,
            method: "GET",
            data: data,
            dataType: "json",
            success: function (response) {
                if (!response || !response.success) {
                    container.html(
                        '<div class="no-results">' +
                        (errorMessage || LANG.error_loading_projects) +
                        "</div>"
                    );
                    return;
                }

                const items = response.data || [];
                const pagination = response.pagination || {};
                const page = pagination.page || data.page;
                const totalPages = pagination.totalPages || 1;

                container.data("page", page);
                container.data("total-pages", totalPages);

                if (items.length > 0) {
                    renderCallback(items);
                } else {
                    container.html(
                        '<div class="no-results">' +
                        (emptyMessage || LANG.no_projects_found) +
                        "</div>"
                    );
                }

                if (pagerCallback) {
                    pagerCallback(page, totalPages);
                }
            },
            error: function () {
                container.html(
                    '<div class="no-results">' +
                    (errorMessage || LANG.error_loading_projects) +
                    "</div>"
                );
            },
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
            $("#projects-grid").html(
                '<div class="loading">' + LANG.loading + "</div>"
            );

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
                            '<div class="no-results">' + LANG.no_projects_found + "</div>"
                        );
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", error);
                    $("#projects-grid").html(
                        '<div class="no-results">' + LANG.error_loading_projects + "</div>"
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
                    ? membersCount +
                    " " +
                    (membersCount > 1 ? LANG.member_plural : LANG.member_singular)
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
                            <span><strong>${LANG.project_domain
                }:</strong> ${escapeHtml(project.domaine)}</span>
                            <span><strong>${LANG.project_manager
                }:</strong> ${escapeHtml(managerName)}</span>
                            <span><strong>${LANG.project_funding
                }:</strong> ${funding}</span>
                            <span><strong>${LANG.project_members
                }:</strong> ${escapeHtml(membersLabel)}</span>
                        </div>
                        <div class="mb-2">
                            <span class="badge ${statusClass}">${escapeHtml(
                    project.statut
                )}</span>
                        </div>
                        <p class="card-text">${escapeHtml(
                    truncate(project.description, 100)
                )}</p>
                        <a href="${BASE_URL}index.php?controller=Project&action=detail&id=${project.id_project
                }" class="btn btn-primary">${LANG.view_details}</a>
                    </div>
                </div>
            `;
        });

        $("#projects-grid").html(html);
    }

    // ====================================
    // Upcoming Events Pagination
    // ====================================
    function updateEventsPagerState(page, totalPages) {
        updatePagerState(
            "#events-prev",
            "#events-next",
            "#events-page-label",
            "#events-total-label",
            page,
            totalPages
        );
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
              <span><strong>${LANG.event_date}:</strong> ${escapeHtml(
                dateLabel
            )}</span>
              ${event.lieu
                    ? `<span><strong>${LANG.event_location
                    }:</strong> ${escapeHtml(event.lieu)}</span>`
                    : ""
                }
            </div>
            <div class="mb-2">
              <span class="badge badge-primary">${escapeHtml(event.type)}</span>
            </div>
            <p class="card-text">${escapeHtml(
                    truncate(event.description, 120)
                )}...</p>
            <a href="${BASE_URL}index.php?controller=Event&action=detail&id=${event.id_event
                }" class="btn btn-primary">${LANG.read_more}</a>
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
        updatePagerState(
            "#pub-prev",
            "#pub-next",
            "#pub-page-label",
            "#pub-total-label",
            page,
            totalPages
        );
    }

    function renderPublications(pubs) {
        let html = "";

        pubs.forEach(function (pub) {
            const dateLabel = pub.date_publication
                ? new Date(pub.date_publication).toLocaleDateString("fr-FR")
                : '<span class="empty-field">N/A</span>';

            const authorsLabel = pub.auteurs
                ? escapeHtml(pub.auteurs)
                : '<span class="empty-field">Aucun auteur listé</span>';

            const resumeLabel = pub.resume
                ? escapeHtml(truncate(pub.resume, 250)) + "..."
                : '<span class="empty-field">Aucun résumé disponible.</span>';

            const doiLabel = pub.doi
                ? escapeHtml(pub.doi)
                : '<span class="empty-field">Non disponible</span>';

            // Download Button Logic
            let downloadBtn = "";
            if (pub.lien_pdf) {
                downloadBtn = `<a href="${escapeHtml(
                    pub.lien_pdf
                )}" target="_blank" class="btn-download">${LANG.pub_download}</a>`;
            } else {
                downloadBtn = `<span class="btn-download disabled">${LANG.pub_download} (Indisponible)</span>`;
            }

            // Domain Label - always show
            const domainLabel = pub.domaine
                ? escapeHtml(pub.domaine)
                : '<span class="empty-field">' +
                (LANG.not_specified || "Non spécifié") +
                "</span>";

            html += `
        <div class="document-item">
          <div class="doc-header">
            <h3 class="doc-title">${escapeHtml(pub.titre)}</h3>
            <span class="doc-type-badge">${escapeHtml(pub.type)}</span>
          </div>

          <div class="doc-meta-row">
            <span><strong>${LANG.pub_date}:</strong> ${dateLabel}</span>
            <span><strong>${LANG.project_domain}:</strong> ${domainLabel}</span>
          </div>

          <div class="doc-authors">
            <strong>${LANG.pub_authors}:</strong> ${authorsLabel}
          </div>

          <div class="doc-abstract">
            <strong>${LANG.pub_abstract}:</strong><br>
            ${resumeLabel}
          </div>

          <div class="doc-footer">
            <span class="doc-doi">DOI: ${doiLabel}</span>
            ${downloadBtn}
          </div>
        </div>
      `;
        });

        // Note: Use the ID 'publications-list' instead of 'publications-grid'
        $("#publications-list").html(html);
    }

    function collectPubFilters() {
        const grid = $("#publications-list");
        const team = grid.data("team");
        const projectFromData = grid.data("project");
        const projectFromFilter = $("#pub-filter-project").val();
        return {
            q: $("#pub-search").val() || "",
            year: $("#pub-filter-year").val() || "all",
            author: $("#pub-filter-author").val() || "all",
            type: $("#pub-filter-type").val() || "all",
            domain: $("#pub-filter-domain").val() || "all",
            sort: $("#pub-sort").val() || "date_desc",
            team: team || "all",
            project: projectFromFilter || projectFromData || "all",
        };
    }

    function loadPublicationsPage(targetPage) {
        const grid = $("#publications-list");
        if (!grid.length) return;

        const perPage = parseInt(grid.data("per-page"), 10) || 6;
        const filters = collectPubFilters();

        loadPaginatedData({
            url: BASE_URL + "index.php",
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
                project: filters.project,
            },
            container: grid,
            renderCallback: renderPublications,
            pagerCallback: updatePubPagerState,
            errorMessage: LANG.error_loading_publications,
            emptyMessage: LANG.no_publications_found,
        });
    }

    if ($("#publications-list").length) {
        const initialPage = parseInt($("#publications-list").data("page"), 10) || 1;
        const initialTotalPages =
            parseInt($("#publications-list").data("total-pages"), 10) || 1;
        updatePubPagerState(initialPage, initialTotalPages);

        const reload = function () {
            loadPublicationsPage(1);
        };

        $(
            "#pub-filter-year, #pub-filter-author, #pub-filter-type, #pub-filter-domain, #pub-filter-project, #pub-sort"
        ).on("change", reload);

        $("#pub-search").on("input", debounce(reload, 300));

        $("#pub-prev").on("click", function () {
            const current = parseInt($("#publications-list").data("page"), 10) || 1;
            loadPublicationsPage(Math.max(1, current - 1));
        });

        $("#pub-next").on("click", function () {
            const current = parseInt($("#publications-list").data("page"), 10) || 1;
            const total =
                parseInt($("#publications-list").data("total-pages"), 10) || 1;
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
                text.text(LANG.view_less_details);
                btn.addClass("expanded");
            } else {
                wrapper.removeClass("expanded").addClass("collapsed");
                text.text(LANG.view_more_details);
                btn.removeClass("expanded");
            }
        });
    }

    // ====================================
    // Members Directory - Filtering, Search, and Sorting
    // ====================================
    (function () {
        const searchInput = document.getElementById("member-search");
        const filterTeam = document.getElementById("filter-team");
        const filterGrade = document.getElementById("filter-grade");
        const sortColumnSelect = document.getElementById("sort-column");
        const sortOrderSelect = document.getElementById("sort-order");
        const resetBtn = document.getElementById("reset-filters");
        const membersTable = document.getElementById("members-table");
        const membersTbody = document.getElementById("members-tbody");
        const resultsCount = document.getElementById("results-count");
        const noResultsMsg = document.getElementById("no-results-message");

        if (!membersTable || !membersTbody) return;

        let allMembers = []; // Store all member data

        // Initialize: Extract all member data from table
        function initializeMembers() {
            const rows = Array.from(membersTbody.querySelectorAll(".member-row"));
            allMembers = rows
                .map((row) => {
                    try {
                        const data = JSON.parse(
                            row.getAttribute("data-member-data") || "{}"
                        );
                        return {
                            prenom: data.prenom || "",
                            nom: data.nom || "",
                            grade: data.grade || "",
                            team_nom: data.team_nom || null,
                            id_team: data.id_team || 0,
                            is_team_leader: data.is_team_leader || 0,
                            // For filtering/sorting
                            name_lower: (data.prenom + " " + data.nom).toLowerCase(),
                            team_name_lower: (data.team_nom || "sans équipe").toLowerCase(),
                            grade_lower: (data.grade || "").toLowerCase(),
                        };
                    } catch (e) {
                        console.error("Error parsing member data:", e);
                        return null;
                    }
                })
                .filter((m) => m !== null);
        }

        // Group members by team
        function groupByTeam(members) {
            const groups = {};
            members.forEach((member) => {
                const teamKey = member.team_nom || "Sans équipe";
                if (!groups[teamKey]) {
                    groups[teamKey] = [];
                }
                groups[teamKey].push(member);
            });
            return groups;
        }

        // Rebuild table with rowspan grouping or flat list
        function rebuildTable(members, sortColumn, sortDirection) {
            membersTbody.innerHTML = "";

            if (members.length === 0) {
                return;
            }

            if (sortColumn === "team") {
                const groupedMembers = groupByTeam(members);

                // Sort teams: named teams first (alphabetically), then "Sans équipe"
                // Respect sortDirection
                const teamNames = Object.keys(groupedMembers).sort((a, b) => {
                    if (a === "Sans équipe") return sortDirection === "asc" ? 1 : -1;
                    if (b === "Sans équipe") return sortDirection === "asc" ? -1 : 1;
                    const cmp = a.localeCompare(b, "fr");
                    return sortDirection === "asc" ? cmp : -cmp;
                });

                let rowIndex = 0; // Track actual visual row index for alternating colors

                teamNames.forEach((teamName) => {
                    const teamMembers = groupedMembers[teamName];
                    const memberCount = teamMembers.length;

                    teamMembers.forEach((member, index) => {
                        const tr = document.createElement("tr");
                        tr.className = "member-row";

                        // Apply alternating background color based on visual row index
                        const bgColor = rowIndex % 2 === 0 ? "white" : "var(--bg-light)";

                        // Team cell (only for first member or all "Sans équipe" members)
                        if (index === 0) {
                            const teamCell = document.createElement("td");
                            teamCell.className = "team-cell";
                            teamCell.textContent = teamName;
                            if (teamName !== "Sans équipe") {
                                teamCell.setAttribute("rowspan", memberCount);
                            }
                            tr.appendChild(teamCell);
                        } else if (teamName === "Sans équipe") {
                            const teamCell = document.createElement("td");
                            teamCell.className = "team-cell";
                            teamCell.textContent = teamName;
                            tr.appendChild(teamCell);
                        }

                        // Name cell
                        const nameCell = document.createElement("td");
                        nameCell.textContent = member.prenom + " " + member.nom;
                        nameCell.style.backgroundColor = bgColor;

                        // Add team leader badge if applicable
                        if (member.is_team_leader == 1) {
                            const leaderBadge = document.createElement("span");
                            leaderBadge.className = "team-leader-badge";
                            leaderBadge.textContent = "(chef d'équipe)";
                            nameCell.appendChild(document.createTextNode(" "));
                            nameCell.appendChild(leaderBadge);
                        }

                        tr.appendChild(nameCell);

                        // Grade cell
                        const gradeCell = document.createElement("td");
                        gradeCell.textContent = member.grade;
                        gradeCell.style.backgroundColor = bgColor;
                        tr.appendChild(gradeCell);

                        membersTbody.appendChild(tr);
                        rowIndex++;
                    });
                });
            } else {
                // Flat view for other sort columns
                let rowIndex = 0;
                members.forEach((member) => {
                    const tr = document.createElement("tr");
                    tr.className = "member-row";
                    const bgColor = rowIndex % 2 === 0 ? "white" : "var(--bg-light)";

                    // Team cell
                    const teamCell = document.createElement("td");
                    teamCell.className = "team-cell";
                    teamCell.textContent = member.team_nom || "Sans équipe";
                    tr.appendChild(teamCell);

                    // Name cell
                    const nameCell = document.createElement("td");
                    nameCell.textContent = member.prenom + " " + member.nom;
                    nameCell.style.backgroundColor = bgColor;

                    if (member.is_team_leader == 1) {
                        const leaderBadge = document.createElement("span");
                        leaderBadge.className = "team-leader-badge";
                        leaderBadge.textContent = "(chef d'équipe)";
                        nameCell.appendChild(document.createTextNode(" "));
                        nameCell.appendChild(leaderBadge);
                    }
                    tr.appendChild(nameCell);

                    // Grade cell
                    const gradeCell = document.createElement("td");
                    gradeCell.textContent = member.grade;
                    gradeCell.style.backgroundColor = bgColor;
                    tr.appendChild(gradeCell);

                    membersTbody.appendChild(tr);
                    rowIndex++;
                });
            }
        }

        // Filter and display members
        function filterMembers() {
            const searchTerm = searchInput
                ? searchInput.value.toLowerCase().trim()
                : "";
            const teamFilter = filterTeam ? filterTeam.value : "all";
            const gradeFilter = filterGrade ? filterGrade.value.toLowerCase() : "all";
            const sortColumn = sortColumnSelect ? sortColumnSelect.value : "team";
            const sortDirection = sortOrderSelect ? sortOrderSelect.value : "asc";

            const filteredMembers = allMembers.filter((member) => {
                // Check search term
                const matchesSearch =
                    !searchTerm || member.name_lower.includes(searchTerm);

                // Check team filter
                const matchesTeam =
                    teamFilter === "all" || String(member.id_team) === teamFilter;

                // Check grade filter
                const matchesGrade =
                    gradeFilter === "all" || member.grade_lower === gradeFilter;

                return matchesSearch && matchesTeam && matchesGrade;
            });

            // Apply sorting
            const displayMembers = sortMembers(
                filteredMembers,
                sortColumn,
                sortDirection
            );

            // Rebuild table
            rebuildTable(displayMembers, sortColumn, sortDirection);

            // Update results count
            if (resultsCount) {
                resultsCount.textContent = displayMembers.length;
            }

            // Show/hide no results message
            if (noResultsMsg) {
                noResultsMsg.style.display =
                    displayMembers.length === 0 ? "block" : "none";
            }

            if (membersTable) {
                membersTable.style.display =
                    displayMembers.length === 0 ? "none" : "table";
            }
        }

        // Sort members array
        function sortMembers(members, column, direction) {
            const sorted = [...members];

            sorted.sort((a, b) => {
                let aVal, bVal;

                switch (column) {
                    case "team":
                        aVal = a.team_name_lower;
                        bVal = b.team_name_lower;
                        break;
                    case "name":
                        aVal = a.name_lower;
                        bVal = b.name_lower;
                        break;
                    case "grade":
                        aVal = a.grade_lower;
                        bVal = b.grade_lower;
                        break;
                    default:
                        return 0;
                }

                const comparison = aVal.localeCompare(bVal, "fr");
                return direction === "asc" ? comparison : -comparison;
            });

            return sorted;
        }

        // Reset all filters
        function resetFilters() {
            if (searchInput) searchInput.value = "";
            if (filterTeam) filterTeam.value = "all";
            if (filterGrade) filterGrade.value = "all";
            if (sortColumnSelect) sortColumnSelect.value = "team";
            if (sortOrderSelect) sortOrderSelect.value = "asc";

            filterMembers();
        }

        // Initialize
        initializeMembers();

        // Event listeners
        if (searchInput) searchInput.addEventListener("input", filterMembers);
        if (filterTeam) filterTeam.addEventListener("change", filterMembers);
        if (filterGrade) filterGrade.addEventListener("change", filterMembers);
        if (sortColumnSelect)
            sortColumnSelect.addEventListener("change", filterMembers);
        if (sortOrderSelect)
            sortOrderSelect.addEventListener("change", filterMembers);
        if (resetBtn) resetBtn.addEventListener("click", resetFilters);
    })();
});
