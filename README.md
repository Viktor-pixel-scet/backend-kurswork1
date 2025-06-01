# Ноутбук-Маркет (Laptop Market)

Комплексна платформа електронної комерції для перегляду та придбання ноутбуків із повноцінною системою кошика та функціоналом обробки замовлень.

![img_1.png](img_1.png)
![img_2.png](img_2.png)

## Функціональність

Проект реалізує стандартний функціонал інтернет-магазину:

- **Перегляд каталогу**: Огляд усіх доступних ноутбуків з можливістю фільтрації (`index.php`)
- **Деталі продукту**: Доступ до детальної інформації про кожен ноутбук (`product.php`)
- **Управління кошиком**: Додавання, видалення та оновлення кількості товарів (`cart.php`)
- **Процес оформлення замовлення**: Завершення покупок із збором інформації про клієнта (`checkout.php`)
- **Управління запасами**: Перевірка наявності товару в реальному часі
- **Сповіщення користувача**: Інтерактивні повідомлення при додаванні товарів до кошика

## Запуск проекту локально

1. Клонуйте репозиторій
```bash
git clone https://git.ztu.edu.ua/ipz/2023-2027/ipz-23-5/malivskyi-viktor/backend-kurswork.git
cd backend-kurswork
```

2. Налаштуйте веб-сервер (Apache/Nginx) для обслуговування каталогу проекту
3. Імпортуйте базу даних з файлу `laptop_market.sql`
4. Налаштуйте підключення до бази даних у файлі `backend/database/core/Database.php`
5. Відкрийте проект у браузері

## Принципи програмування

1. **DRY (Don't Repeat Yourself)**
    - Усі повторювані елементи (навігаційне меню, підвал) винесені у єдину структуру HTML
    - Функції обробки кошика централізовані в `cart.php`
    - Повторювані запити до бази даних інкапсульовані в репозиторіях

2. **KISS (Keep It Simple, Stupid)**
    - Проста та зрозуміла структура проекту без надмірних абстракцій
    - Лаконічний та читабельний код з мінімумом складності
    - Чіткий поділ на функціональні компоненти

3. **Принцип єдиної відповідальності (Single Responsibility Principle)**
    - Кожен файл має чітко визначену область відповідальності:
        - `Database.php` — підключення до бази даних
        - `ProductRepository.php` — операції з даними про товари
        - `cart.php` — логіка роботи з кошиком
        - `checkout.php` — обробка оформлення замовлення

4. **Принцип розділення відповідальності (Separation of Concerns)**
    - Чіткий поділ на логічні шари:
        - Доступ до даних (PDO та репозиторії)
        - Бізнес-логіка (сервіси та обробники)
        - Представлення (HTML шаблони)
        - Стилізація (CSS/Bootstrap)
        - Клієнтська взаємодія (JavaScript)

5. **Fail Fast**
    - Раннє виявлення помилок через валідацію даних на всіх етапах
    - Перевірка наявності товарів одразу при додаванні до кошика
    - Чіткі повідомлення про помилки для користувачів та розробників

6. **YAGNI (You Aren't Gonna Need It)**
    - Проект містить лише необхідні функції без надлишкового коду
    - Кожна функція має чітке призначення та використання

## Шаблони проектування

1. **Шаблон Front Controller**  
   Централізована обробка всіх запитів, пов'язаних з кошиком, через єдину точку входу. Забезпечує структурований підхід до маршрутизації та обробки запитів.  
   **Файл:** [`cart.php`](backend/orders/cart.php)

2. **Шаблон Repository**  
   Інкапсуляція логіки доступу до даних в окремих класах-репозиторіях для продуктів, замовлень і клієнтів. Забезпечує абстракцію від конкретної реалізації бази даних.  
   **Файли:** [`ProductRepository.php`](backend/repositories/ProductRepository.php), [`OrderRepository.php`](backend/repositories/OrderRepository.php), [`CustomerRepository.php`](backend/repositories/CustomerRepository.php)

3. **Шаблон Service**  
   Реалізація бізнес-логіки в сервісних класах, відокремлених від репозиторіїв та контролерів. Забезпечує чистоту архітектури та повторне використання коду.  
   **Файл:** [`OrderService.php`](backend/services/OrderService.php)

4. **Шаблон Observer**  
   JavaScript-реалізація для відстеження подій додавання товарів у кошик та оновлення інтерфейсу без перезавантаження сторінки.  
   **Файли:** [`cart.js`](public/assets/js/features/cart.js), [`main.js`](public/assets/js/ui/main.js)

5. **Шаблон DTO (Data Transfer Object)**  
   Використання спеціальних об'єктів для передачі даних між шарами застосунку, що забезпечує структуровану передачу інформації.  
   **Файл:** [`OrderDTO.php`](backend/dto/OrderDTO.php)

## Техніки рефакторингу

1. **Екстракція методу**  
   Виділення повторюваних фрагментів коду в окремі функції для підвищення читабельності та можливості повторного використання.  
   **Приклад:** Функція `showAddToCartMessage()` в `cart.php`

2. **Інкапсуляція поля**  
   Обмеження прямого доступу до даних сесії (`$_SESSION['cart']`) шляхом створення спеціальних методів доступу і модифікації.  
   **Файли:** [`cart.php`](backend/orders/cart.php), [`storageManager.js`](public/assets/js/core/storageManager.js)

3. **Заміна умовного оператора поліморфізмом**  
   Використання структури `switch` або окремих блоків `if` замість складних умовних конструкцій для різних операцій з кошиком.  
   **Файл:** [`cart.php`](backend/orders/cart.php)

4. **Впровадження контролера**  
   Централізація обробки запитів через параметр `action` для підвищення гнучкості та масштабованості системи.  
   **Файли:** [`cart.php`](backend/orders/cart.php), [`process_order.php`](public/process_order.php)

5. **Використання підготовлених запитів**  
   Застосування підготовлених запитів PDO замість прямого впровадження значень у SQL для підвищення безпеки та захисту від SQL-ін'єкцій.  
   **Файл:** [`Database.php`](backend/database/Database.php)

6. **Видалення дублювання коду**  
   Усунення повторюваних фрагментів коду за допомогою виділення спільної функціональності в повторно використовувані компоненти.  
   **Файли:** [`productFilter.js`](public/assets/js/features/productFilter.js), [`quantityManager.js`](public/assets/js/ui/quantityManager.js)

## Технології

- **Бекенд**: PHP 8.4
- **Фронтенд**: HTML5, CSS3, JavaScript (ES6+)
- **База даних**: MySQL
- **CSS-фреймворк**: Bootstrap 5
- **Керування версіями**: Git

## Структура проекту

```
backend-kurswork/
├── backend/
│   ├── controller/                    # Контролери
│   │   ├── AdminAuthController.php
│   │   ├── AdminBaseController.php
│   │   ├── AdminController.php
│   │   ├── AdminCustomerController.php
│   │   ├── AdminDashboardController.php
│   │   ├── AdminGameController.php
│   │   ├── AdminOrderController.php
│   │   ├── AdminProductController.php
│   │   └── OrderController.php
│   ├── core/                          # Ядро системи
│   │   ├── Database.php
│   │   └── track_previous_url.php
│   ├── dto/                           # Data Transfer Objects
│   │   ├── AdminDTO.php
│   │   └── OrderDTO.php
│   ├── games_testing/                 # Тестові дані ігор
│   │   └── get-games.php
│   ├── Models/                        # Моделі даних
│   │   ├── AdminAuthModel.php
│   │   ├── AdminModel.php
│   │   ├── CustomerModel.php
│   │   ├── GameModel.php
│   │   ├── OrderModel.php
│   │   └── ProductModel.php
│   ├── orders/                        # Модуль замовлень
│   │   ├── cart.php
│   │   └── checkout.php
│   ├── products/                      # Модуль продуктів
│   │   └── product.php
│   ├── repositories/                  # Репозиторії
│   │   ├── CustomerRepository.php
│   │   ├── OrderRepository.php
│   │   └── ProductRepository.php
│   ├── services/                      # Сервіси
│   │   ├── AdminService.php
│   │   └── OrderService.php
│   └── utils/                         # Утиліти
│       └── compare.php
├── cache/                             # Кеш файли
│   ├── 9cfa7aefcc61936b70aaec6729329eda.json
│   ├── customers_list.json
│   ├── games_list.json
│   ├── orders_list.json
│   └── products_list.json
├── public/                            # Публічні ресурси
│   ├── assets/                        # Статичні ресурси
│   │   ├── css/                       # Стилі CSS
│   │   │   ├── 404.css
│   │   │   ├── 500.css
│   │   │   ├── admin-customers.css
│   │   │   ├── admin-games.css
│   │   │   ├── admin-login.css
│   │   │   ├── admin-orders.css
│   │   │   ├── cart.css
│   │   │   ├── checkout.css
│   │   │   ├── compare.css
│   │   │   ├── dashboard.css
│   │   │   ├── gallery.css
│   │   │   ├── includes.css
│   │   │   ├── index.css
│   │   │   ├── order_confirmation.css
│   │   │   ├── product.css
│   │   │   └── style.css
│   │   └── js/                        # JavaScript файли
│   │       ├── core/                  # Основні JS модулі
│   │       │   ├── errorHandler.js
│   │       │   └── storageManager.js
│   │       ├── features/              # Функціональні модулі
│   │       │   ├── cart.js
│   │       │   ├── gameTest.js
│   │       │   ├── productComparison.js
│   │       │   └── productFilter.js
│   │       ├── ui/                    # UI компоненти
│   │       │   ├── imageHandlers.js
│   │       │   └── quantityManager.js
│   │       ├── admin.js
│   │       └── main.js
│   └── includes/                      # Включення
│       ├── admin_footer.php
│       └── admin_header.php
├── views/                             # Представлення
│   ├── admin/                         # Адмін панель
│   │   ├── customers/
│   │   │   ├── list.php
│   │   │   └── view.php
│   │   ├── games/
│   │   │   ├── form.php
│   │   │   └── list.php
│   │   ├── orders/
│   │   │   ├── list.php
│   │   │   └── view.php
│   │   └── products/
│   │       ├── form.php
│   │       └── list.php
│   ├── dashboard.php
│   ├── login.php
│   ├── order_confirmation.php
│   ├── process_order.php
│   └── errors/                        # Сторінки помилок
│       ├── 404.php
│       └── 500.php
├── vendor/                            
├── .gitignore                         # Git ігнор файл
├── .htaccess                          # Apache конфігурація
├── admin.php                          # Головний адмін файл
├── composer.json                      
├── composer.lock                      
├── config.php                         # Файл конфігурації
├── img.png                            
├── index.php                          # Головна сторінка
├── laptop_market.sql                  # База даних SQL
└── README.md                          # Документація проекту
```

## Автор
 - Малівський Віктор. ІПЗ-23-5

Проєкт розроблено як курсова робота з Backend

