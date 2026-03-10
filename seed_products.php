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
