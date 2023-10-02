CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    surname VARCHAR(255) NOT NULL,
    student_no VARCHAR(20) NOT NULL,
    contact VARCHAR(20) NOT NULL,
    module_code VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL
);