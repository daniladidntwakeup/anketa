<?php
// Подключаемся к базе данных
require_once 'functions.php';
notAuth();
if (isset($_GET['poll_id'])) {
    $poll_id = $_GET['poll_id'];
} else {
    $poll_id = null;
}
//проверка на наличие доступа
if (PollAccess($_SESSION['user_id'], $poll_id)) {
    $flagValidation = 0;
    $pdo = connect();
// Получаем id опроса из GET-параметра
    $poll_id = $_GET['poll_id'];
    $user_id = $_SESSION['user_id'];

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
        if (isset($_POST['answers'])) {
            $selected_answers = $_POST['answers'];
        } else {
            $flagValidation = 1;
        }
        if ($flagValidation === 0) {
            $flagValidation = CheckPoll($questions,$selected_answers);
            if ($flagValidation === 0) {
                foreach ($questions as $question) {
                    pollResponses($question['answer_type'], $selected_answers, $question, $poll_id, $user_id, $pdo);
                }
                // Удаляем разрешение на прохождение опроса у пользователя
                $sql = "DELETE FROM access WHERE user_id = :user_id AND poll_id = :poll_id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':user_id', $user_id);
                $stmt->bindValue(':poll_id', $poll_id);
                $stmt->execute();
                isAuth();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $poll['title']; ?></title>
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
    <script>
        function TextAreaCheck() {
            let flag = true;
            let textareas = document.querySelectorAll('textarea');
            textareas.forEach((textarea) => {
                let trimmedValue = textarea.value.trim();
                if (trimmedValue == '') {
                    textarea.value = trimmedValue;
                    flag = false;
                }
            });
            return flag;
        }
        function isAnyCheckBoxSelected() {
            let questions = document.querySelectorAll('.question_answer');
            let questionsCounter = document.querySelectorAll('.question_answer input[type="checkbox"]').length;
            let counter=0;
            if (questionsCounter>0) {
                for (let i = 0; i < questions.length; i++) {
                    let answerSelected = false;
                    let answers = questions[i].querySelectorAll('.variants label input[type="checkbox"]');
                    for (let j = 0; j < answers.length; j++) {
                        if (answers[j].checked) {
                            answerSelected = true;
                            break;
                        }
                    }
                    if (!answerSelected && answers.length>0) {
                        let errorSpan = questions[i].querySelector('.error');
                        errorSpan.textContent = 'Выберите хотя бы 1 вариант ответа';
                        counter++;
                    } else {
                        let errorSpan = questions[i].querySelector('.error');
                        errorSpan.textContent = '';
                    }
                }
            }
            if (counter>0)
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        document.addEventListener("DOMContentLoaded", function () {
            if (document.querySelector('#poll_form')) {
                let myForm = document.querySelector('#poll_form');
                myForm.addEventListener('submit', function (event) {
                    event.preventDefault();
                    if (isAnyCheckBoxSelected()) {
                        if (TextAreaCheck())
                        {
                            myForm.submit();
                        }
                        // Все `checkbox` выбраны, отправляем форму
                    }
                });
            }
        });
    </script>
</head>
<body>
<div class="wrapper">
    <div>
        <main>
            <nav>
                <ul>
                    <li><?php echo(profile()); ?></li>
                    <li><?php echo(pollsResultsRole()); ?></li>
                </ul>
            </nav>
        </main>
    </div>
    <div class="poll_title">
        <h1><?php echo $poll['title']; ?></h1>
    </div>
    <form method="POST" id="poll_form">
        <?php foreach ($questions as $question): ?>
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
            ?>
            <div class="question_answer">
            <span class="error" name="answer_<?php echo $question['id']; ?>"></span>
            <?php
            if ($question['answer_type'] === 'single_choice'):
                foreach ($answers as $answer):
                    ?>
                    <div class="variants">
                        <label>
                            <input type="radio" required name="answers[<?php echo $question['id']; ?>][]"
                                   value="<?php echo $answer['id']; ?>">
                            <?php echo $answer['answer']; ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php
            if ($question['answer_type'] === 'multiple_choice'):
                foreach ($answers as $answer):
                    ?>
                    <div class="variants">
                        <label>
                            <input type="checkbox" name="answers[<?php echo $question['id']; ?>][]"
                                   value="<?php echo $answer['id']; ?>">
                            <?php echo $answer['answer']; ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php
            if ($question['answer_type'] === 'open_answer'):
                ?>
                <div class="variants">
                    <textarea name="answers[<?php echo $question['id']; ?>] []" rows="4" cols="50" required></textarea>
                </div>
            <?php endif; ?>
            <?php
            if ($question['answer_type'] === 'scale'):
                ?>
                <div class="variants">
                    <input type="range" name="answers[<?php echo $question['id']; ?>] []" value="0" max="10" step="1"
                           min="0"
                           oninput="document.getElementsByName('output_<?php echo $question['id']; ?>')[0].value = this.value;"
                           required>
                    <output name="output_<?php echo $question['id']; ?>">0</output>
                </div>
            <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <div class="poll_submit"><input type="submit" value="Отправить""></div>
</div>
</form>
</body>
</html>