
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

require_once "db.php";

$user_id = (int) $_SESSION["user_id"];

// Fetch faculty user information
$user_sql = "
    SELECT
        id,
        name,
        account_type,
        department,
        email
    FROM users
    WHERE id = ?
";

$user_stmt = mysqli_prepare($conn, $user_sql);
mysqli_stmt_bind_param($user_stmt, "i", $user_id);
mysqli_stmt_execute($user_stmt);

$user_result = mysqli_stmt_get_result($user_stmt);
$user = mysqli_fetch_assoc($user_result);

if (!$user) {
    session_destroy();
    header("Location: login.html");
    exit();
}

// Only faculty can access this dashboard
if (strtolower(trim($user["account_type"])) !== "faculty") {
    header("Location: profile.php");
    exit();
}

// Fetch faculty profile
$profile_sql = "
    SELECT
        employee_id,
        designation,
        office_room,
        office_hours,
        bio,
        research_interests
    FROM faculty_profiles
    WHERE user_id = ?
";

$profile_stmt = mysqli_prepare($conn, $profile_sql);
mysqli_stmt_bind_param($profile_stmt, "i", $user_id);
mysqli_stmt_execute($profile_stmt);

$profile_result = mysqli_stmt_get_result($profile_stmt);
$faculty_profile = mysqli_fetch_assoc($profile_result);

// Default values
$employee_id = $faculty_profile["employee_id"] ?? "Not added";
$designation = $faculty_profile["designation"] ?? "Not added";
$office_room = $faculty_profile["office_room"] ?? "Not added";
$office_hours = $faculty_profile["office_hours"] ?? "Not added";
$bio = $faculty_profile["bio"] ?? "No bio added yet.";
$research_interests = $faculty_profile["research_interests"] ?? "Not added";

// Total posts
$post_sql = "
    SELECT COUNT(*) AS total_posts
    FROM posts
    WHERE user_id = ?
";

$post_stmt = mysqli_prepare($conn, $post_sql);
mysqli_stmt_bind_param($post_stmt, "i", $user_id);
mysqli_stmt_execute($post_result = $post_stmt);

$post_data = mysqli_fetch_assoc(
    mysqli_stmt_get_result($post_stmt)
);

$total_posts = (int) ($post_data["total_posts"] ?? 0);

// Total followers
$followers_sql = "
    SELECT COUNT(*) AS total_followers
    FROM follows
    WHERE following_id = ?
";

$followers_stmt = mysqli_prepare($conn, $followers_sql);
mysqli_stmt_bind_param($followers_stmt, "i", $user_id);
mysqli_stmt_execute($followers_stmt);

$followers_data = mysqli_fetch_assoc(
    mysqli_stmt_get_result($followers_stmt)
);

$total_followers = (int) (
    $followers_data["total_followers"] ?? 0
);

// Total following
$following_sql = "
    SELECT COUNT(*) AS total_following
    FROM follows
    WHERE follower_id = ?
";

$following_stmt = mysqli_prepare($conn, $following_sql);
mysqli_stmt_bind_param($following_stmt, "i", $user_id);
mysqli_stmt_execute($following_stmt);

$following_data = mysqli_fetch_assoc(
    mysqli_stmt_get_result($following_stmt)
);

$total_following = (int) (
    $following_data["total_following"] ?? 0
);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Faculty Dashboard | UIU Social Media</title>

    <link rel="stylesheet" href="style.css">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >

    <style>

        .faculty-dashboard-content {
            padding: 30px 0 50px;
            min-width: 0;
        }

        .faculty-dashboard-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
            max-width: 950px;
            margin: 0 auto;
        }

        .faculty-dashboard-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .faculty-dashboard-heading h2 {
            margin: 0;
            color: #1f2937;
        }

        .faculty-dashboard-heading p {
            color: #6b7280;
            margin-top: 8px;
        }

        .faculty-edit-btn {
            display: inline-block;
            padding: 11px 18px;
            background: #526dce;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }

        .faculty-edit-btn:hover {
            background: #4058b5;
        }

        .faculty-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .faculty-stat-card {
            padding: 22px;
            background: #f5f7ff;
            border-radius: 12px;
            text-align: center;
        }

        .faculty-stat-card i {
            font-size: 24px;
            color: #526dce;
            margin-bottom: 10px;
        }

        .faculty-stat-card h3 {
            margin: 5px 0;
            font-size: 27px;
            color: #1f2937;
        }

        .faculty-stat-card p {
            margin: 0;
            color: #6b7280;
        }

        .faculty-section {
            margin-top: 25px;
            padding-top: 22px;
            border-top: 1px solid #e5e7eb;
        }

        .faculty-section h3 {
            color: #1f2937;
            margin-bottom: 18px;
        }

        .faculty-info-row {
            display: flex;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #f1f1f1;
            flex-wrap: wrap;
        }

        .faculty-info-label {
            width: 160px;
            font-weight: 600;
            color: #374151;
        }

        .faculty-info-value {
            flex: 1;
            color: #6b7280;
            overflow-wrap: anywhere;
        }

        .faculty-quick-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .faculty-quick-actions a {
            text-decoration: none;
            padding: 11px 16px;
            border-radius: 8px;
            background: #eef2ff;
            color: #526dce;
            font-weight: 600;
        }

        .faculty-quick-actions a:hover {
            background: #dfe6ff;
        }

        @media (max-width: 700px) {

            .faculty-dashboard-content {
                padding: 20px 15px;
            }

            .faculty-dashboard-card {
                padding: 20px;
            }

            .faculty-stats {
                grid-template-columns: 1fr;
            }

            .faculty-info-label {
                width: 100%;
            }

        }


        /* =========================
   FACULTY QUICK ACTIONS
========================= */

.quick-actions-section {
    background: #ffffff;
    padding: 25px;
    margin-top: 25px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
}

.section-heading {
    margin-bottom: 20px;
}

.section-heading h2 {
    font-size: 22px;
    color: #222;
    margin-bottom: 6px;
}

.section-heading h2 i {
    color: #4f46e5;
    margin-right: 8px;
}

.section-heading p {
    color: #777;
    font-size: 14px;
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
}

.quick-action-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 18px;
    background: #f8f9ff;
    border: 1px solid #eeeeff;
    border-radius: 12px;
    text-decoration: none;
    transition: 0.3s ease;
}

.quick-action-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
    background: #ffffff;
}

.quick-action-icon {
    min-width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 19px;
}

.announcement-icon {
    background: #e0e7ff;
    color: #4338ca;
}

.profile-icon {
    background: #fce7f3;
    color: #be185d;
}

.view-icon {
    background: #dcfce7;
    color: #15803d;
}

.quick-action-content {
    flex: 1;
}

.quick-action-content h3 {
    color: #222;
    font-size: 15px;
    margin-bottom: 5px;
}

.quick-action-content p {
    color: #777;
    font-size: 12px;
    line-height: 1.4;
}

.quick-action-arrow {
    color: #777;
    font-size: 13px;
}

/* Responsive */

@media (max-width: 900px) {
    .quick-actions-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .quick-actions-grid {
        grid-template-columns: 1fr;
    }

    .quick-actions-section {
        padding: 18px;
    }
}

    </style>

</head>

<body>

    <div id="header"></div>

    <main class="home_main">

        <div id="left-sidebar"></div>

        <div class="home_middle faculty-dashboard-content">

            <section class="faculty-dashboard-card">

                <div class="faculty-dashboard-heading">

                    <div>
                        <h2>
                            <i class="fa-solid fa-chalkboard-user"></i>
                            Faculty Dashboard
                        </h2>

                        <p>
                            Welcome, <?= htmlspecialchars($user["name"]) ?>
                        </p>
                    </div>

                    <a
                        href="faculty-profile.php"
                        class="faculty-edit-btn"
                    >
                        <i class="fa-solid fa-pen-to-square"></i>
                        Edit Faculty Profile
                    </a>

                </div>

                <!-- Statistics -->

                <div class="faculty-stats">

                    <div class="faculty-stat-card">

                        <i class="fa-solid fa-file-lines"></i>

                        <h3>
                            <?= $total_posts ?>
                        </h3>

                        <p>Total Posts</p>

                    </div>

                    <div class="faculty-stat-card">

                        <i class="fa-solid fa-users"></i>

                        <h3>
                            <?= $total_followers ?>
                        </h3>

                        <p>Followers</p>

                    </div>

                    <div class="faculty-stat-card">

                        <i class="fa-solid fa-user-plus"></i>

                        <h3>
                            <?= $total_following ?>
                        </h3>

                        <p>Following</p>

                    </div>

                </div>

                <!-- Basic Information -->

                <div class="faculty-section">

                    <h3>
                        <i class="fa-solid fa-user"></i>
                        Basic Information
                    </h3>

                    <div class="faculty-info-row">

                        <span class="faculty-info-label">
                            Full Name
                        </span>

                        <span class="faculty-info-value">
                            <?= htmlspecialchars($user["name"]) ?>
                        </span>

                    </div>

                    <div class="faculty-info-row">

                        <span class="faculty-info-label">
                            Account Type
                        </span>

                        <span class="faculty-info-value">
                            <?= htmlspecialchars($user["account_type"]) ?>
                        </span>

                    </div>

                    <div class="faculty-info-row">

                        <span class="faculty-info-label">
                            Department
                        </span>

                        <span class="faculty-info-value">
                            <?= htmlspecialchars($user["department"]) ?>
                        </span>

                    </div>

                    <div class="faculty-info-row">

                        <span class="faculty-info-label">
                            Email
                        </span>

                        <span class="faculty-info-value">
                            <?= htmlspecialchars($user["email"]) ?>
                        </span>

                    </div>

                </div>

                <!-- Faculty Information -->

                <div class="faculty-section">

                    <h3>
                        <i class="fa-solid fa-id-card"></i>
                        Faculty Information
                    </h3>

                    <div class="faculty-info-row">

                        <span class="faculty-info-label">
                            Employee ID
                        </span>

                        <span class="faculty-info-value">
                            <?= htmlspecialchars($employee_id) ?>
                        </span>

                    </div>

                    <div class="faculty-info-row">

                        <span class="faculty-info-label">
                            Designation
                        </span>

                        <span class="faculty-info-value">
                            <?= htmlspecialchars($designation) ?>
                        </span>

                    </div>

                    <div class="faculty-info-row">

                        <span class="faculty-info-label">
                            Office Room
                        </span>

                        <span class="faculty-info-value">
                            <?= htmlspecialchars($office_room) ?>
                        </span>

                    </div>

                    <div class="faculty-info-row">

                        <span class="faculty-info-label">
                            Office Hours
                        </span>

                        <span class="faculty-info-value">
                            <?= htmlspecialchars($office_hours) ?>
                        </span>

                    </div>

                </div>

                <!-- Bio -->

                <div class="faculty-section">

                    <h3>
                        <i class="fa-solid fa-address-card"></i>
                        Bio
                    </h3>

                    <p>
                        <?= nl2br(htmlspecialchars($bio)) ?>
                    </p>

                </div>

                <!-- Research Interests -->

                <div class="faculty-section">

                    <h3>
                        <i class="fa-solid fa-flask"></i>
                        Research Interests
                    </h3>

                    <p>
                        <?= nl2br(htmlspecialchars($research_interests)) ?>
                    </p>

                </div>

                <!-- Quick Actions -->
<!-- =========================
     QUICK ACTIONS
========================= -->

<div class="faculty-section quick-actions-section">

    <div class="section-heading">
        <h2>
            <i class="fa-solid fa-bolt"></i>
            Quick Actions
        </h2>

        <p>Quick access to faculty features</p>
    </div>

    <div class="quick-actions-grid">

        <!-- Create Announcement -->
        <a href="create-announcement.php" class="quick-action-card">
            <div class="quick-action-icon announcement-icon">
                <i class="fa-solid fa-bullhorn"></i>
            </div>

            <div class="quick-action-content">
                <h3>Create Announcement</h3>
                <p>Share important announcements with students.</p>
            </div>

            <i class="fa-solid fa-arrow-right quick-action-arrow"></i>
        </a>

        <!-- Faculty Profile -->
        <a href="faculty-profile.php" class="quick-action-card">
            <div class="quick-action-icon profile-icon">
                <i class="fa-solid fa-user-tie"></i>
            </div>

            <div class="quick-action-content">
                <h3>Edit Faculty Profile</h3>
                <p>Update your faculty information.</p>
            </div>

            <i class="fa-solid fa-arrow-right quick-action-arrow"></i>
        </a>

        <!-- View Profile -->
        <a href="profile.php" class="quick-action-card">
            <div class="quick-action-icon view-icon">
                <i class="fa-solid fa-user"></i>
            </div>

            <div class="quick-action-content">
                <h3>View Profile</h3>
                <p>View your public profile and posts.</p>
            </div>

            <i class="fa-solid fa-arrow-right quick-action-arrow"></i>
        </a>

    </div>

</div>
            </section>

        </div>

        <div id="right-sidebar"></div>

    </main>

    <script src="script.js"></script>

</body>

</html>