# Todo List API

Необхідно реалізувати API, яке дозволить керувати списком завдань.

API має надавати можливість:
- отримати список своїх завдань відповідно до фільтра
- створити своє завдання
- редагувати своє завдання
- відзначити своє завдання як виконане
- видалити своє завдання

При отриманні списку завдань користувач повинен мати можливість:
- фільтрувати по полю status
- фільтрувати по полю priority
- фільтрувати по полю title, description (має бути реалізований повнотекстовий пошук)
- Сортувати за createdAt, completedAt, priority - потрібна підтримка сортування за двома полями. Наприклад, priority desc,  createdAt asc.

Користувач не повинен мати можливості:
- змінювати чи видаляти чужі завдання
- видалити вже виконане завдання
- відзначити як виконану задачу, у якої є невиконані завдання

Кожна задача повинна мати такі властивості:
- status (todo, done)
- priority (1...5)
- title
- description
- createdAt
- completedAt

Будь-яка задача може мати підзадачі, рівень вкладеності підзадач має бути необмежений.

Мінімальна версія: PHP 8.1
Фреймворк: Laravel / Symfony
Код необхідно завантажити до публічного репозиторію

## Рекомендації

### Оформлення
- Супроводжувати тестове грамотним та зрозумілим README.md
- Супроводжувати тестове Open API документацією
- Загорнути проект у Docker Compose
- Використовувати для документації та коментарів у коді тільки англійська мова

### Архітектура
- Використовувати якнайбільше вбудованого у фреймворк функціоналу
- Використовувати сервісний шар для бізнес логіки
- Використовувати репозиторії для отримання даних із БД
- Використовувати DTO
- Використовувати Enum
- Використовувати строгу типізацію
- Використовувати REST підхід для роутингу
- Використовувати рекурсію або посилання для формування дерева тасок

### Код стайл
- Слідувати PSR-12
- Відмовитися від роботи з масивами

### База даних
- Використовувати сидери / фікстури для наповнення БД
- Використовувати індекси