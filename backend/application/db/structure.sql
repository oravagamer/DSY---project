CREATE DATABASE dsy_project_vroj;

USE dsy_project_vroj;

CREATE TABLE users
(
    id         VARCHAR(36)   NOT NULL PRIMARY KEY DEFAULT UUID(),
    first_name VARCHAR(255)  NOT NULL,
    last_name  VARCHAR(255)  NOT NULL,
    username   VARCHAR(255)  NOT NULL UNIQUE,
    email      VARCHAR(255)  NOT NULL,
    password   VARCHAR(1023) NOT NULL,
    active     BOOLEAN       NOT NULL             DEFAULT FALSE
);

CREATE TABLE sessions
(
    id      VARCHAR(36)  NOT NULL PRIMARY KEY DEFAULT UUID(),
    action  VARCHAR(255) NOT NULL,
    used    BOOLEAN      NOT NULL             DEFAULT FALSE,
    params  VARCHAR(1024),
    user_id VARCHAR(36),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

CREATE TABLE roles
(
    id          VARCHAR(36)  NOT NULL PRIMARY KEY DEFAULT UUID(),
    name        VARCHAR(255) NOT NULL UNIQUE,
    level       BIT(8)       NOT NULL             DEFAULT 255,
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

CREATE TABLE acc_tokens
(
    id            VARCHAR(36)   NOT NULL PRIMARY KEY DEFAULT UUID(),
    user_id       VARCHAR(36)   NOT NULL,
    secret_key    VARCHAR(2048) NOT NULL,
    is_terminated BOOLEAN       NOT NULL             DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

CREATE TABLE ref_tokens
(
    id            VARCHAR(36)   NOT NULL PRIMARY KEY DEFAULT UUID(),
    user_id       VARCHAR(36)   NOT NULL,
    secret_key    VARCHAR(2048) NOT NULL,
    is_terminated BOOLEAN       NOT NULL             DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

CREATE TABLE shop_order
(
    id           VARCHAR(36)   NOT NULL PRIMARY KEY DEFAULT UUID(),
    name         VARCHAR(256)  NOT NULL UNIQUE,
    created_by   VARCHAR(36),
    created_for  VARCHAR(36),
    date_created TIMESTAMP     NOT NULL             DEFAULT CURRENT_TIMESTAMP(),
    finish_date  TIMESTAMP     NOT NULL             DEFAULT CURRENT_TIMESTAMP(),
    status       BOOLEAN,
    description  VARCHAR(1024) NOT NULL             DEFAULT '',
    FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL,
    FOREIGN KEY (created_for) REFERENCES users (id) ON DELETE SET NULL
);

CREATE TABLE images
(
    id       VARCHAR(36) NOT NULL PRIMARY KEY DEFAULT UUID(),
    data     MEDIUMBLOB  NOT NULL,
    type     VARCHAR(5)  NOT NULL,
    order_id VARCHAR(36) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES shop_order (id) ON DELETE CASCADE
);

CREATE TABLE order_states
(
    id       VARCHAR(36)  NOT NULL PRIMARY KEY DEFAULT UUID(),
    order_id VARCHAR(36)  NOT NULL,
    message  VARCHAR(255) NOT NULL,
    time     TIMESTAMP    NOT NULL             DEFAULT CURRENT_TIMESTAMP(),
    FOREIGN KEY (order_id) REFERENCES shop_order (id) ON DELETE CASCADE
);

INSERT INTO roles(name, description, level)
VALUES ('default', 'Default role', 255),
       ('admin', 'Admin role', 0);

DELIMITER $$
CREATE TRIGGER `assign_def_role`
    AFTER INSERT
    ON `users`
    FOR EACH ROW
BEGIN
    INSERT INTO user_with_role(user_id, role_id) VALUE (NEW.id, (SELECT id FROM roles WHERE roles.name = 'Default'));
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
    ELSEIF (NEW.status = OLD.status) THEN
        INSERT INTO order_states(order_id, message) VALUE (NEW.id, 'Order information change.');
    END IF;
END
$$

DELIMITER $$

CREATE PROCEDURE create_order(
    IN v_user_id VARCHAR(36),
    IN v_time_end TIMESTAMP,
    IN v_name VARCHAR(256),
    IN v_description VARCHAR(1024),
    IN v_created_for VARCHAR(36)
)
BEGIN
    SET @order_id = UUID();

    INSERT INTO shop_order(id, name, created_by, finish_date, description, created_for) VALUE (@order_id, v_name,
                                                                                               v_user_id, v_time_end,
                                                                                               v_description,
                                                                                               v_created_for);

    SELECT @order_id AS id;

END $$