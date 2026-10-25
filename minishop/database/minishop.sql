
CREATE DATABASE IF NOT EXISTS minishop
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE minishop;

SET NAMES utf8mb4;

DROP TABLE IF EXISTS contacts;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

CREATE TABLE categories (
    id   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id INT UNSIGNED NOT NULL,
    name        VARCHAR(150) NOT NULL,
    description TEXT,
    price       DECIMAL(12,0) NOT NULL,
    stock       INT UNSIGNED NOT NULL DEFAULT 0,   -- tồn kho
    image       VARCHAR(255) NOT NULL DEFAULT 'no-image.jpg',
    PRIMARY KEY (id),
    -- Không xóa được danh mục khi còn sản phẩm thuộc danh mục đó
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories (id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    fullname   VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    password   VARCHAR(255) NOT NULL,  
    address    VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id      INT UNSIGNED NOT NULL,
    total        DECIMAL(12,0) NOT NULL DEFAULT 0,
    status       ENUM('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending',
    ship_name    VARCHAR(100) NOT NULL,
    ship_phone   VARCHAR(20)  NOT NULL,
    ship_address VARCHAR(255) NOT NULL,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_orders_user
        FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE order_items (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id     INT UNSIGNED NOT NULL,
    product_id   INT UNSIGNED NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    price        DECIMAL(12,0) NOT NULL,
    quantity     INT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    CONSTRAINT fk_items_order
        FOREIGN KEY (order_id) REFERENCES orders (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_items_product
        FOREIGN KEY (product_id) REFERENCES products (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE contacts (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    fullname   VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    subject    VARCHAR(200) NOT NULL,
    message    TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categories (id, name) VALUES
    (1, 'Laptop'),
    (2, 'Chuột'),
    (3, 'Bàn phím'),
    (4, 'Tai nghe'),
    (5, 'Phụ kiện');

INSERT INTO products (id, category_id, name, description, price, stock, image) VALUES
    (1,  2, 'Chuột Logitech',        'Chuột không dây nhỏ gọn, dễ mang theo đi học.',                    499000,   25, 'sp01.jpg'),
    (2,  3, 'Bàn phím cơ',           'Gõ phím êm tay, phù hợp học tập và chơi game.',                    899000,   10, 'sp02.jpg'),
    (3,  4, 'Tai nghe Bluetooth',    'Kết nối không dây, âm thanh rõ khi học online.',                   699000,   15, 'sp03.jpg'),
    (4,  1, 'Laptop ASUS Vivobook',  'Laptop mỏng nhẹ, Core i5, RAM 16GB, SSD 512GB, màn hình 15.6 inch.', 18990000, 5,  'sp04.jpg'),
    (5,  5, 'Balo laptop',           'Ngăn đệm chống sốc, đựng vừa laptop 15.6 inch.',                   350000,   30, 'sp05.jpg'),
    (6,  5, 'USB 64GB',              'Lưu trữ và sao chép bài tập nhanh gọn.',                           159000,   50, 'sp06.jpg'),
    (7,  1, 'Laptop Acer Aspire',    'Laptop văn phòng cấu hình cân bằng, pin dùng cả buổi học.',        16490000, 8,  'sp07.jpg'),
    (8,  1, 'Laptop Lenovo IdeaPad', 'Laptop giá mềm cho sinh viên năm đầu, đáp ứng tốt việc lập trình.', 14290000, 6,  'sp08.jpg'),
    (9,  2, 'Chuột gaming có dây',   'Chuột có dây độ nhạy cao, đèn LED, phù hợp chơi game.',            299000,   20, 'sp09.jpg'),
    (10, 5, 'Đế tản nhiệt laptop',   'Đế nâng và quạt làm mát giúp laptop chạy mát hơn.',                189000,   40, 'sp10.jpg'),
    (11, 4, 'Tai nghe chụp tai',     'Tai nghe chụp tai có mic, đeo thoải mái khi học lâu.',             459000,   12, 'sp11.jpg'),
    (12, 3, 'Bàn phím mini',         'Bàn phím nhỏ gọn, tiết kiệm chỗ trên bàn học.',                    590000,   18, 'sp12.jpg'),
    (13, 1, 'Laptop HP 15',          'Laptop màn hình 15.6 inch, bàn phím có phím số, giá dễ chịu.',     15790000, 7,  'sp13.jpg');
