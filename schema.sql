CREATE DATABASE yeticave
  DEFAULT CHARACTER SET utf8
  DEFAULT COLLATE utf8_general_ci;

USE yeticave;

CREATE TABLE category (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  symbol_code VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE user (
  id INT AUTO_INCREMENT PRIMARY KEY,
  registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  email VARCHAR(255) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  contacts TEXT
);

CREATE TABLE lot (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  image_url VARCHAR(255),
  start_price DECIMAL(10, 2) NOT NULL,
  end_date DATETIME NOT NULL,
  rate_step DECIMAL(10, 2) NOT NULL,
  author_id INT NOT NULL,
  winner_id INT DEFAULT NULL,
  category_id INT NOT NULL,
  FOREIGN KEY (author_id) REFERENCES user(id) ON DELETE CASCADE,
  FOREIGN KEY (winner_id) REFERENCES user(id) ON DELETE SET NULL,
  FOREIGN KEY (category_id) REFERENCES category(id) ON DELETE CASCADE
);

CREATE TABLE rate (
  id INT AUTO_INCREMENT PRIMARY KEY,
  rate_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  amount DECIMAL(10, 2) NOT NULL,
  user_id INT NOT NULL,
  lot_id INT NOT NULL,
  FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
  FOREIGN KEY (lot_id) REFERENCES lot(id) ON DELETE CASCADE
);

/* Создание индексов для поиска
CREATE INDEX idx_lots_category_id ON lot(category_id);
CREATE INDEX idx_lots_author_id ON lot(author_id);
CREATE INDEX idx_lots_winner_id ON lot(winner_id);
CREATE INDEX idx_bids_user_id ON rate(user_id);
CREATE INDEX idx_bids_lot_id ON rate(lot_id);
 */
