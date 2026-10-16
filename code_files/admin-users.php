
<?php

session_start();


// =====================================================
// LOGIN CHECK
// =====================================================

if (!isset($_SESSION["user_id"])) {

    header("Location: login.html");
    exit();

}


include("db.php");

$current_user_id = (int) $_SESSION["user_id"];


// =====================================================
// ADMIN CHECK
// =====================================================

$admin_sql = "
    SELECT id, name, account_type, role
    FROM users
    WHERE id = ?
";

$admin_stmt = $conn->prepare($admin_sql);

$admin_stmt->bind_param(
    "i",
    $current_user_id
);

$admin_stmt->execute();

$admin_result = $admin_stmt->get_result();

$admin_user = $admin_result->fetch_assoc();


if (!$admin_user || $admin_user["role"] !== "admin") {

    http_response_code(403);

    echo "Access Denied";
    exit();

}


// =====================================================
// SEARCH USER
// =====================================================

$search = trim($_GET["search"] ?? "");


// =====================================================
// FETCH USERS
// =====================================================

if ($search !== "") {

    $user_sql = "
        SELECT
            id,
            name,
            account_type,
            department,
            email,
            role
        FROM users
        WHERE
            name LIKE ?
            OR email LIKE ?
            OR account_type LIKE ?
            OR role LIKE ?
        ORDER BY id DESC
    ";

    $user_stmt = $conn->prepare($user_sql);

    $search_value = "%" . $search . "%";

    $user_stmt->bind_param(
        "ssss",
        $search_value,
        $search_value,
        $search_value,
        $search_value
    );

    $user_stmt->execute();

    $users_result = $user_stmt->get_result();

} else {

    $user_sql = "
        SELECT
            id,
            name,
            account_type,
            department,
            email,
            role
        FROM users
        ORDER BY id DESC
    ";

    $users_result = $conn->query($user_sql);

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manage Users - Admin</title>


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


        /* SIDEBAR */

        .admin_sidebar {

            width: 250px;
            min-height: 100vh;
            background: #111827;
            color: white;
            padding: 25px 15px;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;

        }


        .admin_logo {

            text-align: center;
            font-size: 22px;
            font-weight: bold;
            padding-bottom: 22px;
            margin-bottom: 30px;
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
            gap: 12px;
            padding: 13px 15px;
            border-radius: 7px;
            color: #d1d5db;
            text-decoration: none;
            font-size: 14px;

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
            left: 15px;
            right: 15px;
            bottom: 25px;

        }


        .admin_logout a {

            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: #dc2626;
            border-radius: 7px;
            color: white;
            text-decoration: none;
            font-size: 14px;

        }


        .admin_logout a:hover {

            background: #b91c1c;

        }


        /* MAIN CONTENT */

        .admin_content {

            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 30px;

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
            margin-bottom: 8px;

        }


        .page_header p {

            color: #6b7280;
            font-size: 14px;

        }


        .admin_badge {

            background: white;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);

        }


        /* SEARCH */

        .search_card {

            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);

        }


        .search_form {

            display: flex;
            gap: 10px;

        }


        .search_form input {

            flex: 1;
            min-width: 0;
            padding: 12px 15px;
            border: 1px solid #d1d5db;
            border-radius: 7px;
            font-size: 14px;
            outline: none;

        }


        .search_form input:focus {

            border-color: #2563eb;

        }


        .search_button {

            border: none;
            background: #2563eb;
            color: white;
            padding: 12px 20px;
            border-radius: 7px;
            cursor: pointer;
            font-size: 14px;

        }


        .search_button:hover {

            background: #1d4ed8;

        }


        .clear_button {

            display: flex;
            align-items: center;
            background: #e5e7eb;
            color: #374151;
            padding: 12px 18px;
            border-radius: 7px;
            text-decoration: none;
            font-size: 14px;

        }


        /* TABLE */

        .table_card {

            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);

        }


        .table_header {

            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;

        }


        .table_header h2 {

            font-size: 20px;

        }


        .result_count {

            color: #6b7280;
            font-size: 13px;

        }


        .table_wrapper {

            width: 100%;
            overflow-x: auto;

        }


        .users_table {

            width: 100%;
            min-width: 850px;
            border-collapse: collapse;

        }


        .users_table th {

            text-align: left;
            background: #f9fafb;
            color: #6b7280;
            padding: 14px;
            font-size: 13px;
            white-space: nowrap;

        }


        .users_table td {

            padding: 14px;
            border-top: 1px solid #e5e7eb;
            font-size: 13px;

        }


        .users_table tr:hover {

            background: #f9fafb;

        }


        .user_id {

            color: #6b7280;

        }


        .badge {

            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 11px;
            background: #e5e7eb;
            color: #374151;

        }


        .admin_badge_role {

            background: #dbeafe;
            color: #1d4ed8;

        }


        .user_badge_role {

            background: #f3f4f6;
            color: #4b5563;

        }


        .view_button {

            display: inline-block;
            padding: 7px 11px;
            border-radius: 5px;
            background: #eff6ff;
            color: #2563eb;
            text-decoration: none;
            font-size: 12px;

        }


        .view_button:hover {

            background: #dbeafe;

        }


        .empty_message {

            text-align: center;
            padding: 30px;
            color: #6b7280;

        }


        /* RESPONSIVE */

        @media (max-width: 800px) {

            .admin_sidebar {

                width: 210px;

            }


            .admin_content {

                margin-left: 210px;
                width: calc(100% - 210px);
                padding: 20px;

            }


            .page_header {

                flex-direction: column;
                align-items: flex-start;

            }

        }


        @media (max-width: 600px) {

            .admin_layout {

                display: block;

            }


            .admin_sidebar {

                position: relative;
                width: 100%;
                min-height: auto;
                padding: 15px;

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


            .search_form {

                flex-wrap: wrap;

            }


            .search_form input {

                flex-basis: 100%;

            }

        }

    </style>

</head>


<body>


<div class="admin_layout">


    <!-- ==========================================
                    SIDEBAR
    ========================================== -->


    <aside class="admin_sidebar">


        <div class="admin_logo">

            <span>UIU</span> Social Media

        </div>


        <nav class="admin_nav">


            <a href="admin-dashboard.php">

                <i class="fa-solid fa-chart-line"></i>

                Dashboard

            </a>


            <a
                href="admin-users.php"
                class="active"
            >

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


        <div class="page_header">


            <div>


                <h1>Manage Users</h1>


                <p>

                    View and search registered users

                </p>


            </div>


            <div class="admin_badge">


                <i class="fa-solid fa-shield-halved"></i>

                Administrator


            </div>


        </div>



        <!-- SEARCH SECTION -->


        <section class="search_card">


            <form
                class="search_form"
                method="GET"
                action="admin-users.php"
            >


                <input
                    type="text"
                    name="search"
                    placeholder="Search by name, email, account type or role..."
                    value="<?php echo htmlspecialchars($search); ?>"
                >


                <button
                    type="submit"
                    class="search_button"
                >

                    <i class="fa-solid fa-magnifying-glass"></i>

                    Search

                </button>


                <?php if ($search !== ""): ?>


                    <a
                        href="admin-users.php"
                        class="clear_button"
                    >

                        Clear

                    </a>


                <?php endif; ?>


            </form>


        </section>



        <!-- USERS TABLE -->


        <section class="table_card">


            <div class="table_header">


                <h2>All Users</h2>


                <span class="result_count">

                    <?php

                    echo $users_result
                        ? $users_result->num_rows
                        : 0;

                    ?>

                    result(s)

                </span>


            </div>



            <div class="table_wrapper">


                <table class="users_table">


                    <thead>


                        <tr>

                            <th>ID</th>

                            <th>Name</th>

                            <th>Email</th>

                            <th>Account Type</th>

                            <th>Department</th>

                            <th>Role</th>

                            <th>Action</th>

                        </tr>


                    </thead>



                    <tbody>


                        <?php if (
                            $users_result &&
                            $users_result->num_rows > 0
                        ): ?>


                            <?php while (
                                $user = $users_result->fetch_assoc()
                            ): ?>


                                <tr>


                                    <td class="user_id">

                                        <?php

                                        echo (int)
                                            $user["id"];

                                        ?>

                                    </td>



                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $user["name"]
                                        );

                                        ?>

                                    </td>



                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $user["email"]
                                        );

                                        ?>

                                    </td>



                                    <td>

                                        <span class="badge">

                                            <?php

                                            echo htmlspecialchars(
                                                ucfirst(
                                                    $user["account_type"]
                                                )
                                            );

                                            ?>

                                        </span>

                                    </td>



                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $user["department"] ?? "N/A"
                                        );

                                        ?>

                                    </td>



                                    <td>


                                        <?php

                                        $role =
                                            strtolower(
                                                trim(
                                                    (string)
                                                    $user["role"]
                                                )
                                            );

                                        $role_class =
                                            $role === "admin"
                                            ? "admin_badge_role"
                                            : "user_badge_role";

                                        ?>


                                        <span
                                            class="badge <?php echo $role_class; ?>"
                                        >

                                            <?php

                                            echo htmlspecialchars(
                                                ucfirst($role)
                                            );

                                            ?>

                                        </span>


                                    </td>



                                    <td>


                                        <a
                                            href="profile.php?id=<?php echo (int) $user["id"]; ?>"
                                            class="view_button"
                                        >

                                            <i class="fa-solid fa-eye"></i>

                                            View

                                        </a>


                                    </td>


                                </tr>


                            <?php endwhile; ?>


                        <?php else: ?>


                            <tr>


                                <td
                                    colspan="7"
                                    class="empty_message"
                                >

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