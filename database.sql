CREATE DATABASE IF NOT EXISTS ayurveda_db;
USE ayurveda_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255),
    is_deleted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Wishlist Table
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Reviews Table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Insert a default admin user (password: admin123)
-- Hash generated using password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (name, email, password, role) VALUES 
('Admin User', 'admin@ayurora.com', '$2y$10$Bl7Ells8BVPCI2ZAzsawJ.P1lkTZe6XA.FOqgHXHGWaL/mLZmYLZ6', 'admin');

-- Sample products for first-time setup
INSERT INTO products (name, category, description, price, image) VALUES
('Ashwagandha Premium Capsules', 'Capsules', 'Pure organic Ashwagandha extract. Helps reduce stress and anxiety, improves energy levels and concentration. 60 capsules per bottle.', 2450.00, 'sample_capsules.png'),
('Maha Narayan Therapeutic Oil', 'Oils & Thailas', 'A traditional Ayurvedic medicinal oil used for joint pain and muscle relaxation. Infused with 50+ healing herbs.', 1850.00, 'sample_oil.png'),
('Gotukola & Moringa Herbal Kwath', 'Herbal Tea & Kwath', 'Refreshing and detoxifying herbal tea blend. Rich in antioxidants and promotes natural well-being.', 950.00, 'sample_tea.png'),
('Triphala Churna Detox Powder', 'Powders & Churnas', 'Traditional Ayurvedic formula for digestive health and cleansing. Made from Amla, Bibhitaki, and Haritaki.', 1200.00, 'sample_powder.png'),
('Kshirabala Traditional Paste (Leheya)', 'Leheyas & Pastes', 'A nourishing and rejuvenating herbal paste. Excellent for general health, energy, and muscle strength. Traditionally used in deep tissue healing.', 3200.00, 'sample_paste.png'),
('Neem & Amla Revitalizing Shampoo', 'Hair & Skin Care', 'Gentle cleansing shampoo made from pure Neem and Amla extracts. Promotes hair growth, prevents dandruff, and maintains scalp health naturally.', 1450.00, 'sample_shampoo.png'),
('Sandalwood & Turmeric Face Scrub', 'Hair & Skin Care', 'Gentle exfoliating face scrub made from pure sandalwood powder and native Sri Lankan turmeric. Clears blemishes and deeply nourishes skin.', 1650.00, 'face_scrub.png'),
('Ayurvedic Herbal Pain Relief Balm', 'Oils & Thailas', 'Famous Sri Lankan herbal balm for soothing head colds, muscular aches, and joint pains. Made from eucalyptus, cinnamon, and citronella oils.', 450.00, 'balm.png'),
('Polpala Herbal Wellness Tea', 'Herbal Tea & Kwath', 'Natural diuretic herbal tea from pure Polpala. Helps prevent kidney stones and promotes a healthy urinary tract system.', 850.00, 'herbal_tea.png'),
('Brahmi Brain & Memory Tonic', 'Leheyas & Pastes', 'Traditional Ayurvedic brain tonic prepared with Brahmi and ghee to significantly enhance memory, mental focus, and clarity.', 2100.00, 'brain_tonic.png'),
('Samahan 14 Herbs Remedy Pack', 'Powders & Churnas', 'A traditional and authentic herbal concoction of 14 herbs that gives fast relief from cold, cough, and fever symptoms. Easy to prepare instantly.', 650.00, 'remedy_pack.png'),
('Turmeric & Sandalwood Bath Soap', 'Soaps', 'A natural Ayurvedic bath soap crafted with turmeric and sandalwood. Perfect for glowing and healthy skin.', 350.00, 'turmeric_soap.png'),
('Kumkumadi Tailam Face Oil', 'Oils & Thailas', 'An ancient Ayurvedic recipe for skin lightening, anti-aging and glowing skin. Enriched with pure saffron.', 2500.00, 'kumkumadi.png'),
('Aloe Vera Cooling Gel', 'Creams & Balms', 'Multipurpose clear aloe vera gel that deeply hydrates, cools, and soothes the skin and scalp.', 800.00, 'aloe_gel.png'),
('Amalaki Immune Support', 'Capsules', 'Premium organic Amalaki (Amla) Ayurvedic capsules. A powerful natural antioxidant that supports immunity, digestion, and skin health.', 1800.00, 'amalaki_caps.png'),
('Siddhalepa Herbal Balm', 'Creams & Balms', 'Traditional Ayurvedic herbal pain relief balm. Provides instant relief from headaches, joint pains, and cold symptoms.', 550.00, 'herbal_balm.png'),
('Jeevani Energy Drink Powder', 'Powders & Churnas', 'Ayurvedic herbal energy powder to revitalize your body and mind. Boosts stamina and reduces fatigue naturally.', 1250.00, 'energy_powder.png'),
('Mahanarayan Oil Extra', 'Oils & Thailas', 'A traditional Ayurvedic massage oil designed to deeply nourish muscles and joints, improving flexibility and easing stiffness.', 2200.00, 'massage_oil.png'),
('Gotu Kola Hair Oil', 'Hair & Skin Care', 'A premium green Gotu Kola Ayurvedic hair oil. Nourishes the scalp, promotes hair growth, and reduces hair fall.', 1100.00, 'gotukola_oil.png'),
('Venivel & Turmeric Face Wash', 'Hair & Skin Care', 'A golden Venivel and Turmeric face wash. Deeply cleanses and brightens the skin while fighting acne-causing bacteria naturally.', 1350.00, 'venivel_facewash.png'),
('Navaratna Massage Oil', 'Oils & Thailas', 'A luxurious red Ayurvedic massage oil infused with 9 precious herbs. Relieves stress, fatigue, and body aches.', 2400.00, 'navaratna_oil.png'),
('Link Samahan Spicy Balm', 'Creams & Balms', 'A traditional herbal pain relief balm. Highly effective for fast relief from headaches, colds, and muscular pains.', 350.00, 'samahan_balm.png'),
('Coriander & Ginger Tea', 'Herbal Tea & Kwath', 'Authentic Ayurvedic coriander and ginger herbal tea. Soothes the throat, improves digestion, and boosts natural immunity.', 850.00, 'coriander_tea.png'),
('Triphala Digestive Tablets', 'Capsules', 'Traditional Ayurvedic Triphala tablets. A gentle daily detox that promotes healthy digestion and regular bowel movements.', 1600.00, 'triphala_tabs.png'),
('Kohomba Neem Herbal Soap', 'Soaps', 'A rustic handmade Kohomba (Neem) herbal soap. Contains powerful antibacterial properties for clear, healthy skin.', 250.00, 'kohomba_soap.png'),
('Dashamoola Arishta', 'Herbal Tea & Kwath', 'A traditional liquid herbal tonic made from 10 potent roots. Restores energy, reduces inflammation, and balances Vata dosha.', 1950.00, 'dashamoola.png'),
('Suwadharani Immunity Drink', 'Herbal Tea & Kwath', 'A powerful Ayurvedic immunity powder drink made from traditional Sri Lankan herbs to protect against viral infections.', 950.00, 'suwadharani.png'),
('Kasthuri Kaha Night Cream', 'Creams & Balms', 'A luxurious Kasthuri Kaha (Wild Turmeric) night cream. Rejuvenates the skin overnight and enhances natural radiance.', 2100.00, 'kasthuri_cream.png'),
('Sandalwood Face Pack', 'Powders & Churnas', 'Pure Sandalwood (Chandanam) face pack powder for a glowing and blemish-free complexion.', 1200.00, 'sandalwood_powder.png'),
('Shatavari Women''s Health', 'Capsules', 'Ayurvedic Shatavari capsules to support female reproductive health, hormonal balance, and vitality.', 1850.00, 'shatavari_caps.png'),
('Bhringraj Hair Growth Oil', 'Oils & Thailas', 'Dark green Bhringraj Ayurvedic hair oil. Known as the king of herbs for hair growth and preventing premature graying.', 2200.00, 'bhringraj_oil.png'),
('Kumari Herbal Shampoo', 'Hair & Skin Care', 'A soothing Aloe Vera (Kumari) herbal shampoo. Gently cleanses the scalp while maintaining natural moisture.', 950.00, 'kumari_shampoo.png'),
('Pas Panguwa Remedy Pack', 'Herbal Tea & Kwath', 'Traditional Ayurvedic Pas Panguwa packet containing dried ginger, coriander, and other herbs for fast relief from body aches and colds.', 450.00, 'pas_panguwa.png'),
('Tulsi Holy Basil Tea', 'Herbal Tea & Kwath', 'A warm and refreshing cup of Tulsi (Holy Basil) herbal tea. Promotes respiratory health and reduces stress.', 750.00, 'tulsi_tea.png'),
('Rathmal (Ixora) Beauty Soap', 'Soaps', 'A beautiful pinkish-red herbal soap made from Rathmal (Ixora) flowers. Leaves skin soft and glowing.', 300.00, 'rathmal_soap.png'),
('Guduchi Immune Powder', 'Powders & Churnas', 'Guduchi (Giloy) herbal powder to naturally boost immunity and protect the body from infections.', 1150.00, 'guduchi_powder.png'),
('Pinda Thailaya Cooling Oil', 'Oils & Thailas', 'A traditional red Pinda Thailaya cooling oil. Excellent for relieving burning sensations and joint pain associated with gout.', 1650.00, 'pinda_thailaya.png'),
('Nelli Rasayanaya', 'Leheyas & Pastes', 'A sweet and thick Nelli (Amla) Rasayanaya herbal paste. Rich in Vitamin C and acts as a powerful antioxidant.', 2800.00, 'nelli_rasayanaya.png'),
('Lunuwila Memory Syrup', 'Herbal Tea & Kwath', 'Lunuwila (Bacopa) memory syrup to enhance brain function, memory retention, and concentration.', 1400.00, 'lunuwila_syrup.png'),
('Kottamchukkadi Pain Oil', 'Oils & Thailas', 'A traditional Ayurvedic oil excellent for managing muscle pain, inflammation, and sciatica.', 1900.00, 'kottamchukkadi.png'),
('Organic Moringa Capsules', 'Capsules', 'Bright green Moringa extract capsules packed with essential vitamins, minerals, and amino acids.', 1600.00, 'moringa_capsules.png'),
('Golden Turmeric Day Cream', 'Creams & Balms', 'A luxurious jar of golden yellow Turmeric day cream. Brightens complexion and protects from environmental damage.', 1950.00, 'turmeric_day_cream.png'),
('Vata Relief Massage Balm', 'Creams & Balms', 'A traditional tin of Vata relief massage balm. Warms the muscles and eases stiffness instantly.', 650.00, 'vata_balm.png'),
('Sudhu Handun Soap', 'Soaps', 'A pure Sudhu Handun (White Sandalwood) herbal soap for an unmatched premium spa aesthetic at home.', 400.00, 'sudhu_handun_soap.png'),
('Musta Digestive Churna', 'Powders & Churnas', 'Fine brown Musta (Nutgrass) digestive powder to relieve indigestion, bloating, and support a healthy gut.', 950.00, 'musta_churna.png'),
('Welmadata Skin Brightening Pack', 'Powders & Churnas', 'Vibrant red Welmadata herbal skin brightening powder to even out skin tone and reduce pigmentation.', 1300.00, 'welmadata_pack.png'),
('Pure Neem Extract Capsules', 'Capsules', 'Dark green Neem extract capsules to deeply detoxify the blood and promote clear skin from within.', 1450.00, 'neem_capsules.png'),
('Brahmi Head Massage Oil', 'Oils & Thailas', 'Luxurious green Brahmi head massage oil. Calms the mind, induces deep sleep, and nourishes the scalp.', 2100.00, 'brahmi_massage_oil.png'),
('Haritaki Extract Tablets', 'Capsules', 'Premium Haritaki extract tablets. A potent Ayurvedic rejuvenator that supports digestion, detoxifies the body, and promotes longevity.', 1750.00, 'triphala_tabs.png'),
('Pippali Long Pepper Powder', 'Powders & Churnas', 'Authentic Pippali (Long Pepper) powder. Enhances metabolism, improves respiratory health, and aids in the absorption of nutrients.', 1100.00, 'musta_churna.png'),
('Pure Shilajit Resin', 'Leheyas & Pastes', 'Premium pure Shilajit resin sourced from high altitudes. A powerful natural supplement that boosts energy, stamina, and overall vitality.', 4500.00, 'sample_paste.png');
