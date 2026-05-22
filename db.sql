CREATE DATABASE IF NOT EXISTS lab3 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE lab3;

CREATE TABLE applications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    biography TEXT,
    agreement TINYINT(1) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE programming_languages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE application_languages (
    application_id INT UNSIGNED NOT NULL,
    language_id INT UNSIGNED NOT NULL,

    PRIMARY KEY(application_id, language_id),

    FOREIGN KEY (application_id)
        REFERENCES applications(id)
        ON DELETE CASCADE,

    FOREIGN KEY (language_id)
        REFERENCES programming_languages(id)
        ON DELETE CASCADE
);

INSERT INTO programming_languages(name) VALUES
('Pascal'),
('C'),
('C++'),
('JavaScript'),
('PHP'),
('Python'),
('Java'),
('Haskell'),
('Clojure'),
('Prolog'),
('Scala'),
('Go');
