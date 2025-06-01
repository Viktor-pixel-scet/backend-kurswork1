document.addEventListener("DOMContentLoaded", function() {
    function fetchData(url, method = 'GET', data = null) {
        const options = {
            method: method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        if (data && method !== 'GET') {
            if (data instanceof FormData) {
                options.body = data;
            } else {
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(data);
            }
        }

        return fetch(url, options)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            });
    }

    document.querySelectorAll('.delete-item').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            if (!confirm('Ви впевнені, що хочете видалити цей елемент?')) {
                return;
            }

            const id = this.dataset.id;
            const type = this.dataset.type;
            let url;

            switch (type) {
                case 'product':
                    url = 'admin.php?action=products&subaction=delete';
                    break;
                case 'game':
                    url = 'admin.php?action=games&subaction=delete';
                    break;
                default:
                    return;
            }

            const formData = new FormData();
            formData.append('id', id);

            fetchData(url, 'POST', formData)
                .then(data => {
                    if (data.success) {
                        document.getElementById(`item-${id}`).remove();
                        showAlert('success', data.message);
                    } else {
                        showAlert('danger', data.message);
                    }
                })
                .catch(error => {
                    showAlert('danger', 'Виникла помилка при спробі видалення');
                    console.error(error);
                });
        });
    });

    document.querySelectorAll('.update-order-status').forEach(select => {
        select.addEventListener('change', function() {
            const orderId = this.dataset.orderId;
            const status = this.value;

            const formData = new FormData();
            formData.append('id', orderId);
            formData.append('status', status);

            fetchData('admin.php?action=orders&subaction=update-status', 'POST', formData)
                .then(data => {
                    if (data.success) {
                        showAlert('success', data.message);
                    } else {
                        showAlert('danger', data.message);
                        // Відновлення попереднього значення
                        this.value = this.dataset.oldStatus;
                    }
                })
                .catch(error => {
                    showAlert('danger', 'Помилка при оновленні статусу');
                    console.error(error);
                    // Відновлення попереднього значення
                    this.value = this.dataset.oldStatus;
                });
        });
    });

    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        const alertContainer = document.getElementById('alert-container');
        if (alertContainer) {
            alertContainer.appendChild(alertDiv);

            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    }
});

