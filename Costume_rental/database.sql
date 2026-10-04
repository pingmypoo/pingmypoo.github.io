CREATE DATABASE IF NOT EXISTS costume_rental_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE costume_rental_db;

CREATE TABLE tb_member (
  member_id CHAR(10) PRIMARY KEY, fname VARCHAR(100) NOT NULL, lname VARCHAR(100) NOT NULL,
  address VARCHAR(255), phone VARCHAR(15), email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL, register_date DATE NOT NULL, status VARCHAR(20) NOT NULL DEFAULT 'ใช้งาน'
);
CREATE TABLE tb_employee (
  emp_id CHAR(10) PRIMARY KEY, fname VARCHAR(100) NOT NULL, lname VARCHAR(100) NOT NULL,
  position VARCHAR(50), phone VARCHAR(15), email VARCHAR(100), username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL, start_date DATE
);
CREATE TABLE tb_costume (
  costume_id CHAR(10) PRIMARY KEY, costume_name VARCHAR(150) NOT NULL, category VARCHAR(50),
  size VARCHAR(10), color VARCHAR(30), rental_price DECIMAL(10,2) NOT NULL, deposit_price DECIMAL(10,2) NOT NULL DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'ว่าง', image_path VARCHAR(255), description TEXT
);
CREATE TABLE tb_booking (
  booking_id CHAR(10) PRIMARY KEY, member_id CHAR(10) NOT NULL, costume_id CHAR(10) NOT NULL, emp_id CHAR(10) NULL,
  booking_date DATE NOT NULL, rent_date DATE NOT NULL, due_return_date DATE NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'จอง', total_amount DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (member_id) REFERENCES tb_member(member_id),
  FOREIGN KEY (costume_id) REFERENCES tb_costume(costume_id),
  FOREIGN KEY (emp_id) REFERENCES tb_employee(emp_id)
);
CREATE TABLE tb_payment (
  payment_id CHAR(10) PRIMARY KEY, booking_id CHAR(10) NOT NULL, emp_id CHAR(10) NULL,
  payment_date DATETIME NOT NULL, amount DECIMAL(10,2) NOT NULL, payment_method VARCHAR(30), receipt_no VARCHAR(20),
  FOREIGN KEY (booking_id) REFERENCES tb_booking(booking_id),
  FOREIGN KEY (emp_id) REFERENCES tb_employee(emp_id)
);
CREATE TABLE tb_return (
  return_id CHAR(10) PRIMARY KEY, booking_id CHAR(10) NOT NULL UNIQUE, emp_id CHAR(10) NOT NULL,
  return_date DATE NOT NULL, `condition` VARCHAR(30) NOT NULL, remark VARCHAR(255),
  FOREIGN KEY (booking_id) REFERENCES tb_booking(booking_id),
  FOREIGN KEY (emp_id) REFERENCES tb_employee(emp_id)
);
CREATE TABLE tb_fine (
  fine_id CHAR(10) PRIMARY KEY, return_id CHAR(10) NOT NULL, fine_reason VARCHAR(100), fine_amount DECIMAL(10,2) NOT NULL,
  paid_status VARCHAR(20) NOT NULL DEFAULT 'ยังไม่ชำระ', paid_date DATE NULL,
  FOREIGN KEY (return_id) REFERENCES tb_return(return_id)
);
