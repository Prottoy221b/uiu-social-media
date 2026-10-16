
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

require_once "db.php";

$user_id = (int) $_SESSION["user_id"];

// Fetch current user information
$user_sql = "
    SELECT id, name, account_type, department, email
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

// Only faculty can access this page
if (strtolower($user["account_type"]) !== "faculty") {
    header("Location: profile.php");
    exit();
}

$message = "";
$message_type = "";

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $employee_id = trim($_POST["employee_id"] ?? "");
    $designation = trim($_POST["designation"] ?? "");
    $office_room = trim($_POST["office_room"] ?? "");
    $office_hours = trim($_POST["office_hours"] ?? "");
    $bio = trim($_POST["bio"] ?? "");
    $research_interests = trim($_POST["research_interests"] ?? "");

    $update_sql = "
        INSERT INTO faculty_profiles
        (
            user_id,
            employee_id,
            designation,
            office_room,
            office_hours,
            bio,
            research_interests
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)

        ON DUPLICATE KEY UPDATE
            employee_id = VALUES(employee_id),
            designation = VALUES(designation),
            office_room = VALUES(office_room),
            office_hours = VALUES(office_hours),
            bio = VALUES(bio),
            research_interests = VALUES(research_interests)
    ";

    $update_stmt = mysqli_prepare($conn, $update_sql);

    mysqli_stmt_bind_param(
        $update_stmt,
        "issssss",
        $user_id,
        $employee_id,
        $designation,
        $office_room,
        $office_hours,
        $bio,
        $research_interests
    );

    if (mysqli_stmt_execute($update_stmt)) {
        $message = "Faculty profile updated successfully!";
        $message_type = "success";
    } else {
        $message = "Something went wrong. Please try again.";
        $message_type = "error";
    }
}

// Fetch faculty profile information
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
$faculty_profile = mysqli_fetch_assoc($profile_result) ?? [];

// Helper function for safe output
function e($value) {
    return htmlspecialchars(
        (string) ($value ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Faculty Profile | UIU Social Media</title>

    <link rel="stylesheet" href="style.css">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >

    <style>

        .faculty-page {
            max-width: 950px;
            margin: 35px auto;
            padding: 0 20px 50px;
        }

        .faculty-header {
            background: #ffffff;
            border-radius: 18px;
            padding: 30px;
            display: flex;
            align-items: center;
            gap: 25px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
        }

        .faculty-avatar {
            width: 115px;
            height: 115px;
            border-radius: 50%;
            background: #e8efff;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 48px;
            color: #315bb5;
            flex-shrink: 0;
        }

        .faculty-header-info h2 {
            margin: 0 0 8px;
            color: #1f2937;
            font-size: 28px;
        }

        .faculty-header-info p {
            margin: 6px 0;
            color: #6b7280;
        }

        .faculty-badge {
            display: inline-block;
            background: #e8f5e9;
            color: #237a3b;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-top: 8px;
        }

        .faculty-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 30px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
        }

        .faculty-card h3 {
            margin: 0 0 25px;
            color: #1f2937;
            font-size: 22px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 15px;
        }

        .faculty-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 12px 14px;
            border: 1px solid #d1d5db;
            border-radius: 9px;
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #315bb5;
        }

        .form-group textarea {
            min-height: 110px;
            resize: vertical;
        }

        .faculty-submit {
            grid-column: 1 / -1;
            border: none;
            background: #315bb5;
            color: white;
            padding: 13px 22px;
            border-radius: 9px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }

        .faculty-submit:hover {
            background: #244898;
        }

        .message {
            padding: 13px 16px;
            border-radius: 9px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .message.success {
            background: #e8f7ed;
            color: #21743a;
        }

        .message.error {
            background: #fdecec;
            color: #b42318;
        }

        .back-profile {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #315bb5;
            font-weight: 600;
        }

        @media (max-width: 650px) {

            .faculty-header {
                flex-direction: column;
                text-align: center;
            }

            .faculty-form {
                grid-template-columns: 1fr;
            }

            .form-group.full-width,
            .faculty-submit {
                grid-column: auto;
            }

            .faculty-card {
                padding: 20px;
            }

        }

    </style>

</head>

<body>

    <div id="header"></div>

    <main class="home_main">

        <div id="left-sidebar"></div>

        <div class="home_middle">

            <div class="faculty-page">

                <a href="profile.php" class="back-profile">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to Profile
                </a>

                <!-- Faculty Header -->

                <div class="faculty-header">

                    <div class="faculty-avatar">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>

                    <div class="faculty-header-info">

                        <h2>
                            <?php echo e($user["name"]); ?>
                        </h2>

                        <p>
                            <i class="fa-solid fa-envelope"></i>
                            <?php echo e($user["email"]); ?>
                        </p>

                        <p>
                            <i class="fa-solid fa-building-columns"></i>
                            Department:
                            <?php echo e($user["department"]); ?>
                        </p>

                        <span class="faculty-badge">
                            <i class="fa-solid fa-chalkboard-user"></i>
                            Faculty
                        </span>

                    </div>

                </div>

                <!-- Update Message -->

                <?php if ($message !== ""): ?>

                    <div class="message <?php echo e($message_type); ?>">
                        <?php echo e($message); ?>
                    </div>

                <?php endif; ?>

                <!-- Faculty Profile Form -->

                <div class="faculty-card">

                    <h3>
                        <i class="fa-solid fa-user-pen"></i>
                        Faculty Information
                    </h3>

                    <form
                        method="POST"
                        action=""
                        class="faculty-form"
                    >

                        <div class="form-group">

                            <label for="employee_id">
                                Employee ID
                            </label>

                            <input
                                type="text"
                                id="employee_id"
                                name="employee_id"
                                placeholder="e.g. UIU-FAC-102"
                                value="<?php echo e($faculty_profile["employee_id"] ?? ""); ?>"
                            >

                        </div>

                        <div class="form-group">

                            <label for="designation">
                                Designation
                            </label>

                            <input
                                type="text"
                                id="designation"
                                name="designation"
                                placeholder="e.g. Assistant Professor"
                                value="<?php echo e($faculty_profile["designation"] ?? ""); ?>"
                            >

                        </div>

                        <div class="form-group">

                            <label for="office_room">
                                Office Room
                            </label>

                            <input
                                type="text"
                                id="office_room"
                                name="office_room"
                                placeholder="e.g. Room 402"
                                value="<?php echo e($faculty_profile["office_room"] ?? ""); ?>"
                            >

                        </div>

                        <div class="form-group">

                            <label for="office_hours">
                                Office Hours
                            </label>

                            <input
                                type="text"
                                id="office_hours"
                                name="office_hours"
                                placeholder="e.g. Sat-Tue, 2 PM - 4 PM"
                                value="<?php echo e($faculty_profile["office_hours"] ?? ""); ?>"
                            >

                        </div>

                        <div class="form-group full-width">

                            <label for="bio">
                                About Me
                            </label>

                            <textarea
                                id="bio"
                                name="bio"
                                placeholder="Write something about yourself..."
                            ><?php echo e($faculty_profile["bio"] ?? ""); ?></textarea>

                        </div>

                        <div class="form-group full-width">

                            <label for="research_interests">
                                Research Interests
                            </label>

                            <textarea
                                id="research_interests"
                                name="research_interests"
                                placeholder="e.g. Cybersecurity, Artificial Intelligence, Machine Learning"
                            ><?php echo e($faculty_profile["research_interests"] ?? ""); ?></textarea>

                        </div>

                        <button
                            type="submit"
                            class="faculty-submit"
                        >
                            <i class="fa-solid fa-floppy-disk"></i>
                            Save Profile
                        </button>

                    </form>

                </div>

            </div>

        </div>

        <div id="right-sidebar"></div>

    </main>

    <script>

        // Load header
        fetch("header.html")
            .then(response => response.text())
            .then(data => {
                document.getElementById("header").innerHTML = data;
            })
            .catch(error => {
                console.error("Header loading error:", error);
            });

        // Load left sidebar
        fetch("left-sidebar.html")
            .then(response => response.text())
            .then(data => {
                document.getElementById("left-sidebar").innerHTML = data;
            })
            .catch(error => {
                console.error("Left sidebar loading error:", error);
            });

        // Load right sidebar
        fetch("right-sidebar.html")
            .then(response => response.text())
            .then(data => {
                document.getElementById("right-sidebar").innerHTML = data;
            })
            .catch(error => {
                console.error("Right sidebar loading error:", error);
            });

    </script>

</body>

</html>