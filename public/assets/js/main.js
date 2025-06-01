import ErrorHandler from './core/errorHandler.js';
import LocalStorageManager from './core/storageManager.js';
import ProductComparisonManager from './features/productComparison.js';
import GameTestManager from './features/gameTest.js';
import CartManager from './features/cart.js';
import ProductFilter from './features/productFilter.js';
import { initImageZoom } from './ui/imageHandlers.js';
import { changeQuantity } from './ui/quantityManager.js';
import { changeMainImage } from "./ui/imageHandlers.js";

document.addEventListener('DOMContentLoaded', function() {
    initializeManagers();
    initImageZoom();
    changeQuantity();
    changeMainImage();
});

function initializeManagers() {
    ProductComparisonManager.init();
    GameTestManager.init();
    CartManager.init();
    ProductFilter.init();
}

export { ErrorHandler, LocalStorageManager };