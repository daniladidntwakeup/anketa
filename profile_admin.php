<?php require_once 'functions.php';
checkAccessAdmin();
$fio = $_SESSION['fio'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Личный кабинет администратора</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <style>
        /* Reset styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Set background color for the body */
        body {
            background-color: #f9f9f9;
            color: #333;
            font-family: Arial, sans-serif;
        }

        /* Style the header */
        header {
            background-color: #fff;
            color: #333;
            padding: 10px;
            text-align: center;
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
        /* Style the dropdown content (hidden by default) */
        .dropdown-content {
            display: none;
            position: absolute;
            z-index: 1;
            background-color: #fff;
            min-width: 160px;
            box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
            border-radius: 5px;
        }

        /* Style the links inside the dropdown */
        .dropdown-content a {
            color: #333;
            padding: 10px;
            text-decoration: none;
            display: block;
        }

        /* Change color of dropdown links on hover */
        .dropdown-content a:hover {
            background-color: #4b4b4b;
            color: #ffffff;
        }

        /* Show the dropdown menu on hover */
        li:hover .dropdown-content {
            display: block;
        }
    </style>
</head>
<body>
<div class="wrapper">
<main>
    <nav>
        <ul>
            <li>
                <a href="#">Опросы</a>
                <div class="dropdown-content">
                    <a href="create_poll.php">Создать опрос</a>
                    <a href="polls.php">Список опросов</a>
                    <a href="poll_attempts.php">Результаты опросов</a>
                    <a href="access.php">Доступ к опросу</a>
                </div>
            </li>
            <li>
                <a href="#">Классы</a>
                <div class="dropdown-content">
                    <a href="NewClass.php">Новый класс</a>
                    <a href="EditClass.php">Список классов</a>
                </div>
            </li>
            <li>
                <a href="#">Пользователи</a>
                <div class="dropdown-content">
                    <a href="NewStudent.php">Новый ученик</a>
                    <a href="EditStudent.php">Список учеников</a>
                    <a href="NewTeacher.php">Новый учитель</a>
                    <a href="EditTeacher.php">Список учителей</a>
                    <a href="NewParent.php">Новый родитель</a>
                    <a href="EditParent.php">Список родителей</a>
                    <a href="NewAdmin.php">Новый администратор</a>
                    <a href="EditAdmin.php">Список администраторов</a>
                </div>
            </li>
            <li><a href="logout.php">Выйти</a></li>
        </ul>
    </nav>
</main>
</body>
</html>