// Inventory System JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Modal functionality
    const modal = document.getElementById('add-item-modal');
    const addItemBtn = document.querySelector('.add-item-btn');
    const closeModal = document.querySelector('.close-modal');
    const cancelAdd = document.getElementById('cancel-add');
    
    // Open modal when add item button is clicked
    if (addItemBtn) {
        addItemBtn.addEventListener('click', function() {
            modal.style.display = 'block';
        });
    }
    
    // Close modal when x is clicked
    if (closeModal) {
        closeModal.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    
    // Close modal when cancel button is clicked
    if (cancelAdd) {
        cancelAdd.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Form submission
    const addItemForm = document.getElementById('add-item-form');
    if (addItemForm) {
        addItemForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            // Get form values
            const itemName = document.getElementById('item-name').value;
            const itemCategory = document.getElementById('item-category').value;
            const itemQuantity = document.getElementById('item-quantity').value;
            const itemDescription = document.getElementById('item-description').value;
            const itemCondition = document.getElementById('item-condition').value;
            const itemLocation = document.getElementById('item-location').value;
            
            // In a real application, you would send this data to a server
            // For now, we'll just show an alert and close the modal
            alert(`Item "${itemName}" added successfully!`);
            
            // Clear form and close modal
            addItemForm.reset();
            modal.style.display = 'none';
            
            // You could also add the item to the table dynamically
            addItemToTable(itemName, itemCategory, itemQuantity);
        });
    }
    
    // Function to add a new row to the inventory table
    function addItemToTable(name, category, quantity) {
        const table = document.getElementById('inventory-items');
        if (!table) return;
        
        const tbody = table.querySelector('tbody');
        const newRow = document.createElement('tr');
        
        // Generate a random ID for demo purposes
        const itemId = Math.floor(1000 + Math.random() * 9000);
        
        // Get current date
        const today = new Date();
        const dateAdded = today.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        // Create table row
        newRow.innerHTML = `
            <td>${itemId}</td>
            <td>${name}</td>
            <td>${category}</td>
            <td>${quantity}</td>
            <td>${dateAdded}</td>
            <td><span class="badge badge-success">Available</span></td>
            <td>
                <button class="btn btn-primary btn-action"><i class="fas fa-edit"></i></button>
                <button class="btn btn-secondary btn-action"><i class="fas fa-eye"></i></button>
                <button class="btn btn-warning btn-action"><i class="fas fa-shopping-cart"></i></button>
            </td>
        `;
        
        tbody.insertBefore(newRow, tbody.firstChild);
        
        // Update the total items count
        updateItemCount();
    }
    
    // Function to update the item count display
    function updateItemCount() {
        const totalItemsCard = document.querySelector('.card-blue .card-value');
        const availableItemsCard = document.querySelector('.card-green .card-value');
        
        if (totalItemsCard) {
            // Get the current count and increase by 1
            const currentCount = parseInt(totalItemsCard.textContent, 10);
            totalItemsCard.textContent = currentCount + 1;
        }
        
        if (availableItemsCard) {
            // Get the current count and increase by 1
            const currentCount = parseInt(availableItemsCard.textContent, 10);
            availableItemsCard.textContent = currentCount + 1;
        }
    }
    
    // Search functionality
    const searchInput = document.getElementById('search-inventory');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#inventory-items tbody tr');
            
            tableRows.forEach(row => {
                const itemName = row.cells[1].textContent.toLowerCase();
                const itemCategory = row.cells[2].textContent.toLowerCase();
                
                if (itemName.includes(searchTerm) || itemCategory.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    
    // Filter functionality
    const categoryFilter = document.getElementById('category-filter');
    const statusFilter = document.getElementById('status-filter');
    
    function applyFilters() {
        const categoryValue = categoryFilter ? categoryFilter.value.toLowerCase() : '';
        const statusValue = statusFilter ? statusFilter.value.toLowerCase() : '';
        
        const tableRows = document.querySelectorAll('#inventory-items tbody tr');
        
        tableRows.forEach(row => {
            const itemCategory = row.cells[2].textContent.toLowerCase();
            const itemStatus = row.cells[5].textContent.toLowerCase();
            
            const categoryMatch = categoryValue === '' || itemCategory.includes(categoryValue);
            const statusMatch = statusValue === '' || itemStatus.includes(statusValue);
            
            if (categoryMatch && statusMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', applyFilters);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', applyFilters);
    }
    
    // Table sorting functionality
    const tableHeaders = document.querySelectorAll('#inventory-items th');
    tableHeaders.forEach((header, index) => {
        header.addEventListener('click', function() {
            sortTable(index);
        });
    });
    
    let sortDirection = {};
    
    function sortTable(columnIndex) {
        const table = document.getElementById('inventory-items');
        if (!table) return;
        
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        // Toggle sort direction
        sortDirection[columnIndex] = !sortDirection[columnIndex];
        
        // Sort the rows
        rows.sort((a, b) => {
            let aValue = a.cells[columnIndex].textContent.trim();
            let bValue = b.cells[columnIndex].textContent.trim();
            
            // Convert to numbers if possible for correct sorting
            if (!isNaN(aValue) && !isNaN(bValue)) {
                aValue = parseFloat(aValue);
                bValue = parseFloat(bValue);
            }
            
            if (aValue < bValue) {
                return sortDirection[columnIndex] ? -1 : 1;
            } else if (aValue > bValue) {
                return sortDirection[columnIndex] ? 1 : -1;
            }
            return 0;
        });
        
        // Re-append the sorted rows
        rows.forEach(row => tbody.appendChild(row));
        
        // Update the sort indicators
        tableHeaders.forEach(th => {
            th.querySelector('i').className = 'fas fa-sort';
        });
        
        const currentHeader = tableHeaders[columnIndex];
        const icon = currentHeader.querySelector('i');
        icon.className = sortDirection[columnIndex] ? 'fas fa-sort-up' : 'fas fa-sort-down';
    }
    
    // Pagination functionality
    const paginationButtons = document.querySelectorAll('.pagination-btn');
    if (paginationButtons.length > 0) {
        paginationButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                paginationButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // In a real application, you would fetch and display the appropriate page of data
                // For this demo, we'll just show an alert
                if (!this.textContent.includes('angle')) {
                    alert(`Page ${this.textContent} selected`);
                }
            });
        });
    }
    
    // Add click handlers for action buttons
    document.addEventListener('click', function(event) {
        // Edit button
        if (event.target.closest('.btn-primary')) {
            const row = event.target.closest('tr');
            const itemName = row.cells[1].textContent;
            alert(`Edit item: ${itemName}`);
        }
        
        // View button
        if (event.target.closest('.btn-secondary')) {
            const row = event.target.closest('tr');
            const itemName = row.cells[1].textContent;
            alert(`View details for: ${itemName}`);
        }
        
        // Use/Borrow button
        if (event.target.closest('.btn-warning')) {
            const row = event.target.closest('tr');
            const itemName = row.cells[1].textContent;
            alert(`Use/Borrow item: ${itemName}`);
        }
    });
});
