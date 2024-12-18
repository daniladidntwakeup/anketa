<?php
require_once 'functions.php';
$pdo = connect();
// Получаем идентификатор опроса из параметров запроса
//$poll_id = isset($_GET['poll_id']) ? intval($_GET['poll_id']) : 0;
$poll_id = 11;
// Выбираем информацию об опросе из базы данных
$stmt = $pdo->prepare('SELECT title FROM polls WHERE id = :poll_id');
$stmt->execute([':poll_id' => $poll_id]);
$poll = $stmt->fetch(PDO::FETCH_ASSOC);
// Проверяем, что опрос существует
if (!$poll) {
    echo 'Опрос не найден';
    exit;
}
// Выбираем результаты опроса из базы данных
$stmt = $pdo->prepare('SELECT pr.answer_id, a.answer, a.is_correct, COUNT(*) as count, 
                              CASE
                                  WHEN st.fio IS NOT NULL THEN st.fio
                                  WHEN pa.fio IS NOT NULL THEN pa.fio
                                  WHEN ad.fio IS NOT NULL THEN ad.fio
                                  WHEN t.fio IS NOT NULL THEN t.fio
                                  ELSE ""
                              END as fio
                       FROM poll_responses pr
                       JOIN answers a ON pr.answer_id = a.id
                       JOIN users u ON pr.id_user = u.id
                       LEFT JOIN students st ON u.id = st.id_user
                       LEFT JOIN parents pa ON u.id = pa.id_user
                       LEFT JOIN admins ad ON u.id = ad.id_user
                       LEFT JOIN teachers t ON u.id = t.id_user
                       WHERE pr.poll_id = :poll_id
                       GROUP BY pr.answer_id, a.answer, a.is_correct, fio');
$stmt->execute([':poll_id' => $poll_id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Результаты опроса "<?php echo htmlspecialchars($poll['title']) ?>"</title>
</head>
<body>
<h1>Результаты опроса "<?php echo htmlspecialchars($poll['title']) ?>"</h1>
<?php if (count($results) == 0): ?>
    <p>На данный момент еще нет результатов этого опроса</p>
<?php else: ?>
    <table>
        <tr>
            <th>ФИО</th>
            <th>Ответ</th>
            <th>Количество</th>
            <th>Правильный</th>
        </tr>
        <?php foreach ($results as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['fio']) ?></td>
                <td><?php echo htmlspecialchars($row['answer']) ?></td>
                <td><?php echo intval($row['count']) ?></td>
                <td><?php echo $row['is_correct'] ? 'Да' : 'Нет' ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
</body>
</html>