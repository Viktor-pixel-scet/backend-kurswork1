const ErrorHandler = {
    logError: function(context, error) {
        console.error(`[${context}] Помилка:`, error);
        this.displayUserFriendlyError(context, error);
    },
    displayUserFriendlyError: function(context, error) {
        const errorContainer = document.getElementById('global-error-container');
        if (errorContainer) {
            errorContainer.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Помилка:</strong> ${this.getErrorMessage(context, error)}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        }
    },
    getErrorMessage: function(context, error) {
        const errorMessages = {
            'fetchGames': 'Не вдалося завантажити список ігор. Спробуйте пізніше.',
            'compareProducts': 'Помилка при роботі з порівнянням товарів.',
            'addToCart': 'Не вдалося додати товар до кошика.',
            'performanceTest': 'Помилка під час тестування продуктивності.',
            'default': 'Сталася неочікувана помилка. Спробуйте оновити сторінку.'
        };
        return errorMessages[context] || errorMessages['default'];
    }
};

export default ErrorHandler;