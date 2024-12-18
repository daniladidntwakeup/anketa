<?php
require_once 'functions.php';
checkAccessAdmin();
$fioErr = $classErr = $parentErr = $loginErr = $passErr = '';
// Обработка POST-запроса на сохранение или удаление ученика
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['save'])) {
        $flagValidation = 0;
        // Обработка запроса на обновление ученика
        // Получение данных из массива $_POST
        $pdo = connect();
        $id = check_input($_POST['id']);
        $user_id = check_input($_POST['user_id']);
        $fio = check_input($_POST['Fio']);
        $parent_id = check_input($_POST['parent_id']);
        $class_id = check_input($_POST['class_id']);
        $login = check_input($_POST['login']);
        $passwordChange=0;
        if (isset($_POST['password-change']))
        {
            $passwordChange=$_POST['password-change'];
        }
        else{
            $password = check_input($_POST['password']);
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        }
        //проверка данных
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
            $sql = "SELECT COUNT(*) FROM users WHERE login = :login AND id != :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['login' => $login, 'id' => $user_id]);
            $count = $stmt->fetchColumn();
            if ($count > 0) {
                $loginErr = 'Такой логин уже существует';
                $flagValidation = 1;
            }
        }
        if ($passwordChange===0)
        {
            $uppercase = preg_match('@[A-Z]@', $password);
            $lowercase = preg_match('@[a-z]@', $password);
            $number = preg_match('@[0-9]@', $password);
            $specialChars = preg_match('@[^\w]@', $password);
        }
        if($passwordChange===0) {
            if (empty($password)) {
                $passErr = "Введите пароль";
                $flagValidation = 1;
            } else if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
                $passErr = "Пароль должен содержать хотя бы 1 цифру, 1 спецсимвол, 
        1 латинскую букву в нижнем и верхнем регистрах,общая длина не менее 8 и не больше 20 символов";
                $flagValidation = 1;
            }
        }
        if (empty($fio)) {
            $fioErr = "Введите ФИО";
            $flagValidation = 1;
        }
        if (empty($parent_id)) {
            $parentErr = "Выберите родителя";
            $flagValidation = 1;
        }
        if (empty($class_id)) {
            $classErr = "Выберите класс";
            $flagValidation = 1;
        }
        if ($flagValidation === 0) {
            // Обновление данных ученика в базе данных
            $sql = "UPDATE students SET Fio=:fio, class_id=:class_id, parent_id=:parent_id WHERE id=:id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['fio' => $fio, 'class_id' => $class_id, 'parent_id' => $parent_id, 'id' => $id]);
            if ($passwordChange===0){

                // Обновление данных пользователя в базе данных
                $sql_user = "UPDATE users SET login=:login, password=:password WHERE id=:user_id";
                $stmt_user = $pdo->prepare($sql_user);
                $stmt_user->execute(['login' => $login, 'password' => $passwordHash, 'user_id' => $user_id]);
            }
            else{
                // Обновление данных пользователя в базе данных
                $sql_user = "UPDATE users SET login=:login  WHERE id=:user_id";
                $stmt_user = $pdo->prepare($sql_user);
                $stmt_user->execute(['login' => $login, 'user_id' => $user_id]);
            }
            // Перенаправление на страницу со списком учеников
            header("Location: EditStudent.php");
        }
    } elseif (isset($_POST['delete'])) {
        // Обработка запроса на удаление ученика
        $pdo = connect();
        $user_id = $_POST['user_id'];
        // SQL-запрос для удаления ученика из базы данных
        $sql = "DELETE FROM users WHERE id=:id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $user_id]);
        // Перенаправление на страницу со списком учеников
        header("Location: EditStudent.php");
    }
}

// Получение ID ученика из параметра GET
if(isset($_GET['id']))
{
    $id = $_GET['id'];
}
if(isset($id))
{
    // Получение данных ученика из базы данных
    $pdo = connect();
    $sql = "SELECT s.id, s.Fio, s.class_id, s.parent_id, s.id_user, c.name AS class_name, p.Fio AS parent_name, u.login, u.password
        FROM students s
        LEFT JOIN classes c ON s.class_id = c.id
        LEFT JOIN parents p ON s.parent_id = p.id
        LEFT JOIN users u ON s.id_user = u.id
        WHERE s.id=:id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    if (!$row) {
        // Если ученика с указанным ID не существует, перенаправляем на страницу со списком учеников
        header("Location: EditStudent.php");
    }
}
// Получение данных ученика из результата SQL-запроса
if(isset($row))
{
    $id = $row['id'];
    $fio = $row['Fio'];
    $class_id = $row['class_id'];
    $class_name = $row['class_name'];
    $parent_id = $row['parent_id'];
    $parent_name = $row['parent_name'];
    $user_id = $row['id_user'];
    $login = $row['login'];
    $password = $row['password'];
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Редактирование ученика</title>
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
        select {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-bottom: 15px;
            width: 100%;
        }

        select {
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

        footer {
            background-color: #308631;
            color: white;
            text-align: center;
            position: absolute;
            bottom: 0;
            width: 100%;
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
    <script>
        function ShowPassword() {
            let pass = document.getElementById("password");
            if (pass.type === "password") {
                pass.type = "text";
            } else {
                pass.type = "password";
            }
        }

        function PasswordChange() {
            let change = document.getElementById("password-change");
            let pass = document.getElementById("password");
            if (change.checked) {
                pass.removeAttribute('required');
            } else {
                pass.setAttribute('required','');
            }
        }

    </script>
</head>
<body>
<div class="wrapper">
    <main>
        <nav>
            <ul>
                <li><?php echo (profile()); ?></li>
                <li><a href="EditStudent.php">Список учеников</a></li>
            </ul>
        </nav>
    </main>
    <form method="post">
        <input type="hidden" name="id" value="<?php  if(isset($id)){ echo $id; }?>">
        <input type="hidden" name="user_id" value="<?php if(isset($user_id)) {echo $user_id;} ?>">
        <span class="error"><?php echo $fioErr; ?></span>

            <label for="fio">ФИО ученика:</label>
            <input type="text" id="fio" name="Fio" required value="<?php if(isset($fio)) {echo $fio;} ?>">

        <span class="error"><?php echo $classErr; ?></span>

            <label for="class_id">Класс:</label>
            <select id="class_id" name="class_id" required>
                <?php
                // Получение списка классов
                $stmt_classes = $pdo->query("SELECT id, name FROM classes");
                while ($row_class = $stmt_classes->fetch()) {
                    $selected = '';
                    if ($row_class['id'] == $class_id) {
                        $selected = ' selected';
                    }
                    echo '<option value="' . $row_class['id'] . '"' . $selected . '>' . $row_class['name'] . '</option>';
                }
                ?>
            </select>


            <span class="error"><?php echo $parentErr; ?></span>
            <label for="parent_id">Родитель:</label>
            <select id="parent_id" name="parent_id" required>
                <?php
                // Получение списка родителей
                $stmt_parents = $pdo->query("SELECT id, Fio FROM parents");
                while ($row_parent = $stmt_parents->fetch()) {
                    $selected = '';
                    if ($row_parent['id'] == $parent_id) {
                        $selected = ' selected';
                    }
                    echo '<option value="' . $row_parent['id'] . '"' . $selected . '>' . $row_parent['Fio'] . '</option>';
                }
                ?>
            </select>

        <span class="error"><?php echo $loginErr; ?></span>

            <label for="login">Логин пользователя:</label>
            <input type="text" id="login" name="login" required value="<?php if(isset($login)) {echo $login;} ?>">

        <span class="error"><?php echo $passErr; ?></span>

            <label for="password">Пароль пользователя:</label>
            <input type="password" required id="password" name="password">
            <label><input type="checkbox" id="password-checkbox" class="password-checkbox" onclick="ShowPassword()"> Показать пароль</label>
            <label><input type="checkbox" id="password-change" name="password-change" value="1" class="password-change" onclick="PasswordChange()"> Не менять пароль</label>
        <div class="buttons">
            <input type="submit" name="save" value="Сохранить" </input>
            <input type="submit" name="delete" formnovalidate value="Удалить"</input>
        </div>
    </form>
<!--    <footer>-->
<!--        <p>Все права защищены &copy; --><?php //echo date("Y"); ?><!--</p>-->
<!--    </footer>-->
</div>
</body>
</html>