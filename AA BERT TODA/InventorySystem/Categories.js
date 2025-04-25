const categoryModal = document.getElementById('categoryModal');
const categoryIdInput = document.getElementById('categoryId'); // Reference to the new ID input
const categoryNameInput = document.getElementById('categoryName');
let categories = []; // Array to store categories

function openCategoryModal() {
    categoryIdInput.value = ''; // Clear ID input field
    categoryNameInput.value = ''; // Clear name input field
    categoryModal.style.display = 'flex'; // Show modal
}

function closeCategoryModal() {
    categoryModal.style.display = 'none'; // Hide modal
    categoryModal.removeAttribute('data-edit-id'); // Clear edit mode
    categoryIdInput.value = ''; // Clear ID input field
    categoryNameInput.value = ''; // Clear name input field
}

function submitCategory() {
    const categoryId = categoryIdInput.value.trim(); // Get the ID input value
    const categoryName = categoryNameInput.value.trim(); // Get the Name input value

    // Validate inputs
    if (!categoryId || !categoryName) {
        alert('Please fill in both the Category ID and Name.');
        return;
    }

    // Check if the modal is in edit mode
    const editId = categoryModal.getAttribute('data-edit-id');
    if (editId) {
        // Update the existing category
        const categoryIndex = categories.findIndex(c => c.id === editId);
        if (categoryIndex !== -1) {
            categories[categoryIndex] = { id: categoryId, name: categoryName };
        }
        categoryModal.removeAttribute('data-edit-id'); // Clear edit mode
    } else {
        // Check if the ID is unique for new categories
        const isDuplicateId = categories.some(category => category.id === categoryId);
        if (isDuplicateId) {
            alert(`Category ID "${categoryId}" already exists. Please use a unique ID.`);
            return;
        }

        // Add the new category to the array
        categories.push({ id: categoryId, name: categoryName });
    }

    // Update the table
    renderCategories();

    // Hide the modal
    closeCategoryModal();

    // Show success message
    const successMessage = document.getElementById('successMessage');
    successMessage.style.display = 'block';

    // Hide the success message after a delay
    setTimeout(() => {
        successMessage.style.display = 'none';
    }, 2000); // 2 seconds delay
}
    
function renderCategories() {
    const categoryTableBody = document.getElementById('categoryTableBody');
    categoryTableBody.innerHTML = ''; // Clear the table body

    categories.forEach((category, index) => {
        categoryTableBody.innerHTML += `
            <tr>
                <td>${index + 1}</td>
                <td>${category.id}</td>
                <td>${category.name}</td>
                <td>
                    <button class="btn-edit" onclick="editCategory('${category.id}')">Edit</button>
                    <button class="btn-delete" onclick="deleteCategory('${category.id}')">Delete</button>
                </td>
            </tr>
        `;
    });
}

function editCategory(id) {
    console.log(categories);
    const category = categories.find(c => c.id === id);
    if (category) {
        categoryIdInput.value = category.id;
        categoryNameInput.value = category.name;
        categoryModal.setAttribute('data-edit-id', id); // Store the ID being edited
        openCategoryModal();
    }
}

function deleteCategory(id) {
    categories = categories.filter(c => c.id !== id);
    renderCategories();
}

document.getElementById('search').addEventListener('input', function () {
    const filter = this.value.toLowerCase(); // Get the search input and convert to lowercase
    const rows = document.querySelectorAll('#categoryTableBody tr'); // Get all table rows

    rows.forEach(row => {
        const cells = row.querySelectorAll('td'); // Get all cells in the row
        const match = Array.from(cells).some(cell => cell.textContent.toLowerCase().includes(filter)); // Check if any cell matches the filter
        row.style.display = match ? '' : 'none'; // Show or hide the row based on the match
    });
});