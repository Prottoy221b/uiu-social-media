const header=document.querySelector("#header");
fetch("header.html")
.then(function(response){
  return response.text();

})
.then(function(data){
  header.innerHTML=data;
})

const left=document.querySelector("#left-sidebar");
fetch("left-sidebar.html")
.then(function(response){
  return response.text();
})
.then(function(data){
  left.innerHTML=data;
})


const right=document.querySelector("#right-sidebar");
fetch("right-sidebar.html")
.then(function(response){
  return response.text();
})
.then(function(data){
  right.innerHTML=data;
})

const likeButtons=document.querySelectorAll(".action_btn.like");
likeButtons.forEach(function(likeButton){
  likeButton.addEventListener("click", function(){
    const post=this.closest(".home_middle_middle,.home_middle_bottom");
    const likeCount=post.querySelector(".like-count");
    const number=parseInt(likeCount.textContent);
    const icon=likeButton.querySelector("i");
    console.log(icon);
    if(likeButton.classList.contains("liked")){
      likeButton.classList.remove("liked");

      icon.className="fa-regular fa-thumbs-up";
       likeCount.textContent=(number-1)+ " Likes";
    }
    else{
      likeButton.classList.add("liked");
            icon.className="fa-solid fa-thumbs-up";
      likeCount.textContent=(number+1)+ " Likes";
    }
    
  })
})



const commentButtons= document.querySelectorAll(".action_btn.comment");
commentButtons.forEach(function(commentButton){
  commentButton.addEventListener("click", function(){
    const post=this.closest(".home_middle_middle,.home_middle_bottom");
    const commentSection=post.querySelector(".comment_section");

   
    if(commentSection.style.display==="none"|| commentSection.style.display===""){
      commentSection.style.display="block";
    }else{
      commentSection.style.display="none";
    }
    
  });
});


const sendCommentBtns=document.querySelectorAll(".send_comment_btn");
sendCommentBtns.forEach(function(sendCommentBtn){
  sendCommentBtn.addEventListener("click",function(){
   const post=this.closest(".home_middle_middle,.home_middle_bottom");
   const commentInput=post.querySelector(".comment_input") ;
   const commentList=post.querySelector(".comments_list");
   const newComment=document.createElement("div");
   newComment.classList.add("comment_item");
   const profilePic=document.createElement("img");
   profilePic.src="../img/profile_pic.jpg";
   profilePic.classList.add("comment_user_pic");
   newComment.appendChild(profilePic);
   commentList.appendChild(newComment);
   const commentBubble=document.createElement("div");
   commentBubble.classList.add("comment_bubble");
   const authorName=document.createElement("p");
   authorName.classList.add("comment_author_name");
   authorName.textContent="Mohammad Ronaldo";
   commentBubble.appendChild(authorName);
   const commentText=document.createElement("p");
   commentText.classList.add("comment_text");
   commentText.textContent=commentInput.value;
   commentBubble.appendChild(commentText);
   newComment.appendChild(commentBubble);



   commentInput.value="";
   
   
  });
});



const goingButtons=document.querySelectorAll(".going_btn");
goingButtons.forEach(function(goingButton){
  goingButton.addEventListener("click", function(){
    const event=this.closest(".event_details");

    if(goingButton.classList.contains("active")){
      goingButton.classList.remove("active");
    }else{
      goingButton.classList.add("active");
    }
  });
});


const createEventButton=document.querySelector(".create_event_btn");
if(createEventButton){
  const eventForm=document.querySelector(".event_form");
createEventButton.addEventListener("click",function(){
 
if(eventForm.style.display==="flex"){
  eventForm.style.display="none";
}else{
  eventForm.style.display="flex";
}
});

}

const submitEventButton=document.querySelector(".submit_event_btn");


if(submitEventButton)
  submitEventButton.addEventListener("click",function(){
const template=document.querySelector(".event_template");
const newEvent=template.cloneNode(true);
const eventTitleInput=document.querySelector(".event_title_input");
const eventDateInput=document.querySelector(".event_date_input");
const eventTimeInput=document.querySelector(".event_time_input");
const eventLocationInput=document.querySelector(".event_location_input");
newEvent.querySelector(".event_title").textContent=eventTitleInput.value;
const eventInfo=newEvent.querySelectorAll(".event_info");
eventInfo[0].textContent="📅 " + eventDateInput.value + "-" + eventTimeInput.value;
eventInfo[1].textContent="🌐 "+eventLocationInput.value;
const eventCards=document.querySelector(".event_cards");
eventCards.prepend(newEvent);
eventTimeInput.value="";
eventDateInput.value="";
eventTitleInput.value="";
eventLocationInput.value="";
eventForm.style.display="none";
});



const follows=document.querySelectorAll(".follow");
follows.forEach(function(follow){
  follow.addEventListener("click",function(){
    
    
    if(this.classList.contains("active")){
      this.classList.remove("active");
      this.textContent="+Follow";
    }else{
      this.classList.add("active");
      this.textContent="Followed";
    }
  });
});


const joins=document.querySelectorAll(".join");
joins.forEach(function(join){
  join.addEventListener("click",function(){
    if(this.classList.contains("active")){
      this.classList.remove("active");
      this.textContent="+Join";
    }else{
      this.classList.add("active");
      this.textContent="Joined";
      
    }
  })
})



const postInput=document.querySelector(".post_input");

const postCreateArea=document.querySelector(".post_create_area");
postInput.addEventListener("click", function(){
  postCreateArea.style.display="flex";
  postInput.style.display="none";
  const createPostButton=document.querySelector(".create_post_btn");
  createPostButton.addEventListener("click",function(){
    const postText=document.querySelector(".post_text");
    const postTemplate=document.querySelector(".post_template");
    const newPost=postTemplate.cloneNode(true);
    const newPostText=newPost.querySelector(".home_middle_posts p");
    newPostText.textContent=postText.value;
    
    const postsFeed=document.querySelector(".posts_feed");
    const newLikeCount=newPost.querySelector(".like-count");
    newLikeCount.textContent="0 Likes";

    
    postsFeed.prepend(newPost);


    const newLikeButton=newPost.querySelector(".action_btn.like");
    newLikeButton.addEventListener("click",function(){
      const likeCount=newPost.querySelector(".like-count");
      const number=parseInt(likeCount.textContent);
      const icon=newLikeButton.querySelector("i");
      if(newLikeButton.classList.contains("liked")){
    
    newLikeButton.classList.remove("liked");
    icon.className = "fa-regular fa-thumbs-up";
    likeCount.textContent = (number - 1) + " Likes";

}
else{
    
    newLikeButton.classList.add("liked");
    icon.className = "fa-solid fa-thumbs-up";
    likeCount.textContent = (number + 1) + " Likes";

}
    })
postCreateArea.style.display="none";
  postInput.style.display="flex";
  postText.value="";



  });

  
});

