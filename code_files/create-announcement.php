
<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");

session_start();

require_once "db.php";


/* =========================
   CHECK LOGIN
========================= */

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

$user_id = (int) $_SESSION["user_id"];

$message = "";
$message_type = "";

$title = "";
$description = "";
$target_audience = "";
$expiry_date = "";


/* =========================
   GET USER INFORMATION
========================= */

$user_sql = "
    SELECT id, name, account_type, department, email
    FROM users
    WHERE id = ?
    LIMIT 1
";

$user_stmt = mysqli_prepare($conn, $user_sql);

if (!$user_stmt) {
    die("User query error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $user_stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($user_stmt);

$user_result = mysqli_stmt_get_result($user_stmt);

$user = mysqli_fetch_assoc($user_result);

mysqli_stmt_close($user_stmt);


/* =========================
   CHECK USER
========================= */

if (!$user) {
    die("User information not found.");
}


/* =========================
   CHECK FACULTY
========================= */

if (strtolower(trim($user["account_type"])) !== "faculty") {
    die("Access denied. Only faculty members can create announcements.");
}

$department = trim($user["department"] ?? "");


/* =========================
   FORM SUBMISSION
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");

    $description = trim($_POST["description"] ?? "");

    $target_audience = trim($_POST["target_audience"] ?? "");

    $expiry_date = trim($_POST["expiry_date"] ?? "");


    /* =========================
       VALIDATION
    ========================= */

    if ($title === "") {

        $message = "Please enter an announcement title.";
        $message_type = "error";

    } elseif ($description === "") {

        $message = "Please write announcement description.";
        $message_type = "error";

    } elseif (
        $target_audience !== "all" &&
        $target_audience !== "students" &&
        $target_audience !== "faculty"
    ) {

        $message = "Please select a valid target audience.";
        $message_type = "error";

    } elseif ($department === "") {

        $message = "Your department is missing from your account.";
        $message_type = "error";

    } else {


        /* =========================
           INSERT WITH EXPIRY DATE
        ========================= */

        if ($expiry_date !== "") {

            $insert_sql = "
                INSERT INTO announcements
                (
                    user_id,
                    faculty_id,
                    title,
                    description,
                    department,
                    target_audience,
                    expiry_date
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ";

            $insert_stmt = mysqli_prepare($conn, $insert_sql);

            if (!$insert_stmt) {

                $message = "SQL prepare error: " . mysqli_error($conn);
                $message_type = "error";

            } else {

                /*
                 * user_id and faculty_id
                 * both refer to current faculty user.
                 */

                mysqli_stmt_bind_param(
                    $insert_stmt,
                    "iisssss",
                    $user_id,
                    $user_id,
                    $title,
                    $description,
                    $department,
                    $target_audience,
                    $expiry_date
                );

                if (mysqli_stmt_execute($insert_stmt)) {

                    $message = "Announcement posted successfully!";
                    $message_type = "success";

                    $title = "";
                    $description = "";
                    $target_audience = "";
                    $expiry_date = "";

                } else {

                    $message = "Database error: " . mysqli_stmt_error($insert_stmt);
                    $message_type = "error";
                }

                mysqli_stmt_close($insert_stmt);
            }


        } else {


            /* =========================
               INSERT WITHOUT EXPIRY DATE
            ========================= */

            $insert_sql = "
                INSERT INTO announcements
                (
                    user_id,
                    faculty_id,
                    title,
                    description,
                    department,
                    target_audience,
                    expiry_date
                )
                VALUES (?, ?, ?, ?, ?, ?, NULL)
            ";

            $insert_stmt = mysqli_prepare($conn, $insert_sql);

            if (!$insert_stmt) {

                $message = "SQL prepare error: " . mysqli_error($conn);
                $message_type = "error";

            } else {

                mysqli_stmt_bind_param(
                    $insert_stmt,
                    "iissss",
                    $user_id,
                    $user_id,
                    $title,
                    $description,
                    $department,
                    $target_audience
                );

                if (mysqli_stmt_execute($insert_stmt)) {

                    $message = "Announcement posted successfully!";
                    $message_type = "success";

                    $title = "";
                    $description = "";
                    $target_audience = "";
                    $expiry_date = "";

                } else {

                    $message = "Database error: " . mysqli_stmt_error($insert_stmt);
                    $message_type = "error";
                }

                mysqli_stmt_close($insert_stmt);
            }
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Announcement - UIU Social Media</title>


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >


    <!-- Main CSS -->

    <link rel="stylesheet" href="style.css">


    <style>

        /* =========================
           ANNOUNCEMENT PAGE
        ========================= */

        .announcement-page {

            max-width: 700px;

            margin: 25px auto;

            padding: 25px;

            background: #ffffff;

            border-radius: 15px;

            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);

        }


        .announcement-page h1 {

            margin-bottom: 8px;

            color: #222;

            font-size: 25px;

        }


        .announcement-subtitle {

            margin-bottom: 25px;

            color: #777;

            font-size: 14px;

        }


        /* Department */

        .department-info {

            margin-bottom: 22px;

            padding: 12px 15px;

            background: #f3f4f6;

            border-radius: 8px;

            color: #555;

            font-size: 14px;

        }


        /* Form group */

        .announcement-form-group {

            margin-bottom: 20px;

        }


        .announcement-form-group label {

            display: block;

            margin-bottom: 8px;

            color: #333;

            font-size: 14px;

            font-weight: 600;

        }


        .announcement-form-group input,

        .announcement-form-group textarea,

        .announcement-form-group select {

            width: 100%;

            box-sizing: border-box;

            padding: 12px;

            border: 1px solid #dddddd;

            border-radius: 8px;

            background: #ffffff;

            color: #222;

            font-family: inherit;

            font-size: 14px;

        }


        .announcement-form-group textarea {

            min-height: 150px;

            resize: vertical;

        }


        .announcement-form-group input:focus,

        .announcement-form-group textarea:focus,

        .announcement-form-group select:focus {

            outline: none;

            border-color: #ff6845;

            box-shadow: 0 0 0 2px rgba(255, 104, 69, 0.12);

        }


        /* Submit button */

        .announcement-submit-btn {

            display: block;

            width: 100%;

            padding: 13px;

            border: none;

            border-radius: 8px;

            background: #ff6845;

            color: #ffffff;

            font-size: 15px;

            font-weight: 600;

            cursor: pointer;

            transition: 0.3s ease;

        }


        .announcement-submit-btn:hover {

            background: #e95230;

        }


        /* Messages */

        .announcement-message {

            margin-bottom: 20px;

            padding: 13px 15px;

            border-radius: 8px;

            font-size: 14px;

            line-height: 1.5;

        }


        .announcement-success {

            background: #dcfce7;

            color: #166534;

            border: 1px solid #bbf7d0;

        }


        .announcement-error {

            background: #fee2e2;

            color: #991b1b;

            border: 1px solid #fecaca;

        }


        /* Mobile */

        @media (max-width: 768px) {

            .announcement-page {

                margin: 15px;

                padding: 20px;

            }


            .announcement-page h1 {

                font-size: 21px;

            }

        }

    </style>

</head>


<body>


    <!-- Header -->

    <div id="header"></div>


    <!-- Main Layout -->

    <main class="home_main">


        <!-- Left Sidebar -->

        <div id="left-sidebar"></div>


        <!-- Middle Content -->

        <div class="home_middle">


            <div class="announcement-page">


                <h1>

                    <i class="fa-solid fa-bullhorn"></i>

                    Create Announcement

                </h1>


                <p class="announcement-subtitle">

                    Share important information with students and faculty.

                </p>


                <!-- Success / Error Message -->

                <?php if ($message !== ""): ?>

                    <div class="announcement-message

                        <?php

                        echo $message_type === "success"

                            ? "announcement-success"

                            : "announcement-error";

                        ?>">

                        <?php echo htmlspecialchars($message); ?>

                    </div>

                <?php endif; ?>


                <!-- Department -->

                <div class="department-info">

                    <strong>Department:</strong>

                    <?php echo htmlspecialchars($department); ?>

                </div>


                <!-- Form -->

                <form
                    method="POST"
                    action="create-announcement.php"
                >


                    <!-- Title -->

                    <div class="announcement-form-group">

                        <label for="title">

                            Announcement Title

                        </label>


                        <input

                            type="text"

                            id="title"

                            name="title"

                            placeholder="Enter announcement title"

                            value="<?php echo htmlspecialchars($title); ?>"

                            required

                        >

                    </div>


                    <!-- Description -->

                    <div class="announcement-form-group">

                        <label for="description">

                            Announcement Description

                        </label>


                        <textarea

                            id="description"

                            name="description"

                            placeholder="Write your announcement..."

                            required

                        ><?php echo htmlspecialchars($description); ?></textarea>

                    </div>


                    <!-- Target Audience -->

                    <div class="announcement-form-group">

                        <label for="target_audience">

                            Target Audience

                        </label>


                        <select

                            id="target_audience"

                            name="target_audience"

                            required

                        >

                            <option value="">

                                Select Audience

                            </option>


                            <option

                                value="all"

                                <?php

                                echo $target_audience === "all"

                                    ? "selected"

                                    : "";

                                ?>

                            >

                                Everyone

                            </option>


                            <option

                                value="students"

                                <?php

                                echo $target_audience === "students"

                                    ? "selected"

                                    : "";

                                ?>

                            >

                                Students

                            </option>


                            <option

                                value="faculty"

                                <?php

                                echo $target_audience === "faculty"

                                    ? "selected"

                                    : "";

                                ?>

                            >

                                Faculty

                            </option>

                        </select>

                    </div>


                    <!-- Expiry Date -->

                    <div class="announcement-form-group">

                        <label for="expiry_date">

                            Expiry Date (Optional)

                        </label>


                        <input

                            type="date"

                            id="expiry_date"

                            name="expiry_date"

                            value="<?php echo htmlspecialchars($expiry_date); ?>"

                        >

                    </div>


                    <!-- Submit -->

                    <button

                        type="submit"

                        class="announcement-submit-btn"

                    >

                        <i class="fa-solid fa-paper-plane"></i>

                        Post Announcement

                    </button>


                </form>


            </div>

        </div>


        <!-- Right Sidebar -->

        <div id="right-sidebar"></div>


    </main>


    <!-- JavaScript -->

    <script src="script.js"></script>

</body>

</html>