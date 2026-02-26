-- ==========================================
-- EasyBank Database Schema
-- ==========================================

-- customers table
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active TINYINT(4) DEFAULT 0
);

-- accounts table
CREATE TABLE IF NOT EXISTS accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    firstname VARCHAR(100),
    lastname VARCHAR(100),
    account_no BIGINT UNIQUE,
    IBAN VARCHAR(50) UNIQUE,
    total_balance DECIMAL(15,2) DEFAULT 0.00,
    account_statement TEXT,
    i_code VARCHAR(20),
    i_code_time TIMESTAMP NULL,
    limit_per_day_transfer DECIMAL(15,2) DEFAULT 1000.00,
    over_transfer DECIMAL(15,2) DEFAULT 0.00,
    publishable_key_stripe VARCHAR(255),
    secret_key_stripe VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (email) REFERENCES customers(email)
);

-- notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100),
    lastname VARCHAR(100),
    firstname VARCHAR(100),
    title VARCHAR(255),
    message TEXT,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- transactions_easy_bank table (internal transfers between EasyBank accounts)
CREATE TABLE IF NOT EXISTS transactions_easy_bank (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_number VARCHAR(100),
    _from_customer_account_no BIGINT,
    _to_customer_account_no BIGINT,
    amount DECIMAL(15,2),
    date_transfer TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT
);

-- transactions_anyone_bank table (external transfers via IBAN)
CREATE TABLE IF NOT EXISTS transactions_anyone_bank (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_number VARCHAR(100),
    _from_customer_IBAN VARCHAR(50),
    _to_customer_IBAN VARCHAR(50),
    amount DECIMAL(15,2),
    date_transfer TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT
);

-- transactions_all table (combined record of all transactions)
CREATE TABLE IF NOT EXISTS transactions_all (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_number VARCHAR(100),
    email VARCHAR(100),
    amount DECIMAL(15,2),
    type VARCHAR(50),
    date_transfer TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT
);

-- easybank_reserve_currency table
CREATE TABLE IF NOT EXISTS easybank_reserve_currency (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100),
    amount DECIMAL(15,2),
    currency VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);