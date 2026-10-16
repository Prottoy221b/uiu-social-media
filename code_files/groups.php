<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

include "db.php";

$current_user_id = $_SESSION["user_id"];

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Groups</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>


    <!-- =========================
                    HOME HEADER
         ========================= -->

    <div id="header"></div>


    <!-- =========================
                    MAIN PART
         ========================= -->

    <main class="home_main">


        <div id="left-sidebar"></div>


        <!-- =========================
             GROUP MIDDLE CONTENT START
             ========================= -->

        <div class="group_middle">


            <!-- =========================
                    GROUP TOP
                 ========================= -->

            <div class="group_middle_top">

                <h1>Groups</h1>

                <p>
                    Connect with your department,
                    clubs, and batchmates.
                </p>


                <div class="group_middle_top_buttons">

                    <button
                        class="active group_filter"
                        data-filter="all"
                        type="button"
                    >
                        All
                    </button>


                    <button
                        class="group_filter"
                        data-filter="department"
                        type="button"
                    >
                        Department
                    </button>


                    <button
                        class="group_filter"
                        data-filter="club"
                        type="button"
                    >
                        Club
                    </button>


                    <button
                        class="group_filter"
                        data-filter="batch"
                        type="button"
                    >
                        Batch
                    </button>


                    <button
                        class="group_filter"
                        data-filter="course"
                        type="button"
                    >
                        Course
                    </button>

                    <button type="button" id="createGroupBtn" class="create_group_btn">
    + Create Group
</button>

                </div>

            </div>


            <!-- =========================
                    GROUP LIST
                 ========================= -->

            <div
                class="group-rows"
                id="groupList"
            >

                <div class="group_loading">

                    Loading groups...

                </div>

            </div>


        </div>


        <!-- =========================
             GROUP MIDDLE CONTENT END
             ========================= -->


        <div id="right-sidebar"></div>


        <div id="createGroupModal" class="group_modal">

    <div class="group_modal_content">

        <div class="group_modal_header">
            <h2>Create New Group</h2>

            <button type="button"
                    id="closeGroupModal"
                    class="close_group_modal">
                &times;
            </button>
        </div>


        <form id="createGroupForm">

            <div class="group_form_field">

                <label for="groupName">
                    Group Name
                </label>

                <input
                    type="text"
                    id="groupName"
                    name="name"
                    placeholder="Enter group name"
                    maxlength="100"
                    required
                >

            </div>


            <div class="group_form_field">

                <label for="groupType">
                    Group Type
                </label>

                <select
                    id="groupType"
                    name="group_type"
                    required
                >

                    <option value="">
                        Select group type
                    </option>

                    <option value="department">
                        Department
                    </option>

                    <option value="club">
                        Club
                    </option>

                    <option value="batch">
                        Batch
                    </option>

                    <option value="course">
                        Course
                    </option>

                </select>

            </div>


            <div class="group_form_field">

                <label for="groupDescription">
                    Description
                </label>

                <textarea
                    id="groupDescription"
                    name="description"
                    placeholder="Write something about this group..."
                    maxlength="500"
                    required
                ></textarea>

            </div>


            <div class="group_form_actions">

                <button
                    type="button"
                    id="cancelGroupBtn"
                    class="cancel_group_btn">
                    Cancel
                </button>

                <button
                    type="submit"
                    class="submit_group_btn">
                    Create Group
                </button>

            </div>

        </form>

    </div>

</div>


    </main>


    <!-- =========================
                    SCRIPT
         ========================= -->

    <script src="script.js"></script>


    <!-- Current logged-in user -->

    <script>

        window.currentUserId =
            <?php echo (int)$current_user_id; ?>;

    </script>


</body>

</html>