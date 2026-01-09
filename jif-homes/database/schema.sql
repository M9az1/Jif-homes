-- JIF HOMES Database Schema
-- MySQL/MariaDB

-- Create Database
CREATE DATABASE IF NOT EXISTS jif_homes 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE jif_homes;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    avatar VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    email_verified TINYINT(1) DEFAULT 0,
    reset_token VARCHAR(64) NULL,
    reset_expires TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_reset_token (reset_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Apartments Table
CREATE TABLE IF NOT EXISTS apartments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title_ar VARCHAR(255) NOT NULL,
    title_en VARCHAR(255) NOT NULL,
    description_ar TEXT,
    description_en TEXT,
    address_ar VARCHAR(500),
    address_en VARCHAR(500),
    city_ar VARCHAR(100),
    city_en VARCHAR(100),
    price_per_day DECIMAL(10,2) NOT NULL,
    bedrooms INT DEFAULT 1,
    bathrooms INT DEFAULT 1,
    area_sqm DECIMAL(10,2),
    max_guests INT DEFAULT 2,
    floor_number INT,
    has_wifi TINYINT(1) DEFAULT 0,
    has_ac TINYINT(1) DEFAULT 1,
    has_kitchen TINYINT(1) DEFAULT 0,
    has_parking TINYINT(1) DEFAULT 0,
    has_tv TINYINT(1) DEFAULT 0,
    has_washer TINYINT(1) DEFAULT 0,
    has_pool TINYINT(1) DEFAULT 0,
    has_gym TINYINT(1) DEFAULT 0,
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    featured TINYINT(1) DEFAULT 0,
    view_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_city (city_en),
    INDEX idx_price (price_per_day),
    INDEX idx_featured (featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Apartment Images Table
CREATE TABLE IF NOT EXISTS apartment_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    apartment_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    alt_text_ar VARCHAR(255),
    alt_text_en VARCHAR(255),
    is_primary TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (apartment_id) REFERENCES apartments(id) ON DELETE CASCADE,
    INDEX idx_apartment (apartment_id),
    INDEX idx_primary (is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Locations Table (for map markers)
CREATE TABLE IF NOT EXISTS locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    apartment_id INT,
    name_ar VARCHAR(255) NOT NULL,
    name_en VARCHAR(255) NOT NULL,
    description_ar TEXT,
    description_en TEXT,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    marker_type ENUM('apartment', 'landmark', 'office') DEFAULT 'apartment',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (apartment_id) REFERENCES apartments(id) ON DELETE SET NULL,
    INDEX idx_apartment (apartment_id),
    INDEX idx_coords (latitude, longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bookings Table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    apartment_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    guests INT DEFAULT 1,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (apartment_id) REFERENCES apartments(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_apartment (apartment_id),
    INDEX idx_dates (check_in, check_out),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Feedback Table
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    feedback_type ENUM('general', 'complaint', 'suggestion', 'inquiry') DEFAULT 'general',
    status ENUM('new', 'read', 'responded') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    responded_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_type (feedback_type),
    UNIQUE KEY unique_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contact Messages Table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_is_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Favorites Table
CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    apartment_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (apartment_id) REFERENCES apartments(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, apartment_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reviews Table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    apartment_id INT NOT NULL,
    booking_id INT,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    is_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (apartment_id) REFERENCES apartments(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    INDEX idx_apartment (apartment_id),
    INDEX idx_approved (is_approved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings Table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Default Admin User (password: Admin@123)
INSERT INTO users (first_name, last_name, email, phone, password, role, is_active, email_verified) VALUES
('Admin', 'User', 'admin@jifhomes.com', '+966500000000', '$argon2id$v=19$m=65536,t=4,p=1$dHdQY3FKR2tPSGxhbXBPcg$qR3vC8dJ2uN6wL4xZ9yT1mK5pF7hB0sE+gA2iD4cW6M', 'admin', 1, 1);

-- Insert Sample Apartments
INSERT INTO apartments (title_ar, title_en, description_ar, description_en, address_ar, address_en, city_ar, city_en, price_per_day, bedrooms, bathrooms, area_sqm, max_guests, has_wifi, has_ac, has_kitchen, has_parking, has_tv, latitude, longitude, status, featured) VALUES
('شقة فاخرة في حي الملقا', 'Luxury Apartment in Al Malqa District', 'شقة فاخرة مؤثثة بالكامل مع إطلالة رائعة على المدينة. تتميز بتصميم عصري وتشطيبات راقية.', 'Fully furnished luxury apartment with stunning city views. Features modern design and premium finishes.', 'حي الملقا، شارع الأمير محمد بن عبدالعزيز', 'Al Malqa District, Prince Mohammed bin Abdulaziz Street', 'الرياض', 'Riyadh', 450.00, 2, 2, 120, 4, 1, 1, 1, 1, 1, 24.7749, 46.7387, 'active', 1),
('استوديو أنيق في حي العليا', 'Elegant Studio in Olaya District', 'استوديو عصري مثالي لرجال الأعمال والمسافرين. قريب من المراكز التجارية والمطاعم.', 'Modern studio perfect for business travelers. Close to shopping centers and restaurants.', 'حي العليا، طريق الملك فهد', 'Olaya District, King Fahd Road', 'الرياض', 'Riyadh', 250.00, 1, 1, 45, 2, 1, 1, 1, 0, 1, 24.6908, 46.6853, 'active', 1),
('شقة عائلية في حي الياسمين', 'Family Apartment in Al Yasmin District', 'شقة واسعة مناسبة للعائلات مع غرف نوم متعددة وصالة كبيرة. حي هادئ وآمن.', 'Spacious apartment suitable for families with multiple bedrooms and large living room. Quiet and safe neighborhood.', 'حي الياسمين، شارع أنس بن مالك', 'Al Yasmin District, Anas bin Malik Street', 'الرياض', 'Riyadh', 550.00, 3, 2, 180, 6, 1, 1, 1, 1, 1, 24.8231, 46.6283, 'active', 1),
('شقة مطلة على البحر', 'Sea View Apartment', 'شقة رائعة بإطلالة مباشرة على الكورنيش. مثالية للاسترخاء والاستمتاع بالمنظر.', 'Amazing apartment with direct view of the Corniche. Perfect for relaxation and enjoying the view.', 'حي الشاطئ، طريق الكورنيش', 'Al Shati District, Corniche Road', 'جدة', 'Jeddah', 600.00, 2, 2, 140, 4, 1, 1, 1, 1, 1, 21.5433, 39.1728, 'active', 1),
('بنتهاوس فاخر', 'Luxury Penthouse', 'بنتهاوس حصري مع تراس خاص وإطلالات بانورامية. أعلى مستويات الفخامة والراحة.', 'Exclusive penthouse with private terrace and panoramic views. Highest levels of luxury and comfort.', 'حي الزهراء، برج الفيصلية', 'Al Zahra District, Faisaliah Tower', 'الرياض', 'Riyadh', 1200.00, 4, 3, 300, 8, 1, 1, 1, 1, 1, 24.6902, 46.6855, 'active', 1);

-- Insert Sample Images for Apartments
INSERT INTO apartment_images (apartment_id, image_path, alt_text_ar, alt_text_en, is_primary, sort_order) VALUES
(1, 'apartment1_main.jpg', 'غرفة المعيشة الرئيسية', 'Main Living Room', 1, 1),
(1, 'apartment1_bedroom.jpg', 'غرفة النوم الرئيسية', 'Master Bedroom', 0, 2),
(1, 'apartment1_kitchen.jpg', 'المطبخ الحديث', 'Modern Kitchen', 0, 3),
(2, 'apartment2_main.jpg', 'الاستوديو', 'Studio View', 1, 1),
(2, 'apartment2_bathroom.jpg', 'الحمام', 'Bathroom', 0, 2),
(3, 'apartment3_main.jpg', 'غرفة المعيشة', 'Living Room', 1, 1),
(3, 'apartment3_bedroom1.jpg', 'غرفة النوم الأولى', 'First Bedroom', 0, 2),
(3, 'apartment3_bedroom2.jpg', 'غرفة النوم الثانية', 'Second Bedroom', 0, 3),
(4, 'apartment4_main.jpg', 'إطلالة على البحر', 'Sea View', 1, 1),
(4, 'apartment4_balcony.jpg', 'الشرفة', 'Balcony', 0, 2),
(5, 'apartment5_main.jpg', 'البنتهاوس', 'Penthouse View', 1, 1),
(5, 'apartment5_terrace.jpg', 'التراس الخاص', 'Private Terrace', 0, 2);

-- Insert Sample Locations
INSERT INTO locations (apartment_id, name_ar, name_en, description_ar, description_en, latitude, longitude, marker_type) VALUES
(1, 'شقة الملقا الفاخرة', 'Al Malqa Luxury Apartment', 'شقة فاخرة في قلب حي الملقا', 'Luxury apartment in the heart of Al Malqa', 24.7749, 46.7387, 'apartment'),
(2, 'استوديو العليا', 'Olaya Studio', 'استوديو عصري في حي العليا', 'Modern studio in Olaya district', 24.6908, 46.6853, 'apartment'),
(3, 'شقة الياسمين', 'Al Yasmin Apartment', 'شقة عائلية في حي الياسمين', 'Family apartment in Al Yasmin', 24.8231, 46.6283, 'apartment'),
(4, 'شقة الشاطئ', 'Beach Apartment', 'شقة مطلة على البحر في جدة', 'Sea view apartment in Jeddah', 21.5433, 39.1728, 'apartment'),
(5, 'البنتهاوس الفاخر', 'Luxury Penthouse', 'بنتهاوس فاخر في برج الفيصلية', 'Luxury penthouse in Faisaliah Tower', 24.6902, 46.6855, 'apartment'),
(NULL, 'مكتب جف هومز الرئيسي', 'Jif Homes Main Office', 'المكتب الرئيسي لشركة جف هومز', 'Jif Homes main office location', 24.7136, 46.6753, 'office');

-- Insert Default Settings
INSERT INTO settings (setting_key, setting_value, setting_type) VALUES
('site_name_ar', 'جف هومز', 'string'),
('site_name_en', 'Jif Homes', 'string'),
('contact_email', 'info@jifhomes.com', 'string'),
('contact_phone', '+966 50 000 0000', 'string'),
('contact_address_ar', 'الرياض، المملكة العربية السعودية', 'string'),
('contact_address_en', 'Riyadh, Saudi Arabia', 'string'),
('default_currency', 'SAR', 'string'),
('default_language', 'ar', 'string');
