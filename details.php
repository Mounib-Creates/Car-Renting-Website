<?php
session_start();
$carsData = json_decode(file_get_contents('cars.json'), true);

$carId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$car = null;
foreach ($carsData as $c) {
    if ($c['id'] === $carId) {
        $car = $c;
        break;
    }
}

if (!$car) {
    echo "<h1>Car not found</h1>";
    exit;
}

$userName = $_SESSION['name'] ?? null;

$showDates = isset($_GET['show_dates']);

$errorMessage = $_SESSION['error_message'] ?? '';
unset($_SESSION['error_message']); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $car['brand'] . ' ' . $car['model'] ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="navbar">
        <div class="navbar-brand">
            <a href="index.php" style="text-decoration: none; color: inherit;">iKarRental</a>
        </div>
        <div>
            <?php if ($userName): ?>
                <span>Welcome, <?= htmlspecialchars($userName) ?></span>
                <a href="logout.php" class="btn-yellow">Logout</a>
            <?php else: ?>
                <a href="registration.php" class="btn-yellow">Registration</a>
                <a href="login.php" class="btn-yellow">Login</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="details-container">
        <div class="car-image">
            <img src="<?= $car['image'] ?>" alt="<?= $car['brand'] . ' ' . $car['model'] ?>">
        </div>
        <div class="car-details">
            <h1 class="car-title"><?= $car['brand'] . ' ' . $car['model'] ?></h1>
            <ul class="car-specs">
                <li>Fuel: <?= $car['fuel_type'] ?></li>
                <li>Shifter: <?= $car['transmission'] ?></li>
                <li>Year of manufacture: <?= $car['year'] ?></li>
                <li>Number of seats: <?= $car['passengers'] ?></li>
            </ul>
            <p class="car-price">HUF <?= number_format($car['daily_price_huf'], 0, '.', ',') ?>/day</p>
            
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>

            <form action="process_booking.php" method="POST">
                <div>
                    <a href="?id=<?= $carId ?>&show_dates=1" class="btn-blue">Select a date</a>
                </div>
                <?php if ($userName): ?>
                    <?php if ($showDates): ?>
                        <div id="date-selection" style="margin-top: 10px;">
                            <label for="start-date">Start Date </label>
                            <input type="date" id="start-date" name="start_date" required>
                            <br>
                            <label for="end-date">End Date</label>
                            <input type="date" id="end-date" name="end_date" required>
                            <br>
                            <input type="hidden" name="car_id" value="<?= $carId ?>">
                            <input type="hidden" name="user_email" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>">
                            <button type="submit" class="btn-green" style="margin-top: 10px;">Book it</button>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="guest-message" style="color: gray; margin-top: 20px;">
                        Please <a href="login.php" style="text-decoration: none; color: blue;">log in</a> to book this car.
                    </p>
                <?php endif; ?>
            </form>
        </div>
    </div>
</body>
</html>
