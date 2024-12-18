<?php
require_once 'functions.php';
checkAccessAdmin();
$pollErr = $questionErr = $passORlogErr = '';
$flagValidation = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['poll_title'])) {
        $poll_title = check_input($_POST['poll_title']);
        if ($poll_title === '') {
            $pollErr = 'заполните поле заголовка опроса';
            $flagValidation = 1;
        }
    } else {
        $flagValidation = 1;
        $pollErr = 'заполните поле заголовка опроса';
    }
    if ($flagValidation === 0) {
        $pdo = connect();
        // Вставляем данные опроса в таблицу polls
        $sql = "SELECT COUNT(*) FROM polls WHERE title = :title";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['title' => $poll_title]);
        $count = $stmt->fetchColumn();
        if ($count > 0) {
            $pollErr = 'Такой заголовок уже существует';
            $flagValidation = 1;
        }
        if ($flagValidation === 0) {
            $flagValidation = CheckCreatePoll();
            if ($flagValidation == 0) {
                //вставляем вопрос
                $sql = "INSERT INTO polls (title) VALUES (:title)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['title' => $poll_title]);
                // Получаем id последней вставленной записи в таблицу polls
                $poll_id = $pdo->lastInsertId();
                // Вставляем данные вопросов и ответов в таблицы questions и answers
                foreach ($_POST as $key => $value) {
                    // Проверяем, что это поле вопроса
                    if (strpos($key, 'question_') === 0) {
                        // Получаем номер вопроса из имени поля
                        $questionNumber = substr($key, strlen('question_'));
                        // Получаем текст вопроса
                        $question = check_input($value);
                        //Получаем тип вопроса
                        $answer_type = $_POST["type_$questionNumber"];
                        // Вставляем данные вопроса в таблицу questions
                        $sql = "INSERT INTO questions (poll_id, question, answer_type) VALUES (:poll_id, :question, :answer_type)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute(['poll_id' => $poll_id, 'question' => $question, 'answer_type' => $answer_type]);
                        $question_id = $pdo->lastInsertId();
                        $i = 1;
                        foreach ($_POST as $key2 => $value2) {
                            if (strpos($key2, "answer_{$questionNumber}_$i") === 0) {
                                $answer = check_input($_POST["answer_{$questionNumber}_$i"]);
                                $points = check_input($_POST["points_${questionNumber}_$i"]);
                                $sql = "INSERT INTO answers (question_id, answer, points) VALUES (:question_id, :answer, :points)";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute(['question_id' => $question_id, 'answer' => $answer, 'points' => $points]);
                                $i++;
                            }
                        }
                    }
                }
            }
        }
    }
    if ($flagValidation === 0) {
        // Перенаправляем пользователя на страницу со списком опросов
        header("Location: polls.php");
    } else {

    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Конструктор опросов</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        header {
            color: black;
            text-align: center;
            padding: 20px;
        }

        header h1 {
            margin: 0;
            font-size: 24px;
        }

        .poll-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
            max-width: 500px;
            width: 100%;
        }

        label {
            font-size: 18px;
            margin-right: 10px;
        }

        input[type="text"],
        select, textarea {
            padding: 10px;
            margin: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            width: 90%;
            max-width: 500px;
            resize: vertical;
        }

        input[type="number"] {
            padding: 10px;
            margin: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            max-width: 50px;
        }

        button {
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            margin: 10px;
            cursor: pointer;
        }

        .btndel {
            background-color: #f44336;
            color: #fff;

        }

        .btnsave {
            background-color: #4caf50;
            color: #fff;
        }

        .question {
            margin: 20px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 20px;
            width: 100%;
            max-width: 500px;
            margin-bottom: 10px;
        }

        .question label {
            font-size: 18px;
        }

        .question input[type="text"] {
            margin-top: 10px;
        }

        .question > div {
            display: flex;
            flex-direction: column;
            margin-top: 10px;
        }

        .question > div label {
            margin-bottom: 5px;
        }

        @media screen and (min-width: 768px) {
            .poll-form {
                margin: 20px auto;
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

        span {
            color: red;
            display: block;
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
        let questionNumber = 0;
        function addQuestion() {
            let type = document.getElementById(`type`).value;
            let answerCount = 0;
            if (type !== "scale" && type !== "open_answer") {
                answerCount = prompt('Введите количество ответов на новый вопрос:');
                if (answerCount === null) {
                    return;
                }
                if (!answerCount || isNaN(answerCount) || answerCount < 1) {
                    alert('Некорректное количество ответов!');
                    return;
                }
            }
            questionNumber++;
            let newQuestion = document.createElement('div');
            newQuestion.classList.add('question');
            newQuestion.innerHTML = `
            <span class="error" id="question_${questionNumber}_error"></span>
            <label for="question_${questionNumber}">Вопрос ${questionNumber}:</label>
            <textarea  name="question_${questionNumber}"></textarea>
            <input type="hidden" name="type_${questionNumber}" value="${type}">
            ${generateAnswerFields(answerCount, type)}
            <button type="button" class="btndel" onclick="removeQuestion(this)">Удалить вопрос</button>`;
            let form = document.getElementById('poll-form');
            form.lastElementChild.insertAdjacentElement('afterend', newQuestion);
        }

        // Обработчик события для удаления вопроса
        function removeQuestion(button) {
            let question = button.parentNode;
            question.parentNode.removeChild(question);
            let questions = document.querySelectorAll('.question');
            questionNumber--;
            for (let i = 0; i < questions.length; i++) {
                let label = questions[i].querySelector('label');
                let textArea = questions[i].querySelector('textarea');
                let span = questions[i].querySelector('span');
                let typeField = questions[i].querySelector('input[type="hidden"]');
                let oldNumber = textArea.attributes.item(1).value.substring(9);
                label.innerHTML = `Вопрос ${i + 1}:`;
                label.setAttribute('for', `question_${i + 1}`);
                textArea.setAttribute('name', `question_${i + 1}`);
                span.setAttribute('id', `question_${i + 1}`);
                typeField.setAttribute('name', `type_${i + 1}`);
                let inputAnswer = questions[i].querySelectorAll(`input[name^="answer_${oldNumber}_"]`);
                let labelAnswer = questions[i].querySelectorAll(`label[for^="answer_${oldNumber}_"]`);
                let inputPoints = questions[i].querySelectorAll(`input[name^="points_${oldNumber}_"]`);
                let labelPoints = questions[i].querySelectorAll(`label[for^="points_${oldNumber}_"]`);
                for (let j = 0; j < inputAnswer.length; j++) {
                    inputAnswer[j].setAttribute('name', `answer_${i + 1}_${j + 1}`)
                    labelAnswer[j].setAttribute('for', `answer_${i + 1}_${j + 1}`)
                    inputPoints[j].setAttribute('name', `points_${i + 1}_${j + 1}`)
                    labelPoints[j].setAttribute('for', `points_${i + 1}_${j + 1}`)
                }
            }
        }

        function generateAnswerFields(answerCount, type) {
            let container = '';
            if (type === 'single_choice' || type === 'multiple_choice') {
                for (let i = 1; i <= answerCount; i++) {
                    container += `
                    <div>
                        <label for="answer_${questionNumber}_${i}">Ответ ${i}:</label>
                        <textarea  name="answer_${questionNumber}_${i}" ></textarea>
                        <label for="points_${questionNumber}_${i}">Количество баллов:</label>
                        <input type="number" name="points_${questionNumber}_${i}" min="0" max="10" step="1" value="0">
                    </div>`;
                }
            }
            return container;
        }

        function InputCheck() {
            let flag = true;
            let inputs = document.querySelectorAll('input');
            inputs.forEach((input) => {
                let trimmedValue = input.value.trim();
                if (trimmedValue == '') {
                    input.value = trimmedValue;
                    flag = false;
                }
            });
            return flag;
        }

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

        document.addEventListener("DOMContentLoaded", function () {
            if (document.querySelector('#poll-form')) {
                let myForm = document.querySelector('#poll-form');
                myForm.addEventListener('submit', function (event) {
                    event.preventDefault();
                    if (InputCheck() && TextAreaCheck()) {
                        myForm.submit();
                    }
                });
            }
        });
    </script>
</head>
<body>
<div class="wrapper">
    <main>
        <nav>
            <ul>
                <li><a href="profile_admin.php">Личный кабинет</a></li>
                <li><a href="polls.php">Список опросов</a></li>
            </ul>
        </nav>
    </main>
    <header>
        <h1>Конструктор опросов</h1>
        <?php
        //echo(profile());
        ?>
    </header>
    <form id="poll-form" method="post" class="poll-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <span class=" error"><?php echo $pollErr; ?></span>
        <label> Заголовок опроса:</label>
        <input type="text" name="poll_title">
        <button type="button" id="add-question-btn" onclick="addQuestion()">Добавить вопрос</button>
        <button type="submit" class="btnsave" id="btnsave">Сохранить опрос</button>
        <div>
            <label>Тип вопроса:</label>
            <select id="type">
                <option value="single_choice">Выбор одного варианта</option>
                <option value="multiple_choice">Выбор нескольких вариантов</option>
                <option value="scale">Шкала от 0 до 10</option>
                <option value="open_answer">Открытый ответ</option>
            </select>
        </div>
    </form>
</div>
<!--<footer>-->
<!--    <p>Все права защищены &copy; --><?php //echo date("Y"); ?><!--</p>-->
<!--</footer>-->
</body>
</html>
