=========================================================
                   Web Calendar Project
=========================================================

Описание:
---------
Този проект представлява система за управление на презентации,
която позволява на студенти да се записват за презентации, 
а на преподаватели да ги одобряват.

Основни функционалности:
- Регистрация и вход на потребители (студенти и преподаватели)
- Добавяне на нови потребители (само за преподаватели)
- Записване на слотове за презентации с факултетен номер и име на тема
- Статуси на слотовете: Свободен, Изчакване, Зает
- Одобрение на презентации от преподаватели
- Препоръчване на презентации за студенти, използвайки Recommendation клас
- Радар: визуализация на категориите на одобрените презентации
- Автоматично почистване на отменени презентации
- Logout
- Динамична навигация според ролята на потребителя

---------------------------------------------------------
Структура на проекта:
--------------------
project/
│
├── config/
│   └── config.php           # Настройки за базата данни и базов URL
│
├── public/
│   ├── index.php            # Вход
│   ├── register.php         # Регистрация
│   ├── calendar.php         # Календар и записване на слотове
│   ├── approve_presentations.php # Одобрение на презентации (само за учители)
│   ├── recommended.php      # Страница за препоръчани презентации
│   ├── add_user.php         # Добавяне на нов потребител (само за учители)
│   ├── radar.php            # Визуализация на радар (категории презентации)
│   ├── logout.php           # Logout
│   └── header.php           # Динамично меню според ролята на потребителя
│
├── src/
│   ├── User.php             # Клас за управление на потребители
│   ├── Calendar.php         # Клас за слотовете и записване/отмяна
│   ├── Presentation.php     # Клас за презентациите
│   └── Recommendation.php   # Клас за изчисляване на препоръки
│
└── sql/
    └── schema.sql           # SQL скрипт за създаване на базата данни и таблиците
            

---------------------------------------------------------
Описание на файловете:
----------------------
config/config.php
  - Настройки за база данни и базов URL

public/header.php
  - Динамично меню според ролята на потребителя
  - Линкове: Вход, Регистрация, Календар, Одобрение, Добавяне на потребител, Препоръчани, Радар, Logout

public/index.php
  - Вход за системата
  - Проверка на имейл и парола чрез User.php
  - Пренасочване към calendar.php след успешен вход

public/register.php
  - Регистрация на нов студент
  - Използва User.php за създаване на потребител
  - Проверка за дублиран имейл

public/calendar.php
  - Показва всички слотове за презентации
  - Студентите попълват фак. номер и тема при запис
  - Статуси на слотовете: Свободен, Изчакване, Зает
  - Използва Calendar.php и Presentation.php
  - Поддържа размяна на слотове и проверка за конфликти

public/approve_presentations.php
  - Достъп само за учители
  - Списък с чакащи презентации (approved = 0)
  - Учителят може да одобри презентация
  - Автоматично премахва отменени презентации

public/recommended.php
  - Показва препоръчани теми за студенти, които нямат записана тема
  - Използва Presentation.php за одобрени презентации и Recommendation.php за score
  - Таблицата съдържа: Тема, Категория, Score

public/radar.php
  - Визуализация на радар на презентации
  - Показва всички одобрени презентации с категории
  - Не показва отменени или изтрити презентации
  - Използва Chart.js за визуализация

public/add_user.php
  - Добавяне на нов потребител (само за учители)
  - Използва User.php

public/logout.php
  - Изчиства сесията на потребителя и пренасочва към index.php

src/User.php
  - Клас за работа с потребители
  - Функции:
      - createUser($email, $password, $role)
      - login($email, $password)

src/Calendar.php
  - Клас за управление на слотовете
  - Функции:
      - getSlots()
      - bookSlot()
      - cancelSlotByUser()
      - autoAssignPresentations()
      - hasConflict()

src/Presentation.php
  - Клас за презентациите
  - Функции:
      - submit()
      - getPending()
      - approve()
      - getApproved()
      - getApprovedForRadar()
      - getApprovedForStudent()
      - cancel()
      - cleanupCancelled()

src/Recommendation.php
  - Клас за изчисляване на препоръки
  - Функции:
      - calculateMatch(array $student, array $presentation): float
      - getRecommendations(array $studentPreferences, array $presentations, $top = 5)

sql/schema.sql
  - Създава базата данни web_calendar и таблиците: users, topics, slots, student_preferences
  - Добавя примерни потребители, теми, слотове и предпочитания
    Примерни потребители:
      INSERT INTO users (email, password, role) VALUES
        ('teacher@fmi.bg', '$2y$10$z5K8BzFJ1kM07lXhN3/rseM1v5f8yP7T.Qz4YYzZQ9lD0oDvq0l2G', 'teacher'),
        ('student1@fmi.bg', '$2y$10$K9V6TqYhH6uB1wLk3/F7eO2VQnTz9eOQYlK2r1q2V4G1dR5h2dFSe', 'student'),
        ('student2@fmi.bg', '$2y$10$K9V6TqYhH6uB1wLk3/F7eO2VQnTz9eOQYlK2r1q2V4G1dR5h2dFSe', 'student');
    Примерни теми:
      INSERT INTO topics (title, category, approved) VALUES
        ('Frontend Frameworks Comparison', 'frontend', 1),
        ('Backend API Security', 'backend', 1),
        ('Basics of Databases', 'basics', 1),
        ('Latest Web Technologies', 'technologies', 1),
        ('React vs Angular', 'frontend', 0),
        ('Node.js Performance', 'backend', 0);
    Примерни слотове:
      INSERT INTO slots (date, time, user_id) VALUES
        ('2026-01-30', '09:00', NULL),
        ('2026-01-30', '11:00', NULL),
        ('2026-01-31', '10:00', NULL),
        ('2026-01-31', '14:00', NULL),
        ('2026-02-01', '09:00', NULL);
    Примерни предпочитания:
      INSERT INTO student_preferences (student_id, category, weight) VALUES
        (2, 'frontend', 5),
        (2, 'backend', 3),
        (2, 'basics', 2),
        (2, 'technologies', 4),
        (3, 'frontend', 2),
        (3, 'backend', 5),
        (3, 'basics', 3),
        (3, 'technologies', 1);

---------------------------------------------------------
Инструкции за стартиране:
--------------------------
1. Клонирай проекта в директорията на XAMPP (htdocs/project)
2. Импортирай sql/schema.sql в phpMyAdmin
3. Увери се, че config/config.php има правилните настройки за базата
4. Стартирай XAMPP и отвори:
   - http://localhost/project/public/index.php – Вход
   - http://localhost/project/public/register.php – Регистрация
   - http://localhost/project/public/calendar.php – Календар
   - http://localhost/project/public/recommended.php – Препоръчани презентации
   - http://localhost/project/public/radar.php – Радар
5. Примерни потребители:
   - Учител: teacher@fmi.bg / admin123
   - Студенти: student1@fmi.bg / student123, student2@fmi.bg / student123

---------------------------------------------------------
Забележки:
------------
- Навигацията е динамична според ролята (student/teacher)
- Студентите виждат само своите слотове и препоръчани презентации
- Студентите, които са отменили тема, отново виждат препоръчаните теми
- Преподаватели могат да одобряват презентации и да добавят нови потребители
- Радарът показва само одобрени и налични презентации
- Препоръчани презентации използват Recommendation.php за изчисляване на score
- Размяна на слотове между студенти чрез бутон "Размени"
- Автоматично се почистват отменени или изтрити презентации
