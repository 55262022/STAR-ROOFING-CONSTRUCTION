<?php
include '../authentication/auth.php';
require_once '../database/starroofing_db.php';

// Initialize variables for filters
$search_term = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? 'all';
$date_filter = $_GET['date'] ?? 'all';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build query with filters
$query = "SELECT id, firstname, lastname, email, message, created_at, is_read, is_replied 
          FROM contact_messages 
          WHERE is_archived = 0";

$count_query = "SELECT COUNT(*) as total 
                FROM contact_messages 
                WHERE is_archived = 0";

// Status filter
if ($status_filter !== 'all') {
    switch ($status_filter) {
        case 'unread':
            $query .= " AND is_read = 0";
            $count_query .= " AND is_read = 0";
            break;
        case 'read':
            $query .= " AND is_read = 1";
            $count_query .= " AND is_read = 1";
            break;
        case 'replied':
            $query .= " AND is_replied = 1";
            $count_query .= " AND is_replied = 1";
            break;
        case 'not_replied':
            $query .= " AND is_replied = 0";
            $count_query .= " AND is_replied = 0";
            break;
    }
}

// Date filter
if ($date_filter !== 'all') {
    $date_condition = "";
    switch ($date_filter) {
        case 'today':
            $date_condition = "DATE(created_at) = CURDATE()";
            break;
        case 'yesterday':
            $date_condition = "DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
            break;
        case 'week':
            $date_condition = "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $date_condition = "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
        case 'older':
            $date_condition = "created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
    }
    if ($date_condition) {
        $query .= " AND $date_condition";
        $count_query .= " AND $date_condition";
    }
}

// Search term
if (!empty($search_term)) {
    $search_like = "%$search_term%";
    $query .= " AND (firstname LIKE ? OR lastname LIKE ? OR email LIKE ? OR message LIKE ?)";
    $count_query .= " AND (firstname LIKE ? OR lastname LIKE ? OR email LIKE ? OR message LIKE ?)";
}

$query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";

// Get total count
$stmt_count = $conn->prepare($count_query);
if (!empty($search_term)) {
    $stmt_count->bind_param("ssss", $search_like, $search_like, $search_like, $search_like);
}
$stmt_count->execute();
$count_result = $stmt_count->get_result();
$total_messages = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_messages / $limit);

// Get messages
$stmt = $conn->prepare($query);
if (!empty($search_term)) {
    $stmt->bind_param("ssssii", $search_like, $search_like, $search_like, $search_like, $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - Star Roofing & Construction</title>
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS style -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../css/admin_main.css">
  <style>
        /* Base styles and reset */        
        .messages-content {
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
        
        .btn-outline {
            background-color: #dce73cff;
            color: black;
        }
        
        .btn-outline:hover {
            background-color: #b9c331ff;
        }
        
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }

        /* Gmail-style Search Bar */
        .search-container {
            margin-bottom: 20px;
            position: relative;
        }

        .search-box {
            display: flex;
            align-items: center;
            background: white;
            border-radius: 24px;
            padding: 8px 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e0e0e0;
        }

        .search-box:focus-within {
            box-shadow: 0 2px 12px rgba(0,0,0,0.15);
            border-color: #3498db;
        }

        .search-icon {
            color: #5f6368;
            margin-right: 12px;
        }

        .search-input {
            flex: 1;
            border: none;
            outline: none;
            padding: 8px 0;
            font-size: 14px;
            background: transparent;
        }

        .search-actions {
            display: flex;
            gap: 8px;
            margin-left: 12px;
        }

        .search-action-btn {
            background: none;
            border: none;
            padding: 8px;
            border-radius: 50%;
            cursor: pointer;
            color: #5f6368;
            transition: all 0.2s;
        }

        .search-action-btn:hover {
            background-color: #f1f3f4;
        }

        /* Gmail-style Filter Chips */
        .filter-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 20px;
            align-items: center;
        }

        .filter-chip {
            display: inline-flex;
            align-items: center;
            background: #e8f0fe;
            color: #1a73e8;
            padding: 6px 12px;
            border-radius: 16px;
            font-size: 13px;
            font-weight: 500;
            gap: 8px;
        }

        .filter-chip.active {
            background: #1a73e8;
            color: white;
        }

        .filter-chip .remove {
            cursor: pointer;
            padding: 2px;
            border-radius: 50%;
            transition: background 0.2s;
        }

        .filter-chip .remove:hover {
            background: rgba(0,0,0,0.1);
        }

        .filter-chip.active .remove:hover {
            background: rgba(255,255,255,0.2);
        }

        .more-filters-btn {
            background: none;
            border: 1px solid #dadce0;
            padding: 6px 16px;
            border-radius: 16px;
            cursor: pointer;
            font-size: 13px;
            color: #5f6368;
            transition: all 0.2s;
        }

        .more-filters-btn:hover {
            background: #f8f9fa;
            border-color: #3498db;
        }

        /* Filter Dropdown */
        .filter-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
            padding: 16px;
            min-width: 200px;
            z-index: 1000;
            margin-top: 8px;
            display: none;
        }

        .filter-dropdown.active {
            display: block;
        }

        .filter-section {
            margin-bottom: 16px;
        }

        .filter-section:last-child {
            margin-bottom: 0;
        }

        .filter-section h4 {
            font-size: 14px;
            color: #5f6368;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .filter-option {
            display: flex;
            align-items: center;
            padding: 8px 0;
            cursor: pointer;
            font-size: 14px;
            color: #202124;
            transition: background 0.2s;
            border-radius: 4px;
            padding-left: 8px;
        }

        .filter-option:hover {
            background: #f8f9fa;
        }

        .filter-option.active {
            color: #1a73e8;
            font-weight: 500;
        }

        .filter-option input {
            margin-right: 12px;
        }

        /* Table styles */
        .messages-table table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .messages-table th, .messages-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .messages-table th {
            background-color: #3498db;
            color: white;
            font-weight: 600;
        }

        .messages-table tr:hover {
            background-color: #f9f9f9;
        }

        .message-unread {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .message-unread .message-sender {
            color: #1a365d;
        }

        /* Pagination */
        .pagination {
            margin-top: 20px;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
        }

        .page-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #3498db;
            color: #3498db;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
            min-width: 40px;
            height: 40px;
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

        .page-ellipsis {
            padding: 8px 12px;
            color: #7f8c8d;
            font-weight: bold;
        }
        
        .message-preview {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .message-sender {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 5px 0;
        }
        
        .message-email {
            color: #7f8c8d;
            font-size: 14px;
            line-height: 1.5;
            margin: 0 0 5px 0;
        }

        .no-messages {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
            background: white;
            border-radius: 10px;
        }
        
        .table-actions {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
            padding: 15px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .clickable-row { 
            cursor: pointer; 
        }

        .message-status {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            padding: 2px 8px;
            border-radius: 12px;
            background: #f8f9fa;
            color: #5f6368;
        }

        .status-read {
            background: #e8f6f3;
            color: #1abc9c;
        }

        .status-replied {
            background: #e8f4fd;
            color: #3498db;
        }

        /* Modal and other existing styles remain the same */
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
            max-width: 600px;
            max-height: 80vh;
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
            white-space: pre-wrap;
            line-height: 1.6;
        }
        
        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .search-box {
                flex-direction: column;
                gap: 10px;
                padding: 12px;
            }
            
            .search-actions {
                margin-left: 0;
                justify-content: space-between;
                width: 100%;
            }
            
            .messages-table {
                overflow-x: auto;
                display: block;
                white-space: nowrap;
            }
            
            .table-actions {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .pagination {
                gap: 3px;
            }
            
            .page-btn {
                padding: 6px 10px;
                font-size: 12px;
                min-width: 35px;
                height: 35px;
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

      <!-- Messages Content -->
      <div class="messages-content">
        <div class="page-header">
          <div>
            <h1 class="page-title">Contact Messages</h1>
            <p class="page-description">Manage customer inquiries and messages</p>
          </div>
        </div>

        <!-- Gmail-style Search and Filters -->
        <div class="search-container">
            <form method="GET" action="" id="searchForm">
                <!-- Hidden fields for filters -->
                <input type="hidden" name="status" id="statusFilter" value="<?= htmlspecialchars($status_filter) ?>">
                <input type="hidden" name="date" id="dateFilter" value="<?= htmlspecialchars($date_filter) ?>">
                
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="search" placeholder="Search messages..." 
                          value="<?= htmlspecialchars($search_term) ?>" class="search-input" id="searchInput">
                    <div class="search-actions">
                        <button type="button" class="search-action-btn" id="filterToggle">
                            <i class="fas fa-sliders-h"></i>
                        </button>
                        <button type="submit" class="search-action-btn">
                            <i class="fas fa-search"></i>
                        </button>
                        <button type="button" class="search-action-btn" id="clearSearch">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Filter Dropdown -->
                <div class="filter-dropdown" id="filterDropdown">
                    <div class="filter-section">
                        <h4>Status</h4>
                        <div class="filter-option <?= $status_filter === 'all' ? 'active' : '' ?>" data-filter="status" data-value="all">
                            <input type="radio" name="filter_status" <?= $status_filter === 'all' ? 'checked' : '' ?>> All messages
                        </div>
                        <div class="filter-option <?= $status_filter === 'unread' ? 'active' : '' ?>" data-filter="status" data-value="unread">
                            <input type="radio" name="filter_status" <?= $status_filter === 'unread' ? 'checked' : '' ?>> Unread
                        </div>
                        <div class="filter-option <?= $status_filter === 'read' ? 'active' : '' ?>" data-filter="status" data-value="read">
                            <input type="radio" name="filter_status" <?= $status_filter === 'read' ? 'checked' : '' ?>> Read
                        </div>
                        <div class="filter-option <?= $status_filter === 'replied' ? 'active' : '' ?>" data-filter="status" data-value="replied">
                            <input type="radio" name="filter_status" <?= $status_filter === 'replied' ? 'checked' : '' ?>> Replied
                        </div>
                        <div class="filter-option <?= $status_filter === 'not_replied' ? 'active' : '' ?>" data-filter="status" data-value="not_replied">
                            <input type="radio" name="filter_status" <?= $status_filter === 'not_replied' ? 'checked' : '' ?>> Not replied
                        </div>
                    </div>
                    
                    <div class="filter-section">
                        <h4>Date</h4>
                        <div class="filter-option <?= $date_filter === 'all' ? 'active' : '' ?>" data-filter="date" data-value="all">
                            <input type="radio" name="filter_date" <?= $date_filter === 'all' ? 'checked' : '' ?>> Any time
                        </div>
                        <div class="filter-option <?= $date_filter === 'today' ? 'active' : '' ?>" data-filter="date" data-value="today">
                            <input type="radio" name="filter_date" <?= $date_filter === 'today' ? 'checked' : '' ?>> Today
                        </div>
                        <div class="filter-option <?= $date_filter === 'yesterday' ? 'active' : '' ?>" data-filter="date" data-value="yesterday">
                            <input type="radio" name="filter_date" <?= $date_filter === 'yesterday' ? 'checked' : '' ?>> Yesterday
                        </div>
                        <div class="filter-option <?= $date_filter === 'week' ? 'active' : '' ?>" data-filter="date" data-value="week">
                            <input type="radio" name="filter_date" <?= $date_filter === 'week' ? 'checked' : '' ?>> Last 7 days
                        </div>
                        <div class="filter-option <?= $date_filter === 'month' ? 'active' : '' ?>" data-filter="date" data-value="month">
                            <input type="radio" name="filter_date" <?= $date_filter === 'month' ? 'checked' : '' ?>> Last 30 days
                        </div>
                        <div class="filter-option <?= $date_filter === 'older' ? 'active' : '' ?>" data-filter="date" data-value="older">
                            <input type="radio" name="filter_date" <?= $date_filter === 'older' ? 'checked' : '' ?>> Older
                        </div>
                    </div>
                </div>
            </form>

            <!-- Active Filter Chips -->
            <div class="filter-chips" id="filterChips">
                <?php if ($status_filter !== 'all'): ?>
                    <div class="filter-chip active" data-filter="status" data-value="<?= $status_filter ?>">
                        <?= ucfirst(str_replace('_', ' ', $status_filter)) ?>
                        <span class="remove" onclick="removeFilter('status')">×</span>
                    </div>
                <?php endif; ?>
                
                <?php if ($date_filter !== 'all'): ?>
                    <div class="filter-chip active" data-filter="date" data-value="<?= $date_filter ?>">
                        <?= ucfirst($date_filter) ?>
                        <span class="remove" onclick="removeFilter('date')">×</span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($search_term)): ?>
                    <div class="filter-chip active" data-filter="search">
                        Search: "<?= htmlspecialchars($search_term) ?>"
                        <span class="remove" onclick="removeFilter('search')">×</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (count($messages) > 0): ?>
          <!-- Action Bar -->
          <div class="table-actions">
            <label style="display: flex; align-items: center; gap: 8px;">
              <input type="checkbox" id="selectAll"> Select All
            </label>
            <button class="btn btn-danger" id="archiveBtn">
              <i class="fas fa-archive"></i> Archive Selected
            </button>
          </div>

          <!-- Messages Table -->
          <form id="messagesForm">
            <div class="messages-table">
              <table>
                <thead>
                  <tr>
                    <th>Select</th>
                    <th>Sender</th>
                    <th>Email</th>
                    <th>Message Preview</th>
                    <th>Date Received</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($messages as $message): ?>
                    <tr class="clickable-row <?= $message['is_read'] ? '' : 'message-unread' ?>" 
                        data-id="<?= $message['id'] ?>"
                        data-name="<?= htmlspecialchars($message['firstname'] . ' ' . $message['lastname']) ?>"
                        data-email="<?= htmlspecialchars($message['email']) ?>"
                        data-message="<?= htmlspecialchars($message['message']) ?>"
                        data-date="<?= $message['created_at'] ?>">
                      <td>
                        <input type="checkbox" name="ids[]" value="<?= $message['id'] ?>" onclick="event.stopPropagation();">
                      </td>
                      <td>
                        <div class="message-sender">
                          <?= htmlspecialchars($message['firstname'] . ' ' . $message['lastname']) ?>
                        </div>
                      </td>
                      <td>
                        <div class="message-email">
                          <?= htmlspecialchars($message['email']) ?>
                        </div>
                      </td>
                      <td>
                        <div class="message-preview" title="<?= htmlspecialchars($message['message']) ?>">
                          <?= strlen($message['message']) > 50 ? 
                              substr(htmlspecialchars($message['message']), 0, 50) . '...' : 
                              htmlspecialchars($message['message']) ?>
                        </div>
                      </td>
                      <td><?= date('M j, Y g:i A', strtotime($message['created_at'])) ?></td>
                      <td>
                        <div class="message-status <?= $message['is_replied'] ? 'status-replied' : ($message['is_read'] ? 'status-read' : '') ?>">
                          <i class="fas fa-<?= $message['is_replied'] ? 'check-circle' : ($message['is_read'] ? 'eye' : 'envelope') ?>"></i>
                          <?= $message['is_replied'] ? 'Replied' : ($message['is_read'] ? 'Read' : 'Unread') ?>
                        </div>
                      </td>
                      <td>
                        <button type="button" class="btn btn-outline view-btn" 
                                data-id="<?= $message['id'] ?>"
                                onclick="event.stopPropagation(); openMessageModal(
                                  '<?= htmlspecialchars($message['firstname'] . ' ' . $message['lastname']) ?>',
                                  '<?= htmlspecialchars($message['email']) ?>',
                                  `<?= htmlspecialchars(str_replace('`', '\`', $message['message'])) ?>`,
                                  '<?= $message['created_at'] ?>'
                                )">
                          <i class="fas fa-eye"></i> View
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </form>

          <!-- Smart Pagination -->
          <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=1&search=<?= urlencode($search_term) ?>&status=<?= $status_filter ?>&date=<?= $date_filter ?>" class="page-btn" title="First Page">
                    <i class="fas fa-angle-double-left"></i>
                </a>
                <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search_term) ?>&status=<?= $status_filter ?>&date=<?= $date_filter ?>" class="page-btn" title="Previous Page">
                    <i class="fas fa-angle-left"></i>
                </a>
            <?php endif; ?>

            <?php
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $page + 2);
            
            if ($start_page == 1) {
                $end_page = min($total_pages, 5);
            }
            
            if ($end_page == $total_pages) {
                $start_page = max(1, $total_pages - 4);
            }
            
            if ($start_page > 1): ?>
                <a href="?page=1&search=<?= urlencode($search_term) ?>&status=<?= $status_filter ?>&date=<?= $date_filter ?>" class="page-btn">1</a>
                <?php if ($start_page > 2): ?>
                    <span class="page-ellipsis">...</span>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search_term) ?>&status=<?= $status_filter ?>&date=<?= $date_filter ?>" 
                   class="page-btn <?= ($i == $page) ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($end_page < $total_pages): ?>
                <?php if ($end_page < $total_pages - 1): ?>
                    <span class="page-ellipsis">...</span>
                <?php endif; ?>
                <a href="?page=<?= $total_pages ?>&search=<?= urlencode($search_term) ?>&status=<?= $status_filter ?>&date=<?= $date_filter ?>" class="page-btn">
                    <?= $total_pages ?>
                </a>
            <?php endif; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search_term) ?>&status=<?= $status_filter ?>&date=<?= $date_filter ?>" class="page-btn" title="Next Page">
                    <i class="fas fa-angle-right"></i>
                </a>
                <a href="?page=<?= $total_pages ?>&search=<?= urlencode($search_term) ?>&status=<?= $status_filter ?>&date=<?= $date_filter ?>" class="page-btn" title="Last Page">
                    <i class="fas fa-angle-double-right"></i>
                </a>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div class="no-messages">
            <p>No messages found. <?= (!empty($search_term) || $status_filter !== 'all' || $date_filter !== 'all') ? 'Try adjusting your filters.' : 'Check back later for new messages.' ?></p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Message Detail Modal -->
  <div class="modal" id="messageModal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title" id="modalTitle">Message Details</h2>
        <button class="modal-close" id="closeModal">&times;</button>
      </div>
      <div class="modal-body" id="modalBody"></div>
      <div class="modal-footer">
        <button class="btn btn-outline" id="closeModalBtn">Close</button>
        <button class="btn btn-danger" id="archiveSingleBtn">
          <i class="fas fa-archive"></i> Archive Message
        </button>
      </div>
    </div>
  </div>

  <script>
    let currentMessageId = null;

    // Filter functionality
    document.addEventListener('DOMContentLoaded', function() {
        const filterToggle = document.getElementById('filterToggle');
        const filterDropdown = document.getElementById('filterDropdown');
        const searchForm = document.getElementById('searchForm');

        // Toggle filter dropdown
        filterToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            filterDropdown.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            filterDropdown.classList.remove('active');
        });

        // Prevent dropdown from closing when clicking inside
        filterDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Filter option selection
        document.querySelectorAll('.filter-option').forEach(option => {
            option.addEventListener('click', function() {
                const filterType = this.dataset.filter;
                const filterValue = this.dataset.value;
                
                // Update hidden input
                document.getElementById(filterType + 'Filter').value = filterValue;
                
                // Submit form
                searchForm.submit();
            });
        });

        // Clear search
        document.getElementById('clearSearch').addEventListener('click', function() {
            document.getElementById('searchInput').value = '';
            searchForm.submit();
        });

        // Enter key to search
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchForm.submit();
            }
        });
    });

    function removeFilter(filterType) {
        if (filterType === 'search') {
            document.getElementById('searchInput').value = '';
        } else {
            document.getElementById(filterType + 'Filter').value = 'all';
        }
        document.getElementById('searchForm').submit();
    }

    // Modal functionality (existing code)
    function openMessageModal(name, email, message, date) {
      const modalTitle = document.getElementById('modalTitle');
      const modalBody = document.getElementById('modalBody');
      
      modalTitle.textContent = `Message from ${name}`;
      modalBody.innerHTML = `
        <div style="margin-bottom: 20px;">
          <strong>Sender:</strong> ${name}<br>
          <strong>Email:</strong> ${email}<br>
          <strong>Date:</strong> ${new Date(date).toLocaleString()}
        </div>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; border-left: 4px solid #3498db;">
          <strong>Message:</strong><br>
          ${message.replace(/\n/g, '<br>')}
        </div>
      `;
      
      const clickedRow = event.target.closest('.clickable-row');
      currentMessageId = clickedRow ? clickedRow.dataset.id : null;
      
      document.getElementById('messageModal').classList.add('active');
    }

    function closeMessageModal() {
      document.getElementById('messageModal').classList.remove('active');
      currentMessageId = null;
    }

    document.getElementById('closeModal').addEventListener('click', closeMessageModal);
    document.getElementById('closeModalBtn').addEventListener('click', closeMessageModal);

    window.addEventListener('click', function(event) {
      const modal = document.getElementById('messageModal');
      if (event.target === modal) {
        closeMessageModal();
      }
    });

    // Archive functionality (existing code)
    document.getElementById('archiveSingleBtn').addEventListener('click', function() {
      if (!currentMessageId) return;
      
      Swal.fire({
        title: "Archive Message?",
        text: "This message will be moved to archives.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, archive it",
        cancelButtonText: "Cancel"
      }).then((result) => {
        if (result.isConfirmed) {
          const formData = new FormData();
          formData.append('ids[]', currentMessageId);
          
          fetch("archive_messages.php", {
            method: "POST",
            body: formData
          })
          .then(res => res.text())
          .then(data => {
            Swal.fire("Archived!", "Message has been archived.", "success").then(() => {
              location.reload();
            });
          })
          .catch(err => {
            Swal.fire("Error", "Something went wrong.", "error");
          });
        }
      });
    });

    document.getElementById("selectAll").addEventListener("change", function() {
      let checkboxes = document.querySelectorAll('input[name="ids[]"]');
      checkboxes.forEach(cb => cb.checked = this.checked);
    });

    document.getElementById("archiveBtn").addEventListener("click", function() {
      let form = document.getElementById("messagesForm");
      let formData = new FormData(form);

      if (!formData.has("ids[]")) {
        Swal.fire({
          title: "No Selection",
          text: "Please select at least one message to archive.",
          icon: "warning",
          confirmButtonText: "OK"
        });
        return;
      }

      Swal.fire({
        title: "Archive Messages?",
        text: "Selected messages will be moved to archives.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, archive them",
        cancelButtonText: "Cancel"
      }).then((result) => {
        if (result.isConfirmed) {
          fetch("archive_messages.php", {
            method: "POST",
            body: formData
          })
          .then(res => res.text())
          .then(data => {
            Swal.fire("Archived!", "Selected messages have been archived.", "success").then(() => {
              location.reload();
            });
          })
          .catch(err => {
            Swal.fire("Error", "Something went wrong while archiving messages.", "error");
          });
        }
      });
    });

    document.querySelectorAll(".clickable-row").forEach(row => {
      row.addEventListener("click", function() {
        const name = this.dataset.name;
        const email = this.dataset.email;
        const message = this.dataset.message;
        const date = this.dataset.date;
        openMessageModal(name, email, message, date);
      });
    });
  </script>
</body>
</html>
<?php 
if (isset($stmt_count)) $stmt_count->close();
if (isset($stmt)) $stmt->close();
$conn->close(); 
?>