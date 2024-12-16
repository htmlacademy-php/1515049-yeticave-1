CREATE DATABASE yeticave
  DEFAULT CHARACTER SET utf8
  DEFAULT COLLATE utf8_general_ci;

USE yeticave

CREATE TABLE category (
  id INT AUTO_INCREMENT PRIMARY KEY,
  symbol_code CHAR(64)
);

CREATE TABLE lot (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  name,
  description,
  img_url,
  start_price,
  rate_step,
);

CREATE TABLE rate (
  id INT AUTO_INCREMENT PRIMARY KEY,
);

CREATE TABLE user (
  id INT AUTO_INCREMENT PRIMARY KEY,
);
