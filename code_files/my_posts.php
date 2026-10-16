<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

require_once 'db.php';

$user_id = (int) $_SESSION['user_id'];

$user_sql = "SELECT name FROM users WHERE id = ?";
$user_stmt = mysqli_prepare($conn, $user_sql);
mysqli_stmt_bind_param($user_stmt, 'i', $user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user = mysqli_fetch_assoc($user_result);

if (!$user) {
    session_destroy();
    header('Location: login.html');
    exit();
}

$post_sql = "
    SELECT
        posts.id,
        posts.content,
        posts.post_type,
        posts.created_at,
        (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count,
        (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id) AS comment_count
    FROM posts
    WHERE posts.user_id = ?
    ORDER BY posts.created_at DESC
";

$post_stmt = mysqli_prepare($conn, $post_sql);
mysqli_stmt_bind_param($post_stmt, 'i', $user_id);
mysqli_stmt_execute($post_result = $post_stmt);
$post_result = mysqli_stmt_get_result($post_stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Posts | UIU Social Media</title>

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        .my-posts-page {
            min-width: 0;
            padding: 25px 0 45px;
        }

        .my-posts-heading {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 18px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        }

        .my-posts-heading h2 {
            margin: 0 0 6px;
            color: #1f2937;
        }

        .my-posts-heading p {
            margin: 0;
            color: #6b7280;
        }

        .my-posts-empty {
            background: #ffffff;
            border-radius: 12px;
            padding: 45px 20px;
            text-align: center;
            color: #6b7280;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        }

        .my-posts-empty i {
            font-size: 38px;
            margin-bottom: 12px;
            color: #526dce;
        }

        @media (max-width: 1000px) {
            .my-posts-page {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div id="header"></div>

    <main class="home_main">
        <div id="left-sidebar"></div>

        <div class="home_middle my-posts-page">
            <section class="my-posts-heading">
                <h2>My Posts</h2>
                <p>Posts shared by <?= htmlspecialchars($user['name']) ?></p>
            </section>

            <div class="posts_feed">
                <?php if (mysqli_num_rows($post_result) === 0): ?>
                    <div class="my-posts-empty">
                        <i class="fa-regular fa-pen-to-square"></i>
                        <h3>No posts yet</h3>
                        <p>You have not shared any posts yet.</p>
                    </div>
                <?php else: ?>
                    <?php while ($post = mysqli_fetch_assoc($post_result)): ?>
                        <article class="home_middle_bottom" data-post-id="<?= (int) $post['id'] ?>">
                            <div class="home_middle_bottom_top">
                                <img src="../img/profile_pic.jpg" alt="Profile picture">
                                <div class="past_name_time">
                                    <p class="others_name"><?= htmlspecialchars($user['name']) ?></p>
                                    <p><?= htmlspecialchars($post['created_at']) ?></p>
                                </div>
                            </div>

                            <div class="middle_posts">
                                <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                            </div>

                            <div class="like_comment_counter">
                                <p class="like-count"><?= (int) $post['like_count'] ?> Likes</p>
                                <p class="comment-count"><?= (int) $post['comment_count'] ?> Comments</p>
                                <p>0 Shares</p>
                            </div>

                            <div class="home_middle_bottom_bottom">
                                <button class="action_btn like" type="button">
                                    <i class="fa-regular fa-thumbs-up like_icon"></i>
                                    <span>Like</span>
                                </button>

                                <button class="action_btn comment" type="button">
                                    <i class="fa-regular fa-comment comment_icon"></i>
                                    <span>Comment</span>
                                </button>

                                <button class="action_btn share" type="button">
                                    <i class="fa-solid fa-share share_icon"></i>
                                    <span>Share</span>
                                </button>
                            </div>

                            <div class="comment_section">
                                <div class="comments_list"></div>

                                <div class="comment_input_box">
                                    <img src="../img/profile_pic.jpg" alt="User Profile" class="comment_user_pic">
                                    <div class="comment_field_wrapper">
                                        <input type="text" class="comment_input" placeholder="Write a comment...">
                                        <button class="send_comment_btn" type="button">Comment</button>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>

        <div id="right-sidebar"></div>
    </main>

    <script src="script.js"></script>
</body>
</html>