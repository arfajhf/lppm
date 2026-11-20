import './bootstrap';

// Native details remains usable without JavaScript; collapse it on smaller screens.
const sidebarMenu = document.getElementById('sidebar-menu');
if (sidebarMenu) {
    const compactNavigation = window.matchMedia('(max-width: 1080px)');
    const syncNavigation = () => { sidebarMenu.open = !compactNavigation.matches; };
    syncNavigation();
    compactNavigation.addEventListener('change', syncNavigation);
    sidebarMenu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            if (compactNavigation.matches) sidebarMenu.open = false;
        });
    });
}
