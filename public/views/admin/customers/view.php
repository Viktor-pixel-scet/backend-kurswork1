<?php

?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Адміністрування - Інформація про клієнта</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/backend-kurswork/public/assets/css/admin-customers.css">
</head>
<body class="bg-light">
<?php include __DIR__ . '/../../../includes/admin_header.php'; ?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1><i class="fas fa-user me-2"></i>Інформація про клієнта</h1>
            <a href="admin.php?action=customers" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Повернутися до списку
            </a>
        </div>
    </div>

    <?php if ($customer): ?>
        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Особисті дані</h5>
                    </div>
                    <div class="card-body">
                        <table class="table info-table">
                            <tr>
                                <th style="width: 150px;">ID:</th>
                                <td><span class="badge bg-secondary"><?= $customer['id'] ?></span></td>
                            </tr>
                            <tr>
                                <th>Ім'я:</th>
                                <td><?= htmlspecialchars($customer['name']) ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td>
                                    <a href="mailto:<?= htmlspecialchars($customer['email']) ?>" class="text-decoration-none">
                                        <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($customer['email']) ?>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>Телефон:</th>
                                <td>
                                    <a href="tel:<?= htmlspecialchars($customer['phone']) ?>" class="text-decoration-none">
                                        <i class="fas fa-phone me-1"></i><?= htmlspecialchars($customer['phone']) ?>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>Адреса:</th>
                                <td><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($customer['address']) ?></td>
                            </tr>
                            <tr>
                                <th>Дата реєстрації:</th>
                                <td>
                                    <i class="fas fa-calendar-alt me-1" ></i>
                                    <?= isset($customer['created_at']) ? date('d.m.Y H:i', strtotime($customer['created_at'])) : 'Невідомо' ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Статистика замовлень</h5>
                    </div>
                    <div class="card-body">
                        <table class="table info-table">
                            <tr>
                                <th>Кількість замовлень:</th>
                                <td><span class="badge bg-primary fs-6"><?= count($orders) ?></span></td>
                            </tr>
                            <tr>
                                <th>Загальна сума:</th>
                                <td>
                                    <span class="text-success fw-bold fs-5">
                                        <?php
                                        $totalAmount = 0;
                                        foreach ($orders as $order) {
                                            $totalAmount += (float)$order['total_amount'];
                                        }
                                        echo number_format($totalAmount, 2) . ' грн';
                                        ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Останнє замовлення:</th>
                                <td>
                                    <?php
                                    $lastOrderDate = 'Немає замовлень';
                                    if (!empty($orders)) {
                                        $lastOrder = $orders[0]; // Припускаємо, що замовлення відсортовані за датою
                                        $lastOrderDate = '<i class="fas fa-clock me-1"></i>' . date('d.m.Y H:i', strtotime($lastOrder['created_at']));
                                    }
                                    echo $lastOrderDate;
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Історія замовлень</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="ordersTable">
                        <thead>
                        <tr>
                            <th class="sortable-header">ID</th>
                            <th class="sortable-header" >Дата</th>
                            <th class="sortable-header">Статус</th>
                            <th class="sortable-header">Сума</th>
                            <th class="no-sort">Дії</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?= $order['id'] ?></td>
                                    <td style="color: white" data-sort="<?= strtotime($order['created_at']) ?>">
                                        <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClasses = [
                                            'new' => 'badge bg-primary status-badge',
                                            'processing' => 'badge bg-info status-badge',
                                            'shipped' => 'badge bg-warning status-badge',
                                            'delivered' => 'badge bg-success status-badge',
                                            'cancelled' => 'badge bg-danger status-badge'
                                        ];
                                        $statusLabels = [
                                            'new' => 'Нове',
                                            'processing' => 'В обробці',
                                            'shipped' => 'Відправлено',
                                            'delivered' => 'Доставлено',
                                            'cancelled' => 'Скасовано'
                                        ];
                                        $status = $order['status'] ?? 'new';
                                        $statusClass = $statusClasses[$status] ?? 'badge bg-secondary status-badge';
                                        $statusLabel = $statusLabels[$status] ?? 'Невідомо';
                                        ?>
                                        <span class="<?= $statusClass ?>" data-sort="<?= $status ?>"><?= $statusLabel ?></span>
                                    </td>
                                    <td data-sort="<?= (float)$order['total_amount'] ?>">
                                        <span class="fw-bold text-success"><?= number_format((float)$order['total_amount'], 2) ?> грн</span>
                                    </td>
                                    <td>
                                        <a href="admin.php?action=orders&subaction=view&id=<?= $order['id'] ?>"
                                           class="btn btn-sm btn-info" title="Переглянути деталі замовлення">
                                            <i class="fas fa-eye"></i> Деталі
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-shopping-cart fa-2x mb-3 d-block"></i>
                                    Клієнт не має замовлень
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>Клієнта не знайдено
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../../includes/admin_footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const table = document.getElementById('ordersTable');
        const headers = table.querySelectorAll('thead th');
        const tbody = table.querySelector('tbody');

        let sortedColumn = null;
        let ascending = true;

        headers.forEach(header => {
            if (header.classList.contains('no-sort')) return;

            header.style.cursor = 'pointer';

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

                if (rowsArray.length === 1 && rowsArray[0].children[0].colSpan) {
                    return;
                }

                rowsArray.sort((a, b) => {
                    let aValue, bValue;

                    const aSort = a.children[index].getAttribute('data-sort');
                    const bSort = b.children[index].getAttribute('data-sort');

                    if (aSort !== null && bSort !== null) {
                        if (index === 1) { // Дата
                            aValue = parseInt(aSort) || 0;
                            bValue = parseInt(bSort) || 0;
                            return ascending ? aValue - bValue : bValue - aValue;
                        } else if (index === 3) {
                            aValue = parseFloat(aSort) || 0;
                            bValue = parseFloat(bSort) || 0;
                            return ascending ? aValue - bValue : bValue - aValue;
                        } else if (index === 2) { // Статус
                            const statusOrder = { 'new': 1, 'processing': 2, 'shipped': 3, 'delivered': 4, 'cancelled': 5 };
                            aValue = statusOrder[aSort] || 0;
                            bValue = statusOrder[bSort] || 0;
                            return ascending ? aValue - bValue : bValue - aValue;
                        }
                    }

                    if (index === 0) {
                        const aText = a.children[index].innerText.trim();
                        const bText = b.children[index].innerText.trim();
                        aValue = parseInt(aText) || 0;
                        bValue = parseInt(bText) || 0;
                        return ascending ? aValue - bValue : bValue - aValue;
                    }

                    // Для текстових колонок
                    const aText = a.children[index].innerText.trim();
                    const bText = b.children[index].innerText.trim();
                    return ascending
                        ? aText.localeCompare(bText, 'uk', { numeric: true, sensitivity: 'base' })
                        : bText.localeCompare(aText, 'uk', { numeric: true, sensitivity: 'base' });
                });

                rowsArray.forEach(row => tbody.appendChild(row));
            });
        });

        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
</body>
</html>