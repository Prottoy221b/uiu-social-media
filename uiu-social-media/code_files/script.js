fetch("left-sidebar.html")
    .then(response => response.text())
    .then(data => {
        document.getElementById("left-sidebar").innerHTML = data;
    });

fetch("right-sidebar.html")
    .then(response => response.text())
    .then(data => {
        document.getElementById("right-sidebar").innerHTML = data;
    });

    fetch("header.html")
    .then(response=> response.text())
    .then(data=> {
        document.getElementById("header").innerHTML = data;
    });



    // like  (Toggle & Counter)
const allLikeButtons = document.querySelectorAll('.action_btn.like');

for (let i = 0; i < allLikeButtons.length; i++) {
  allLikeButtons[i].addEventListener('click', function () {
    
    const post = this.closest('.home_middle_bottom') || this.closest('.home_middle_middle');
    
    const likeText = post.querySelector('.like-count');
    const icon = this.querySelector('i');
    
    let number = parseInt(likeText.textContent);

    if (this.classList.contains('liked')) {

      this.classList.remove('liked');
      number = number - 1;
      icon.className = 'fa-regular fa-thumbs-up';
    } else {

      this.classList.add('liked');
      number = number + 1;
      icon.className = 'fa-solid fa-thumbs-up';
    }

    likeText.textContent = number + ' Likes';
    
  });
}


// comment section open and close

const commentButtons = document.querySelectorAll('.action_btn.comment');

commentButtons.forEach((button) => {
  button.addEventListener('click', function () {
    const post = this.closest('.home_middle_middle, .home_middle_bottom');
    const commentSection = post.querySelector('.comment_section');

    // display: flex বা none টগল করা
    if (
      commentSection.style.display === 'none' ||
      !commentSection.style.display
    ) {
      commentSection.style.display = 'block';
    } else {
      commentSection.style.display = 'none';
    }
  });
});

// add post 
const sendCommentBtns = document.querySelectorAll('.send_comment_btn');

sendCommentBtns.forEach((btn) => {
  btn.addEventListener('click', function () {
    const commentSection = this.closest('.comment_section');
    const input = commentSection.querySelector('.comment_input');
    const commentsList = commentSection.querySelector('.comments_list');
    const post = this.closest('.home_middle_middle, .home_middle_bottom');
    const commentCountElem = post.querySelector('.comment-count');

    if (input.value.trim() !== '') {

      const newComment = document.createElement('div');
      newComment.classList.add('comment_item');
      newComment.innerHTML = `<strong>You:</strong> ${input.value}`;

      commentsList.appendChild(newComment);

      let currentComments = parseInt(commentCountElem.textContent);
      commentCountElem.textContent = `${currentComments + 1} Comments`;

      input.value = '';
    }
  });
});