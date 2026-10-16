
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Notifications | UIU Social Media</title>

    <link rel="stylesheet" href="style.css">

    <style>
        /* ==========================================
           NOTIFICATION PAGE
        ========================================== */

        .notifications {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .notification1 {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 15px;
            border-radius: 10px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            transition: background 0.2s ease;
        }

        .notification1:hover {
            background: #f8fafc;
        }

        .notification1.unread {
            background: #eef5ff;
            border-left: 4px solid #2563eb;
        }

        .notification-icon {
            width: 38px;
            height: 38px;
            flex-shrink: 0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e8eefc;
            font-size: 18px;
        }

        .notification_time {
            flex: 1;
            min-width: 0;
        }

        .notifiy {
            margin: 0;
            color: #1f2937;
            font-size: 14px;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .notify_time {
            margin: 6px 0 0;
            color: #6b7280;
            font-size: 12px;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }

        .notification-header h2 {
            margin: 0;
            font-size: 22px;
        }

        .refresh-notifications-btn {
            border: none;
            border-radius: 7px;
            padding: 8px 12px;
            background: #2563eb;
            color: white;
            cursor: pointer;
            font-size: 13px;
        }

        .refresh-notifications-btn:hover {
            background: #1d4ed8;
        }

        .notification-status {
            padding: 20px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }

        @media (max-width: 600px) {
            .notification-header h2 {
                font-size: 18px;
            }

            .notification1 {
                padding: 12px;
            }
        }
    </style>
</head>

<body>

    <!-- ==========================================
         HEADER
    ========================================== -->

    <div id="header"></div>


    <!-- ==========================================
         MAIN CONTENT
    ========================================== -->

    <main class="home_main">

        <!-- LEFT SIDEBAR -->
        <div id="left-sidebar"></div>


        <!-- ==========================================
             MIDDLE CONTENT
        ========================================== -->

        <div class="notification_main">

            <div class="notification_middle">

                <div class="notification-header">

                    <h2>Notifications</h2>

                    <button
                        type="button"
                        class="refresh-notifications-btn"
                        id="refresh-notifications-btn">

                        Refresh

                    </button>

                </div>


                <!-- NOTIFICATIONS WILL LOAD HERE -->

                <div
                    class="notifications"
                    id="notifications-container">

                    <p class="notification-status">
                        Loading notifications...
                    </p>

                </div>

            </div>

        </div>


        <!-- RIGHT SIDEBAR -->

        <div id="right-sidebar"></div>

    </main>


    <!-- ==========================================
         MAIN PROJECT JAVASCRIPT
    ========================================== -->

    <script src="script.js"></script>


    <!-- ==========================================
         NOTIFICATION JAVASCRIPT
    ========================================== -->

    <script>

        // ==========================================
        // NOTIFICATION ELEMENTS
        // ==========================================

        const notificationsContainer =
            document.getElementById("notifications-container");

        const refreshNotificationsButton =
            document.getElementById("refresh-notifications-btn");


        // ==========================================
        // NOTIFICATION ICON
        // ==========================================

        function getNotificationIcon(type) {

            switch (type) {

                case "like":
                    return "❤️";

                case "comment":
                    return "💬";

                case "group":
                case "group_join":
                    return "👥";

                case "follow":
                    return "👤";

                default:
                    return "🔔";

            }

        }


        // ==========================================
        // FORMAT DATE
        // ==========================================

        function formatNotificationDate(dateString) {

            if (!dateString) {
                return "";
            }

            const date = new Date(
                dateString.replace(" ", "T")
            );

            if (Number.isNaN(date.getTime())) {
                return dateString;
            }

            return date.toLocaleString([], {
                dateStyle: "medium",
                timeStyle: "short"
            });

        }


        // ==========================================
        // LOAD NOTIFICATIONS
        // ==========================================

        async function loadNotifications() {

            if (!notificationsContainer) {
                return;
            }

            notificationsContainer.innerHTML = `
                <p class="notification-status">
                    Loading notifications...
                </p>
            `;

            try {

                const response = await fetch(
                    "load_notifications.php",
                    {
                        method: "GET",
                        credentials: "same-origin",
                        cache: "no-store"
                    }
                );


                if (!response.ok) {
                    throw new Error(
                        "HTTP error: " + response.status
                    );
                }


                const data = await response.json();


                if (!data.success) {

                    notificationsContainer.innerHTML = `
                        <p class="notification-status">
                            ${data.message || "Unable to load notifications."}
                        </p>
                    `;

                    return;
                }


                const notifications = Array.isArray(
                    data.notifications
                )
                    ? data.notifications
                    : [];


                if (notifications.length === 0) {

                    notificationsContainer.innerHTML = `
                        <p class="notification-status">
                            No notifications yet.
                        </p>
                    `;

                    return;
                }


                notificationsContainer.innerHTML = "";


                notifications.forEach(notification => {

                    const notificationItem =
                        document.createElement("div");

                    notificationItem.className =
                        "notification1";


                    // Unread notification

                    if (
                        Number(notification.is_read) === 0
                    ) {

                        notificationItem.classList.add(
                            "unread"
                        );

                    }


                    // Icon

                    const icon =
                        document.createElement("div");

                    icon.className =
                        "notification-icon";

                    icon.textContent =
                        getNotificationIcon(
                            notification.type
                        );


                    // Notification content

                    const content =
                        document.createElement("div");

                    content.className =
                        "notification_time";


                    const message =
                        document.createElement("p");

                    message.className =
                        "notifiy";

                    message.textContent =
                        notification.message || "";


                    const time =
                        document.createElement("p");

                    time.className =
                        "notify_time";

                    time.textContent =
                        formatNotificationDate(
                            notification.created_at
                        );


                    content.appendChild(message);

                    content.appendChild(time);


                    notificationItem.appendChild(icon);

                    notificationItem.appendChild(content);


                    notificationsContainer.appendChild(
                        notificationItem
                    );

                });

            } catch (error) {

                console.error(
                    "Notification loading error:",
                    error
                );


                notificationsContainer.innerHTML = `
                    <p class="notification-status">
                        Something went wrong while loading notifications.
                    </p>
                `;

            }

        }


        // ==========================================
        // REFRESH BUTTON
        // ==========================================

        if (refreshNotificationsButton) {

            refreshNotificationsButton.addEventListener(
                "click",
                loadNotifications
            );

        }


        // ==========================================
        // INITIAL LOAD
        // ==========================================

        loadNotifications();


        // ==========================================
        // AUTO REFRESH EVERY 30 SECONDS
        // ==========================================

        setInterval(
            loadNotifications,
            30000
        );

    </script>

</body>

</html>