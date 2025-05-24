<?php
header('Content-Type: application/json');
require 'db_connection.php';

$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? 'all';
$dateFilter = $_GET['date'] ?? 'all';

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
    // no need to handle 'all'
}

$query .= " ORDER BY submitted_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$inquiry = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Normalize data
foreach ($inquiry as &$row) {
    $row['inquiry_id'] = $row['id'] ?? null;
    $row['full_name'] = $row['full_name'] ?? '';
    $row['email'] = $row['email'] ?? '';
    $row['age'] = $row['age'] ?? '';
    $row['contact_number'] = $row['contact_number'] ?? '';
    $row['gender'] = $row['gender'] ?? '';
    $row['address'] = $row['address'] ?? '';
    $row['inquiry_type'] = $row['inquiry_type'] ?? '';
    $row['message'] = $row['message'] ?? '';
    $row['status'] = $row['status'] ?? '';
    $row['submitted_at'] = $row['submitted_at'] ?? '';
}

// Calculate stats
$stats = ['total' => 0, 'pending' => 0, 'processing' => 0, 'resolved' => 0];
foreach ($inquiry as $inq) {
    $stats['total']++;
    $s = strtolower($inq['status']);
    if (isset($stats[$s])) $stats[$s]++;
}

// Output JSON
echo json_encode([
    'status' => 'success',
    'statistics' => $stats,
    'data' => $inquiry,
]);
