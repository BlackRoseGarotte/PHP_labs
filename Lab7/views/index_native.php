<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Книжный магазин (PHP Native)</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header>
        <h1>Книжный магазин (Native PHP)</h1>
        <a href="?engine=twig" style="background:#333; color:#fff; padding:5px 10px; text-decoration:none; border-radius:4px;">Switch to Twig</a>
    </header>

    <main>
        <section class="form-section">
            <h2>Добавить книгу</h2>
            <?php if ($success): ?>
                <div class="alert success">Книга добавлена!</div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <ul class="alert error">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <!-- Явный action с параметром engine -->
            <form action="?engine=<?= $engine ?>" method="POST">
                <div class="form-group">
                    <label>Название *</label>
                    <input type="text" name="title" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Автор *</label>
                    <input type="text" name="author" required value="<?= htmlspecialchars($_POST['author'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>ISBN *</label>
                    <input type="text" name="isbn" required pattern="^(97(8|9))?\d{9}(\d|X)?$" value="<?= htmlspecialchars($_POST['isbn'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Дата *</label>
                    <input type="date" name="publication_date" required value="<?= htmlspecialchars($_POST['publication_date'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Описание *</label>
                    <textarea name="description" required minlength="50"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Цена *</label>
                    <input type="number" name="price" required step="0.01" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
                </div>
                <div class="form-group checkbox-group">
                    <input type="checkbox" name="is_featured" value="1" <?= isset($_POST['is_featured']) ? 'checked' : '' ?>>
                    <label>Рекомендуемое</label>
                </div>
                <button type="submit">Сохранить</button>
            </form>
        </section>

        <section class="table-section">
            <h2>Список книг</h2>
            <table>
                <thead>
                    <tr>
                        <!-- Ссылки сортировки тоже должны сохранять engine -->
                        <th><a href="?sort=title&engine=<?= $engine ?>">Название</a></th>
                        <th><a href="?sort=author&engine=<?= $engine ?>">Автор</a></th>
                        <th><a href="?sort=price&engine=<?= $engine ?>">Цена</a></th>
                        <th><a href="?sort=publication_date&engine=<?= $engine ?>">Дата</a></th>
                        <th>Описание</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?= htmlspecialchars($book['title']) ?></td>
                            <td><?= htmlspecialchars($book['author']) ?></td>
                            <td><?= number_format((float)$book['price'], 2, '.', ' ') ?> MDL</td>
                            <td><?= htmlspecialchars($book['publication_date']) ?></td>
                            <td><?= htmlspecialchars(mb_strimwidth($book['description'], 0, 50, '...')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>