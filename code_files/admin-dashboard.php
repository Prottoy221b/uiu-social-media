
<?php

session_start();


// =====================================================
// CHECK LOGIN
// =====================================================

if (!isset($_SESSION["user_id"])) {

    header("Location: login.html");
    exit();

}


include("db.php");

$current_user_id = (int) $_SESSION["user_id"];


// =====================================================
// CHECK ADMIN ROLE
// =====================================================

$admin_check_sql = "
    SELECT id, name, account_type, role
    FROM users
    WHERE id = ?
";

$admin_check_stmt = $conn->prepare($admin_check_sql);

$admin_check_stmt->bind_param(
    "i",
    $current_user_id
);

$admin_check_stmt->execute();

$admin_check_result =
    $admin_check_stmt->get_result();

$admin_user =
    $admin_check_result->fetch_assoc();


// =====================================================
// ACCESS DENIED FOR NON-ADMIN
// =====================================================

if (!$admin_user || $admin_user["role"] !== "admin") {

    http_response_code(403);

    echo "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Access Denied</title>
        </head>

        <body style='font-family: Arial; text-align: center; padding: 80px;'>

            <h1>403 - Access Denied</h1>

            <p>You do not have permission to access this page.</p>

            <a href='home.php'>Return to Home</a>

        </body>
        </html>
    ";

    exit();

}


// =====================================================
// TOTAL USERS
// =====================================================

$users_sql = "
    SELECT COUNT(*) AS total_users
    FROM users
";

$users_result = $conn->query($users_sql);

$users_data = $users_result->fetch_assoc();

$total_users = (int) $users_data["total_users"];


// =====================================================
// TOTAL POSTS
// =====================================================

$posts_sql = "
    SELECT COUNT(*) AS total_posts
    FROM posts
";

$posts_result = $conn->query($posts_sql);

$posts_data = $posts_result->fetch_assoc();

$total_posts = (int) $posts_data["total_posts"];


// =====================================================
// TOTAL COMMENTS
// =====================================================

$comments_sql = "
    SELECT COUNT(*) AS total_comments
    FROM comments
";

$comments_result = $conn->query($comments_sql);

$comments_data = $comments_result->fetch_assoc();

$total_comments = (int) $comments_data["total_comments"];


// =====================================================
// TOTAL FOLLOW RELATIONSHIPS
// =====================================================

$follows_sql = "
    SELECT COUNT(*) AS total_follows
    FROM follows
";

$follows_result = $conn->query($follows_sql);

$follows_data = $follows_result->fetch_assoc();

$total_follows = (int) $follows_data["total_follows"];


// =====================================================
// RECENT USERS
// =====================================================

$recent_users_sql = "
    SELECT
        id,
        name,
        account_type,
        email
    FROM users
    ORDER BY id DESC
    LIMIT 8
";

$recent_users_result =
    $conn->query($recent_users_sql);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Dashboard - UIU Social Media</title>


    <!-- FONT AWESOME -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >


    <style>

        * {

            margin: 0;
            padding: 0;
            box-sizing: border-box;

        }


        body {

            font-family: Arial, Helvetica, sans-serif;
            background: #f3f4f6;
            color: #1f2937;

        }


        .admin_layout {

            display: flex;
            min-height: 100vh;

        }


        /* ==========================================
                    ADMIN SIDEBAR
        ========================================== */

        .admin_sidebar {

            width: 250px;
            min-height: 100vh;
            background: #111827;
            color: white;
            padding: 25px 15px;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;

        }


        .admin_logo {

            font-size: 22px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 35px;
            padding-bottom: 20px;
            border-bottom: 1px solid #374151;

        }


        .admin_logo span {

            color: #60a5fa;

        }


        .admin_nav {

            display: flex;
            flex-direction: column;
            gap: 8px;

        }


        .admin_nav a {

            display: flex;
            align-items: center;
            gap: 13px;
            padding: 13px 15px;
            border-radius: 7px;
            color: #d1d5db;
            text-decoration: none;
            font-size: 14px;
            transition: 0.2s;

        }


        .admin_nav a:hover,
        .admin_nav a.active {

            background: #2563eb;
            color: white;

        }


        .admin_nav i {

            width: 20px;
            text-align: center;

        }


        .admin_logout {

            position: absolute;
            bottom: 25px;
            left: 15px;
            right: 15px;

        }


        .admin_logout a {

            background: #dc2626;
            color: white !important;
            justify-content: center;

        }


        .admin_logout a:hover {

            background: #b91c1c !important;

        }


        /* ==========================================
                    MAIN CONTENT
        ========================================== */

        .admin_content {

            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 30px;

        }


        .admin_topbar {

            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;

        }


        .admin_heading h1 {

            font-size: 28px;
            margin-bottom: 8px;

        }


        .admin_heading p {

            color: #6b7280;
            font-size: 14px;

        }


        .admin_profile {

            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            padding: 10px 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);

        }


        .admin_profile i {

            color: #2563eb;
            font-size: 20px;

        }


        .admin_profile_name {

            font-weight: bold;
            font-size: 14px;

        }


        .admin_profile_role {

            color: #6b7280;
            font-size: 12px;
            margin-top: 3px;

        }


        /* ==========================================
                    STATISTICS CARDS
        ========================================== */

        .stats_grid {

            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;

        }


        .stat_card {

            background: white;
            border-radius: 12px;
            padding: 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);

        }


        .stat_card h3 {

            font-size: 13px;
            font-weight: normal;
            color: #6b7280;
            margin-bottom: 8px;

        }


        .stat_card strong {

            font-size: 28px;
            color: #111827;

        }


        .stat_icon {

            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;

        }


        .users_icon {

            background: #dbeafe;
            color: #2563eb;

        }


        .posts_icon {

            background: #dcfce7;
            color: #16a34a;

        }


        .comments_icon {

            background: #fef3c7;
            color: #d97706;

        }


        .follows_icon {

            background: #fce7f3;
            color: #db2777;

        }


        /* ==========================================
                    CONTENT CARDS
        ========================================== */

        .dashboard_card {

            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
            margin-bottom: 25px;

        }


        .dashboard_card_header {

            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;

        }


        .dashboard_card_header h2 {

            font-size: 20px;

        }


        .view_all_button {

            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            padding: 9px 14px;
            font-size: 13px;

        }


        /* ==========================================
                    USERS TABLE
        ========================================== */

        .table_wrapper {

            width: 100%;
            overflow-x: auto;

        }


        .users_table {

            width: 100%;
            border-collapse: collapse;
            min-width: 550px;

        }


        .users_table th {

            background: #f9fafb;
            color: #6b7280;
            font-size: 13px;
            text-align: left;
            padding: 14px;

        }


        .users_table td {

            padding: 15px 14px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;

        }


        .users_table tr:hover {

            background: #f9fafb;

        }


        .user_id {

            color: #6b7280;

        }


        .account_badge {

            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            background: #e5e7eb;
            color: #374151;
            font-size: 12px;

        }


        .user_view_button {

            background: #eff6ff;
            color: #2563eb;
            padding: 7px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;

        }


        /* ==========================================
                    QUICK ACTIONS
        ========================================== */

        .quick_actions {

            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;

        }


        .quick_action {

            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            color: #374151;
            text-decoration: none;
            transition: 0.2s;

        }


        .quick_action:hover {

            background: #eff6ff;
            border-color: #93c5fd;

        }


        .quick_action i {

            font-size: 20px;
            color: #2563eb;

        }


        .quick_action strong {

            display: block;
            font-size: 14px;

        }


        .quick_action span {

            display: block;
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;

        }


        /* ==========================================
                    RESPONSIVE DESIGN
        ========================================== */

        @media (max-width: 1100px) {

            .stats_grid {

                grid-template-columns: repeat(2, 1fr);

            }

        }


        @media (max-width: 800px) {

            .admin_sidebar {

                width: 210px;

            }


            .admin_content {

                margin-left: 210px;
                width: calc(100% - 210px);
                padding: 20px;

            }


            .quick_actions {

                grid-template-columns: 1fr;

            }

        }


        @media (max-width: 600px) {

            .admin_sidebar {

                position: relative;
                width: 100%;
                min-height: auto;
                padding: 15px;

            }


            .admin_layout {

                display: block;

            }


            .admin_content {

                margin-left: 0;
                width: 100%;
                padding: 15px;

            }


            .admin_logout {

                position: static;
                margin-top: 20px;

            }


            .admin_topbar {

                flex-direction: column;
                align-items: flex-start;

            }


            .stats_grid {

                grid-template-columns: 1fr;

            }


            .stat_card {

                padding: 20px;

            }

        }

    </style>

</head>


<body>


    <div class="admin_layout">


        <!-- ==========================================
                    ADMIN SIDEBAR
        ========================================== -->


        <aside class="admin_sidebar">


            <div class="admin_logo">

                <span>UIU</span> Social Media

            </div>


            <nav class="admin_nav">


                <a
                    href="admin-dashboard.php"
                    class="active"
                >

                    <i class="fa-solid fa-chart-line"></i>

                    Dashboard

                </a>


                <a href="admin-users.php">

                    <i class="fa-solid fa-users"></i>

                    Manage Users

                </a>


                <a href="admin-posts.php">

                    <i class="fa-solid fa-newspaper"></i>

                    Manage Posts

                </a>


                <a href="admin-comments.php">

                    <i class="fa-solid fa-comments"></i>

                    Manage Comments

                </a>


                <a href="home.php">

                    <i class="fa-solid fa-house"></i>

                    User Home

                </a>


            </nav>


            <div class="admin_logout">


                <a href="logout.php">

                    <i class="fa-solid fa-right-from-bracket"></i>

                    Logout

                </a>


            </div>


        </aside>



        <!-- ==========================================
                    MAIN CONTENT
        ========================================== -->


        <main class="admin_content">


            <!-- TOPBAR -->


            <div class="admin_topbar">


                <div class="admin_heading">


                    <h1>Admin Dashboard</h1>


                    <p>

                        Welcome to UIU Social Media Administration

                    </p>


                </div>



                <div class="admin_profile">


                    <i class="fa-solid fa-circle-user"></i>


                    <div>


                        <div class="admin_profile_name">

                            <?php

                            echo htmlspecialchars(
                                $admin_user["name"]
                            );

                            ?>

                        </div>


                        <div class="admin_profile_role">

    <?php

    echo htmlspecialchars(
        ucfirst($admin_user["account_type"])
    );

    ?>

    ·

    <?php

    echo htmlspecialchars(
        ucfirst($admin_user["role"])
    );

    ?>

</div>


                    </div>


                </div>


            </div>



            <!-- STATISTICS CARDS -->


            <section class="stats_grid">


                <!-- TOTAL USERS -->


                <div class="stat_card">


                    <div>


                        <h3>Total Users</h3>


                        <strong>

                            <?php echo $total_users; ?>

                        </strong>


                    </div>


                    <div class="stat_icon users_icon">

                        <i class="fa-solid fa-users"></i>

                    </div>


                </div>



                <!-- TOTAL POSTS -->


                <div class="stat_card">


                    <div>


                        <h3>Total Posts</h3>


                        <strong>

                            <?php echo $total_posts; ?>

                        </strong>


                    </div>


                    <div class="stat_icon posts_icon">

                        <i class="fa-solid fa-newspaper"></i>

                    </div>


                </div>



                <!-- TOTAL COMMENTS -->


                <div class="stat_card">


                    <div>


                        <h3>Total Comments</h3>


                        <strong>

                            <?php echo $total_comments; ?>

                        </strong>


                    </div>


                    <div class="stat_icon comments_icon">

                        <i class="fa-solid fa-comments"></i>

                    </div>


                </div>



                <!-- TOTAL FOLLOWS -->


                <div class="stat_card">


                    <div>


                        <h3>Total Follows</h3>


                        <strong>

                            <?php echo $total_follows; ?>

                        </strong>


                    </div>


                    <div class="stat_icon follows_icon">

                        <i class="fa-solid fa-user-group"></i>

                    </div>


                </div>


            </section>



            <!-- QUICK ACTIONS -->


            <section class="dashboard_card">


                <div class="dashboard_card_header">


                    <h2>Quick Actions</h2>


                </div>


                <div class="quick_actions">


                    <a
                        href="admin-users.php"
                        class="quick_action"
                    >

                        <i class="fa-solid fa-users"></i>


                        <div>

                            <strong>Manage Users</strong>

                            <span>View and manage users</span>

                        </div>

                    </a>



                    <a
                        href="admin-posts.php"
                        class="quick_action"
                    >

                        <i class="fa-solid fa-newspaper"></i>


                        <div>

                            <strong>Manage Posts</strong>

                            <span>Review user posts</span>

                        </div>

                    </a>



                    <a
                        href="admin-comments.php"
                        class="quick_action"
                    >

                        <i class="fa-solid fa-comments"></i>


                        <div>

                            <strong>Manage Comments</strong>

                            <span>Review comments</span>

                        </div>

                    </a>


                </div>


            </section>



            <!-- RECENT USERS -->


            <section class="dashboard_card">


                <div class="dashboard_card_header">


                    <h2>Recent Users</h2>


                    <a
                        href="admin-users.php"
                        class="view_all_button"
                    >

                        View All

                    </a>


                </div>



                <div class="table_wrapper">


                    <table class="users_table">


                        <thead>


                            <tr>

                                <th>ID</th>

                                <th>Name</th>

                                <th>Account Type</th>

                                <th>Email</th>

                                <th>Action</th>

                            </tr>


                        </thead>



                        <tbody>


                            <?php if (
                                $recent_users_result &&
                                $recent_users_result->num_rows > 0
                            ): ?>


                                <?php while (
                                    $recent_user =
                                    $recent_users_result->fetch_assoc()
                                ): ?>


                                    <tr>


                                        <td class="user_id">

                                            <?php

                                            echo (int)
                                                $recent_user["id"];

                                            ?>

                                        </td>



                                        <td>

                                            <?php

                                            echo htmlspecialchars(
                                                $recent_user["name"]
                                            );

                                            ?>

                                        </td>



                                        <td>


                                            <span class="account_badge">

                                                <?php

                                                echo htmlspecialchars(
                                                    $recent_user["account_type"]
                                                );

                                                ?>

                                            </span>


                                        </td>



                                        <td>

                                            <?php

                                            echo htmlspecialchars(
                                                $recent_user["email"]
                                            );

                                            ?>

                                        </td>



                                        <td>


                                            <a
                                                href="profile.php?id=<?php echo (int) $recent_user["id"]; ?>"
                                                class="user_view_button"
                                            >

                                                View

                                            </a>


                                        </td>


                                    </tr>


                                <?php endwhile; ?>


                            <?php else: ?>


                                <tr>


                                    <td colspan="5">

                                        No users found.

                                    </td>


                                </tr>


                            <?php endif; ?>


                        </tbody>


                    </table>


                </div>


            </section>



        </main>


    </div>


</body>

</html>