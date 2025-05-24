<?php
include 'database.php';

// Get total items count
$total_items_query = "SELECT COUNT(*) as total FROM items";
$total_items_result = $conn->query($total_items_query);
$total_items = $total_items_result->fetch_assoc()['total'];

// Get total categories count
$total_categories_query = "SELECT COUNT(*) as total FROM categories";
$total_categories_result = $conn->query($total_categories_query);
$total_categories = $total_categories_result->fetch_assoc()['total'];

// Get items currently in use count
$in_use_query = "SELECT COUNT(*) as total FROM items WHERE status='in-use'";
$in_use_result = $conn->query($in_use_query);
$in_use_items = $in_use_result->fetch_assoc()['total'];

// Get total returned items count from return_history table
$returned_query = "SELECT COUNT(*) as total FROM return_history";
$returned_result = $conn->query($returned_query);
$returned_items = $returned_result->fetch_assoc()['total'];

// Get 3 most recent items
$recent_items_query = "SELECT * FROM items ORDER BY dateAdded DESC, itemID DESC LIMIT 3";
$recent_items_result = $conn->query($recent_items_query);

// Get 3 most recent returns from return_history
$recent_returns_query = "SELECT i.name, u.borrowerName, u.borrowDate, rh.returnDate, rh.returnCondition
FROM return_history rh
INNER JOIN items i ON rh.itemID = i.itemID
INNER JOIN usage_log u ON rh.usageID = u.id
ORDER BY rh.returnDate DESC, rh.created_at DESC
LIMIT 3";
$recent_returns_result = $conn->query($recent_returns_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Items Overview - SK Barangay San Isidro</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="180x180" href="Logo/SK_LOGO.jpg">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="items_overview.css">

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
                <!-- Items Overview -->
                <div id="items-overview">
                    <div class="overview-title"><h1>Items Overview  </h1></div>
                    
                    <div class="dashboard-cards">
                        <!-- Card 1 -->
                        <a href="items.php" class="card card-blue">
                            <div class="card-info">
                                <div class="card-value"><?php echo $total_items; ?></div>
                                <div class="card-label">TOTAL ITEMS</div>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-boxes fa-lg"></i>
                            </div>
                            <div class="more-info">
                                MORE INFO <div class="info-icon">i</div>
                            </div>
                        </a>
                        
                        <!-- Card 2 -->
                        <a href="categories.php" class="card card-purple"> 
                            <div class="card-info">
                                <div class="card-value"><?php echo $total_categories; ?></div>
                                <div class="card-label">CATEGORIES</div>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-th-large fa-lg"></i>
                            </div>
                            <div class="more-info">
                                MORE INFO <div class="info-icon">i</div>
                            </div>
                        </a>
                        
                        <!-- Card 3 -->
                        <a href="usage.php#items-in-use" class="card card-green">
                            <div class="card-info">
                                <div class="card-value"><?php echo $in_use_items; ?></div>
                                <div class="card-label">ITEM IN USE</div>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-clipboard-check fa-lg"></i>
                            </div>
                            <div class="more-info">
                                MORE INFO <div class="info-icon">i</div>
                            </div>
                        </a>
                        
                        <!-- Card 4 -->
                        <a href="usage.php#return-history" class="card card-orange">
                            <div class="card-info">
                                <div class="card-value"><?php echo $returned_items; ?></div>
                                <div class="card-label">ITEM RETURNED</div>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-undo fa-lg"></i>
                            </div>
                            <div class="more-info">
                                MORE INFO <div class="info-icon">i</div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="dashboard-table">
                        <h2>Recent Items</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Category</th>
                                    <th>Date Added</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $recent_items_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['category']) ?></td>
                                    <td><?= date('F j, Y', strtotime($row['dateAdded'])) ?></td>
                                    <td><span class="badge badge-<?= $row['status'] === 'available' ? 'success' : ($row['status'] === 'in-use' ? 'warning' : ($row['status'] === 'damaged' ? 'danger' : 'secondary')) ?>">
                                        <?= $row['status'] === 'in-use' ? 'In Use' : ucfirst($row['status']) ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="dashboard-table">
                        <h2>Item Usage History</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>User</th>
                                    <th>Date Borrowed</th>
                                    <th>Date Returned</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $recent_returns_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['borrowerName']) ?></td>
                                    <td><?= date('F j, Y', strtotime($row['borrowDate'])) ?></td>
                                    <td><?= date('F j, Y', strtotime($row['returnDate'])) ?></td>
                                    <td><span class="badge badge-<?= $row['returnCondition'] === 'Damaged' ? 'danger' : ($row['returnCondition'] === 'Fair' || $row['returnCondition'] === 'Poor' ? 'warning' : 'success') ?>">
                                        <?= htmlspecialchars($row['returnCondition']) ?></span></td>
                                </tr>
                                <?php endwhile; ?>
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
        });
    </script>
</body>
</html>