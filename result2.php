<?php
require_once 'functions.php'; // Подключаем файл с функцией checkAccess
checkAccess();
// создание подключения к базе данных с использованием PDO
try {
    require_once 'functions.php'; // Подключаем файл с функцией checkAccess
    $pdo = connect();
    $sql = "SELECT u.id, u.role,
    (SELECT a.Fio FROM admins a WHERE a.id_user = u.id
     UNION
     SELECT p.Fio FROM parents p WHERE p.id_user = u.id
     UNION
     SELECT st.Fio FROM students st WHERE st.id_user = u.id
     UNION
     SELECT t.Fio FROM teachers t WHERE t.id_user = u.id) AS Fio,
    pl.title AS poll_title, pr.date AS attempt_date,
    IF(SUM(ans.is_correct) IS NULL, 0, SUM(ans.is_correct)) AS correct_answers_count, COUNT(DISTINCT q.id) AS total_questions_count
    FROM users u
    JOIN poll_responses pr ON u.id = pr.id_user
    JOIN polls pl ON pr.poll_id = pl.id
    JOIN questions q ON pr.poll_id = q.poll_id
    LEFT JOIN answers ans ON q.id = ans.question_id AND ans.is_correct = 1 AND pr.answer_id = ans.id
    GROUP BY u.id, u.role, Fio, pl.title, pr.date";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    // вывод результатов на экран
    if ($stmt->rowCount() > 0) {
        // вывод заголовков таблицы
        echo "<table><tr><th>Роль</th><th>ФИО пользователя</th><th>Название опроса</th><th>Количество верных ответов</th><th>Количество вопросов</th><th>Дата прохождения опроса</th></tr>";
        // вывод строк таблицы с данными
        while ($row = $stmt->fetch()) {
            echo "<tr><td>" . $row["role"] . "</td><td>" . $row["Fio"] . "</td><td>" . $row["poll_title"] . "</td><td>" . $row["correct_answers_count"] . "</td><td>" . $row["total_questions_count"] . "</td><td>" . $row["attempt_date"] . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "0 results";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>