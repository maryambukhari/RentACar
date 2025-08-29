<?php
require_once 'db.php';

$booking_id = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);

if (!$booking_id) {
    die("Error: Invalid booking ID. Please try again.");
}

try {
    $stmt = $pdo->prepare("SELECT b.*, c.brand, c.model, c.image, c.car_type, c.fuel_type FROM bookings b JOIN cars c ON b.car_id = c.id WHERE b.id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        die("Error: Booking not found. Please try again.");
    }

    $start = new DateTime($booking['start_date']);
    $return = new DateTime($booking['return_date']);
    $days = $start->diff($return)->days;
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentACar - Booking Confirmation</title>
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
        .confirmation {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            color: #333;
        }
        .confirmation h2 {
            margin-top: 0;
            color: #ff6f61;
        }
        .confirmation p {
            margin: 10px 0;
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
        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #ff6f61;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            transition: background 0.3s;
        }
        .button:hover {
            background: #e55a50;
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
        <h1>Booking Confirmation</h1>
        <div class="confirmation">
            <h2>Thank You, <?php echo htmlspecialchars($booking['user_name']); ?>!</h2>
            <p>Your booking has been confirmed. Details are below:</p>
            <div class="car-details">
                <img src="<?php echo htmlspecialchars($booking['image']); ?>" alt="<?php echo htmlspecialchars($booking['model']); ?>">
                <div>
                    <p><strong>Car:</strong> <?php echo htmlspecialchars($booking['brand']); ?> <?php echo htmlspecialchars($booking['model']); ?></p>
                    <p><strong>Car Type:</strong> <?php echo htmlspecialchars($booking['car_type']); ?></p>
                    <p><strong>Fuel Type:</strong> <?php echo htmlspecialchars($booking['fuel_type']); ?></p>
                    <p><strong>Pickup Location:</strong> <?php echo htmlspecialchars($booking['pickup_location']); ?></p>
                    <p><strong>Start Date:</strong> <?php echo htmlspecialchars($booking['start_date']); ?></p>
                    <p><strong>Return Date:</strong> <?php echo htmlspecialchars($booking['return_date']); ?></p>
                    <p><strong>Total Days:</strong> <?php echo $days; ?></p>
                    <p><strong>Total Price:</strong> $<?php echo number_format($booking['total_price'], 2); ?></p>
                    <p><strong>Booking Date:</strong> <?php echo htmlspecialchars($booking['booking_date']); ?></p>
                </div>
            </div>
            <a href="index.php" class="button">Back to Home</a>
        </div>
    </div>
</body>
</html>
