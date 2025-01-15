<?php
session_start();

if (!isset($_SESSION['name']) || !isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$userName = $_SESSION['name'];
$userEmail = $_SESSION['email'];

$bookings = json_decode(file_get_contents('booking.json'), true);

$carsData = json_decode(file_get_contents('cars.json'), true);

$userBookings = array_filter($bookings, function ($booking) use ($userEmail) {
    return $booking['user_email'] === $userEmail;
});

$reservations = [];
foreach ($userBookings as $booking) {
    foreach ($carsData as $car) {
        if ($car['id'] === $booking['car_id']) {
            $reservations[] = [
                'car' => $car['brand'] . ' ' . $car['model'],
                'seats' => $car['passengers'],
                'transmission' => $car['transmission'],
                'start_date' => date('m.d', strtotime($booking['start_date'])),
                'end_date' => date('m.d', strtotime($booking['end_date'])),
                'image' => $car['image'],
            ];
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="navbar">
         <div class="navbar-brand">
        <a href="index.php" style="text-decoration: none; color: inherit;">iKarRental</a>
    </div>
        <div>
            <span>Welcome, <?= htmlspecialchars($userName) ?></span>
            <a href="logout.php" class="btn-yellow">Logout</a>
        </div>
    </div>

    <div class="profile-container">
        <div class="profile-header">
            <div class="user-info">
             <span>Logged in as</span>
             <span class="user-name"><?= htmlspecialchars($userName) ?></span>
        </div>
        </div>

        <h3>My Reservations</h3>
        <div class="reservations">
            <?php if (!empty($reservations)): ?>
                <?php foreach ($reservations as $reservation): ?>
                    <div class="reservation-card">
                        <img src="<?= $reservation['image'] ?>" alt="<?= $reservation['car'] ?>">
                        <h4><?= $reservation['car'] ?></h4>
                        <p><?= $reservation['seats'] ?> seats - <?= $reservation['transmission'] ?></p>
                        <p><?= $reservation['start_date'] ?> - <?= $reservation['end_date'] ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No reservations found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
