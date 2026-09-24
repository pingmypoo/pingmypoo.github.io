-- phpMyAdmin: select YOUR database on the left -> Import tab -> choose this file -> Go
-- (No CREATE DATABASE here: on shared hosting the database is created in the hosting panel.)
SET NAMES utf8mb4;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  phone VARCHAR(20) NOT NULL DEFAULT '',
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE dresses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(10) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  category VARCHAR(30) NOT NULL,
  size VARCHAR(3) NOT NULL,
  color VARCHAR(30) NOT NULL,
  color_hex VARCHAR(7) NOT NULL,
  price_per_day INT NOT NULL,
  deposit INT NOT NULL,
  available TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  dress_id INT NOT NULL,
  renter_name VARCHAR(100) NOT NULL,
  contact VARCHAR(255) NOT NULL,
  pickup_date DATE NOT NULL,
  return_date DATE NOT NULL,
  days INT NOT NULL,
  rent_total INT NOT NULL,
  deposit INT NOT NULL,
  status ENUM('pending','paid','cancelled') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (dress_id) REFERENCES dresses(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL UNIQUE,
  method ENUM('bank_transfer','promptpay','pay_at_shop') NOT NULL,
  amount INT NOT NULL,
  paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_id) REFERENCES bookings(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO dresses (code,name,category,size,color,color_hex,price_per_day,deposit,available) VALUES
('A123','ชุดราตรีสีทอง','ชุดราตรี','M','ทอง','#e6c26e',800,1000,1),
('T045','ชุดไทยจิตรลดา','ชุดไทย','S','ชมพู','#f2a6c0',600,800,1),
('S210','สูทสีกรมท่า','ชุดสูท','L','กรมท่า','#5b78a8',500,700,0),
('F311','ชุดแฟนซีแม่มด','ชุดแฟนซี','M','ดำ','#8d7a99',350,500,1),
('T067','ชุดไทยประยุกต์','ชุดไทย','M','เขียว','#7cc3a0',700,900,1),
('A150','ชุดราตรีสีแดง','ชุดราตรี','S','แดง','#e57a8a',900,1200,0),
('S118','สูทสีดำ','ชุดสูท','XL','ดำ','#9a8fa3',550,800,1),
('F402','ชุดแฟนซีเจ้าหญิง','ชุดแฟนซี','S','ฟ้า','#8fc7ea',400,600,1);
