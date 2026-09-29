-- ============================================
-- Mini Clinic Manager - Database Schema
-- ============================================

CREATE DATABASE IF NOT EXISTS clinic_manager;
USE clinic_manager;

-- Users table (patients, doctors, admin all live here, differentiated by role)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    role ENUM('patient', 'doctor', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Doctor-specific extra info
CREATE TABLE doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    bio TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Doctor weekly availability (set by Admin)
CREATE TABLE doctor_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    day_of_week ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    slot_duration_minutes INT DEFAULT 30,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

-- Appointments
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    time_slot TIME NOT NULL,
    status ENUM('booked','completed','cancelled') DEFAULT 'booked',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    UNIQUE KEY unique_slot (doctor_id, appointment_date, time_slot)
);

-- Prescriptions / consultation notes
CREATE TABLE prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    notes TEXT,
    medicines TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
);

-- ============================================
-- Sample seed data (for demo purposes)
-- ============================================

-- Admin (password: admin123)
INSERT INTO users (name, email, password_hash, role) VALUES
('Admin User', 'admin@clinic.com', '$2b$10$aZUADNmxYrzG8c3p/YlhM.mYKvAJdFusdSa02r96Jer/klcGPWjvO', 'admin');

-- Doctors (password: doctor123 for both)
INSERT INTO users (name, email, password_hash, phone, role) VALUES
('Dr. Ansari Sharma', 'ansari@clinic.com', '$2b$10$DGb/qTwOnYIFKUMDEc.21eJAVO5FqD5Lit2.R6E5yDI2ZU1rG6aDC', '9876543210', 'doctor'),
('Dr. Priya Nair', 'priya@clinic.com', '$2b$10$DGb/qTwOnYIFKUMDEc.21eJAVO5FqD5Lit2.R6E5yDI2ZU1rG6aDC', '9876543211', 'doctor');

INSERT INTO doctors (user_id, specialization, bio) VALUES
(2, 'General Physician', 'MBBS, 10 years experience'),
(3, 'Dermatologist', 'MD Dermatology, 6 years experience');

INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time, slot_duration_minutes) VALUES
(1, 'Monday', '10:00:00', '13:00:00', 30),
(1, 'Wednesday', '10:00:00', '13:00:00', 30),
(2, 'Tuesday', '14:00:00', '17:00:00', 30),
(2, 'Thursday', '14:00:00', '17:00:00', 30);
