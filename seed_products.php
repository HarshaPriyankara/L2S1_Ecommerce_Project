<?php
include 'includes/db.php';

$products = [
    [
        'name' => 'Ashwagandha Premium Capsules',
        'category' => 'Capsules',
        'description' => 'Pure organic Ashwagandha extract. Helps reduce stress and anxiety, improves energy levels and concentration. 60 capsules per bottle.',
        'price' => 2450.00,
        'image' => 'sample_capsules.png'
    ],
    [
        'name' => 'Maha Narayan Therapeutic Oil',
        'category' => 'Oils & Thailas',
        'description' => 'A traditional Ayurvedic medicinal oil used for joint pain and muscle relaxation. Infused with 50+ healing herbs.',
        'price' => 1850.00,
        'image' => 'sample_oil.png'
    ],
    [
        'name' => 'Gotukola & Moringa Herbal Kwath',
        'category' => 'Herbal Tea & Kwath',
        'description' => 'Refreshing and detoxifying herbal tea blend. Rich in antioxidants and promotes natural well-being.',
        'price' => 950.00,
        'image' => 'sample_tea.png'
    ],
    [
        'name' => 'Triphala Churna Detox Powder',
        'category' => 'Powders & Churnas',
        'description' => 'Traditional Ayurvedic formula for digestive health and cleansing. Made from Amla, Bibhitaki, and Haritaki.',
        'price' => 1200.00,
        'image' => 'sample_powder.png'
    ],
    [
        'name' => 'Kshirabala Traditional Paste (Leheya)',
        'category' => 'Leheyas & Pastes',
        'description' => 'A nourishing and rejuvenating herbal paste. Excellent for general health, energy, and muscle strength. Traditionally used in deep tissue healing.',
        'price' => 3200.00,
        'image' => 'sample_paste.png'
    ],
    [
        'name' => 'Neem & Amla Revitalizing Shampoo',
        'category' => 'Hair & Skin Care',
        'description' => 'Gentle cleansing shampoo made from pure Neem and Amla extracts. Promotes hair growth, prevents dandruff, and maintains scalp health naturally.',
        'price' => 1450.00,
        'image' => 'sample_shampoo.png'
    ],
    [
        'name' => 'Sandalwood & Turmeric Face Scrub',
        'category' => 'Hair & Skin Care',
        'description' => 'Gentle exfoliating face scrub made from pure sandalwood powder and native Sri Lankan turmeric. Clears blemishes and deeply nourishes skin.',
        'price' => 1650.00,
        'image' => 'face_scrub.png'
    ],
    [
        'name' => 'Ayurvedic Herbal Pain Relief Balm',
        'category' => 'Oils & Thailas',
        'description' => 'Famous Sri Lankan herbal balm for soothing head colds, muscular aches, and joint pains. Made from eucalyptus, cinnamon, and citronella oils.',
        'price' => 450.00,
        'image' => 'balm.png'
    ],
    [
        'name' => 'Polpala Herbal Wellness Tea',
        'category' => 'Herbal Tea & Kwath',
        'description' => 'Natural diuretic herbal tea from pure Polpala. Helps prevent kidney stones and promotes a healthy urinary tract system.',
        'price' => 850.00,
        'image' => 'herbal_tea.png'
    ],
    [
        'name' => 'Brahmi Brain & Memory Tonic',
        'category' => 'Leheyas & Pastes',
        'description' => 'Traditional Ayurvedic brain tonic prepared with Brahmi and ghee to significantly enhance memory, mental focus, and clarity.',
        'price' => 2100.00,
        'image' => 'brain_tonic.png'
    ],
    [
        'name' => 'Samahan 14 Herbs Remedy Pack',
        'category' => 'Powders & Churnas',
        'description' => 'A traditional and authentic herbal concoction of 14 herbs that gives fast relief from cold, cough, and fever symptoms. Easy to prepare instantly.',
        'price' => 650.00,
        'image' => 'remedy_pack.png'
    ]
];

echo "<h2>Seeding Sample Products...</h2>";

foreach ($products as $p) {
    $name = $conn->real_escape_string($p['name']);
    $category = $conn->real_escape_string($p['category']);
    $description = $conn->real_escape_string($p['description']);
    $price = $p['price'];
    $image = $p['image'];
    
    // Check if it already exists to avoid duplicates
    $check_sql = "SELECT id FROM products WHERE name = '$name'";
    $result = $conn->query($check_sql);
    
    if ($result->num_rows == 0) {
        $sql = "INSERT INTO products (name, category, description, price, image) VALUES ('$name', '$category', '$description', '$price', '$image')";
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>Added: $name</p>";
        } else {
            echo "<p style='color: red;'>Error adding $name: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: orange;'>Skipped (already exists): $name</p>";
    }
}

echo "<br><a href='index.php'>Go to Homepage</a>";
?>
