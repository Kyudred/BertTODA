-- Create the inquiry table if it doesn't exist
CREATE TABLE IF NOT EXISTS inquiry (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    gender VARCHAR(10) NOT NULL,
    address TEXT NOT NULL,
    inquiry_type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'processing', 'resolved', 'closed') DEFAULT 'pending',
    response TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
); 