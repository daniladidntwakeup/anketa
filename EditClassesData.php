<?php
require_once 'functions.php';
checkAccessAdmin();
$nameErr = '';
// Обработка POST-запроса на сохранение или удаление ученика
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['save'])) {
        $flagValidation = 0;
        $pdo = connect();
        $id = check_input($_POST['id']);
        $name = check_input($_POST['name']);
        //проверка данных
        if (empty($name)) {
            $nameErr = "Введите название класса";
            $flagValidation = 1;
        }
        if ($flagValidation === 0) {
            $pdo = connect();
            $sql = "SELECT COUNT(*) FROM classes WHERE name = :name AND id != :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['name' => $name, 'id' => $id]);
            $count = $stmt->fetchColumn();
            if ($count > 0) {
                $nameErr = 'Такое название класса уже существует';
                $flagValidation = 1;
            }
        }
        if ($flagValidation === 0) {
            // Обновление данных класса в базе данных
            $sql = "UPDATE classes SET name=:name WHERE id=:id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['name' => $name, 'id' => $id]);
            header("Location: EditClass.php");
        }
    } elseif (isset($_POST['delete'])) {
        // Обработка запроса на удаление ученика
        $pdo = connect();
        $id = $_POST['id'];
        // SQL-запрос для удаления ученика из базы данных
        $sql = "DELETE FROM classes WHERE id=:id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        // Перенаправление на страницу со списком учеников
        header("Location: EditAdmin.php");
    }
}

// Получение ID администратора из параметра GET
if(isset($_GET['id']))
{
    $id = $_GET['id'];
}
if(isset($id))
{
    // Получение данных ученика из базы данных
    $pdo = connect();
    $sql = "SELECT c.id, c.name FROM classes c WHERE c.id=:id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    if (!$row) {
        // Если класска с указанным ID не существует, перенаправляем на страницу со списком классов
        header("Location: EditClass.php");
    }
}
// Получение данных класса из результата SQL-запроса
if(isset($row))
{
    $id = $row['id'];
    $name = $row['name'];
}

?>
    <!DOCTYPE html>
    <html>
<head>
    <title>Редактирование класса</title>
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
</head>
<body>
<div class="wrapper">
    <main>
        <nav>
            <ul>
                <li><?php echo (profile()); ?></li>
                <li><a href="EditClass.php">Список классов</a></li>
            </ul>
        </nav>
    </main>
    <form method="post">
        <input type="hidden" name="id" value="<?php  if(isset($id)){ echo $id; }?>">
        <span class="error"><?php echo $nameErr; ?></span>
        <label for="name">Название класса:</label>
        <input type="text" id="name" name="name" required value="<?php if(isset($name)) {echo $name;} ?>">
        <div class="buttons">
            <input type="submit" name="save" value="Сохранить" </input>
            <input type="submit" name="delete" formnovalidate value="Удалить"</input>
        </div>
    </form>
</div>
</body>
    </html><?php
