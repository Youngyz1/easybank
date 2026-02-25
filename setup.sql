-- Create customers table if it doesn't exist
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    pin VARCHAR(255) NOT NULL,
    account_number BIGINT NOT NULL UNIQUE,
    IBAN VARCHAR(50) NOT NULL UNIQUE,
    identity_back_name VARCHAR(255),
    identity_back_type VARCHAR(100),
    identity_back_size INT,
    identity_back_data LONGBLOB,
    instant_register TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_instant_register VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add any other tables you might need here