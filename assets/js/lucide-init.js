/**
 * NOXARA - Lucide Icons Auto-Init
 * Initializes icons after deferred lucide.js loads
 */
(function() {
    'use strict';
    function tryInit() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        } else {
            setTimeout(tryInit, 50);
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', tryInit);
    } else {
        tryInit();
    }
})();
