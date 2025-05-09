<?php
session_start();

if (!isset($_SESSION['isAdmin']) || !$_SESSION['isAdmin']) {
    header('Location: login.php');
    exit;
}

$carsFile = 'cars.json';
$bookingsFile = 'booking.json';
$errors = []; 

if (file_exists($carsFile) && is_readable($carsFile)) {
    $cars = json_decode(file_get_contents($carsFile), true) ?? [];
} else {
    $errors[] = "Error: Cars data file is missing or not readable.";
    $cars = [];
}


if (file_exists($bookingsFile) && is_readable($bookingsFile)) {
    $bookings = json_decode(file_get_contents($bookingsFile), true) ?? [];
} else {
    $bookings = [];
}


$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;
$transmission = $_GET['transmission'] ?? null;
$passengers = $_GET['passengers'] ?? null;
$minPrice = $_GET['min_price'] ?? null;
$maxPrice = $_GET['max_price'] ?? null;

if ($startDate && $endDate && strtotime($startDate) > strtotime($endDate)) {
    $errors[] = "Start date cannot be after end date.";
}

if ($passengers && (!is_numeric($passengers) || $passengers < 2 || $passengers > 10)) {
    $errors[] = "Seats must be a number between 2 and 10.";
}

if ($transmission && !in_array($transmission, ['Automatic', 'Manual'])) {
    $errors[] = "Invalid gear type selected. Please choose either 'Automatic' or 'Manual'.";
}

if (($minPrice && (!is_numeric($minPrice) || $minPrice < 0) ) || ($maxPrice && (!is_numeric($maxPrice) || $maxPrice < 0))  || ($minPrice && $maxPrice && $minPrice > $maxPrice)) {
    $errors[] = "Invalid price range. Ensure values are positive and minimum price is less than maximum price.";
}


$filteredCars = array_filter($cars, function ($car) use ($startDate, $endDate, $transmission, $passengers, $minPrice, $maxPrice, $bookings) {
    if ($transmission && $car['transmission'] !== $transmission) return false;
    if ($passengers && $car['passengers'] < intval($passengers)) return false;
    if ($minPrice && $car['daily_price_huf'] < intval($minPrice)) return false;
    if ($maxPrice && $car['daily_price_huf'] > intval($maxPrice)) return false;

  
    if ($startDate && $endDate) {
        $filterStart = strtotime($startDate);
        $filterEnd = strtotime($endDate);

        foreach ($bookings as $booking) {
            if ($booking['car_id'] === $car['id']) {
                $bookedStart = strtotime($booking['start_date']);
                $bookedEnd = strtotime($booking['end_date']);

                if ($filterStart <= $bookedEnd && $filterEnd >= $bookedStart) {
                    return false;
                }
            }
        }
    }
    return true;
});


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_car'])) {
    $carId = intval($_POST['delete_car']);

    
    $bookings = array_filter($bookings, fn($booking) => $booking['car_id'] !== $carId);

    $cars = array_filter($cars, fn($car) => $car['id'] !== $carId);

    file_put_contents('booking.json', json_encode($bookings, JSON_PRETTY_PRINT));
    file_put_contents('cars.json', json_encode(array_values($cars), JSON_PRETTY_PRINT));

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">iKarRental</a>
            <div>
                <span>Admin</span>
                <a href="logout.php" class="btn-yellow">Logout</a>
            </div>
        </div>
    </nav>

    <div class="filter-container container my-4">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="start_date" class="form-label">From</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">Until</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label for="passengers" class="form-label">Seats</label>
                <input type="number" name="passengers" id="passengers" class="form-control" value="<?= htmlspecialchars($_GET['passengers'] ?? '') ?>" placeholder="e.g., 4">
            </div>
            <div class="col-md-2">
                <label for="transmission" class="form-label">Gear type</label>
                <select name="transmission" id="transmission" class="form-select">
                    <option value="">Any</option>
                    <option value="Automatic" <?= (($_GET['transmission'] ?? '') === 'Automatic') ? 'selected' : '' ?>>Automatic</option>
                    <option value="Manual" <?= (($_GET['transmission'] ?? '') === 'Manual') ? 'selected' : '' ?>>Manual</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="price_range" class="form-label">Price Range (Ft)</label>
                <div class="d-flex align-items-center">
                    <input type="number" name="min_price" id="min_price" class="form-control me-2" placeholder="Min" value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>">
                    <span class="mx-2">-</span>
                    <input type="number" name="max_price" id="max_price" class="form-control ms-2" placeholder="Max" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>">
                </div>
            </div>

            <div class="col-md-12 d-flex justify-content-between">
                <button type="submit" class="btn-yellow">Filter</button>
                <a href="add.php" class="btn btn-success">Add New Car</a>
            </div>
        </form>
    </div>

    <div class="container py-5">
        <div class="row g-3">
        <?php
        if (!empty($errors)) {
            echo "<div class='alert alert-danger'>";
            foreach ($errors as $error) {
                echo "<p>" . htmlspecialchars($error) . "</p>";
            }
            echo "</div>";
        } elseif (count($filteredCars) > 0) {
            foreach ($filteredCars as $car): ?>
                <div class="col-md-4 col-lg-3">
                    <div class="card car-card">
                        <img src="<?= htmlspecialchars($car['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>">
                        <div class="card-body">
                            <h5 class="card-title text-dark"><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h5>
                            <p class="card-text text-dark"><?= htmlspecialchars($car['passengers']) ?> seats - <?= htmlspecialchars($car['transmission']) ?></p>
                            <p class="card-text text-warning"><?= number_format($car['daily_price_huf'], 0) ?> Ft</p>
                            <form method="POST" class="d-inline">
                                <button type="submit" name="delete_car" value="<?= $car['id'] ?>" class="btn btn-danger">Delete</button>
                            </form>
                            <a href="edit.php?id=<?= $car['id'] ?>" class="btn btn-primary">Edit</a>
                        </div>
                    </div>
                </div>
            <?php endforeach;
        } else {
            echo "<p class='text-center text-warning'>No cars match the selected filters.</p>";
        }
        ?>
        </div>
    </div>

    
</body>
<footer>
    <p>&copy; 2024 iKarRental. All rights reserved.</p>
</footer>
</html>
