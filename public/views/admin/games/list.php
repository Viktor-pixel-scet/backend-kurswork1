<?php

?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Адміністрування - Ігри</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/backend-kurswork/public/assets/css/admin-games.css">
</head>
<body class="bg-light">
<?php include __DIR__ . '/../../../includes/admin_header.php'; ?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1>Управління іграми</h1>
            <a href="admin.php?action=games&subaction=add" class="btn btn-warning">
                <i class="fas fa-plus"></i> Додати гру
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="gamesTable">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Код гри</th>
                        <th>Назва</th>
                        <th>Мін. FPS</th>
                        <th>Макс. FPS</th>
                        <th>Дії</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($games)): ?>
                        <?php foreach ($games as $game): ?>
                            <tr>
                                <td><?= $game['id'] ?></td>
                                <td><?= htmlspecialchars($game['game_code']) ?></td>
                                <td><?= htmlspecialchars($game['game_name']) ?></td>
                                <td><?= $game['min_fps'] ?></td>
                                <td><?= $game['max_fps'] ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="admin.php?action=games&subaction=edit&id=<?= $game['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger delete-game" data-id="<?= $game['id'] ?>" data-name="<?= htmlspecialchars($game['game_name']) ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Немає доступних ігор</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteGameModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Підтвердження видалення</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Ви дійсно хочете видалити гру "<span id="gameName"></span>"?</p>
                <p class="text-danger">Ця дія незворотна!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Видалити</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../includes/admin_footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        let gameIdToDelete = null;

        $('.delete-game').click(function() {
            gameIdToDelete = $(this).data('id');
            const gameName = $(this).data('name');
            $('#gameName').text(gameName);
            $('#deleteGameModal').modal('show');
        });

        $('#confirmDelete').click(function() {
            if (gameIdToDelete) {
                $.ajax({
                    url: 'admin.php?action=games&subaction=delete',
                    type: 'POST',
                    data: { id: gameIdToDelete },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            location.reload();
                        } else {
                            alert(response.message || 'Помилка під час видалення гри');
                        }
                    },
                    error: function() {
                        alert('Виникла помилка при виконанні запиту');
                    },
                    complete: function() {
                        $('#deleteGameModal').modal('hide');
                        gameIdToDelete = null;
                    }
                });
            }
        });

        $('#categoryFilter').change(function() {
            const category = $(this).val();

            if (category === 'all') {
                $('#gamesTable tbody tr').show();
            } else {
                $('#gamesTable tbody tr').hide();
                $(`#gamesTable tbody tr td:nth-child(6):contains("${category}")`).parent('tr').show();
            }
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        const table = document.getElementById('gamesTable');
        if (!table) return;

        const headers = table.querySelectorAll('thead th');
        const tbody = table.querySelector('tbody');

        let currentSort = {
            column: null,
            direction: 'asc'
        };

        headers.forEach((header, index) => {
            if (header.textContent.trim().includes('Дії') ||
                header.textContent.trim().includes('Дія') ||
                header.classList.contains('no-sort')) {
                header.classList.add('no-sort');
                header.style.cursor = 'default';
                return;
            }

            header.style.cursor = 'pointer';
            header.style.userSelect = 'none';
            header.style.position = 'relative';
            header.style.paddingRight = '25px';

            const sortIcon = document.createElement('span');
            sortIcon.className = 'sort-indicator';
            sortIcon.innerHTML = '<i class="fas fa-sort text-white opacity-50"></i>';
            sortIcon.style.position = 'absolute';
            sortIcon.style.right = '8px';
            sortIcon.style.top = '50%';
            sortIcon.style.transform = 'translateY(-50%)';
            sortIcon.style.transition = 'all 0.3s ease';
            header.appendChild(sortIcon);

            header.addEventListener('mouseenter', () => {
                if (currentSort.column !== header) {
                    sortIcon.querySelector('i').style.opacity = '0.7';
                }
            });

            header.addEventListener('mouseleave', () => {
                if (currentSort.column !== header) {
                    sortIcon.querySelector('i').style.opacity = '0.5';
                }
            });

            header.addEventListener('click', () => {
                sortTable(header, index);
            });
        });

        function sortTable(clickedHeader, columnIndex) {
            if (currentSort.column === clickedHeader) {
                currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort.direction = 'asc';
            }

            const previousColumn = currentSort.column;
            currentSort.column = clickedHeader;

            if (previousColumn && previousColumn !== clickedHeader) {
                const prevIcon = previousColumn.querySelector('.sort-indicator i');
                prevIcon.className = 'fas fa-sort text-white opacity-50';
                prevIcon.style.color = 'white';
            }

            const currentIcon = clickedHeader.querySelector('.sort-indicator i');
            const isAscending = currentSort.direction === 'asc';

            currentIcon.className = `fas fa-sort-${isAscending ? 'up' : 'down'} text-white`;
            currentIcon.style.opacity = '1';
            currentIcon.style.color = '#ffd43b';

            tbody.style.opacity = '0.6';
            tbody.style.pointerEvents = 'none';

            const rows = Array.from(tbody.querySelectorAll('tr:not(.empty-row)'));

            rows.sort((rowA, rowB) => {
                const cellA = rowA.children[columnIndex];
                const cellB = rowB.children[columnIndex];

                if (!cellA || !cellB) return 0;

                let valueA = cellA.textContent.trim();
                let valueB = cellB.textContent.trim();

                const numA = parseFloat(valueA.replace(/[^\d.-]/g, ''));
                const numB = parseFloat(valueB.replace(/[^\d.-]/g, ''));

                let comparison = 0;

                if (!isNaN(numA) && !isNaN(numB)) {
                    comparison = numA - numB;
                } else {
                    comparison = valueA.localeCompare(valueB, 'uk', {
                        numeric: true,
                        sensitivity: 'base'
                    });
                }

                return currentSort.direction === 'asc' ? comparison : -comparison;
            });

            setTimeout(() => {
                tbody.innerHTML = '';

                rows.forEach((row, index) => {
                    row.style.opacity = '0';
                    row.style.transform = 'translateY(-10px)';
                    tbody.appendChild(row);

                    setTimeout(() => {
                        row.style.transition = 'all 0.3s ease';
                        row.style.opacity = '1';
                        row.style.transform = 'translateY(0)';
                    }, index * 50);
                });

                if (rows.length === 0) {
                    const emptyRow = document.createElement('tr');
                    emptyRow.className = 'empty-row';
                    emptyRow.innerHTML = '<td colspan="7" class="text-center py-4 text-muted">Немає доступних ігор</td>';
                    tbody.appendChild(emptyRow);
                }

                setTimeout(() => {
                    tbody.style.opacity = '1';
                    tbody.style.pointerEvents = 'auto';
                }, 300);
            }, 100);

            clickedHeader.style.transform = 'scale(0.98)';
            setTimeout(() => {
                clickedHeader.style.transition = 'transform 0.2s ease';
                clickedHeader.style.transform = 'scale(1)';
            }, 100);
        }

        const style = document.createElement('style');
        style.textContent = `
        #gamesTable thead th {
            transition: all 0.3s ease;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
        }

        #gamesTable thead th:not(.no-sort):hover {
            background: linear-gradient(135deg, #5c7cfa 0%, #4263eb 100%);
            transform: translateY(-1px);
        }

        #gamesTable thead th:not(.no-sort):active {
            transform: translateY(0px) scale(0.98);
        }

        .sort-indicator i {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        #gamesTable tbody tr {
            transition: all 0.3s ease;
        }

        #gamesTable tbody tr:hover {
            background-color: rgba(76, 110, 245, 0.08) !important;
            transform: translateX(2px);
        }

        #gamesTable tbody.sorting {
            opacity: 0.6;
            pointer-events: none;
        }

        .empty-row td {
            background: linear-gradient(135deg, #f8f9fc 0%, #ffffff 100%);
            font-style: italic;
            color: var(--text-muted);
        }

        @media (max-width: 768px) {
            #gamesTable thead th {
                padding-right: 20px !important;
            }

            .sort-indicator {
                right: 4px !important;
            }

            .sort-indicator i {
                font-size: 0.75rem;
            }
        }

        #gamesTable thead th:not(.no-sort) {
            position: relative;
            overflow: hidden;
        }

        #gamesTable thead th:not(.no-sort)::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s ease;
        }

        #gamesTable thead th:not(.no-sort):hover::before {
            left: 100%;
        }
    `;
        document.head.appendChild(style);

        const tableContainer = table.closest('.table-responsive');
        if (tableContainer) {
            const hint = document.createElement('div');
            hint.className = 'sort-hint text-muted small mb-2';
            hint.innerHTML = '<i class="fas fa-info-circle me-1"></i>Натисніть на заголовок колонки для сортування';
            hint.style.opacity = '0.7';
            hint.style.transition = 'opacity 0.3s ease';

            tableContainer.parentNode.insertBefore(hint, tableContainer);

            let hintHidden = false;
            headers.forEach(header => {
                header.addEventListener('click', () => {
                    if (!hintHidden) {
                        hint.style.opacity = '0';
                        setTimeout(() => {
                            hint.style.display = 'none';
                        }, 300);
                        hintHidden = true;
                    }
                });
            });
        }

        table.setAttribute('aria-label', 'Таблиця ігор з можливістю сортування');
        headers.forEach(header => {
            header.setAttribute('role', 'button');
            header.setAttribute('aria-label', `Сортувати за ${header.textContent.trim()}`);
        });
    });
</script>
</body>
</html>