projects.js
document.addEventListener("DOMContentLoaded", () => {
    console.log("✅ projects.js is running");

    // Required: projects grid
    const projectsGrid = document.getElementById("project-grid");
    if (!projectsGrid) {
        console.warn("⚠️ Missing #project-grid element in HTML.");
        return;
    }

    // Optional containers
    const recentProjects = document.getElementById("recent-projects");
    const archiveList = document.getElementById("archive-list");
    const pagination = document.getElementById("pagination");
    const categorySelect = document.getElementById("category-select");
    const searchInput = document.getElementById("projectSearchInput"); // Changed this line to match HTML ID

    let allProjects = [];

    fetch("get-projects.php")
        .then(res => res.json())
        .then(data => {
            allProjects = data;
            console.log(`📰 Loaded ${allProjects.length} projects items from database`);
            renderRecent(allProjects);
            renderArchive(allProjects);
            filterAndRender();
        })
        .catch(err => {
            console.error("❌ Failed to fetch project from server", err);
        });

    let currentPage = 1;
    const itemsPerPage = 6;

    // Fix variable naming in renderProjects function
    function renderProjects(projectsItems) {
        projectsGrid.innerHTML = "";

        if (projectsItems.length === 0) {
            projectsGrid.innerHTML = `<div class="no-project-message"><p>No project items found</p></div>`;
            return;
        }

        const start = (currentPage - 1) * itemsPerPage;
        const paginatedProjects = projectsItems.slice(start, start + itemsPerPage);

        // FIXED: Set the class directly on the existing container
        projectsGrid.className = "projects-grid";

        paginatedProjects.forEach(project => {
            const card = document.createElement("div");
            card.className = "projects-card";
            card.innerHTML = `
                <div class="project-image">
                    <img src="${project.image || 'https://via.placeholder.com/300x200'}" alt="Project Image">
                </div>
                <div class="project-text">
                    <h2 class="project-title">${project.title}</h2>
                    <p class="project-date">${new Date(project.date || project.created_at).toLocaleDateString()}</p>
                    <p class="project-description">${project.description}</p>
                </div>
            `;

            // Attach click event to open modal
            card.addEventListener("click", () => {
                document.getElementById("modal-title").textContent = project.title;
                document.getElementById("modal-date").textContent = new Date(project.date || project.created_at).toLocaleDateString();
                document.getElementById("modal-image").src = project.image || 'https://via.placeholder.com/300x200';
                document.getElementById("modal-description").innerHTML = project.description;
                {
                    const rawCategory = project.categoryText || project.category || "";
                    const formattedCategory = rawCategory.charAt(0).toUpperCase() + rawCategory.slice(1).toLowerCase();
                    document.getElementById("modal-category").textContent = formattedCategory;
                }

                const fbLink = document.getElementById("modal-facebook");
                if (project.facebook) {
                    fbLink.href = project.facebook;
                    fbLink.style.display = "inline-block";
                } else {
                    fbLink.style.display = "none";
                }

                document.getElementById("project-modal").style.display = "block";
            });

            projectsGrid.appendChild(card);
        });

        renderPagination(projectsItems.length);
    }

    function renderRecent(projectsItems) {
        if (!recentProjects) return;
        recentProjects.innerHTML = "";
        projectsItems.slice(0, 5).forEach(project => {
            const item = document.createElement("div");
            item.className = "recent-item";
            item.innerHTML = `<p>${project.title}</p>`;
            recentProjects.appendChild(item);
        });
    }

    function renderArchive(projectsItems) {
        if (!archiveList) return;
        const archive = {};
        projectsItems.forEach(project => {
            const date = new Date(project.date);
            const key = `${date.getMonth() + 1}-${date.getFullYear()}`;
            archive[key] = (archive[key] || 0) + 1;
        });

        archiveList.innerHTML = "";
        for (const [month, count] of Object.entries(archive)) {
            const li = document.createElement("li");
            li.textContent = `${month.replace('-', '/')} (${count})`;
            archiveList.appendChild(li);
        }
    }

    function renderPagination(totalItems) {
        if (!pagination) return;
        pagination.innerHTML = "";

        const totalPages = Math.ceil(totalItems / itemsPerPage);
        if (totalPages <= 1) return;

        // ← Left arrow
        const prevBtn = document.createElement("button");
        prevBtn.innerHTML = "&larr;";
        prevBtn.classList.add("arrow");
        prevBtn.disabled = currentPage === 1;
        prevBtn.addEventListener("click", () => {
            if (currentPage > 1) {
                currentPage--;
                filterAndRender();
            }
        });
        pagination.appendChild(prevBtn);

        // Numbered page buttons
        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement("button");
            btn.textContent = i;
            if (i === currentPage) btn.classList.add("active");
            btn.addEventListener("click", () => {
                currentPage = i;
                filterAndRender();
            });
            pagination.appendChild(btn);
        }

        // → Right arrow
        const nextBtn = document.createElement("button");
        nextBtn.innerHTML = "&rarr;";
        nextBtn.classList.add("arrow");
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.addEventListener("click", () => {
            if (currentPage < totalPages) {
                currentPage++;
                filterAndRender();
            }
        });
        pagination.appendChild(nextBtn);
    }

    function filterAndRender() {
        let filtered = [...allProjects];

        if (searchInput && searchInput.value.trim()) {
            const query = searchInput.value.toLowerCase();
            filtered = filtered.filter(project =>
                project.title.toLowerCase().includes(query) // Filter only by title
            );
        }

        if (categorySelect && categorySelect.value) {
            filtered = filtered.filter(project => project.category === categorySelect.value);
        }

        renderProjects(filtered);
    }

    // Setup filter events
    if (categorySelect) {
        categorySelect.addEventListener("change", () => {
            currentPage = 1;
            filterAndRender();
        });
    }

    if (searchInput) {
        // Event listener for the search input
        searchInput.addEventListener("input", () => {
            currentPage = 1;
            filterAndRender();
        });
        // Event listener for the search button
        document.getElementById("projectSearchButton").addEventListener("click", () => { // Changed this line to match HTML ID
            currentPage = 1;
            filterAndRender();
        });
    }

    // Initial render
    console.log(`📰 Loaded ${allProjects.length} project items from localStorage`);
    renderRecent(allProjects);
    renderArchive(allProjects);
    filterAndRender();
});

document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("project-modal");
    const closeBtn = document.querySelector(".project-modal-close");

    // Close on ❌
    if (closeBtn) {
        closeBtn.addEventListener("click", () => {
            modal.style.display = "none";
        });
    }

    // Close when clicking outside the modal content
    window.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.style.display = "none";
        }
    });
});
    /*card.innerHTML = `
    <a href="project-detail.php?id=${project.id}" class="project-link">
        <div class="project-image">
            <img src="${project.image || 'https://via.placeholder.com/300x200'}">
        </div>
        <div class="project-text">
            <div class="project-title">${project.title}</div>
            <div class="project-date">${new Date(project.date || project.created_at).toLocaleDateString()}</div>
            <div class="project-description">${project.description}</div>
        </div>
    </a>
`;*/