<?php
$filename = 'cars.json';
$bookingFile = 'booking.json';

$errors = [];

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $carId = (int) $_GET['id'];
    $cars = json_decode(file_get_contents($filename), true);
    $carToEdit = null;
    foreach ($cars as $car) {
        if ((int) $car['id'] === $carId) {
            $carToEdit = $car;
            break;
        }
    }

    if (!$carToEdit) {
        die('Car not found.');
    }

    $bookings = json_decode(file_get_contents($bookingFile), true);

    $carBookings = array_filter($bookings, function($booking) use ($carId) {
        return $booking['car_id'] == $carId;
    });

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            if (empty($_POST['brand'])) {
                $errors['brand'] = 'Brand is required.';
            }
            if (empty($_POST['model'])) {
                $errors['model'] = 'Model is required.';
            }
            if (!in_array($_POST['transmission'], ['Manual', 'Automatic'])) {
                $errors['transmission'] = 'Invalid transmission value.';
            }
            if (!in_array($_POST['fuel_type'], ['Petrol', 'Diesel', 'Electric'])) {
                $errors['fuel_type'] = 'Invalid fuel type value.';
            }
            if (!is_numeric($_POST['passengers']) || intval($_POST['passengers']) < 2 || intval($_POST['passengers']) > 10) {
                $errors['passengers'] = 'Passengers must be a number between 2 and 10.';
            }
            if (!is_numeric($_POST['daily_price_huf']) || intval($_POST['daily_price_huf']) <= 0) {
                $errors['daily_price_huf'] = 'Daily price must be a positive number.';
            }
            if (empty($_POST['image'])) {
                $errors['image'] = 'Image URL is required.';
            }
            if (!is_numeric($_POST['year']) || intval($_POST['year']) < 1900 || intval($_POST['year']) > date('Y')) {
                $errors['year'] = 'Year must be a valid number between 1900 and the current year.';
            }

            if (empty($errors)) {
                $carToEdit['brand'] = $_POST['brand'];
                $carToEdit['model'] = $_POST['model'];
                $carToEdit['year'] = intval($_POST['year']);
                $carToEdit['transmission'] = $_POST['transmission'];
                $carToEdit['fuel_type'] = $_POST['fuel_type'];
                $carToEdit['passengers'] = intval($_POST['passengers']);
                $carToEdit['daily_price_huf'] = intval($_POST['daily_price_huf']);
                $carToEdit['image'] = $_POST['image'];

                foreach ($cars as $index => &$car) {
                    if ((int) $car['id'] === $carId) {
                        $cars[$index] = $carToEdit;
                        break;
                    }
                }

                file_put_contents($filename, json_encode($cars, JSON_PRETTY_PRINT));

                $successMessage = "Car updated successfully!";
            }
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
        }
    }

    if (isset($_POST['delete_booking'])) {
        $deleteBookingId = $_POST['delete_booking'];
        $updatedBookings = array_filter($bookings, function($booking) use ($deleteBookingId) {
            return $booking['car_id'] != $deleteBookingId;
        });


        if (file_put_contents($bookingFile, json_encode($updatedBookings, JSON_PRETTY_PRINT))) {
            header("Location: edit.php?id=$carId");
            exit;
        }
    }
} else {
    die('Invalid or missing ID.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Car</title>
    <link rel="stylesheet" href="edit.css">
</head>
<body>
    <div class="container">
        <h1>Edit Car</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">Please fix the following errors:</div>
        <?php endif; ?>

        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="form-group">
                <label for="brand">Brand</label>
                <input type="text" id="brand" name="brand" value="<?= htmlspecialchars($carToEdit['brand']) ?>" required>
                <?php if (isset($errors['brand'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errors['brand']) ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="model">Model</label>
                <input type="text" id="model" name="model" value="<?= htmlspecialchars($carToEdit['model']) ?>" required>
                <?php if (isset($errors['model'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errors['model']) ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="year">Year</label>
                <input type="number" id="year" name="year" value="<?= htmlspecialchars($carToEdit['year']) ?>" required min="1900" max="<?= date('Y') ?>">
                <?php if (isset($errors['year'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errors['year']) ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="transmission">Transmission</label>
                <select id="transmission" name="transmission" required>
                    <option value="Manual" <?= $carToEdit['transmission'] === 'Manual' ? 'selected' : '' ?>>Manual</option>
                    <option value="Automatic" <?= $carToEdit['transmission'] === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                </select>
                <?php if (isset($errors['transmission'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errors['transmission']) ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="fuel_type">Fuel Type</label>
                <select id="fuel_type" name="fuel_type" required>
                    <option value="Petrol" <?= $carToEdit['fuel_type'] === 'Petrol' ? 'selected' : '' ?>>Petrol</option>
                    <option value="Diesel" <?= $carToEdit['fuel_type'] === 'Diesel' ? 'selected' : '' ?>>Diesel</option>
                    <option value="Electric" <?= $carToEdit['fuel_type'] === 'Electric' ? 'selected' : '' ?>>Electric</option>
                </select>
                <?php if (isset($errors['fuel_type'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errors['fuel_type']) ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="passengers">Passengers</label>
                <input type="number" id="passengers" name="passengers" value="<?= htmlspecialchars($carToEdit['passengers']) ?>" min="2" max="10" required>
                <?php if (isset($errors['passengers'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errors['passengers']) ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="daily_price_huf">Daily Price (HUF)</label>
                <input type="number" id="daily_price_huf" name="daily_price_huf" value="<?= htmlspecialchars($carToEdit['daily_price_huf']) ?>" required>
                <?php if (isset($errors['daily_price_huf'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errors['daily_price_huf']) ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="image">Image URL</label>
                <input type="text" id="image" name="image" value="<?= htmlspecialchars($carToEdit['image']) ?>" required>
                <?php if (isset($errors['image'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errors['image']) ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" name="update_car" class="btn btn-primary">Update Car</button>
        </form>

        <h2>Bookings for this Car</h2>
        <?php if (empty($carBookings)): ?>
            <p>No bookings found for this car.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>User Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($carBookings as $booking): ?>
                        <tr id="booking-<?= htmlspecialchars($booking['car_id']) ?>">
                            <td><?= htmlspecialchars($booking['start_date']) ?></td>
                            <td><?= htmlspecialchars($booking['end_date']) ?></td>
                            <td><?= htmlspecialchars($booking['user_email']) ?></td>
                            <td>
                                <form action="edit.php?id=<?= $carId ?>" method="POST" style="display:inline;">
                                    <input type="hidden" name="delete_booking" value="<?= $booking['car_id'] ?>">
                                    <button type="submit" class="btn btn-danger">Delete Booking</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <a href="admin_profile.php" class="btn btn-secondary">Back to Admin Page</a>
    </div>
</body>
</html>
