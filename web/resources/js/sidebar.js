const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    const topbar = document.getElementById('topbar');
    const toggleBtn = document.getElementById('toggleBtn');
    const mobileBtn = document.getElementById('mobileBtn');
    const overlay = document.getElementById('overlay');

    // Desktop collapse
    if (toggleBtn) {
      toggleBtn.addEventListener('click', () => {
        if (sidebar) sidebar.classList.toggle('collapsed');
        if (content) content.classList.toggle('full');
        if (topbar) topbar.classList.toggle('full');
      });
    }

    // Mobile sidebar open
    if (mobileBtn) {
      mobileBtn.addEventListener('click', () => {
        if (sidebar) sidebar.classList.add('mobile-show');
        if (overlay) overlay.classList.add('show');
      });
    }

    // 🔥 Click outside to close
    if (overlay) {
      overlay.addEventListener('click', () => {
        if (sidebar) sidebar.classList.remove('mobile-show');
        if (overlay) overlay.classList.remove('show');
      });
    }

// Restore & save sidebar scroll position
const sidebarNav = document.querySelector('.sidebar .nav');
if (sidebarNav) {
  const savedScroll = localStorage.getItem('sidebar-scroll');
  const activeLink = sidebarNav.querySelector('.nav-link.active');
  
  if (savedScroll !== null) {
    sidebarNav.scrollTop = parseInt(savedScroll, 10);
  } else if (activeLink) {
    activeLink.scrollIntoView({ block: 'nearest' });
  }
  
  sidebarNav.addEventListener('scroll', () => {
    localStorage.setItem('sidebar-scroll', sidebarNav.scrollTop);
  });
}