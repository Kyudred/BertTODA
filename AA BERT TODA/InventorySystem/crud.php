<?php
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $redirect_page = isset($_POST['redirect']) ? $_POST['redirect'] : 'itemsThatworks.php';

    if ($action == 'create') {
        $name = $_POST['name'];
        $category = $_POST['category'];
        $quantity = $_POST['quantity'];
        $status = $_POST['status'];
        $description = $_POST['description'];
        $notes = $_POST['notes'];
        $date = date("Y-m-d");

        $sql = "INSERT INTO items (name, category, quantity, status, dateAdded, lastUpdated, description, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisssss", $name, $category, $quantity, $status, $date, $date, $description, $notes);
        
        if ($stmt->execute()) {
            header("Location: $redirect_page?action=create&status=success");
        } else {
            header("Location: $redirect_page?action=create&status=error");
        }
        exit();
    }

    if ($action == 'update') {
        $id = $_POST['itemID'];
        $name = $_POST['name'];
        $category = $_POST['category'];
        $quantity = $_POST['quantity'];
        $status = $_POST['status'];
        $description = $_POST['description'];
        $notes = $_POST['notes'];
        $date = date("Y-m-d");

        $sql = "UPDATE items SET name=?, category=?, quantity=?, status=?, lastUpdated=?, description=?, notes=? WHERE itemID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssissssi", $name, $category, $quantity, $status, $date, $description, $notes, $id);
        
        if ($stmt->execute()) {
            header("Location: $redirect_page?action=update&status=success");
        } else {
            header("Location: $redirect_page?action=update&status=error");
        }
        exit();
    }

    if ($action == 'delete') {
        $id = $_POST['itemID'];
        
        // First check if the item is in use
        $check_usage = "SELECT status FROM items WHERE itemID = ?";
        $stmt = $conn->prepare($check_usage);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        
        if ($item['status'] === 'in-use') {
            header("Location: $redirect_page?action=delete&status=error&message=Item is currently in use and cannot be deleted");
            exit();
        }
        
        // If not in use, proceed with deletion
        $sql = "DELETE FROM items WHERE itemID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        try {
            if ($stmt->execute()) {
                header("Location: $redirect_page?action=delete&status=success");
            } else {
                throw new Exception($stmt->error);
            }
        } catch (Exception $e) {
            header("Location: $redirect_page?action=delete&status=error&message=" . urlencode("Cannot delete item: " . $e->getMessage()));
        }
        exit();
    }
    
    // Handle item usage actions
    if ($action == 'log_use') {
        $item_id = $_POST['itemID'];
        $borrower_name = $_POST['borrowerName'];
        $borrower_contact = $_POST['borrowerContact'];
        $borrow_date = $_POST['borrowDate'];
        $expected_return = $_POST['expectedReturn'];
        $purpose = $_POST['purpose'];
        $initial_condition = $_POST['initialCondition'];
        $notes = $_POST['notes'];
        
        // First update the item status to 'in-use'
        $updateItem = "UPDATE items SET status='in-use', lastUpdated=NOW() WHERE itemID=?";
        $stmt = $conn->prepare($updateItem);
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        
        // Then log the usage in the usage table
        $log = "INSERT INTO usage_log (itemID, borrowerName, borrowerContact, borrowDate, expectedReturn, purpose, initialCondition, notes, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'borrowed')";
        $stmt = $conn->prepare($log);
        $stmt->bind_param("isssssss", $item_id, $borrower_name, $borrower_contact, $borrow_date, $expected_return, $purpose, $initial_condition, $notes);
        
        if ($stmt->execute()) {
            header("Location: $redirect_page?action=log_use&status=success");
        } else {
            header("Location: $redirect_page?action=log_use&status=error");
        }
        exit();
    }
    
    if ($action == 'log_return') {
        $usage_id = $_POST['usageID'];
        $item_id = $_POST['itemID'];
        $return_date = $_POST['returnDate'];
        $return_condition = $_POST['returnCondition'];
        $return_notes = $_POST['returnNotes'];
        
        // First update the item status to 'available' (or 'damaged' if the condition requires)
        $newStatus = ($return_condition == 'Damaged') ? 'damaged' : 'available';
        $updateItem = "UPDATE items SET status=?, lastUpdated=NOW() WHERE itemID=?";
        $stmt = $conn->prepare($updateItem);
        $stmt->bind_param("si", $newStatus, $item_id);
        $stmt->execute();
        
        // Then update the usage_log record
        $updateLog = "UPDATE usage_log SET returnDate=?, returnCondition=?, returnNotes=?, status='returned' WHERE id=?";
        $stmt = $conn->prepare($updateLog);
        $stmt->bind_param("sssi", $return_date, $return_condition, $return_notes, $usage_id);
        
        if ($stmt->execute()) {
            header("Location: $redirect_page?action=log_return&status=success");
        } else {
            header("Location: $redirect_page?action=log_return&status=error");
        }
        exit();
    }
}

// Handle GET requests for fetching data
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['fetch'])) {
    header('Content-Type: application/json');
    
    if ($_GET['fetch'] == 'available_items') {
        $sql = "SELECT * FROM items WHERE status='available' ORDER BY name";
        $result = $conn->query($sql);
        
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $items]);
        exit();
    }
    
    if ($_GET['fetch'] == 'in_use_items') {
        $sql = "SELECT i.*, u.id AS usageID, u.borrowerName, u.borrowDate, u.expectedReturn 
                FROM items i
                JOIN usage_log u ON i.itemID = u.itemID
                WHERE i.status='in-use' AND u.status='borrowed'
                ORDER BY u.expectedReturn ASC";
        $result = $conn->query($sql);
        
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $items]);
        exit();
    }
    
    if ($_GET['fetch'] == 'return_history') {
        $sql = "SELECT i.name, u.* 
                FROM usage_log u
                JOIN items i ON u.itemID = i.itemID
                WHERE u.status='returned'
                ORDER BY u.returnDate DESC";
        $result = $conn->query($sql);
        
        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $history]);
        exit();
    }
    
    if ($_GET['fetch'] == 'item_details' && isset($_GET['id'])) {
        $item_id = $_GET['id'];
        $sql = "SELECT * FROM items WHERE itemID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo json_encode(['status' => 'success', 'data' => $row]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Item not found']);
        }
        exit();
    }
    
    if ($_GET['fetch'] == 'usage_details' && isset($_GET['id'])) {
        $usage_id = $_GET['id'];
        $sql = "SELECT u.*, i.name 
                FROM usage_log u
                JOIN items i ON u.itemID = i.itemID
                WHERE u.id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usage_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo json_encode(['status' => 'success', 'data' => $row]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Usage record not found']);
        }
        exit();
    }
}
?>