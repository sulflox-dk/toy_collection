/**
 * Collection Module - Main Entry Point
 * Loads all collection sub-modules in the correct order
 * 
 * Load Order:
 * 1. API module (no dependencies)
 * 2. UI module (depends on UiHelper)
 * 3. Forms module (depends on ApiClient, UiHelper, Validation)
 * 4. Media module (depends on ApiClient, UiHelper)
 * 5. Manager module (orchestrates all modules, extends EntityManager)
 * 
 * Prerequisites (must be loaded before this file):
 * - ApiClient (base class)
 * - UiHelper (base class)
 * - Validation (base class)
 * - EntityManager (base class)
 */

// This file serves as documentation and can be used to verify load order
// The individual module files should be loaded in HTML in this order:

console.log('Collection Module: Loading...');

// Module load order verification
const requiredDependencies = [
    'ApiClient',
    'UiHelper', 
    'Validation',
    'EntityManager'
];

const missingDependencies = requiredDependencies.filter(dep => typeof window[dep] === 'undefined');

if (missingDependencies.length > 0) {
    console.error('Collection Module: Missing required dependencies:', missingDependencies);
    console.error('Please ensure the following files are loaded before collection modules:');
    console.error('1. /assets/js/core/api-client.js');
    console.error('2. /assets/js/core/ui-helper.js');
    console.error('3. /assets/js/core/validation.js');
    console.error('4. /assets/js/core/entity-manager.js');
} else {
    console.log('Collection Module: All dependencies loaded');
}

// Export module status for debugging
window.CollectionModuleStatus = {
    loaded: true,
    dependencies: {
        ApiClient: typeof window.ApiClient !== 'undefined',
        UiHelper: typeof window.UiHelper !== 'undefined',
        Validation: typeof window.Validation !== 'undefined',
        EntityManager: typeof window.EntityManager !== 'undefined'
    },
    modules: {
        Api: typeof window.CollectionApi !== 'undefined',
        Ui: typeof window.CollectionUi !== 'undefined',
        Forms: typeof window.CollectionForms !== 'undefined',
        Media: typeof window.CollectionMedia !== 'undefined',
        Manager: typeof window.CollectionManager !== 'undefined'
    }
};

console.log('Collection Module Status:', window.CollectionModuleStatus);
