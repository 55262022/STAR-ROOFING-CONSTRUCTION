<?php
include '../authentication/auth.php';
require_once '../database/starroofing_db.php';

// Fetch projects from database
$projects = [];
$result = $conn->query("SELECT * FROM projects ORDER BY created_at DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
}

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
    <title>Project Monitoring & 3D Configurator - Star Roofing & Construction</title>
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Three.js -->
    <script src="https://cdn.jsdelivr.net/npm/three@0.132.2/build/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.132.2/examples/js/controls/OrbitControls.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- CSS style -->
    <link rel="stylesheet" href="../css/admin_main.css">
    <style>
        /* Dashboard Content */
        .dashboard-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 5px 0;
        }
        
        .page-description {
            color: #7f8c8d;
            margin: 0;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-warning {
            background-color: #dce73cff;
            color: black;
        }
        
        .btn-warning:hover {
            background-color: #b9c331ff;
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid #bdc3c7;
            color: #7f8c8d;
        }
        
        .btn-outline:hover {
            background-color: #f8f9fa;
        }
        
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 24px;
        }
        
        .stat-icon.projects {
            background: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }
        
        .stat-icon.progress {
            background: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
        }
        
        .stat-icon.completed {
            background: rgba(241, 196, 15, 0.1);
            color: #f1c40f;
        }
        
        .stat-icon.issues {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }
        
        .stat-info h3 {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stat-info p {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        /* Project Controls */
        .project-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .filter-controls {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .filter-select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: white;
            font-family: 'Montserrat', sans-serif;
            font-size: 14px;
        }
        
        /* Card Styles */
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .card-header {
            padding: 20px 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .card-action {
            color: #3498db;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        
        .card-body {
            padding: 25px;
        }
        
        /* Progress Bars */
        .progress-item {
            margin-bottom: 20px;
        }
        
        .progress-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .progress-label {
            font-weight: 500;
            color: #2c3e50;
        }
        
        .progress-percent {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .progress-bar {
            height: 10px;
            background: #ecf0f1;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            border-radius: 5px;
            transition: width 0.5s ease;
        }
        
        .progress-fill.foundation {
            background: #3498db;
            width: 85%;
        }
        
        .progress-fill.structure {
            background: #2ecc71;
            width: 75%;
        }
        
        .progress-fill.roofing {
            background: #f39c12;
            width: 60%;
        }
        
        .progress-fill.finishing {
            background: #9b59b6;
            width: 30%;
        }
        
        /* Image Gallery */
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .gallery-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            height: 150px;
            cursor: pointer;
            transition: transform 0.3s;
        }
        
        .gallery-item:hover {
            transform: scale(1.05);
        }
        
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 8px;
            font-size: 12px;
        }
        
        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        thead th {
            background-color: #3498db;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        tbody td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            color: #2c3e50;
        }
        
        tbody tr:hover {
            background-color: #f9f9f9;
        }
        
        .status {
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status.completed {
            background-color: #e8f6f3;
            color: #1abc9c;
        }
        
        .status.pending {
            background-color: #fef9e7;
            color: #f1c40f;
        }
        
        .status.progress {
            background-color: #e3f2fd;
            color: #3498db;
        }
        
        .status.delayed {
            background-color: #fdedec;
            color: #e74c3c;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background-color: white;
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .modal-title {
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #7f8c8d;
            transition: color 0.3s;
        }
        
        .modal-close:hover {
            color: #34495e;
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .modal-image {
            width: 100%;
            max-height: 500px;
            object-fit: contain;
            border-radius: 8px;
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            transition: border-color 0.3s;
            font-family: 'Montserrat', sans-serif;
        }
        
        input:focus, select:focus, textarea:focus {
            border-color: #3498db;
            outline: none;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .project-controls {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filter-controls {
                width: 100%;
                flex-wrap: wrap;
            }
            
            
            .main-layout {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
            }
            
            .controls-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            

        }
    </style>
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
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Project Monitoring</h1>
                        <p class="page-description">Track construction progress and manage project details</p>
                    </div>
                    <button class="btn btn-primary" id="addProjectBtn">
                        <i class="fas fa-plus"></i> New Project
                    </button>
                </div>
                
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon projects">
                            <i class="fas fa-hard-hat"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= count($projects) ?></h3>
                            <p>Total Projects</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon progress">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="stat-info">
                            <h3>
                                <?php 
                                $avg = array_reduce($projects, function($carry, $project) { 
                                    return $carry + ($project['progress'] ?? 0); 
                                }, 0) / (count($projects) ?: 1);

                                // Clamp between 0 and 100
                                $avg = max(0, min($avg, 100));

                                // Format as number with 2 decimal places
                                echo number_format($avg, 2) . '%';
                                ?>
                            </h3>
                            <p>Average Progress</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon completed">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= count(array_filter($projects, function($project) { 
                                return $project['status'] === 'Completed'; 
                            })) ?></h3>
                            <p>Completed Projects</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon issues">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= count(array_filter($projects, function($project) { 
                                return $project['status'] === 'Delayed'; 
                            })) ?></h3>
                            <p>Delayed Projects</p>
                        </div>
                    </div>
                </div>
                
                <!-- Project Controls -->
                <div class="project-controls">
                    <div class="filter-controls">
                        <select class="filter-select" id="statusFilter">
                            <option value="all">All Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Completed">Completed</option>
                            <option value="Delayed">Delayed</option>
                        </select>
                        <select class="filter-select" id="progressFilter">
                            <option value="all">All Progress</option>
                            <option value="0-25">0-25%</option>
                            <option value="26-50">26-50%</option>
                            <option value="51-75">51-75%</option>
                            <option value="76-100">76-100%</option>
                        </select>
                    </div>
                    <div class="search-form">
                        <input type="text" id="projectSearch" placeholder="Search projects..." class="search-input">
                        <button class="search-btn" id="searchBtn">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>

                <!-- Projects Table -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Projects List</h2>
                        <a href="#" class="card-action" id="exportProjects">Export Report</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="projectsTable">
                                <thead>
                                    <tr>
                                        <th>Project Code</th>
                                        <th>Project Name</th>
                                        <th>Client Name</th>
                                        <th>Budget</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Progress</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($projects) > 0): ?>
                                        <?php foreach ($projects as $project): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($project['project_code']) ?></td>
                                                <td><?= htmlspecialchars($project['project_name']) ?></td>
                                                <td><?= htmlspecialchars($project['client_name']) ?></td>
                                                <td>₱<?= number_format($project['budget'], 2) ?></td>
                                                <td><?= date('M d, Y', strtotime($project['start_date'])) ?></td>
                                                <td><?= date('M d, Y', strtotime($project['end_date'])) ?></td>
                                                <td>
                                                    <div class="progress-bar">
                                                        <div class="progress-fill" style="width: <?= $project['progress'] ?>%; background-color: 
                                                            <?= $project['progress'] >= 75 ? '#2ecc71' : 
                                                               ($project['progress'] >= 50 ? '#3498db' : 
                                                               ($project['progress'] >= 25 ? '#f39c12' : '#e74c3c')) ?>">
                                                        </div>
                                                    </div>
                                                    <small><?= $project['progress'] ?>%</small>
                                                </td>
                                                <td>
                                                    <span class="status <?= strtolower($project['status']) ?>">
                                                        <?= $project['status'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-warning edit-project-btn" data-id="<?= $project['project_id'] ?>">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <button class="btn btn-danger delete-project-btn" 
                                                            data-id="<?= $project['project_id'] ?>" 
                                                            data-name="<?= htmlspecialchars($project['project_name']) ?>">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" style="text-align: center; padding: 20px;">
                                                No projects found. <a href="#" id="addFirstProject">Add your first project</a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Progress Images -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Progress Photo Gallery</h2>
                        <a href="#" class="card-action">View All Images</a>
                    </div>
                    <div class="card-body">
                        <div class="image-gallery">
                            <div class="gallery-item" data-image="foundation.jpg">
                                <img src="https://images.unsplash.com/photo-1581091226033-d5c48150dbaa?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Foundation Work">
                                <div class="gallery-overlay">Foundation (Jan 2025)</div>
                            </div>
                            <div class="gallery-item" data-image="structure.jpg">
                                <img src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Structural Work">
                                <div class="gallery-overlay">Structural (Feb 2025)</div>
                            </div>
                            <div class="gallery-item" data-image="roof-framing.jpg">
                                <img src="https://images.unsplash.com/photo-1586023492125-27a2dfa0e5e3?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Roof Framing">
                                <div class="gallery-overlay">Roof Framing (Mar 2025)</div>
                            </div>
                            <div class="gallery-item" data-image="electrical.jpg">
                                <img src="https://images.unsplash.com/photo-1594223274512-ad4803739b7c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Electrical Work">
                                <div class="gallery-overlay">Electrical (Mar 2025)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal" id="imageModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Progress Image</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <img id="modalImage" src="" alt="Progress Image" class="modal-image">
                <div style="margin-top: 20px;">
                    <p><strong>Date Taken:</strong> <span id="imageDate">March 15, 2025</span></p>
                    <p><strong>Description:</strong> <span id="imageDesc">Concrete pouring for second floor completed</span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="closeImageModal">Close</button>
            </div>
        </div>
    </div>

    <!-- Add Project Modal -->
    <div class="modal" id="addProjectModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Add New Project</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addProjectForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="projectCode">Project Code *</label>
                            <input type="text" id="projectCode" name="project_code" required>
                        </div>
                        <div class="form-group">
                            <label for="projectName">Project Name *</label>
                            <input type="text" id="projectName" name="project_name" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="projectDescription">Description</label>
                        <textarea id="projectDescription" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="clientName">Client Name *</label>
                            <input type="text" id="clientName" name="client_name" required>
                        </div>
                        <div class="form-group">
                            <label for="clientEmail">Client Email</label>
                            <input type="email" id="clientEmail" name="client_email">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="clientPhone">Client Phone</label>
                            <input type="text" id="clientPhone" name="client_phone">
                        </div>
                        <div class="form-group">
                            <label for="projectAddress">Address</label>
                            <input type="text" id="projectAddress" name="address">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="projectBudget">Budget (₱)</label>
                            <input type="number" id="projectBudget" name="budget" step="0.01">
                        </div>
                        <div class="form-group">
                            <label for="projectCost">Actual Cost (₱)</label>
                            <input type="number" id="projectCost" name="actual_cost" step="0.01">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="startDate">Start Date</label>
                            <input type="date" id="startDate" name="start_date">
                        </div>
                        <div class="form-group">
                            <label for="endDate">End Date</label>
                            <input type="date" id="endDate" name="end_date">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="projectStatus">Status</label>
                            <select id="projectStatus" name="status">
                                <option value="Pending">Pending</option>
                                <option value="Ongoing">Ongoing</option>
                                <option value="Completed">Completed</option>
                                <option value="Delayed">Delayed</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="projectProgress">Progress (%)</label>
                            <input type="number" id="projectProgress" name="progress" min="0" max="100" step="1" value="0">
                        </div>
                    </div>
                    
                    <div class="form-group">
                    <label for="project_manager_id">Project Manager</label>
                    <select id="project_manager_id" name="project_manager_id" required>
                        <option value="">Select Project Manager</option>
                    </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelAddProject">Cancel</button>
                <button class="btn btn-primary" id="saveProjectBtn">Save Project</button>
            </div>
        </div>
    </div>

    <!-- Upload Image Modal -->
    <div class="modal" id="uploadImageModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Upload Progress Image</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="uploadImageForm">
                    <div class="form-group">
                        <label for="imageFile">Select Image *</label>
                        <input type="file" id="imageFile" name="image" accept="image/*" required>
                    </div>
                    <div class="form-group">
                        <label for="imageDescription">Description</label>
                        <textarea id="imageDescription" name="description" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="imageDate">Date Taken</label>
                        <input type="date" id="imageDateTaken" name="date_taken" value="<?= date('Y-m-d') ?>">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelUpload">Cancel</button>
                <button class="btn btn-primary" id="uploadImageBtnConfirm">Upload</button>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Add Project Modal ---
    const addProjectBtn = document.getElementById('addProjectBtn');
    const addProjectModal = document.getElementById('addProjectModal');
    const cancelAddProject = document.getElementById('cancelAddProject');
    const closeAddProject = addProjectModal.querySelector('.modal-close');
    const saveProjectBtn = document.getElementById('saveProjectBtn');
    const addProjectForm = document.getElementById('addProjectForm');

    if (addProjectBtn) {
        addProjectBtn.addEventListener('click', () => addProjectModal.classList.add('active'));
    }

    if (closeAddProject) {
        closeAddProject.addEventListener('click', () => addProjectModal.classList.remove('active'));
    }

    if (cancelAddProject) {
        cancelAddProject.addEventListener('click', () => addProjectModal.classList.remove('active'));
    }

    // --- Upload Image Modal ---
    const uploadImageBtn = document.getElementById('uploadImageBtn');
    const uploadImageModal = document.getElementById('uploadImageModal');
    const cancelUpload = document.getElementById('cancelUpload');
    const closeUpload = uploadImageModal ? uploadImageModal.querySelector('.modal-close') : null;

    if (uploadImageBtn && uploadImageModal) {
        uploadImageBtn.addEventListener('click', () => uploadImageModal.classList.add('active'));
    }

    if (closeUpload) {
        closeUpload.addEventListener('click', () => uploadImageModal.classList.remove('active'));
    }

    if (cancelUpload) {
        cancelUpload.addEventListener('click', () => uploadImageModal.classList.remove('active'));
    }

    // --- Gallery Modal ---
    const imageModal = document.getElementById('imageModal');
    const closeImageModal = document.getElementById('closeImageModal');
    const modalImage = document.getElementById('modalImage');
    const imageDate = document.getElementById('imageDate');
    const imageDesc = document.getElementById('imageDesc');

    document.querySelectorAll('.gallery-item').forEach(item => {
        item.addEventListener('click', function() {
            const imgSrc = this.querySelector('img').src;
            const caption = this.querySelector('.gallery-overlay').textContent;
            
            modalImage.src = imgSrc;
            imageDate.textContent = caption.match(/\(([^)]+)\)/)[1];
            imageDesc.textContent = 'Progress image showing ' + caption.split('(')[0].trim();
            
            imageModal.classList.add('active');
        });
    });

    if (closeImageModal) {
        closeImageModal.addEventListener('click', () => imageModal.classList.remove('active'));
    }

    // Close modals when clicking outside
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.classList.remove('active');
        });
    });

    // --- Save Project (Actual DB Saving) ---
    saveProjectBtn.addEventListener('click', async function() {
        const form = addProjectForm;
        const formData = new FormData(form);

        // Basic validation
        const projectCode = document.getElementById('projectCode').value.trim();
        const projectName = document.getElementById('projectName').value.trim();
        const clientName = document.getElementById('clientName').value.trim();

        if (!projectCode || !projectName || !clientName) {
            Swal.fire('Error', 'Please fill in all required fields', 'error');
            return;
        }

        // Loading alert
        Swal.fire({
            title: 'Saving Project...',
            text: 'Please wait while we save your data.',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const response = await fetch('crud/add_project.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.text();
            Swal.close();

            if (result.trim() === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Project Added!',
                    text: 'The project has been successfully saved.',
                    timer: 2000,
                    showConfirmButton: false
                });

                addProjectModal.classList.remove('active');
                form.reset();
                setTimeout(() => location.reload(), 2000);
            } else {
                Swal.fire('Error', result, 'error');
            }
        } catch (error) {
            Swal.close();
            Swal.fire('Error', 'Failed to save project: ' + error.message, 'error');
        }
    });

    // Load admins for project manager selection
    async function loadAdmins() {
        try {
            const res = await fetch('crud/get_admins.php');
            const data = await res.json();
            const select = document.getElementById('project_manager_id');
            select.innerHTML = '<option value="">Select Project Manager</option>';
            if (data.success) {
                data.data.forEach(admin => {
                    select.innerHTML += `<option value="${admin.id}">${admin.email}</option>`;
                });
            }
        } catch (err) {
            console.error('Failed to load admins:', err);
        }
    }

    loadAdmins();

    // --- Upload Image Confirm ---
    const uploadImageBtnConfirm = document.getElementById('uploadImageBtnConfirm');
    if (uploadImageBtnConfirm) {
        uploadImageBtnConfirm.addEventListener('click', function() {
            const form = document.getElementById('uploadImageForm');
            const fileInput = document.getElementById('imageFile');
            
            if (!fileInput.files[0]) {
                Swal.fire('Error', 'Please select an image file', 'error');
                return;
            }

            Swal.fire({
                title: 'Uploading Image...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            setTimeout(() => {
                Swal.close();
                Swal.fire('Success', 'Image uploaded successfully!', 'success');
                uploadImageModal.classList.remove('active');
                form.reset();
            }, 1500);
        });
    }

    // --- Filter functionality ---
    const statusFilter = document.getElementById('statusFilter');
    const progressFilter = document.getElementById('progressFilter');
    const projectSearch = document.getElementById('projectSearch');
    const searchBtn = document.getElementById('searchBtn');

    function filterProjects() {
        const statusValue = statusFilter.value;
        const progressValue = progressFilter.value;
        const searchValue = projectSearch.value.toLowerCase();
        
        const rows = document.querySelectorAll('#projectsTable tbody tr');
        
        rows.forEach(row => {
            const status = row.cells[7].textContent.trim();
            const progress = parseInt(row.cells[6].querySelector('small').textContent);
            const projectName = row.cells[1].textContent.toLowerCase();
            const clientName = row.cells[2].textContent.toLowerCase();
            const projectCode = row.cells[0].textContent.toLowerCase();
            
            let statusMatch = statusValue === 'all' || status === statusValue;
            let progressMatch = progressValue === 'all' || 
                (progressValue === '0-25' && progress >= 0 && progress <= 25) ||
                (progressValue === '26-50' && progress >= 26 && progress <= 50) ||
                (progressValue === '51-75' && progress >= 51 && progress <= 75) ||
                (progressValue === '76-100' && progress >= 76 && progress <= 100);
            let searchMatch = searchValue === '' || 
                projectName.includes(searchValue) || 
                clientName.includes(searchValue) ||
                projectCode.includes(searchValue);
            
            if (statusMatch && progressMatch && searchMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    statusFilter.addEventListener('change', filterProjects);
    progressFilter.addEventListener('change', filterProjects);
    searchBtn.addEventListener('click', filterProjects);
    projectSearch.addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            filterProjects();
        }
    });

    // --- Delete Project Functionality ---
    document.querySelectorAll('.delete-project-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const projectId = this.getAttribute('data-id');
            const projectName = this.getAttribute('data-name');
            
            Swal.fire({
                title: 'Delete Project?',
                text: `Are you sure you want to delete "${projectName}"? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#7f8c8d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send delete request
                    fetch(`crud/delete_project.php?id=${projectId}`)
                        .then(response => response.text())
                        .then(result => {
                            if (result.trim() === 'success') {
                                Swal.fire(
                                    'Deleted!',
                                    'Project has been deleted.',
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    'Failed to delete project.',
                                    'error'
                                );
                            }
                        })
                        .catch(error => {
                            Swal.fire(
                                'Error!',
                                'Failed to delete project: ' + error,
                                'error'
                            );
                        });
                }
            });
        });
    });

    // --- Edit Project Functionality ---
    document.querySelectorAll('.edit-project-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const projectId = this.getAttribute('data-id');
            // Redirect to edit page or open edit modal
            window.location.href = `edit_project.php?id=${projectId}`;
        });
    });

    // --- Add First Project Link ---
    const addFirstProject = document.getElementById('addFirstProject');
    if (addFirstProject) {
        addFirstProject.addEventListener('click', function(e) {
            e.preventDefault();
            addProjectModal.classList.add('active');
        });
    }
});

</script>

</body>
</html>