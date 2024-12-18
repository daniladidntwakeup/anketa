<?php
require_once 'functions.php';
checkAccessAdmin();
$classErr = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Подключаемся к базе данных
    $pdo = connect();
    // Получаем данные из формы
    $flagValidation = 0;
    $class = check_input($_POST['class']);
    if (empty($class)) {
        $classErr = "Введите название класса";
        $flagValidation = 1;
    }
    if ($flagValidation === 0) {
        $pdo = connect();
        // Вставляем данные опроса в таблицу polls
        $sql = "SELECT COUNT(*) FROM classes WHERE name = :name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['name' => $class]);
        $count = $stmt->fetchColumn();
        if ($count > 0) {
            $loginErr = 'Такое название класса уже существует';
            $flagValidation = 1;
        }
    }
    if ($flagValidation === 0) {
        // Создаем класс
        $stmt = $pdo->prepare("INSERT INTO classes (name) VALUES (:name)");
        $stmt->bindParam(':name', $class);
        $stmt->execute();
    }
}
?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Добавление нового класса</title>
        <style>
            /* Стили для основного блока wrapper */
            body {
                margin: 0;
                font-family: Arial, sans-serif;
                font-size: 16px;
                line-height: 1.5;
            }

            span {
                color: red;
                word-wrap: break-word;
            }

            .wrapper {
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
                align-items: center;
                height: 90vh;
            }

            /* Стили для формы */
            form {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                background-color: #fff;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
                margin-top: 30px;
                width:30vw;
            }

            label {
                margin-bottom: 10px;
                font-weight: bold;
            }

            input[type="text"],
            input[type="password"],
            input[type="tel"],
            input[type="email"],
            select {
                padding: 8px;
                border-radius: 5px;
                border: 1px solid #ccc;
                margin-bottom: 15px;
                width: 100%;
            }
            /* Стили для заголовка */
            h1 {
                margin-bottom: 30px;
                font-size: 28px;
                font-weight: bold;
            }

            footer {
                background-color: #308631;
                color: white;
                text-align: center;
                position: absolute;
                bottom: 0;
                width: 100%;
            }

            /* Медиа-запрос для адаптивной верстки */
            @media (max-width: 768px) {
                form {
                    width: 80%;
                }
            }

            /* Style the main content */
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
                padding: 20px;
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
            input[type="submit"] {
                background-color: #4CAF50;
                color: #fff;
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
                margin-top: 15px;
            }

            input[type="submit"]:hover {
                background-color: #3e8e41;
            }
            .buttons
            {
                flex-direction: row;
            }
        </style>
    </head>
    <body>
    <div class="wrapper">
        <main>
            <nav>
                <ul>
                    <li><?php echo(profile()); ?></li>
                    <li><a href="EditClass.php">Список классов</a></li>
                </ul>
            </nav>
        </main>
        <h1>Добавление нового класса</h1>
        <form method="post">
            <span class="error"><?php echo $classErr; ?></span>
            <label for="class">Название класса:</label>
            <input type="text" id="class" name="class" required>
            <div class="buttons">
                <input type="submit" value="Добавить">
            </div>
        </form>
    </div>
    </body>
    </html>
<?php
