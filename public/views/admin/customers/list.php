<?php

?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Адміністрування - Клієнти</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/backend-kurswork/public/assets/css/admin-customers.css">
</head>
<body class="bg-light">
<?php include __DIR__ . '/../../../includes/admin_header.php'; ?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1><i class="fas fa-users me-2"></i>Управління клієнтами</h1>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="customersTable">
                    <thead>
                    <tr>
                        <th class="sortable-header">ID</th>
                        <th class="sortable-header">Ім'я</th>
                        <th class="sortable-header">Email</th>
                        <th class="sortable-header">Телефон</th>
                        <th class="sortable-header">Адреса</th>
                        <th class="sortable-header">Дата реєстрації</th>
                        <th class="no-sort">Дії</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($customers)): ?>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?= $customer['id'] ?></td>
                                <td style="color: white"><?= htmlspecialchars($customer['name']) ?></td>
                                <td style="color: white"><?= htmlspecialchars($customer['email']) ?></td>
                                <td style="color: white"><?= htmlspecialchars($customer['phone']) ?></td>
                                <td style="color: white"><?= htmlspecialchars($customer['address']) ?></td>
                                <td style="color: white" data-sort="<?= isset($customer['created_at']) ? strtotime($customer['created_at']) : 0 ?>">
                                    <?= isset($customer['created_at']) ? date('d.m.Y H:i', strtotime($customer['created_at'])) : 'Невідомо' ?>
                                </td>
                                <td>
                                    <a href="admin.php?action=customers&subaction=view&id=<?= $customer['id'] ?>"
                                       class="btn btn-sm btn-info" title="Переглянути деталі клієнта">
                                        <i class="fas fa-eye"></i> Деталі
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-users fa-2x mb-3 d-block"></i>
                                Немає зареєстрованих клієнтів
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../includes/admin_footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const table = document.getElementById('customersTable');
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

                    if (index === 5) {
                        const aSort = a.children[index].getAttribute('data-sort');
                        const bSort = b.children[index].getAttribute('data-sort');
                        aValue = parseInt(aSort) || 0;
                        bValue = parseInt(bSort) || 0;
                        return ascending ? aValue - bValue : bValue - aValue;
                    }

                    let aText = a.children[index].innerText.trim();
                    let bText = b.children[index].innerText.trim();

                    if (index === 0) {
                        aValue = parseInt(aText) || 0;
                        bValue = parseInt(bText) || 0;
                        return ascending ? aValue - bValue : bValue - aValue;
                    }

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