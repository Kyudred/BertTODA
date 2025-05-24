-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS InventorySystemdb;

-- Use the database
USE InventorySystemdb;

-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    categoryID INT AUTO_INCREMENT PRIMARY KEY,
    categoryName VARCHAR(100) NOT NULL,
    description TEXT,
    dateCreated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create items table
CREATE TABLE IF NOT EXISTS items (
    itemID INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(100) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    status ENUM('available', 'in-use', 'maintenance', 'damaged') DEFAULT 'available',
    description TEXT,
    notes TEXT,
    dateAdded TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lastUpdated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create usage_log table
CREATE TABLE IF NOT EXISTS usage_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    itemID INT NOT NULL,
    borrowerName VARCHAR(100) NOT NULL,
    borrowDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expectedReturn TIMESTAMP,
    returnDate TIMESTAMP NULL,
    status ENUM('borrowed', 'returned') DEFAULT 'borrowed',
    FOREIGN KEY (itemID) REFERENCES items(itemID)
);

-- Create return_history table
CREATE TABLE IF NOT EXISTS return_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    itemID INT NOT NULL,
    usageID INT NOT NULL,
    returnDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    returnCondition ENUM('Good', 'Fair', 'Poor', 'Damaged') DEFAULT 'Good',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (itemID) REFERENCES items(itemID),
    FOREIGN KEY (usageID) REFERENCES usage_log(id)
);

-- Create inquiry table
CREATE TABLE IF NOT EXISTS inquiry (
    inquiryID INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'in-progress', 'resolved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);  