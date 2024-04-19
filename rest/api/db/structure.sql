CREATE DATABASE dsy_project_vroj;

CREATE TABLE users (
    id INT NOT NULL PRIMARY KEY,
    first_name VARCHAR(255) UNIQUE NOT NULL,
    last_name VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(1023) NOT NULL
);

CREATE TABLE roles (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) UNIQUE NOT NULL,
    description VARCHAR(255)
);

CREATE TABLE user_with_role (
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (role_id) REFERENCES roles(id),
    PRIMARY KEY (user_id, role_id)
);

CREATE TABLE session (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    access_token VARCHAR(36) NOT NULL UNIQUE,
    refresh_token VARCHAR(36) NOT NULL UNIQUE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE shop_order (
    id VARCHAR(36) NOT NULL PRIMARY KEY,
    name VARCHAR(31) NOT NULL UNIQUE,
    created_by INT NOT NULL,
    created_for INT NOT NULL,
    date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    finish_date TIMESTAMP NOT NULL,
    status BOOLEAN NOT NULL,
    description VARCHAR(255),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (created_for) REFERENCES users(id)
);

DELIMITER $$
CREATE TRIGGER `generate_user_id` 
BEFORE INSERT ON `users` FOR EACH ROW 
BEGIN
    SET new.id = uuid();
END $$

DELIMITER $$
CREATE TRIGGER `generate_access_token` 
BEFORE INSERT ON `session` FOR EACH ROW 
BEGIN
    SET new.access_token = uuid();
    SET new.refresh_token = SHA2(uuid(), 256);
END $$

DELIMITER $$
CREATE TRIGGER `generate_order_id` 
BEFORE INSERT ON `shop_order` FOR EACH ROW 
BEGIN
    SET new.id = uuid();
END $$

CREATE TABLE images (
    id VARCHAR(36) NOT NULL PRIMARY KEY,
    data MEDIUMBLOB NOT NULL,
    type VARCHAR(5) NOT NULL,
    order_id VARCHAR(36) NOT NULL
);

DELIMITER $$
CREATE TRIGGER `generate_image_id` 
BEFORE INSERT ON `images` FOR EACH ROW 
BEGIN
    SET new.id = uuid();
END $$

CREATE TABLE order_states (
    id VARCHAR(36) NOT NULL PRIMARY KEY,
    order_id INT NOT NULL,
    message VARCHAR(255) NOT NULL
);

DELIMITER $$
CREATE TRIGGER `generate_order_status_id` 
BEFORE INSERT ON `order_states` FOR EACH ROW 
BEGIN
    SET new.id = uuid();
END $$
