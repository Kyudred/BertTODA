<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Database connection
    $pdo = new PDO("mysql:host=localhost;dbname=brgy_sanisidro", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Get filter parameters
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : 'all';
    $dateFilter = isset($_GET['date']) ? trim($_GET['date']) : 'all';

    // Base query
    $query = "SELECT * FROM inquiry WHERE 1=1";
    $params = [];

    // Search filter
    if (!empty($search)) {
        $query .= " AND (full_name LIKE ? OR email LIKE ? OR inquiry_type LIKE ?)";
        $searchTerm = '%' . $search . '%';
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
    }

    // Status filter
    if ($status !== 'all') {
        $query .= " AND status = ?";
        $params[] = $status;
    }

    // Date filter
    switch ($dateFilter) {
        case 'today':
            $query .= " AND DATE(submitted_at) = CURDATE()";
            break;
        case 'week':
            $query .= " AND YEARWEEK(submitted_at, 1) = YEARWEEK(CURDATE(), 1)";
            break;
        case 'month':
            $query .= " AND MONTH(submitted_at) = MONTH(CURDATE()) AND YEAR(submitted_at) = YEAR(CURDATE())";
            break;
    }

    $query .= " ORDER BY submitted_at DESC";

    // Debug information
    error_log("Query: " . $query);
    error_log("Parameters: " . print_r($params, true));

    // Prepare and execute query
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $inquiries = $stmt->fetchAll();

    // Debug information
    error_log("Number of results: " . count($inquiries));

    // Normalize and sanitize data
    $normalizedInquiries = [];
    foreach ($inquiries as $inquiry) {
        $normalizedInquiry = [
            'id' => intval($inquiry['id']),
            'full_name' => htmlspecialchars($inquiry['full_name'] ?? ''),
            'email' => htmlspecialchars($inquiry['email'] ?? ''),
            'age' => intval($inquiry['age'] ?? 0),
            'contact_number' => htmlspecialchars($inquiry['contact_number'] ?? ''),
            'gender' => htmlspecialchars($inquiry['gender'] ?? ''),
            'address' => htmlspecialchars($inquiry['address'] ?? ''),
            'inquiry_type' => htmlspecialchars($inquiry['inquiry_type'] ?? ''),
            'message' => htmlspecialchars($inquiry['message'] ?? ''),
            'status' => htmlspecialchars($inquiry['status'] ?? 'pending'),
            'response' => htmlspecialchars($inquiry['response'] ?? ''),
            'submitted_at' => $inquiry['submitted_at'] ?? date('Y-m-d H:i:s'),
            'updated_at' => $inquiry['updated_at'] ?? date('Y-m-d H:i:s')
        ];
        $normalizedInquiries[] = $normalizedInquiry;
    }

    // Calculate statistics
    $stats = [
        'total' => count($normalizedInquiries),
        'pending' => 0,
        'processing' => 0,
        'resolved' => 0,
        'closed' => 0
    ];

    foreach ($normalizedInquiries as $inquiry) {
        $status = strtolower($inquiry['status']);
        if (isset($stats[$status])) {
            $stats[$status]++;
        }
    }

    // Return JSON response
    echo json_encode([
        'status' => 'success',
        'statistics' => $stats,
        'data' => $normalizedInquiries
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    // Log the error for debugging
    error_log("Database Error: " . $e->getMessage());
    error_log("SQL State: " . $e->getCode());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred',
        'details' => $e->getMessage()
    ]);
} catch (Exception $e) {
    // Log the error for debugging
    error_log("General Error: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An unexpected error occurred',
        'details' => $e->getMessage()
    ]);
}
?>
