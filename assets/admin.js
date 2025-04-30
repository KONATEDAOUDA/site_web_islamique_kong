// Fichier admin.js pour le panneau d'administration

// Import des CSS spécifiques à l'administration
import './styles/admin.scss';

// Import des modules nécessaires pour AdminLTE
import 'admin-lte/plugins/jquery/jquery.min';
import 'admin-lte/plugins/bootstrap/js/bootstrap.bundle.min';
import 'admin-lte/dist/js/adminlte.min';

// Initialisation d'AdminLTE
document.addEventListener('DOMContentLoaded', function() {
    // Active les fonctionnalités d'AdminLTE
    if (window.$.fn.AdminLTE) {
        window.$.fn.AdminLTE.init();
    }
    
    // Initialisation des menus latéraux
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            document.body.classList.toggle('sidebar-collapse');
        });
    }
    
    // Initialisation des autres plugins si nécessaire
    // ...
});