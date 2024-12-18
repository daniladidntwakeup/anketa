<?php
    require_once 'functions.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Прохождение опросов пользователями </title>
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
            text-align: left;
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

        .allresults {
            font-size: 20px;
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
                <li><?php echo(profile()); ?></li>
                <li><?php echo(pollsRole()); ?></li>
            </ul>
        </nav>
    </main>
    <h1>Прохождение опросов пользователями</h1>
    <?php
    require_once 'functions.php';
    checkAccess();
    $pdo = connect();
    // для phpword
    require_once 'vendor/autoload.php';
    // Обработка формы
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $poll_id = $_POST['poll_id'];
        $date = $_POST['date'];
        $user_id = $_POST['id_user'];
        if (isset($_POST['delete_record'])) {
            // Выполняем запрос для удаления записей из таблицы poll_responses
            $sql = "DELETE FROM poll_responses WHERE poll_id = :poll_id AND date = :date AND id_user = :id_user";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':poll_id', $poll_id);
            $stmt->bindValue(':date', $date);
            $stmt->bindValue(':id_user', $user_id);
            $stmt->execute();
        }
        // Перенаправляем пользователя на страницу с результатами опросов
        header("Location: poll_attempts.php");
        exit();
    }
    //ТУТ ПОСМОТРЕТЬ
    $stmt = null;
    $first_responses = '';
    if (isset($_GET['user_role']) && $_GET['user_role'] !== 'nothing') {
        if (isset($_GET['poll_id']) && $_GET['poll_id'] !== 'nothing') {
            $user_role = $_GET['user_role'];
            $poll_id = $_GET['poll_id'];
            $class_id = $_GET['class_id'];
            if ($class_id !== 'nothing') {
                $stmt = $pdo->prepare("SELECT * FROM poll_responses JOIN students ON poll_responses.id_user = students.id_user JOIN users ON poll_responses.id_user =users.id WHERE users.role = :user_role AND poll_id = :poll_id AND students.class_id = :class_id GROUP BY poll_id, poll_responses.id_user, date ORDER BY date DESC");
                $stmt->bindValue(':class_id', $class_id);
            } else {
                $stmt = $pdo->prepare("SELECT * FROM poll_responses JOIN users ON poll_responses.id_user = users.id WHERE users.role = :user_role AND poll_id = :poll_id GROUP BY poll_id, poll_responses.id_user, date ORDER BY date DESC");
            }
            $stmt->bindValue(':user_role', $user_role);
            $stmt->bindValue(':poll_id', $poll_id);
        } else {
            $user_role = $_GET['user_role'];
            $class_id = $_GET['class_id'];
            if ($class_id !== 'nothing') {
                $stmt = $pdo->prepare("SELECT * FROM poll_responses JOIN students ON poll_responses.id_user = students.id_user JOIN users ON poll_responses.id_user = users.id WHERE users.role = :user_role AND students.class_id = :class_id GROUP BY poll_id, poll_responses.id_user, date ORDER BY date DESC");
                $stmt->bindValue(':class_id', $class_id);
            } else {
                $stmt = $pdo->prepare("SELECT * FROM poll_responses JOIN users ON poll_responses.id_user = users.id WHERE users.role = :user_role GROUP BY poll_id, poll_responses.id_user, date ORDER BY date DESC");
            }
            $stmt->bindValue(':user_role', $user_role);
        }
    } elseif (isset($_GET['poll_id']) && $_GET['poll_id'] !== 'nothing') {
        $poll_id = $_GET['poll_id'];
        $class_id = $_GET['class_id'];
        if ($class_id !== 'nothing') {
            $stmt = $pdo->prepare("SELECT * FROM poll_responses JOIN students ON poll_responses.id_user = students.id_user WHERE poll_id = :poll_id AND students.class_id = :class_id GROUP BY poll_id, poll_responses.id_user, date ORDER BY date DESC");
            $stmt->bindValue(':class_id', $class_id);
            $stmt->bindValue(':poll_id', $poll_id);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM poll_responses WHERE poll_id = :poll_id GROUP BY poll_id, poll_responses.id_user, date ORDER BY date DESC");
            $stmt->bindValue(':poll_id', $poll_id);
        }
    } else {
        $stmt = $pdo->prepare("SELECT * FROM poll_responses GROUP BY poll_id, poll_responses.id_user, date ORDER BY date DESC");
    }
    if ($stmt !== null) {
        $stmt->execute();
        $first_responses = $stmt->fetchAll();
    }

    //возможно даннные уже есть и не надо заново запросы делать для дальнейших некотр=х запрсоов
    echo "<form method='get'>";
    echo "<label for='user_role'>Роль пользователя:</label>";
    echo "<select name='user_role'>";
    if (isset($_GET['user_role'])) {
        $user_role = $_GET['user_role'];
    } else {
        $user_role = '';
    }
    if ($user_role == "student") {
        echo "<option value='student' selected>Ученики</option>";
        echo "<option value='parent'>Родители</option>";
        echo "<option value='teacher'>Учителя</option>";
        echo "<option value='admin'>Администраторы</option>";
        echo "<option value='nothing'>Не выбрано</option>";
    } else if ($user_role == "parent") {
        echo "<option value='student'>Ученики</option>";
        echo "<option value='parent' selected>Родители</option>";
        echo "<option value='teacher'>Учителя</option>";
        echo "<option value='admin'>Администраторы</option>";
        echo "<option value='nothing'>Не выбрано</option>";
    } else if ($user_role == "teacher") {
        echo "<option value='student'>Ученики</option>";
        echo "<option value='parent'>Родители</option>";
        echo "<option value='teacher' selected>Учителя</option>";
        echo "<option value='admin'>Администраторы</option>";
        echo "<option value='nothing'>Не выбрано</option>";
    } else if ($user_role == "admin") {
        echo "<option value='student'>Ученики</option>";
        echo "<option value='parent'>Родители</option>";
        echo "<option value='teacher'>Учителя</option>";
        echo "<option value='admin' selected>Администраторы</option>";
        echo "<option value='nothing'>Не выбрано</option>";
    } else {
        echo "<option value='nothing'>Не выбрано</option>";
        echo "<option value='student'>Ученики</option>";
        echo "<option value='parent'>Родители</option>";
        echo "<option value='teacher'>Учителя</option>";
        echo "<option value='admin'>Администраторы</option>";
    }
    echo "</select>";
    echo "<label for='poll_id'>Опрос:</label>";
    echo "<select name='poll_id'>";
    echo "<option value='nothing'>Не выбрано</option>";
    $stmt = $pdo->prepare("SELECT id, title FROM polls");
    $stmt->execute();
    $polls = $stmt->fetchAll();
    foreach ($polls as $poll) {
        if (isset($_GET['poll_id']) && $_GET['poll_id'] == $poll['id']) {
            echo "<option value='" . $poll['id'] . "' selected>" . $poll['title'] . "</option>";
        } else {
            echo "<option value='" . $poll['id'] . "'>" . $poll['title'] . "</option>";
        }
    }
    echo " </select>";
    echo "<label for='class_id'>Класс:</label>";
    echo "<select name='class_id'>";
    echo "<option value='nothing'>Не выбрано</option>";
    $stmt = $pdo->prepare("SELECT id, name FROM classes");
    $stmt->execute();
    $classes = $stmt->fetchAll();
    foreach ($classes as $class) {
        if (isset($_GET['class_id']) && $_GET['class_id'] == $class['id']) {
            echo "<option value='" . $class['id'] . "' selected>" . $class['name'] . "</option>";
        } else {
            echo "<option value='" . $class['id'] . "'>" . $class['name'] . "</option>";
        }
    }
    echo " </select>";
    echo " <input type='submit' value='Показать результаты' name='filter' class='form_button'>";
    echo " <a href='?'>Сбросить фильтр</a>";
    echo "</form>";

    // Выполняем запросы для получения нужных данных для каждой первой записи, соответствующей выбранной роли пользователя
    $counter = 0;
    $flag = false;
    $counter2 = count($first_responses);

    foreach ($first_responses as $row) {
        //(1)
        $counter++;
        $poll_id = $row['poll_id'];
        $user_id = $row['id_user'];
        $date = $row['date'];
        $question_id = $row['question_id'];
        $fio = fio($user_id);
        $answer = $row['answer'];
        // Выполняем запрос для получения номеров вопросов и ответов попытки прохождения опроса
        $sql = "SELECT question_id, answer,answer_id, open_answer_points FROM poll_responses WHERE poll_id = :poll_id AND id_user = :id_user AND date = :date";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':poll_id', $poll_id);
        $stmt->bindValue(':id_user', $user_id);
        $stmt->bindValue(':date', $date);
        $stmt->execute();
        $question_id_answer = $stmt->fetchAll();

        // Выполняем запрос для получения заголовка опроса
        $sql = "SELECT title FROM polls WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $poll_id);
        $stmt->execute();
        $title = $stmt->FetchColumn();

        // Выполняем запрос для получения баллов для обычных отвебатов+ллы с открытых вопросов
        $sum = 0;
        foreach ($question_id_answer as $qia) {
            $sql = "SELECT answers.points FROM answers
        WHERE question_id = :question_id AND id = :answer_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':question_id', $qia['question_id']);
            $stmt->bindValue(':answer_id', $qia['answer_id']);
            $stmt->execute();
            $results = $stmt->fetchAll();
            $sum = $sum + $qia['open_answer_points'];
            foreach ($results as $result) {
                $sum = $sum + $result['points'];
            }
        }
        // Добавляем баллы со шкалы
        foreach ($question_id_answer as $qia) {
            $sql = "SELECT poll_responses.answer
        FROM poll_responses
        JOIN questions ON poll_responses.question_id = questions.id
        WHERE questions.answer_type = 'scale' AND poll_responses.question_id = :question_id AND poll_responses.answer = :answer
        AND poll_responses.id_user = :id_user AND poll_responses.date = :date";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':question_id', $qia['question_id']);
            $stmt->bindValue(':answer', $qia['answer']);
            $stmt->bindValue(':id_user', $user_id);
            $stmt->bindValue(':date', $date);
            $stmt->execute();
            $answers = $stmt->fetchAll();
            foreach ($answers as $answer) {
                $sum = $sum + $answer['answer'];
            }
        }
        // подсчёт баллов которые можно набрать в тесте
        //обычные вопросы
        $stmt = $pdo->prepare("SELECT SUM(points) AS max_points FROM answers
                       JOIN questions ON answers.question_id = questions.id
                       WHERE questions.poll_id = :poll_id");
        $stmt->bindValue(':poll_id', $poll_id);
        $stmt->execute();
        $max_points_row = $stmt->fetch();
        if (isset($max_points_row['max_points'])) {
            $max_points = $max_points_row['max_points'];
        } else {
            $max_points = 0;
        }

        //вопросы scale,open_answer
        $stmt = $pdo->prepare("SELECT COUNT(*) AS num_questions FROM questions
                       WHERE poll_id = :poll_id AND (answer_type = 'scale' OR answer_type = 'open_answer')");
        $stmt->bindValue(':poll_id', $poll_id);
        $stmt->execute();
        $num_questions_row = $stmt->fetch();
        if (isset($num_questions_row['num_questions'])) {
            $num_questions = $num_questions_row['num_questions'] * 10;
        } else {
            $num_questions = 0;
        }

        $max_points = $max_points + $num_questions;
        //расчёт показателя
        if ($max_points===0)
        {
            $percentage=100;
        }
        else
        {
            $percentage = $sum / $max_points * 100;
        }
        // Определяем уровень показателя на основе процента набранных баллов
        if ($percentage < 50) {
            $indicator = 'недопустимый уровень';
        } elseif ($percentage < 70) {
            $indicator = 'допустимый уровень';
        } else {
            $indicator = 'оптимальный уровень';
        }
        //работа с отчётом
        if (!file_exists('reports')) {
            mkdir('reports', 0777, true);
        }

        $filename = 'report';
        if (isset($title) && isset($user_id) && isset($date) && isset($sum) && isset($max_points) && isset($indicator)) {
            // Создаем новый документ Word
            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            $phpWord->setDefaultFontName('Times New Roman');
            $phpWord->setDefaultFontSize(12);

            // Добавляем страницу в документ
            $section = $phpWord->addSection();

            // Создаем таблицу и добавляем ее в документ
            $table = $section->addTable();
            $table->addRow();
            $table->addCell(2000)->addText('Название опроса');
            $table->addCell(2000)->addText('ID пользователя');
            $table->addCell(2000)->addText('Дата прохождения');
            $table->addCell(2000)->addText('Набранные баллы');
            $table->addCell(2000)->addText('Максимально возможные баллы');
            $table->addCell(2000)->addText('Показатель');

            // Создаем строку и добавляем ее в таблицу
            $row = $table->addRow();
            $row->addCell(2000)->addText($title);
            $row->addCell(2000)->addText($user_id);
            $row->addCell(2000)->addText($date);
            $row->addCell(2000)->addText($sum);
            $row->addCell(2000)->addText($max_points);
            $row->addCell(2000)->addText($indicator);

            $filename = 'report_' . $counter . '_.docx'; // Установить имя файла для отчетного файла
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save('reports/' . $filename);
        }
        //все строки сохраняем
        if (isset($title) && isset($user_id) && isset($date) && isset($sum) && isset($max_points) && isset($indicator)) {
            if ($flag === false) {
                $phpWordAll = new \PhpOffice\PhpWord\PhpWord();
                $phpWordAll->setDefaultFontName('Times New Roman');
                $phpWordAll->setDefaultFontSize(12);
                // Добавляем страницу в документ
                $sectionAll = $phpWordAll->addSection();
                // Создаем таблицу и добавляем ее в документ
                $tableAll = $sectionAll->addTable();
                // Добавляем заголовки таблицы
                $tableAll->addRow();
                $tableAll->addCell(2000)->addText('Название опроса');
                $tableAll->addCell(2000)->addText('ID пользователя');
                $tableAll->addCell(2000)->addText('Дата прохождения');
                $tableAll->addCell(2000)->addText('Набранные баллы');
                $tableAll->addCell(2000)->addText('Максимально возможные баллы');
                $tableAll->addCell(2000)->addText('Показатель');
                $flag = true;
            }
            $rowAll = $tableAll->addRow();
            $rowAll->addCell(2000)->addText($title);
            $rowAll->addCell(2000)->addText($user_id);
            $rowAll->addCell(2000)->addText($date);
            $rowAll->addCell(2000)->addText($sum);
            $rowAll->addCell(2000)->addText($max_points);
            $rowAll->addCell(2000)->addText($indicator);

            if ($counter == $counter2) {
                $filenameAll = 'report_all_.docx'; // Установить имя файла для отчетного файла
                $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWordAll, 'Word2007');
                $objWriter->save('reports/' . $filenameAll);
            }
        }
        // Выводим результаты в виде таблицы
        echo "<table class='table'><tr><th>Название опроса</th><th>ID пользователя</th><th>Дата прохождения</th><th>Баллов набрано</th><th>Баллов максимум</th><th>Показатель</th><th>Скачать отчёт</th><th>Ответы пользователя</th><th>Удалить</th></tr>";
        echo "<tr><td>" . $title . "</td><td>" . $user_id . "</td><td>" . $date . "</td><td>" . $sum . "</td><td>" . $max_points . "</td><td>" . $indicator . "</td><td><a href=/reports/$filename>Отчёт</a></td><td><a href='poll_response.php?poll_id=" . $poll_id . "&date=" . $date . "&user_id=" . $user_id ."'>Ответы пользователя</a></td>";
        echo "<td><form method='post'><input type='hidden' name='poll_id' value='" . $poll_id . "'><input type='hidden' name='date' value='" . $date . "'><input type='hidden' name='id_user' value='" . $user_id . "'>                                                              
        <input type='submit' value='Удалить' name='delete_record'></form></td></tr>";
        echo "</table>";
        if ($counter == $counter2) {
            echo "<a href=/reports/$filenameAll class='allresults'>Скачать все результаты</a></tr>";
        }
    }
    ?>
    <!--    <footer>-->
    <!--        <p>Все права защищены &copy; --><?php //echo date("Y"); ?><!--</p>-->
    <!--    </footer>-->
</div class="wrapper">
</body>
</html>