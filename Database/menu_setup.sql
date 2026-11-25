CREATE TABLE IF NOT EXISTS menuitem (
    menuItemID VARCHAR(10) NOT NULL,
    foodName VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(100) NOT NULL,
    image VARCHAR(255) NOT NULL DEFAULT '',
    isPopular TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (menuItemID),
    INDEX idx_menuitem_category (category),
    INDEX idx_menuitem_popular (isPopular)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES
    ('M0001', 'Sizzling Yee Mee',
     'A fragrant blend of herbs and spices, featuring tender meat and vibrant vegetables in harmony.',
     16.00, 'Noodle Soups', 'cbf924a1d64b90dfd2f3a82e946f9230.jpg', 0),
    ('M0002', 'Hokkien Mee',
     'Stir-fired noodles with seafood, vegetables, and a savory sauce, delivering a delightful umami experience',
     15.00, 'Noodle Soups', 'dd071a29eebe25490b9fd638145eac3b.jpg', 0)
ON DUPLICATE KEY UPDATE
    foodName = VALUES(foodName),
    description = VALUES(description),
    price = VALUES(price),
    category = VALUES(category),
    image = VALUES(image),
    isPopular = VALUES(isPopular);

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0003', 'Fried Fries', 'Crispy, golden fries, perfectly seasoned and served with a side of zesty dipping sauce.', 10.50, 'Sides', '3052c4f115401feee44dd6196d59ef3b.jpg', 0)
ON DUPLICATE KEY UPDATE foodName = 'Fried Fries', description = 'Crispy, golden fries, perfectly seasoned and served with a side of zesty dipping sauce.', price = 10.50, category = 'Sides', image = '3052c4f115401feee44dd6196d59ef3b.jpg', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0004', 'Nyonya Cili Garam Prawn Rice', 'Savor succulent prawns in a spicy Nyonya chili sauce, served with fragrant jasmine rice.', 20.00, 'Nyonya Series', 'b8be4739f3ae739b87129e1c1226440e.jpg', 0)
ON DUPLICATE KEY UPDATE foodName = 'Nyonya Cili Garam Prawn Rice', description = 'Savor succulent prawns in a spicy Nyonya chili sauce, served with fragrant jasmine rice.', price = 20.00, category = 'Nyonya Series', image = 'b8be4739f3ae739b87129e1c1226440e.jpg', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0002', 'Hokkien Mee', 'Stir-fired noodles with seafood, vegetables, and a savory sauce, delivering a delightful umami experience.', 15.00, 'Noodle Soups', 'dd071a29eebe25490b9fd638145eac3b.jpg', 0)
ON DUPLICATE KEY UPDATE foodName = 'Hokkien Mee', description = 'Stir-fired noodles with seafood, vegetables, and a savory sauce, delivering a delightful umami experience.', price = 15.00, category = 'Noodle Soups', image = 'dd071a29eebe25490b9fd638145eac3b.jpg', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0005', 'Chicken Hotdog Set', 'Juicy chicken hotdog served with crispy fries and a refreshing drink for a perfect meal.', 12.50, 'Sets', '9f74b77d76076d31e2b03290259a152d.jpg', 0)
ON DUPLICATE KEY UPDATE foodName = 'Chicken Hotdog Set', description = 'Juicy chicken hotdog served with crispy fries and a refreshing drink for a perfect meal.', price = 12.50, category = 'Sets', image = '9f74b77d76076d31e2b03290259a152d.jpg', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0006', 'Chocolate Shake', 'Indulge in a creamy, rich chocolate shake that satisfies your sweet cravings and provides a delightful treat.', 9.90, 'Bubble Milk Tea', '3bc7e5d7ea583abf0476d2990de467ff.jpg', 0)
ON DUPLICATE KEY UPDATE foodName = 'Chocolate Shake', description = 'Indulge in a creamy, rich chocolate shake that satisfies your sweet cravings and provides a delightful treat.', price = 9.90, category = 'Bubble Milk Tea', image = '3bc7e5d7ea583abf0476d2990de467ff.jpg', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0007', 'Jiang Nan Noodle', 'Savor our hand-pulled noodles in rich broth, topped with fresh vegetables and tender chicken.', 17.50, 'Noodle Soups', 'f566d2192567afa61a66565fb9a93f96.jpg', 0)
ON DUPLICATE KEY UPDATE foodName = 'Jiang Nan Noodle', description = 'Savor our hand-pulled noodles in rich broth, topped with fresh vegetables and tender chicken.', price = 17.50, category = 'Noodle Soups', image = 'f566d2192567afa61a66565fb9a93f96.jpg', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0008', 'Fish Mihun Soup', 'A fragrant noodle soup with tender fish, fresh herbs, and a rich, savory broth.', 17.50, 'Noodle Soups', 'c5defecb172e99f2dcba4803cddf686f.jpg', 0)
ON DUPLICATE KEY UPDATE foodName = 'Fish Mihun Soup', description = 'A fragrant noodle soup with tender fish, fresh herbs, and a rich, savory broth.', price = 17.50, category = 'Noodle Soups', image = 'c5defecb172e99f2dcba4803cddf686f.jpg', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0009', 'Zhi Hun Gen', 'A fragrant blend of herbs and spices, featuring tender meat and vibrant vegetables inharmony.', 16.00, 'Noodle Soups', '58e9e2a871cb91bd59fea2c7b551858e.jpg', 0)
ON DUPLICATE KEY UPDATE foodName = 'Zhi Hun Gen', description = 'A fragrant blend of herbs and spices, featuring tender meat and vibrant vegetables inharmony.', price = 16.00, category = 'Noodle Soups', image = '58e9e2a871cb91bd59fea2c7b551858e.jpg', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0001', 'Sizzling Yee Mee', 'Wok-fried noodles with fresh vegetables, succulent seafood, and a savory sauce, served sizzling hot.', 16.00, 'Noodle Soups', 'cbf924a1d64b90dfd2f3a82e946f9230.jpg', 0)
ON DUPLICATE KEY UPDATE foodName = 'Sizzling Yee Mee', description = 'Wok-fried noodles with fresh vegetables, succulent seafood, and a savory sauce, served sizzling hot.', price = 16.00, category = 'Noodle Soups', image = 'cbf924a1d64b90dfd2f3a82e946f9230.jpg', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0010', 'Mango', 'A refreshing blend of ripe mangoes and creamy goodness, perfect for a tropical escape.', 9.90, 'Bubble Milk Tea', '2e500326e08ef5523c79672bc9f8d0ba.jpg', 0)
ON DUPLICATE KEY UPDATE foodName = 'Mango', description = 'A refreshing blend of ripe mangoes and creamy goodness, perfect for a tropical escape.', price = 9.90, category = 'Bubble Milk Tea', image = '2e500326e08ef5523c79672bc9f8d0ba.jpg', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0011', 'Claypot Udon Mee Soup', 'Savory udon noodles in a rich broth, topped with fresh vegetables and tender chicken.', 17.50, 'Noodle Soups', '32dec9fa7b415c434ab9965af941b554.jpg', 0)
ON DUPLICATE KEY UPDATE foodName = 'Claypot Udon Mee Soup', description = 'Savory udon noodles in a rich broth, topped with fresh vegetables and tender chicken.', price = 17.50, category = 'Noodle Soups', image = '32dec9fa7b415c434ab9965af941b554.jpg', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0012', 'Blended Yam', 'Creamy blended yams infused with spices, served with a drizzle of rich, savory sauce.', 9.90, 'Bubble Milk Tea', '16723953ada3d77cd6dc5169bee97c51.jpg', 0)
ON DUPLICATE KEY UPDATE foodName = 'Blended Yam', description = 'Creamy blended yams infused with spices, served with a drizzle of rich, savory sauce.', price = 9.90, category = 'Bubble Milk Tea', image = '16723953ada3d77cd6dc5169bee97c51.jpg', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0013', 'Blended Strawberry', 'Refreshing blend of ripe strawberries, creamy yogurt, and a hint of sweetness for a delightful treat.', 9.90, 'Bubble Milk Tea', '608d39fc19ba8202a60b0dae844062e8.jpg', 0)
ON DUPLICATE KEY UPDATE foodName = 'Blended Strawberry', description = 'Refreshing blend of ripe strawberries, creamy yogurt, and a hint of sweetness for a delightful treat.', price = 9.90, category = 'Bubble Milk Tea', image = '608d39fc19ba8202a60b0dae844062e8.jpg', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0014', 'Blended Chocolate', 'Indulge in a rich, velvety blend of premium chocolates, creating a decadent dessert experience.', 9.90, 'Bubble Milk Tea', '73c601e1a878eb97960fdb3829c35169.jpg', 0)
ON DUPLICATE KEY UPDATE foodName = 'Blended Chocolate', description = 'Indulge in a rich, velvety blend of premium chocolates, creating a decadent dessert experience.', price = 9.90, category = 'Bubble Milk Tea', image = '73c601e1a878eb97960fdb3829c35169.jpg', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0015', 'Apple Shake', 'Creamy shake with fresh apple for a refreshing and sweet treat.', 9.90, 'Bubble Milk Tea', 'e6b9206bcff410d6438fee78a12287bf.jpg', 0)
ON DUPLICATE KEY UPDATE foodName = 'Apple Shake', description = 'Creamy shake with fresh apple for a refreshing and sweet treat.', price = 9.90, category = 'Bubble Milk Tea', image = 'e6b9206bcff410d6438fee78a12287bf.jpg', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0016', 'Seafood Spaghetti', 'Pasta tossed with fresh, flavorful seafood in a light sauce', 21.80, 'Spaghetti', '969647889b5ad807491377973f4d9705.jpg', 0)
ON DUPLICATE KEY UPDATE foodName = 'Seafood Spaghetti', description = 'Pasta tossed with fresh, flavorful seafood in a light sauce', price = 21.80, category = 'Spaghetti', image = '969647889b5ad807491377973f4d9705.jpg', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0016', 'Seafood Spaghetti', 'Pasta tossed with fresh, flavorful seafood in a light sauce', 21.80, 'Spaghetti', '5e654c335ff9130d95fedcf01ecf9eeb.jpg', 0)
ON DUPLICATE KEY UPDATE foodName = 'Seafood Spaghetti', description = 'Pasta tossed with fresh, flavorful seafood in a light sauce', price = 21.80, category = 'Spaghetti', image = '5e654c335ff9130d95fedcf01ecf9eeb.jpg', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0017', 'Mushroom Spaghetti', 'Earthy mushrooms tossed in al dente pasta with a light sauce.', 16.50, 'Spaghetti', '725e01e9fcccec1106a78eee8abc0499.jpg', 0)
ON DUPLICATE KEY UPDATE foodName = 'Mushroom Spaghetti', description = 'Earthy mushrooms tossed in al dente pasta with a light sauce.', price = 16.50, category = 'Spaghetti', image = '725e01e9fcccec1106a78eee8abc0499.jpg', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0018', 'Tomato Spaghetti', 'Savory spaghetti tossed in a rich tomato sauce, garnished with fresh basil and Parmesan cheese.', 16.50, 'Spaghetti', 'cbdc40f250d962f60ae7fc50cbef2b14.jpg', 0)
ON DUPLICATE KEY UPDATE foodName = 'Tomato Spaghetti', description = 'Savory spaghetti tossed in a rich tomato sauce, garnished with fresh basil and Parmesan cheese.', price = 16.50, category = 'Spaghetti', image = 'cbdc40f250d962f60ae7fc50cbef2b14.jpg', isPopular = 0;

-- Menu item deleted
DELETE FROM menuitem WHERE menuItemID = 'M0005';

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0019', 'Fried Sotong', 'Crispy fried squid served with a zesty dipping sauce and  fresh herbs for a delightful crunch.', 19.00, 'Special Items', '1e9e04e61650ddb10f864a4b2ea35530.jpg', 0)
ON DUPLICATE KEY UPDATE foodName = 'Fried Sotong', description = 'Crispy fried squid served with a zesty dipping sauce and  fresh herbs for a delightful crunch.', price = 19.00, category = 'Special Items', image = '1e9e04e61650ddb10f864a4b2ea35530.jpg', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0018', 'Tomato Spaghetti', 'Savory spaghetti tossed in a rich tomato sauce, garnished with fresh basil and Parmesan cheese.', 16.50, 'Spaghetti', 'cbdc40f250d962f60ae7fc50cbef2b14.jpg', 1)
ON DUPLICATE KEY UPDATE foodName = 'Tomato Spaghetti', description = 'Savory spaghetti tossed in a rich tomato sauce, garnished with fresh basil and Parmesan cheese.', price = 16.50, category = 'Spaghetti', image = 'cbdc40f250d962f60ae7fc50cbef2b14.jpg', isPopular = 1;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0020', 'Fried Prawn', 'Crispy and savory fried prawns, perfect for a delicious appetizer or snack.', 19.00, 'Special Items', 'bf059b5d9dc06509d2f6a90b690064b3.png', 0)
ON DUPLICATE KEY UPDATE foodName = 'Fried Prawn', description = 'Crispy and savory fried prawns, perfect for a delicious appetizer or snack.', price = 19.00, category = 'Special Items', image = 'bf059b5d9dc06509d2f6a90b690064b3.png', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0021', 'Fried Vegetable Popiah', 'Crispy spring rolls filled with fresh vegetables, served with a tangy dipping sauce.', 11.70, 'Special Items', '263c755a8d56e19f409bd1de0d49f1b9.png', 0)
ON DUPLICATE KEY UPDATE foodName = 'Fried Vegetable Popiah', description = 'Crispy spring rolls filled with fresh vegetables, served with a tangy dipping sauce.', price = 11.70, category = 'Special Items', image = '263c755a8d56e19f409bd1de0d49f1b9.png', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0022', 'Nyonya Cili Garam Fish Fillet Rice', 'Spicy Nyonya fish fillet served with fragrant rice and a zesty chili sauce.', 18.00, 'Nyonya Series', '916f51fb60a6d279011c63259b13639c.jpg', 0)
ON DUPLICATE KEY UPDATE foodName = 'Nyonya Cili Garam Fish Fillet Rice', description = 'Spicy Nyonya fish fillet served with fragrant rice and a zesty chili sauce.', price = 18.00, category = 'Nyonya Series', image = '916f51fb60a6d279011c63259b13639c.jpg', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0023', 'Nyonya Cili Garam Chicken Rice', 'Savory Nyonya-style chicken served with fragrant rice and a spicy chili-garam sauce', 18.00, 'Nyonya Series', 'ba30342e00104c1efaa28901c8119416.jpg', 0)
ON DUPLICATE KEY UPDATE foodName = 'Nyonya Cili Garam Chicken Rice', description = 'Savory Nyonya-style chicken served with fragrant rice and a spicy chili-garam sauce', price = 18.00, category = 'Nyonya Series', image = 'ba30342e00104c1efaa28901c8119416.jpg', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0024', 'Mushroom Chicken Chop', 'Juicy chicken chop topped with savory mushroom sauce, served with seasonal vegetables and garlic mashed potatoes.', 26.00, 'Western Food', '846c7ce89c808b53f0983f369c309955.jpg', 1)
ON DUPLICATE KEY UPDATE foodName = 'Mushroom Chicken Chop', description = 'Juicy chicken chop topped with savory mushroom sauce, served with seasonal vegetables and garlic mashed potatoes.', price = 26.00, category = 'Western Food', image = '846c7ce89c808b53f0983f369c309955.jpg', isPopular = 1;

-- Menu item deleted
DELETE FROM menuitem WHERE menuItemID = 'M0003';

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0025', 'Longan Milk', 'Creamy longan milk blended with sweet coconut and a hint of vanilla for a refreshing treat.', 11.30, 'Beverages', '6abbf4770ea68b77647a09f070564c76.webp', 0)
ON DUPLICATE KEY UPDATE foodName = 'Longan Milk', description = 'Creamy longan milk blended with sweet coconut and a hint of vanilla for a refreshing treat.', price = 11.30, category = 'Beverages', image = '6abbf4770ea68b77647a09f070564c76.webp', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0026', 'Mango Smoothie', 'A refreshing blend of ripe mangoes, creamy yogurt, and a hint of honey.', 15.70, 'Beverages', 'c14df07b0ef67307d3ba6143da01045f.webp', 0)
ON DUPLICATE KEY UPDATE foodName = 'Mango Smoothie', description = 'A refreshing blend of ripe mangoes, creamy yogurt, and a hint of honey.', price = 15.70, category = 'Beverages', image = 'c14df07b0ef67307d3ba6143da01045f.webp', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0027', 'Cappuccino', 'Rich espresso blended with velvety steamed milk, topped with a delicate foam and cocoa dust.', 9.00, 'Beverages', '229db0c01a362c8f31ffab24e6ad18ec.webp', 0)
ON DUPLICATE KEY UPDATE foodName = 'Cappuccino', description = 'Rich espresso blended with velvety steamed milk, topped with a delicate foam and cocoa dust.', price = 9.00, category = 'Beverages', image = '229db0c01a362c8f31ffab24e6ad18ec.webp', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0028', 'Grass Jelly & Lime Tea', 'Refreshing grass jelly infused with zesty lime, served chilled for a delightful, revitalizing experience.', 9.90, 'Beverages', 'e6d405d8475668b3430a521182bf631b.webp', 0)
ON DUPLICATE KEY UPDATE foodName = 'Grass Jelly & Lime Tea', description = 'Refreshing grass jelly infused with zesty lime, served chilled for a delightful, revitalizing experience.', price = 9.90, category = 'Beverages', image = 'e6d405d8475668b3430a521182bf631b.webp', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0029', 'Sea Coconut Longan', 'A refreshing blend of sea coconut and longan, served chilled for a delightful tropical treat', 11.50, 'Beverages', '2907ec624cab6f4c4af69fd9e6bc0acb.webp', 1)
ON DUPLICATE KEY UPDATE foodName = 'Sea Coconut Longan', description = 'A refreshing blend of sea coconut and longan, served chilled for a delightful tropical treat', price = 11.50, category = 'Beverages', image = '2907ec624cab6f4c4af69fd9e6bc0acb.webp', isPopular = 1;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0030', 'Lamb Chop', 'Succulent lamb chops grilled to perfection, served with a savory herb sauce and seasonal vegetables.', 38.00, 'Western Food', 'ba1b8d4809ad853f9ff9fb180bd29352.webp', 1)
ON DUPLICATE KEY UPDATE foodName = 'Lamb Chop', description = 'Succulent lamb chops grilled to perfection, served with a savory herb sauce and seasonal vegetables.', price = 38.00, category = 'Western Food', image = 'ba1b8d4809ad853f9ff9fb180bd29352.webp', isPopular = 1;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0031', 'Black Pepper Chicken Chop', 'Juicy chicken chop seasoned with aromatic black pepper, served with a side of vibrant vegetables.', 26.00, 'Western Food', 'bddb28fdd2539150ceed21df32eef207.webp', 0)
ON DUPLICATE KEY UPDATE foodName = 'Black Pepper Chicken Chop', description = 'Juicy chicken chop seasoned with aromatic black pepper, served with a side of vibrant vegetables.', price = 26.00, category = 'Western Food', image = 'bddb28fdd2539150ceed21df32eef207.webp', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0032', 'Dried Chili Chicken Rice', 'Savory dried chili chicken atop a bed of aromatic rice, delivering a perfect balance of heat and flavor in every bite.', 16.00, 'Rice', '4f8b01ec1a1454c3c4df526ddc1d4df0.webp', 0)
ON DUPLICATE KEY UPDATE foodName = 'Dried Chili Chicken Rice', description = 'Savory dried chili chicken atop a bed of aromatic rice, delivering a perfect balance of heat and flavor in every bite.', price = 16.00, category = 'Rice', image = '4f8b01ec1a1454c3c4df526ddc1d4df0.webp', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0033', 'JInxiang Chicken Rice', 'A savory dish featuring tender Jinxiang chicken served over a bed of perfectly cooked rice.', 16.00, 'Rice', '074baa411539c46b9108de0b1ca1c5c5.webp', 0)
ON DUPLICATE KEY UPDATE foodName = 'JInxiang Chicken Rice', description = 'A savory dish featuring tender Jinxiang chicken served over a bed of perfectly cooked rice.', price = 16.00, category = 'Rice', image = '074baa411539c46b9108de0b1ca1c5c5.webp', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0034', 'Hometown Fried Rice', 'Savory fried rice stir-fried with fresh vegetables, eggs, and your choice of protein.', 14.50, 'Rice', '7904fb180ed09d0581e205f763e8f33f.webp', 0)
ON DUPLICATE KEY UPDATE foodName = 'Hometown Fried Rice', description = 'Savory fried rice stir-fried with fresh vegetables, eggs, and your choice of protein.', price = 14.50, category = 'Rice', image = '7904fb180ed09d0581e205f763e8f33f.webp', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0035', 'Thai Style Chicken Rice', 'Savory Thai chicken served over fragrant jasmine rice, accompanied by fresh herbs and zesty sauce.', 16.00, 'Rice', '79167a269116cadea26ff0e3e980480a.webp', 0)
ON DUPLICATE KEY UPDATE foodName = 'Thai Style Chicken Rice', description = 'Savory Thai chicken served over fragrant jasmine rice, accompanied by fresh herbs and zesty sauce.', price = 16.00, category = 'Rice', image = '79167a269116cadea26ff0e3e980480a.webp', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0036', 'Martine Chicken Rice', 'Savory chicken served over fragrant rice, accompanied by zesty sauces and fresh vegetables.', 16.00, 'Rice', 'f85102a375e0f4837e5fef712bf99e23.webp', 0)
ON DUPLICATE KEY UPDATE foodName = 'Martine Chicken Rice', description = 'Savory chicken served over fragrant rice, accompanied by zesty sauces and fresh vegetables.', price = 16.00, category = 'Rice', image = 'f85102a375e0f4837e5fef712bf99e23.webp', isPopular = 0;

-- Menu item added or updated
INSERT INTO menuitem (menuItemID, foodName, description, price, category, image, isPopular)
VALUES ('M0037', 'Nasi Lemak Kepak', 'Fragrant coconut rice served with spicy sambal, crispy chicken, boiled egg, and fresh cucumber slices.', 16.00, 'Rice', 'ba905b293a93b57b5c7fba6d79ac6c86.webp', 0)
ON DUPLICATE KEY UPDATE foodName = 'Nasi Lemak Kepak', description = 'Fragrant coconut rice served with spicy sambal, crispy chicken, boiled egg, and fresh cucumber slices.', price = 16.00, category = 'Rice', image = 'ba905b293a93b57b5c7fba6d79ac6c86.webp', isPopular = 0;
