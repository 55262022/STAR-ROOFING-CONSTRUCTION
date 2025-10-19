<?php
include '../authentication/auth.php';
require_once '../database/starroofing_db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roof Types Management - Star Roofing & Construction</title>
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- CSS style -->
    <link rel="stylesheet" href="../css/admin_main.css">
    <style>
        /* Your existing CSS styles */        
        .user-profile {
            position: relative;
            cursor: pointer;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .user-name {
            font-weight: 500;
        }
        
        .user-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            width: 200px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            z-index: 100;
            margin-top: 10px;
        }
        
        .user-dropdown.active {
            display: block;
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #2c3e50;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .dropdown-item:hover {
            background-color: #f8f9fa;
        }
        
        .dropdown-item i {
            margin-right: 10px;
            width: 16px;
            text-align: center;
        }
        
        .dropdown-divider {
            height: 1px;
            background-color: #eee;
            margin: 5px 0;
        }
        
        .rooftype-content {
            flex: 1;
            padding: 30px;
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

        /* Search Form */
        .search-form {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        /* Search Input */
        .search-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .search-input:focus {
            border-color: #007bff;
        }

        .search-btn {
            padding: 10px 18px;
            background-color: #3498db;
            border: none;
            border-radius: 6px;
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background-color 0.2s ease;
        }

        .search-btn:hover {
            background-color: #2980b9;
        }
        
        /* Table styles */
        .rooftype-table table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .rooftype-table th, .rooftype-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .rooftype-table th {
            background-color: #3498db;
            color: white;
            font-weight: 600;
        }

        .rooftype-table tr:hover {
            background-color: #f9f9f9;
        }

        .rooftype-table img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }

        /* Pagination */
        .pagination {
            margin-top: 20px;
            text-align: center;
        }

        .page-btn {
            display: inline-block;
            margin: 0 5px;
            padding: 8px 14px;
            border-radius: 6px;
            border: 1px solid #3498db;
            color: #3498db;
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }

        .page-btn:hover {
            background-color: #3498db;
            color: white;
        }

        .page-btn.active {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }
        
        .placeholder {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            color: #bdc3c7;
            font-size: 40px;
        }        
        
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
            color: #34495e;
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
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
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
            
            <!-- Roof Types Content -->
            <div class="rooftype-content">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Roof Types Management</h1>
                        <p class="page-description">Manage your roof types and specifications</p>
                    </div>
                    <button class="btn btn-primary" id="addRoofTypeBtn">
                        <i class="fas fa-plus"></i> Add New Roof Type
                    </button>
                </div>

                <!-- Roof Types Table -->
                <div class="rooftype-container">
                    <div class="rooftype-table">
                        <?php
                        $query = "SELECT * FROM roof_types";
                        $result = $conn->query($query);
                        ?>
                        
                        <?php if ($result && $result->num_rows > 0): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Type Name</th>
                                        <th>Description</th>
                                        <th>Steel/sqm</th>
                                        <th>Screw/sqm</th>
                                        <th>Paint/sqm</th>
                                        <th>Slope %</th>
                                        <th>Image</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['id']) ?></td>
                                            <td><?= htmlspecialchars($row['type_name']) ?></td>
                                            <td><?= htmlspecialchars($row['description']) ?></td>
                                            <td><?= htmlspecialchars($row['steel_per_sqm']) ?></td>
                                            <td><?= htmlspecialchars($row['screw_per_sqm']) ?></td>
                                            <td><?= htmlspecialchars($row['paint_per_sqm']) ?></td>
                                            <td><?= htmlspecialchars($row['slope_percentage']) ?></td>
                                            <td>
                                                <?php if (!empty($row['image_path'])): ?>
                                                    <img src="../<?= htmlspecialchars($row['image_path']) ?>" width="50" height="50">
                                                <?php else: ?>
                                                    No Image
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-warning edit-btn" 
                                                        data-id="<?= $row['id'] ?>"
                                                        data-type_name="<?= htmlspecialchars($row['type_name']) ?>"
                                                        data-description="<?= htmlspecialchars($row['description']) ?>"
                                                        data-steel="<?= $row['steel_per_sqm'] ?>"
                                                        data-screw="<?= $row['screw_per_sqm'] ?>"
                                                        data-paint="<?= $row['paint_per_sqm'] ?>"
                                                        data-slope="<?= $row['slope_percentage'] ?>"
                                                        data-image="<?= htmlspecialchars($row['image_path']) ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="btn btn-danger delete-btn" 
                                                        data-id="<?= $row['id'] ?>" 
                                                        data-name="<?= htmlspecialchars($row['type_name']) ?>">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No roof types found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Roof Type Modal -->
    <div class="modal" id="addRoofTypeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Add New Roof Type</h2>
                <button class="modal-close" id="closeAddModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addRoofTypeForm" method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="addTypeName">Type Name</label>
                            <input type="text" id="addTypeName" name="type_name" required>
                        </div>
                        <div class="form-group">
                            <label for="addSlopePercentage">Slope (%)</label>
                            <input type="number" id="addSlopePercentage" name="slope_percentage" step="0.1" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="addDescription">Description</label>
                        <textarea id="addDescription" name="description" rows="2" required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="addSteelPerSqm">Steel per sqm</label>
                            <input type="number" id="addSteelPerSqm" name="steel_per_sqm" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="addScrewPerSqm">Screw per sqm</label>
                            <input type="number" id="addScrewPerSqm" name="screw_per_sqm" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="addPaintPerSqm">Paint per sqm</label>
                            <input type="number" id="addPaintPerSqm" name="paint_per_sqm" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="addImagePath">Upload Image</label>
                        <input type="file" id="addImagePath" name="image_path" accept="image/*" required>
                        <img id="addPreviewImage" style="display:none; max-width:150px; margin-top:10px;">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelAddBtn">Cancel</button>
                <button class="btn btn-primary" id="saveRoofTypeBtn">Save Roof Type</button>
            </div>
        </div>
    </div>

    <!-- Edit Roof Type Modal -->
    <div class="modal" id="editRoofTypeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Edit Roof Type</h2>
                <button class="modal-close" id="closeEditModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editRoofTypeForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="editId" name="id">
                    <input type="hidden" id="editExistingImage" name="existing_image">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editTypeName">Type Name</label>
                            <input type="text" id="editTypeName" name="type_name" required>
                        </div>
                        <div class="form-group">
                            <label for="editSlopePercentage">Slope (%)</label>
                            <input type="number" id="editSlopePercentage" name="slope_percentage" step="0.1" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="editDescription">Description</label>
                        <textarea id="editDescription" name="description" rows="2" required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editSteelPerSqm">Steel per sqm</label>
                            <input type="number" id="editSteelPerSqm" name="steel_per_sqm" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="editScrewPerSqm">Screw per sqm</label>
                            <input type="number" id="editScrewPerSqm" name="screw_per_sqm" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="editPaintPerSqm">Paint per sqm</label>
                            <input type="number" id="editPaintPerSqm" name="paint_per_sqm" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="editImagePath">Upload Image</label>
                        <input type="file" id="editImagePath" name="image_path" accept="image/*">
                        <img id="editPreviewImage" style="display:none; max-width:150px; margin-top:10px; border:1px solid #ddd; border-radius:6px;">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelEditBtn">Cancel</button>
                <button class="btn btn-primary" id="updateRoofTypeBtn">Update Roof Type</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Confirm Delete</h2>
                <button class="modal-close" id="closeDeleteModal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteRoofTypeName"></strong>? This action cannot be undone.</p>
                <form id="deleteForm" method="POST">
                    <input type="hidden" name="id" id="deleteRoofTypeId">
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelDeleteBtn">Cancel</button>
                <button class="btn btn-danger" id="confirmDeleteBtn">Delete Roof Type</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal elements
            const addRoofTypeBtn = document.getElementById('addRoofTypeBtn');
            const addRoofTypeModal = document.getElementById('addRoofTypeModal');
            const closeAddModal = document.getElementById('closeAddModal');
            const cancelAddBtn = document.getElementById('cancelAddBtn');
            
            const editRoofTypeModal = document.getElementById('editRoofTypeModal');
            const closeEditModal = document.getElementById('closeEditModal');
            const cancelEditBtn = document.getElementById('cancelEditBtn');
            
            const deleteModal = document.getElementById('deleteModal');
            const closeDeleteModal = document.getElementById('closeDeleteModal');
            const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
            
            // Add Roof Type Modal
            if (addRoofTypeBtn) {
                addRoofTypeBtn.addEventListener('click', () => {
                    document.getElementById('addRoofTypeForm').reset();
                    addRoofTypeModal.classList.add('active');
                });
            }
            
            if (closeAddModal) {
                closeAddModal.addEventListener('click', () => {
                    addRoofTypeModal.classList.remove('active');
                });
            }
            
            if (cancelAddBtn) {
                cancelAddBtn.addEventListener('click', () => {
                    addRoofTypeModal.classList.remove('active');
                });
            }
            
            // Edit Roof Type Modal
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const typeName = this.getAttribute('data-type_name');
                    const description = this.getAttribute('data-description');
                    const steel = this.getAttribute('data-steel');
                    const screw = this.getAttribute('data-screw');
                    const paint = this.getAttribute('data-paint');
                    const slope = this.getAttribute('data-slope');
                    const image = this.getAttribute('data-image');
                    
                    document.getElementById('editId').value = id;
                    document.getElementById('editTypeName').value = typeName;
                    document.getElementById('editDescription').value = description;
                    document.getElementById('editSteelPerSqm').value = steel;
                    document.getElementById('editScrewPerSqm').value = screw;
                    document.getElementById('editPaintPerSqm').value = paint;
                    document.getElementById('editSlopePercentage').value = slope;
                    document.getElementById('editExistingImage').value = image;
                    
                    // Show existing image preview
                    const previewImg = document.getElementById('editPreviewImage');
                    if (image && image !== "") {
                        previewImg.src = '../' + image;
                        previewImg.style.display = 'block';
                    } else {
                        previewImg.style.display = 'none';
                    }
                    
                    editRoofTypeModal.classList.add('active');
                });
            });
            
            if (closeEditModal) {
                closeEditModal.addEventListener('click', () => {
                    editRoofTypeModal.classList.remove('active');
                });
            }
            
            if (cancelEditBtn) {
                cancelEditBtn.addEventListener('click', () => {
                    editRoofTypeModal.classList.remove('active');
                });
            }
            
            // Delete Confirmation Modal
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    
                    document.getElementById('deleteRoofTypeName').textContent = name;
                    document.getElementById('deleteRoofTypeId').value = id;
                    deleteModal.classList.add('active');
                });
            });
            
            if (closeDeleteModal) {
                closeDeleteModal.addEventListener('click', () => {
                    deleteModal.classList.remove('active');
                });
            }
            
            if (cancelDeleteBtn) {
                cancelDeleteBtn.addEventListener('click', () => {
                    deleteModal.classList.remove('active');
                });
            }
            
            // Image Preview
            const addImageInput = document.getElementById('addImagePath');
            const addPreview = document.getElementById('addPreviewImage');
            if (addImageInput) {
                addImageInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = e => {
                            addPreview.src = e.target.result;
                            addPreview.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            const editImageInput = document.getElementById('editImagePath');
            const editPreview = document.getElementById('editPreviewImage');
            if (editImageInput) {
                editImageInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = e => {
                            editPreview.src = e.target.result;
                            editPreview.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            // Form submissions
            document.getElementById('saveRoofTypeBtn').addEventListener('click', function() {
                const formData = new FormData(document.getElementById('addRoofTypeForm'));
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to add this roof type?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3498db',
                    cancelButtonColor: '#e74c3c',
                    confirmButtonText: 'Yes, add it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('crud/insert_roof_type.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Added!',
                                    text: data.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.message
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to add roof type.'
                            });
                        });
                    }
                });
            });
            
            document.getElementById('updateRoofTypeBtn').addEventListener('click', function() {
                const formData = new FormData(document.getElementById('editRoofTypeForm'));
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to update this roof type?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3498db',
                    cancelButtonColor: '#e74c3c',
                    confirmButtonText: 'Yes, update it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('crud/update_roof_type.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Updated!',
                                    text: data.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.message
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to update roof type.'
                            });
                        });
                    }
                });
            });
            
            document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
                const id = document.getElementById('deleteRoofTypeId').value;
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This roof type will be permanently deleted!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e74c3c',
                    cancelButtonColor: '#3498db',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('crud/delete_roof_type.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'id=' + encodeURIComponent(id)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: data.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.message
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to delete roof type.'
                            });
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>