-- Migration: 001_create_subscription_plans
-- Description: Create subscription_plans table and seed initial data

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

INSERT IGNORE INTO `subscription_plans` (`id`, `name`, `slug`, `price`, `duration_days`, `downloads_limit`, `description`) VALUES
(1, 'Бесплатный',          'free',    0.00,    NULL, 1,    'Ограниченный доступ: 1 скачивание документа'),
(2, '1 месяц',             'monthly', 290.00,  30,   NULL, 'Полный доступ ко всем документам на 1 месяц'),
(3, '1 год',               'annual',  1490.00, 365,  NULL, 'Полный доступ ко всем документам на 1 год'),
(4, 'Пожизненный доступ',  'eternal', 1990.00, NULL, NULL, 'Безлимитный бессрочный доступ ко всем документам');
