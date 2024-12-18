<?php
require_once 'functions.php'; // Подключаем файл с функцией checkAccess
isAuth();
$loginErr = $passErr = $passORlogErr = '';
$flagValidation = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = check_input($_POST["login"]);
    $pass = check_input($_POST["password"]);
    if (empty($login)) {
        $loginErr = "Введите логин";
        $flagValidation = 1;
    }
    if (empty($pass)) {
        $passErr = "Введите пароль";
        $flagValidation = 1;
    }
    if ($loginErr === '' && $passErr === '' && $flagValidation === 0) {
        // Получаем логин и пароль из формы
        $pdo = connect();
        // Проверяем, есть ли пользователь с указанным логином и паролем
        $sql = "SELECT id, role, login, password FROM users WHERE login = :login";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['login' => $login]);
        $user = $stmt->fetch();
        //сравнение введённого пароля с хэшем хранимым в бд
        if (password_verify($pass, $user['password'])) {
            // Сохраняем информацию о пользователе в сессии
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_login'] = $user['login'];
            $_SESSION['auth'] = true;
            $_SESSION['fio'] = fio($_SESSION['user_id']);
            // Перенаправляем пользователя на главную страницу сайта
            if ($user['role'] == 'admin') {
                header("Location: profile_admin.php");
            } else if ($user['role'] == 'teacher') {
                header("Location: profile_teacher.php");
            } else {
                header("Location: profile_student_parent.php");
            }
        } else {
            // Если пользователя не найдено, выводим сообщение об ошибке
            $passORlogErr = "Неверный логин или пароль";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <!--    <link rel="stylesheet" href="styles.css">-->
    <title>Вход</title>
    <style>
        /* Общие стили */
        * {
            box-sizing: border-box;
        }

        span {
            color: red;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #F0F5F9;
        }

        .wrapper {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        h1 {
            text-align: center;
            margin-top: 0;
        }

        /* Стили для формы авторизации */
        form {
            background-color: #FFFFFF;
            border-radius: 5px;
            overflow: hidden;
            padding: 20px;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
            max-width: 400px;
        }

        .form-group {
            margin-bottom: 20px;
            width: 100%;
        }

        label {
            display: block;
            font-size: 16px;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #CCCCCC;
            border-radius: 5px;
            font-size: 16px;
            margin-bottom: 10px;
        }

        button[type="submit"] {
            background-color: #4CAF50;
            color: #FFFFFF;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
            width: 100%;
        }

        button[type="submit"]:hover {
            background-color: #3E8E41;
        }

        @media only screen and (max-width: 600px) {
            /* Стили для мобильных устройств */
            form {
                padding: 10px;
                max-width: 300px;
            }

            input[type="text"],
            input[type="password"] {
                font-size: 14px;
            }

            button[type="submit"] {
                font-size: 14px;
                padding: 8px 16px;
            }
        }
    </style>
</head>
<body>
<div class="wrapper">
    <h1>Вход</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <span class="error"><?php echo $passORlogErr; ?></span>
        <div class="form-group">
            <label for="login">Логин:</label>
            <input type="text" name="login" >
            <span class="error"><?php echo $loginErr; ?></span>
        </div>
        <div class="form-group">
            <label for="password">Пароль:</label>
            <input type="password" name="password" >
            <span class="error"><?php echo $passErr; ?></span>
        </div>
        <div class="submit_auth">
            <button type="submit">Войти</button>
        </div>
    </form>
</div>
</body>
</html>