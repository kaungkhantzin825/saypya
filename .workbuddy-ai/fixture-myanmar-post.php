<?php
// Insert the live Myanmar blog post into the LOCAL dev database so the rendering
// can be reproduced and verified without touching production.
//
// Run: php .workbuddy-ai/fixture-myanmar-post.php
// Remove: php .workbuddy-ai/fixture-myanmar-post.php --remove

$json = json_decode(file_get_contents('C:/Users/Ko Kaung/AppData/Local/Temp/myanmar-post.json'), true);
if (! $json) {
    // Fall back to wherever the earlier step wrote it.
    $candidates = [
        '/tmp/myanmar-post.json',
        __DIR__ . '/myanmar-post.json',
    ];
    foreach ($candidates as $c) {
        if (is_file($c)) {
            $json = json_decode(file_get_contents($c), true);
            break;
        }
    }
}
if (! $json) {
    fwrite(STDERR, "could not locate myanmar-post.json\n");
    exit(1);
}

$pdo = new PDO('mysql:host=127.0.0.1;dbname=Learningweb;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$slug = $json['slug'];

if (in_array('--remove', $argv, true)) {
    $stmt = $pdo->prepare('DELETE FROM blog_posts WHERE slug = ?');
    $stmt->execute([$slug]);
    echo "removed {$stmt->rowCount()} row(s) for slug {$slug}\n";
    exit(0);
}

$stmt = $pdo->prepare(
    'INSERT INTO blog_posts (title, slug, excerpt, content, featured_image, author_id, status, published_at, views_count, created_at, updated_at)
     VALUES (:title, :slug, :excerpt, :content, NULL, 1, :status, :published_at, 0, NOW(), NOW())
     ON DUPLICATE KEY UPDATE title = VALUES(title), excerpt = VALUES(excerpt), content = VALUES(content),
                             status = VALUES(status), published_at = VALUES(published_at), updated_at = NOW()'
);
$stmt->execute([
    ':title' => $json['title'],
    ':slug' => $slug,
    ':excerpt' => $json['excerpt'],
    ':content' => $json['content'],
    ':status' => $json['status'] ?: 'published',
    ':published_at' => $json['published_at'] ?? date('Y-m-d H:i:s'),
]);

$row = $pdo->query("SELECT id, CHAR_LENGTH(content) AS chars, status FROM blog_posts WHERE slug = " . $pdo->quote($slug))->fetch(PDO::FETCH_ASSOC);
echo "upserted: id={$row['id']} chars={$row['chars']} status={$row['status']}\n";
echo "url: /blog/{$slug}\n";
