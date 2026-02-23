<?php
// Получаем текущий день недели (0 - воскресенье, 1 - понедельник и т.д.)
$dayOfWeek = date('N'); // 1 (понедельник) до 7 (воскресенье)

// Функция для определения графика John Styles
function getJohnStylesSchedule($day) {
    // Понедельник (1), среда (3), пятница (5)
    if ($day == 1 || $day == 3 || $day == 5) {
        return '8:00-12:00';
    } else {
        return 'Нерабочий день';
    }
}

// Функция для определения графика Jane Doe
function getJaneDoeSchedule($day) {
    // Вторник (2), четверг (4), суббота (6)
    if ($day == 2 || $day == 4 || $day == 6) {
        return '12:00-16:00';
    } else {
        return 'Нерабочий день';
    }
}

// Получаем расписание
$johnSchedule = getJohnStylesSchedule($dayOfWeek);
$janeSchedule = getJaneDoeSchedule($dayOfWeek);
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