<aside class="sidebar">
    <div class="brand">
        <h3>Admin Panel</h3>
        <small>AkademikaBot</small>
    </div>
    <nav class="menu">
        <a href="dashboard_home.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard_home.php' ? 'active' : '' ?>">📊 Dashboard</a>
        <a href="admin.php" class="<?= basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : '' ?>">💬 Live Chat Admin</a>
        <a href="admin_user_list.php" class="<?= basename($_SERVER['PHP_SELF']) == 'admin_user_list.php' ? 'active' : '' ?>">👥 Daftar Pengguna</a>
        <a href="admin_chatbot_crud.php" class="<?= basename($_SERVER['PHP_SELF']) == 'admin_chatbot_crud.php' ? 'active' : '' ?>">➕ Kelola Pertanyaan Chatbot</a>
        <a href="admin_chatbot_list.php" class="<?= basename($_SERVER['PHP_SELF']) == 'admin_chatbot_list.php' ? 'active' : '' ?>">📄 Lihat Semua Pertanyaan</a>
        <a href="admin_chatbot_feedback.php" class="<?= basename($_SERVER['PHP_SELF']) == 'admin_chatbot_feedback.php' ? 'active' : '' ?>">📊 Pertanyaan Sering Ditanyakan</a>
        <a href="admin_chatbot_statistik.php" class="<?= basename($_SERVER['PHP_SELF']) == 'admin_chatbot_statistik.php' ? 'active' : '' ?>">📈 Statistik Penggunaan</a>
        <a href="admin_chatbot_feedback_user.php" class="<?= basename($_SERVER['PHP_SELF']) == 'admin_chatbot_feedback_user.php' ? 'active' : '' ?>">🗣️ Feedback Pengguna</a>
        <a href="admin_chatbot_training.php" class="<?= basename($_SERVER['PHP_SELF']) == 'admin_chatbot_training.php' ? 'active' : '' ?>">🤖 Pelatihan Chatbot</a>
        <a href="admin_manage_account.php" class="<?= basename($_SERVER['PHP_SELF']) == 'admin_manage_account.php' ? 'active' : '' ?>">👨‍💼 Manajemen Admin</a>
        <a href="logout.php" class="logout">🚪 Logout</a>
    </nav>
</aside>
