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

    <title>Messages</title>

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
             MESSAGE CONTENT START
             ========================= -->

        <div class="message_top home_middle">

            <div class="messenger_container">


                <!-- =========================
                     LEFT CHAT LIST
                     ========================= -->

                <div class="conversation_panel">

                    <div class="conversation_header">

                        <h2>Messages</h2>

                    </div>


                    <!-- SEARCH -->

                    <div class="conversation_search">

                        <i class="fa-solid fa-magnifying-glass"></i>

                        <input
                            type="text"
                            id="messageSearch"
                            placeholder="Search Messenger..."
                            autocomplete="off"
                        >

                    </div>


                    <!-- USER LIST -->

                    <div
                        class="conversation_list"
                        id="conversationList"
                    >

                        <?php

                        $user_sql = "SELECT
                                        id,
                                        name,
                                        account_type
                                     FROM users
                                     WHERE id != ?
                                     ORDER BY name ASC";

                        $user_stmt =
                            $conn->prepare($user_sql);

                        $user_stmt->bind_param(
                            "i",
                            $current_user_id
                        );

                        $user_stmt->execute();

                        $user_result =
                            $user_stmt->get_result();


                        while (
                            $user =
                            $user_result->fetch_assoc()
                        ) {

                            $user_id =
                                $user["id"];

                            $user_name =
                                $user["name"];

                            $account_type =
                                $user["account_type"];

                        ?>

                            <div
                                class="conversation_item"
                                data-user-id="<?php echo $user_id; ?>"
                                data-user-name="<?php echo htmlspecialchars($user_name); ?>"
                            >

                                <div class="conversation_avatar">

                                    <img
                                        src="../img/profile_pic.jpg"
                                        alt="Profile"
                                    >

                                    <span class="online_dot"></span>

                                </div>


                                <div class="conversation_details">

                                    <div class="conversation_name_row">

                                        <span class="conversation_name">

                                            <?php
                                            echo htmlspecialchars(
                                                $user_name
                                            );
                                            ?>

                                        </span>

                                    </div>


                                    <div class="conversation_preview">

                                        <span class="account_type">

                                            <?php
                                            echo htmlspecialchars(
                                                ucfirst(
                                                    $account_type
                                                )
                                            );
                                            ?>

                                        </span>

                                    </div>

                                </div>

                            </div>

                        <?php

                        }

                        ?>

                    </div>

                </div>


                <!-- =========================
                     RIGHT CHAT PANEL
                     ========================= -->

                <div class="chat_panel">


                    <!-- CHAT HEADER -->

                    <div class="chat_header">

                        <div class="chat_header_user">

                            <div class="chat_header_avatar">

                                <img
                                    src="../img/profile_pic.jpg"
                                    alt="Profile"
                                >

                                <span
                                    class="online_dot"
                                    id="chatOnlineDot"
                                ></span>

                            </div>


                            <div class="chat_header_info">

                                <h3 id="activeUserName">
                                    Select a conversation
                                </h3>

                                <span
                                    id="activeUserStatus"
                                    class="chat_status"
                                ></span>

                            </div>

                        </div>

                    </div>


                    <!-- MESSAGES -->

                    <div
                        class="messages_area"
                        id="messagesArea"
                    >

                        <div class="empty_chat">

                            <div class="empty_chat_icon">

                                <i class="fa-regular fa-comments"></i>

                            </div>

                            <h3>Your Messages</h3>

                            <p>
                                Select someone from the left
                                to start a conversation.
                            </p>

                        </div>

                    </div>


                    <!-- MESSAGE INPUT -->

                    <div
                        class="chat_input_bar"
                        id="chatInputBar"
                    >

                        <label
                            class="attach_btn"
                            title="Attach File"
                        >

                            <i class="fa-solid fa-paperclip"></i>

                            <input
                                type="file"
                                id="messageFile"
                                style="display:none;"
                            >

                        </label>


                        <input
                            type="text"
                            placeholder="Type a message..."
                            class="chat_input"
                            id="chatInput"
                            autocomplete="off"
                            disabled
                        >


                        <button
                            class="send_btn"
                            id="sendMessageBtn"
                            type="button"
                            disabled
                        >
Send
                            <i class="fa-solid fa-paper-plane"></i>

                        </button>

                    </div>

                </div>

            </div>

        </div>


        <!-- =========================
              MESSAGE CONTENT END
             ========================= -->


        <div id="right-sidebar"></div>

    </main>


    <script src="script.js"></script>


    <!-- Current logged-in user ID for message JS -->

    <script>

        window.currentUserId =
            <?php echo (int)$current_user_id; ?>;

    </script>


</body>

</html>