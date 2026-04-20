-- Миграция: добавление статуса оплаты к заказам
-- Запустить: docker exec -i seven-db mysql -u root -p < database/migration-orders.sql

USE seven_eleven_shop;

-- Добавляем колонку payment_status
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS payment_status ENUM('unpaid', 'paid', 'failed') DEFAULT 'unpaid' AFTER payment_type;
