-- 1. Crear la base de datos
CREATE DATABASE IF NOT EXISTS `hotel_reservas`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `hotel_reservas`;

-- 2. Tabla roles
CREATE TABLE IF NOT EXISTS roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(20) NOT NULL
);
INSERT INTO roles (name) VALUES 
  ('Administrator'),
  ('Hotel Manager'),
  ('Receptionist'),
  ('Customer'),
  ('Supplier')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- 3. Tabla users
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- 4. Tabla rooms
CREATE TABLE IF NOT EXISTS rooms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_number INT NOT NULL UNIQUE,
  room_type VARCHAR(20) NOT NULL,
  room_price DECIMAL(10,2) NOT NULL,
  is_available BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Tabla bookings
CREATE TABLE IF NOT EXISTS bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  room_id INT NOT NULL,
  booking_date DATETIME NOT NULL,
  check_in_date DATE NOT NULL,
  check_out_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (room_id) REFERENCES rooms(id)
);

-- 6. Tabla supplies
CREATE TABLE IF NOT EXISTS supplies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  quantity INT NOT NULL,
  supplier_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (supplier_id) REFERENCES users(id)
);
-- 1) Asegurarnos de que existen las habitaciones de ejemplo
INSERT INTO rooms (room_number, room_type, room_price, is_available)
VALUES
  (101, 'estandar', 50.00, 1),
  (102, 'estandar', 60.00, 1),
  (201, 'deluxe',   80.00, 1),
  (202, 'deluxe',   90.00, 1),
  (301, 'suite',   120.00, 1),
  (302, 'suite',   130.00, 1)
ON DUPLICATE KEY UPDATE
  room_price = VALUES(room_price),
  is_available = VALUES(is_available);
