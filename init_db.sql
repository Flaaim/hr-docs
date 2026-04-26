-- ============================================================
-- Полная схема БД для hr-docs
-- Запуск: docker compose exec db mysql -u slim_user -puser_pass slim_db < init_db.sql
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- Пользователи
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `email`         VARCHAR(255)    NOT NULL UNIQUE,
    `password_hash` VARCHAR(255)    NULL,
    `role`          VARCHAR(50)     NOT NULL DEFAULT 'user',
    `verified`      TINYINT(1)      NOT NULL DEFAULT 0,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Подтверждения email
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users_confirmations` (
    `id`        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`   INT UNSIGNED    NOT NULL,
    `token`     VARCHAR(255)    NOT NULL UNIQUE,
    `expires`   BIGINT          NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Сброс пароля
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users_resets` (
    `id`        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`   INT UNSIGNED    NOT NULL,
    `token`     VARCHAR(255)    NOT NULL UNIQUE,
    `expires`   BIGINT          NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Remember Me токены
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `remember_tokens` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED    NOT NULL,
    `token`      VARCHAR(255)    NOT NULL UNIQUE,
    `expires_at` DATETIME        NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Социальные аккаунты
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `social_accounts` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED    NOT NULL,
    `provider`   VARCHAR(50)     NOT NULL,
    `social_id`  VARCHAR(255)    NOT NULL,
    `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_provider_social` (`provider`, `social_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Планы подписок
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `subscription_plans` (
    `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`            VARCHAR(100)    NOT NULL,
    `slug`            VARCHAR(50)     NOT NULL UNIQUE,
    `price`           DECIMAL(10,2)   NOT NULL DEFAULT 0,
    `duration_days`   INT             NULL COMMENT 'NULL = бессрочно',
    `downloads_limit` INT             NULL COMMENT 'NULL = безлимит',
    `description`     TEXT            NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Заполнение планов подписок
INSERT IGNORE INTO `subscription_plans` (`id`, `name`, `slug`, `price`, `duration_days`, `downloads_limit`, `description`) VALUES
(1, 'Бесплатный',         'free',    0.00,   NULL, 1,    'Ограниченный доступ: 1 скачивание документа'),
(2, '1 месяц',            'monthly', 290.00, 30,   NULL, 'Полный доступ ко всем документам на 1 месяц'),
(3, '1 год',              'annual',  1490.00,365,  NULL, 'Полный доступ ко всем документам на 1 год'),
(4, 'Пожизненный доступ','eternal',  1990.00,NULL, NULL, 'Безлимитный бессрочный доступ ко всем документам');

-- Обновление цен если планы уже существуют
UPDATE `subscription_plans` SET `price` = 290.00  WHERE `slug` = 'monthly';
UPDATE `subscription_plans` SET `price` = 1490.00 WHERE `slug` = 'annual';
UPDATE `subscription_plans` SET `price` = 1990.00 WHERE `slug` = 'eternal';

-- ------------------------------------------------------------
-- Подписки пользователей
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `subscriptions` (
    `id`                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`             INT UNSIGNED    NOT NULL UNIQUE,
    `plan_id`             INT UNSIGNED    NOT NULL,
    `downloads_remaining` INT             NULL,
    `starts_at`           DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `ends_at`             DATETIME        NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Платежи
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `yookassa_id`  VARCHAR(255)    NOT NULL,
    `user_id`      INT UNSIGNED    NOT NULL,
    `payment_id`   VARCHAR(255)    NOT NULL UNIQUE,
    `plan_slug`    VARCHAR(50)     NOT NULL,
    `amount`       DECIMAL(10,2)   NOT NULL,
    `currency`     VARCHAR(10)     NOT NULL DEFAULT 'RUB',
    `status`       VARCHAR(50)     NOT NULL DEFAULT 'pending',
    `description`  VARCHAR(255)    NULL,
    `metadata`     JSON            NULL,
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Направления (Certification areas)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `directions` (
    `id`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Разделы
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sections` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`         VARCHAR(255) NOT NULL,
    `direction_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`direction_id`) REFERENCES `directions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Типы документов
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `types` (
    `id`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Документы
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `documents` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `stored_name` VARCHAR(255)    NOT NULL UNIQUE,
    `title`       VARCHAR(500)    NOT NULL,
    `section_id`  INT UNSIGNED    NULL,
    `type_id`     INT UNSIGNED    NULL,
    `updated`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`section_id`) REFERENCES `sections`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`type_id`)    REFERENCES `types`(`id`)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Результат
SELECT id, name, slug, price, duration_days, downloads_limit FROM subscription_plans ORDER BY price ASC;
