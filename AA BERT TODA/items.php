<?php
include 'database.php';

// Get all items from database with proper error handling
try {
    $sql = "SELECT 
                i.*,
                COALESCE(u.borrowerName, '') as currentBorrower,
                COALESCE(u.borrowDate, '') as borrowDate,
                COALESCE(u.expectedReturn, '') as expectedReturn
            FROM items i
            LEFT JOIN usage_log u ON i.itemID = u.itemID AND u.status = 'borrowed'
            ORDER BY i.itemID DESC";
            
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $totalItems = $result->num_rows;
    
    // Get categories for filter
    $categories = $conn->query("SELECT categoryName FROM categories ORDER BY categoryName");
    if (!$categories) {
        throw new Exception("Failed to fetch categories: " . $conn->error);
    }
    
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $error = "Error loading items: " . $e->getMessage();
    $totalItems = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Items Management - SK Barangay San Isidro</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="items.css">
    <style>
        .notice-content {
            max-width: 400px;
            margin: 15% auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .notice-content .modal-header {
            background-color: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
            border-radius: 8px 8px 0 0;
        }

        .notice-content .modal-header h2 {
            margin: 0;
            color: #333;
            font-size: 1.25rem;
        }

        .notice-content .modal-body {
            padding: 20px;
            text-align: center;
        }

        .notice-content .modal-body p {
            margin: 0 0 20px 0;
            color: #666;
            font-size: 1rem;
        }

        .notice-content .modal-actions {
            text-align: center;
        }

        .notice-content .btn-primary {
            padding: 8px 20px;
            background-color: #ff3b30;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .notice-content .btn-primary:hover {
            background-color: #c0392b;
        }

        .notice-content .close-modal {
            float: right;
            font-size: 1.5rem;
            font-weight: bold;
            color: #666;
            cursor: pointer;
            transition: color 0.2s;
        }

        .notice-content .close-modal:hover {
            color: #333;
        }

        /* Expand sidebar when a dropdown is open */
        .sidebar.expanded,
        .sidebar:hover {
            width: var(--sidebar-width);
        }
        .sidebar.expanded .text,
        .sidebar:hover .text {
            opacity: 1;
        }
        .sidebar.expanded .submenu,
        .sidebar:hover .submenu {
            display: block;
        }
        .sidebar.expanded .dropdown-toggle.active + .submenu,
        .sidebar:hover .dropdown-toggle.active + .submenu {
            display: block;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Add this right after main-container div opens -->
        <?php
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            if (isset($_GET['status'])) {
                if ($_GET['status'] === 'error') {
                    $message = isset($_GET['message']) ? $_GET['message'] : 'An error occurred while deleting the item.';
                    echo '<div class="alert alert-danger" style="position: fixed; top: 20px; right: 20px; z-index: 1000; padding: 15px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px;">
                            <i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($message) . '
                            <button type="button" class="close-alert" style="float: right; background: none; border: none; color: #721c24; cursor: pointer;">&times;</button>
                          </div>';
                } elseif ($_GET['status'] === 'success') {
                    echo '<div class="alert alert-success" style="position: fixed; top: 20px; right: 20px; z-index: 1000; padding: 15px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px;">
                            <i class="fas fa-check-circle"></i> Item successfully deleted.
                            <button type="button" class="close-alert" style="float: right; background: none; border: none; color: #155724; cursor: pointer;">&times;</button>
                          </div>';
                }
            }
        }
        ?>
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
                                <a href="items_overview.php" id="items-overview-link" class="active">
                                    <span class="icon"><i class="fas fa-tachometer-alt"></i></span>
                                    <span class="text">Items Overview</span>
                                </a>
                            </li>
                            <li>
                                <a href="categories.php" id="categories-link">
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
            <div class="main-content" id="main-content">
                <div class="page-header">
                    <h1 class="page-title">Items Management</h1>
                    <button id="add-item-btn" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Item
                    </button>
                </div>

                <!-- Search and Filter Section -->
                <div class="search-filter-container">
                    <div class="search-box">
                        <input type="text" id="search-input" placeholder="Search items...">
                        <button id="search-btn"><i class="fas fa-search"></i></button>
                    </div>
                    <div class="filter-options">
                        <select id="category-filter">
                            <option value="">All Categories</option>
                            <?php 
                            if (isset($categories)) {
                                while($cat = $categories->fetch_assoc()) {
                                    echo "<option value='" . htmlspecialchars($cat['categoryName']) . "'>" . 
                                         htmlspecialchars($cat['categoryName']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                        <select id="status-filter">
                            <option value="">All Status</option>
                            <option value="available">Available</option>
                            <option value="in-use">In Use</option>
                            <option value="maintenance">Under Maintenance</option>
                            <option value="damaged">Damaged</option>
                        </select>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="items-table-container">
                    <table id="items-table">
                        <thead>
                            <tr>
                                <th>Item ID</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Status</th>
                                <th>Current Borrower</th>
                                <th>Expected Return</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (isset($error)) {
                                echo '<tr><td colspan="8" class="error-message">' . htmlspecialchars($error) . '</td></tr>';
                            } elseif ($totalItems > 0) {
                                while($row = $result->fetch_assoc()): 
                                    // Define status class based on status
                                    $statusClass = "";
                                    switch($row['status']) {
                                        case 'available': $statusClass = "badge-success"; break;
                                        case 'in-use': $statusClass = "badge-warning"; break;
                                        case 'maintenance': $statusClass = "badge-info"; break;
                                        case 'damaged': $statusClass = "badge-danger"; break;
                                        default: $statusClass = "badge-secondary";
                                    }
                                    
                                    // Format status text
                                    $statusText = $row['status'] === 'maintenance' ? 'Under Maintenance' : 
                                        ucfirst(str_replace('-', ' ', $row['status']));
                                    
                                    // Format dates
                                    $expectedReturn = !empty($row['expectedReturn']) ? 
                                        date('M d, Y', strtotime($row['expectedReturn'])) : '-';
                            ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['itemID']) ?></td>
                                        <td><?= htmlspecialchars($row['name']) ?></td>
                                        <td><?= htmlspecialchars($row['category']) ?></td>
                                        <td><?= htmlspecialchars($row['quantity']) ?></td>
                                        <td><span class="badge <?= $statusClass ?>"><?= $statusText ?></span></td>
                                        <td><?= htmlspecialchars($row['currentBorrower'] ?: '-') ?></td>
                                        <td><?= $expectedReturn ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view-btn" data-id="<?= $row['itemID'] ?>" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="action-btn edit-btn" data-id="<?= $row['itemID'] ?>" title="Edit Item">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="action-btn delete-btn" data-id="<?= $row['itemID'] ?>" title="Delete Item">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile;
                            } else {
                                echo '<tr><td colspan="8" class="no-results">No items found in the database.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination-container">
                    <div class="pagination">
                        <button id="prev-page" class="page-btn"><i class="fas fa-chevron-left"></i></button>
                        <div id="page-numbers">
                            <span class="page-number active">1</span>
                            <span class="page-number">2</span>
                            <span class="page-number">3</span>
                        </div>
                        <button id="next-page" class="page-btn"><i class="fas fa-chevron-right"></i></button>
                    </div>
                    <div class="items-per-page">
                        <label for="items-per-page-select">Items per page:</label>
                        <select id="items-per-page-select">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal for Add/Edit Item -->
        <div id="item-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="modal-title">Add New Item</h2>
                    <span class="close-modal">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="item-form" action="crud.php" method="POST">
                        <input type="hidden" name="action" value="create" id="form-action">
                        <input type="hidden" name="itemID" id="item-id">
                        
                        <div class="form-group">
                            <label for="name">Item Name:</label>
                            <input type="text" id="item-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="category">Category:</label>
                            <select id="item-category" name="category" required>
                                <option value="">Select Category</option>
                                <?php 
                                // Reset the categories result pointer
                                if (isset($categories)) {
                                    $categories->data_seek(0);
                                    while($cat = $categories->fetch_assoc()) {
                                        echo "<option value='" . htmlspecialchars($cat['categoryName']) . "'>" . 
                                             htmlspecialchars($cat['categoryName']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="quantity">Quantity:</label>
                            <input type="number" id="item-quantity" name="quantity" min="1" required>
                        </div>  
                        <div class="form-group">
                            <label for="status">Status:</label>
                            <select id="item-status" name="status" required>
                                <option value="available">Available</option>
                                <option value="in-use">In Use</option>
                                <option value="maintenance">Under Maintenance</option>
                                <option value="damaged">Damaged</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="description">Description:</label>
                            <textarea id="item-description" name="description" rows="4"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="notes">Notes:</label>
                            <textarea id="item-notes" name="notes" rows="2"></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="button" id="cancel-item" class="btn btn-secondary">Cancel</button>
                            <button type="submit" id="save-item" class="btn btn-primary">Save Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal for Item Details -->
        <div id="item-details-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="detail-modal-title">Item Details</h2>
                    <span class="close-modal">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="item-details-container">
                        <div class="item-info">
                            <div class="detail-row">
                                <span class="detail-label">Item Name:</span>
                                <span id="detail-item-name" class="detail-value"></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Category:</span>
                                <span id="detail-item-category" class="detail-value"></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Quantity:</span>
                                <span id="detail-item-quantity" class="detail-value"></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Status:</span>
                                <span id="detail-item-status" class="detail-value"></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date Added:</span>
                                <span id="detail-item-date" class="detail-value"></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Last Updated:</span>
                                <span id="detail-item-updated" class="detail-value"></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Description:</span>
                                <div id="detail-item-description" class="detail-value-block"></div>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Notes:</span>
                                <div id="detail-item-notes" class="detail-value-block"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button id="edit-item-btn" class="btn btn-primary">Edit Item</button>
                        <button id="delete-item-btn" class="btn btn-danger">Delete Item</button>
                        <button id="close-details-btn" class="btn btn-secondary">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <div id="confirm-modal" class="modal">
            <div class="modal-content confirm-content">
                <div class="modal-header">
                    <h2>Confirm Delete</h2>
                    <span class="close-modal">&times;</span>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this item? This action cannot be undone.</p>
                    <div class="modal-actions">
                        <button id="cancel-delete" class="btn btn-secondary">Cancel</button>
                        <form id="delete-form" action="crud.php" method="POST">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="itemID" id="delete-item-id">
                            <button id="confirm-delete" type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notice Modal -->
        <div id="notice-modal" class="modal">
            <div class="modal-content notice-content">
                <div class="modal-header">
                    <h2>Notice</h2>
                    <span class="close-modal">&times;</span>
                </div>
                <div class="modal-body">
                    <p id="notice-message"></p>
                    <div class="modal-actions">
                        <button id="close-notice" class="btn btn-primary">OK</button>
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
    </div>

    <!-- JavaScript -->
    <script src="js/dropdown.js"></script>
    <script>
        // Store items from PHP to JavaScript
        let databaseItems = [];
        
        <?php
        // Reset the result pointer to start
        $result->data_seek(0);
        
        // Loop through results again for JavaScript
        if ($result->num_rows > 0) {
            echo "databaseItems = [";
            while($row = $result->fetch_assoc()) {
                echo "{";
                echo "id: " . $row['itemID'] . ",";
                echo "name: '" . addslashes($row['name']) . "',";
                echo "category: '" . addslashes($row['category']) . "',";
                echo "quantity: " . $row['quantity'] . ",";
                echo "status: '" . $row['status'] . "',";
                echo "dateAdded: '" . ($row['dateAdded'] ?? '') . "',";
                echo "lastUpdated: '" . ($row['lastUpdated'] ?? '') . "',";
                echo "description: '" . addslashes($row['description'] ?? '') . "',";
                echo "notes: '" . addslashes($row['notes'] ?? '') . "'";
                echo "},";
            }
            echo "];";
        }
        ?>

        // Global variables
        let currentItems = [...databaseItems];
        let currentPage = 1;
        let itemsPerPage = 10;
        let selectedItem = null;

        // DOM Elements
        document.addEventListener('DOMContentLoaded', function() {
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
            
            // Close buttons for all modals
            document.querySelectorAll('.close-modal').forEach(btn => {
                btn.addEventListener('click', function() {
                    const modal = this.closest('.modal');
                    if (modal) {
                        modal.style.display = 'none';
                    }
                });
            });
            
            // Modal action buttons
            document.getElementById('close-details-btn').addEventListener('click', function() {
                document.getElementById('item-details-modal').style.display = 'none';
            });
            
            document.getElementById('edit-item-btn').addEventListener('click', editCurrentItem);
            document.getElementById('delete-item-btn').addEventListener('click', confirmDelete);
            document.getElementById('cancel-delete').addEventListener('click', function() {
                document.getElementById('confirm-modal').style.display = 'none';
            });
            
            document.getElementById('cancel-item').addEventListener('click', function() {
                document.getElementById('item-modal').style.display = 'none';
            });
            
            // Add event listeners to table buttons
            addTableButtonListeners();
            
            // Prevent modal closing when clicking inside modal content
            document.querySelectorAll('.modal-content').forEach(content => {
                content.addEventListener('click', function(event) {
                    event.stopPropagation();
                });
            });
            
            // Close modals when clicking outside
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('click', function(event) {
                    if (event.target === this) {
                        this.style.display = 'none';
                    }
                });
            });
            
            // Update pagination based on data
            updatePagination();

            // Add alert close functionality
            document.querySelectorAll('.close-alert').forEach(button => {
                button.addEventListener('click', function() {
                    this.parentElement.remove();
                });
            });

            // Auto-hide alerts after 5 seconds
            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(alert => {
                    alert.remove();
                });
            }, 5000);

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

            // Icon preview for Add/Edit Item modal
            const iconSelect = document.getElementById('item-icon');
            const iconPreview = document.getElementById('icon-preview');
            if (iconSelect && iconPreview) {
                function updateIconPreview() {
                    const iconClass = iconSelect.value;
                    iconPreview.innerHTML = `<i class="fas ${iconClass}"></i>`;
                }
                iconSelect.addEventListener('change', updateIconPreview);
                updateIconPreview(); // Set initial preview
            }
        });

        // Filter items based on search input and filter selections
        function filterItems() {
            const searchTerm = document.getElementById('search-input').value.toLowerCase();
            const categoryFilter = document.getElementById('category-filter').value.toLowerCase();
            const statusFilter = document.getElementById('status-filter').value.toLowerCase();
            
            currentItems = databaseItems.filter(item => {
                // Filter by search term
                const matchesSearch = searchTerm === '' || 
                    item.name.toLowerCase().includes(searchTerm) || 
                    item.category.toLowerCase().includes(searchTerm);
                
                // Filter by category
                const matchesCategory = categoryFilter === '' || 
                    item.category.toLowerCase().includes(categoryFilter);
                
                // Filter by status
                const matchesStatus = statusFilter === '' || 
                    item.status.toLowerCase() === statusFilter;
                
                return matchesSearch && matchesCategory && matchesStatus;
            });
            
            currentPage = 1; // Reset to first page
            renderFilteredItems();
        }

        // Render filtered items
        function renderFilteredItems() {
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
                // Define status class based on status
                let statusClass = "";
                switch(item.status) {
                    case 'available': statusClass = "badge-success"; break;
                    case 'in-use': statusClass = "badge-warning"; break;
                    case 'maintenance': statusClass = "badge-info"; break;
                    case 'damaged': statusClass = "badge-danger"; break;
                    default: statusClass = "badge-secondary";
                }
                
                // Format status text
                const statusText = item.status === 'maintenance' ? 'Under Maintenance' : 
                    item.status.charAt(0).toUpperCase() + item.status.slice(1).replace(/-/g, ' ');
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.id}</td>
                    <td>${item.name}</td>
                    <td>${item.category}</td>
                    <td>${item.quantity}</td>
                    <td><span class="badge ${statusClass}">${statusText}</span></td>
                    <td>${item.dateAdded || '-'}</td>
                    <td>${item.lastUpdated || '-'}</td>
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
            
            // Add event listeners to table buttons
            addTableButtonListeners();
            
            // Update pagination
            updatePagination();
        }

        // Add event listeners to table buttons
        function addTableButtonListeners() {
            document.querySelectorAll('.view-btn').forEach(btn => {
                btn.addEventListener('click', () => viewItemDetails(parseInt(btn.dataset.id)));
            });
            
            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const itemId = parseInt(btn.dataset.id);
                    const item = databaseItems.find(item => item.id === itemId);
                    
                    if (item.status === 'in-use') {
                        showNotice('This item is currently in use and cannot be edited.');
                        return;
                    }
                    
                    openEditItemModal(itemId);
                });
            });
            
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const itemId = parseInt(btn.dataset.id);
                    const item = databaseItems.find(item => item.id === itemId);
                    
                    if (item.status === 'in-use') {
                        showNotice('This item is currently in use and cannot be deleted.');
                        return;
                    }
                    
                    selectedItem = item;
                    document.getElementById('delete-item-id').value = itemId;
                    document.getElementById('confirm-modal').style.display = 'block';
                });
            });
        }

        // Update pagination controls
        function updatePagination() {
            const totalPages = Math.max(1, Math.ceil(currentItems.length / itemsPerPage));
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
                    renderFilteredItems();
                });
                pageNumbers.appendChild(pageNumber);
            }
            
            // Disable buttons if at first or last page
            document.getElementById('prev-page').disabled = currentPage === 1;
            document.getElementById('next-page').disabled = currentPage === totalPages;
        }

        // Handle page changes
        function changePage(direction) {
            const totalPages = Math.ceil(currentItems.length / itemsPerPage);
            const newPage = currentPage + direction;
            
            if (newPage >= 1 && newPage <= totalPages) {
                currentPage = newPage;
                renderFilteredItems();
            }
        }

        // Change items per page
        function changeItemsPerPage() {
            const newItemsPerPage = parseInt(document.getElementById('items-per-page-select').value);
            itemsPerPage = newItemsPerPage;
            currentPage = 1; // Reset to first page
            renderFilteredItems();
        }

        // Open add item modal
        function openAddItemModal() {
            // Reset form
            document.getElementById('item-form').reset();
            document.getElementById('modal-title').textContent = 'Add New Item';
            document.getElementById('form-action').value = 'create';
            
            // Show modal
            const modal = document.getElementById('item-modal');
            modal.style.display = 'block';
        }

        // Open edit item modal
        function openEditItemModal(itemId) {
            const item = databaseItems.find(item => item.id === itemId);
            if (!item) return;
            
            selectedItem = item;
            
            // Populate form with item data
            document.getElementById('item-id').value = item.id;
            document.getElementById('item-name').value = item.name;
            document.getElementById('item-category').value = item.category;
            document.getElementById('item-quantity').value = item.quantity;
            document.getElementById('item-status').value = item.status;
            document.getElementById('item-description').value = item.description || '';
            document.getElementById('item-notes').value = item.notes || '';
            
            // Set form action to update
            document.getElementById('form-action').value = 'update';
            
            // Set modal title
            document.getElementById('modal-title').textContent = 'Edit Item';
            
            // Show modal
            const modal = document.getElementById('item-modal');
            modal.style.display = 'block';
        }

        // View item details
        function viewItemDetails(itemId) {
            const item = databaseItems.find(item => item.id === itemId);
            if (!item) return;
            selectedItem = item;
            
            // Populate details
            document.getElementById('detail-item-name').textContent = item.name;
            document.getElementById('detail-item-category').textContent = item.category;
            document.getElementById('detail-item-quantity').textContent = item.quantity;
            
            // Format status text
            const statusText = item.status === 'maintenance' ? 'Under Maintenance' : 
                item.status.charAt(0).toUpperCase() + item.status.slice(1).replace(/-/g, ' ');
            document.getElementById('detail-item-status').textContent = statusText;
            
            document.getElementById('detail-item-date').textContent = item.dateAdded || '-';
            document.getElementById('detail-item-updated').textContent = item.lastUpdated || '-';
            document.getElementById('detail-item-description').textContent = item.description || '-';
            document.getElementById('detail-item-notes').textContent = item.notes || '-';
            
            // Show modal
            const modal = document.getElementById('item-details-modal');
            modal.style.display = 'block';
        }

        // Edit current item
        function editCurrentItem() {
            if (!selectedItem) return;
            document.getElementById('item-details-modal').style.display = 'none';
            openEditItemModal(selectedItem.id);
        }

        // Confirm delete
        function confirmDelete() {
            if (!selectedItem) return;
            document.getElementById('item-details-modal').style.display = 'none';
            document.getElementById('delete-item-id').value = selectedItem.id;
            document.getElementById('confirm-modal').style.display = 'block';
        }

        // Add real-time search functionality
        document.getElementById('search-input').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#items-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Add category filter functionality
        document.getElementById('category-filter').addEventListener('change', function(e) {
            const category = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#items-table tbody tr');
            
            rows.forEach(row => {
                if (!category) {
                    row.style.display = '';
                    return;
                }
                const rowCategory = row.children[2].textContent.toLowerCase();
                row.style.display = rowCategory === category ? '' : 'none';
            });
        });

        // Add status filter functionality
        document.getElementById('status-filter').addEventListener('change', function(e) {
            const status = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#items-table tbody tr');
            
            rows.forEach(row => {
                if (!status) {
                    row.style.display = '';
                    return;
                }
                const rowStatus = row.children[4].textContent.toLowerCase();
                row.style.display = rowStatus === status ? '' : 'none';
            });
        });

        // Add refresh functionality
        /*function refreshItems() {
            location.reload();
        }

        // Auto-refresh every 30 seconds
        //setInterval(refreshItems, 30000);
        */

        // Function to show notice modal
        function showNotice(message) {
            document.getElementById('notice-message').textContent = message;
            document.getElementById('notice-modal').style.display = 'block';
        }

        // Add event listener for notice modal close button
        document.getElementById('close-notice').addEventListener('click', function() {
            document.getElementById('notice-modal').style.display = 'none';
        });

        // Add event listener for notice modal close (x) button
        document.querySelector('#notice-modal .close-modal').addEventListener('click', function() {
            document.getElementById('notice-modal').style.display = 'none';
        });

        document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            this.classList.toggle('active'); // <- This is KEY!
            const submenu = this.nextElementSibling;
            // likely toggling submenu display too here (missing in your snippet)
        });
    });
});
    </script>
</body>
</html>    