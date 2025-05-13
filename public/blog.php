<?php
session_start();
$pageTitle = "Blogs";
require_once '../includes/db.php';
include_once '../includes/header.php';

// Fetch blogs from database
$query = "SELECT * FROM Blog ORDER BY created_at DESC";
$result = $conn->query($query);

$blogs = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $blogs[] = $row;
    }
}
?>

<div class="page-header">
    <div class="container">
        <h1>Pet Care & Adoption Tips</h1>
        <p>Explore expert tips, stories, and guides for happy pet parenting.</p>
    </div>
</div>

<section class="blogs-section">
    <div class="container">
        <?php if (empty($blogs)): ?>
            <p>No blogs found. Check back later!</p>
        <?php else: ?>
            <div class="blog-grid">
                <?php foreach ($blogs as $blog): ?>
                    <div class="blog-card">
                        <h2><?php echo htmlspecialchars($blog['title']); ?></h2>
                        <small>Posted on <?php echo date("F j, Y", strtotime($blog['created_at'])); ?></small>
                        <p><?php echo nl2br(htmlspecialchars(substr($blog['content'], 0, 200))); ?>...</p>
                        <a href="blog_view.php?id=<?php echo $blog['id']; ?>" class="btn-read">Read More</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include_once '../includes/footer.php'; ?>
