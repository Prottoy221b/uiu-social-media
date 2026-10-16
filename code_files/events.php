<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

$current_user_id = $_SESSION["user_id"];

?>

<!doctype html>

<html lang="en">

<head>

    <meta charset="UTF-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Events</title>

    <link rel="stylesheet" href="style.css" />

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


        <!-- =========================
                LEFT SIDEBAR
        ========================= -->

        <div id="left-sidebar"></div>


        <!-- =========================
                HOME MIDDLE CONTENT
        ========================= -->

        <div class="home_middle">


            <!-- =========================
                    EVENT TOP
            ========================= -->

            <div class="event_top">

                <div class="event_top_left">

                    <h2>Events</h2>

                    <p>
                        Seminars, fests, and meetups happening at UIU.
                    </p>

                </div>


                <div class="event_top_right">

                    <div class="event_btn">

                        <button
                            class="create_event_btn"
                            type="button"
                        >
                            Create Event
                        </button>

                    </div>

                </div>

            </div>


            <!-- =========================
                    CREATE EVENT FORM
            ========================= -->

            <div class="event_form">

                <input
                    type="text"
                    class="event_title_input"
                    placeholder="Event Title"
                >

                <input
                    type="date"
                    class="event_date_input"
                >

                <input
                    type="time"
                    class="event_time_input"
                >

                <input
                    type="text"
                    class="event_location_input"
                    placeholder="Location"
                >
                <textarea class="event_description_input"
          placeholder="Event Description"
          maxlength="1000"></textarea>

                <button
                    class="submit_event_btn"
                    type="button"
                >
                    Create
                </button>

            </div>


            <!-- =========================
                    EVENT CARDS
            ========================= -->

            <div
                class="event_cards"
                id="eventCards"
            >

                <p>Loading events...</p>

            </div>


        </div>


        <!-- =========================
                RIGHT SIDEBAR
        ========================= -->

        <div id="right-sidebar"></div>


    </main>


    <!-- =========================
            CURRENT USER
    ========================= -->

    <script>

        window.currentUserId =
            <?php echo (int)$current_user_id; ?>;

    </script>


    <!-- =========================
            MAIN JAVASCRIPT
    ========================= -->

    <script src="script.js"></script>


</body>

</html>