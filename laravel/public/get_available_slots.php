<?php

require_once 'config.php';
header('Content-Type: application/json');

$meja_id = isset($_GET['meja_id']) ? (int)$_GET['meja_id'] : 0;
$tanggal = isset($_GET['tanggal']) ? clean_input($_GET['tanggal']) : '';
$paket = isset($_GET['paket']) ? clean_input($_GET['paket']) : 'perjam';

// Validation
if (!$meja_id || !$tanggal) {
    echo json_encode([
        'success' => false, 
        'bookedSlots' => [],
        'message' => 'Data tidak lengkap'
    ]);
    exit;
}


$query = "SELECT waktu_mulai, waktu_selesai 
          FROM booked_slots 
          WHERE meja_id = ? AND tanggal_booking = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("is", $meja_id, $tanggal);
$stmt->execute();
$result = $stmt->get_result();

$booked_times = [];
$booked_ranges = [];

while ($row = $result->fetch_assoc()) {
    // Store full time range
    $booked_ranges[] = [
        'start' => $row['waktu_mulai'],
        'end' => $row['waktu_selesai']
    ];
    
    // Convert to hours for blocking
    $start_hour = (int)substr($row['waktu_mulai'], 0, 2);
    $end_hour = (int)substr($row['waktu_selesai'], 0, 2);
    
    // Block ALL hours in the range
    for ($hour = $start_hour; $hour < $end_hour; $hour++) {
        $time_slot = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
        if (!in_array($time_slot, $booked_times)) {
            $booked_times[] = $time_slot;
        }
    }
}

$stmt->close();


// Get duration from paket type
$duration = 2; // default
if ($paket === 'promo_siang') {
    $duration = 3;
} elseif ($paket === 'promo_malam') {
    $duration = 4;
}

// Check if any time slot would overlap with existing bookings
$additional_blocked = [];

foreach ($booked_ranges as $range) {
    $range_start = strtotime($range['start']);
    $range_end = strtotime($range['end']);
    
    // Check all possible start times
    for ($hour = 9; $hour <= 23; $hour++) {
        $check_start = strtotime(sprintf('%02d:00:00', $hour));
        $check_end = $check_start + ($duration * 3600);
        
        // If this time slot would overlap with booked range
        if (($check_start < $range_end) && ($check_end > $range_start)) {
            $time_slot = sprintf('%02d:00', $hour);
            if (!in_array($time_slot, $booked_times) && !in_array($time_slot, $additional_blocked)) {
                $additional_blocked[] = $time_slot;
            }
        }
    }
}

// Merge all blocked slots
$all_blocked = array_unique(array_merge($booked_times, $additional_blocked));
sort($all_blocked);

echo json_encode([
    'success' => true,
    'bookedSlots' => $all_blocked,
    'totalBlocked' => count($all_blocked),
    'message' => 'Slot waktu berhasil dimuat',
    'debug' => [
        'meja_id' => $meja_id,
        'tanggal' => $tanggal,
        'paket' => $paket,
        'duration' => $duration,
        'booked_ranges' => $booked_ranges
    ]
]);

$conn->close();
?>