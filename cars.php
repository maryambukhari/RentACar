<?php
require_once 'db.php';

$location = filter_input(INPUT_GET, 'location', FILTER_SANITIZE_STRING) ?? '';
$start_date = filter_input(INPUT_GET, 'start_date', FILTER_SANITIZE_STRING) ?? '';
$return_date = filter_input(INPUT_GET, 'return_date', FILTER_SANITIZE_STRING) ?? '';
$car_type = filter_input(INPUT_GET, 'car_type', FILTER_SANITIZE_STRING) ?? '';
$fuel_type = filter_input(INPUT_GET, 'fuel_type', FILTER_SANITIZE_STRING) ?? '';
$sort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING) ?? 'price_asc';

// Check if location column exists
try {
    $pdo->query("SELECT location FROM cars LIMIT 1");
    $location_exists = true;
} catch (PDOException $e) {
    $location_exists = false;
}

$query = "SELECT * FROM cars WHERE available = 1";
$params = [];

if ($location && $location_exists) {
    $query .= " AND (LOWER(location) LIKE LOWER(?) OR location = 'All Locations')";
    $params[] = "%" . trim($location) . "%";
}
if ($car_type) {
    $query .= " AND car_type = ?";
    $params[] = $car_type;
}
if ($fuel_type) {
    $query .= " AND fuel_type = ?";
    $params[] = $fuel_type;
}

if ($sort == 'price_asc') {
    $query .= " ORDER BY price_per_day ASC";
} elseif ($sort == 'price_desc') {
    $query .= " ORDER BY price_per_day DESC";
} elseif ($sort == 'rating') {
    $query .= " ORDER BY rating DESC";
}

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get available locations for error message
    $locations = ['New York', 'Los Angeles', 'San Francisco', 'Miami', 'Chicago', 'All Locations'];
    if ($location_exists) {
        $loc_stmt = $pdo->query("SELECT DISTINCT location FROM cars WHERE available = 1 AND location != 'All Locations'");
        $locations = array_merge($loc_stmt->fetchAll(PDO::FETCH_COLUMN), ['All Locations']);
    }

    // Check if cars table has data
    $check = $pdo->query("SELECT COUNT(*) as count FROM cars WHERE available = 1")->fetch(PDO::FETCH_ASSOC);
    $has_cars = $check['count'] > 0;
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentACar - Available Cars</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            background: linear-gradient(135deg, #2a5298, #1e3c72);
            color: #fff;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .filters {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filters select, .filters input[type="text"] {
            padding: 10px;
            font-size: 1em;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 200px;
        }
        .filters button {
            padding: 10px 20px;
            background: #4caf50;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .filters button:hover {
            background: #45a049;
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
        .car-card button {
            width: 100%;
            padding: 10px;
            background: #ff6f61;
            color: #fff;
            border: none;
            border-radius: 0 0 10px 10px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .car-card button:hover {
            background: #e55a50;
        }
        .error {
            background: #ff4d4d;
            color: #fff;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
        }
        .error a {
            color: #fff;
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .filters select, .filters input[type="text"] {
                width: 100%;
            }
            .filters button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Available Cars</h1>
        <?php if (empty($cars)): ?>
            <div class="error">
                No cars found matching your criteria. Try locations like '<?php echo implode("', '", array_map('htmlspecialchars', $locations)); ?>' or <a href="cars.php?start_date=<?php echo urlencode($start_date); ?>&return_date=<?php echo urlencode($return_date); ?>">clear filters</a>.
                <?php if (!$has_cars): ?>
                    <p>It looks like no cars are available in the database. Please contact support.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <form id="filterForm" class="filters">
            <input type="text" name="location" placeholder="Pickup Location" value="<?php echo htmlspecialchars($location); ?>">
            <select name="car_type" onchange="this.form.submit()">
                <option value="">All Car Types</option>
                <option value="Sedan" <?php if ($car_type == 'Sedan') echo 'selected'; ?>>Sedan</option>
                <option value="SUV" <?php if ($car_type == 'SUV') echo 'selected'; ?>>SUV</option>
                <option value="Hatchback" <?php if ($car_type == 'Hatchback') echo 'selected'; ?>>Hatchback</option>
                <option value="Luxury" <?php if ($car_type == 'Luxury') echo 'selected'; ?>>Luxury</option>
            </select>
            <select name="fuel_type" onchange="this.form.submit()">
                <option value="">All Fuel Types</option>
                <option value="Petrol" <?php if ($fuel_type == 'Petrol') echo 'selected'; ?>>Petrol</option>
                <option value="Diesel" <?php if ($fuel_type == 'Diesel') echo 'selected'; ?>>Diesel</option>
                <option value="Electric" <?php if ($fuel_type == 'Electric') echo 'selected'; ?>>Electric</option>
                <option value="Hybrid" <?php if ($fuel_type == 'Hybrid') echo 'selected'; ?>>Hybrid</option>
            </select>
            <select name="sort" onchange="this.form.submit()">
                <option value="price_asc" <?php if ($sort == 'price_asc') echo 'selected'; ?>>Price: Low to High</option>
                <option value="price_desc" <?php if ($sort == 'price_desc') echo 'selected'; ?>>Price: High to Low</option>
                <option value="rating" <?php if ($sort == 'rating') echo 'selected'; ?>>Best Rated</option>
            </select>
            <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
            <input type="hidden" name="return_date" value="<?php echo htmlspecialchars($return_date); ?>">
            <button type="button" onclick="clearFilters()">Clear Filters</button>
        </form>
        <div class="car-grid">
            <?php foreach ($cars as $car): ?>
                <div class="car-card">
                    <img src="<?php echo htmlspecialchars($car['image']); ?>" alt="<?php echo htmlspecialchars($car['model']); ?>">
                    <h3><?php echo htmlspecialchars($car['brand']); ?> <?php echo htmlspecialchars($car['model']); ?></h3>
                    <p>$<?php echo number_format($car['price_per_day'], 2); ?>/day</p>
                    <p>Fuel: <?php echo htmlspecialchars($car['fuel_type']); ?></p>
                    <p>Type: <?php echo htmlspecialchars($car['car_type']); ?></p>
                    <?php if ($location_exists): ?>
                        <p>Location: <?php echo htmlspecialchars($car['location']); ?></p>
                    <?php endif; ?>
                    <p>Rating: <?php echo number_format($car['rating'], 1); ?></p>
                    <button onclick="bookCar(<?php echo $car['id']; ?>, '<?php echo htmlspecialchars($start_date); ?>', '<?php echo htmlspecialchars($return_date); ?>', '<?php echo htmlspecialchars($location); ?>')">Book Now</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
        function bookCar(carId, startDate, returnDate, location) {
            if (!carId || !startDate || !returnDate) {
                alert('Please ensure all required search fields (dates) are filled.');
                return;
            }
            const url = `booking.php?car_id=${encodeURIComponent(carId)}&start_date=${encodeURIComponent(startDate)}&return_date=${encodeURIComponent(returnDate)}&location=${encodeURIComponent(location)}`;
            window.location.href = url;
        }

        function clearFilters() {
            const url = 'cars.php?start_date=<?php echo urlencode($start_date); ?>&return_date=<?php echo urlencode($return_date); ?>';
            window.location.href = url;
        }
    </script>
</body>
</html>
