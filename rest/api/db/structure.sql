CREATE DATABASE dsy_project_vroj;

USE dsy_project_vroj;

CREATE TABLE users
(
    id         VARCHAR(36)         NOT NULL PRIMARY KEY DEFAULT UUID(),
    first_name VARCHAR(255)        NOT NULL,
    last_name  VARCHAR(255)        NOT NULL,
    username   VARCHAR(255)        NOT NULL UNIQUE,
    email      VARCHAR(255) UNIQUE NOT NULL,
    password   VARCHAR(1023)       NOT NULL
);

CREATE TABLE roles
(
    id          VARCHAR(36)         NOT NULL PRIMARY KEY DEFAULT UUID(),
    name        VARCHAR(255) UNIQUE NOT NULL,
    description VARCHAR(255)
);

CREATE TABLE user_with_role
(
    user_id VARCHAR(36) NOT NULL,
    role_id VARCHAR(36) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, role_id)
);

CREATE TABLE session
(
    id            INT          NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id       VARCHAR(36)  NOT NULL,
    access_token  VARCHAR(36)  NOT NULL UNIQUE,
    refresh_token VARCHAR(40) NOT NULL UNIQUE,
    status        BOOLEAN      NOT NULL DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users (id)  ON DELETE CASCADE
);

CREATE TABLE shop_order
(
    id           VARCHAR(36) NOT NULL PRIMARY KEY DEFAULT UUID(),
    name         VARCHAR(31) NOT NULL UNIQUE,
    created_by   VARCHAR(36) NOT NULL,
    created_for  VARCHAR(36) NOT NULL,
    date_created TIMESTAMP   NOT NULL             DEFAULT CURRENT_TIMESTAMP(),
    finish_date  TIMESTAMP   NOT NULL             DEFAULT CURRENT_TIMESTAMP(),
    status       BOOLEAN,
    description  VARCHAR(255),
    FOREIGN KEY (created_by) REFERENCES users (id)  ON DELETE CASCADE,
    FOREIGN KEY (created_for) REFERENCES users (id)  ON DELETE CASCADE
);

CREATE TABLE images
(
    id       VARCHAR(36) NOT NULL PRIMARY KEY DEFAULT UUID(),
    data     MEDIUMBLOB  NOT NULL,
    type     VARCHAR(5)  NOT NULL,
    order_id VARCHAR(36) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES shop_order (id)  ON DELETE CASCADE
);

CREATE TABLE order_states
(
    id       VARCHAR(36)  NOT NULL PRIMARY KEY DEFAULT UUID(),
    order_id VARCHAR(36)  NOT NULL,
    message  VARCHAR(255) NOT NULL,
    time     TIMESTAMP    NOT NULL             DEFAULT CURRENT_TIMESTAMP(),
    FOREIGN KEY (order_id) REFERENCES shop_order (id)  ON DELETE CASCADE
);

INSERT INTO roles(name, description) VALUE ('Default', 'Default role');

DELIMITER $$
CREATE TRIGGER `assign_def_role`
    AFTER INSERT
    ON `users`
    FOR EACH ROW
BEGIN
    INSERT INTO user_with_role(user_id, role_id) VALUE (NEW.id, (SELECT id FROM roles WHERE roles.name = 'Default'));
END $$

DELIMITER $$
CREATE TRIGGER `generate_access_token`
    BEFORE INSERT
    ON `session`
    FOR EACH ROW
BEGIN
    SET new.access_token = uuid();
    SET new.refresh_token = SHA1(uuid());
END $$

DELIMITER $$
CREATE TRIGGER `order_state_insert`
    AFTER INSERT
    ON `shop_order`
    FOR EACH ROW
BEGIN
    INSERT INTO order_states(order_id, message) VALUE (NEW.id, 'Order created.');
END
$$

DELIMITER $$
CREATE TRIGGER `order_state_changes`
    BEFORE UPDATE
    ON `shop_order`
    FOR EACH ROW
BEGIN
    IF (NEW.status = 0 AND OLD.status IS NULL) THEN
        INSERT INTO order_states(order_id, message) VALUE (NEW.id, 'Order in process.');
    ELSEIF (NEW.status = 1) THEN
        INSERT INTO order_states(order_id, message) VALUE (NEW.id, 'Order finished.');
    ELSEIF (NEW.status IS NULL AND OLD.status = 0) THEN
        INSERT INTO order_states(order_id, message) VALUE (NEW.id, 'Order not in process.');
    ELSEIF (NEW.status = 0 AND OLD.status = 1) THEN
        INSERT INTO order_states(order_id, message) VALUE (NEW.id, 'Order back in process.');
    ELSEIF (NEW.status IS NULL AND OLD.status = 1) THEN
        INSERT INTO order_states(order_id, message) VALUE (NEW.id, 'Order refresh.');
    END IF;
END
$$

DELIMITER $$

CREATE PROCEDURE make_session(
    IN v_user_id VARCHAR(36)
)
BEGIN
    DECLARE session_id INT UNSIGNED DEFAULT 0;

    INSERT INTO session(user_id) VALUE (v_user_id);

    SET session_id = LAST_INSERT_ID();

    select access_token, refresh_token from session where id = session_id;

END $$