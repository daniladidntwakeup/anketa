<?php
// Подключаемся к базе данных
require_once 'functions.php';
checkAccess();
if (isset($_GET['poll_id'])) {
    $poll_id = $_GET['poll_id'];
} else {
    $poll_id = null;
}
if (isset($_GET['date'])) {
    $date = $_GET['date'];
} else {
    $date = null;
}
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
} else {
    $user_id = null;
}
if ($poll_id !== null && $date !== null && $user_id !== null) {
    $pdo = connect();
// Получаем id опроса из GET-параметра
    $poll_id = $_GET['poll_id'];
    $date = $_GET['date'];
    $user_id = $_GET['user_id'];;
// Получаем информацию об опросе из базы данных
    $sql = "SELECT id, title  FROM polls WHERE id = :poll_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':poll_id', $poll_id);
    $stmt->execute();
    $poll = $stmt->fetch();
// Получаем список вопросов для данного опроса
    $sql = "SELECT id, question, answer_type FROM questions WHERE poll_id = :poll_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':poll_id', $poll_id);
    $stmt->execute();
    $questions = $stmt->fetchAll();
// Обработка отправки формы
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Получаем список выбранных пользователем ответов
        if (isset($_POST['points'])) {
            $selected_answers = $_POST['points'];
        } else {
            $selected_answers = $_POST;
        }
        foreach ($questions as $question) {
            if (isset($selected_answers[$question['id']]) && $question['answer_type'] === 'open_answer') {
                // Получаем значение баллов из поля ввода
                $open_answer_points = check_input($selected_answers[$question['id']]);
                if (is_numeric($open_answer_points)) {
                    // Обновляем баллы в базе данных
                    $sql = "UPDATE poll_responses SET open_answer_points = :open_answer_points WHERE poll_id = :poll_id AND question_id = :question_id AND id_user = :id_user
AND date = :date";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':open_answer_points', $open_answer_points);
                    $stmt->bindValue(':poll_id', $poll_id);
                    $stmt->bindValue(':question_id', $question['id']);
                    $stmt->bindValue(':id_user', $user_id);
                    $stmt->bindValue(':date', $date);
                    $stmt->execute();
                }
            }
        }
        // Перенаправляем пользователя на страницу с опросами
        header("Location: poll_attempts.php");
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            font-size: 16px;
            line-height: 1.5;
        }

        .wrapper {
            max-width: 1200px;
            margin: 0 auto 20px;
        }

        .menu {
            background-color: #4CAF50;
            padding: 10px;
            display: flex;
            justify-content: flex-end;
        }

        .menu ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: flex-end;
        }

        .menu li {
            margin-left: 20px;
        }

        .poll_title {
            margin-top: 20px;
            text-align: center;
        }

        h1 {
            font-size: 28px;
            font-weight: bold;
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        h3 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .variants {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        label {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        input[type="radio"],
        input[type="checkbox"] {
            margin-right: 10px;
            margin-top: 3px;
        }

        textarea {
            width: 95%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            resize: none;
        }

        input[type="range"] {
            width: 95%;
            margin-top: 10px;
        }

        input[type="number"] {
            width: 10%;
        }

        output {
            font-size: 16px;
            font-weight: bold;
            margin-top: 5px;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
            text-align: center;
            margin-top: 10px;
        }

        input[type="submit"]:hover {
            background-color: #3e8e41;
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

            .poll_title {
                margin-top: 10px;
            }

            h1 {
                font-size: 24px;
            }

            form {
                margin-top: 10px;
                padding: 10px;
            }

            h3 {
                font-size: 18px;
            }

            textarea {
                font-size: 14px;
            }

            output {
                font-size: 14px;
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
                <li><?php echo (profile()); ?></li>
                <li><a href="poll_attempts.php">Список результатов</a></li>
            </ul>
        </nav>
    </main>
    <div class="poll_title">
        <h1><?php
            if (isset($poll['title'])) {
                echo($poll['title']);
            }
            ?></h1>
    </div>
    <form method="POST">
        <?php
        if (isset($questions)) {
            foreach ($questions as $question):
                ?>
                <div class="question">
                    <h3><?php echo $question['question']; ?></h3>
                </div>
                <?php
                // Получаем список ответов для данного вопроса
                $sql = "SELECT id, answer FROM answers WHERE question_id = :question_id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':question_id', $question['id']);
                $stmt->execute();
                $answers = $stmt->fetchAll();

                $sql = "SELECT * FROM poll_responses WHERE question_id = :question_id AND date = :date AND id_user = :id_user";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':question_id', $question['id']);
                $stmt->bindValue(':date', $date);
                $stmt->bindValue(':id_user', $_GET['user_id']);
                $stmt->execute();
                $used_answers = $stmt->fetchAll();
                ?><?php
                if ($question['answer_type'] === 'single_choice') {
                    foreach ($answers as $answer) {
                        $checked = '';
                        foreach ($used_answers as $used_answer) {
                            if ($used_answer['answer_id'] === $answer['id']) {
                                $checked = 'checked';
                            }
                        }
                        ?>
                        <div class="variants">
                            <label>
                                <input type="radio" name="answers[<?php echo $question['id']; ?>]"
                                       value="<?php echo $answer['id']; ?>" disabled <?php echo $checked; ?>>
                                <?php echo $answer['answer']; ?>
                            </label>
                        </div>
                        <?php
                    }
                } ?>
                <?php
                if ($question['answer_type'] === 'multiple_choice') {
                    foreach ($answers as $answer) {
                        $checked = '';
                        foreach ($used_answers as $used_answer) {
                            if ($used_answer['answer_id'] === $answer['id']) {
                                $checked = 'checked';
                                break;
                            }
                        }
                        ?>
                        <div class="variants">
                            <label>
                                <input type="checkbox" name="answers[<?php echo $question['id']; ?>][]"
                                       value="<?php echo $answer['id']; ?>" disabled <?php echo $checked; ?>>
                                <?php echo $answer['answer']; ?>
                            </label>
                        </div>
                        <?php
                    }
                }
                if ($question['answer_type'] === 'open_answer') {
                    $answer = '';
                    foreach ($used_answers as $used_answer) {
                        if ($used_answer['question_id'] === $question['id']) {
                            $answer = $used_answer['answer'];
                            $points = $used_answer['open_answer_points'];
                            break;
                        }
                    }
                    ?>
                    <div class="variants">
                    <textarea name="answers[<?php echo $question['id']; ?>]" rows="4" cols="50"
                              disabled><?php echo $answer; ?></textarea>
                        <label for="points_<?php echo $question['id']; ?>">Количество баллов:</label>
                        <input type="number" name="points[<?php echo $question['id']; ?>]" min="0" max="10" step="1"
                               value="<?php echo $points; ?>">
                    </div>
                    <?php
                } ?>
                <?php
                if ($question['answer_type'] === 'scale') {
                    $answer = '';
                    foreach ($used_answers as $used_answer) {
                        if ($used_answer['question_id'] === $question['id']) {
                            $answer = $used_answer['answer'];
                            break;
                        }
                    }
                    ?>
                    <div class="variants">
                        <input type="range" name="answers[<?php echo $question['id']; ?>]"
                               value="<?php echo $answer; ?>"
                               max="10" step="1" min="0"
                               oninput="document.getElementsByName('output_<?php echo $question['id']; ?>')[0].value = this.value;"
                               required disabled>
                        <output name="output_<?php echo $question['id']; ?>"><?php echo $answer; ?></output>
                    </div>
                    <?php
                }
                ?>
            <?php endforeach; ?>
        <?php } ?>
        <div class="poll_submit">
            <input type="submit" value="Отправить">
        </div>
</div>
<!--<footer>-->
<!--    <p>Все права защищены &copy; --><?php //echo date("Y");
?><!--</p>-->
<!--</footer>-->
</form>
</body>
</html>