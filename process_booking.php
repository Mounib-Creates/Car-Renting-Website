<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startDate = $_POST['start_date'] ?? null;
    $endDate = $_POST['end_date'] ?? null;
    $carId = $_POST['car_id'] ?? null;
    $userEmail = $_POST['user_email'] ?? null;

    if (!$startDate || !$endDate || !$carId || !$userEmail) {
        header('Location: booking_result.php?status=failure&reason=missing_data');
        exit;
    }

    if (strtotime($startDate) >= strtotime($endDate)) {
        header('Location: booking_result.php?status=failure&reason=date_error');
        exit;
    }

    $filePath = 'booking.json';
    $bookings = file_exists($filePath) ? json_decode(file_get_contents($filePath), true) : [];

    foreach ($bookings as $booking) {
        $existingStartDate = strtotime($booking['start_date']);
        $existingEndDate = strtotime($booking['end_date']);
        $newStartDate = strtotime($startDate);
        $newEndDate = strtotime($endDate);
        if ($booking['car_id'] == $carId &&
            (($newStartDate >= $existingStartDate && $newStartDate <= $existingEndDate) ||
             ($newEndDate >= $existingStartDate && $newEndDate <= $existingEndDate) ||
             ($newStartDate <= $existingStartDate && $newEndDate >= $existingEndDate))) {

            header('Location: booking_result.php?status=failure&reason=conflict');
            exit;
        }
    }

    $bookings[] = [
        'start_date' => $startDate,
        'end_date' => $endDate,
        'user_email' => $userEmail,
        'car_id' => intval($carId)
    ];

    file_put_contents($filePath, json_encode($bookings, JSON_PRETTY_PRINT));

    header("Location: booking_result.php?status=success&start_date=$startDate&end_date=$endDate&car_id=$carId");
    exit;
}
