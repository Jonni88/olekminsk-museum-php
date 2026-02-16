-- База данных музея "Память Олёкминского края"
-- Для импорта в phpMyAdmin на Бегете

CREATE DATABASE IF NOT EXISTS olekminsk_museum 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

USE olekminsk_museum;

-- Таблица ветеранов
CREATE TABLE veterans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    last_name VARCHAR(100) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    patronymic VARCHAR(100),
    birth_year INT,
    death_year INT,
    settlement VARCHAR(200),
    rank VARCHAR(200),
    awards TEXT,
    biography TEXT,
    front_path TEXT,
    photo_main VARCHAR(255),
    photos JSON,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    submitted_by VARCHAR(200),
    submitter_contact VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    approved_by INT,
    views_count INT DEFAULT 0,
    INDEX idx_last_name (last_name),
    INDEX idx_status (status),
    INDEX idx_settlement (settlement),
    FULLTEXT INDEX idx_search (last_name, first_name, patronymic, biography)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Таблица погибших (Книга памяти)
CREATE TABLE fallen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(300) NOT NULL,
    birth_year INT,
    death_year INT,
    settlement VARCHAR(200),
    burial_place VARCHAR(300),
    circumstances TEXT,
    status ENUM('pending', 'approved') DEFAULT 'pending',
    submitted_by VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_settlement (settlement)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Таблица фотоархива
CREATE TABLE photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(300),
    description TEXT,
    filename VARCHAR(255) NOT NULL,
    year INT,
    source VARCHAR(300),
    status ENUM('pending', 'approved') DEFAULT 'approved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Таблица документов
CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(300) NOT NULL,
    type ENUM('award', 'letter', 'memo', 'other') DEFAULT 'other',
    description TEXT,
    filename VARCHAR(255),
    veteran_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (veteran_id) REFERENCES veterans(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Таблица администраторов
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    telegram_id BIGINT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Таблица посланий (гостевая книга)
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    veteran_id INT,
    author_name VARCHAR(100),
    message TEXT NOT NULL,
    is_public BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (veteran_id) REFERENCES veterans(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Демо-данные (тестовые ветераны)
INSERT INTO veterans (last_name, first_name, patronymic, birth_year, death_year, settlement, rank, awards, biography, status) VALUES
('Иванов', 'Петр', 'Степанович', 1920, 1942, 'с. Олёкминск', 'рядовой', 'Орден Красной Звезды', 'Участник обороны Сталинграда. Погиб в бою 15 января 1942 года.', 'approved'),
('Сидоров', 'Иван', 'Михайлович', 1918, 1985, 'п. Марха', 'старшина', 'Орден Отечественной войны II степени, медаль "За отвагу"', 'Прошёл всю войну. Демобилизован в 1946 году.', 'approved'),
('Петрова', 'Мария', 'Алексеевна', 1925, NULL, 'с. Тюкян', 'младший сержант', 'Медаль "За боевые заслуги"', 'Санитарка в полевом госпитале. Награждена за спасение раненых под огнём.', 'approved');

-- Демо-админ (логин: admin, пароль: admin123)
-- В реальном проекте сменить пароль!
INSERT INTO admins (username, password_hash, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@museum.ru');
