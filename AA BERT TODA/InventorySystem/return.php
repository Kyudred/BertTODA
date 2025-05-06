<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<style>
    /* Root Variables */
:root {
    --pink-color: #ff1493;
    --light-pink: #fdd8ef;
    --lightest-pink: #ffe6f2;
    --dark-grey: #333;
    --medium-grey: #666;
    --light-grey: #f5f5f5;
    --border-color: #ddd;
    --success-color: #98ffbe;
    --warning-color: #ffc107;
    --danger-color: #ff6b6b;
}

/* General Body Styling */
html, body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: var(--light-grey);
    font-size: 16px;
    min-height: 100vh;
    overflow-x: hidden;
}

/* Dashboard Title */
.dashboard-title {
    font-size: 1.75rem;
    margin-bottom: 1.5rem;
    color: black;
    font-weight: bolder;
}

/* Search Bar */
.search-bar {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}

.search-bar input {
    padding: 0.5rem;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    font-size: 1rem;
    margin-right: 0.5rem;
    flex: 1;
}

.search-bar button {
    border-radius: 4px;
    background-color: rgb(182, 0, 91);
    box-shadow: rgba(182, 0, 91, 0.301) 0 -25px 18px -14px inset, rgba(182, 0, 91, 0.205) 0 1px 2px, rgba(182, 0, 91, 0.24) 0 2px 4px, rgba(182, 0, 91, 0.219) 0 4px 8px, rgba(182, 0, 91, 0.205) 0 8px 16px, rgba(182, 0, 91, 0.205) 0 16px 32px;
    color: white;
    cursor: pointer;
    padding: 0.45rem 0.8rem;
    font-size: 0.8rem;
    border: 0;
    transition: all 250ms;
}

.search-bar button:hover {
    background-color: rgb(133, 2, 67);
}

/* Add Item Button */
#add-item-btn {
    margin-bottom: 1.5rem;
    background-color: var(--success-color);
    border-radius: 100px;
    box-shadow: rgba(44, 187, 99, 0.267) 0 -25px 18px -14px inset, rgba(44, 187, 99, .15) 0 1px 2px, rgba(44, 187, 99, .15) 0 2px 4px, rgba(44, 187, 99, .15) 0 4px 8px, rgba(44, 187, 99, 0.219) 0 8px 16px, rgba(44, 187, 99, .15) 0 16px 32px;
    color: green;
    display: inline-block;
    padding: 5px 20px;
    text-align: center;
    text-decoration: none;
    transition: all 250ms;
    border: 0;
    font-size: 16px;
    font-weight: bolder;
    cursor: pointer;
}

#add-item-btn:hover {
    background-color: #45ff89;
}

/* Table Styles */
#item-table {
    width: 100%;
    border-collapse: collapse;
    background-color: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

#item-table th,
#item-table td {
    padding: 0.75rem 1rem;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

#item-table th {
    background-color: var(--light-pink);
    font-weight: bold;
    color: var(--dark-grey);
    text-transform: uppercase;
}

#item-table tbody tr:hover {
    background-color: var(--lightest-pink);
}

#item-table tbody tr:last-child td {
    border-bottom: none;
}

/* Status Badges */
.status {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: bold;
    text-align: center;
    color: white;
}

.status.available {
    background-color: var(--success-color);
}

.status.in-use {
    background-color: var(--warning-color);
}

.status.returned {
    background-color: var(--danger-color);
}

/* Action Buttons */
.btn-action {
    margin-right: 0.5rem;
    border-radius: 4px;
    padding: 0.45rem 0.8rem;
    font-size: 0.8rem;  
    cursor: pointer;
    transition: all 250ms;
    border: 0;
}

.btn-primary {
    background-color: #ff1493;
    color: white;
}

.btn-primary:hover {
    background-color: #e01384;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
}
</style>
<body>
    <!-- Use/Return Section -->
<div id="usage-section" class="dashboard-section">
    <div class="dashboard-title">Use/Return</div>
    <div class="dashboard-table">
        <h2>Item Usage Management</h2>
        <div class="search-bar">
            <input type="text" id="usage-search" placeholder="Search usage records...">
            <button id="category-search-btn">Search</button>
        </div>
        <button id="add-usage-btn" class="Catbtn">Add New Usage</button>
        <table id="usage-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>NAME</th>
                    <th>CATEGORY</th>
                    <th>ITEM NO.</th>
                    <th>ITEM USED</th>
                    <th>TIME RETURN</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody id="usage-table-body">
                <!-- Table data will be populated here -->
            </tbody>
        </table>
    </div>
</div>
</body>
</html>