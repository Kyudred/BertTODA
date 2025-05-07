/**
 * Categories Page JavaScript
 * For SK Barangay San Isidro Admin Dashboard
 */
// Document ready function
document.addEventListener('DOMContentLoaded', function() {
    initializeModalHandlers();
    initializeSearchFunctionality();
});

/**
 * Initialize modal popup handlers
 */
function initializeModalHandlers() {
    // Close modal when clicking outside the modal content
    window.onclick = function(event) {
        if (event.target.className === 'modal') {
            event.target.style.display = "none";
        }
    };

    // Add category form submission
    const addCategoryForm = document.querySelector('#addCategoryModal form');
    if (addCategoryForm) {
        addCategoryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // Get form data
            const categoryName = document.getElementById('categoryName').value;
            const categoryDescription = document.getElementById('categoryDescription').value;
            const categoryIcon = document.getElementById('categoryIcon').value;
            
            // Here you would normally send this data to the server
            console.log('Adding new category:', {
                name: categoryName,
                description: categoryDescription,
                icon: categoryIcon
            });
            
            // Give feedback to user and close modal
            alert('Category added successfully!');
            document.getElementById('addCategoryModal').style.display = 'none';
            
            // In a real application, you would update the UI with the new category
            // or reload the categories list from the server
            addCategoryForm.reset();
        });
    }

    // Edit category form submission
    const editCategoryForm = document.querySelector('#editCategoryModal form');
    if (editCategoryForm) {
        editCategoryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // Get form data
            const categoryId = document.getElementById('editCategoryId').value;
            const categoryName = document.getElementById('editCategoryName').value;
            const categoryDescription = document.getElementById('editCategoryDescription').value;
            const categoryIcon = document.getElementById('editCategoryIcon').value;
            
            // Here you would normally send this data to the server
            console.log('Updating category:', {
                id: categoryId,
                name: categoryName,
                description: categoryDescription,
                icon: categoryIcon
            });
            
            // Give feedback to user and close modal
            alert('Category updated successfully!');
            document.getElementById('editCategoryModal').style.display = 'none';
            
            // In a real application, you would update the UI with the updated category
            // or reload the categories list from the server
        });
    }

    // Delete category confirmation
    const deleteButtons = document.querySelectorAll('.delete-category-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = this.getAttribute('data-id');
            const categoryName = this.getAttribute('data-name');
            
            // Set the category ID in the hidden field
            document.getElementById('deleteCategoryId').value = categoryId;
            
            // Update confirmation message
            document.getElementById('deleteCategoryName').textContent = categoryName;
            
            // Show the delete confirmation modal
            document.getElementById('deleteCategoryModal').style.display = 'block';
        });
    });
    
    // Delete category confirmation form submission
    const deleteCategoryForm = document.querySelector('#deleteCategoryModal form');
    if (deleteCategoryForm) {
        deleteCategoryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const categoryId = document.getElementById('deleteCategoryId').value;
            
            // Here you would normally send this data to the server
            console.log('Deleting category with ID:', categoryId);
            
            // Give feedback to user and close modal
            alert('Category deleted successfully!');
            document.getElementById('deleteCategoryModal').style.display = 'none';
            
            // In a real application, you would remove the category from the UI
            // or reload the categories list from the server
        });
    }
    
    // Open modal buttons
    const addCategoryBtn = document.getElementById('addCategoryBtn');
    if (addCategoryBtn) {
        addCategoryBtn.addEventListener('click', function() {
            document.getElementById('addCategoryModal').style.display = 'block';
        });
    }
    
    // Edit category button click handlers
    const editButtons = document.querySelectorAll('.edit-category-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = this.getAttribute('data-id');
            const categoryName = this.getAttribute('data-name');
            const categoryDescription = this.getAttribute('data-description');
            const categoryIcon = this.getAttribute('data-icon');
            
            // Set the form values
            document.getElementById('editCategoryId').value = categoryId;
            document.getElementById('editCategoryName').value = categoryName;
            document.getElementById('editCategoryDescription').value = categoryDescription;
            document.getElementById('editCategoryIcon').value = categoryIcon;
            
            // Show the edit modal
            document.getElementById('editCategoryModal').style.display = 'block';
        });
    });
    
    // Close button click handlers
    const closeButtons = document.querySelectorAll('.close-modal');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Find the parent modal and close it
            const modal = this.closest('.modal');
            if (modal) {
                modal.style.display = 'none';
            }
        });
    });
    
    // Cancel button click handlers
    const cancelButtons = document.querySelectorAll('.cancel-modal');
    cancelButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Find the parent modal and close it
            const modal = this.closest('.modal');
            if (modal) {
                modal.style.display = 'none';
            }
        });
    });
}

/**
 * Initialize search functionality
 */
function initializeSearchFunctionality() {
    const searchInput = document.getElementById('searchCategories');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const categoryItems = document.querySelectorAll('.category-item');
            
            categoryItems.forEach(item => {
                const categoryName = item.querySelector('.category-name').textContent.toLowerCase();
                const categoryDescription = item.querySelector('.category-description').textContent.toLowerCase();
                
                if (categoryName.includes(searchTerm) || categoryDescription.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Show or hide "no results" message
            const noResults = document.getElementById('noResults');
            if (noResults) {
                let visibleItems = 0;
                categoryItems.forEach(item => {
                    if (item.style.display !== 'none') {
                        visibleItems++;
                    }
                });
                
                noResults.style.display = visibleItems === 0 ? 'block' : 'none';
            }
        });
    }
}

/**
 * Function to load categories from server
 */
function loadCategories() {
    // In a real application, you would fetch categories from the server
    // using fetch or XMLHttpRequest
    fetch('/api/categories')
        .then(response => response.json())
        .then(data => {
            displayCategories(data);
        })
        .catch(error => {
            console.error('Error loading categories:', error);
            // Show error message to user
            document.getElementById('categoriesContainer').innerHTML = 
                '<div class="error-message">Failed to load categories. Please try again later.</div>';
        });
}

/**
 * Function to display categories in the UI
 * @param {Array} categories - Array of category objects
 */
function displayCategories(categories) {
    const container = document.getElementById('categoriesContainer');
    
    if (!container) return;
    
    if (categories.length === 0) {
        container.innerHTML = '<div class="no-categories">No categories found. Add a new category to get started.</div>';
        return;
    }
    
    let html = '';
    
    categories.forEach(category => {
        html += `
            <div class="category-item" id="category-${category.id}">
                <div class="category-icon">
                    <i class="${category.icon}"></i>
                </div>
                <div class="category-details">
                    <h3 class="category-name">${category.name}</h3>
                    <p class="category-description">${category.description}</p>
                </div>
                <div class="category-actions">
                    <button class="edit-category-btn" 
                            data-id="${category.id}" 
                            data-name="${category.name}" 
                            data-description="${category.description}" 
                            data-icon="${category.icon}">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="delete-category-btn" 
                            data-id="${category.id}" 
                            data-name="${category.name}">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    
    // Re-initialize buttons for the newly added elements
    initializeModalHandlers();
}

/**
 * Function to sort categories
 * @param {string} sortBy - Property to sort by
 * @param {boolean} ascending - Sort direction
 */
function sortCategories(sortBy, ascending = true) {
    // Get all category items
    const categoryItems = Array.from(document.querySelectorAll('.category-item'));
    const container = document.getElementById('categoriesContainer');
    
    if (!container || categoryItems.length === 0) return;
    
    // Sort the items
    categoryItems.sort((a, b) => {
        let valueA, valueB;
        
        if (sortBy === 'name') {
            valueA = a.querySelector('.category-name').textContent.toLowerCase();
            valueB = b.querySelector('.category-name').textContent.toLowerCase();
        } else if (sortBy === 'date') {
            // Assuming we have a data-created attribute
            valueA = new Date(a.getAttribute('data-created'));
            valueB = new Date(b.getAttribute('data-created'));
        }
        
        if (ascending) {
            return valueA > valueB ? 1 : -1;
        } else {
            return valueA < valueB ? 1 : -1;
        }
    });
    
    // Clear the container
    container.innerHTML = '';
    
    // Append the sorted items
    categoryItems.forEach(item => {
        container.appendChild(item);
    });
}

// Export functions for testing purposes
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        initializeModalHandlers,
        initializeSearchFunctionality,
        loadCategories,
        displayCategories,
        sortCategories
    };
}