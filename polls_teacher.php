<?php
// Подключаемся к базе данных
require_once 'functions.php';
checkAccess();
$pdo = connect();
// Получаем список опросов из базы данных
$sql = "SELECT id, title FROM polls";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$polls = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Список опросов</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            font-size: 16px;
            line-height: 1.5;
        }

        .wrapper {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            height: 90vh;
        }
        .polls
        {
            width: 50vw;
        }
        h1 {
            margin-top: 20px;
            text-align: center;
        }

        .polls ul {
            margin-top: 20px;
            list-style: none;
            padding: 0;
        }

        .polls li {
            margin-bottom: 10px;
            background-color: #fff;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .polls a {
            color: #000;
            text-decoration: none;
            font-size: 20px;
            font-weight: bold;
        }


        @media only screen and (max-width: 768px) {
            .wrapper {
                padding: 10px;
            }

            .menu {
                padding: 5px;
                justify-content: center;
            }

            .menu ul {
                justify-content: center;
            }

            h1 {
                margin-top: 10px;
            }

            ul {
                margin-top: 10px;
            }

            li {
                margin-bottom: 5px;
                padding: 5px;
            }

            a {
                font-size: 16px;
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

        .polls li:hover {
            background-color: #308631;
            color: white;
        }

        .polls li:hover a {
            color: white;
        }
        .links {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-content: center;
            align-items: center;
            justify-content: space-evenly;
        }
        .links li
        {
            width: 20vw;
            text-align: center;
        }
        .links li a
        {
            word-wrap: break-word;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div>
        <main>
            <nav>
                <ul>
                    <li><?php echo(profile()); ?></li>
                    <li><a href="poll_attempts.php">Результаты прохождения опросов</a></li>
                </ul>
            </nav>
        </main>
    </div>
    <header>
        <h1>Список опросов</h1>
    </header>
    <div class="polls">
        <ul>
            <?php foreach ($polls as $poll): ?>
                <div class="links">
                    <li>
                        <a href="poll.php?poll_id=<?php echo $poll['id']; ?>"><?php echo $poll['title']; ?></a>
                    </li>
                </div>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<!--<footer>-->
<!--    <p>Все права защищены &copy; --><?php //echo date("Y"); ?><!--</p>-->
<!--</footer>-->
</body>
</html>