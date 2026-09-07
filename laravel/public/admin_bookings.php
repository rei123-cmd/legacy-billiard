<?php
require_once 'config.php';
require_once 'admin_check.php';

// Handle Status Update
if (isset($_POST['update_status'])) {
    $booking_id = (int)$_POST['booking_id'];
    $status = $_POST['status'];
    
    $query = "UPDATE booking SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $booking_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Booking status updated!';
    }
    $stmt->close();
    header('Location: admin_bookings.php');
    exit;
}

// Handle Payment Status Update
if (isset($_POST['update_payment'])) {
    $booking_id = (int)$_POST['booking_id'];
    $payment_status = $_POST['payment_status'];
    
    $query = "UPDATE booking SET payment_status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $payment_status, $booking_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Payment status updated!';
    }
    $stmt->close();
    header('Location: admin_bookings.php');
    exit;
}

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

// Build query
$query = "SELECT b.*, u.nama, m.nama_meja 
          FROM booking b 
          LEFT JOIN users u ON b.user_id = u.id 
          LEFT JOIN meja m ON b.meja_id = m.id 
          WHERE 1=1";

if ($filter != 'all') {
    $query .= " AND b.status = '$filter'";
}

if (!empty($search)) {
    $query .= " AND (b.nama_pemesan LIKE '%$search%' OR b.telepon_pemesan LIKE '%$search%' OR u.nama LIKE '%$search%')";
}

$query .= " ORDER BY b.created_at DESC";
$bookings = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --admin-primary: #1a1a2e;
            --admin-secondary: #16213e;
            --admin-accent: #0f3460;
            --admin-gold: #D4AF37;
        }

        body {
            background: #0a0a0a;
            color: #ffffff;
            font-family: 'Inter', sans-serif;
        }

        .admin-content {
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
        }

        .admin-header {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            border: 2px solid var(--admin-gold);
        }

        .admin-header h1 {
            color: var(--admin-gold);
            font-weight: 900;
            margin: 0 0 1rem 0;
        }

        .filter-bar {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            border: 2px solid var(--admin-accent);
            background: var(--admin-accent);
            color: #ffffff;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .filter-btn:hover, .filter-btn.active {
            border-color: var(--admin-gold);
            background: var(--admin-gold);
            color: #000;
        }

        .search-box {
            flex: 1;
            max-width: 400px;
        }

        .search-box input {
            width: 100%;
            padding: 0.6rem 1rem;
            border-radius: 50px;
            border: 2px solid var(--admin-accent);
            background: var(--admin-accent);
            color: #ffffff;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--admin-gold);
        }

        .bookings-table {
            background: var(--admin-primary);
            border-radius: 15px;
            border: 2px solid var(--admin-accent);
            overflow: hidden;
        }

        .table {
            color: #ffffff;
            margin: 0;
        }

        .table thead {
            background: var(--admin-accent);
        }

        .table th {
            color: var(--admin-gold);
            font-weight: 700;
            padding: 1rem;
            border: none;
            white-space: nowrap;
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid var(--admin-accent);
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: var(--admin-accent);
        }

        .badge-status {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
            display: inline-block;
        }

        .status-pending {
            background: #ffc107;
            color: #000;
        }

        .status-confirmed {
            background: #28a745;
            color: #fff;
        }

        .status-cancelled {
            background: #dc3545;
            color: #fff;
        }

        .status-completed {
            background: #17a2b8;
            color: #fff;
        }

        .payment-unpaid {
            background: #6c757d;
            color: #fff;
        }

        .payment-paid {
            background: #28a745;
            color: #fff;
        }

        .btn-action {
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            margin: 0.2rem;
        }

        .btn-view {
            background: #17a2b8;
            color: #fff;
        }

        .btn-update {
            background: var(--admin-gold);
            color: #000;
        }

        .modal-content {
            background: var(--admin-secondary);
            border: 2px solid var(--admin-gold);
            color: #ffffff;
        }

        .form-select {
            background: var(--admin-accent);
            border: 2px solid var(--admin-accent);
            color: #ffffff;
        }

        .form-select:focus {
            background: var(--admin-accent);
            border-color: var(--admin-gold);
            color: #ffffff;
        }

        @media (max-width: 768px) {
            .admin-content {
                margin-left: 70px;
                padding: 1rem;
            }

            .table {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-calendar-check"></i> All Bookings</h1>
            
            <div class="filter-bar">
                <a href="?filter=all" class="filter-btn <?= $filter == 'all' ? 'active' : '' ?>">All</a>
                <a href="?filter=pending" class="filter-btn <?= $filter == 'pending' ? 'active' : '' ?>">Pending</a>
                <a href="?filter=confirmed" class="filter-btn <?= $filter == 'confirmed' ? 'active' : '' ?>">Confirmed</a>
                <a href="?filter=completed" class="filter-btn <?= $filter == 'completed' ? 'active' : '' ?>">Completed</a>
                <a href="?filter=cancelled" class="filter-btn <?= $filter == 'cancelled' ? 'active' : '' ?>">Cancelled</a>
                
                <div class="search-box">
                    <form method="GET">
                        <input type="hidden" name="filter" value="<?= $filter ?>">
                        <input type="text" name="search" placeholder="Search by name or phone..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </form>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <div class="bookings-table">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Table</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Duration</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($bookings->num_rows > 0): ?>
                            <?php while ($booking = $bookings->fetch_assoc()): ?>
                                <tr>
                                    <td><strong>#<?= $booking['id'] ?></strong></td>
                                    <td>
                                        <?= htmlspecialchars($booking['nama'] ?? $booking['nama_pemesan']) ?><br>
                                        <small style="color: #888;"><?= htmlspecialchars($booking['telepon_pemesan']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($booking['nama_meja']) ?></td>
                                    <td><?= date('d M Y', strtotime($booking['tanggal_booking'])) ?></td>
                                    <td><?= date('H:i', strtotime($booking['waktu_mulai'])) ?></td>
                                    <td><?= $booking['durasi_jam'] ?> jam</td>
                                    <td><strong>Rp <?= number_format($booking['total_harga'], 0, ',', '.') ?></strong></td>
                                    <td>
                                        <span class="badge-status status-<?= $booking['status'] ?>">
                                            <?= strtoupper($booking['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-status payment-<?= $booking['payment_status'] ?>">
                                            <?= strtoupper($booking['payment_status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn-action btn-update" 
                                                onclick="updateStatus(<?= $booking['id'] ?>, '<?= $booking['status'] ?>', '<?= $booking['payment_status'] ?>')">
                                            <i class="fas fa-edit"></i> Update
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" style="text-align: center; color: #888; padding: 3rem;">
                                    <i class="fas fa-inbox" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
                                    No bookings found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="color: var(--admin-gold);">
                        <i class="fas fa-edit"></i> Update Booking Status
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="booking_id" id="booking_id">
                        
                        <div class="mb-3">
                            <label class="form-label" style="color: var(--admin-gold);">
                                <i class="fas fa-clipboard-check"></i> Booking Status
                            </label>
                            <select class="form-select" name="status" id="status">
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="color: var(--admin-gold);">
                                <i class="fas fa-money-bill-wave"></i> Payment Status
                            </label>
                            <select class="form-select" name="payment_status" id="payment_status">
                                <option value="unpaid">Unpaid</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_status" class="btn-action btn-update">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <button type="submit" name="update_payment" class="btn-action btn-update">
                            <i class="fas fa-dollar-sign"></i> Update Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateStatus(bookingId, currentStatus, currentPayment) {
            document.getElementById('booking_id').value = bookingId;
            document.getElementById('status').value = currentStatus;
            document.getElementById('payment_status').value = currentPayment;
            
            new bootstrap.Modal(document.getElementById('updateModal')).show();
        }
    </script>
</body>
</html>
