 
<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

include "db.php";

/*
=================================
CHECK ADMIN ROLE
=================================
*/

$current_user_id = $_SESSION["user_id"];

$admin_sql = "
    SELECT role
    FROM users
    WHERE id = ?
";

$admin_stmt = $conn->prepare($admin_sql);
$admin_stmt->bind_param("i", $current_user_id);
$admin_stmt->execute();

$admin_result = $admin_stmt->get_result();
$admin_user = $admin_result->fetch_assoc();

if (!$admin_user || $admin_user["role"] !== "admin") {
    echo "Access Denied";
    exit();
}

/*
=================================
DELETE COMMENT
=================================
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["delete_comment_id"])
) {

    $delete_comment_id = intval($_POST["delete_comment_id"]);

    $delete_sql = "
        DELETE FROM comments
        WHERE id = ?
    ";

    $delete_stmt = $conn->prepare($delete_sql);

    $delete_stmt->bind_param(
        "i",
        $delete_comment_id
    );

    if ($delete_stmt->execute()) {

        header("Location: admin-comments.php?deleted=success");
        exit();

    } else {

        header("Location: admin-comments.php?deleted=failed");
        exit();

    }
}

/*
=================================
SEARCH COMMENTS
=================================
*/

$search = trim($_GET["search"] ?? "");

if ($search !== "") {

    $comments_sql = "
        SELECT
            comments.id,
            comments.user_id,
            comments.post_id,
            comments.content,
            comments.created_at,
            users.name,
            users.email

        FROM comments

        JOIN users
        ON comments.user_id = users.id

        WHERE
            comments.content LIKE ?
            OR users.name LIKE ?
            OR users.email LIKE ?

        ORDER BY comments.created_at DESC
    ";

    $stmt = $conn->prepare($comments_sql);

    $search_value = "%" . $search . "%";

    $stmt->bind_param(
        "sss",
        $search_value,
        $search_value,
        $search_value
    );

} else {

    $comments_sql = "
        SELECT
            comments.id,
            comments.user_id,
            comments.post_id,
            comments.content,
            comments.created_at,
            users.name,
            users.email

        FROM comments

        JOIN users
        ON comments.user_id = users.id

        ORDER BY comments.created_at DESC
    ";

    $stmt = $conn->prepare($comments_sql);
}

$stmt->execute();

$comments_result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Comments Management</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #222;
        }

        .admin_layout {
            display: flex;
            min-height: 100vh;
        }

        .admin_sidebar {
            width: 240px;
            background: #172033;
            color: white;
            padding: 25px 15px;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
        }

        .admin_sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 22px;
        }

        .admin_sidebar a {
            display: block;
            text-decoration: none;
            color: #dce3f0;
            padding: 13px 15px;
            margin-bottom: 8px;
            border-radius: 6px;
        }

        .admin_sidebar a:hover,
        .admin_sidebar a.active {
            background: #2e405f;
            color: white;
        }

        .admin_content {
            margin-left: 240px;
            padding: 30px;
            width: calc(100% - 240px);
        }

        .page_header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }

        .page_header h1 {
            font-size: 28px;
        }

        .search_form {
            display: flex;
            gap: 8px;
        }

        .search_form input {
            width: 260px;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .search_form button {
            padding: 11px 18px;
            border: none;
            border-radius: 6px;
            background: #2563eb;
            color: white;
            cursor: pointer;
        }

        .message {
            background: #d1fae5;
            color: #065f46;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .table_container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            overflow-x: auto;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }

        th,
        td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        th {
            background: #f1f5f9;
            font-size: 14px;
        }

        td {
            font-size: 14px;
        }

        .comment_content {
            max-width: 300px;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .post_id {
            display: inline-block;
            background: #e0e7ff;
            color: #3730a3;
            padding: 5px 9px;
            border-radius: 5px;
            font-size: 12px;
        }

        .view_btn {
            display: inline-block;
            text-decoration: none;
            background: #2563eb;
            color: white;
            padding: 7px 11px;
            border-radius: 5px;
            margin-bottom: 6px;
        }

        .delete_btn {
            background: #dc2626;
            color: white;
            border: none;
            padding: 8px 11px;
            border-radius: 5px;
            cursor: pointer;
        }

        .delete_btn:hover {
            background: #b91c1c;
        }

        .empty_message {
            text-align: center;
            padding: 30px;
            color: #64748b;
        }

        @media (max-width: 800px) {

            .admin_sidebar {
                width: 190px;
            }

            .admin_content {
                margin-left: 190px;
                width: calc(100% - 190px);
                padding: 20px;
            }

            .page_header {
                flex-direction: column;
                align-items: flex-start;
            }

            .search_form input {
                width: 190px;
            }

        }

    </style>

</head>

<body>

    <div class="admin_layout">

        <!-- =========================
             ADMIN SIDEBAR
        ========================= -->

        <aside class="admin_sidebar">

            <h2>Admin Panel</h2>

            <a href="admin-dashboard.php">
                Dashboard
            </a>

            <a href="admin-users.php">
                Manage Users
            </a>

            <a href="admin-posts.php">
                Manage Posts
            </a>

            <a href="admin-comments.php" class="active">
                Manage Comments
            </a>

            <a href="dashboard.php">
                Home Feed
            </a>

            <a href="logout.php">
                Logout
            </a>

        </aside>


        <!-- =========================
             MAIN CONTENT
        ========================= -->

        <main class="admin_content">

            <div class="page_header">

                <h1>Comments Management</h1>

                <form
                    method="GET"
                    action="admin-comments.php"
                    class="search_form"
                >

                    <input
                        type="text"
                        name="search"
                        placeholder="Search comments..."
                        value="<?php echo htmlspecialchars($search); ?>"
                    >

                    <button type="submit">
                        Search
                    </button>

                </form>

            </div>


            <?php if (isset($_GET["deleted"]) && $_GET["deleted"] === "success"): ?>

                <div class="message">
                    Comment deleted successfully.
                </div>

            <?php endif; ?>


            <div class="table_container">

                <?php if ($comments_result->num_rows > 0): ?>

                    <table>

                        <thead>

                            <tr>
                                <th>ID</th>
                                <th>Author</th>
                                <th>Email</th>
                                <th>Post ID</th>
                                <th>Comment</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php while ($comment = $comments_result->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        <?php echo (int) $comment["id"]; ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($comment["name"]); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($comment["email"]); ?>
                                    </td>

                                    <td>

                                        <span class="post_id">
                                            <?php echo (int) $comment["post_id"]; ?>
                                        </span>

                                    </td>

                                    <td class="comment_content">
                                        <?php echo htmlspecialchars($comment["content"]); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($comment["created_at"]); ?>
                                    </td>

                                    <td>

                                        <a
                                            class="view_btn"
                                            href="profile.php?id=<?php echo (int) $comment["user_id"]; ?>"
                                        >
                                            View Author
                                        </a>


                                        <form
                                            method="POST"
                                            action="admin-comments.php"
                                            onsubmit="return confirm('Are you sure you want to delete this comment?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="delete_comment_id"
                                                value="<?php echo (int) $comment["id"]; ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="delete_btn"
                                            >
                                                Delete
                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        </tbody>

                    </table>

                <?php else: ?>

                    <div class="empty_message">
                        No comments found.
                    </div>

                <?php endif; ?>

            </div>

        </main>

    </div>

</body>

</html>