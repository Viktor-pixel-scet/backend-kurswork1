<?php
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Адміністрування - Перегляд замовлення</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/backend-kurswork/public/assets/css/admin-orders.css">
</head>
<body class="bg-light">
<?php include __DIR__ . '/../../../includes/admin_header.php'; ?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>Перегляд замовлення #<?= $order['order_number'] ?></h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="admin.php?action=orders" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Назад до списку
            </a>
            <button class="btn btn-primary update-status" data-id="<?= $order['id'] ?>" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                <i class="fas fa-sync-alt"></i> Оновити статус
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="mb-0">Інформація про замовлення</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>ID замовлення:</th>
                            <td><?= $order['id'] ?></td>
                        </tr>
                        <tr>
                            <th>Номер замовлення:</th>
                            <td><?= htmlspecialchars($order['order_number']) ?></td>
                        </tr>
                        <tr>
                            <th>Статус:</th>
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
                        </tr>
                        <tr>
                            <th>Загальна сума:</th>
                            <td><?= number_format($order['total_amount'], 2) ?> грн</td>
                        </tr>
                        <tr>
                            <th>Спосіб оплати:</th>
                            <td>
                                <?php
                                $paymentMethods = [
                                    'cash' => 'Готівка при отриманні',
                                    'card' => 'Оплата карткою',
                                    'bank_transfer' => 'Банківський переказ'
                                ];
                                echo $paymentMethods[$order['payment_method']] ?? $order['payment_method'];
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Дата створення:</th>
                            <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                        </tr>
                        <tr>
                            <th>Дата оновлення:</th>
                            <td><?= date('d.m.Y H:i', strtotime($order['updated_at'])) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="mb-0">Інформація про клієнта</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>ID клієнта:</th>
                            <td><?= $customer['id'] ?></td>
                        </tr>
                        <tr>
                            <th>Ім'я:</th>
                            <td><?= htmlspecialchars($customer['name']) ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?= htmlspecialchars($customer['email']) ?></td>
                        </tr>
                        <tr>
                            <th>Телефон:</th>
                            <td><?= htmlspecialchars($customer['phone']) ?></td>
                        </tr>
                        <tr>
                            <th>Адреса:</th>
                            <td><?= htmlspecialchars($customer['address']) ?></td>
                        </tr>
                        <tr>
                            <th>Дії:</th>
                            <td>
                                <a href="admin.php?action=customers&subaction=view&id=<?= $customer['id'] ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Переглянути профіль
                                </a>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">Товари в замовленні</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="orderItemsTable">
                    <thead>
                    <tr>
                        <th class="sortable">ID</th>
                        <th class="sortable">Товар</th>
                        <th class="sortable">Ціна</th>
                        <th class="sortable">Кількість</th>
                        <th class="sortable">Сума</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($orderItems)): ?>
                        <?php foreach ($orderItems as $item): ?>
                            <tr>
                                <td><?= $item['id'] ?></td>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td><?= number_format($item['price'], 2) ?> грн</td>
                                <td><?= $item['quantity'] ?></td>
                                <td><?= number_format($item['price'] * $item['quantity'], 2) ?> грн</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Немає товарів у замовленні</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Загальна сума:</th>
                        <th><?= number_format($order['total_amount'], 2) ?> грн</th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateStatusModalLabel">Оновлення статусу замовлення</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateStatusForm">
                    <input type="hidden" id="order-id" name="id" value="<?= $order['id'] ?>">
                    <div class="mb-3">
                        <label for="status" class="form-label">Статус</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Очікує</option>
                            <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Обробляється</option>
                            <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>Відправлено</option>
                            <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Доставлено</option>
                            <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Скасовано</option>
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

        const table = document.getElementById('orderItemsTable');
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