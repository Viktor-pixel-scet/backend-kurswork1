class ProductFilterManager {
    constructor() {
        this.form = null;
        this.errorMessageContainer = null;
        this.ERROR_MESSAGES = {
            networkError: 'Помилка мережі. Перевірте підключення до інтернету.',
            serverError: 'Помилка сервера. Спробуйте пізніше.',
            invalidInput: 'Введіть коректні числові значення для ціни',
            negativePrice: 'Ціна не може бути від\'ємною',
            invalidPriceRange: 'Мінімальна ціна не може бути більшою за максимальну',
            noProducts: 'Не знайдено товарів за заданими параметрами',
            parseError: 'Помилка обробки відповіді від сервера',
            timeout: 'Час очікування відповіді вичерпано'
        };
    }

    init() {
        this.form = document.getElementById('advanced-filter');
        if (!this.form) return;

        this.errorMessageContainer = document.createElement('div');
        this.errorMessageContainer.classList.add('alert', 'alert-danger', 'error-container');
        this.errorMessageContainer.style.display = 'none';
        this.form.insertBefore(this.errorMessageContainer, this.form.firstChild);

        this.form.addEventListener('submit', this.handleFormSubmit.bind(this));
        this.form.addEventListener('reset', this.handleFormReset.bind(this));

        const priceInputs = this.form.querySelectorAll('input[name="min_price"], input[name="max_price"]');
        priceInputs.forEach(input => {
            input.addEventListener('input', this.handlePriceInput);
            input.addEventListener('paste', this.handlePricePaste);
        });

        window.addEventListener('unhandledrejection', this.handleUnhandledRejection.bind(this));
    }

    logError(errorType, details = {}) {
        console.error(`[Помилка фільтрації - ${errorType}]`, {
            timestamp: new Date().toISOString(),
            ...details
        });
    }

    displayErrorMessage(message, options = {}) {
        const {
            persistent = false,
            type = 'danger'
        } = options;

        if (this.errorMessageContainer.timeoutId) {
            clearTimeout(this.errorMessageContainer.timeoutId);
        }

        this.errorMessageContainer.textContent = message;
        this.errorMessageContainer.className = `alert alert-${type}`;
        this.errorMessageContainer.style.display = 'block';

        if (!persistent) {
            this.errorMessageContainer.timeoutId = setTimeout(() => {
                this.hideErrorMessage();
            }, 5000);
        }
    }

    hideErrorMessage() {
        this.errorMessageContainer.textContent = '';
        this.errorMessageContainer.style.display = 'none';
    }

    validatePriceInputs(minPriceInput, maxPriceInput) {
        const minPrice = parseFloat(minPriceInput.value);
        const maxPrice = parseFloat(maxPriceInput.value);

        if (isNaN(minPrice) || isNaN(maxPrice)) {
            this.displayErrorMessage(this.ERROR_MESSAGES.invalidInput);
            this.logError('ValidationError', {
                input: { minPrice, maxPrice }
            });
            return false;
        }

        if (minPrice < 0 || maxPrice < 0) {
            this.displayErrorMessage(this.ERROR_MESSAGES.negativePrice);
            this.logError('ValidationError', {
                input: { minPrice, maxPrice }
            });
            return false;
        }

        if (maxPrice > 0 && minPrice > maxPrice) {
            this.displayErrorMessage(this.ERROR_MESSAGES.invalidPriceRange);
            this.logError('ValidationError', {
                input: { minPrice, maxPrice }
            });
            return false;
        }

        return true;
    }

    fetchProducts(formData) {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => {
            controller.abort();
            this.displayErrorMessage(this.ERROR_MESSAGES.timeout);
            this.logError('TimeoutError');
        }, 10000);

        return fetch('index.php?' + new URLSearchParams(formData).toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            signal: controller.signal
        })
            .then(response => {
                clearTimeout(timeoutId);

                if (!response.ok) {
                    this.logError('ServerError', {
                        status: response.status,
                        statusText: response.statusText
                    });
                    throw new Error(`HTTP помилка! Статус: ${response.status}`);
                }
                return response.text();
            })
            .catch(error => {
                clearTimeout(timeoutId);

                if (error.name === 'AbortError') {
                    this.displayErrorMessage(this.ERROR_MESSAGES.timeout, { persistent: true });
                } else if (error instanceof TypeError) {
                    this.displayErrorMessage(this.ERROR_MESSAGES.networkError);
                    this.logError('NetworkError', { message: error.message });
                } else {
                    this.displayErrorMessage(this.ERROR_MESSAGES.serverError);
                    this.logError('FetchError', { message: error.message });
                }
                throw error;
            });
    }

    handleFormSubmit(e) {
        e.preventDefault();
        this.hideErrorMessage();

        const minPriceInput = this.form.querySelector('input[name="min_price"]');
        const maxPriceInput = this.form.querySelector('input[name="max_price"]');
        const productContainer = document.querySelector('.row .col-md-9 .row');

        if (!this.validatePriceInputs(minPriceInput, maxPriceInput)) {
            return;
        }

        const formData = new FormData(this.form);

        this.fetchProducts(formData)
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newProducts = doc.querySelector('.row .col-md-9 .row');

                if (!newProducts || newProducts.children.length === 0) {
                    this.displayErrorMessage(this.ERROR_MESSAGES.noProducts, { type: 'warning' });
                    this.logError('NoProductsError');
                    return;
                }

                productContainer.innerHTML = newProducts.innerHTML;
            })
            .catch(error => {

            });
    }

    handleFormReset() {
        const minPriceInput = this.form.querySelector('input[name="min_price"]');
        const maxPriceInput = this.form.querySelector('input[name="max_price"]');
        minPriceInput.value = 0;
        maxPriceInput.value = 0;

        setTimeout(() => {
            this.form.dispatchEvent(new Event('submit'));
        }, 0);
    }

    handlePriceInput() {
        this.value = this.value.replace(/[^0-9.]/g, '');

        const matches = this.value.match(/\./g);
        if (matches && matches.length > 1) {
            this.value = this.value.replace(/\.+/, '.');
        }
    }

    handlePricePaste(e) {
        e.preventDefault();
        const pastedText = e.clipboardData.getData('text/plain').replace(/[^0-9.]/g, '');
        this.value = pastedText;
    }

    handleUnhandledRejection(event) {
        this.displayErrorMessage(this.ERROR_MESSAGES.serverError);
        this.logError('UnhandledRejection', {
            reason: event.reason
        });
        event.preventDefault();
    }
}

const ProductFilter = new ProductFilterManager();
export default ProductFilter;
