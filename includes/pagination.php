          <link rel="stylesheet" href="../css/pagination.css">
          
          <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=1&search=<?= urlencode($search_term) ?>" class="page-btn" title="First Page">
                    <i class="fas fa-angle-double-left"></i>
                </a>
                <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search_term) ?>" class="page-btn" title="Previous Page">
                    <i class="fas fa-angle-left"></i>
                </a>
            <?php endif; ?>

            <?php
            // Show limited page numbers (current page ± 2)
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $page + 2);
            
            // Adjust if we're near the start
            if ($start_page == 1) {
                $end_page = min($total_pages, 5);
            }
            
            // Adjust if we're near the end
            if ($end_page == $total_pages) {
                $start_page = max(1, $total_pages - 4);
            }
            
            // Show first page with ellipsis if needed
            if ($start_page > 1): ?>
                <a href="?page=1&search=<?= urlencode($search_term) ?>" class="page-btn">1</a>
                <?php if ($start_page > 2): ?>
                    <span class="page-ellipsis">...</span>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search_term) ?>" 
                   class="page-btn <?= ($i == $page) ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($end_page < $total_pages): ?>
                <?php if ($end_page < $total_pages - 1): ?>
                    <span class="page-ellipsis">...</span>
                <?php endif; ?>
                <a href="?page=<?= $total_pages ?>&search=<?= urlencode($search_term) ?>" class="page-btn">
                    <?= $total_pages ?>
                </a>
            <?php endif; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search_term) ?>" class="page-btn" title="Next Page">
                    <i class="fas fa-angle-right"></i>
                </a>
                <a href="?page=<?= $total_pages ?>&search=<?= urlencode($search_term) ?>" class="page-btn" title="Last Page">
                    <i class="fas fa-angle-double-right"></i>
                </a>
            <?php endif; ?>
          </div>