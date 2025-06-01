<?php
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Адміністрування - Товари</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/backend-kurswork/public/assets/css/admin-products.css">
</head>
<body class="bg-light">
<?php include __DIR__ . '/../../../includes/admin_header.php'; ?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1>Управління товарами</h1>
            <a href="admin.php?action=products&subaction=add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Додати товар
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="productsTable">
                    <thead>
                    <tr>
                        <th class="sortable">ID</th>
                        <th class="sortable">Назва</th>
                        <th class="sortable">Бренд</th>
                        <th class="sortable">Ціна</th>
                        <th class="sortable">Запас</th>
                        <th class="no-sort">Дії</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= $product['id'] ?></td>
                                <td><?= htmlspecialchars($product['name']) ?></td>
                                <td><?= htmlspecialchars($product['brand'] ?? '-') ?></td>
                                <td><?= number_format($product['price'], 2) ?> грн</td>
                                <td><?= $product['stock'] ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="admin.php?action=products&subaction=edit&id=<?= $product['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger delete-product" data-id="<?= $product['id'] ?>" data-name="<?= htmlspecialchars($product['name']) ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Немає доступних товарів</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Підтвердження видалення</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Ви дійсно хочете видалити товар "<span id="productName"></span>"?</p>
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
        let productIdToDelete = null;

        $('.delete-product').click(function() {
            productIdToDelete = $(this).data('id');
            const productName = $(this).data('name');
            $('#productName').text(productName);
            $('#deleteProductModal').modal('show');
        });

        $('#confirmDelete').click(function() {
            if (productIdToDelete) {
                $.ajax({
                    url: 'admin.php?action=products&subaction=delete',
                    type: 'POST',
                    data: { id: productIdToDelete },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            location.reload();
                        } else {
                            alert(response.message || 'Помилка під час видалення товару');
                        }
                    },
                    error: function() {
                        alert('Виникла помилка при виконанні запиту');
                    },
                    complete: function() {
                        $('#deleteProductModal').modal('hide');
                        productIdToDelete = null;
                    }
                });
            }
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        const table = document.getElementById('productsTable');
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
    });
</script>
</body>
</html>