<?php
require_once 'functions.php'; // Подключаем файл с функцией checkAccess
checkAccess();
// Подключение к базе данных
$pdo = connect();
// Получение списка опросов
$stmt = $pdo->prepare('SELECT * FROM polls');
$stmt->execute();
$polls = $stmt->fetchAll();
// Получение списка пользователей
$stmt = $pdo->prepare('
SELECT u.id, s.Fio AS Fio 
FROM users u JOIN students s ON u.id = s.id_user 
UNION 
SELECT u.id, p.Fio AS Fio 
FROM users u JOIN parents p ON u.id = p.id_user
');
$stmt->execute();
$users = $stmt->fetchAll();
// Получение списка классов
$stmt = $pdo->prepare('SELECT * FROM classes');
$stmt->execute();
$classes = $stmt->fetchAll();
// Обработка отправки формы
$errorPoll=$errorUser=$errorClass='';
$flagValidation = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['poll_id'])) {
        $errorPoll = 'Выберите опрос';
        $flagValidation = 1;
    }
    if (!isset($_POST['user_or_class'])) {
        $errorUser = 'Выберите класс или пользователя';
        $flagValidation = 1;
    }
    if ($flagValidation === 0) {
        // Получение данных из формы
        $poll_id = $_POST['poll_id'];
        if ($_POST['user_or_class'] === 'user') { // Если выбран пользователь
            $user_id = $_POST['user_id'];
            // Проверка, есть ли уже запись в таблице access для этого пользователя и этого опроса
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM access WHERE user_id = ? AND poll_id = ?');
            $stmt->execute([$user_id, $poll_id]);
            $count = $stmt->fetchColumn();
            if ($count > 0) {
                // Если запись уже существует, выдаем сообщение об ошибке и не добавляем новую запись
                echo "Доступ уже выдан этому пользователю для этого опроса";
            } else {
                // Добавление записи в таблицу access для пользователя
                $stmt = $pdo->prepare('INSERT INTO access (user_id, poll_id) VALUES (?, ?)');
                $stmt->execute([$user_id, $poll_id]);
            }
        } elseif ($_POST['user_or_class'] === 'class') { // Если выбран класс
            $class_id = $_POST['class_id'];
            // Получение списка учеников класса
            $stmt = $pdo->prepare('SELECT * FROM students WHERE class_id = ?');
            $stmt->execute([$class_id]);
            $students = $stmt->fetchAll();
            // Добавление записи в таблицу access для каждого ученика из класса, у которого еще нет доступа к опросу
            foreach ($students as $student) {
                // Проверка, есть ли уже запись в таблице access для этого ученика и этого опроса
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM access WHERE user_id = ? AND poll_id = ?');
                $stmt->execute([$student['id_user'], $poll_id]);
                $count = $stmt->fetchColumn();
                if ($count === 0) {
                    // Если записи еще нет, добавляем новую запись в таблицу access
                    $stmt = $pdo->prepare('INSERT INTO access (user_id, poll_id) VALUES (?, ?)');
                    $stmt->execute([$student['id_user'], $poll_id]);
                }
            }
        }
        isAuth();
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Добавление доступа к опросу</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            font-size: 16px;
            line-height: 1.5;
        }
        .wrapper {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }
        span {
            color: red;
        }
        .content {
            max-width: 800px;
            width: 100%;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin-top: 30px;
        }

        h1 {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 30px;
        }
        form {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        label {
            font-weight: bold;
            margin-bottom: 10px;
        }

        select {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
            border: none;
            background-color: #f2f2f2;
            font-size: 16px;
            line-height: 1.5;
        }

        input[type="radio"] {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
            border: none;
            background-color: #f2f2f2;
            font-size: 16px;
            line-height: 1.5;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            line-height: 1.5;
            margin-top: 20px;
        }

        .part {
            margin-bottom: 20px;
        }

        /* Медиа-запрос для адаптивной верстки */
        @media (max-width: 768px) {
            .wrapper {
                height: auto;
                padding: 20px;
            }

            .content {
                padding: 10px;
            }
        }

        footer {
            background-color: #308631;
            color: white;
            text-align: center;
            position: absolute;
            bottom: 0;
            width: 100%;
        }
        main {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 50px;
        }
        /* Style the navigation menu */
        nav {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            padding:20px;
        }

        nav ul {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        nav li {
            margin: 10px;
        }

        nav a {
            color: #333;
            text-decoration: none;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        nav a:hover {
            background-color: #308631;
            color: #fff;
        }
        .polls li:hover{
            background-color: #308631;
            color: white;
        }
        .polls li:hover a {
            color: white;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <main>
        <nav>
            <ul>
                <li><?php echo(profile()); ?></li>
                <li><?php echo(pollsRole()); ?></li>
            </ul>
        </nav>
    </main>
    <div class="content">
        <h1>Добавление доступа к опросу</h1>
        <form method="post">
            <span class="error"><?php echo $errorPoll; ?></span>
            <div class="part">
                <label for="poll_id">Опрос:</label>
                <select name="poll_id" id="poll_id">
                    <?php foreach ($polls as $poll): ?>
                        <option value="<?= $poll['id'] ?>"><?= $poll['title'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <span class="error"><?php echo $errorUser; ?></span>
            <div class="part">
                <label for="user_radio">Пользователь:</label>
                <input type="radio" name="user_or_class" id="user_radio" value="user" checked>
                <select name="user_id" id="user_id">
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= $user['Fio'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="part">
                <label for="class_radio">Класс:</label>
                <input type="radio" name="user_or_class" id="class_radio" value="class">
                <select name="class_id" id="class_id">
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= $class['id'] ?>">Класс <?= $class['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <input type="submit"></input>
        </form>
    </div>
</div>
<!--<footer>-->
<!--    <p>Все права защищены &copy; --><?php //echo date("Y"); ?><!--</p>-->
<!--</footer>-->
</body>
</html>