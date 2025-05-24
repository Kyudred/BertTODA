-- Create Admin_User table
CREATE TABLE IF NOT EXISTS admin_users (
    adminID INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    fullName VARCHAR(100) NOT NULL,
    role ENUM('admin', 'editor', 'viewer') DEFAULT 'editor',
    lastLogin TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Posts table
CREATE TABLE IF NOT EXISTS posts (
    postID INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    postType ENUM('news', 'project') NOT NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    authorID INT NOT NULL,
    imageURL VARCHAR(255),
    slug VARCHAR(200) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (authorID) REFERENCES admin_users(adminID)
);

-- Create Post_Categories table
CREATE TABLE IF NOT EXISTS post_categories (
    categoryID INT AUTO_INCREMENT PRIMARY KEY,
    categoryName VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Post_Tags table
CREATE TABLE IF NOT EXISTS post_tags (
    tagID INT AUTO_INCREMENT PRIMARY KEY,
    tagName VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Post_Category_Relations table
CREATE TABLE IF NOT EXISTS post_category_relations (
    postID INT NOT NULL,
    categoryID INT NOT NULL,
    PRIMARY KEY (postID, categoryID),
    FOREIGN KEY (postID) REFERENCES posts(postID) ON DELETE CASCADE,
    FOREIGN KEY (categoryID) REFERENCES post_categories(categoryID) ON DELETE CASCADE
);

-- Create Post_Tag_Relations table
CREATE TABLE IF NOT EXISTS post_tag_relations (
    postID INT NOT NULL,
    tagID INT NOT NULL,
    PRIMARY KEY (postID, tagID),
    FOREIGN KEY (postID) REFERENCES posts(postID) ON DELETE CASCADE,
    FOREIGN KEY (tagID) REFERENCES post_tags(tagID) ON DELETE CASCADE
);

-- Create Inquiry table
CREATE TABLE IF NOT EXISTS inquiry (
    inquiryID INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'in-progress', 'resolved') DEFAULT 'pending',
    adminResponse TEXT,
    responseDate TIMESTAMP NULL,
    assignedTo INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assignedTo) REFERENCES admin_users(adminID)
);

-- Insert default admin user
INSERT INTO admin_users (username, password, email, fullName, role)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 'System Administrator', 'admin')
ON DUPLICATE KEY UPDATE username = username;

-- Insert default categories
INSERT INTO post_categories (categoryName, description) VALUES
('Announcements', 'Important announcements and updates'),
('Events', 'Upcoming events and activities'),
('Projects', 'Ongoing and completed projects'),
('News', 'General news and updates')
ON DUPLICATE KEY UPDATE categoryName = categoryName; 