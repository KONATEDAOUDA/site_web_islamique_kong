const path = require('path');
const Encore = require('@symfony/webpack-encore');

Encore
    // Répertoire de sortie des assets compilés
    .setOutputPath('public/build/')
    
    // URL publique pour accéder aux assets
    .setPublicPath('/build')
    
    // Points d'entrée
    .addEntry('app', './assets/app.js')
    .addEntry('admin', './assets/admin.js')
    
    // Runtime chunk unique pour optimiser le chargement
    .enableSingleRuntimeChunk()
    
    // Nettoyage du répertoire avant la compilation
    .cleanupOutputBeforeBuild()
    
    // Source maps pour le débogage (désactivé en production)
    .enableSourceMaps(!Encore.isProduction())
    
    // Versioning des fichiers en production
    .enableVersioning(Encore.isProduction())
    
    // Support pour Sass/SCSS
    .enableSassLoader()
    
    // jQuery disponible automatiquement
    .autoProvidejQuery()
    
    // Configuration de Babel pour la compatibilité des navigateurs
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
    
    // Résolution des modules via node_modules
    .addAliases({
        '~': path.resolve(__dirname, 'node_modules/')
    });

module.exports = Encore.getWebpackConfig();