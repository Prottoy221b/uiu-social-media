// ============================================================
// LOAD HEADER, LEFT SIDEBAR AND RIGHT SIDEBAR
// ============================================================

function loadSharedComponents() {

    const header = document.querySelector("#header");
    const left = document.querySelector("#left-sidebar");
    const right = document.querySelector("#right-sidebar");

    const headerPromise = header
        ? fetch("header.html")
            .then(function (response) {
                return response.text();
            })
            .then(function (data) {
                header.innerHTML = data;
            })
        : Promise.resolve();

    const leftPromise = left
        ? fetch("left-sidebar.html")
            .then(function (response) {
                return response.text();
            })
            .then(function (data) {
                left.innerHTML = data;
            })
        : Promise.resolve();

    const rightPromise = right
        ? fetch("right-sidebar.html")
            .then(function (response) {
                return response.text();
            })
            .then(function (data) {
                right.innerHTML = data;

                if (typeof loadPeopleMayKnow === "function") {
                    loadPeopleMayKnow();
                }
            })
        : Promise.resolve();

    return Promise.all([
        headerPromise,
        leftPromise,
        rightPromise
    ]);
}


// ============================================================
// SESSION / CURRENT USER / ONLINE STATUS
// ============================================================

function escapeHtml(value) {
  const div = document.createElement("div");
  div.textContent = value ?? "";
  return div.innerHTML;
}

function loadCurrentUser() {
  fetch("session_api.php", { credentials: "same-origin" })
    .then(function (response) {
      if (response.status === 401) {
        window.location.href = "login.html";
        return null;
      }
      return response.json();
    })
    .then(function (data) {
      if (!data || !data.logged_in) return;

      const user = data.user;

      // Header
      const headerName = document.querySelector("#header-user-name");
      const headerType = document.querySelector("#header-user-type");
      if (headerName) headerName.textContent = user.name;
      if (headerType) headerType.textContent = user.account_type;

      // Left sidebar
      const sidebarName = document.querySelector("#sidebar-user-name");
      const sidebarType = document.querySelector("#sidebar-user-type");
      if (sidebarName) sidebarName.textContent = user.name;
      if (sidebarType) sidebarType.textContent = user.account_type;

      // Home post composer
      document.querySelectorAll(".post_input input, .post_text").forEach(function (input) {
        if (input.classList.contains("post_text")) {
          input.placeholder = "What's on your mind, " + user.name + "?";
        } else {
          input.placeholder = "What's on your mind, " + user.name + "?";
        }
      });

      // Existing hard-coded comment author
      document.querySelectorAll(".comment_author_name").forEach(function (el) {
        el.textContent = user.name;
      });
    })
    .catch(function (error) {
      console.error("Session check failed:", error);
    });
}

function sendHeartbeat() {
  fetch("heartbeat.php", {
    method: "POST",
    credentials: "same-origin",
    cache: "no-store"
  }).catch(function (error) {
    console.error("Heartbeat failed:", error);
  });
}

function loadOnlineUsers() {
  const onlineBox = document.querySelector("#online-people");
  if (!onlineBox) return;

  fetch("online_users.php", {
    credentials: "same-origin",
    cache: "no-store"
  })
    .then(function (response) {
      if (response.status === 401) {
        window.location.href = "login.html";
        return null;
      }
      return response.json();
    })
    .then(function (data) {
      if (!data || !data.logged_in) return;

      onlineBox.innerHTML = "";

      if (!data.users || data.users.length === 0) {
        onlineBox.innerHTML = "<p>No one is online right now.</p>";
        return;
      }

      data.users.forEach(function (user) {
        const people = document.createElement("div");
        people.className = "people";

        const img = document.createElement("img");
        img.src = "../img/profile_pic.jpg";
        img.alt = user.name + " profile picture";

        const name = document.createElement("p");
        name.className = "person_name";
        name.textContent = user.name;

        const status = document.createElement("span");
        status.textContent = " 🟢 Online";
        status.style.fontSize = "12px";

        people.appendChild(img);
        const wrapper = document.createElement("div");
        wrapper.appendChild(name);
        wrapper.appendChild(status);
        people.appendChild(wrapper);

        onlineBox.appendChild(people);
      });
    })
    .catch(function (error) {
      console.error("Online users request failed:", error);
    });
}
// ============================================================
// SESSION START
// ============================================================

// login.html এবং registration.html-এ session check চলবে না
const currentPage = window.location.pathname.split("/").pop().toLowerCase();

const publicPages = [
  "login.html",
  "registration.html"
];

if (!publicPages.includes(currentPage)) {

    loadSharedComponents()
        .then(function () {

            // Header and sidebar load হওয়ার পরে current user load হবে
            loadCurrentUser();

            sendHeartbeat();

            loadOnlineUsers();

        })
        .catch(function (error) {

            console.error(
                "Shared components loading failed:",
                error
            );

        });

    setInterval(function () {

        sendHeartbeat();

        loadOnlineUsers();

    }, 30000);

}

document.addEventListener("click", function (event) {

    const likeButton = event.target.closest(".action_btn.like");

    if (!likeButton) {
        return;
    }

    const post = likeButton.closest(
        ".home_middle_middle, .home_middle_bottom"
    );

    if (!post) {
        return;
    }

    const postId = post.dataset.postId;

    if (!postId) {
        console.log("Post ID not found");
        return;
    }

    const likeCount = post.querySelector(".like-count");
    const icon = likeButton.querySelector("i");

    const formData = new FormData();
    formData.append("post_id", postId);

    fetch("like.php", {
        method: "POST",
        body: formData,
        credentials: "same-origin"
    })
    .then(function (response) {
        return response.json();
    })
    .then(function (data) {

        if (!data.success) {
            alert(data.message);
            return;
        }

        likeCount.textContent = data.likes + " Likes";

        if (data.liked) {

            likeButton.classList.add("liked");
            icon.className = "fa-solid fa-thumbs-up";

        } else {

            likeButton.classList.remove("liked");
            icon.className = "fa-regular fa-thumbs-up";

        }

    })
    .catch(function (error) {

        console.error("Like error:", error);

    });

});

















function loadLikeStatus(post) {

    const postId = post.dataset.postId;

    if (!postId) {
        return;
    }

    const likeCount = post.querySelector(".like-count");
    const likeButton = post.querySelector(".action_btn.like");
    const icon = likeButton ? likeButton.querySelector("i") : null;

    if (!likeCount || !likeButton || !icon) {
        return;
    }

    fetch("load_likes.php?post_id=" + postId, {
        credentials: "same-origin"
    })
    .then(function (response) {
        return response.json();
    })
    .then(function (data) {

        if (!data.success) {
            console.log(data.message);
            return;
        }

        // Update total like count
        likeCount.textContent = data.likes + " Likes";

        // Restore user's Like status
        if (data.liked) {

            likeButton.classList.add("liked");
            icon.className = "fa-solid fa-thumbs-up";

        } else {

            likeButton.classList.remove("liked");
            icon.className = "fa-regular fa-thumbs-up";

        }

    })
    .catch(function (error) {
        console.error("Load like status error:", error);
    });

}





document.querySelectorAll(
    ".home_middle_middle[data-post-id], .home_middle_bottom[data-post-id]"
).forEach(function (post) {

    loadLikeStatus(post);

});









/* =========================
   COMMENT TOGGLE
========================= */

document.addEventListener("click", function(event) {

    const commentButton =
        event.target.closest(".action_btn.comment");

    if (!commentButton) {
        return;
    }

    const post =
        commentButton.closest(
            ".home_middle_middle, .home_middle_bottom"
        );

    if (!post) {
        return;
    }

    const commentSection =
        post.querySelector(".comment_section");

    if (!commentSection) {
        return;
    }

    if (
        commentSection.style.display === "none" ||
        commentSection.style.display === ""
    ) {

        commentSection.style.display = "block";

    } else {

        commentSection.style.display = "none";

    }

});

document.addEventListener("click", function (event) {

    const sendButton = event.target.closest(".send_comment_btn");

    if (!sendButton) {
        return;
    }

    const post = sendButton.closest(
        ".home_middle_middle, .home_middle_bottom"
    );

    if (!post) {
        return;
    }

    const postId = post.dataset.postId;

    if (!postId) {
        console.log("Post ID not found");
        return;
    }

    const commentInput = post.querySelector(".comment_input");
    const commentList = post.querySelector(".comments_list");

    const commentText = commentInput.value.trim();

    if (commentText === "") {
        alert("Please write a comment.");
        return;
    }

    const formData = new FormData();
    formData.append("post_id", postId);
    formData.append("content", commentText);

    fetch("comment.php", {
        method: "POST",
        body: formData,
        credentials: "same-origin"
    })
    .then(function (response) {
        return response.json();
    })
    .then(function (data) {

        if (!data.success) {
            alert(data.message);
            return;
        }

        const newComment = document.createElement("div");
        newComment.classList.add("comment_item");

        const profilePic = document.createElement("img");
        profilePic.src = "../img/profile_pic.jpg";
        profilePic.classList.add("comment_user_pic");

        const commentBubble = document.createElement("div");
        commentBubble.classList.add("comment_bubble");

        const authorName = document.createElement("p");
        authorName.classList.add("comment_author_name");
        authorName.textContent = data.comment.name;

        const commentTextElement = document.createElement("p");
        commentTextElement.classList.add("comment_text");
        commentTextElement.textContent = data.comment.content;

        commentBubble.appendChild(authorName);
        commentBubble.appendChild(commentTextElement);

        newComment.appendChild(profilePic);
        newComment.appendChild(commentBubble);

        commentList.appendChild(newComment);

        const commentCount = post.querySelector(".comment-count");

        if (commentCount) {
            commentCount.textContent =
                data.comment_count + " Comments";
        }

        commentInput.value = "";

    })
    .catch(function (error) {
        console.error("Comment error:", error);
    });

});













function loadComments(post) {

    const postId = post.dataset.postId;

    if (!postId) {
        return;
    }

    const commentList = post.querySelector(".comments_list");

    if (!commentList) {
        return;
    }

    fetch("load_comments.php?post_id=" + postId, {
        credentials: "same-origin"
    })
    .then(function (response) {
        return response.json();
    })
    .then(function (data) {

        if (!data.success) {
            console.log(data.message);
            return;
        }

        commentList.innerHTML = "";

        data.comments.forEach(function (comment) {

            const newComment = document.createElement("div");
            newComment.classList.add("comment_item");

            const profilePic = document.createElement("img");
            profilePic.src = "../img/profile_pic.jpg";
            profilePic.classList.add("comment_user_pic");

            const commentBubble = document.createElement("div");
            commentBubble.classList.add("comment_bubble");

            const authorName = document.createElement("p");
            authorName.classList.add("comment_author_name");
            authorName.textContent = comment.name;

            const commentText = document.createElement("p");
            commentText.classList.add("comment_text");
            commentText.textContent = comment.content;

            commentBubble.appendChild(authorName);
            commentBubble.appendChild(commentText);

            newComment.appendChild(profilePic);
            newComment.appendChild(commentBubble);

            commentList.appendChild(newComment);
        });


        const commentCount = post.querySelector(".comment-count");

if (commentCount) {
    commentCount.textContent =
        data.comments.length + " Comments";
}   

    })
    .catch(function (error) {
        console.error("Load comments error:", error);
    });

}









document.querySelectorAll(
    ".home_middle_middle[data-post-id], .home_middle_bottom[data-post-id]"
).forEach(function (post) {

    loadComments(post);

});









/* =========================
   EVENT GOING BUTTON
========================= */

document.addEventListener("click", function(event) {

    const goingButton =
        event.target.closest(".going_btn");

    if (!goingButton) {
        return;
    }

    if (goingButton.classList.contains("active")) {

        goingButton.classList.remove("active");

    } else {

        goingButton.classList.add("active");

    }

});

const createEventButton = document.querySelector(".create_event_btn");
if (createEventButton) {
  const eventForm = document.querySelector(".event_form");
  createEventButton.addEventListener("click", function () {
    if (eventForm.style.display === "flex") {
      eventForm.style.display = "none";
    } else {
      eventForm.style.display = "flex";
    }
  });
}

const submitEventButton = document.querySelector(".submit_event_btn");

if (submitEventButton)
  submitEventButton.addEventListener("click", function () {
    
    const eventTitleInput = document.querySelector(".event_title_input");
    const eventDateInput = document.querySelector(".event_date_input");
    const eventTimeInput = document.querySelector(".event_time_input");
    const eventLocationInput = document.querySelector(".event_location_input");
    const eventDescriptionInput =
    document.querySelector(".event_description_input");
    
    const eventFormData = new FormData();

eventFormData.append("title", eventTitleInput.value.trim());
eventFormData.append("event_date", eventDateInput.value);
eventFormData.append("event_time", eventTimeInput.value);
eventFormData.append("location", eventLocationInput.value.trim());
eventFormData.append(
    "description",
    eventDescriptionInput.value.trim()
);
fetch("create_event.php", {
    method: "POST",
    body: eventFormData,
    credentials: "same-origin"
})
.then(function(response) {
    return response.json();
})
.then(function(data) {

    if (!data.success) {

        alert(data.message);

        return;
    }

    alert("Event created successfully!");

    document.querySelector(".event_form").style.display = "none";

    loadEvents();

})
.catch(function(error) {

    console.error("Create event error:", error);

    alert("Something went wrong.");

});
    
    document.querySelector(".event_form").style.display = "none";
    eventTimeInput.value = "";
    eventDateInput.value = "";
    eventTitleInput.value = "";
    eventLocationInput.value = "";
    eventDescriptionInput.value = "";
  });

const follows = document.querySelectorAll(".follow");
follows.forEach(function (follow) {
  follow.addEventListener("click", function () {
    if (this.classList.contains("active")) {
      this.classList.remove("active");
      this.textContent = "+Follow";
    } else {
      this.classList.add("active");
      this.textContent = "Followed";
    }
  });
});

const joins = document.querySelectorAll(".join");
joins.forEach(function (join) {
  join.addEventListener("click", function () {
    if (this.classList.contains("active")) {
      this.classList.remove("active");
      this.textContent = "+Join";
    } else {
      this.classList.add("active");
      this.textContent = "Joined";
    }
  });
});









const postInput = document.querySelector(".post_input");

if (postInput) {

    const postCreateArea = document.querySelector(".post_create_area");

    postInput.addEventListener("click", function () {

        postCreateArea.style.display = "flex";
        postInput.style.display = "none";

    });


    const createPostButton = document.querySelector(".create_post_btn");

    if (createPostButton) {

        createPostButton.addEventListener("click", function () {

            const postText = document.querySelector(".post_text");

            const content = postText.value.trim();

            if (content === "") {
                alert("Please write something.");
                return;
            }


            const formData = new FormData();

            formData.append("content", content);


            fetch("create_post.php", {
                method: "POST",
                body: formData,
                credentials: "same-origin"
            })

            .then(function (response) {
                return response.json();
            })

            .then(function (data) {

                if (!data.success) {
                    alert(data.message);
                    return;
                }


                const postTemplate =
                    document.querySelector(".post_template");

                const newPost =
                    postTemplate.cloneNode(true);


                /* Remove template class */
                newPost.classList.remove("post_template");


                /* Add database Post ID */
                newPost.setAttribute(
                    "data-post-id",
                    data.post.id
                );


                /* Set author name */
                const authorName =
                    newPost.querySelector(".post_author_name");

                if (authorName) {
                    authorName.textContent = data.post.name;
                }


                /* Set post content */
                const newPostText =
                    newPost.querySelector(".home_middle_posts p");

                if (newPostText) {
                    newPostText.textContent =
                        data.post.content;
                }


                /* Reset like count */
                const newLikeCount =
                    newPost.querySelector(".like-count");

                if (newLikeCount) {
                    newLikeCount.textContent = "0 Likes";
                }


                /* Reset comment count */
                const newCommentCount =
                    newPost.querySelector(".comment-count");

                if (newCommentCount) {
                    newCommentCount.textContent =
                        "0 Comments";
                }


                /* Clear old comments */
                const commentList =
                    newPost.querySelector(".comments_list");

                if (commentList) {
                    commentList.innerHTML = "";
                }


                /* Add post to feed */
                const postsFeed =
                    document.querySelector(".posts_feed");

                postsFeed.prepend(newPost);


                /* Close composer */
                postCreateArea.style.display = "none";
                postInput.style.display = "flex";

                postText.value = "";

            })

            .catch(function (error) {

                console.error(
                    "Create post error:",
                    error
                );

            });

        });

    }

}






//Registration Form

const registrationForm = document.querySelector(".registration_form");
if (registrationForm) {
  registrationForm.addEventListener("submit", function (event) {
    event.preventDefault();
    console.log("SUBMIT RUNNING");
    const nameInput = document.querySelector(".name_input");
    const departmentInput = document.querySelector(".department_input");
    const emailInput = document.querySelector(".email_input");
    const passwordInput = document.querySelector(".password_input");

    const accountType = document.querySelector(
      'input[name="accountType"]:checked',
    );

    const selectedAccountType = accountType.value;

    if (
      nameInput.value === "" ||
      departmentInput.value === "" ||
      emailInput.value === "" ||
      passwordInput.value == ""
    ) {
      alert("Please fill in all fields");
      return;
    }
    const emailPattern = /^[^\s@]+@[a-zA-Z0-9]+\.uiu\.ac\.bd$/;
    if (!emailPattern.test(emailInput.value)) {
      alert("Please enter a valid UIU email address");
      return;
    }

    if (passwordInput.value.length < 8) {
      alert("Password must be at least 8 characters");
      return;
    }

    const name = nameInput.value;
    const department = departmentInput.value;
    const email = emailInput.value;
    const password = passwordInput.value;
    const type = accountType.value;
    fetch("register.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body:
        "name=" +
        encodeURIComponent(name) +
        "&department=" +
        encodeURIComponent(department) +
        "&email=" +
        encodeURIComponent(email) +
        "&password=" +
        encodeURIComponent(password) +
        "&account_type=" +
        encodeURIComponent(type),
    })
      .then(function (response) {
        return response.text();
      })
      .then(function (data) {
        data = data.trim();
        console.log(data);
        if (data === "Registration successful") {
          alert("Registration Successful");
          nameInput.value = "";
          departmentInput.value = "";
          emailInput.value = "";
          passwordInput.value = "";
        } else if (data === "Email already registered") {
          alert("Email already registered");
        }
      });
  });
}


/*
=====================================================
              LOGIN AUTHENTICATION
=====================================================
*/

const loginButton = document.querySelector(".signIn");


if (loginButton) {

    loginButton.addEventListener("click", function () {


        const emailInput =
            document.querySelector(".email_input");


        const passwordInput =
            document.querySelector(".password_input");


        const email =
            emailInput.value.trim();


        const password =
            passwordInput.value;


        if (email === "" || password === "") {

            alert("Please enter email and password");

            return;

        }


        loginButton.disabled = true;


        fetch("login.php", {

            method: "POST",

            headers: {

                "Content-Type":
                    "application/x-www-form-urlencoded"

            },

            body:

                "email=" +
                encodeURIComponent(email) +

                "&password=" +
                encodeURIComponent(password)

        })


        .then(function(response) {

            return response.text();

        })


        .then(function(data) {


            data = data.trim();


            console.log("Login response:", data);


            const loginData =
                data.split("|");


            const loginStatus =
                loginData[0];


            const userRole =
                (loginData[1] || "user").trim().toLowerCase();



            if (loginStatus === "Login successful") {


                if (userRole === "admin") {


                    window.location.href =
                        "admin-dashboard.php";


                } else {


                    window.location.href =
                        "home.php";


                }


            }


            else if (loginStatus === "Email not found") {


                alert("Email not found");

                loginButton.disabled = false;


            }


            else if (loginStatus === "Wrong password") {


                alert("Wrong password");

                loginButton.disabled = false;


            }


            else {


                alert(data);

                loginButton.disabled = false;


            }


        })


        .catch(function(error) {


            console.error(
                "Login error:",
                error
            );


            alert(
                "Something went wrong. Please try again."
            );


            loginButton.disabled = false;


        });


    });

}






/* =========================================================
                    MESSENGER SYSTEM
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const conversationList =
        document.getElementById("conversationList");

    const messagesArea =
        document.getElementById("messagesArea");

    const activeUserName =
        document.getElementById("activeUserName");

    const activeUserStatus =
        document.getElementById("activeUserStatus");

    const chatInput =
        document.getElementById("chatInput");

    const sendMessageBtn =
        document.getElementById("sendMessageBtn");

    const messageSearch =
        document.getElementById("messageSearch");


    /*
    ------------------------------------------
            Make sure this is Messages page
    ------------------------------------------
    */

    if (
        !conversationList ||
        !messagesArea ||
        !chatInput ||
        !sendMessageBtn
    ) {

        return;

    }


    let selectedReceiverId = null;

    let messageRefreshInterval = null;


    /*
    ==========================================
            USER SELECTION
    ==========================================
    */

    conversationList.addEventListener(
        "click",
        function (event) {

            const conversation =
                event.target.closest(
                    ".conversation_item"
                );


            if (!conversation) {

                return;

            }


            selectedReceiverId =
                conversation.dataset.userId;


            const userName =
                conversation.dataset.userName;


            /*
            Active class
            */

            document
                .querySelectorAll(
                    ".conversation_item"
                )
                .forEach(function (item) {

                    item.classList.remove(
                        "active"
                    );

                });


            conversation.classList.add(
                "active"
            );


            /*
            Header
            */

            activeUserName.textContent =
                userName;


            activeUserStatus.textContent =
                "🟢 Online";


            /*
            Enable input
            */

            chatInput.disabled = false;

            sendMessageBtn.disabled = false;


            /*
            Store receiver
            */

            messagesArea.dataset.receiverId =
                selectedReceiverId;


            /*
            Load conversation
            */

            loadMessengerMessages(
                selectedReceiverId
            );


            /*
            Start auto refresh
            */

            startMessengerRefresh(
                selectedReceiverId
            );

        }
    );


    /*
    ==========================================
                SEND MESSAGE
    ==========================================
    */

    sendMessageBtn.addEventListener(
        "click",
        function () {

            sendMessengerMessage();

        }
    );


    /*
    ==========================================
                ENTER TO SEND
    ==========================================
    */

    chatInput.addEventListener(
        "keydown",
        function (event) {

            if (
                event.key === "Enter"
                &&
                !event.shiftKey
            ) {

                event.preventDefault();

                sendMessengerMessage();

            }

        }
    );


    /*
    ==========================================
                SEARCH USERS
    ==========================================
    */

    messageSearch.addEventListener(
        "input",
        function () {

            const search =
                this.value
                .trim()
                .toLowerCase();


            document
                .querySelectorAll(
                    ".conversation_item"
                )
                .forEach(function (item) {

                    const name =
                        item.dataset.userName
                        .toLowerCase();


                    if (
                        name.includes(search)
                    ) {

                        item.style.display =
                            "flex";

                    } else {

                        item.style.display =
                            "none";

                    }

                });

        }
    );


    /*
    ==========================================
                SEND FUNCTION
    ==========================================
    */

    function sendMessengerMessage() {

        if (!selectedReceiverId) {

            alert(
                "Please select a user first."
            );

            return;

        }


        const message =
            chatInput.value.trim();


        if (message === "") {

            return;

        }


        const formData =
            new FormData();


        formData.append(
            "receiver_id",
            selectedReceiverId
        );


        formData.append(
            "message",
            message
        );


        sendMessageBtn.disabled =
            true;


        fetch(
            "send_message.php",
            {
                method: "POST",
                body: formData,
                credentials: "same-origin"
            }
        )

        .then(function (response) {

            return response.json();

        })

        .then(function (data) {

            if (!data.success) {

                alert(
                    data.message
                );

                return;

            }


            /*
            Reload from database
            */

            loadMessengerMessages(
                selectedReceiverId
            );


            /*
            Clear input
            */

            chatInput.value = "";

        })

        .catch(function (error) {

            console.error(
                "Send message error:",
                error
            );

        })

        .finally(function () {

            sendMessageBtn.disabled =
                false;

            chatInput.focus();

        });

    }


    /*
    ==========================================
                LOAD MESSAGES
    ==========================================
    */

    function loadMessengerMessages(
        receiverId
    ) {

        fetch(
            "load_messages.php?receiver_id="
            +
            encodeURIComponent(
                receiverId
            ),
            {
                credentials: "same-origin"
            }
        )

        .then(function (response) {

            return response.json();

        })

        .then(function (data) {

            if (!data.success) {

                messagesArea.innerHTML = `
                    <div class="no_chat_message">
                        ${data.message}
                    </div>
                `;

                return;

            }


            messagesArea.innerHTML = "";


            if (
                data.messages.length === 0
            ) {

                messagesArea.innerHTML = `
                    <div class="empty_chat">

                        <div class="empty_chat_icon">

                            <i class="fa-regular fa-comments"></i>

                        </div>

                        <h3>
                            Start a conversation
                        </h3>

                        <p>
                            Send a message to start chatting.
                        </p>

                    </div>
                `;

                return;

            }


            data.messages.forEach(
                function (msg) {

                    const messageDiv =
                        document.createElement(
                            "div"
                        );


                    /*
                    Current user
                    */

                    if (
                        Number(
                            msg.sender_id
                        )
                        ===
                        Number(
                            window.currentUserId
                        )
                    ) {

                        messageDiv.classList.add(
                            "message_outgoing"
                        );

                    }

                    /*
                    Other user
                    */

                    else {

                        messageDiv.classList.add(
                            "message_incoming"
                        );


                        const senderName =
                            document.createElement(
                                "div"
                            );


                        senderName.classList.add(
                            "msg_sender_name"
                        );


                        senderName.textContent =
                            msg.sender_name;


                        messageDiv.appendChild(
                            senderName
                        );

                    }


                    /*
                    Message bubble
                    */

                    const bubble =
                        document.createElement(
                            "div"
                        );


                    bubble.classList.add(
                        "msg_bubble"
                    );


                    bubble.textContent =
                        msg.message;


                    messageDiv.appendChild(
                        bubble
                    );


                    /*
                    Time
                    */

                    const time =
                        document.createElement(
                            "span"
                        );


                    time.classList.add(
                        "msg_time"
                    );


                    const date =
                        new Date(
                            msg.created_at.replace(
                                " ",
                                "T"
                            )
                        );


                    time.textContent =
                        date.toLocaleTimeString(
                            [],
                            {
                                hour: "numeric",
                                minute: "2-digit"
                            }
                        );


                    messageDiv.appendChild(
                        time
                    );


                    messagesArea.appendChild(
                        messageDiv
                    );

                }
            );


            /*
            Scroll bottom
            */

            messagesArea.scrollTop =
                messagesArea.scrollHeight;

        })

        .catch(function (error) {

            console.error(
                "Load messages error:",
                error
            );

        });

    }


    /*
    ==========================================
                AUTO REFRESH
    ==========================================
    */

    function startMessengerRefresh(
        receiverId
    ) {

        if (
            messageRefreshInterval
        ) {

            clearInterval(
                messageRefreshInterval
            );

        }


        messageRefreshInterval =
            setInterval(
                function () {

                    if (
                        selectedReceiverId
                        ===
                        receiverId
                    ) {

                        loadMessengerMessages(
                            receiverId
                        );

                    }

                },
                2000
            );

    }

});








//Follow




document.addEventListener("click", function(event) {

    const followButton =
        event.target.closest(".follow");

    if (!followButton) return;

    // follow logic

});





/* =========================================================
                    PEOPLE MAY KNOW
   ========================================================= */

function loadPeopleMayKnow() {

    const peopleContainer =
        document.getElementById(
            "people-may-know"
        );


    if (!peopleContainer) {

        return;

    }


    fetch(
        "load_people.php",
        {
            credentials: "same-origin"
        }
    )

    .then(function (response) {

        return response.json();

    })

    .then(function (data) {

        if (!data.success) {

            peopleContainer.innerHTML = `
                <p>
                    ${data.message}
                </p>
            `;

            return;

        }


        peopleContainer.innerHTML = "";


        data.people.forEach(
            function (person) {


                /*
                Main person container
                */

                const personDiv =
                    document.createElement(
                        "div"
                    );


                personDiv.classList.add(
                    "person"
                );


                /*
                Profile image
                */

                const image =
                    document.createElement(
                        "img"
                    );


                image.src =
                    "../img/profile_pic.jpg";


                image.alt =
                    person.name;


                /*
                Name + title
                */

                const nameTitle =
                    document.createElement(
                        "div"
                    );


                nameTitle.classList.add(
                    "name_title"
                );


                const name =
                    document.createElement(
                        "p"
                    );


                name.classList.add(
                    "person_name"
                );


                name.textContent =
                    person.name;


                const type =
                    document.createElement(
                        "p"
                    );


                if (
                    person.account_type ===
                    "faculty"
                ) {

                    type.textContent =
                        "Faculty, CSE";

                } else {

                    type.textContent =
                        "Student";

                }


                nameTitle.appendChild(
                    name
                );


                nameTitle.appendChild(
                    type
                );


                /*
                Follow button
                */

                const followButton =
                    document.createElement(
                        "button"
                    );


                followButton.classList.add(
                    "follow"
                );


                followButton.dataset.userId =
                    person.id;


                if (person.following) {

                    followButton.textContent =
                        "Following";

                    followButton.classList.add(
                        "following"
                    );

                } else {

                    followButton.textContent =
                        "+Follow";

                }


                /*
                Build person card
                */

                personDiv.appendChild(
                    image
                );


                personDiv.appendChild(
                    nameTitle
                );


                personDiv.appendChild(
                    followButton
                );


                peopleContainer.appendChild(
                    personDiv
                );

            }
        );

    })

    .catch(function (error) {

        console.error(
            "Load people error:",
            error
        );

    });

}




/* =========================================================
                    FOLLOW / UNFOLLOW
   ========================================================= */

document.addEventListener(
    "click",
    function (event) {

        const followButton =
            event.target.closest(
                ".follow"
            );


        if (!followButton) {

            return;

        }


        const userId =
            followButton.dataset.userId;


        if (!userId) {

            return;

        }


        const formData =
            new FormData();


        formData.append(
            "following_id",
            userId
        );


        followButton.disabled = true;


        fetch(
            "follow.php",
            {
                method: "POST",
                body: formData,
                credentials: "same-origin"
            }
        )

        .then(function (response) {

            return response.json();

        })

        .then(function (data) {

            if (!data.success) {

                alert(
                    data.message
                );

                return;

            }


            if (data.following) {

                followButton.textContent =
                    "Following";


                followButton.classList.add(
                    "following"
                );

            } else {

                followButton.textContent =
                    "+Follow";


                followButton.classList.remove(
                    "following"
                );

            }

        })

        .catch(function (error) {

            console.error(
                "Follow error:",
                error
            );

        })

        .finally(function () {

            followButton.disabled =
                false;

        });

    }
);





/* =========================================================
                    GROUP SYSTEM
   ========================================================= */

function loadGroups(filter = "all") {

    const groupList =
        document.getElementById("groupList");

    if (!groupList) {
        return;
    }


    fetch("load_groups.php", {
        credentials: "same-origin"
    })

    .then(function(response) {

        return response.json();

    })

    .then(function(data) {

        if (!data.success) {

            groupList.innerHTML = `
                <div class="group_loading">
                    ${data.message}
                </div>
            `;

            return;
        }


        groupList.innerHTML = "";


        const filteredGroups =
            data.groups.filter(function(group) {

                if (filter === "all") {
                    return true;
                }

                return (
                    String(group.group_type)
                        .toLowerCase()
                    ===
                    filter.toLowerCase()
                );

            });


        if (filteredGroups.length === 0) {

            groupList.innerHTML = `
                <div class="group_loading">
                    No groups found.
                </div>
            `;

            return;
        }


        /*
        ==========================================
                CREATE GROUP CARDS
        ==========================================
        */

        filteredGroups.forEach(
            function(group) {

                const groupCard =
                    document.createElement("div");

                groupCard.classList.add(
                    "group_card"
                );


                groupCard.dataset.groupId = group.id;
groupCard.style.cursor = "pointer";

                /*
                Group container
                */

                const groupContent =
                    document.createElement("div");

                groupContent.classList.add(
                    "dynamic_group_content"
                );


                /*
                Image + name
                */

                const imgName =
                    document.createElement("div");

                imgName.classList.add(
                    "img_name"
                );


                const image =
                    document.createElement("img");

                image.src =
                    getGroupImage(group.name);

                image.alt =
                    group.name;


                const groupName =
                    document.createElement("p");

                groupName.textContent =
                    group.name;


                imgName.appendChild(image);

                imgName.appendChild(groupName);


                /*
                Description
                */

                const description =
                    document.createElement("p");

                description.textContent =
                    group.description || "";


                /*
                Member + Join
                */

                const memberJoin =
                    document.createElement("div");

                memberJoin.classList.add(
                    "member_join"
                );


                const memberCount =
                    document.createElement("p");

                memberCount.classList.add(
                    "group_member_count"
                );

                memberCount.textContent =
                    group.member_count
                    + " Members";


                /*
                Join button
                */
const joinButton = document.createElement("button");

joinButton.className = "join";

joinButton.dataset.groupId = group.id;

joinButton.textContent =
    group.joined ? "Joined" : "+Join";

if (group.joined) {
    joinButton.classList.add("joined");
}

                /*
                Build card
                */

                memberJoin.appendChild(
                    memberCount
                );

                memberJoin.appendChild(
                    joinButton
                );


                groupContent.appendChild(
                    imgName
                );

                groupContent.appendChild(
                    description
                );

                groupContent.appendChild(
                    memberJoin
                );


                groupCard.appendChild(
                    groupContent
                );


                groupList.appendChild(
                    groupCard
                );

            }
        );

    })

    .catch(function(error) {

        console.error(
            "Load groups error:",
            error
        );

    });

}


/*
=========================================================
                GROUP IMAGE
=========================================================
*/

function getGroupImage(groupName) {

    const name =
        String(groupName).toLowerCase();


    if (
        name.includes("cse")
        &&
        name.includes("department")
    ) {

        return "../img/CSE_grp.jpg";

    }


    if (
        name.includes("robot")
    ) {

        return "../img/robotic_grp.jpg";

    }


    if (
        name.includes("batch")
    ) {

        return "../img/batc_grp.jpg";

    }


    if (
        name.includes("data structure")
        ||
        name.includes("dsa")
    ) {

        return "../img/dsa_grp.jpg";

    }


    if (
        name.includes("photography")
    ) {

        return "../img/photography.jpg";

    }


    if (
        name.includes("eee")
        &&
        name.includes("department")
    ) {

        return "../img/electric.jpg";

    }


    /*
    Default group image
    */

    return "../img/CSE_grp.jpg";

}


/*
=========================================================
                GROUP FILTER
=========================================================
*/

document.addEventListener(
    "click",
    function(event) {

        const filterButton =
            event.target.closest(
                ".group_filter"
            );


        if (!filterButton) {
            return;
        }


        document
            .querySelectorAll(
                ".group_filter"
            )
            .forEach(function(button) {

                button.classList.remove(
                    "active"
                );

            });


        filterButton.classList.add(
            "active"
        );


        const filter =
            filterButton.dataset.filter;


        loadGroups(filter);

    }
);


/* =========================
   GROUP JOIN / LEAVE
========================= */

document.addEventListener("click", function(event) {

    const joinButton = event.target.closest(".join");

    if (!joinButton) {
        return;
    }

    const groupId = joinButton.dataset.groupId;

    if (!groupId) {
        console.error("Group ID missing");
        return;
    }

    const formData = new FormData();

    formData.append("group_id", groupId);

    joinButton.disabled = true;

    fetch("join_group.php", {
        method: "POST",
        body: formData,
        credentials: "same-origin"
    })

    .then(function(response) {
        return response.json();
    })

    .then(function(data) {

        console.log("Join response:", data);

        if (!data.success) {

            alert(data.message);

            return;
        }


        /* Update button */

        if (data.joined) {

            joinButton.textContent = "Joined";

            joinButton.classList.add("joined");

        } else {

            joinButton.textContent = "+Join";

            joinButton.classList.remove("joined");

        }


        /* Update member count */

        const groupCard =
            joinButton.closest(".group_card");

        if (groupCard) {

            const memberCount =
                groupCard.querySelector(
                    ".group_member_count"
                );

            if (memberCount) {

                memberCount.textContent =
                    data.members + " Members";

            }

        }

    })

    .catch(function(error) {

        console.error(
            "Join/Leave error:",
            error
        );

        alert(
            "Something went wrong. Please try again."
        );

    })

    .finally(function() {

        joinButton.disabled = false;

    });

});

/*
=========================================================
                INITIAL GROUP LOAD
=========================================================
*/

if (
    document.getElementById("groupList")
) {

    loadGroups("all");

}




/* =========================
   CREATE GROUP SYSTEM
========================= */

const createGroupBtn = document.getElementById("createGroupBtn");
const createGroupModal = document.getElementById("createGroupModal");
const closeGroupModal = document.getElementById("closeGroupModal");
const cancelGroupBtn = document.getElementById("cancelGroupBtn");
const createGroupForm = document.getElementById("createGroupForm");


/* Open modal */

if (createGroupBtn) {

    createGroupBtn.addEventListener("click", function () {

        createGroupModal.classList.add("show");

    });

}


/* Close modal */

function closeCreateGroupModal() {

    if (createGroupModal) {

        createGroupModal.classList.remove("show");

    }

}


/* Close buttons */

if (closeGroupModal) {

    closeGroupModal.addEventListener(
        "click",
        closeCreateGroupModal
    );

}

if (cancelGroupBtn) {

    cancelGroupBtn.addEventListener(
        "click",
        closeCreateGroupModal
    );

}


/* Close by clicking outside */

if (createGroupModal) {

    createGroupModal.addEventListener(
        "click",
        function(event) {

            if (event.target === createGroupModal) {

                closeCreateGroupModal();

            }

        }
    );

}


/* Create group */

if (createGroupForm) {

    createGroupForm.addEventListener(
        "submit",
        function(event) {

            event.preventDefault();

            const formData = new FormData(createGroupForm);

            const submitButton =
                createGroupForm.querySelector(
                    ".submit_group_btn"
                );

            submitButton.disabled = true;

            submitButton.textContent = "Creating...";


            fetch("create_group.php", {

                method: "POST",

                body: formData,

                credentials: "same-origin"

            })

            .then(function(response) {

                return response.json();

            })

            .then(function(data) {

                if (!data.success) {

                    alert(data.message);

                    return;

                }


                alert("Group created successfully!");


                /* Reset form */

                createGroupForm.reset();


                /* Close modal */

                closeCreateGroupModal();


                /* Reload groups */

                if (typeof loadGroups === "function") {

                    loadGroups("all");

                }

            })

            .catch(function(error) {

                console.error(
                    "Create group error:",
                    error
                );

                alert(
                    "Something went wrong while creating the group."
                );

            })

            .finally(function() {

                submitButton.disabled = false;

                submitButton.textContent = "Create Group";

            });

        }
    );

}




/* =========================
   GROUP DETAILS
========================= */

function loadGroupDetails() {

    if (!window.currentGroupId) {
        return;
    }


    fetch(
        "load_group.php?group_id=" +
        window.currentGroupId,
        {
            credentials: "same-origin"
        }
    )

    .then(function(response) {

        return response.json();

    })

    .then(function(data) {

        if (!data.success) {

            alert(data.message);

            return;
        }


        const group = data.group;


        document.getElementById(
            "groupName"
        ).textContent =
            group.name;


        document.getElementById(
            "groupType"
        ).textContent =
            group.group_type;


        document.getElementById(
            "groupMembers"
        ).textContent =
            group.member_count +
            " Members";


        document.getElementById(
            "groupDescription"
        ).textContent =
            group.description;


        const joinButton =
            document.getElementById(
                "groupJoinBtn"
            );


        joinButton.textContent =
            group.joined
                ? "Joined"
                : "+Join";


        if (group.joined) {

            joinButton.classList.add(
                "joined"
            );

        }


        /* MEMBERS */

        const membersList =
            document.getElementById(
                "groupMembersList"
            );


        membersList.innerHTML = "";


        if (data.members.length === 0) {

            membersList.innerHTML =
                "<p>No members yet.</p>";

            return;
        }


        data.members.forEach(
            function(member) {

                const memberDiv =
                    document.createElement(
                        "div"
                    );

                memberDiv.className =
                    "group_member";


                memberDiv.innerHTML = `

                    <img
                        src="../img/profile_pic.jpg"
                        alt="Profile">

                    <div>

                        <p class="member_name">
                            ${member.name}
                        </p>

                        <p class="member_type">
                            ${member.account_type}
                        </p>

                    </div>

                `;


                membersList.appendChild(
                    memberDiv
                );

            }
        );

    })

    .catch(function(error) {

        console.error(
            "Group details error:",
            error
        );

    });

}


/* Load group details */

if (
    document.getElementById(
        "groupName"
    )
) {

    loadGroupDetails();

}



/* =========================
   OPEN GROUP DETAILS
========================= */

document.addEventListener(
    "click",
    function(event) {

        const groupCard =
            event.target.closest(".group_card");

        if (!groupCard) {
            return;
        }

        /* Join button click করলে group page খুলবে না */

        if (event.target.closest(".join")) {
            return;
        }

        const groupId =
            groupCard.dataset.groupId;

        if (!groupId) {
            return;
        }

        window.location.href =
            "group.php?id=" + groupId;

    }
); 




/* =========================
   GROUP POSTS
========================= */

function loadGroupPosts() {

    if (!window.currentGroupId) {
        return;
    }

    const postsList =
        document.getElementById("groupPostsList");

    if (!postsList) {
        return;
    }

    fetch(
        "load_group_posts.php?group_id=" +
        window.currentGroupId,
        {
            credentials: "same-origin"
        }
    )

    .then(function(response) {
        return response.json();
    })

    .then(function(data) {

        if (!data.success) {

            postsList.innerHTML =
                "<p>" + data.message + "</p>";

            return;
        }

        postsList.innerHTML = "";


        if (data.posts.length === 0) {

            postsList.innerHTML = `
                <div class="no_group_posts">

                    <p>No posts yet.</p>

                    <span>
                        Be the first one to start a discussion.
                    </span>

                </div>
            `;

            return;
        }


        data.posts.forEach(function(post) {

            /* =========================
               POST
            ========================= */

            const postDiv =
                document.createElement("div");

            postDiv.className =
                "group_post home_middle_bottom";

            postDiv.dataset.postId =
                post.id;


            /* =========================
               HEADER
            ========================= */

            const header =
                document.createElement("div");

            header.className =
                "group_post_header";


            const profile =
                document.createElement("img");

            profile.src =
                "../img/profile_pic.jpg";

            profile.alt =
                "Profile";


            const userInfo =
                document.createElement("div");


            const name =
                document.createElement("p");

            name.className =
                "group_post_author";

            name.textContent =
                post.name;


            const time =
                document.createElement("p");

            time.className =
                "group_post_time";

            time.textContent =
                post.created_at;


            userInfo.appendChild(name);
            userInfo.appendChild(time);

            header.appendChild(profile);
            header.appendChild(userInfo);


            /* =========================
               CONTENT
            ========================= */

            const content =
                document.createElement("p");

            content.className =
                "group_post_content";

            content.textContent =
                post.content;


            /* =========================
               LIKE / COMMENT COUNT
            ========================= */

            const counter =
                document.createElement("div");

            counter.className =
                "like_comment_counter";


            const likeCount =
                document.createElement("p");

            likeCount.className =
                "like-count";

            likeCount.textContent =
                "0 Likes";


            const commentCount =
                document.createElement("p");

            commentCount.className =
                "comment-count";

            commentCount.textContent =
                "0 Comments";


            counter.appendChild(likeCount);
            counter.appendChild(commentCount);


            /* =========================
               ACTION BUTTONS
            ========================= */

            const actions =
                document.createElement("div");

            actions.className =
                "home_middle_bottom_bottom";


            const likeButton =
                document.createElement("button");

            likeButton.className =
                "action_btn like";

            likeButton.type =
                "button";

            likeButton.innerHTML = `
                <i class="fa-regular fa-thumbs-up"></i>
                <span>Like</span>
            `;


            const commentButton =
                document.createElement("button");

            commentButton.className =
                "action_btn comment";

            commentButton.type =
                "button";

            commentButton.innerHTML = `
                <i class="fa-regular fa-comment"></i>
                <span>Comment</span>
            `;


            actions.appendChild(likeButton);
            actions.appendChild(commentButton);


            /* =========================
               COMMENT SECTION
            ========================= */

            const commentSection =
                document.createElement("div");

            commentSection.className =
                "comment_section";


            const commentsList =
                document.createElement("div");

            commentsList.className =
                "comments_list";


            const inputBox =
                document.createElement("div");

            inputBox.className =
                "comment_input_box";


            const userPic =
                document.createElement("img");

            userPic.src =
                "../img/profile_pic.jpg";

            userPic.className =
                "comment_user_pic";


            const fieldWrapper =
                document.createElement("div");

            fieldWrapper.className =
                "comment_field_wrapper";


            const input =
                document.createElement("input");

            input.type =
                "text";

            input.className =
                "comment_input";

            input.placeholder =
                "Write a comment...";


            const sendButton =
                document.createElement("button");

            sendButton.type =
                "button";

            sendButton.className =
                "send_comment_btn";

            sendButton.textContent =
                "Post";


            fieldWrapper.appendChild(input);
            fieldWrapper.appendChild(sendButton);

            inputBox.appendChild(userPic);
            inputBox.appendChild(fieldWrapper);


            commentSection.appendChild(commentsList);
            commentSection.appendChild(inputBox);


            /* =========================
               BUILD POST
            ========================= */

            postDiv.appendChild(header);

            postDiv.appendChild(content);

            postDiv.appendChild(counter);

            postDiv.appendChild(actions);

            postDiv.appendChild(commentSection);


            postsList.appendChild(postDiv);


            /* =========================
               LOAD LIKE STATUS
            ========================= */

            loadLikeStatus(postDiv);


            /* =========================
               LOAD COMMENTS
            ========================= */

            loadComments(postDiv);

        });

    })

    .catch(function(error) {

        console.error(
            "Group posts error:",
            error
        );

        postsList.innerHTML =
            "<p>Failed to load posts.</p>";

    });

}


/* =========================
   CREATE GROUP POST
========================= */

const createGroupPostBtn =
    document.getElementById(
        "createGroupPostBtn"
    );


const groupPostInput =
    document.getElementById(
        "groupPostInput"
    );


if (
    createGroupPostBtn &&
    groupPostInput
) {

    createGroupPostBtn.addEventListener(
        "click",
        function() {

            const content =
                groupPostInput.value.trim();


            if (content === "") {

                alert(
                    "Please write something first."
                );

                return;
            }


            const formData =
                new FormData();


            formData.append(
                "group_id",
                window.currentGroupId
            );


            formData.append(
                "content",
                content
            );


            createGroupPostBtn.disabled =
                true;


            createGroupPostBtn.textContent =
                "Posting...";


            fetch(
                "create_group_post.php",
                {
                    method: "POST",
                    body: formData,
                    credentials: "same-origin"
                }
            )

            .then(function(response) {

                return response.json();

            })

            .then(function(data) {

                if (!data.success) {

                    alert(data.message);

                    return;
                }


                groupPostInput.value = "";


                loadGroupPosts();

            })

            .catch(function(error) {

                console.error(
                    "Create group post error:",
                    error
                );

                alert(
                    "Something went wrong."
                );

            })

            .finally(function() {

                createGroupPostBtn.disabled =
                    false;

                createGroupPostBtn.textContent =
                    "Post";

            });

        }
    );

}


/* =========================
   INITIAL LOAD
========================= */

if (
    document.getElementById(
        "groupPostsList"
    )
) {

    loadGroupPosts();

}




/* =========================
   LOAD EVENTS
========================= */

function loadEvents() {

    const eventCards =
        document.getElementById("eventCards");

    if (!eventCards) {
        return;
    }

    fetch("load_events.php", {
        credentials: "same-origin"
    })

    .then(function(response) {
        return response.json();
    })

    .then(function(data) {

        if (!data.success) {

            eventCards.innerHTML =
                "<p>" + data.message + "</p>";

            return;
        }

        eventCards.innerHTML = "";

        if (data.events.length === 0) {

            eventCards.innerHTML =
                "<p>No events available.</p>";

            return;
        }


        data.events.forEach(function(event) {

            const card =
                document.createElement("div");

            card.className =
                "event_card1";


            /* Banner */

            const banner =
                document.createElement("div");

            banner.className =
                "event_banner";


            const image =
                document.createElement("img");

            image.src =
                "../img/image 58.jpg";

            image.alt =
                event.title;


            banner.appendChild(image);


            /* Details */

            const details =
                document.createElement("div");

            details.className =
                "event_details";


            /* Top Row */

            const topRow =
                document.createElement("div");

            topRow.className =
                "event_top_row";


            const badge =
                document.createElement("span");

            badge.className =
                "badge";

            badge.textContent =
                "EVENT";


            const goingButton =
                document.createElement("button");

            goingButton.className =
                "going_btn";

            goingButton.textContent =
                "Going";


            topRow.appendChild(badge);
            topRow.appendChild(goingButton);


            /* Title */

            const title =
                document.createElement("h3");

            title.className =
                "event_title";

            title.textContent =
                event.title;


            /* Date */

            const dateInfo =
                document.createElement("p");

            dateInfo.className =
                "event_info";

            dateInfo.textContent =
                "📅 " +
                event.event_date +
                " - " +
                event.event_time;


            /* Location */

            const locationInfo =
                document.createElement("p");

            locationInfo.className =
                "event_info";

            locationInfo.textContent =
                "🌐 " +
                event.location;


            /* Creator */
            const description =
    document.createElement("p");

description.className =
    "event_description";

description.textContent =
    event.description || "No description available.";

            const subtext =
                document.createElement("p");

            subtext.className =
                "event_subtext";

            subtext.textContent =
                "By " +
                (event.creator_name || "Unknown") +
                " · 0 attending";


            details.appendChild(topRow);
            details.appendChild(title);
            details.appendChild(dateInfo);
            details.appendChild(locationInfo);
            details.appendChild(description);
            details.appendChild(subtext);


            card.appendChild(banner);
            card.appendChild(details);


            eventCards.appendChild(card);

        });

    })

    .catch(function(error) {

        console.error(
            "Load events error:",
            error
        );

        eventCards.innerHTML =
            "<p>Failed to load events.</p>";

    });

}


/* =========================
   INITIALIZE EVENTS
========================= */

if (document.getElementById("eventCards")) {

    loadEvents();

}