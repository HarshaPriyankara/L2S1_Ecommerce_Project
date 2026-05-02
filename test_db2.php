<?php $conn = new mysqli("127.0.0.1", "root", "", "ayurveda_db", 3308); if ($conn->connect_error) echo "127.0.0.1 Error: " . $conn->connect_error; else { echo "127.0.0.1 Success\n"; } ?>
