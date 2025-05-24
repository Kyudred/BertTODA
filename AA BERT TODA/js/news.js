news.js
document.addEventListener("DOMContentLoaded", () => {
    console.log("✅ news.js is running");

    // Required: news grid
    const newsGrid = document.getElementById("news-grid");
    if (!newsGrid) {
        console.warn("⚠️ Missing #news-grid element in HTML.");
        return;
    }

    // Optional containers
    const recentNews = document.getElementById("recent-news");
    const archiveList = document.getElementById("archive-list");
    const pagination = document.getElementById("pagination");
    const categorySelect = document.getElementById("category-select");
    const searchInput = document.getElementById("newsSearchInput"); // Changed to newsSearchInput as per HTML

    let allNews = [];

    fetch("get-news.php")
        .then(res => res.json())
        .then(data => {
            allNews = data;
            console.log(`📰 Loaded ${allNews.length} news items from database`);
            renderRecent(allNews);
            renderArchive(allNews);
            filterAndRender();
        })
        .catch(err => {
            console.error("❌ Failed to fetch news from server", err);
        });

    let currentPage = 1;
    const itemsPerPage = 6;

    function renderNews(newsItems) {
    newsGrid.innerHTML = "";

    if (newsItems.length === 0) {
        newsGrid.innerHTML = `<div class="no-news-message"><p>No news items found</p></div>`;
        return;
    }

    const start = (currentPage - 1) * itemsPerPage;
    const paginatedNews = newsItems.slice(start, start + itemsPerPage);

    paginatedNews.forEach(news => {
        const card = document.createElement("div");
        card.className = "news-card";
        card.innerHTML = `
            <div class="news-image">
                <img src="${news.image || 'https://via.placeholder.com/300x200'}" alt="News Image">
            </div>
            <div class="news-text">
                <h2 class="news-title">${news.title}</h2>
                <p class="news-date">${new Date(news.date || news.created_at).toLocaleDateString()}</p>
                <p class="news-description">${news.summary}</p>
            </div>
        `;

        // ✅ Attach click event to open modal
        card.addEventListener("click", () => {
            document.getElementById("modal-title").textContent = news.title;
            document.getElementById("modal-date").textContent = new Date(news.date || news.created_at).toLocaleDateString();
            document.getElementById("modal-summary").textContent = news.summary;
            document.getElementById("modal-image").src = news.image;
            document.getElementById("modal-content").innerHTML = news.content;
            const rawCategory = news.categoryText || news.category || "";
            const formattedCategory = rawCategory.charAt(0).toUpperCase() + rawCategory.slice(1).toLowerCase();
            document.getElementById("modal-category").textContent = formattedCategory;



            const fbLink = document.getElementById("modal-facebook");
            if (news.facebook) {
                fbLink.href = news.facebook;
                fbLink.style.display = "inline-block";
            } else {
                fbLink.style.display = "none";
            }

            document.getElementById("news-modal").style.display = "block";
        });

        newsGrid.appendChild(card);
    });

    renderPagination(newsItems.length);
}


    function renderRecent(newsItems) {
        if (!recentNews) return;
        recentNews.innerHTML = "";
        newsItems.slice(0, 5).forEach(news => {
            const item = document.createElement("div");
            item.className = "recent-item";
            item.innerHTML = `<p>${news.title}</p>`;
            recentNews.appendChild(item);
        });
    }

    function renderArchive(newsItems) {
        if (!archiveList) return;
        const archive = {};
        newsItems.forEach(news => {
            const date = new Date(news.date);
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
        let filtered = [...allNews];

        if (searchInput && searchInput.value.trim()) {
            const query = searchInput.value.toLowerCase();
            filtered = filtered.filter(news =>
                news.title.toLowerCase().includes(query) // Filter only by title
            );
        }

        if (categorySelect && categorySelect.value) {
            filtered = filtered.filter(news => news.category === categorySelect.value);
        }

        renderNews(filtered);
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
        document.getElementById("newsSearchButton").addEventListener("click", () => {
            currentPage = 1;
            filterAndRender();
        });
    }

    // Initial render
    console.log(`📰 Loaded ${allNews.length} news items from localStorage`);
    renderRecent(allNews);
    renderArchive(allNews);
    filterAndRender();
});

document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("news-modal");
    const closeBtn = document.querySelector(".news-modal-close");

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
    <a href="news-detail.php?id=${news.id}" class="news-link">
        <div class="news-image">
            <img src="${news.image || 'https://via.placeholder.com/300x200'}">
        </div>
        <div class="news-text">
            <div class="news-title">${news.title}</div>
            <div class="news-date">${new Date(news.date || news.created_at).toLocaleDateString()}</div>
            <div class="news-description">${news.summary}</div>
        </div>
    </a>
`;*/