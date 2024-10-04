const searchBar = document.querySelector(".users .search input"),
searchBtn = document.querySelector(".users .search button"),
usersList = document.querySelector(".users .users-list");

searchBtn.onclick = ()=>{
    searchBar.classList.toggle("active");
    searchBar.focus();
    searchBtn.classList.toggle("active");
    searchBar.value = "";
}

searchBar.onkeyup = ()=>{
  let searchTerm = searchBar.value;
  if(searchTerm != ""){ // if searchTerm is not empty then the search result is shown
    searchBar.classList.add("active");
  }else{
    searchBar.classList.remove("active"); 
  }
  // ajax starts
  let xhr = new XMLHttpRequest(); //creating XML object
  //xhr.open takes many parameters but we are using only 3
  xhr.open("POST", "php/search.php", true); // using post method to send the data
  xhr.onload = ()=>{
     if(xhr.readyState === XMLHttpRequest.DONE){
        if(xhr.status === 200){
          let data = xhr.response;
          usersList.innerHTML = data;
        }
     }
  }
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.send("searchTerm=" + searchTerm);
}


    // ajax starts
  let xhr = new XMLHttpRequest(); //creating XML object
  //xhr.open takes many parameters but we are using only 3
  xhr.open("GET", "php/users.php", true); // using get method to receive the data
  xhr.onload = ()=>{
     if(xhr.readyState === XMLHttpRequest.DONE){
        if(xhr.status === 200){
          let data = xhr.response;
          if(!searchBar.classList.contains("active")){ // if search bar is not active then add the data
            usersList.innerHTML = data;
          }
        }
     }
  }
  xhr.send();
  // ajax ends
