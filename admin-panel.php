<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Session control
if (!isset($_SESSION['admin'])) {
    header("Location: admin-login.html");
    exit;
}

// CSRF token control
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once 'db.php';

try {
    // Test query
    $test = $db->query("SELECT 1");
} catch(PDOException $e) {
    die("Database connection error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <img src="logo.png" alt="Company Logo" class="logo">
            <h2>Appointment Management</h2>
        </div>
        <div class="search-container">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search by name, phone, or email..." onkeyup="searchAppointments()">
                <i class="fas fa-search search-icon"></i>
            </div>
            <div class="action-buttons-container">
                <button class="block-time-btn" onclick="showBlockTimeModal()">
                    <i class="fas fa-clock"></i> Block
                </button>
                <button class="appointment-btn" onclick="window.location.href='index.html'">
                    <i class="fas fa-calendar-plus"></i> Appointment
                </button>
            </div>
        </div>
        
        <!-- Block Time Modal -->
        <div id="blockTimeModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h3>Block Time</h3>
                <div class="block-time-form">
                    <input type="date" id="blockDate" min="" required onchange="checkWeekend(this)">
                    <p class="error-message" id="weekendError" style="display: none; color: red;">
                        You cannot block time on Sundays.
                    </p>
                    <select id="blockTime" required>
                        <?php
                        for($hour = 9; $hour < 17; $hour++) {
                            printf('<option value="%02d:00">%02d:00</option>', $hour, $hour);
                        }
                        ?>
                    </select>
                    <button onclick="blockTime()">Block Time</button>
                </div>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $db->query("
                    SELECT *, 
                    DATE_FORMAT(appointment_date, '%d/%m/%Y') as formatted_date,
                    CASE 
                        WHEN appointment_date >= CURDATE() THEN 1 
                        ELSE 0 
                    END as is_future 
                    FROM appointments 
                    ORDER BY 
                        is_future DESC,
                        CASE 
                            WHEN appointment_date >= CURDATE() THEN appointment_date 
                            END ASC,
                        CASE 
                            WHEN appointment_date < CURDATE() THEN appointment_date 
                            END DESC,
                        appointment_time ASC
                ");

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['formatted_date']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['appointment_time']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td class='status-" . $row['status'] . "'>" . 
                        ($row['status'] == 'confirmed' ? 'Appointment Confirmed' : 
                        ($row['status'] == 'cancelled' ? 'Cancelled' : 
                        htmlspecialchars($row['status']))) . 
                        "</td>";
                    echo "<td>
                            <div class='action-buttons'>
                                <button class='icon-button cancel' onclick='if(confirm(\"Are you sure you want to cancel this appointment?\")) updateStatus(" . $row['id'] . ", \"cancelled\")' title='Cancel'>
                                    <i class='fas fa-times'></i>
                                </button>
                                <button class='icon-button delete' onclick='deleteAppointment(" . $row['id'] . ")' title='Delete'>
                                    <i class='fas fa-trash'></i>
                                </button>
                            </div>
                          </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
    function searchAppointments() {
        const searchText = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const name = row.cells[2].textContent.toLowerCase();
            const phone = row.cells[3].textContent.toLowerCase();
            const email = row.cells[4].textContent.toLowerCase();
            
            if (name.includes(searchText) || 
                phone.includes(searchText) || 
                email.includes(searchText)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function deleteAppointment(id) {
        if (confirm('Are you sure you want to delete this appointment?')) {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');

            fetch('delete-appointment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Delete operation failed!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred!');
            });
        }
    }

    function updateStatus(id, status) {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('status', status);
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');

        fetch('update-status.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || 'Error updating status!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred!');
        });
    }

    // Modal fonksiyonları
    function showBlockTimeModal() {
        const modal = document.getElementById('blockTimeModal');
        const dateInput = document.getElementById('blockDate');
        dateInput.min = new Date().toISOString().split('T')[0];
        modal.style.display = 'block';
    }

    function closeModal() {
        document.getElementById('blockTimeModal').style.display = 'none';
    }

    function blockTime() {
        const date = document.getElementById('blockDate').value;
        const time = document.getElementById('blockTime').value;
        
        if (!date || !time) {
            alert('Please select date and time!');
            return;
        }

        // Pazar günü kontrolü
        const selectedDate = new Date(date);
        if (selectedDate.getDay() === 0) {
            alert('You cannot block time on Sundays.');
            return;
        }

        const formData = new FormData();
        formData.append('date', date);
        formData.append('time', time);
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');

        fetch('block-time.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Time successfully blocked!');
                closeModal();
                location.reload();
            } else {
                alert(data.error || 'Time blocking operation failed!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred!');
        });
    }

    // Click outside modal to close
    window.onclick = function(event) {
        const modal = document.getElementById('blockTimeModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    function checkWeekend(input) {
        const date = new Date(input.value);
        const weekendError = document.getElementById('weekendError');
        
        if (date.getDay() === 0) { // 0 = Sunday
            weekendError.style.display = 'block';
            input.value = ''; // Clear date
            return false;
        }
        
        weekendError.style.display = 'none';
        return true;
    }
    </script>
</body>
</html> 
