<?php
// admin/includes/header.php
?>
<header class="admin-header">
    <div class="header-left">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h2>ClothingShop Admin</h2>
    </div>
    
    <div class="header-right">
        <div class="admin-user">
            <i class="fas fa-user-shield"></i>
            <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        </div>
    </div>
</header>

<script>
// Sidebar toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.admin-sidebar');
    
    if (toggle && sidebar) {
        toggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
        });
    }
});
</script>