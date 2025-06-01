import ErrorHandler from '../core/errorHandler.js';

const GameTestManager = {
    init: function() {
        this.performanceTestButtons = document.querySelectorAll('.performance-test-btn');
        this.bindEvents();
    },
    bindEvents: function() {
        this.performanceTestButtons.forEach(button => {
            button.addEventListener('click', this.initPerformanceTest.bind(this));
        });
    },
    initPerformanceTest: function(event) {
        const card = event.currentTarget.closest('.card');
        const productName = card.querySelector('.card-title').textContent;
        const productId = card.getAttribute('data-product-id');

        this.showPerformanceTestModal(productName, productId);
    },
    showPerformanceTestModal: function(productName, productId) {
        try {
            let performanceTestModal = document.getElementById('performanceTestModal');
            if (!performanceTestModal) {
                performanceTestModal = this.createPerformanceTestModal();
                document.body.appendChild(performanceTestModal);
            }

            this.setupModalContent(performanceTestModal, productName, productId);

            const modalInstance = new bootstrap.Modal(performanceTestModal);
            modalInstance.show();

            this.fetchGamesForTesting();
        } catch (error) {
            ErrorHandler.logError('performanceTest', error);
        }
    },
    createPerformanceTestModal: function() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'performanceTestModal';
        return modal;
    },
    setupModalContent: function(modal, productName, productId) {
        modal.innerHTML = `
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-white">🎮 Тестування Продуктивності: ${productName}</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
                </div >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card mb-3">
                                <div class="card-header text-white">Ігрові Тести</div>
                                <div class="card-body game-checkbox-container text-white"></div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header text-white">Результати Тестування</div>
                                <div class="card-body text-white" id="modal-test-results">
                                    <div class="alert alert-info">Оберіть ігри для тестування</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
        modal.setAttribute('data-product-id', productId);
    },
    fetchGamesForTesting: function() {
        fetch('backend/games_testing/get-games.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(this.updateGameTestModal.bind(this))
            .catch(error => {
                ErrorHandler.logError('fetchGames', error);
                this.handleGameFetchError();
            });
    },
    handleGameFetchError: function() {
        const gameCheckboxContainer = document.querySelector('#performanceTestModal .game-checkbox-container');
        if (gameCheckboxContainer) {
            gameCheckboxContainer.innerHTML = `
                <div class="alert alert-danger">
                    Помилка завантаження ігор. Спробуйте пізніше.
                </div>
            `;
        }
    },
    updateGameTestModal: function(games) {
        const gameCheckboxContainer = document.querySelector('#performanceTestModal .game-checkbox-container');

        if (!gameCheckboxContainer) {
            ErrorHandler.logError('fetchGames', new Error('Game checkbox container not found'));
            return;
        }

        gameCheckboxContainer.innerHTML = games.map(game => `
            <div class="form-check">
                <input class="form-check-input game-checkbox" 
                       type="checkbox" 
                       value="${game.game_code}" 
                       id="modal-${game.game_code}-test"
                       data-min-fps="${game.min_fps}"
                       data-max-fps="${game.max_fps}"
                       data-category="${game.category}">
                <label class="form-check-label" for="modal-${game.game_code}-test">
                    ${game.game_name} (${game.category})
                </label>
            </div>
        `).join('');

        this.setupGameTestEventListeners(games);
    },
    setupGameTestEventListeners: function(games) {
        const modalCheckboxes = document.querySelectorAll('#performanceTestModal .game-checkbox');
        const modalTestResults = document.getElementById('modal-test-results');

        modalCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const selectedGames = Array.from(modalCheckboxes)
                    .filter(cb => cb.checked)
                    .map(cb => ({
                        code: cb.value,
                        name: cb.nextElementSibling.textContent,
                        minFps: parseInt(cb.getAttribute('data-min-fps')),
                        maxFps: parseInt(cb.getAttribute('data-max-fps'))
                    }));

                this.updateTestResults(selectedGames);
            });
        });
    },
    updateTestResults: function(selectedGames) {
        const modalTestResults = document.getElementById('modal-test-results');
        const productName = document.querySelector('#performanceTestModal .modal-title').textContent.split(': ')[1];
        const productId = document.getElementById('performanceTestModal').getAttribute('data-product-id');

        if (selectedGames.length === 0) {
            modalTestResults.innerHTML = '<div class="alert alert-info">Оберіть ігри для тестування</div>';
            return;
        }

        const resultsHTML = selectedGames.map(game => {
            const result = this.runGameTest(game, productName, productId);
            return `
                <div class="card mb-3">
                    <div class="card-header" style="color: white">${game.name}</div>
                    <div class="card-body">
                        <p style="color: white">Середній FPS: <strong>${result.fps}</strong></p>
                        <p style="color: white">Стабільність: <strong>${result.stability}%</strong></p>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: ${result.stability}%;
                                 color: white; 
                                 background-color: ${result.stability > 90 ? 'green' : result.stability > 70 ? 'orange' : 'red'}" 
                                 aria-valuenow="${result.stability}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        modalTestResults.innerHTML = resultsHTML;
    },
    runGameTest: function(game, productName, productId) {
        const fps = Math.floor(Math.random() * (game.maxFps - game.minFps + 1)) + game.minFps;
        const stability = Math.floor(Math.random() * 20) + 80;

        this.savePerformanceTest(game, fps, stability, productId);

        return { fps, stability, productName: game.name };
    },
    savePerformanceTest: function(game, fps, stability, productId) {
        fetch('/save-performance-test.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                game: game.code,
                fps: fps,
                stability: stability,
                productId: productId
            })
        }).catch(error => {
            ErrorHandler.logError('performanceTest', error);
        });
    }
};

export default GameTestManager;