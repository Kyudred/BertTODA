<?php
include 'database.php';

// Handle POST requests for category management
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add_category') {
        $categoryName = trim($_POST['categoryName']);
        $description = trim($_POST['categoryDescription']);
        $icon = trim($_POST['categoryIcon']);
        $date = date("Y-m-d");
        
        // Check if category name already exists
        $check_sql = "SELECT COUNT(*) as count FROM categories WHERE categoryName = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $categoryName);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            header("Location: categories.php?status=error&message=Category name already exists");
            exit();
        }
        
        $sql = "INSERT INTO categories (categoryName, description, icon, dateCreated) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $categoryName, $description, $icon, $date);
        
        if ($stmt->execute()) {
            header("Location: categories.php?status=success&message=Category added successfully");
        } else {
            header("Location: categories.php?status=error&message=Failed to add category: " . $conn->error);
        }
        exit();
    }
    
    if ($action == 'update_category') {
        $id = $_POST['categoryID'];
        $categoryName = trim($_POST['categoryName']);
        $description = trim($_POST['categoryDescription']);
        $icon = trim($_POST['categoryIcon']);
        
        // Check if new category name already exists (excluding current category)
        $check_sql = "SELECT COUNT(*) as count FROM categories WHERE categoryName = ? AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $categoryName, $id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            header("Location: categories.php?status=error&message=Category name already exists");
            exit();
        }
        
        $sql = "UPDATE categories SET categoryName=?, description=?, icon=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $categoryName, $description, $icon, $id);
        
        if ($stmt->execute()) {
            header("Location: categories.php?status=success&message=Category updated successfully");
        } else {
            header("Location: categories.php?status=error&message=Failed to update category: " . $conn->error);
        }
        exit();
    }
    
    if ($action == 'delete_category') {
        $id = $_POST['categoryID'];
        
        // First check if category is in use
        $check_sql = "SELECT COUNT(*) as count FROM items WHERE category = (SELECT categoryName FROM categories WHERE id = ?)";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            header("Location: categories.php?status=error&message=Cannot delete category that is in use");
            exit();
        }
        
        $sql = "DELETE FROM categories WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            header("Location: categories.php?status=success&message=Category deleted successfully");
        } else {
            header("Location: categories.php?status=error&message=Failed to delete category: " . $conn->error);
        }
        exit();
    }
}

// Handle GET requests for API
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['fetch'])) {
    header('Content-Type: application/json');
    
    if ($_GET['fetch'] == 'categories') {
        $sql = "SELECT id, categoryName FROM categories ORDER BY categoryName";
        $result = $conn->query($sql);
        
        if (!$result) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to fetch categories']);
            exit();
        }
        
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $categories]);
        exit();
    }
}

// Get all categories
$sql = "SELECT c.id, c.categoryName, c.description, c.icon, c.dateCreated, 
        COALESCE(COUNT(i.itemID), 0) as itemCount 
        FROM categories c 
        LEFT JOIN items i ON c.categoryName = i.category 
        GROUP BY c.id, c.categoryName, c.description, c.icon, c.dateCreated";
$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}
$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - SK Barangay San Isidro</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="categories.css">
    
    <style>
        /* Dropdown Toggle */
        .dropdown-toggle {
            position: relative;
            cursor: pointer;
        }

        .toggle-icon {
            margin-left: auto;
            transition: transform 0.3s ease;
        }

        .dropdown-toggle.active .toggle-icon {
            transform: rotate(180deg);
        }

        /* Submenu */
        .submenu {
            display: none;
            background-color: var(--light-grey);
            padding-left: 1rem;
            list-style: none;
        }

        .submenu li {
            margin: 0;
            padding: 0;
        }

        .submenu a {
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            color: var(--dark-grey);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .submenu a:hover {
            background-color: var(--lightest-pink);
            border-left: 3px solid var(--pink-color);
            color: var(--pink-color);
        }

        .submenu a.active {
            background-color: var(--lightest-pink);
            border-left: 3px solid var(--pink-color);
            color: var(--pink-color);
        }

        /* When sidebar is expanded */
        .sidebar:hover .submenu {
            display: block;
        }

        .sidebar:hover .dropdown-toggle.active + .submenu {
            display: block;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="user-info">
                <span class="user-name">Welcome, Administrator</span>
            </div>
            <a href="logout.php" class="logout-btn">LOGOUT</a>
        </div>
        
        <!-- Header Section -->
        <div class="header">
            <div class="header-left">
                <img src="Logo/SK.png" alt="SK Logo">
                <h1>
                    <span class="pink">SANGGUNIANG KABATAAN</span>
                    <div class="header-line"></div>
                    <span class="black">BARANGAY SAN ISIDRO</span>
                </h1>
            </div>
        </div>
        
        <div class="dashboard-container">
            <!-- Sidebar -->
            <div class="sidebar" id="sidebar">
                <ul>
                    <li>
                        <a href="#" id="inventory-link" class="dropdown-toggle">
                            <span class="icon"><i class="fas fa-box"></i></span>
                            <span class="text">Inventory System</span>
                            <span class="toggle-icon"><i class="fas fa-chevron-down"></i></span>
                        </a>
                        <ul class="submenu">
                            <li>
                                <a href="items_overview.php" id="items-overview-link">
                                    <span class="icon"><i class="fas fa-tachometer-alt"></i></span>
                                    <span class="text">Items Overview</span>
                                </a>
                            </li>
                            <li>
                                <a href="categories.php" id="categories-link" class="active">
                                    <span class="icon"><i class="fas fa-list"></i></span>
                                    <span class="text">Categories</span>
                                </a>
                            </li>
                            <li>
                                <a href="items.php" id="items-link">
                                    <span class="icon"><i class="fas fa-boxes"></i></span>
                                    <span class="text">Items</span>
                                </a>
                            </li>
                            <li>
                                <a href="usage.php" id="usage-link">
                                    <span class="icon"><i class="fas fa-undo"></i></span>
                                    <span class="text">Use/Return</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#" id="posts-link" class="dropdown-toggle">
                            <span class="icon"><i class="fas fa-newspaper"></i></span>
                            <span class="text">Posts</span>
                            <span class="toggle-icon"><i class="fas fa-chevron-down"></i></span>
                        </a>
                        <ul class="submenu">
                            <li>
                                <a href="post-news.html" id="post-news-link">
                                    <span class="icon"><i class="fas fa-bullhorn"></i></span>
                                    <span class="text">Post News</span>
                                </a>
                            </li>
                            <li>
                                <a href="post-project.html" id="post-project-link">
                                    <span class="icon"><i class="fas fa-project-diagram"></i></span>
                                    <span class="text">Post Project</span>
                                </a>
                            </li>
                            <li>
                                <a href="manage-posts.html" id="manage-posts-link">
                                    <span class="icon"><i class="fas fa-tasks"></i></span>
                                    <span class="text">Manage Posts</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="events.html" id="events-link">
                            <span class="icon"><i class="fas fa-calendar"></i></span>
                            <span class="text">Events Calendar</span>
                        </a>
                    </li>
                    <li>
                        <a href="inquiry.html" id="inquiry-link">
                            <span class="icon"><i class="fa-solid fa-clipboard"></i></span>
                            <span class="text">Inquiry History</span>
                        </a>
                    </li>
                    <li>
                        <a href="Home.html">
                            <span class="icon"><i class="fas fa-home"></i></span>
                            <span class="text">Back to Website</span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Main Content -->
            <div class="main-content">
                <div class="page-title">
                    <span>Categories Management</span>
                    <button class="btn btn-primary" onclick="document.getElementById('addCategoryModal').style.display='flex'">
                        <i class="fas fa-plus"></i> Add New Category
                    </button>
                </div>
                
                <!-- Search and Filter Section -->
                <div class="search-filter">
                    <div class="search-box">
                        <input type="text" class="search-input" id="categorySearch" placeholder="Search categories...">
                        <button class="search-button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Categories List -->
                <div class="card">
                    <div class="card-header">
                        <h2>All Categories (<?= count($categories) ?>)</h2>
                    </div>
                    <div class="card-body">
                        <table>
                            <thead>
                                <tr>
                                    <th>Category Name</th>
                                    <th>Description</th>
                                    <th>Items Count</th>
                                    <th>Date Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td>
                                        <i class="fas <?= $category['icon'] ?> category-icon"></i>
                                        <?= htmlspecialchars($category['categoryName']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($category['description']) ?></td>
                                    <td><span class="items-count"><?= $category['itemCount'] ?></span></td>
                                    <td><?= $category['dateCreated'] ?></td>
                                    <td>
                                        <button class="btn btn-primary btn-action" onclick="editCategory(<?= htmlspecialchars(json_encode($category)) ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-danger btn-action" onclick="deleteCategory(<?= $category['id'] ?>, '<?= htmlspecialchars($category['categoryName']) ?>')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pink Footer -->
        <div class="pink-footer">
            <a href="https://www.facebook.com" target="_blank">
                <i class="fab fa-facebook fa-2x facebook-icon"></i>
            </a>
        </div>
        
        <!-- Add Category Modal -->
        <div id="addCategoryModal" class="modal">
            <div class="modal-content">
                <span class="close-modal" onclick="document.getElementById('addCategoryModal').style.display='none'">&times;</span>
                <div class="modal-title">Add New Category</div>
                <form action="categories.php" method="POST">
                    <input type="hidden" name="action" value="add_category">
                    <div class="form-group">
                        <label for="categoryName">Category Name</label>
                        <input type="text" id="categoryName" name="categoryName" class="form-control" placeholder="Enter category name" required>
                    </div>
                    <div class="form-group">
                        <label for="categoryDescription">Description</label>
                        <textarea id="categoryDescription" name="categoryDescription" class="form-control" rows="3" placeholder="Enter description"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="categoryIcon">Icon</label>
                        <select id="categoryIcon" name="categoryIcon" class="form-control" style="display:block; margin-bottom: 20px;">
                            <option value="fa-laptop">Electronic Device</option>
                            <option value="fa-basketball-ball">Sports Equipment</option>
                            <option value="fa-microphone">Audio Equipment</option>
                            <option value="fa-chair">Furniture</option>
                            <option value="fa-paint-brush">Arts & Crafts</option>
                            <option value="fa-book">Books & Materials</option>
                            <option value="fa-tools">Tools & Equipment</option>
                            <option value="fa-star">Other</option>
                        </select>
                        <div id="icon-preview-container" style="width:100%; display:flex; justify-content:center; align-items:center;">
                            <span id="icon-preview" style="font-size:2em; transition: transform 0.2s, box-shadow 0.2s;">
                                <i class="fas fa-laptop"></i>
                            </span>
                        </div>
                    </div>
                    <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('addCategoryModal').style.display='none'">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Edit Category Modal -->
        <div id="editCategoryModal" class="modal">
            <div class="modal-content">
                <span class="close-modal" onclick="document.getElementById('editCategoryModal').style.display='none'">&times;</span>
                <div class="modal-title">Edit Category</div>
                <form action="categories.php" method="POST">
                    <input type="hidden" name="action" value="update_category">
                    <input type="hidden" name="categoryID" id="editCategoryID">
                    <div class="form-group">
                        <label for="editCategoryName">Category Name</label>
                        <input type="text" id="editCategoryName" name="categoryName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editCategoryDescription">Description</label>
                        <textarea id="editCategoryDescription" name="categoryDescription" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="editCategoryIcon">Icon</label>
                        <select id="editCategoryIcon" name="categoryIcon" class="form-control" style="display:block; margin-bottom: 20px;">
                            <option value="fa-laptop">Electronic Device</option>
                            <option value="fa-basketball-ball">Sports Equipment</option>
                            <option value="fa-microphone">Audio Equipment</option>
                            <option value="fa-chair">Furniture</option>
                            <option value="fa-paint-brush">Arts & Crafts</option>
                            <option value="fa-book">Books & Materials</option>
                            <option value="fa-tools">Tools & Equipment</option>
                            <option value="fa-star">Other</option>
                        </select>
                        <div id="edit-icon-preview-container" style="width:100%; display:flex; justify-content:center; align-items:center;">
                            <span id="edit-icon-preview" style="font-size:2em; transition: transform 0.2s, box-shadow 0.2s;">
                                <i class="fas fa-laptop"></i>
                            </span>
                        </div>
                    </div>
                    <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('editCategoryModal').style.display='none'">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Delete Category Modal -->
        <div id="deleteCategoryModal" class="modal">
            <div class="modal-content">
                <span class="close-modal" onclick="document.getElementById('deleteCategoryModal').style.display='none'">&times;</span>
                <div class="modal-title">Delete Category</div>
                <form action="categories.php" method="POST">
                    <input type="hidden" name="action" value="delete_category">
                    <input type="hidden" name="categoryID" id="deleteCategoryID">
                    <p>Are you sure you want to delete the category "<span id="deleteCategoryName"></span>"?</p>
                    <p class="warning">This action cannot be undone.</p>
                    <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('deleteCategoryModal').style.display='none'">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle dropdown toggles
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
            
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Toggle active class
                    this.classList.toggle('active');
                    
                    // Get the submenu
                    const submenu = this.nextElementSibling;
                    const toggleIcon = this.querySelector('.toggle-icon i');
                    
                    // Toggle submenu visibility
                    if (submenu.style.display === 'block') {
                        submenu.style.display = 'none';
                        toggleIcon.classList.remove('fa-chevron-up');
                        toggleIcon.classList.add('fa-chevron-down');
                    } else {
                        submenu.style.display = 'block';
                        toggleIcon.classList.remove('fa-chevron-down');
                        toggleIcon.classList.add('fa-chevron-up');
                    }
                    
                    // Close other dropdowns
                    dropdownToggles.forEach(otherToggle => {
                        if (otherToggle !== this) {
                            otherToggle.classList.remove('active');
                            const otherSubmenu = otherToggle.nextElementSibling;
                            const otherIcon = otherToggle.querySelector('.toggle-icon i');
                            otherSubmenu.style.display = 'none';
                            otherIcon.classList.remove('fa-chevron-up');
                            otherIcon.classList.add('fa-chevron-down');
                        }
                    });
                });
            });

            // Set active state for current page
            const currentPage = window.location.pathname.split('/').pop();
            const menuLinks = document.querySelectorAll('.sidebar a');
            
            menuLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href === currentPage) {
                    link.classList.add('active');
                    // If it's in a submenu, also activate the parent dropdown
                    const parentDropdown = link.closest('.submenu')?.previousElementSibling;
                    if (parentDropdown && parentDropdown.classList.contains('dropdown-toggle')) {
                        parentDropdown.classList.add('active');
                        const submenu = parentDropdown.nextElementSibling;
                        const toggleIcon = parentDropdown.querySelector('.toggle-icon i');
                        submenu.style.display = 'block';
                        toggleIcon.classList.remove('fa-chevron-down');
                        toggleIcon.classList.add('fa-chevron-up');
                    }
                }
            });

            // Icon preview for Add Category modal
            const iconSelect = document.getElementById('categoryIcon');
            const iconPreview = document.getElementById('icon-preview');
            const iconPreviewContainer = document.getElementById('icon-preview-container');
            if (iconSelect && iconPreview) {
                function updateIconPreview() {
                    const iconClass = iconSelect.value;
                    iconPreview.innerHTML = `<i class="fas ${iconClass}"></i>`;
                    iconPreview.style.transform = 'scale(1.2)';
                    iconPreview.style.boxShadow = '0 2px 8px rgba(255,20,147,0.15)';
                    setTimeout(() => {
                        iconPreview.style.transform = 'scale(1)';
                        iconPreview.style.boxShadow = 'none';
                    }, 200);
                }
                iconSelect.addEventListener('change', updateIconPreview);
                iconSelect.addEventListener('focus', () => {
                    iconPreviewContainer.style.background = '#ffe6f2';
                });
                iconSelect.addEventListener('blur', () => {
                    iconPreviewContainer.style.background = 'none';
                });
                updateIconPreview();
            }
            // Icon preview for Edit Category modal
            const editIconSelect = document.getElementById('editCategoryIcon');
            const editIconPreview = document.getElementById('edit-icon-preview');
            const editIconPreviewContainer = document.getElementById('edit-icon-preview-container');
            if (editIconSelect && editIconPreview) {
                function updateEditIconPreview() {
                    const iconClass = editIconSelect.value;
                    editIconPreview.innerHTML = `<i class="fas ${iconClass}"></i>`;
                    editIconPreview.style.transform = 'scale(1.2)';
                    editIconPreview.style.boxShadow = '0 2px 8px rgba(255,20,147,0.15)';
                    setTimeout(() => {
                        editIconPreview.style.transform = 'scale(1)';
                        editIconPreview.style.boxShadow = 'none';
                    }, 200);
                }
                editIconSelect.addEventListener('change', updateEditIconPreview);
                editIconSelect.addEventListener('focus', () => {
                    editIconPreviewContainer.style.background = '#ffe6f2';
                });
                editIconSelect.addEventListener('blur', () => {
                    editIconPreviewContainer.style.background = 'none';
                });
                updateEditIconPreview();
            }
        });

        // Search functionality
        document.getElementById('categorySearch').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const categoryName = row.querySelector('td:first-child').textContent.toLowerCase();
                const description = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                
                if (categoryName.includes(searchTerm) || description.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // Edit category
        function editCategory(category) {
            document.getElementById('editCategoryID').value = category.id;
            document.getElementById('editCategoryName').value = category.categoryName;
            document.getElementById('editCategoryDescription').value = category.description;
            document.getElementById('editCategoryIcon').value = category.icon;
            document.getElementById('editCategoryModal').style.display = 'flex';
        }
        
        // Delete category
        function deleteCategory(id, name) {
            document.getElementById('deleteCategoryID').value = id;
            document.getElementById('deleteCategoryName').textContent = name;
            document.getElementById('deleteCategoryModal').style.display = 'flex';
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
        
        // Show success/error messages
        <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
        alert('<?= $_GET['message'] ?>');
        <?php endif; ?>
    </script>
</body>
</html>