import LocalStorageManager from '../core/storageManager.js';
import ErrorHandler from '../core/errorHandler.js';

const ProductComparisonManager = {
    MAX_COMPARISON_ITEMS: 4,
    init: function() {
        this.comparisonList = document.querySelector('.comparison-list');
        this.compareButtons = document.querySelectorAll('.compare-toggle');
        this.fullCompareLink = document.getElementById('full-compare-link');
        this.compareModal = document.getElementById('compareModal') ?
            new bootstrap.Modal(document.getElementById('compareModal')) : null;

        this.productComparison = LocalStorageManager.getItem('productComparison');
        this.bindEvents();
        this.updateComparisonUI();
    },
    bindEvents: function() {
        this.compareButtons.forEach(button => {
            button.addEventListener('click', this.toggleProductComparison.bind(this));
        });
    },
    toggleProductComparison: function(event) {
        event.preventDefault();
        try {
            const button = event.currentTarget;
            const productId = button.getAttribute('data-product-id');

            if (this.productComparison.includes(productId)) {
                this.removeFromComparison(productId);
            } else if (this.productComparison.length < this.MAX_COMPARISON_ITEMS) {
                this.addToComparison(productId);
            } else {
                alert('Максимум 4 товари для порівняння');
                return;
            }

            LocalStorageManager.setItem('productComparison', this.productComparison);
            this.updateComparisonUI();

            if (this.compareModal && this.productComparison.length > 0) {
                this.compareModal.show();
            }
        } catch (error) {
            ErrorHandler.logError('compareProducts', error);
        }
    },
    addToComparison: function(productId) {
        this.productComparison.push(productId);
    },
    removeFromComparison: function(productId) {
        this.productComparison = this.productComparison.filter(id => id !== productId);
    },
    updateComparisonUI: function() {
        try {
            this.updateCompareButtonStates();
            this.updateComparisonList();
            this.updateFullCompareLink();
            this.updateCompareCountBadge();
            this.updateCompareLink();
        } catch (error) {
            ErrorHandler.logError('compareProducts', error);
        }
    },
    updateCompareButtonStates: function() {
        this.compareButtons.forEach(button => {
            const productId = button.getAttribute('data-product-id');
            button.classList.toggle('active', this.productComparison.includes(productId));
        });
    },
    updateComparisonList: function() {
        if (!this.comparisonList) return;

        this.comparisonList.innerHTML = this.productComparison.map(productId => `
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">${this.getProductName(productId)}</h5>
                        <button class="btn btn-sm btn-danger remove-compare" data-product-id="${productId}">
                            Видалити
                        </button>
                    </div>
                </div>
            </div>
        `).join('');

        this.bindRemoveCompareButtons();
    },
    getProductName: function(productId) {
        const productButton = document.querySelector(`.compare-toggle[data-product-id="${productId}"]`);
        return productButton ? productButton.getAttribute('data-product-name') : `Товар #${productId}`;
    },
    bindRemoveCompareButtons: function() {
        const removeButtons = this.comparisonList.querySelectorAll('.remove-compare');
        removeButtons.forEach(button => {
            button.addEventListener('click', (event) => {
                const productId = event.currentTarget.getAttribute('data-product-id');
                this.removeFromComparison(productId);
                LocalStorageManager.setItem('productComparison', this.productComparison);
                this.updateComparisonUI();
            });
        });
    },
    updateFullCompareLink: function() {
        if (this.fullCompareLink) {
            if (this.productComparison.length > 0) {
                this.fullCompareLink.href = `backend/utils/compare.php?products=${this.productComparison.join(',')}`;
                this.fullCompareLink.classList.remove('disabled');
            } else {
                this.fullCompareLink.classList.add('disabled');
            }
        }
    },
    updateCompareCountBadge: function() {
        const compareCountBadge = document.getElementById('compare-count');
        if (compareCountBadge) {
            compareCountBadge.textContent = this.productComparison.length;
        }
    },
    updateCompareLink: function() {
        const compareLink = document.getElementById('compare-link');
        if (compareLink) {
            if (this.productComparison.length === 0) {
                compareLink.classList.add('disabled');
                compareLink.href = 'backend/utils/compare.php';
            } else {
                compareLink.classList.remove('disabled');
                compareLink.href = `backend/utils/compare.php?products=${this.productComparison.join(',')}`;
            }
        }
    },
};

export default ProductComparisonManager;