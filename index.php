<?php
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentACar - Home</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: #fff;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding: 50px 0;
        }
        .header h1 {
            font-size: 3em;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
        .search-bar {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .search-bar input, .search-bar select {
            padding: 10px;
            font-size: 1em;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 200px;
        }
        .search-bar button {
            padding: 10px 20px;
            background: #ff6f61;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .search-bar button:hover {
            background: #e55a50;
        }
        .featured-cars {
            margin-top: 50px;
        }
        .car-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .car-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
        }
        .car-card:hover {
            transform: scale(1.05);
        }
        .car-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .car-card h3 {
            color: #333;
            margin: 10px;
        }
        .car-card p {
            color: #666;
            margin: 0 10px 10px;
        }
        .error {
            background: #ff4d4d;
            color: #fff;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .search-bar input, .search-bar select {
                width: 100%;
            }
            .search-bar button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>RentACar - Your Journey Starts Here</h1>
        </div>
        <form id="searchForm" class="search-bar">
            <input type="text" name="location" placeholder="Pickup Location" value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>">
            <input type="date" name="start_date" value="<?php echo htmlspecialchars($_GET['start_date'] ?? ''); ?>" required>
            <input type="date" name="return_date" value="<?php echo htmlspecialchars($_GET['return_date'] ?? ''); ?>" required>
            <select name="car_type">
                <option value="">All Car Types</option>
                <option value="Sedan" <?php if (isset($_GET['car_type']) && $_GET['car_type'] == 'Sedan') echo 'selected'; ?>>Sedan</option>
                <option value="SUV" <?php if (isset($_GET['car_type']) && $_GET['car_type'] == 'SUV') echo 'selected'; ?>>SUV</option>
                <option value="Hatchback" <?php if (isset($_GET['car_type']) && $_GET['car_type'] == 'Hatchback') echo 'selected'; ?>>Hatchback</option>
                <option value="Luxury" <?php if (isset($_GET['car_type']) && $_GET['car_type'] == 'Luxury') echo 'selected'; ?>>Luxury</option>
            </select>
            <select name="fuel_type">
                <option value="">All Fuel Types</option>
                <option value="Petrol" <?php if (isset($_GET['fuel_type']) && $_GET['fuel_type'] == 'Petrol') echo 'selected'; ?>>Petrol</option>
                <option value="Diesel" <?php if (isset($_GET['fuel_type']) && $_GET['fuel_type'] == 'Diesel') echo 'selected'; ?>>Diesel</option>
                <option value="Electric" <?php if (isset($_GET['fuel_type']) && $_GET['fuel_type'] == 'Electric') echo 'selected'; ?>>Electric</option>
                <option value="Hybrid" <?php if (isset($_GET['fuel_type']) && $_GET['fuel_type'] == 'Hybrid') echo 'selected'; ?>>Hybrid</option>
            </select>
            <button type="submit">Search Cars</button>
        </form>
        <div class="featured-cars">
            <h2>Featured Cars</h2>
            <div class="car-grid">
                <?php
                try {
                    $stmt = $pdo->query("SELECT * FROM cars WHERE available = 1 LIMIT 4");
                    while ($car = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<div class='car-card'>";
                        echo "<img src='" . htmlspecialchars($car['image']) . "' alt='" . htmlspecialchars($car['model']) . "'>";
                        echo "<h3>" . htmlspecialchars($car['brand']) . " " . htmlspecialchars($car['model']) . "</h3>";
                        echo "<p>$" . number_format($car['price_per_day'], 2) . "/day</p>";
                        echo "<p>Rating: " . number_format($car['rating'], 1) . "</p>";
                        echo "</div>";
                    }
                } catch (PDOException $e) {
                    echo "<div class='error'>Database error: " . htmlspecialchars($e->getMessage()) . "</div>";
                }
                ?>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData);
            if (!formData.get('start_date') || !formData.get('return_date')) {
                alert('Please select both start and return dates.');
                return;
            }
            window.location.href = 'cars.php?' + params.toString();
        });
    </script>
</body>
</html>
