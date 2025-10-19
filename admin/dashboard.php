<?php
include '../authentication/auth.php';
require_once '../database/starroofing_db.php';

$welcome_message = '';
if (isset($_SESSION['success'])) {
    $welcome_message = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Star Roofing & Construction</title>
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS style -->
    <link rel="stylesheet" href="../css/admin_main.css">
    <link rel="stylesheet" href="../css/admin_dashboard.css">
</head>
<body>
    <div class="main-container">
        <!-- Sidebar -->
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation -->
             <?php include '../includes/admin_navbar.php'; ?>
            
            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <h1 class="page-title">Dashboard Overview</h1>
                
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon clients">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3>248</h3>
                            <p>Total Clients</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon projects">
                            <i class="fas fa-hard-hat"></i>
                        </div>
                        <div class="stat-info">
                            <h3>54</h3>
                            <p>Active Projects</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon revenue">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>₱1.2M</h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon tasks">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="stat-info">
                            <h3>18</h3>
                            <p>Pending Tasks</p>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Projects -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Recent Projects</h2>
                        <a href="#" class="card-action">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Project Name</th>
                                        <th>Client</th>
                                        <th>Start Date</th>
                                        <th>Deadline</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Garcia Residence Roofing</td>
                                        <td>Rodrigo Garcia</td>
                                        <td>Oct 10, 2023</td>
                                        <td>Nov 15, 2023</td>
                                        <td><span class="status progress">In Progress</span></td>
                                    </tr>
                                    <tr>
                                        <td>San Juan Commercial Complex</td>
                                        <td>San Juan Development Corp</td>
                                        <td>Sep 28, 2023</td>
                                        <td>Dec 20, 2023</td>
                                        <td><span class="status progress">In Progress</span></td>
                                    </tr>
                                    <tr>
                                        <td>Santos Steel Truss Installation</td>
                                        <td>Maria Santos</td>
                                        <td>Oct 5, 2023</td>
                                        <td>Oct 30, 2023</td>
                                        <td><span class="status pending">Pending</span></td>
                                    </tr>
                                    <tr>
                                        <td>Rivera Roof Repair</td>
                                        <td>Carlos Rivera</td>
                                        <td>Sep 15, 2023</td>
                                        <td>Oct 5, 2023</td>
                                        <td><span class="status completed">Completed</span></td>
                                    </tr>
                                    <tr>
                                        <td>Nueva Ecija Government Building</td>
                                        <td>Nueva Ecija LGU</td>
                                        <td>Aug 20, 2023</td>
                                        <td>Nov 30, 2023</td>
                                        <td><span class="status progress">In Progress</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Clients -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Recent Clients</h2>
                        <a href="#" class="card-action">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Client Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Projects</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Antonio Dela Cruz</td>
                                        <td>antonio.dc@example.com</td>
                                        <td>0917-123-4567</td>
                                        <td>2</td>
                                        <td>Oct 15, 2023</td>
                                    </tr>
                                    <tr>
                                        <td>Elena Rodriguez</td>
                                        <td>elena.r@example.com</td>
                                        <td>0918-987-6543</td>
                                        <td>1</td>
                                        <td>Oct 12, 2023</td>
                                    </tr>
                                    <tr>
                                        <td>Roberto Santiago</td>
                                        <td>roberto.s@example.com</td>
                                        <td>0919-555-1234</td>
                                        <td>3</td>
                                        <td>Oct 10, 2023</td>
                                    </tr>
                                    <tr>
                                        <td>Marisol Hernandez</td>
                                        <td>marisol.h@example.com</td>
                                        <td>0916-777-8888</td>
                                        <td>1</td>
                                        <td>Oct 8, 2023</td>
                                    </tr>
                                    <tr>
                                        <td>Francisco Lim</td>
                                        <td>francisco.l@example.com</td>
                                        <td>0915-222-3333</td>
                                        <td>2</td>
                                        <td>Oct 5, 2023</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    <?php if (!empty($welcome_message)): ?>
        Swal.fire({
            icon: 'info',
            title: 'Welcome Admin',
            text: '<?php echo addslashes($welcome_message); ?>',
            timer: 3000,
            confirmButtonColor: '#3B71CA'
        });
    <?php endif; ?>
    </script>

</body>
</html>