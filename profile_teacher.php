<?php require_once 'functions.php';
checkAccess();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Личный кабинет учителя</title>
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
        /* Style the footer */
        footer {
            background-color: #308631;
            color: white;
            text-align: center;
            padding: 10px;
            position: absolute;
            bottom: 0;
            width: 100%;
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
                        <a href="polls_teacher.php">Список опросов</a>
                        <a href="poll_attempts.php">Результаты опросов</a>
                        <a href="access.php">Доступ к опросу</a>
                    </div>
                </li>
                <li>
                <li><a href="logout.php">Выйти</a></li>
            </ul>
        </nav>
    </main>
    <!--<footer>-->
    <!--    <p>Все права защищены &copy; --><?php //echo date("Y"); ?><!--</p>-->
    <!--</footer>-->
</body>
</html>