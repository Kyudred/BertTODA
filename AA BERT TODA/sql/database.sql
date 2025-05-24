-- Drop existing foreign key constraints if they exist
ALTER TABLE items DROP FOREIGN KEY IF EXISTS fk_category;
ALTER TABLE usage_log DROP FOREIGN KEY IF EXISTS fk_item;

-- Drop existing tables if they exist
DROP TABLE IF EXISTS usage_log;
DROP TABLE IF EXISTS items;
DROP TABLE IF EXISTS categories;

-- Create categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoryName VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(50) NOT NULL,
    dateCreated DATE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create items table
CREATE TABLE items (
    itemID INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    quantity INT NOT NULL DEFAULT 0,
    category VARCHAR(100),
    status ENUM('available', 'in-use', 'damaged', 'lost') NOT NULL DEFAULT 'available',
    dateAdded DATE NOT NULL,
    CONSTRAINT fk_category FOREIGN KEY (category) REFERENCES categories(categoryName)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create usage_log table
CREATE TABLE usage_log (
    logID INT AUTO_INCREMENT PRIMARY KEY,
    itemID INT NOT NULL,
    userID INT NOT NULL,
    action ENUM('use', 'return') NOT NULL,
    condition ENUM('good', 'damaged') NOT NULL,
    date DATETIME NOT NULL,
    notes TEXT,
    CONSTRAINT fk_item FOREIGN KEY (itemID) REFERENCES items(itemID)
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert some default categories
INSERT INTO categories (categoryName, description, icon, dateCreated) VALUES
('Electronics', 'Electronic devices and equipment', 'fa-laptop', CURDATE()),
('Sports Equipment', 'Sports-related equipment and accessories', 'fa-basketball-ball', CURDATE()),
('Audio Equipment', 'Sound systems and audio accessories', 'fa-microphone', CURDATE()),
('Furniture', 'Chairs, tables, and other furniture items', 'fa-chair', CURDATE()),
('Arts & Crafts', 'Art supplies and materials', 'fa-paint-brush', CURDATE()),
('Books & Materials', 'Educational materials and books', 'fa-book', CURDATE()),
('Tools & Equipment', 'Maintenance tools and equipment', 'fa-tools', CURDATE()); 