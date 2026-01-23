<?php
// admin/includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="admin-sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-store"></i>
        <span>ADMIN PANEL</span>
    </div>
    
    <nav class="sidebar-nav">
        <a href="index.php" class="nav-item <?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        
        <a href="products.php" class="nav-item <?php echo $current_page === 'products.php' ? 'active' : ''; ?>">
            <i class="fas fa-box"></i>
            <span>Products</span>
        </a>
        
        <a href="categories.php" class="nav-item <?php echo $current_page === 'categories.php' ? 'active' : ''; ?>">
            <i class="fas fa-tags"></i>
            <span>Categories</span>
        </a>
        
        <a href="orders.php" class="nav-item <?php echo $current_page === 'orders.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i>
            <span>Orders</span>
        </a>
        
        <a href="users.php" class="nav-item <?php echo $current_page === 'users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Users</span>
        </a>
        
        <div class="sidebar-divider"></div>
        
        <a href="../index.php" class="nav-item">
            <i class="fas fa-home"></i>
            <span>Back to Store</span>
        </a>
        
        <a href="../logout.php" class="nav-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </nav>
</div>