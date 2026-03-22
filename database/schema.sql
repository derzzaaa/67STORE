-- Создание базы данных для интернет-магазина 7-Eleven
CREATE DATABASE IF NOT EXISTS seven_eleven_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE seven_eleven_shop;

-- Таблица пользователей
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица категорий
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    icon VARCHAR(50),
    sort_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица товаров
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    old_price DECIMAL(10,2),
    image VARCHAR(255),
    stock INT DEFAULT 0,
    is_hot_deal BOOLEAN DEFAULT FALSE,
    discount_percent INT,
    deal_ends_at DATETIME NULL,
    category_label VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица заказов
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    total DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    tax DECIMAL(10,2) DEFAULT 0,
    delivery_fee DECIMAL(10,2) DEFAULT 0,
    discount DECIMAL(10,2) DEFAULT 0,
    promo_code VARCHAR(50),
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    delivery_address TEXT,
    delivery_type ENUM('delivery', 'pickup') DEFAULT 'delivery',
    payment_type ENUM('online', 'cash', 'card') DEFAULT 'online',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица позиций заказа
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица промокодов
CREATE TABLE promo_codes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount_percent INT,
    discount_amount DECIMAL(10,2),
    valid_from DATE,
    valid_to DATE,
    usage_limit INT,
    used_count INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Вставка категорий
INSERT INTO categories (name, slug, icon, sort_order) VALUES
('All Hot Deals', 'hot-deals', '', 1),
('Beverages', 'beverages', '', 2),
('Snacks', 'snacks', '', 3),
('Fresh Food', 'fresh-food', '', 4),
('Frozen Treats', 'frozen-treats', '', 5),
('Essentials', 'essentials', '', 6);

-- Вставка тестовых товаров
INSERT INTO products (category_id, name, description, price, old_price, image, stock, is_hot_deal, discount_percent, deal_ends_at, category_label) VALUES
-- Beverages
(2, 'Giant Slurp® Refillable Mug (32oz)', 'Refreshing frozen beverage in a reusable mug', 2.49, NULL, 'slurp.jpg', 50, TRUE, 50, DATE_ADD(NOW(), INTERVAL 2 HOUR), 'BEVERAGE BLAST'),
(2, 'Fresh Squeezed Style Orange Juice (1L)', 'Fresh and natural orange juice', 4.79, NULL, 'orange-juice.jpg', 40, FALSE, 20, DATE_ADD(NOW(), INTERVAL 1 DAY), 'BEVERAGES'),
(2, 'Large Slurpee® Wild Cherry Flavor', 'Classic frozen drink with wild cherry flavor', 2.19, NULL, 'slurpee.jpg', 100, FALSE, NULL, NULL, 'BEVERAGES'),
(2, 'Pure Leaf Iced Tea 18.5 fl oz Bottle', 'Premium brewed iced tea', 5.98, NULL, 'iced-tea.jpg', 60, FALSE, NULL, NULL, 'BEVERAGES'),

-- Snacks
(3, 'Jumbo Classic Corn Dogs (2 Pack)', 'Hot and crispy corn dogs', 1.75, NULL, 'corndog.jpg', 30, FALSE, 30, DATE_ADD(NOW(), INTERVAL 5 HOUR), 'HOT SNACKS'),
(3, 'Family Size Kettle Cooked Potato Chips', 'Crunchy kettle-cooked chips', 2.99, NULL, 'chips.jpg', 60, FALSE, NULL, NULL, 'SALTY SNACKS'),
(3, 'Lay\'s Classic Potato Chips 8 oz Party Size', 'America\'s favorite potato chips', 4.49, NULL, 'lays-chips.jpg', 80, FALSE, NULL, NULL, 'SALTY SNACKS'),
(3, 'Cheetos Puffs', 'Cheesy and crunchy snack', 1.99, NULL, 'cheetos.jpg', 70, FALSE, NULL, NULL, 'SNACKS'),

-- Fresh Food
(4, 'Fresh Baked Glazed Donuts (6 ct)', 'Soft and sweet glazed donuts', 6.29, NULL, 'donuts.jpg', 20, TRUE, 30, DATE_ADD(NOW(), INTERVAL 5 HOUR), 'BAKERY FRESH'),
(4, 'Whole 14" Pepperoni Party Pizza', 'Hot and ready pepperoni pizza', 7.79, 9.99, 'pizza.jpg', 15, TRUE, 40, DATE_ADD(NOW(), INTERVAL 7 HOUR), 'HOT FOOD'),
(4, '7-Eleven Quarter Pound Big Bite Hot Dog', 'Classic American hot dog', 2.49, NULL, 'hotdog.jpg', 50, FALSE, NULL, NULL, 'HOT FOOD'),
(4, 'Mexican Coke 12oz', 'Authentic Mexican Coca-Cola', 2.29, NULL, 'mexican-coke.jpg', 100, FALSE, NULL, NULL, 'BEVERAGES'),

-- Frozen Treats
(5, 'Premium Milk Chocolate Bar (Regular Size)', 'Rich and creamy chocolate', 1.67, 2.50, 'chocolate.jpg', 100, TRUE, 40, DATE_ADD(NOW(), INTERVAL 10 HOUR), 'SWEET TREATS'),
(5, '7-Select Glazed Donut', 'Fresh glazed donut', 1.49, NULL, 'glazed-donut.jpg', 40, FALSE, NULL, NULL, 'BAKERY'),
(5, 'Haribo Goldbears', 'Classic gummy bears', 3.19, NULL, 'haribo.jpg', 90, FALSE, NULL, NULL, 'CANDY'),

-- Essentials
(6, 'Paper Towels 2-Pack', 'Absorbent paper towels', 5.99, NULL, 'paper-towels.jpg', 30, FALSE, NULL, NULL, 'HOUSEHOLD'),
(6, 'Hand Sanitizer 8oz', 'Kills 99.9% of germs', 3.99, NULL, 'sanitizer.jpg', 50, FALSE, NULL, NULL, 'HEALTH');

-- Вставка тестовых промокодов
INSERT INTO promo_codes (code, discount_percent, discount_amount, valid_from, valid_to, usage_limit, used_count, is_active) VALUES
('WELCOME10', 10, NULL, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 100, 0, TRUE),
('SAVE5', NULL, 5.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 50, 0, TRUE),
('FREESHIP', NULL, 3.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 200, 0, TRUE);

-- Создание тестового пользователя (пароль: password123)
INSERT INTO users (email, password, first_name, last_name, phone) VALUES
('test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test', 'User', '+1234567890');
