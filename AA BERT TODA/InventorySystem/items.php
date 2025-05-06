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

    /* Item Management Styles */
    .item-management {
      background-color: #ffeaf2;
      padding: 15px;
      border-radius: 5px;
      margin-bottom: 20px;
    }

    .filter-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .category-filter {
      display: flex;
      align-items: center;
    }

    .category-filter label {
      margin-right: 10px;
      font-weight: bold;
    }

    .category-filter select {
      padding: 8px;
      border-radius: 4px;
      border: 1px solid #ccc;
      background-color: white;
    }

    .search-row {
      display: flex;
      gap: 10px;
      margin-bottom: 15px;
    }

    .search-box {
      flex: 1;
      display: flex;
    }

    .search-box input {
      width: 100%;
      padding: 8px;
      border-radius: 4px;
      border: 1px solid #ccc;
    }

    .search-btn {
      padding: 8px 15px;
      border: none;
      border-radius: 4px;
      background-color: #ff70a6;
      color: white;
      font-weight: bold;
      cursor: pointer;
    }

    .entries-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .entries-selector {
      display: flex;
      align-items: center;
    }

    .entries-selector label {
      margin-right: 10px;
    }

    .entries-selector select {
      padding: 5px;
      border-radius: 4px;
      border: 1px solid #ccc;
    }

    .add-btn {
      padding: 8px 15px;
      border: none;
      border-radius: 20px;
      background-color: #4cd964;
      color: white;
      font-weight: bold;
      cursor: pointer;
    }

    .items-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      background-color: white;
    }

    .items-table th, .items-table td {
      padding: 10px;
      border-bottom: 1px solid #ccc;
      text-align: left;
    }

    .items-table th {
      background-color: #f2f2f2;
      font-weight: bold;
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

    .use-btn {
      background-color: #2ed573;
      color: white;
    }

    .modal-overlay {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      display: none;
      justify-content: center;
      align-items: center;
      background: rgba(0, 0, 0, 0.3);
      z-index: 1000;
    }

    .modal {
      background: white;
      padding: 20px;
      border-radius: 10px;
      width: 400px;
      max-width: 90%;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
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
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }

    .form-group input, .form-group select {
      width: 100%;
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    .submit-btn {
      padding: 10px 15px;
      background-color: #4cd964;
      color: white;
      border: none;
      border-radius: 20px;
      cursor: pointer;
      width: 100%;
      font-weight: bold;
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

    .facebook-icon {
      color: #1877f2;
      font-size: 24px;
    }
  </style>
</head>
<body>

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
        <li><a href="#"><i>📋</i><span class="label-text">Categories</span></a></li>
        <li><a href="#" class="active"><i>📝</i><span class="label-text">Items</span></a></li>
        <li><a href="#"><i>🔄</i><span class="label-text">Use/Return</span></a></li>
        <li><a href="#"><i>📅</i><span class="label-text">Events Calendar</span></a></li>
        <li><a href="#"><i>🏠</i><span class="label-text">Back to Website</span></a></li>
      </ul>
    </div>

    <!-- Content -->
    <div class="content">
      <div class="content-header">
        <h1>Items</h1>
      </div>

      <div class="item-management">
        <h2>Item Management</h2>

        <div class="filter-row">
          <div class="category-filter">
            <label for="categoryFilter">Select Category:</label>
            <select id="categoryFilter">
              <option value="all">All Categories</option>
              <option value="equipment">Equipment</option>
              <option value="supplies">Supplies</option>
              <option value="furniture">Furniture</option>
              <option value="electronics">Electronics</option>
            </select>
          </div>
        </div>

        <div class="search-row">
          <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search items..."/>
          </div>
          <button class="search-btn">Search</button>
        </div>

        <div class="entries-row">
          <div class="entries-selector">
            <label for="entriesDropdown">Show entries:</label>
            <select id="entriesDropdown">
              <option value="10">10</option>
              <option value="20">20</option>
              <option value="30">30</option>
              <option value="40">40</option>
              <option value="50">50</option>
            </select>
          </div>
          <button class="add-btn" onclick="openAddModal()">Add New Item</button>
        </div>
      </div>

      <table class="items-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>ITEM</th>
            <th>CATEGORY</th>
            <th>AVAILABLE</th>
            <th>TOTAL</th>
            <th>ACTIONS</th>
          </tr>
        </thead>
        <tbody id="itemsTableBody">
          <!-- Item rows will be added here dynamically -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- Footer -->
  <div class="footer">
    <a href="#"><span class="facebook-icon">f</span></a>
  </div>

  <!-- Add Item Modal -->
  <div class="modal-overlay" id="addItemModal">
    <div class="modal">
      <div class="modal-header">
        <h2>Add New Item</h2>
        <button class="close-btn" onclick="closeModal('addItemModal')">&times;</button>
      </div>
      <form id="addItemForm">
        <div class="form-group">
          <label for="itemName">Item Name:</label>
          <input type="text" id="itemName" placeholder="Enter item name" required/>
        </div>
        <div class="form-group">
          <label for="itemCategory">Category:</label>
          <select id="itemCategory" required>
            <option value="">Select Category</option>
            <option value="equipment">Equipment</option>
            <option value="supplies">Supplies</option>
            <option value="furniture">Furniture</option>
            <option value="electronics">Electronics</option>
          </select>
        </div>
        <div class="form-group">
          <label for="availableQuantity">Available Quantity:</label>
          <input type="number" id="availableQuantity" placeholder="Enter available quantity" required min="0"/>
        </div>
        <div class="form-group">
          <label for="totalQuantity">Total Quantity:</label>
          <input type="number" id="totalQuantity" placeholder="Enter total quantity" required min="0"/>
        </div>
        <button type="submit" class="submit-btn">Add Item</button>
      </form>
    </div>
  </div>

  <script>
    // Empty array to store items
    const sampleItems = [];

    // Initialize the table with sample data
    document.addEventListener('DOMContentLoaded', function() {
      loadItems(sampleItems);
      
      // Add event listener for form submission
      document.getElementById('addItemForm').addEventListener('submit', function(e) {
        e.preventDefault();
        addItem();
      });
      
      // Add event listener for category filter
      document.getElementById('categoryFilter').addEventListener('change', function() {
        filterItems();
      });
      
      // Add event listener for search input
      document.getElementById('searchInput').addEventListener('input', function() {
        filterItems();
      });
      
      // Add event listener for entries dropdown
      document.getElementById('entriesDropdown').addEventListener('change', function() {
        filterItems();
      });
    });

    function loadItems(items) {
      const tbody = document.getElementById('itemsTableBody');
      tbody.innerHTML = '';
      
      items.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${item.id}</td>
          <td>${item.name}</td>
          <td>${item.category}</td>
          <td>${item.available}</td>
          <td>${item.total}</td>
          <td>
            <button class="action-btn edit-btn" onclick="editItem('${item.id}')">Edit</button>
            <button class="action-btn delete-btn" onclick="deleteItem('${item.id}')">Delete</button>
            <button class="action-btn use-btn" onclick="useItem('${item.id}')">Use</button>
          </td>
        `;
        tbody.appendChild(row);
      });
    }

    function filterItems() {
      const category = document.getElementById('categoryFilter').value;
      const searchText = document.getElementById('searchInput').value.toLowerCase();
      const entriesLimit = parseInt(document.getElementById('entriesDropdown').value);
      
      let filteredItems = sampleItems;
      
      // Filter by category
      if (category !== 'all') {
        filteredItems = filteredItems.filter(item => 
          item.category.toLowerCase() === category.toLowerCase()
        );
      }
      
      // Filter by search text
      if (searchText) {
        filteredItems = filteredItems.filter(item => 
          item.name.toLowerCase().includes(searchText) || 
          item.id.toLowerCase().includes(searchText) ||
          item.category.toLowerCase().includes(searchText)
        );
      }
      
      // Limit by entries
      filteredItems = filteredItems.slice(0, entriesLimit);
      
      loadItems(filteredItems);
    }

    function openAddModal() {
      document.getElementById('addItemModal').style.display = 'flex';
    }

    function closeModal(id) {
      document.getElementById(id).style.display = 'none';
    }

    function addItem() {
      const name = document.getElementById('itemName').value.trim();
      const category = document.getElementById('itemCategory').value;
      const available = parseInt(document.getElementById('availableQuantity').value);
      const total = parseInt(document.getElementById('totalQuantity').value);
      
      if (!name || !category || isNaN(available) || isNaN(total)) {
        alert("Please fill all fields with valid values.");
        return;
      }
      
      if (available > total) {
        alert("Available quantity cannot be greater than total quantity.");
        return;
      }
      
      // Generate a new ID
      const newId = 'ITM' + String(sampleItems.length + 1).padStart(3, '0');
      
      // Add new item to sample data
      const newItem = {
        id: newId,
        name: name,
        category: category,
        available: available,
        total: total
      };
      
      sampleItems.push(newItem);
      
      // Refresh the table
      filterItems();
      
      // Reset form and close modal
      document.getElementById('addItemForm').reset();
      closeModal('addItemModal');
    }

    function editItem(id) {
      alert(Edit functionality for item ${id} not implemented yet.);
    }

    function deleteItem(id) {
      if (confirm(Are you sure you want to delete item ${id}?)) {
        const index = sampleItems.findIndex(item => item.id === id);
        if (index !== -1) {
          sampleItems.splice(index, 1);
          filterItems();
        }
      }
    }

    function useItem(id) {
      alert(Use/Return functionality for item ${id} not implemented yet.);
    }
  </script>

</body>
</html>