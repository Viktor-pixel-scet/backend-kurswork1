import ErrorHandler from '../core/errorHandler.js';

const CartManager = {
    init: function() {
        this.addToCartButtons = document.querySelectorAll('a[href^="cart.php?action=add"]');
        this.bindEvents();
    },
    bindEvents: function() {
        this.addToCartButtons.forEach(button => {
            button.addEventListener('click', this.handleAddToCart.bind(this));
        });
    },
    handleAddToCart: function(event) {
        try {
            const productElement = event.currentTarget.closest('.card')?.querySelector('.card-title')
                || document.querySelector('h1');

            if (productElement) {
                const productName = productElement.textContent;
                this.showAddToCartMessage(productName);
            }
        } catch (error) {
            ErrorHandler.logError('addToCart', error);
        }
    },
    showAddToCartMessage: function(productName) {
        console.log(`Added ${productName} to cart`);
    }
};

export default CartManager;