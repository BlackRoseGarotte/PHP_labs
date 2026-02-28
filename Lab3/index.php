<?php

$dayOfWeek = date('N');


if ($dayOfWeek == 1 || $dayOfWeek == 3 || $dayOfWeek == 5) {
    $johnSchedule = '8:00-12:00';
} else {
    $johnSchedule = 'Нерабочий день';
}


if ($dayOfWeek == 2 || $dayOfWeek == 4 || $dayOfWeek == 6) {
    $janeSchedule = '12:00-16:00';
} else {
    $janeSchedule = 'Нерабочий день';
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Расписание работы</title>
    
<style>
        body {
            text-align: center;
        }
        table {
            margin: 0 auto;
            border: 1px solid black;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
        }

    </style>

</head>
<body>

<div class="current-day">
    Сегодня: <?php 
    $days = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];
    echo $days[date('w')] . ' (' . date('d.m.Y') . ')';
    ?>
</div>

<table>
    <thead>
        <tr>
            <th>№</th>
            <th>Фамилия Имя</th>
            <th>График работы</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>John Styles</td>
            <td><?php echo $johnSchedule; ?></td>
        </tr>
        <tr>
            <td>2</td>
            <td>Jane Doe</td>
            <td><?php echo $janeSchedule; ?></td>
        </tr>
    </tbody>
</table>

</body>
</html>

