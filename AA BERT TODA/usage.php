<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$user = 'root';
$password = ''; // Default password in XAMPP is empty
$dbname = 'inventorysystemdb'; // Replace with your actual database name

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create items table if it doesn't exist
$create_items_table = "CREATE TABLE IF NOT EXISTS items (
    itemID INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(100) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    status ENUM('available', 'in-use', 'damaged', 'lost') NOT NULL DEFAULT 'available',
    dateAdded DATE NOT NULL,
    lastUpdated DATE NOT NULL,
    description TEXT,
    notes TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

try {
    if ($conn->query($create_items_table) === FALSE) {
        throw new Exception("Error creating items table: " . $conn->error);
    }
    
    // Check if items table is empty and insert sample data if it is
    $result = $conn->query("SELECT COUNT(*) as count FROM items");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        // Instead of sample data, fetch from the main items table
        $fetch_items = "INSERT INTO items (name, category, quantity, status, dateAdded, lastUpdated, description, notes)
                       SELECT name, category, quantity, status, dateAdded, lastUpdated, description, notes
                       FROM items
                       WHERE status = 'available'";
        
        if ($conn->query($fetch_items) === FALSE) {
            throw new Exception("Error fetching items: " . $conn->error);
        }
    }
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    die("Error setting up database: " . $e->getMessage());
}

// Create usage_log table if it doesn't exist
$create_usage_table = "CREATE TABLE IF NOT EXISTS usage_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    itemID INT NOT NULL,
    borrowerName VARCHAR(100) NOT NULL,
    borrowerContact VARCHAR(20) NOT NULL,
    borrowDate DATE NOT NULL,
    expectedReturn DATE NOT NULL,
    returnDate DATE NULL,
    purpose TEXT NOT NULL,
    initialCondition VARCHAR(20) NOT NULL,
    returnCondition VARCHAR(20) NULL,
    notes TEXT NULL,
    returnNotes TEXT NULL,
    status ENUM('borrowed', 'returned') NOT NULL DEFAULT 'borrowed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_usage_item FOREIGN KEY (itemID) REFERENCES items(itemID)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

try {
    if ($conn->query($create_usage_table) === FALSE) {
        throw new Exception("Error creating usage_log table: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    die("Error setting up database: " . $e->getMessage());
}

// Create return_history table if it doesn't exist
$create_return_history_table = "CREATE TABLE IF NOT EXISTS return_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usageID INT NOT NULL,
    itemID INT NOT NULL,
    returnDate DATE NOT NULL,
    returnCondition VARCHAR(20) NOT NULL,
    returnNotes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

try {
    if ($conn->query($create_return_history_table) === FALSE) {
        throw new Exception("Error creating return_history table: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    die("Error setting up database: " . $e->getMessage());
}

// Set header to return JSON responses only for API requests
if (isset($_GET['fetch']) || isset($_POST['action'])) {
    header('Content-Type: application/json');
}

// Function to send JSON response
function sendResponse($status, $message = '', $data = null) {
    $response = ['status' => $status];
    if ($message) $response['message'] = $message;
    if ($data !== null) $response['data'] = $data;
    echo json_encode($response);
    exit();
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    try {
        if ($action == 'log_use') {
            // Debug: Log the POST data
            error_log("POST data received: " . print_r($_POST, true));
            
            // Validate required fields
            $required_fields = ['itemID', 'borrowerName', 'borrowerContact', 'borrowDate', 'expectedReturn', 'purpose', 'initialCondition'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    sendResponse('error', "Missing required field: $field");
                }
            }
            
            $item_id = $_POST['itemID'];
            $borrower_name = $_POST['borrowerName'];
            $borrower_contact = $_POST['borrowerContact'];
            $borrow_date = $_POST['borrowDate'];
            $expected_return = $_POST['expectedReturn'];
            $purpose = $_POST['purpose'];
            $initial_condition = $_POST['initialCondition'];
            $notes = $_POST['notes'] ?? '';
            
            // Debug: Log the processed data
            error_log("Processed data: itemID=$item_id, borrowerName=$borrower_name, borrowDate=$borrow_date");
            
            // Check if item exists and is available
            $check_item = "SELECT status FROM items WHERE itemID = ?";
            $stmt = $conn->prepare($check_item);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("i", $item_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                sendResponse('error', 'Item not found');
            }
            
            $item = $result->fetch_assoc();
            if ($item['status'] !== 'available') {
                sendResponse('error', 'Item is not available for use');
            }
            
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Update item status
                $update_item = "UPDATE items SET status='in-use', lastUpdated=NOW() WHERE itemID=?";
                $stmt = $conn->prepare($update_item);
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $stmt->bind_param("i", $item_id);
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
                
                // Log the usage
                $log = "INSERT INTO usage_log (itemID, borrowerName, borrowerContact, borrowDate, expectedReturn, purpose, initialCondition, notes, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'borrowed')";
                $stmt = $conn->prepare($log);
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $stmt->bind_param("isssssss", $item_id, $borrower_name, $borrower_contact, $borrow_date, $expected_return, $purpose, $initial_condition, $notes);
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
                
                $conn->commit();
                sendResponse('success', 'Item usage logged successfully');
            } catch (Exception $e) {
                $conn->rollback();
                error_log("Transaction error: " . $e->getMessage());
                sendResponse('error', 'Database error: ' . $e->getMessage());
            }
        }
        
        if ($action == 'log_return') {
            // Debug logging
            error_log("Processing log_return action with POST data: " . print_r($_POST, true));
            
            // Validate required fields
            $required_fields = ['usageID', 'itemID', 'returnDate', 'returnCondition'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    error_log("Missing required field: " . $field);
                    sendResponse('error', "Missing required field: $field");
                }
            }
            
            $usage_id = $_POST['usageID'];
            $item_id = $_POST['itemID'];
            $return_date = $_POST['returnDate'];
            $return_condition = $_POST['returnCondition'];
            $return_notes = $_POST['returnNotes'] ?? '';
            
            // Debug logging
            error_log("Processed return data: usageID=$usage_id, itemID=$item_id, returnDate=$return_date, returnCondition=$return_condition");
            
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Check if usage record exists and is not already returned
                $check_usage = "SELECT status FROM usage_log WHERE id = ? AND itemID = ?";
                $stmt = $conn->prepare($check_usage);
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $stmt->bind_param("ii", $usage_id, $item_id);
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
                
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    throw new Exception("Usage record not found");
                }
                
                $usage = $result->fetch_assoc();
                if ($usage['status'] === 'returned') {
                    throw new Exception("Item has already been returned");
                }
                
                // Update item status
                $new_status = ($return_condition == 'Damaged') ? 'damaged' : 'available';
                $update_item = "UPDATE items SET status=?, lastUpdated=NOW() WHERE itemID=?";
                $stmt = $conn->prepare($update_item);
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $stmt->bind_param("si", $new_status, $item_id);
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
                
                // Update usage log with return information
                $update_log = "UPDATE usage_log SET 
                              returnDate=?, 
                              returnCondition=?, 
                              returnNotes=?, 
                              status='returned',
                              updated_at=NOW()
                              WHERE id=? AND itemID=?";
                $stmt = $conn->prepare($update_log);
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $stmt->bind_param("sssii", $return_date, $return_condition, $return_notes, $usage_id, $item_id);
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
                
                // Insert into return_history table
                $insert_history = "INSERT INTO return_history 
                                  (usageID, itemID, returnDate, returnCondition, returnNotes) 
                                  VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_history);
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $stmt->bind_param("iisss", $usage_id, $item_id, $return_date, $return_condition, $return_notes);
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
                
                $conn->commit();
                error_log("Return logged successfully: usageID=$usage_id");
                sendResponse('success', 'Item return logged successfully');
            } catch (Exception $e) {
                $conn->rollback();
                error_log("Transaction error: " . $e->getMessage());
                sendResponse('error', 'Database error: ' . $e->getMessage());
            }
        }
        
        sendResponse('error', 'Invalid action');
    } catch (Exception $e) {
        error_log("Server error: " . $e->getMessage());
        sendResponse('error', 'Server error: ' . $e->getMessage());
    }
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['fetch'])) {
    try {
        if ($_GET['fetch'] == 'available_items') {
            $sql = "SELECT itemID, name, category, status, description, notes 
                    FROM items 
                    WHERE status='available' 
                    ORDER BY name";
            $result = $conn->query($sql);
            
            if (!$result) {
                throw new Exception("Query failed: " . $conn->error);
            }
            
            $items = [];
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
            
            sendResponse('success', '', $items);
        }
        
        if ($_GET['fetch'] == 'in_use_items') {
            $sql = "SELECT i.itemID, i.name, i.category, u.id AS usageID, 
                           u.borrowerName, u.borrowDate, u.expectedReturn 
                    FROM items i
                    JOIN usage_log u ON i.itemID = u.itemID
                    WHERE i.status='in-use' AND u.status='borrowed'
                    ORDER BY u.expectedReturn ASC";
            $result = $conn->query($sql);
            
            if (!$result) {
                throw new Exception("Query failed: " . $conn->error);
            }
            
            $items = [];
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
            
            sendResponse('success', '', $items);
        }
        
        if ($_GET['fetch'] == 'return_history') {
            try {
                // Debug log the start of the fetch
                error_log("Starting return history fetch");
                
                $sql = "SELECT 
                            rh.returnID,
                            rh.usageID,
                            rh.itemID,
                            i.name,
                            u.borrowerName,
                            u.borrowDate,
                            rh.returnDate,
                            rh.returnCondition,
                            rh.returnNotes,
                            u.purpose,
                            u.initialCondition, 
                            rh.created_at as return_timestamp
                        FROM return_history rh
                        INNER JOIN items i ON rh.itemID = i.itemID
                        INNER JOIN usage_log u ON rh.usageID = u.id
                        WHERE u.status = 'returned'
                        ORDER BY rh.returnDate DESC, rh.created_at DESC";
                
                // Debug log the SQL query
                error_log("Return history SQL query: " . $sql);
                
                $result = $conn->query($sql);
                
                if (!$result) {
                    error_log("Query failed: " . $conn->error);
                    throw new Exception("Query failed: " . $conn->error);
                }
                
                $history = [];
                while ($row = $result->fetch_assoc()) {
                    // Format dates for consistency
                    $row['borrowDate'] = date('Y-m-d', strtotime($row['borrowDate']));
                    $row['returnDate'] = date('Y-m-d', strtotime($row['returnDate']));
                    $row['return_timestamp'] = date('Y-m-d H:i:s', strtotime($row['return_timestamp']));
                    $history[] = $row;
                }
                
                // Debug log the number of records found
                error_log("Found " . count($history) . " return history records");
                
                sendResponse('success', '', $history);
            } catch (Exception $e) {
                error_log("Server error in return history: " . $e->getMessage());
                sendResponse('error', 'Server error: ' . $e->getMessage());
            }
        }
        
        if ($_GET['fetch'] == 'usage_details' && isset($_GET['id'])) {
            $usage_id = $_GET['id'];
            $sql = "SELECT u.*, i.name, i.category 
                    FROM usage_log u
                    JOIN items i ON u.itemID = i.itemID
                    WHERE u.id=?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("i", $usage_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                sendResponse('success', '', $row);
            } else {
                sendResponse('error', 'Usage record not found');
            }
        }
        
        sendResponse('error', 'Invalid fetch request');
    } catch (Exception $e) {
        error_log("Server error: " . $e->getMessage());
        sendResponse('error', 'Server error: ' . $e->getMessage());
    }
}

// If we get here and it's an API request, return error
if (isset($_GET['fetch']) || isset($_POST['action'])) {
    sendResponse('error', 'Invalid request method');
}

// If we get here, it's a regular page load - continue with HTML
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usage Management - SK Barangay San Isidro</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="usage.css">
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
                <div class="dashboard-title">Item Usage Management</div>
                
                <!-- Tabs -->
                <div class="tab-navigation">
                    <button class="tab-button active" data-tab="items-available">Available Items</button>
                    <button class="tab-button" data-tab="items-in-use">Items In Use</button>
                    <button class="tab-button" data-tab="return-history">Return History</button>
                </div>
                
                <!-- Available Items Tab -->
                <div id="items-available" class="tab-content active">
                    <div class="search-container">
                        <input type="text" class="search-input" id="available-search" placeholder="Search available items...">
                        <select class="form-control" id="category-filter">
                            <option value="">All Categories</option>
                        </select>
                        <button class="btn btn-primary" id="refresh-available-btn">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                    
                    <div class="table-container">
                        <table id="available-items-table">
                            <thead>
                                <tr>
                                    <th>Item ID</th>
                                    <th>Item Name</th>
                                    <th>Category</th>
                                    <th>Condition</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['itemID']) ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['category']) ?></td>
                                    <td><span class="badge badge-success">Available</span></td>
                                    <td>
                                        <button class="btn btn-primary btn-action log-use-btn" data-id="<?= $row['itemID'] ?>" data-name="<?= htmlspecialchars($row['name']) ?>">Log Use</button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Items In Use Tab -->
                <div id="items-in-use" class="tab-content">
                    <div class="search-container">
                        <input type="text" class="search-input" id="in-use-search" placeholder="Search items in use...">
                        <button class="btn btn-primary" id="refresh-in-use-btn">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                    
                    <div class="table-container">
                        <table id="in-use-items-table">
                            <thead>
                                <tr>
                                    <th>Item ID</th>
                                    <th>Item Name</th>
                                    <th>Borrowed By</th>
                                    <th>Date Borrowed</th>
                                    <th>Expected Return</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>002</td>
                                    <td>Basketball</td>
                                    <td>Juan Santos</td>
                                    <td>May 3, 2025</td>
                                    <td>May 10, 2025</td>
                                    <td>
                                        <button class="btn btn-success btn-action log-return-btn" data-id="002" data-name="Basketball">Log Return</button>
                                        <button class="btn btn-secondary btn-action" onclick="viewUsageDetails('002')">Details</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>004</td>
                                    <td>Digital Camera</td>
                                    <td>Ana Reyes</td>
                                    <td>May 2, 2025</td>
                                    <td>May 9, 2025</td>
                                    <td>
                                        <button class="btn btn-success btn-action log-return-btn" data-id="004" data-name="Digital Camera">Log Return</button>
                                        <button class="btn btn-secondary btn-action" onclick="viewUsageDetails('004')">Details</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>006</td>
                                    <td>Extension Cord</td>
                                    <td>Miguel Cruz</td>
                                    <td>May 5, 2025</td>
                                    <td>May 12, 2025</td>
                                    <td>
                                        <button class="btn btn-success btn-action log-return-btn" data-id="006" data-name="Extension Cord">Log Return</button>
                                        <button class="btn btn-secondary btn-action" onclick="viewUsageDetails('006')">Details</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>008</td>
                                    <td>Portable Speaker</td>
                                    <td>Sofia Garcia</td>
                                    <td>May 4, 2025</td>
                                    <td>May 11, 2025</td>
                                    <td>
                                        <button class="btn btn-success btn-action log-return-btn" data-id="008" data-name="Portable Speaker">Log Return</button>
                                        <button class="btn btn-secondary btn-action" onclick="viewUsageDetails('008')">Details</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Return History Tab -->
                <div id="return-history" class="tab-content">
                    <div class="search-container">
                        <input type="text" class="search-input" id="history-search" placeholder="Search return history...">
                        <input type="date" class="form-control" id="date-filter">
                        <button class="btn btn-primary" id="refresh-history-btn">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                    
                    <div class="table-container">
                        <table id="history-table">
                            <thead>
                                <tr>
                                    <th>Item ID</th>
                                    <th>Item Name</th>
                                    <th>Borrowed By</th>
                                    <th>Date Borrowed</th>
                                    <th>Date Returned</th>
                                    <th>Return Condition</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>009</td>
                                    <td>Volleyball Net</td>
                                    <td>Maria Garcia</td>
                                    <td>April 20, 2025</td>
                                    <td>April 27, 2025</td>
                                    <td><span class="badge badge-success">Good</span></td>
                                    <td>
                                        <button class="btn btn-secondary btn-action" onclick="viewReturnDetails('009')">Details</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>010</td>
                                    <td>Wireless Microphone</td>
                                    <td>Pedro Reyes</td>
                                    <td>April 15, 2025</td>
                                    <td>April 16, 2025</td>
                                    <td><span class="badge badge-success">Good</span></td>
                                    <td>
                                        <button class="btn btn-secondary btn-action" onclick="viewReturnDetails('010')">Details</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>011</td>
                                    <td>Badminton Set</td>
                                    <td>Liza Tan</td>
                                    <td>April 10, 2025</td>
                                    <td>April 12, 2025</td>
                                    <td><span class="badge badge-warning">Fair</span></td>
                                    <td>
                                        <button class="btn btn-secondary btn-action" onclick="viewReturnDetails('011')">Details</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>012</td>
                                    <td>Tent</td>
                                    <td>Ramon Diaz</td>
                                    <td>April 5, 2025</td>
                                    <td>April 7, 2025</td>
                                    <td><span class="badge badge-danger">Damaged</span></td>
                                    <td>
                                        <button class="btn btn-secondary btn-action" onclick="viewReturnDetails('012')">Details</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal for Log Use -->
        <div class="modal-overlay" id="log-use-modal">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">Log Item Usage</h3>
                    <button class="modal-close" id="close-log-use-modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="log-use-form">
                        <input type="hidden" id="use-item-id">
                        <div class="form-group">
                            <label for="use-item-name">Item Name</label>
                            <input type="text" class="form-control" id="use-item-name" disabled>
                        </div>
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="borrower-name">Borrower Name</label>
                                    <input type="text" class="form-control" id="borrower-name" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="borrower-contact">Contact Number</label>
                                    <input type="text" class="form-control" id="borrower-contact" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="borrow-date">Date Borrowed</label>
                                    <input type="date" class="form-control" id="borrow-date" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="expected-return-date">Expected Return Date</label>
                                    <input type="date" class="form-control" id="expected-return-date" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="purpose">Purpose</label>
                            <textarea class="form-control" id="purpose" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="initial-condition">Item Condition on Borrow</label>
                            <select class="form-control" id="initial-condition" required>
                                <option value="">Select Condition</option>
                                <option value="Excellent">Excellent</option>
                                <option value="Good">Good</option>
                                <option value="Fair">Fair</option>
                                <option value="Poor">Poor</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="notes">Additional Notes</label>
                            <textarea class="form-control" id="notes" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" id="cancel-log-use">Cancel</button>
                    <button class="btn btn-primary" id="submit-log-use">Submit</button>
                </div>
            </div>
        </div>
        
        <!-- Modal for Log Return -->
        <div class="modal-overlay" id="log-return-modal">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">Log Item Return</h3>
                    <button class="modal-close" id="close-log-return-modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="log-return-form">
                        <input type="hidden" id="return-item-id">
                        <input type="hidden" id="return-usage-id">
                        <div class="form-group">
                            <label for="return-item-name">Item Name</label>
                            <input type="text" class="form-control" id="return-item-name" disabled>
                        </div>
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="return-date">Date Returned</label>
                                    <input type="date" class="form-control" id="return-date" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="return-condition">Return Condition</label>
                                    <select class="form-control" id="return-condition" required>
                                        <option value="">Select Condition</option>
                                        <option value="Excellent">Excellent</option>
                                        <option value="Good">Good</option>
                                        <option value="Fair">Fair</option>
                                        <option value="Poor">Poor</option>
                                        <option value="Damaged">Damaged</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="return-notes">Return Notes</label>
                            <textarea class="form-control" id="return-notes" rows="3" placeholder="Describe the condition of the item and any issues..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" id="cancel-log-return">Cancel</button>
                    <button class="btn btn-success" id="submit-log-return">Log Return</button>
                </div>
            </div>
        </div>
        
        <!-- Modal for View Details -->
        <div class="modal-overlay" id="view-details-modal">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">Item Details</h3>
                    <button class="modal-close" id="close-view-details-modal">&times;</button>
                </div>
                <div class="modal-body" id="details-content">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" id="close-details">Close</button>
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
        // Define validateForm function at the top of the script
        function validateForm(formId) {
            const form = document.getElementById(formId);
            if (!form) {
                console.error('Form not found:', formId);
                return false;
            }
            
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = 'red';
                    isValid = false;
                } else {
                    field.style.borderColor = '';
                }
            });
            
            return isValid;
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Tab Navigation
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const tabId = button.getAttribute('data-tab');
                    
                    // Remove active class from all buttons and contents
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Add active class to current button and content
                    button.classList.add('active');
                    document.getElementById(tabId).classList.add('active');
                });
            });

            // Log Use Button Click Handler
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('log-use-btn')) {
                    const itemId = e.target.getAttribute('data-id');
                    const itemName = e.target.getAttribute('data-name');
                    
                    // Set the item details in the form
                    document.getElementById('use-item-id').value = itemId;
                    document.getElementById('use-item-name').value = itemName;
                    
                    // Set today as default borrow date
                    const today = new Date().toISOString().split('T')[0];
                    document.getElementById('borrow-date').value = today;
                    
                    // Set default expected return date (7 days from now)
                    const nextWeek = new Date();
                    nextWeek.setDate(nextWeek.getDate() + 7);
                    document.getElementById('expected-return-date').value = nextWeek.toISOString().split('T')[0];
                    
                    // Show the modal
                    document.getElementById('log-use-modal').classList.add('active');
                }
            });

            // Close Log Use Modal
            document.getElementById('close-log-use-modal').addEventListener('click', () => {
                document.getElementById('log-use-modal').classList.remove('active');
            });

            document.getElementById('cancel-log-use').addEventListener('click', () => {
                document.getElementById('log-use-modal').classList.remove('active');
            });

            // Submit Log Use Form
            document.getElementById('submit-log-use').addEventListener('click', () => {
                const form = document.getElementById('log-use-form');
                const formData = new FormData();
                
                // Get all form values
                formData.append('action', 'log_use');
                formData.append('itemID', document.getElementById('use-item-id').value);
                formData.append('borrowerName', document.getElementById('borrower-name').value);
                formData.append('borrowerContact', document.getElementById('borrower-contact').value);
                formData.append('borrowDate', document.getElementById('borrow-date').value);
                formData.append('expectedReturn', document.getElementById('expected-return-date').value);
                formData.append('purpose', document.getElementById('purpose').value);
                formData.append('initialCondition', document.getElementById('initial-condition').value);
                formData.append('notes', document.getElementById('notes').value);

                // Validate form
                if (!validateForm('log-use-form')) {
                    alert('Please fill in all required fields');
                    return;
                }

                // Submit the form
                fetch('usage.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Item usage logged successfully!');
                        document.getElementById('log-use-modal').classList.remove('active');
                        // Reset form
                        form.reset();
                        // Refresh the tables
                        loadAvailableItems();
                        loadInUseItems();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while processing your request.');
                });
            });

            // Load initial data
            loadAvailableItems();
            loadInUseItems();
            loadReturnHistory();
            loadCategories();

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

        // View usage details
        function viewUsageDetails(usageId) {
            fetch(`usage.php?fetch=usage_details&id=${usageId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const item = data.data;
                        const detailsContent = document.getElementById('details-content');
                        
                        detailsContent.innerHTML = `
                            <h4>${item.name} - Usage Details</h4>
                            <p><strong>Borrower Name:</strong> ${item.borrowerName}</p>
                            <p><strong>Contact Number:</strong> ${item.borrowerContact}</p>
                            <p><strong>Date Borrowed:</strong> ${item.borrowDate}</p>
                            <p><strong>Expected Return:</strong> ${item.expectedReturn}</p>
                            <p><strong>Purpose:</strong> ${item.purpose}</p>
                            <p><strong>Initial Condition:</strong> ${item.initialCondition}</p>
                            <p><strong>Notes:</strong> ${item.notes || 'No notes provided'}</p>
                        `;
                        
                        document.getElementById('view-details-modal').classList.add('active');
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while fetching usage details.');
                });
        }

        // Add refresh button handlers
        document.getElementById('refresh-available-btn').addEventListener('click', function() {
            console.log('Refreshing available items...');
            loadAvailableItems();
        });

        document.getElementById('refresh-in-use-btn').addEventListener('click', function() {
            console.log('Refreshing in-use items...');
            loadInUseItems();
        });

        // Function to load available items
        function loadAvailableItems() {
            console.log('Loading available items...');
            fetch('usage.php?fetch=available_items')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const tbody = document.querySelector('#available-items-table tbody');
                        tbody.innerHTML = '';
                        
                        if (data.data && data.data.length > 0) {
                            data.data.forEach(item => {
                                tbody.innerHTML += `
                                    <tr>
                                        <td>${item.itemID}</td>
                                        <td>${item.name}</td>
                                        <td>${item.category}</td>
                                        <td><span class="badge badge-success">Available</span></td>
                                        <td>
                                            <button class="btn btn-primary btn-action log-use-btn" 
                                                    data-id="${item.itemID}" 
                                                    data-name="${item.name}">Log Use</button>
                                        </td>
                                    </tr>
                                `;
                            });
                        } else {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="5" class="text-center">No available items found</td>
                                </tr>
                            `;
                        }
                    } else {
                        console.error('Error loading available items:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading available items:', error);
                });
        }

        // View return details
        function viewReturnDetails(usageId) {
            fetch(`usage.php?fetch=usage_details&id=${usageId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const item = data.data;
                        const detailsContent = document.getElementById('details-content');
                        
                        detailsContent.innerHTML = `
                            <h4>${item.name} - Return History</h4>
                            <p><strong>Borrowed By:</strong> ${item.borrowerName}</p>
                            <p><strong>Date Borrowed:</strong> ${item.borrowDate}</p>
                            <p><strong>Date Returned:</strong> ${item.returnDate}</p>
                            <p><strong>Purpose:</strong> ${item.purpose}</p>
                            <p><strong>Initial Condition:</strong> ${item.initialCondition}</p>
                            <p><strong>Return Condition:</strong> ${item.returnCondition}</p>
                            <p><strong>Return Notes:</strong> ${item.returnNotes || 'No notes provided'}</p>
                        `;
                        
                        document.getElementById('view-details-modal').classList.add('active');
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while fetching return details.');
                });
        }

        // Function to load in-use items
        function loadInUseItems() {
            console.log('Loading in-use items...');
            fetch('usage.php?fetch=in_use_items')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const tbody = document.querySelector('#in-use-items-table tbody');
                        tbody.innerHTML = '';
                        
                        if (data.data && data.data.length > 0) {
                            data.data.forEach(item => {
                                tbody.innerHTML += `
                                    <tr>
                                        <td>${item.itemID}</td>
                                        <td>${item.name}</td>
                                        <td>${item.borrowerName}</td>
                                        <td>${formatDate(item.borrowDate)}</td>
                                        <td>${formatDate(item.expectedReturn)}</td>
                                        <td>
                                            <button class="btn btn-success btn-action log-return-btn" 
                                                    data-id="${item.itemID}" 
                                                    data-name="${item.name}"
                                                    data-usage-id="${item.usageID}">Log Return</button>
                                            <button class="btn btn-secondary btn-action" onclick="viewUsageDetails('${item.usageID}')">Details</button>
                                        </td>
                                    </tr>
                                `;
                            });
                        } else {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="6" class="text-center">No items currently in use</td>
                                </tr>
                            `;
                        }
                    } else {
                        console.error('Error loading in-use items:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading in-use items:', error);
                });
        }

        // Function to load return history
        function loadReturnHistory() {
            console.log('Loading return history...'); // Debug log
            
            // Show loading state
            const tbody = document.querySelector('#history-table tbody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center">Loading return history...</td>
                </tr>
            `;
            
            fetch('usage.php?fetch=return_history')
                .then(response => {
                    console.log('Raw response:', response); // Debug log
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Return history data:', data); // Debug log
                    
                    if (data.status === 'success') {
                        tbody.innerHTML = '';
                        
                        if (data.data && data.data.length > 0) {
                            data.data.forEach(item => {
                                console.log('Processing item:', item); // Debug log
                                
                                // Format dates
                                const borrowDate = new Date(item.borrowDate).toLocaleDateString();
                                const returnDate = new Date(item.returnDate).toLocaleDateString();
                                
                                // Determine badge class based on return condition
                                let badgeClass = 'success';
                                if (item.returnCondition === 'Damaged') {
                                    badgeClass = 'danger';
                                } else if (item.returnCondition === 'Fair' || item.returnCondition === 'Poor') {
                                    badgeClass = 'warning';
                                }
                                
                                tbody.innerHTML += `
                                    <tr data-date="${item.returnDate}">
                                        <td>${item.itemID}</td>
                                        <td>${item.name}</td>
                                        <td>${item.borrowerName}</td>
                                        <td>${formatDate(item.borrowDate)}</td>
                                        <td>${formatDate(item.returnDate)}</td>
                                        <td><span class="badge badge-${badgeClass}">${item.returnCondition}</span></td>
                                        <td>
                                            <button class="btn btn-secondary btn-action" onclick="viewReturnDetails('${item.usageID}')">Details</button>
                                        </td>
                                    </tr>
                                `;
                            });
                        } else {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="7" class="text-center">No return history found</td>
                                </tr>
                            `;
                        }
                    } else {
                        console.error('Error loading return history:', data.message);
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="7" class="text-center">Error loading history: ${data.message}</td>
                            </tr>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading return history:', error);
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center">Error loading history: ${error.message}</td>
                        </tr>
                    `;
                });
        }

        // Log Return Button Click Handler
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('log-return-btn')) {
                const itemId = e.target.getAttribute('data-id');
                const itemName = e.target.getAttribute('data-name');
                const usageId = e.target.getAttribute('data-usage-id');
                
                console.log('Log Return clicked:', { itemId, itemName, usageId }); // Debug log
                
                // Set the item details in the form
                document.getElementById('return-item-id').value = itemId;
                document.getElementById('return-item-name').value = itemName;
                document.getElementById('return-usage-id').value = usageId;
                
                // Set today as default return date
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('return-date').value = today;
                
                // Show the modal
                document.getElementById('log-return-modal').classList.add('active');
            }
        });

        // Submit Log Return Form
        document.getElementById('submit-log-return').addEventListener('click', () => {
            const form = document.getElementById('log-return-form');
            const formData = new FormData();
            
            // Get all form values
            formData.append('action', 'log_return');
            formData.append('usageID', document.getElementById('return-usage-id').value);
            formData.append('itemID', document.getElementById('return-item-id').value);
            formData.append('returnDate', document.getElementById('return-date').value);
            formData.append('returnCondition', document.getElementById('return-condition').value);
            formData.append('returnNotes', document.getElementById('return-notes').value);

            // Debug logging
            console.log('Submitting return form with data:', {
                usageID: formData.get('usageID'),
                itemID: formData.get('itemID'),
                returnDate: formData.get('returnDate'),
                returnCondition: formData.get('returnCondition'),
                returnNotes: formData.get('returnNotes')
            });

            // Validate form
            if (!validateForm('log-return-form')) {
                alert('Please fill in all required fields');
                return;
            }

            // Submit the form
            fetch('usage.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Raw response:', response);
                return response.json();
            })
            .then(data => {
                console.log('Server response:', data);
                if (data.status === 'success') {
                    alert('Item return logged successfully!');
                    document.getElementById('log-return-modal').classList.remove('active');
                    // Reset form
                    form.reset();   
                    // Refresh all tables
                    loadAvailableItems();
                    loadInUseItems();
                    loadReturnHistory();
                    // Switch to Return History tab
                    document.querySelector('[data-tab="return-history"]').click();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing your request.');
            });
        });

        // Close Log Return Modal
        document.getElementById('close-log-return-modal').addEventListener('click', () => {
            document.getElementById('log-return-modal').classList.remove('active');
        });

        document.getElementById('cancel-log-return').addEventListener('click', () => {
            document.getElementById('log-return-modal').classList.remove('active');
        });

        // Add refresh button handler
        document.getElementById('refresh-history-btn').addEventListener('click', () => {
            loadReturnHistory();
        });

        // Add search and filter functionality
        document.getElementById('available-search').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#available-items-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        document.getElementById('category-filter').addEventListener('change', function(e) {
            const category = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#available-items-table tbody tr');
            
            rows.forEach(row => {
                if (!category) {
                    row.style.display = '';
                    return;
                }
                const rowCategory = row.children[2].textContent.toLowerCase();
                row.style.display = rowCategory === category ? '' : 'none';
            });
        });

        document.getElementById('in-use-search').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#in-use-items-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        document.getElementById('history-search').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#history-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        document.getElementById('date-filter').addEventListener('change', function(e) {
            const selectedDate = e.target.value;
            const rows = document.querySelectorAll('#history-table tbody tr');
            rows.forEach(row => {
                if (!selectedDate) {
                    row.style.display = '';
                    return;
                }
                const rowDate = row.getAttribute('data-date');
                row.style.display = rowDate === selectedDate ? '' : 'none';
            });
        });

        // Fix modal closing functionality
        document.getElementById('close-view-details-modal').addEventListener('click', () => {
            document.getElementById('view-details-modal').classList.remove('active');
        });

        document.getElementById('close-details').addEventListener('click', () => {
            document.getElementById('view-details-modal').classList.remove('active');
        });

        document.querySelector('[data-tab="return-history"]').addEventListener('click', () => {
            const searchValue = document.getElementById('history-search').value.trim();
            const dateValue = document.getElementById('date-filter').value.trim();
            if (!searchValue && !dateValue) {
                loadReturnHistory();
            }
        });

        // Function to load categories
        function loadCategories() {
            fetch('categories.php?fetch=categories')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const categoryFilter = document.getElementById('category-filter');
                        // Keep the "All Categories" option
                        categoryFilter.innerHTML = '<option value="">All Categories</option>';
                        
                        // Add categories from the database
                        data.data.forEach(category => {
                            categoryFilter.innerHTML += `
                                <option value="${category.categoryName}">${category.categoryName}</option>
                            `;
                        });
                    } else {
                        console.error('Error loading categories:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading categories:', error);
                });
        }

        // Add this function at the top of your main script block
        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            if (isNaN(d)) return '-';
            return d.toLocaleString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
        }
    </script>
</body>
</html>