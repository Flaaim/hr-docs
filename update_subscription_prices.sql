-- Обновление цен планов подписки
-- 1 месяц: 290 руб, 1 год: 1490 руб, Пожизненный: 1990 руб

UPDATE subscription_plans SET price = 290  WHERE slug = 'monthly';
UPDATE subscription_plans SET price = 1490 WHERE slug = 'annual';
UPDATE subscription_plans SET price = 1990 WHERE slug = 'eternal';

-- Проверка результата
SELECT id, name, slug, price FROM subscription_plans ORDER BY price ASC;
