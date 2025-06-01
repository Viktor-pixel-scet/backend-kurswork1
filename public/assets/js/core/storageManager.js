import ErrorHandler from './errorHandler.js';

const LocalStorageManager = {
    getItem: function(key, defaultValue = []) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : defaultValue;
        } catch (error) {
            ErrorHandler.logError('localStorage', error);
            return defaultValue;
        }
    },
    setItem: function(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (error) {
            ErrorHandler.logError('localStorage', error);
        }
    }
};

export default LocalStorageManager;