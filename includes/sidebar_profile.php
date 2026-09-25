<a class="sidebar-profile" href="/Project_IMS/profile/index.php">
    <span class="sidebar-profile-avatar"><?= strtoupper(substr($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'U', 0, 1)) ?></span>
    <span class="sidebar-profile-info">
        <strong><?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User') ?></strong>
        <span><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'staff')) ?></span>
    </span>
</a>
