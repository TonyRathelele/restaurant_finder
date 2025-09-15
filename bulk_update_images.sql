-- Bulk Image Update Script
-- Use this to update multiple images at once

USE restaurant_finder;

-- Update Restaurant Images
-- Replace the URLs below with your desired image URLs

-- Example: Update all restaurant images to new Unsplash URLs
UPDATE restaurants SET image_url = 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80' WHERE id = 1;
UPDATE restaurants SET image_url = 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-4.0.3&auto=format&fit=crop&w=2074&q=80' WHERE id = 2;
UPDATE restaurants SET image_url = 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80' WHERE id = 3;
UPDATE restaurants SET image_url = 'https://images.unsplash.com/photo-1551218808-94e220e084d2?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80' WHERE id = 4;
UPDATE restaurants SET image_url = 'https://images.unsplash.com/photo-1559339352-11d035aa65de?ixlib=rb-4.0.3&auto=format&fit=crop&w=2074&q=80' WHERE id = 5;
UPDATE restaurants SET image_url = 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80' WHERE id = 6;

-- Update Menu Item Images
-- Replace the URLs below with your desired food image URLs

-- Example: Update menu item images to new food images
UPDATE menu_items SET image_url = 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80' WHERE id = 1;
UPDATE menu_items SET image_url = 'https://images.unsplash.com/photo-1551218808-94e220e084d2?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80' WHERE id = 2;
UPDATE menu_items SET image_url = 'https://images.unsplash.com/photo-1559339352-11d035aa65de?ixlib=rb-4.0.3&auto=format&fit=crop&w=2074&q=80' WHERE id = 3;
UPDATE menu_items SET image_url = 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-4.0.3&auto=format&fit=crop&w=2074&q=80' WHERE id = 4;
UPDATE menu_items SET image_url = 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80' WHERE id = 5;
UPDATE menu_items SET image_url = 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80' WHERE id = 6;

-- Clear all images (set to NULL)
-- Uncomment the lines below if you want to remove all images
-- UPDATE restaurants SET image_url = NULL;
-- UPDATE menu_items SET image_url = NULL;

-- View current images
SELECT 'RESTAURANTS' as table_name, id, name, image_url FROM restaurants WHERE image_url IS NOT NULL
UNION ALL
SELECT 'MENU_ITEMS' as table_name, id, name, image_url FROM menu_items WHERE image_url IS NOT NULL
ORDER BY table_name, id;
