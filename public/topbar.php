<header class="topbar">
    <div class="topbar-left">
        <button id="sidebarToggle" class="btn-toggle">☰</button>
        <h2 id="pageTitle"><?= isset($pageTitle) ? $pageTitle : '' ?></h2>
    </div>
    <div class="topbar-right">
        <span>👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
    </div>
</header>
