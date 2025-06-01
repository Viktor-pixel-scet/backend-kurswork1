<?php

?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Адміністрування - Замовлення</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/backend-kurswork/public/assets/css/admin-orders.css">
</head>
<body>
<?php include __DIR__ . '/../../../includes/admin_header.php'; ?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1><i class="fas fa-shopping-cart me-3"></i>Управління замовленнями</h1>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="orders-table">
                    <thead>
                    <tr>
                        <th class="sortable"><i class="fas fa-hashtag me-2"></i>ID</th>
                        <th class="sortable"><i class="fas fa-receipt me-2"></i>Номер замовлення</th>
                        <th class="sortable"><i class="fas fa-user me-2"></i>Клієнт</th>
                        <th class="sortable"><i class="fas fa-money-bill-wave me-2"></i>Сума</th>
                        <th class="sortable"><i class="fas fa-info-circle me-2"></i>Статус</th>
                        <th class="sortable"><i class="fas fa-calendar me-2"></i>Дата створення</th>
                        <th class="no-sort"><i class="fas fa-cogs me-2"></i>Дії</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= $order['id'] ?></td>
                                <td><span class="order-number"><?= htmlspecialchars($order['order_number']) ?></span></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td><span class="order-amount"><?= number_format($order['total_amount'], 2) ?> грн</span></td>
                                <td>
                                    <?php
                                    $statusClass = [
                                        'pending' => 'warning',
                                        'processing' => 'info',
                                        'shipped' => 'primary',
                                        'delivered' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                    $statusNames = [
                                        'pending' => 'Очікує',
                                        'processing' => 'Обробляється',
                                        'shipped' => 'Відправлено',
                                        'delivered' => 'Доставлено',
                                        'cancelled' => 'Скасовано'
                                    ];
                                    $class = $statusClass[$order['status']] ?? 'secondary';
                                    $name = $statusNames[$order['status']] ?? $order['status'];
                                    ?>
                                    <span class="badge bg-<?= $class ?>"><?= $name ?></span>
                                </td>
                                <td><span class="order-date"><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></span></td>
                                <td>
                                    <div class="order-actions">
                                        <a href="admin.php?action=orders&subaction=view&id=<?= $order['id'] ?>" class="btn btn-sm btn-info" title="Переглянути">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-sm btn-primary update-status" data-id="<?= $order['id'] ?>" data-bs-toggle="modal" data-bs-target="#updateStatusModal" title="Оновити статус">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Немає замовлень</h5>
                                <p class="text-muted">Замовлення з'являться тут після їх створення</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Оновлення статусу замовлення</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateStatusForm">
                    <input type="hidden" id="order-id" name="id">
                    <div class="mb-3">
                        <label for="status" class="form-label">Статус</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pending">Очікує</option>
                            <option value="processing">Обробляється</option>
                            <option value="shipped">Відправлено</option>
                            <option value="delivered">Доставлено</option>
                            <option value="cancelled">Скасовано</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                <button type="button" class="btn btn-primary" id="save-status">Зберегти</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../includes/admin_footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const updateStatusButtons = document.querySelectorAll('.update-status');
        updateStatusButtons.forEach(button => {
            button.addEventListener('click', function() {
                const orderId = this.getAttribute('data-id');
                document.getElementById('order-id').value = orderId;
            });
        });

        document.getElementById('save-status').addEventListener('click', function() {
            const form = document.getElementById('updateStatusForm');
            const formData = new FormData(form);

            fetch('admin.php?action=orders&subaction=update-status', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Помилка оновлення статусу: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Виникла помилка при оновленні статусу');
                });
        });

        const table = document.getElementById('orders-table');
        if (table) {
            const headers = table.querySelectorAll('thead th');
            const tbody = table.querySelector('tbody');
            let sortedColumn = null;
            let ascending = true;

            headers.forEach(header => {
                if (!header.classList.contains('sortable')) return;

                header.addEventListener('click', () => {
                    if (sortedColumn === header) {
                        ascending = !ascending;
                    } else {
                        if (sortedColumn) sortedColumn.classList.remove('sort-asc', 'sort-desc');
                        sortedColumn = header;
                        ascending = true;
                    }

                    headers.forEach(h => {
                        if (h !== header) h.classList.remove('sort-asc', 'sort-desc');
                    });

                    header.classList.toggle('sort-asc', ascending);
                    header.classList.toggle('sort-desc', !ascending);

                    const index = Array.from(headers).indexOf(header);
                    const rowsArray = Array.from(tbody.querySelectorAll('tr'));

                    if (rowsArray.length === 0 || (rowsArray.length === 1 && rowsArray[0].children[0].getAttribute('colspan'))) {
                        return;
                    }

                    rowsArray.sort((a, b) => {
                        let aText = a.children[index].innerText.trim();
                        let bText = b.children[index].innerText.trim();

                        if (index === 4) {
                            aText = a.children[index].querySelector('.badge').innerText.trim();
                            bText = b.children[index].querySelector('.badge').innerText.trim();
                        }

                        if (index === 5) {
                            const aDate = new Date(a.children[index].innerText.split(' ')[0].split('.').reverse().join('-'));
                            const bDate = new Date(b.children[index].innerText.split(' ')[0].split('.').reverse().join('-'));
                            return ascending ? aDate - bDate : bDate - aDate;
                        }

                        const aNum = parseFloat(aText.replace(/[^\d.-]/g, ''));
                        const bNum = parseFloat(bText.replace(/[^\d.-]/g, ''));

                        if (!isNaN(aNum) && !isNaN(bNum)) {
                            return ascending ? aNum - bNum : bNum - aNum;
                        } else {
                            return ascending
                                ? aText.localeCompare(bText, 'uk')
                                : bText.localeCompare(aText, 'uk');
                        }
                    });

                    rowsArray.forEach(row => tbody.appendChild(row));
                });
            });
        }
    });
</script>
</body>
</html>