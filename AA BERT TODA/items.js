// Define a sample items array for demonstration
const sampleItems = [
    {
        id: 1,
        name: "Projector",
        category: "Electronics",
        quantity: 2,
        status: "available",
        dateAdded: "2025-04-28",
        lastUpdated: "2025-05-01",
        description: "High definition projector with HDMI and USB connectivity.",
        notes: "Store in the equipment cabinet when not in use.",
        image: "placeholder.jpg",
        history: [
            { user: "Maria Garcia", dateBorrowed: "2025-04-15", dateReturned: "2025-04-18", condition: "Good" },
            { user: "Juan Santos", dateBorrowed: "2025-03-20", dateReturned: "2025-03-22", condition: "Good" }
        ]
    },
    {
        id: 2,
        name: "Basketball",
        category: "Sports Equipment",
        quantity: 5,
        status: "in-use",
        dateAdded: "2025-04-25",
        lastUpdated: "2025-04-30",
        description: "Official size basketball for youth programs.",
        notes: "Check for proper inflation before and after use.",
        image: "placeholder.jpg",
        history: [
            { user: "Pedro Reyes", dateBorrowed: "2025-04-30", dateReturned: null, condition: "In Use" },
            { user: "Juan Santos", dateBorrowed: "2025-04-20", dateReturned: "2025-04-25", condition: "Good" }
        ]
    },
    {
        id: 3,
        name: "Microphone Set",
        category: "Audio Equipment",
        quantity: 3,
        status: "available",
        dateAdded: "2025-04-20",
        lastUpdated: "2025-04-20",
        description: "Wireless microphone set with two handheld mics and one lapel mic.",
        notes: "Check battery levels before events.",
        image: "placeholder.jpg",
        history: [
            { user: "Rosa Diaz", dateBorrowed: "2025-04-10", dateReturned: "2025-04-12", condition: "Good" }
        ]
    },
    {
        id: 4,
        name: "Volleyball Net",
        category: "Sports Equipment",
        quantity: 1,
        status: "available",
        dateAdded: "2025-04-15",
        lastUpdated: "2025-04-27",
        description: "Regulation size volleyball net with adjustable height.",
        notes: "Store in carrying case when not in use.",
        image: "placeholder.jpg",
        history: [
            { user: "Maria Garcia", dateBorrowed: "2025-04-20", dateReturned: "2025-04-27", condition: "Good" }
        ]
    },
    {
        id: 5,
        name: "Folding Tables",
        category: "Furniture",
        quantity: 10,
        status: "available",
        dateAdded: "2025-04-10",
        lastUpdated: "2025-04-10",
        description: "Plastic folding tables for events and gatherings.",
        notes: "Clean with disinfectant after each use.",
        image: "placeholder.jpg",
        history: [
            { user: "Community Meeting", dateBorrowed: "2025-04-05", dateReturned: "2025-04-05", condition: "Good" }
        ]
    },
    {
        id: 6,
        name: "Sound System",
        category: "Audio Equipment",
        quantity: 1,
        status: "maintenance",
        dateAdded: "2025-03-15",
        lastUpdated: "2025-05-01",
        description: "Complete sound system with speakers, amplifier, and mixer.",
        notes: "Currently being repaired - left speaker has issues.",
        image: "placeholder.jpg",
        history: [
            { user: "Youth Festival", dateBorrowed: "2025-03-25", dateReturned: "2025-03-27", condition: "Damaged" }
        ]
    },
    {
        id: 7,
        name: "Volleyball",
        category: "Sports Equipment",
        quantity: 3,
        status: "available",
        dateAdded: "2025-03-20",
        lastUpdated: "2025-03-20",
        description: "Official size volleyball for games and practice.",
        notes: "Check air pressure regularly.",
        image: "placeholder.jpg",
        history: [
            { user: "Sports Clinic", dateBorrowed: "2025-03-10", dateReturned: "2025-03-12", condition: "Good" }
        ]
    },
    {
        id: 8,
        name: "Party Decorations",
        category: "Decorations",
        quantity: 5,
        status: "available",
        dateAdded: "2025-03-01",
        lastUpdated: "2025-03-01",
        description: "Assorted party decorations including streamers, balloons, and banners.",
        notes: "Store in labeled containers.",
        image: "placeholder.jpg",
        history: [
            { user: "Children's Day", dateBorrowed: "2025-02-20", dateReturned: "2025-02-21", condition: "Good" }
        ]
    }
];

// Global variables
let currentItems = [...sampleItems];
let currentPage = 1;
let itemsPerPage = 10;
let selectedItem = null;

// DOM Elements
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the items table
    renderItemsTable();
    
    // Event listeners for search and filter
    document.getElementById('search-input').addEventListener('input', filterItems);
    document.getElementById('search-btn').addEventListener('click', filterItems);
    document.getElementById('category-filter').addEventListener('change', filterItems);
    document.getElementById('status-filter').addEventListener('change', filterItems);
    
    // Event listeners for pagination
    document.getElementById('prev-page').addEventListener('click', () => changePage(-1));
    document.getElementById('next-page').addEventListener('click', () => changePage(1));
    document.getElementById('items-per-page-select').addEventListener('change', changeItemsPerPage);
    
    // Event listeners for modals
    document.getElementById('add-item-btn').addEventListener('click', openAddItemModal);
    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', closeAllModals);
    });
    
    // Form submission for adding/editing items
    document.getElementById('item-form').addEventListener('submit', saveItem);
    document.getElementById('cancel-item').addEventListener('click', closeAllModals);
    
    // Modal action buttons
    document.getElementById('close-details-btn').addEventListener('click', closeAllModals);
    document.getElementById('edit-item-btn').addEventListener('click', editCurrentItem);
    document.getElementById('delete-item-btn').addEventListener('click', confirmDelete);
    document.getElementById('cancel-delete').addEventListener('click', closeAllModals);
    document.getElementById('confirm-delete').addEventListener('click', deleteItem);
    
    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            closeAllModals();
        }
    });
});

// Render the items table with current data
function renderItemsTable() {
    const tableBody = document.querySelector('#items-table tbody');
    tableBody.innerHTML = '';
    
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const pageItems = currentItems.slice(startIndex, endIndex);
    
    if (pageItems.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="no-results">
                    No items found matching your criteria.
                </td>
            </tr>
        `;
        return;
    }
    
    pageItems.forEach(item => {
        const statusClass = getStatusClass(item.status);
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.id}</td>
            <td>${item.name}</td>
            <td>${item.category}</td>
            <td>${item.quantity}</td>
            <td><span class="badge ${statusClass}">${formatStatus(item.status)}</span></td>
            <td>${formatDate(item.dateAdded)}</td>
            <td>${formatDate(item.lastUpdated)}</td>
            <td>
                <div class="action-buttons">
                    <button class="action-btn view-btn" data-id="${item.id}" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="action-btn edit-btn" data-id="${item.id}" title="Edit Item">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete-btn" data-id="${item.id}" title="Delete Item">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </td>
        `;
        tableBody.appendChild(row);
    });
    
    // Add event listeners to action buttons
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', () => viewItemDetails(parseInt(btn.dataset.id)));
    });
    
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => openEditItemModal(parseInt(btn.dataset.id)));
    });
    
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            selectedItem = sampleItems.find(item => item.id === parseInt(btn.dataset.id));
            document.getElementById('confirm-modal').style.display = 'block';
        });
    });
    
    updatePagination();
}

// Update pagination controls
function updatePagination() {
    const totalPages = Math.ceil(currentItems.length / itemsPerPage);
    const pageNumbers = document.getElementById('page-numbers');
    pageNumbers.innerHTML = '';
    
    // Determine which page numbers to show
    let startPage = Math.max(1, currentPage - 1);
    let endPage = Math.min(totalPages, startPage + 2);
    
    // Adjust if needed
    if (endPage - startPage < 2) {
        startPage = Math.max(1, endPage - 2);
    }
    
    for (let i = startPage; i <= endPage; i++) {
        const pageNumber = document.createElement('span');
        pageNumber.classList.add('page-number');
        if (i === currentPage) {
            pageNumber.classList.add('active');
        }
        pageNumber.textContent = i;
        pageNumber.addEventListener('click', () => {
            currentPage = i;
            renderItemsTable();
        });
        pageNumbers.appendChild(pageNumber);
    }
    
    // Disable buttons if at first or last page
    document.getElementById('prev-page').disabled = currentPage === 1;
    document.getElementById('next-page').disabled = currentPage === totalPages;
}

// Filter items based on search input and filter selections
function filterItems() {
    const searchTerm = document.getElementById('search-input').value.toLowerCase();
    const categoryFilter = document.getElementById('category-filter').value.toLowerCase();
    const statusFilter = document.getElementById('status-filter').value.toLowerCase();
    
    currentItems = sampleItems.filter(item => {
        // Filter by search term
        const matchesSearch = searchTerm === '' || 
            item.name.toLowerCase().includes(searchTerm) || 
            item.category.toLowerCase().includes(searchTerm);
        
        // Filter by category
        const matchesCategory = categoryFilter === '' || 
            item.category.toLowerCase() === categoryFilter;
        
        // Filter by status
        const matchesStatus = statusFilter === '' || 
            item.status.toLowerCase() === statusFilter;
        
        return matchesSearch && matchesCategory && matchesStatus;
    });
    
    currentPage = 1; // Reset to first page
    renderItemsTable();
}

// Handle page changes
function changePage(direction) {
    const totalPages = Math.ceil(currentItems.length / itemsPerPage);
    const newPage = currentPage + direction;
    
    if (newPage >= 1 && newPage <= totalPages) {
        currentPage = newPage;
        renderItemsTable();
    }
}

// Change items per page
function changeItemsPerPage() {
    const newItemsPerPage = parseInt(document.getElementById('items-per-page-select').value);
    itemsPerPage = newItemsPerPage;
    currentPage = 1; // Reset to first page
    renderItemsTable();
}

// Open add item modal
function openAddItemModal() {
    // Reset form
    document.getElementById('item-form').reset();
    document.getElementById('modal-title').textContent = 'Add New Item';
    
    // Show modal
    document.getElementById('item-modal').style.display = 'block';
}

// Open edit item modal
function openEditItemModal(itemId) {
    const item = sampleItems.find(item => item.id === itemId);
    if (!item) return;
    selectedItem = item;
    
    // Populate form with item data
    document.getElementById('item-name').value = item.name;
    document.getElementById('item-category').value = item.category.toLowerCase();
    document.getElementById('item-quantity').value = item.quantity;
    document.getElementById('item-status').value = item.status;
    document.getElementById('item-description').value = item.description;
    document.getElementById('item-notes').value = item.notes;
    
    // Set modal title
    document.getElementById('modal-title').textContent = 'Edit Item';
    
    // Show modal
    document.getElementById('item-modal').style.display = 'block';
}

// Save new or edited item
function saveItem(event) {
    event.preventDefault();
    
    // Get form values
    const name = document.getElementById('item-name').value;
    const category = document.getElementById('item-category').value;
    const quantity = parseInt(document.getElementById('item-quantity').value);
    const status = document.getElementById('item-status').value;
    const description = document.getElementById('item-description').value;
    const notes = document.getElementById('item-notes').value;
    
    const currentDate = new Date().toISOString().split('T')[0];
    
    if (selectedItem) {
        // Update existing item
        const index = sampleItems.findIndex(item => item.id === selectedItem.id);
        if (index !== -1) {
            sampleItems[index] = {
                ...selectedItem,
                name,
                category: formatCategory(category),
                quantity,
                status,
                description,
                notes,
                lastUpdated: currentDate
            };
        }
    } else {
        // Add new item
        const newId = sampleItems.length > 0 ? Math.max(...sampleItems.map(item => item.id)) + 1 : 1;
        const newItem = {
            id: newId,
            name,
            category: formatCategory(category),
            quantity,
            status,
            dateAdded: currentDate,
            lastUpdated: currentDate,
            description,
            notes,
            image: "placeholder.jpg",
            history: []
        };
        sampleItems.push(newItem);
    }
    
    // Reset and close modal
    selectedItem = null;
    document.getElementById('item-form').reset();
    document.getElementById('item-modal').style.display = 'none';
    
    // Update table
    currentItems = [...sampleItems];
    renderItemsTable();
}

// View item details
function viewItemDetails(itemId) {
    const item = sampleItems.find(item => item.id === itemId);
    if (!item) return;
    selectedItem = item;
    
    // Populate details
    document.getElementById('detail-item-name').textContent = item.name;
    document.getElementById('detail-item-category').textContent = item.category;
    document.getElementById('detail-item-quantity').textContent = item.quantity;
    document.getElementById('detail-item-status').textContent = formatStatus(item.status);
    document.getElementById('detail-item-date').textContent = formatDate(item.dateAdded);
    document.getElementById('detail-item-updated').textContent = formatDate(item.lastUpdated);
    document.getElementById('detail-item-description').textContent = item.description;
    document.getElementById('detail-item-notes').textContent = item.notes;
    document.getElementById('detail-item-image').src = item.image;
    
    // Populate history table
    const historyTableBody = document.getElementById('history-table-body');
    historyTableBody.innerHTML = '';
    
    if (item.history.length === 0) {
        historyTableBody.innerHTML = `
            <tr>
                <td colspan="4" class="no-results">No usage history available.</td>
            </tr>
        `;
    } else {
        item.history.forEach(entry => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${entry.user}</td>
                <td>${formatDate(entry.dateBorrowed)}</td>
                <td>${entry.dateReturned ? formatDate(entry.dateReturned) : '-'}</td>
                <td>${entry.condition}</td>
            `;
            historyTableBody.appendChild(row);
        });
    }
    
    // Show modal
    document.getElementById('item-details-modal').style.display = 'block';
}

// Edit current item
function editCurrentItem() {
    if (!selectedItem) return;
    document.getElementById('item-details-modal').style.display = 'none';
    openEditItemModal(selectedItem.id);
}

// Confirm delete
function confirmDelete() {
    document.getElementById('item-details-modal').style.display = 'none';
    document.getElementById('confirm-modal').style.display = 'block';
}

// Delete item
function deleteItem() {
    if (!selectedItem) return;
    
    const index = sampleItems.findIndex(item => item.id === selectedItem.id);
    if (index !== -1) {
        sampleItems.splice(index, 1);
        currentItems = [...sampleItems];
        renderItemsTable();
    }
    
    selectedItem = null;
    document.getElementById('confirm-modal').style.display = 'none';
}

// Close all modals
function closeAllModals() {
    document.getElementById('item-modal').style.display = 'none';
    document.getElementById('item-details-modal').style.display = 'none';
    document.getElementById('confirm-modal').style.display = 'none';
}

// Helper functions
function formatStatus(status) {
    // Convert status from "available" to "Available"
    return status.charAt(0).toUpperCase() + status.slice(1).replace(/-/g, ' ');
}

function formatCategory(category) {
    // Convert category from "electronics" to "Electronics"
    const categoryMap = {
        'electronics': 'Electronics',
        'sports': 'Sports Equipment',
        'audio': 'Audio Equipment',
        'office': 'Office Supplies',
        'furniture': 'Furniture',
        'decorations': 'Decorations',
        'other': 'Other'
    };
    return categoryMap[category] || 'Other';
}

function getStatusClass(status) {
    const statusClasses = {
        'available': 'badge-success',
        'in-use': 'badge-warning',
        'maintenance': 'badge-info',
        'damaged': 'badge-danger'
    };
    return statusClasses[status] || 'badge-secondary';
}

function formatDate(dateString) {
    if (!dateString) return '-';
    
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}