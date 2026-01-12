document.addEventListener('DOMContentLoaded', function () {
    const links = document.querySelectorAll('.sidebar .menu a[data-page]');
    const contentEl = document.getElementById('main-content');
    const pageTitle = document.getElementById('pageTitle');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');

    // Load halaman default (yang active atau yang pertama)
    function initialLoad() {
        const defaultLink = document.querySelector('.sidebar .menu a[data-page].active') || links[0];
        if (defaultLink) {
            setActiveLink(defaultLink);
            loadPage(defaultLink.dataset.page);
        }
    }

    // Set styling link aktif dan title
    function setActiveLink(linkEl) {
        links.forEach(l => l.classList.remove('active'));
        if (linkEl) linkEl.classList.add('active');
        pageTitle.textContent = linkEl ? linkEl.textContent.trim() : '';
    }

    // Load halaman via AJAX
    function loadPage(url) {
        contentEl.innerHTML = '<div class="loading">Memuat...</div>';
        fetch(url, { method: 'GET', credentials: 'same-origin' })
            .then(response => {
                if (!response.ok) throw new Error('Network error');
                return response.text();
            })
            .then(html => {
                contentEl.innerHTML = html;
                if (typeof pageInit === 'function') pageInit();
            })
            .catch(error => {
                contentEl.innerHTML = '<div class="loading">Gagal memuat halaman.</div>';
                console.error(error);
            });
    }

  links.forEach(link => {
    link.addEventListener('click', function(e) {
        if (this.dataset.page === 'admin.php' || this.dataset.page === 'admin_chatbot_crud.php') {
            // Jangan cegah default, biarkan browser reload full halaman
            return;
        }
        // Untuk link lain, pakai AJAX load
        e.preventDefault();
        setActiveLink(this);
        loadPage(this.dataset.page);
    });
});


    // Toggle sidebar untuk layar kecil
    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        if (sidebar.classList.contains('collapsed')) {
            sidebar.style.width = '60px';
            document.querySelector('.content').style.marginLeft = '60px';
        } else {
            sidebar.style.width = '260px';
            document.querySelector('.content').style.marginLeft = '260px';
        }
    });

    initialLoad();
});
