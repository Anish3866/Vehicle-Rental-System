CREATE DATABASE IF NOT EXISTS vehicle_rental
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE vehicle_rental;


CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    status ENUM('active', 'disabled') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_email (email),
    INDEX idx_users_role (role),
    INDEX idx_users_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    type ENUM('two-wheeler', 'four-wheeler') NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_categories_type (type),
    INDEX idx_categories_slug (slug)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    vehicle_number VARCHAR(30) NOT NULL UNIQUE,
    brand VARCHAR(100) NOT NULL,
    model_year INT NOT NULL,
    price_per_day DECIMAL(10, 2) NOT NULL,
    description TEXT,
    image VARCHAR(255) DEFAULT NULL,
    status ENUM('available', 'rented', 'maintenance', 'unavailable') NOT NULL DEFAULT 'available',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_vehicles_category (category_id),
    INDEX idx_vehicles_status (status),
    INDEX idx_vehicles_number (vehicle_number),
    CONSTRAINT fk_vehicles_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    rental_days INT NOT NULL,
    price_per_day DECIMAL(10, 2) NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    booking_status ENUM('pending', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    payment_status ENUM('unpaid', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'unpaid',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_bookings_user (user_id),
    INDEX idx_bookings_vehicle (vehicle_id),
    INDEX idx_bookings_status (booking_status),
    INDEX idx_bookings_payment (payment_status),
    INDEX idx_bookings_dates (start_date, end_date),
    CONSTRAINT fk_bookings_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_bookings_vehicle
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    transaction_id VARCHAR(100) UNIQUE,
    payment_method ENUM('esewa', 'khalti', 'card') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'completed', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    gateway_response TEXT,
    payment_date DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_payments_booking (booking_id),
    INDEX idx_payments_user (user_id),
    INDEX idx_payments_status (status),
    INDEX idx_payments_method (payment_method),
    CONSTRAINT fk_payments_booking
        FOREIGN KEY (booking_id) REFERENCES bookings(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_payments_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    payment_id INT DEFAULT NULL,
    transaction_code VARCHAR(50) NOT NULL UNIQUE,
    type ENUM('payment', 'refund', 'cancellation') NOT NULL DEFAULT 'payment',
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('success', 'failed', 'pending') NOT NULL DEFAULT 'pending',
    remarks TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_transactions_booking (booking_id),
    INDEX idx_transactions_user (user_id),
    INDEX idx_transactions_status (status),
    INDEX idx_transactions_code (transaction_code),
    CONSTRAINT fk_transactions_booking
        FOREIGN KEY (booking_id) REFERENCES bookings(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_transactions_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_transactions_payment
        FOREIGN KEY (payment_id) REFERENCES payments(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;


INSERT INTO users (full_name, email, phone, address, password, role, status) VALUES
('Anish Admin', 'anish206195@gmail.com', '9800000000', 'Kathmandu, Nepal', '$2y$10$zwV/1FwftFRwMWeMBd7/5eZHIjXDwNVBj7hmyW8xDMjhoGCY72ola', 'admin', 'active');

CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_messages_read (is_read),
    INDEX idx_messages_date (created_at)
) ENGINE=InnoDB;

INSERT INTO users (full_name, email, phone, address, password, role, status) VALUES
('Ram Sharma', 'ram@example.com', '9812345678', 'Pokhara, Nepal', '$2y$10$BxA/cTqs1tuZmd0rsNDUfOkCV0TT/m3NwqBaltBNUis.QQt4FZYBO', 'user', 'active'),
('Sita Thapa', 'sita@example.com', '9823456789', 'Lalitpur, Nepal', '$2y$10$BxA/cTqs1tuZmd0rsNDUfOkCV0TT/m3NwqBaltBNUis.QQt4FZYBO', 'user', 'active');

INSERT INTO categories (name, slug, description, type) VALUES
('Motorcycle', 'motorcycle', 'Standard motorcycles for city and highway travel', 'two-wheeler'),
('Scooter', 'scooter', 'Easy-to-ride scooters for daily commute', 'two-wheeler'),
('Electric Scooter', 'electric-scooter', 'Eco-friendly electric scooters', 'two-wheeler'),
('Car', 'car', 'Comfortable cars for family and business travel', 'four-wheeler'),
('Jeep', 'jeep', 'Rugged jeeps for off-road and mountain trips', 'four-wheeler'),
('Van', 'van', 'Spacious vans for group travel and cargo', 'four-wheeler');

INSERT INTO vehicles (category_id, name, vehicle_number, brand, model_year, price_per_day, description, image, status) VALUES
(1, 'Pulsar NS200', 'BA 1 PA 2024', 'Bajaj', 2023, 1500.00, 'A powerful 200cc motorcycle perfect for city rides and short trips. Features ABS braking and digital console.', 'pulsar_ns200.jpg', 'available'),
(1, 'Apache RTR 160', 'BA 2 PA 3045', 'TVS', 2024, 1200.00, 'Sporty 160cc motorcycle with excellent fuel efficiency and smooth handling.', 'apache_rtr160.jpg', 'available'),
(2, 'Honda Activa 6G', 'BA 3 PA 5567', 'Honda', 2023, 800.00, 'Indias most trusted scooter. Easy to ride with great mileage and comfort.', 'activa_6g.jpg', 'available'),
(2, 'Dio 125', 'BA 4 PA 6789', 'Honda', 2024, 900.00, 'Stylish and sporty scooter with excellent pick-up and modern features.', 'dio_125.jpg', 'available'),
(3, 'Ather 450X', 'BA 5 PA 1122', 'Ather', 2024, 1000.00, 'Premium electric scooter with fast charging, connected features, and 85 km range.', 'ather_450x.jpg', 'available'),

(4, 'Swift Dzire', 'BA 1 JA 2233', 'Maruti Suzuki', 2023, 3500.00, 'Compact sedan with spacious interiors, great mileage, and smooth automatic transmission.', 'swift_dzire.jpg', 'available'),
(4, 'Honda City', 'BA 2 JA 4455', 'Honda', 2024, 4500.00, 'Premium sedan with advanced safety features, sunroof, and powerful engine.', 'honda_city.jpg', 'available'),
(5, 'Mahindra Thar', 'BA 3 JA 6677', 'Mahindra', 2023, 5000.00, 'Rugged off-road SUV with 4x4 capability, perfect for mountain adventures.', 'mahindra_thar.jpg', 'available'),
(5, 'Scorpio N', 'BA 4 JA 8899', 'Mahindra', 2024, 4800.00, 'Powerful SUV with seating for 7, ideal for family road trips and off-road trails.', 'scorpio_n.jpg', 'available'),
(6, 'Toyota HiAce', 'BA 5 JA 1010', 'Toyota', ' 2022', 6000.00, 'Spacious 15-seater van perfect for group travel, tours, and events.', 'toyota_hiace.jpg', 'available');

