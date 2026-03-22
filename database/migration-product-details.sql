-- ============================================================
-- migration-product-details.sql
-- Добавление полей для детальной страницы продукта (MySQL 8.0)
-- ============================================================

USE seven_eleven_shop;

-- Добавляем столбцы (без IF NOT EXISTS — для MySQL 8.0)
ALTER TABLE products
    ADD COLUMN ingredients TEXT NULL,
    ADD COLUMN weight VARCHAR(50) NULL,
    ADD COLUMN brand VARCHAR(100) NULL,
    ADD COLUMN country VARCHAR(100) NULL,
    ADD COLUMN shelf_life VARCHAR(100) NULL,
    ADD COLUMN storage_conditions VARCHAR(255) NULL,
    ADD COLUMN calories DECIMAL(6,1) NULL,
    ADD COLUMN proteins DECIMAL(5,1) NULL,
    ADD COLUMN fats DECIMAL(5,1) NULL,
    ADD COLUMN carbs DECIMAL(5,1) NULL,
    ADD COLUMN rating DECIMAL(2,1) DEFAULT 4.5,
    ADD COLUMN reviews_count INT DEFAULT 0;

-- Обновление тестовых данных
UPDATE products SET
    calories = 42.0, proteins = 0.1, fats = 0.0, carbs = 10.5,
    ingredients = 'Water, high fructose corn syrup, citric acid, natural flavors, artificial colors',
    weight = '32 fl oz (946 ml)', brand = '7-Eleven', country = 'USA',
    shelf_life = '12 months', storage_conditions = 'Keep refrigerated',
    rating = 4.7, reviews_count = 312
WHERE name LIKE '%Slurp%Mug%';

UPDATE products SET
    calories = 45.0, proteins = 0.7, fats = 0.2, carbs = 10.4,
    ingredients = 'Orange juice (100%)',
    weight = '1 L', brand = 'Fresh', country = 'USA',
    shelf_life = '7 days', storage_conditions = 'Keep refrigerated at 2-6C',
    rating = 4.5, reviews_count = 198
WHERE name LIKE '%Orange Juice%';

UPDATE products SET
    calories = 38.0, proteins = 0.0, fats = 0.0, carbs = 9.8,
    ingredients = 'Water, high fructose corn syrup, citric acid, cherry flavoring, Red 40',
    weight = '32 fl oz', brand = '7-Eleven Slurpee', country = 'USA',
    shelf_life = '24 months', storage_conditions = 'Keep frozen',
    rating = 4.4, reviews_count = 521
WHERE name LIKE '%Slurpee%Cherry%';

UPDATE products SET
    calories = 1.0, proteins = 0.0, fats = 0.0, carbs = 0.0,
    ingredients = 'Brewed tea, water',
    weight = '18.5 fl oz (547 ml)', brand = 'Pure Leaf', country = 'USA',
    shelf_life = '18 months', storage_conditions = 'Keep in a cool dry place',
    rating = 4.6, reviews_count = 87
WHERE name LIKE '%Iced Tea%';

UPDATE products SET
    calories = 280.0, proteins = 11.0, fats = 16.0, carbs = 22.0,
    ingredients = 'Corn meal, pork, beef, water, corn syrup, salt, spices',
    weight = '2 pieces (200g)', brand = '7-Eleven', country = 'USA',
    shelf_life = 'See package date', storage_conditions = 'Keep frozen',
    rating = 4.2, reviews_count = 156
WHERE name LIKE '%Corn Dog%';

UPDATE products SET
    calories = 540.0, proteins = 7.0, fats = 32.0, carbs = 57.0,
    ingredients = 'Potatoes, vegetable oil, salt',
    weight = '8.5 oz (241g)', brand = 'Kettle Brand', country = 'USA',
    shelf_life = '6 months', storage_conditions = 'Store in cool dry place',
    rating = 4.5, reviews_count = 203
WHERE name LIKE '%Kettle Cooked%';

UPDATE products SET
    calories = 160.0, proteins = 2.0, fats = 10.0, carbs = 15.0,
    ingredients = 'Potatoes, vegetable oil, salt',
    weight = '8 oz (227g)', brand = "Lay's", country = 'USA',
    shelf_life = '6 months', storage_conditions = 'Store in cool dry place',
    rating = 4.8, reviews_count = 1024
WHERE name LIKE '%Lay%';

UPDATE products SET
    calories = 160.0, proteins = 2.0, fats = 10.0, carbs = 14.0,
    ingredients = 'Enriched corn meal, vegetable oil, whey, cheddar cheese, salt',
    weight = '2.375 oz (67g)', brand = 'Cheetos', country = 'USA',
    shelf_life = '6 months', storage_conditions = 'Store in cool dry place',
    rating = 4.6, reviews_count = 445
WHERE name LIKE '%Cheetos%';

UPDATE products SET
    calories = 390.0, proteins = 6.0, fats = 19.0, carbs = 49.0,
    ingredients = 'Enriched flour, sugar, water, soybean oil, yeast, eggs, milk, glaze',
    weight = '6 pieces (450g)', brand = 'Freshness Guaranteed', country = 'USA',
    shelf_life = '3 days', storage_conditions = 'Store at room temperature',
    rating = 4.7, reviews_count = 289
WHERE name LIKE '%Glazed Donuts%6%';

UPDATE products SET
    calories = 270.0, proteins = 12.0, fats = 13.0, carbs = 26.0,
    ingredients = 'Enriched flour, pepperoni, mozzarella, tomato sauce, water, yeast, salt',
    weight = '14" (672g)', brand = '7-Eleven', country = 'USA',
    shelf_life = 'Same day', storage_conditions = 'Serve hot',
    rating = 4.4, reviews_count = 178
WHERE name LIKE '%Pepperoni Party Pizza%';

UPDATE products SET
    calories = 180.0, proteins = 7.0, fats = 16.0, carbs = 2.0,
    ingredients = 'Pork, water, corn syrup, salt, spices, natural flavors',
    weight = '4 oz (113g)', brand = '7-Eleven', country = 'USA',
    shelf_life = 'Same day', storage_conditions = 'Keep hot, serve immediately',
    rating = 4.3, reviews_count = 94
WHERE name LIKE '%Hot Dog%';

UPDATE products SET
    calories = 43.0, proteins = 0.0, fats = 0.0, carbs = 11.0,
    ingredients = 'Carbonated water, sugar, caramel color, phosphoric acid, natural flavors, caffeine',
    weight = '12 fl oz (355ml)', brand = 'Coca-Cola', country = 'Mexico',
    shelf_life = '12 months', storage_conditions = 'Keep in a cool dry place',
    rating = 4.9, reviews_count = 832
WHERE name LIKE '%Mexican Coke%';

UPDATE products SET
    calories = 545.0, proteins = 5.0, fats = 31.0, carbs = 60.0,
    ingredients = 'Sugar, cocoa butter, chocolate, skim milk, lactose, milkfat, soy lecithin',
    weight = '1.55 oz (43.9g)', brand = 'Premium', country = 'USA',
    shelf_life = '12 months', storage_conditions = 'Store in cool dry place below 21C',
    rating = 4.6, reviews_count = 567
WHERE name LIKE '%Chocolate Bar%';

UPDATE products SET
    calories = 350.0, proteins = 6.0, fats = 14.0, carbs = 50.0,
    ingredients = 'Enriched flour, sugar, water, soybean oil, yeast, salt, glaze',
    weight = '70g', brand = '7-Select', country = 'USA',
    shelf_life = '2 days', storage_conditions = 'Store at room temperature',
    rating = 4.1, reviews_count = 63
WHERE name LIKE '%Select Glazed Donut%';

UPDATE products SET
    calories = 340.0, proteins = 6.0, fats = 0.0, carbs = 77.0,
    ingredients = 'Glucose syrup, sugar, gelatin, dextrose, citric acid, fruit juice concentrate',
    weight = '5.3 oz (150g)', brand = 'Haribo', country = 'Germany',
    shelf_life = '24 months', storage_conditions = 'Store in cool dry place',
    rating = 4.8, reviews_count = 713
WHERE name LIKE '%Haribo%';

UPDATE products SET
    weight = '2-pack', brand = 'Great Value', country = 'USA',
    shelf_life = '6 months', storage_conditions = 'Keep dry',
    rating = 4.0, reviews_count = 28
WHERE name LIKE '%Paper Towels%';

UPDATE products SET
    calories = 0.0, proteins = 0.0, fats = 0.0, carbs = 0.0,
    ingredients = 'Ethyl Alcohol 70%, Carbomer, Glycerin, Water, Fragrance',
    weight = '8 fl oz (237ml)', brand = 'CleanTech', country = 'USA',
    shelf_life = '24 months', storage_conditions = 'Store at room temperature',
    rating = 4.5, reviews_count = 145
WHERE name LIKE '%Sanitizer%';
