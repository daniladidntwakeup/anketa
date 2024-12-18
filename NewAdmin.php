<?php
require_once 'functions.php';
checkAccessAdmin();
$loginErr = $passErr = $fioErr = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Подключаемся к базе данных
    $pdo = connect();
    // Получаем данные из формы
    $flagValidation = 0;
    $login = check_input($_POST['login']);
    $password = check_input($_POST['password']);
    $fio = check_input($_POST['fio']);
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    if (empty($login)) {
        $loginErr = "Введите логин";
        $flagValidation = 1;
    } elseif (!preg_match("/^[a-z0-9-_]{8,20}$/i", ($login))) {
        $loginErr = "Логин может содержать только латинские буквы, 
        цифры, тире и знак подчёркивания и длиной не меньше 
        8 символов и не больше 20";
        $flagValidation = 1;
    }
    if ($flagValidation === 0) {
        $pdo = connect();
        // Вставляем данные опроса в таблицу polls
        $sql = "SELECT COUNT(*) FROM users WHERE login = :login";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['login' => $login]);
        $count = $stmt->fetchColumn();
        if ($count > 0) {
            $loginErr = 'Такой логин уже существует';
            $flagValidation = 1;
        }
    }
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number = preg_match('@[0-9]@', $password);
    $specialChars = preg_match('@[^\w]@', $password);
    if (empty($password)) {
        $passErr = "Введите пароль";
        $flagValidation = 1;
    } else if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
        $passErr = "Пароль должен содержать хотя бы 1 цифру, 1 спецсимвол, 
        1 латинскую букву в нижнем и верхнем регистрах,общая длина не менее 8 и не больше 20 символов";
        $flagValidation = 1;
    }
    if (empty($fio)) {
        $fioErr = "Введите ФИО";
        $flagValidation = 1;
    }
    if ($flagValidation === 0) {
        // Создаем пользователя
        $stmt = $pdo->prepare("INSERT INTO users (role, login, password) VALUES ('admin', :login, :password)");
        $stmt->bindParam(':login', $login);
        $stmt->bindParam(':password', $passwordHash);
        $stmt->execute();
        $user_id = $pdo->lastInsertId();
        // Создаем ученика
        $stmt = $pdo->prepare("INSERT INTO admins (id_user, fio) VALUES (:id_user, :fio)");
        $stmt->bindParam(':id_user', $user_id);
        $stmt->bindParam(':fio', $fio);
        $stmt->execute();
    }
}
?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Добавление нового администратора</title>
        <script>
            function ShowPassword() {
                let pass = document.getElementById("password");
                if (pass.type === "password") {
                    pass.type = "text";
                } else {
                    pass.type = "password";
                }
            }
        </script>
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
                    <li><a href="EditAdmin.php">Список администраторов</a></li>
                </ul>
            </nav>
        </main>
        <h1>Добавление нового администратора</h1>
        <form method="post">
            <span class="error"><?php echo $loginErr; ?></span>
            <label for="login">Логин пользователя:</label>
            <input type="text" id="login" name="login" required>
            <span class="error"><?php echo $passErr; ?></span>
            <label for="password">Пароль пользователя:</label>
            <input type="password" id="password" name="password" required>
            <label><input type="checkbox" class="password-checkbox" onclick="ShowPassword()"> Показать пароль</label>
            <span class="error"><?php echo $fioErr; ?></span>
            <label for="fio">ФИО администратора:</label>
            <input type="text" id="fio" name="fio" required>
            <div class="buttons">
            <input type="submit" value="Добавить">
            </div>
        </form>
    </div>
    </body>
    </html>
<?php
