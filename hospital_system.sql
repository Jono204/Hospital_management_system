-- ============================================================
-- Hospital Appointment & Patient Management System
-- MySQL Database Script
-- HPXS302-1 | Question 6
-- ============================================================

CREATE DATABASE IF NOT EXISTS hospital_system;
USE hospital_system;

-- ============================================================
-- TABLE CREATION
-- ============================================================

CREATE TABLE patient (
    patient_id    INT AUTO_INCREMENT PRIMARY KEY,
    first_name    VARCHAR(50)  NOT NULL,
    last_name     VARCHAR(50)  NOT NULL,
    email         VARCHAR(100) NOT NULL UNIQUE,
    phone         VARCHAR(15)  NOT NULL,
    date_of_birth DATE         NOT NULL,
    address       VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL
);

CREATE TABLE doctor (
    doctor_id      INT AUTO_INCREMENT PRIMARY KEY,
    first_name     VARCHAR(50)  NOT NULL,
    last_name      VARCHAR(50)  NOT NULL,
    email          VARCHAR(100) NOT NULL UNIQUE,
    specialisation VARCHAR(100) NOT NULL,
    password_hash  VARCHAR(255) NOT NULL
);

CREATE TABLE admin (
    admin_id      INT AUTO_INCREMENT PRIMARY KEY,
    first_name    VARCHAR(50)  NOT NULL,
    last_name     VARCHAR(50)  NOT NULL,
    email         VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
);

CREATE TABLE appointment (
    appointment_id   INT AUTO_INCREMENT PRIMARY KEY,
    patient_id       INT          NOT NULL,
    doctor_id        INT          NOT NULL,
    appointment_date DATE         NOT NULL,
    appointment_time TIME         NOT NULL,
    status           ENUM('requested','confirmed','completed','cancelled') NOT NULL DEFAULT 'requested',
    reason           VARCHAR(255) NOT NULL,
    CONSTRAINT fk_appointment_patient
        FOREIGN KEY (patient_id) REFERENCES patient(patient_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_appointment_doctor
        FOREIGN KEY (doctor_id) REFERENCES doctor(doctor_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE medical_record (
    record_id      INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT          NOT NULL UNIQUE,
    diagnosis      VARCHAR(255) NOT NULL,
    treatment      VARCHAR(255) NOT NULL,
    notes          TEXT,
    created_at     DATE         NOT NULL,
    CONSTRAINT fk_record_appointment
        FOREIGN KEY (appointment_id) REFERENCES appointment(appointment_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- ============================================================
-- SAMPLE DATA
-- SECURITY: Passwords stored as bcrypt hashes (PASSWORD_DEFAULT)
-- Plain text passwords shown in comments for testing ONLY
-- In production, never store or share plain text passwords
--
-- patient passwords  : password123, sarapass, james2026, lerato99, mikevw78
-- doctor passwords   : drsmith2026, drnkosi2026, drpatel2026, drbotha2026
-- admin passwords    : adminpass2026, karen2026
-- ============================================================

INSERT INTO patient (first_name, last_name, email, phone, date_of_birth, address, password_hash) VALUES
('John',    'Doe',     'john.doe@email.com',     '0821234567', '1990-04-15', '12 Oak Street, Cape Town',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Sara',    'Mokoena', 'sara.mokoena@email.com', '0839876543', '1985-08-22', '45 Elm Road, Johannesburg',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('James',   'Peters',  'james.p@email.com',      '0761112233', '1995-11-03', '7 Pine Avenue, Durban',         '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Lerato',  'Dlamini', 'lerato.d@email.com',     '0724445566', '2000-01-30', '99 Maple Lane, Pretoria',       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Michael', 'van Wyk', 'mike.vanwyk@email.com',  '0817778899', '1978-06-18', '3 Rose Street, Port Elizabeth', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO doctor (first_name, last_name, email, specialisation, password_hash) VALUES
('Amanda', 'Smith', 'dr.smith@hospital.com', 'General Practitioner', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Thabo',  'Nkosi', 'dr.nkosi@hospital.com', 'Cardiology',           '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Priya',  'Patel', 'dr.patel@hospital.com', 'Paediatrics',          '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Willem', 'Botha', 'dr.botha@hospital.com', 'Orthopaedics',         '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO admin (first_name, last_name, email, password_hash) VALUES
('System', 'Admin',  'admin@hospital.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Karen',  'Joubert','karen.j@hospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO appointment (patient_id, doctor_id, appointment_date, appointment_time, status, reason) VALUES
(1, 1, '2026-06-14', '09:00:00', 'confirmed',  'Annual check-up'),
(2, 2, '2026-06-14', '10:30:00', 'confirmed',  'Chest pain follow-up'),
(3, 3, '2026-06-15', '08:00:00', 'requested',  'Child vaccination'),
(4, 1, '2026-06-16', '14:00:00', 'requested',  'Flu symptoms'),
(5, 4, '2026-06-17', '11:00:00', 'confirmed',  'Knee injury consultation'),
(1, 2, '2026-06-20', '09:30:00', 'requested',  'Heart screening'),
(2, 1, '2026-05-10', '10:00:00', 'completed',  'Blood pressure check'),
(3, 3, '2026-05-12', '08:30:00', 'completed',  'Ear infection'),
(4, 4, '2026-05-20', '13:00:00', 'cancelled',  'Back pain'),
(5, 2, '2026-05-25', '15:00:00', 'completed',  'Cardiac stress test');

INSERT INTO medical_record (appointment_id, diagnosis, treatment, notes, created_at) VALUES
(7,  'Stage 1 hypertension',        'Prescribed lisinopril 10mg daily',  'Patient advised to reduce salt intake and exercise regularly.',      '2026-05-10'),
(8,  'Acute otitis media',           'Prescribed amoxicillin 250mg',      'Patient to return in 1 week if symptoms persist.',                  '2026-05-12'),
(10, 'Mild coronary artery disease', 'Prescribed aspirin 81mg daily',     'Referred for echocardiogram. Avoid strenuous activity.',            '2026-05-25');

-- ============================================================
-- VERIFICATION QUERIES
-- ============================================================

SELECT
    a.appointment_id,
    CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
    CONCAT(d.first_name, ' ', d.last_name) AS doctor_name,
    d.specialisation,
    a.appointment_date,
    a.appointment_time,
    a.status,
    a.reason
FROM appointment a
JOIN patient p ON a.patient_id = p.patient_id
JOIN doctor  d ON a.doctor_id  = d.doctor_id
ORDER BY a.appointment_date;

SELECT
    a.appointment_id,
    CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
    mr.diagnosis,
    mr.treatment,
    mr.notes,
    mr.created_at
FROM medical_record mr
JOIN appointment a ON mr.appointment_id = a.appointment_id
JOIN patient     p ON a.patient_id      = p.patient_id;
