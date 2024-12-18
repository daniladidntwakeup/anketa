<?php
require_once 'functions.php';
$pdo = connect();

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
$stmt = null;
if (isset($_GET['user_role']) && $_GET['user_role'] !== 'nothing') {
    if (isset($_GET['poll_id']) && $_GET['poll_id'] !== 'nothing') {
        $user_role = $_GET['user_role'];
        $poll_id = $_GET['poll_id'];
        $class_id = $_GET['class_id'];
        if ($class_id !== 'nothing') {
            $stmt = $pdo->prepare("SELECT poll_id, poll_responses.id_user, question_id, answer, date FROM poll_responses JOIN students ON poll_responses.id_user = students.id_user JOIN users ON poll_responses.id_user =users.id WHERE users.role = :user_role AND poll_id = :poll_id AND students.class_id = :class_id GROUP BY poll_id, poll_responses.id_user, date ORDER BY date DESC");
            $stmt->bindValue(':class_id', $class_id);
        } else {
            $stmt = $pdo->prepare("SELECT poll_id, poll_responses.id_user, question_id, answer, date FROM poll_responses JOIN users ON poll_responses.id_user = users.id WHERE users.role = :user_role AND poll_id = :poll_id GROUP BY poll_id, poll_responses.id_user, date ORDER BY date DESC");
        }
        $stmt->bindValue(':user_role', $user_role);
        $stmt->bindValue(':poll_id', $poll_id);
    } else {
        $user_role = $_GET['user_role'];
        $class_id = $_GET['class_id'];
        if ($class_id !== 'nothing') {
            $stmt = $pdo->prepare("SELECT poll_id, poll_responses.id_user, question_id, answer, date FROM poll_responses JOIN students ON poll_responses.id_user = students.id_user JOIN users ON poll_responses.id_user = users.id WHERE users.role = :user_role AND students.class_id = :class_id GROUP BY poll_id, poll_responses.id_user, date ORDER BY date DESC");
            $stmt->bindValue(':class_id', $class_id);
        } else {
            $stmt = $pdo->prepare("SELECT poll_id, poll_responses.id_user, question_id, answer, date FROM poll_responses JOIN users ON poll_responses.id_user = users.id WHERE users.role = :user_role GROUP BY poll_id, poll_responses.id_user, date ORDER BY date DESC");
        }
        $stmt->bindValue(':user_role', $user_role);
    }
} elseif (isset($_GET['poll_id']) && $_GET['poll_id'] !== 'nothing') {
    $poll_id = $_GET['poll_id'];
    $class_id = $_GET['class_id'];
    if ($class_id !== 'nothing') {
        $stmt = $pdo->prepare("SELECT poll_id, poll_responses.id_user, question_id, answer, date FROM poll_responses JOIN students ON poll_responses.id_user = students.id_user WHERE poll_id = :poll_id AND students.class_id = :class_id GROUP BY poll_id, poll_responses.id_user, date ORDER BY date DESC");
        $stmt->bindValue(':class_id', $class_id);
        $stmt->bindValue(':poll_id', $poll_id);
    } else {
        $stmt = $pdo->prepare("SELECT poll_id, poll_responses.id_user, question_id, answer, date FROM poll_responses WHERE poll_id = :poll_id GROUP BY poll_id, poll_responses.id_user, date ORDER BY date DESC");
        $stmt->bindValue(':poll_id', $poll_id);
    }
} else {
    $stmt = $pdo->prepare("SELECT poll_id, poll_responses.id_user,question_id, answer, date FROM poll_responses GROUP BY poll_id, poll_responses.id_user, date ORDER BY date DESC");
}
if ($stmt !== null) {
    $stmt->execute();
    $first_responses = $stmt->fetchAll();
}


echo "<form method='get'>";
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
echo "<select name='poll_id'>";
echo "<option value='nothing'>Не выбрано</option>";
$stmt = $pdo->prepare("SELECT id, title FROM polls");
$stmt->execute();
$polls = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($polls as $poll) {
    if (isset($_GET['poll_id']) && $_GET['poll_id'] == $poll['id']) {
        echo "<option value='" . $poll['id'] . "' selected>" . $poll['title'] . "</option>";
    } else {
        echo "<option value='" . $poll['id'] . "'>" . $poll['title'] . "</option>";
    }
}
echo " </select>";
echo "<select name='class_id'>";
echo "<option value='nothing'>Не выбрано</option>";
$stmt = $pdo->prepare("SELECT id, name FROM classes");
$stmt->execute();
$classes = $stmt->fetchAll();
foreach ($classes as $class) {
    if (isset($_GET['class_id']) && $_GET['class_id'] == $class['id']) {
        echo "<option value='" . $class['id'] . "' selected>" . $class['name'] . "</option>";
    }
    else {
        echo "<option value='" . $class['id'] . "'>" . $class['name'] . "</option>";
    }
}
echo " </select>";
echo " <input type='submit' value='Показать результаты' name='filter'>";
echo " <a href='?'>Сбросить фильтр</a>  ";
echo "</form>";

// Выполняем запросы для получения нужных данных для каждой первой записи, соответствующей выбранной роли пользователя
foreach ($first_responses as $row) {
    $poll_id = $row['poll_id'];
    $user_id = $row['id_user'];
    $date = $row['date'];
    $question_id = $row['question_id'];
    $fio = fio($user_id);
    $answer = $row['answer'];

    // Выполняем запрос для получения номеров вопросов и ответов попытки прохождения опроса
    $sql = "SELECT question_id, answer, open_answer_points FROM poll_responses WHERE poll_id = :poll_id AND id_user = :id_user AND date = :date";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':poll_id', $poll_id);
    $stmt->bindValue(':id_user', $user_id);
    $stmt->bindValue(':date', $date);
    $stmt->execute();
    $question_id_answer = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Выполняем запрос для получения заголовка опроса
    $sql = "SELECT title FROM polls WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $poll_id);
    $stmt->execute();
    $polls = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $title = $polls[0]["title"];

    // Выполняем запрос для получения баллов
    $sum = 0;
    foreach ($question_id_answer as $qia) {
        $sql = "SELECT points FROM answers WHERE question_id = :question_id AND answer = :answer";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':question_id', $qia['question_id']);
        $stmt->bindValue(':answer', $qia['answer']);
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
        WHERE questions.answer_type = 'scale' AND poll_responses.question_id = :question_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':question_id', $qia['question_id']);
        $stmt->execute();
        $answers = $stmt->fetchAll();
        foreach ($answers as $answer) {
            $sum = $sum + $answer['answer'];
        }
    }

    // Выводим результаты в виде таблицы
    echo "<table><tr><th>Название опроса</th><th>Имя пользователя</th><th>Дата прохождения</th><th>Баллы</th><th>Ответы пользователя</th><th>Удалить</th></tr>";
    echo "<tr><td>" . $title . "</td><td>" . $fio . "</td><td>" . $date . "</td><td>" . $sum . "</td><td><a href='poll_response.php?poll_id=" . $poll_id . "&date=" . $date . "'>Ответы пользователя</a></td>";
    echo "<td><form method='post'><input type='hidden' name='poll_id' value='" . $poll_id . "'><input type='hidden' name='date' value='" . $date . "'>
    <input type='hidden' name='id_user' value='" . $user_id . "'><input type='submit' value='Удалить' name='delete_record'></form></td></tr>";
    echo "</table>";
}