SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

UPDATE subscription_plans SET
  name = '1 месяц',
  description = 'Полный доступ ко всем документам на 1 месяц'
WHERE slug = 'monthly';

UPDATE subscription_plans SET
  name = '1 год',
  description = 'Полный доступ ко всем документам на 1 год'
WHERE slug = 'annual';

UPDATE subscription_plans SET
  name = 'Пожизненный доступ',
  description = 'Безлимитный бессрочный доступ ко всем документам'
WHERE slug = 'eternal';

UPDATE subscription_plans SET
  name = 'Бесплатный',
  description = 'Ограниченный доступ: 1 скачивание документа'
WHERE slug = 'free';

SELECT id, name, slug, price FROM subscription_plans ORDER BY price ASC;
