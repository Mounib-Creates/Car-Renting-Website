<?php
session_start();


$carsData = json_decode(file_get_contents('cars.json'), true);

$status = $_GET['status'] ?? 'failure';
$reason = $_GET['reason'] ?? null;
$carId = $_GET['car_id'] ?? null;
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

if ($status === 'success' && $carId) {
    $car = null;
    foreach ($carsData as $c) {
        if ($c['id'] == $carId) {
            $car = $c;
            break;
        }
    }

    if (!$car) {
        echo "<h1>Error: Car not found.</h1>";
        echo "<a href='index.php'>Back to Home</a>";
        exit;
    }

    $startDateObj = new DateTime($startDate);
    $endDateObj = new DateTime($endDate);
    $interval = $startDateObj->diff($endDateObj)->days + 1;
    $totalPrice = $interval * $car['daily_price_huf'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Result</title>
    <link rel="stylesheet" href="booking.css">
</head>
<body>
    <div class="container">
        <?php if ($status === 'success' && $car): ?>
            <h1>Booking Successful!</h1>
            <div class="car-details">
                <img src="<?= $car['image'] ?>" alt="<?= $car['brand'] . ' ' . $car['model'] ?>" style="width:300px;">
                <h2><?= $car['brand'] . ' ' . $car['model'] ?></h2>
                <p>Fuel: <?= $car['fuel_type'] ?></p>
                <p>Shifter: <?= $car['transmission'] ?></p>
                <p>Year: <?= $car['year'] ?></p>
                <p>Seats: <?= $car['passengers'] ?></p>
            </div>
            <h3>Booking Details</h3>
            <p>Start Date: <?= htmlspecialchars($startDate) ?></p>
            <p>End Date: <?= htmlspecialchars($endDate) ?></p>
            <p>Total Price: HUF <?= number_format($totalPrice, 0, '.', ',') ?></p>
            <a href="index.php" class="btn">Back to Home</a>
        <?php else: ?>
            <h1>Booking Failed</h1>
            <p>
                <?php if ($reason === 'conflict'): ?>
                    The car is already booked for the selected period.
                <?php else: ?>
                    An error occurred while processing your booking.
                <?php endif; ?>
            </p>
            <a href="index.php" class="btn">Back to Home</a>
        <?php endif; ?>
    </div>
</body>
</html>
