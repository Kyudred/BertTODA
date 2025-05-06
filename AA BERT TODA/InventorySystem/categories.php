<?php
include_once 'crud.php'; // Include the Crud class

$crud = new Crud(); // Instantiate the Crud class

// Handle form submissions or AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add') {
            // Add a new category
            $data = [
                'categoryID' => $_POST['categoryID'],
                'categoryName' => $_POST['categoryName']
            ];
            $crud->create('categories', $data);
        } elseif ($action === 'edit') {
            // Edit an existing category
            $data = [
                'categoryName' => $_POST['categoryName']
            ];
            $conditions = "categoryID = " . $_POST['categoryID'];
            $crud->update('categories', $data, $conditions);
        } elseif ($action === 'delete') {
            // Delete a category
            $conditions = "categoryID = " . $_POST['categoryID'];
            $crud->delete('categories', $conditions);
        }
    }
}

// Fetch all categories to display in the table
$categories = $crud->read('categories');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Barangay San Isidro Inventory System</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    /* Header */
    .header {
      background-color: #ffd6e7;
      padding: 8px 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.9em;
    }

    .header h2 {
      font-weight: normal;
      font-size: 1em;
    }

    .logout-btn {
      text-decoration: none;
      color: #333;
      font-weight: bold;
      font-size: 0.9em;
    }

    /* Barangay Header */
    .barangay-header {
      background-color: white;
      padding: 5px 15px;
      display: flex;
      align-items: center;
      border-bottom: 1px solid #eee;
    }

    .barangay-header img {
      height: 30px;
      margin-right: 10px;
    }

    .barangay-title h1 {
      color: #ff4081;
      margin-bottom: 2px;
      border-bottom: 1px solid #333;
      font-size: 1.2em;
    }

    .barangay-title h2 {
      font-size: 1.1em;
    }

    .container {
      display: flex;
      flex: 1;
    }

    /* Sidebar */
    .sidebar {
      background-color: white;
      width: 60px;
      transition: width 0.3s ease;
      overflow: hidden;
      border-right: 1px solid #eee;
    }

    .sidebar:hover {
      width: 200px;
    }

    .sidebar-menu {
      list-style: none;
      padding: 0;
    }

    .sidebar-menu li {
      border-bottom: 1px solid #f5f5f5;
    }

    .sidebar-menu li a {
      display: flex;
      align-items: center;
      padding: 15px 10px;
      color: #555;
      text-decoration: none;
      white-space: nowrap;
      transition: background-color 0.3s;
    }

    .sidebar-menu li a:hover {
      background-color: #f8f8f8;
    }

    .sidebar-menu li a.active {
      background-color: #f0f0f0;
      color: #000;
      border-left: 3px solid #ff4081;
    }

    .sidebar-menu li a i {
      width: 40px;
      text-align: center;
      font-size: 20px;
    }

    .label-text {
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .sidebar:hover .label-text {
      opacity: 1;
      margin-left: 10px;
    }

    /* Main Content */
    .content {
      flex: 1;
      padding: 20px;
    }

    .content-header h1 {
      font-size: 1.5em;
      color: #333;
      margin-bottom: 15px;
    }

    .card {
      background-color: #ffe6f0;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    .card-header h2 {
      font-size: 20px;
      font-weight: bold;
      margin-bottom: 10px;
    }

    .search-add {
      display: flex;
      gap: 10px;
      margin-bottom: 10px;
    }

    .search-box input {
      padding: 8px;
      border-radius: 4px;
      border: 1px solid #ccc;
      flex: 1;
    }

    .search-btn, .add-btn {
      padding: 8px 15px;
      border: none;
      border-radius: 20px;
      font-weight: bold;
      cursor: pointer;
    }

    .search-btn {
      background-color: #ff70a6;
      color: white;
    }

    .add-btn {
      background-color: #4cd964;
      color: white;
      margin-top: 10px;
    }

    .categories-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    .categories-table th, .categories-table td {
      padding: 10px;
      border-bottom: 1px solid #ccc;
      text-align: left;
    }

    .action-btn {
      padding: 4px 10px;
      margin-right: 5px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 0.8em;
    }

    .edit-btn {
      background-color: #ffa502;
      color: white;
    }

    .delete-btn {
      background-color: #ff4757;
      color: white;
    }

    .modal-overlay {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      display: none;
      justify-content: center;
      align-items: center;
      background: rgba(0, 0, 0, 0.3);
    }

    .modal {
      background: white;
      padding: 20px;
      border-radius: 10px;
      width: 300px;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .modal-header h2 {
      font-size: 1.2em;
    }

    .close-btn {
      font-size: 1.5em;
      background: none;
      border: none;
      cursor: pointer;
    }

    .form-group {
      margin-top: 10px;
    }

    .form-group label {
      display: block;
      margin-bottom: 5px;
    }

    .form-group input {
      width: 100%;
      padding: 6px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    .submit-btn {
      margin-top: 15px;
      padding: 8px 15px;
      background-color: #4cd964;
      color: white;
      border: none;
      border-radius: 20px;
      cursor: pointer;
      width: 100%;
    }

    /* Footer */
    .footer {
      background-color: #ffd6e7;
      padding: 15px;
      text-align: right;
    }

    .footer img {
      height: 30px;
    }

  </style>
</head>
<body onload = "table();">

  <!-- Header -->
  <div class="header">
    <h2>Welcome, Administrator</h2>
    <a href="#" class="logout-btn">LOGOUT</a>
  </div>

  <!-- Barangay Header -->
  <div class="barangay-header">
    <img src="https://via.placeholder.com/50" alt="SK Logo" />
    <div class="barangay-title">
      <h1>SANGGUNIANG KABATAAN</h1>
      <h2>BARANGAY SAN ISIDRO</h2>
    </div>
  </div>

  <!-- Main Layout -->
  <div class="container">

    <!-- Sidebar -->
    <div class="sidebar">
      <ul class="sidebar-menu">
        <li><a href="#"><i>📦</i><span class="label-text">Inventory System</span></a></li>
        <li><a href="#"><i>📊</i><span class="label-text">Dashboard</span></a></li>
        <li><a href="#" class="active"><i>📋</i><span class="label-text">Categories</span></a></li>
        <li><a href="#"><i>📝</i><span class="label-text">Items</span></a></li>
        <li><a href="#"><i>🔄</i><span class="label-text">Use/Return</span></a></li>
        <li><a href="#"><i>📅</i><span class="label-text">Events Calendar</span></a></li>
        <li><a href="#"><i>🏠</i><span class="label-text">Back to Website</span></a></li>
      </ul>
    </div>

    <!-- Content -->
    <div class="content">
      <div class="content-header">
        <h1>Categories</h1>
      </div>

      <div class="card">
        <div class="card-header">
          <h2>Item Categories</h2>
        </div>

        <div class="search-add">
          <div class="search-box">
            <input type="text" placeholder="Search categories..."/>
          </div>
          <button class="search-btn">Search</button>
        </div>

        <form id="addCategoryForm" method="POST">
          <input type="hidden" name="action" value="add">
          <div class="form-group">
            <label for="categoryID">Category ID:</label>
            <input type="text" name="categoryID" id="categoryID" required>
          </div>
          <div class="form-group">
            <label for="categoryName">Category Name:</label>
            <input type="text" name="categoryName" id="categoryName" required>
          </div>
          <button class="add-btn" type="submit">Add New Category</button>
        </form>

        <table class="categories-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $categories->fetch_assoc()): ?>
              <tr>
                <td><?php echo $row['categoryID']; ?></td>
                <td><?php echo $row['categoryName']; ?></td>
                <td>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="categoryID" value="<?php echo $row['categoryID']; ?>">
                    <input type="text" name="categoryName" value="<?php echo $row['categoryName']; ?>" required>
                    <button class="action-btn edit-btn" type="submit">Edit</button>
                  </form>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="categoryID" value="<?php echo $row['categoryID']; ?>">
                    <button class="action-btn delete-btn" type="submit">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <div class="footer">
    <a href="#"><img src="https://via.placeholder.com/30" alt="Facebook Icon" /></a>
  </div>

</body>
</html>