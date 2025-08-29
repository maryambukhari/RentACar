<?php
require_once 'db.php';

$car_id = filter_input(INPUT_GET, 'car_id', FILTER_VALIDATE_INT);
$start_date = $_GET['start_date'] ?? '';
$return_date = $_GET['return_date'] ?? '';
$location = $_GET['location'] ?? '';

if (!$car_id || !$start_date || !$return_date || !$location) {
    die("Error: Missing required parameters. Please go back and try again.");
}

try {
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ? AND available = 1");
    $stmt->execute([$car_id]);
    $car = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$car) {
        die("Error: Car not found or unavailable. Please select another car.");
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $user_name = filter_input(INPUT_POST, 'user_name', FILTER_SANITIZE_STRING);
        $user_email = filter_input(INPUT_POST, 'user_email', FILTER_VALIDATE_EMAIL);
        $pickup_location = filter_input(INPUT_POST, 'pickup_location', FILTER_SANITIZE_STRING);
        $total_price = filter_input(INPUT_POST, 'total_price', FILTER_VALIDATE_FLOAT);

        if (!$user_name || !$user_email || !$pickup_location || !$total_price) {
            $error = "Please fill in all required fields correctly.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO bookings (car_id, user_name, user_email, pickup_location, start_date, return_date, total_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$car_id, $user_name, $user_email, $pickup_location, $start_date, $return_date, $total_price]);
            $success = "Booking confirmed! You'll receive a confirmation email soon.";
        }
    }

    $start = new DateTime($start_date);
    $return = new DateTime($return_date);
    $days = $start->diff($return)->days;
    if ($days <= 0) {
        die("Error: Return date must be after start date.");
    }
    $total_price = $car['price_per_day'] * $days;
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentACar - Book Your Car</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: #fff;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .booking-form {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            color: #333;
        }
        .booking-form h2 {
            margin-top: 0;
        }
        .booking-form input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
        }
        .booking-form button {
            width: 100%;
            padding: 10px;
            background: #ff6f61;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .booking-form button:hover {
            background: #e55a50;
        }
        .success, .error {
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
        }
        .success {
            background: #4caf50;
            color: #fff;
        }
        .error {
            background: #ff4d4d;
            color: #fff;
        }
        .car-details {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .car-details img {
            width: 200px;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
        }
        @media (max-width: 768px) {
            .car-details {
                flex-direction: column;
            }
            .car-details img {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Book Your Car</h1>
        <?php if (isset($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php elseif (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <div class="car-details">
            <img src="<?php echo htmlspecialchars($car['image']); ?>" alt="<?php echo htmlspecialchars($car['model']); ?>">
            <div>
                <h2><?php echo htmlspecialchars($car['brand']); ?> <?php echo htmlspecialchars($car['model']); ?></h2>
                <p>Price: $<?php echo number_format($car['price_per_day'], 2); ?>/day</p>
                <p>Total for <?php echo $days; ?> days: $<?php echo number_format($total_price, 2); ?></p>
                <p>Fuel: <?php echo htmlspecialchars($car['fuel_type']); ?></p>
                <p>Type: <?php echo htmlspecialchars($car['car_type']); ?></p>
            </div>
        </div>
        <form class="booking-form" method="POST">
            <input type="hidden" name="car_id" value="<?php echo htmlspecialchars($car_id); ?>">
            <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
            <input type="hidden" name="return_date" value="<?php echo htmlspecialchars($return_date); ?>">
            <input type="hidden" name="total_price" value="<?php echo htmlspecialchars($total_price); ?>">
            <input type="text" name="user_name" placeholder="Your Name" value="<?php echo isset($_POST['user_name']) ? htmlspecialchars($_POST['user_name']) : ''; ?>" required>
            <input type="email" name="user_email" placeholder="Your Email" value="<?php echo isset($_POST['user_email']) ? htmlspecialchars($_POST['user_email']) : ''; ?>" required>
            <input type="text" name="pickup_location" placeholder="Pickup Location" value="<?php echo htmlspecialchars($location); ?>" required>
            <button type="submit">Confirm Booking</button>
        </form>
    </div>
</body>
</html>
