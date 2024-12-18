<!DOCTYPE html>
<html>
<head>
    <title>Список администраторов</title>
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
<?php
require_once 'functions.php';
?>
<div class="wrapper">
    <main>
        <nav>
            <ul>
                <li><?php echo (profile()); ?></li>
                <li><a href="NewAdmin.php">Новый администратор</a></li>
            </ul>
        </nav>
    </main>
    <h1>Список администраторов</h1>
    <?php
    checkAccessAdmin();
    // Получение списка всех учеников и их данных из базы данных
    $pdo = connect();
    $sql = "SELECT a.id,a.id_user, a.Fio FROM admins a ORDER BY a.id";
    $stmt = $pdo->query($sql);

    // Отображение списка учеников и кнопки "Редактировать" для каждого ученика
    echo '<table class="table">';
    echo '<tr><th>ID</th><th>ФИО</th><th>Действия</th></tr>';
    while ($row = $stmt->fetch()) {
        $id = $row['id'];
        $id_user = $row['id_user'];
        $fio = $row['Fio'];
        echo '<tr>';
        echo '<td>' . $id . '</td>';
        echo '<td>' . $fio . '</td>';
        echo '<td><a href="EditAdminsData.php?id=' . $id . '">Редактировать</a></td>';
        echo '</tr>';
    }
    echo '</table>';
    ?>
    <!--    <footer>-->
    <!--        <p>Все права защищены &copy; --><?php //echo date("Y"); ?><!--</p>-->
    <!--    </footer>-->
</div>
</body>
</html><?php
