<!DOCTYPE html>
<html>
<head>
    <title>Список учителей</title>
    <style>
        .wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            width: 100%;
            flex-direction: column;
            font-family: Arial, sans-serif;
        }
        span {
            color: red;
            word-wrap: break-word;
        }

        .table {
            border: 1px solid #eee;
            table-layout: auto;
            width: 80%;
            margin-bottom: 15px;
            margin-top: 15px;
            text-align: center;
            border-collapse: collapse;
        }

        .table th {
            font-weight: bold;
            padding: 5px;
            background-color: #9ce79e;
            border: 1px solid #dddddd;
        }

        .table td {
            padding: 5px 10px;
            border: 1px solid #ffffff;
            text-align: center;
        }

        .table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        .table tbody tr:nth-child(even) {
            background: #F7F7F7;
        }

        tr:hover {
            background-color: #e6e6e6;
        }

        select {
            margin-bottom: 20px;
        }

        a {
            margin-bottom: 20px;
            text-decoration: none
        }

        input[type="text"], input[type="number"], select, .form_button {
            padding: 10px;
            margin: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            width: 100%;
            max-width: 220px;
            background-color: white;
        }

        @media screen and (min-width: 768px) { {
            .table {
                border: 1px solid #eee;
                table-layout: auto;
                width: 80%;
                margin-bottom: 10px;
                margin-top: 10px;
                text-align: center;
                border-collapse: collapse;
                font-size: 5px;
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
    </style>
</head>
<body>
<div class="wrapper">
<main>
    <nav>
        <ul>
            <?php  require_once 'functions.php';?>
            <li><?php echo (profile()); ?></li>
            <li><a href="NewTeacher.php">Новый учитель</a></li>
        </ul>
    </nav>
</main>
    <h1>Список учителей</h1>
<table class="table">
    <tr>
        <th>ID</th>
        <th>ФИО</th>
        <th>Класс</th>
        <th>Действия</th>
    </tr>
    <?php
    require_once 'functions.php'; // Подключаем файл с функцией checkAccess
    checkAccessAdmin();

    $pdo = connect();
    // SQL-запрос для получения данных об учителях и классах
    $sql = "SELECT t.id, t.Fio, c.name AS class_name
            FROM teachers t LEFT JOIN classes c ON t.class_id = c.id
            ORDER BY t.id";
    // Выполнение SQL-запроса и получение результата
    $result = $pdo->query($sql);
    // Отображение данных об учителях и классах в HTML таблице
    while ($row = $result->fetch()) {
        $id = $row['id'];
        $fio = $row['Fio'];
        $class_name = $row['class_name'];
        echo "<tr>";
        echo "<td>$id</td>";
        echo "<td>$fio</td>";
        echo "<td>$class_name</td>";
        echo "<td><a href='EditTeacherData.php?id=$id'>Редактировать</a></td>";
        echo "</tr>";
    }
    ?>
</table>
</div>
</body>
</html>