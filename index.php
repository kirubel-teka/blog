<!DOCTYPE html>
<html lang="en" data-theme="dim">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <title>Microblog</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="navbar">
        <div class="navbar__brand">
            <div class="navbar__logo">Microblog</div>
        </div>
        <ul class="navbar__navigation">
            <li class="navbar__navigation-item"><a href="#" class="navbar__link">Recent</a></li>
            <li class="navbar__navigation-item"><a href="#" class="navbar__link">Calendar</a></li>
            <li class="navbar__navigation-item">
                <button id="theme-toggle" class="theme-toggle">Toggle Theme</button>
            </li>
        </ul>
    </header>

    <main class="main">
        <section>
            <h2>Add new entry</h2>
            <?php
            session_start();
            if (isset($_SESSION['upload_errors'])) {
                echo "<div class='form__error'>";
                foreach ($_SESSION['upload_errors'] as $error) {
                    echo "<p>$error</p>";
                }
                echo "</div>";
                unset($_SESSION['upload_errors']);
            }
            ?>
            <form class="form" action="add_entry.php" method="POST" enctype="multipart/form-data">
                <label class="form__label" for="entry-content">Entry contents:</label>
                <textarea class="form__textarea" id="entry-content" name="content" rows="4"></textarea>
                <label class="form__label" for="entry-image">Upload image (PNG, JPEG):</label>
                <input type="file" class="form__input" id="entry-image" name="image" accept="image/png,image/jpeg">
                <label class="form__label" for="entry-video">Upload video (MP4, WEBM):</label>
                <input type="file" class="form__input" id="entry-video" name="video" accept="video/mp4,video/webm">
                <button class="form__submit" type="submit">Add entry</button>
            </form>
        </section>

        <section>
            <h2>Recent posts</h2>
            <?php
            require_once 'config.php';
            try {
                $stmt = $pdo->query("SELECT id, content, image_path, video_path, created_at FROM entries ORDER BY created_at DESC");
                while ($entry = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $content_preview = !empty($entry['content']) ? (strlen($entry['content']) > 30 ? substr($entry['content'], 0, 27) . '...' : $entry['content']) : 'Media post';
                    $date = date('M d, Y', strtotime($entry['created_at']));
                    echo "<article class='entry'>";
                    echo "<header>";
                    echo "<h3 class='entry__title'>$content_preview</h3>";
                    echo "<span class='entry__date'> • $date</span>";
                    echo "<a href='delete_entry.php?id={$entry['id']}' class='entry__delete' onclick='return confirm(\"Are you sure you want to delete this entry?\");'>Delete</a>";
                    echo "</header>";
                    echo "<div class='entry__body'>";
                    if ($entry['image_path'] || $entry['video_path']) {
                        echo "<div class='media-container'>";
                        if ($entry['image_path']) {
                            echo "<img class='entry__media entry__image' src='{$entry['image_path']}' alt='Post image'>";
                        }
                        if ($entry['video_path']) {
                            echo "<video class='entry__media entry__video' controls><source src='{$entry['video_path']}' type='video/" . pathinfo($entry['video_path'], PATHINFO_EXTENSION) . "'></video>";
                        }
                        echo "</div>";
                    }
                    if (!empty($entry['content'])) {
                        echo "<p class='entry__content'>{$entry['content']}</p>";
                    }
                    echo "</div>";
                    echo "</article>";
                }
            } catch (PDOException $e) {
                echo "<p>Error fetching entries: " . $e->getMessage() . "</p>";
            }
            ?>
        </section>
    </main>

    <footer class="footer">
        <div class="footer__content">
            <div class="left">
                <p>Made by Kirubel Teka Zewdie</p>
                <p>Microblog is a blogging site</p>
            </div>
            <div class="right">
                <div class="footer__column">
                    <a href="#" class="footer__item">Recent</a>
                    <a href="#" class="footer__item">Calendar</a>
                </div>
                <div class="footer__column">
                    <a href="#" class="footer__item">About</a>
                    <a href="#" class="footer__item">How this was made</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        const html = document.documentElement;
        const toggle = document.getElementById('theme-toggle');
        const themes = ['light', 'dim', 'lights-out'];
        let currentThemeIndex = 0;

        toggle.addEventListener('click', () => {
            currentThemeIndex = (currentThemeIndex + 1) % themes.length;
            html.setAttribute('data-theme', themes[currentThemeIndex]);
            toggle.textContent = `Switch to ${themes[(currentThemeIndex + 1) % themes.length]} mode`;
        });

        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            html.setAttribute('data-theme', 'dim');
            toggle.textContent = 'Switch to lights-out mode';
            currentThemeIndex = 1;
        }
    </script>
</body>
</html>