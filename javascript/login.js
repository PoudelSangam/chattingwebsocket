const form = document.querySelector(".Login form"),
continueBtn = form.querySelector(".button input"),
errorText = form.querySelector(".error-txt");

form.onsubmit = (e)=>{
    e.preventDefault(); //prevents form from submitted.
}

continueBtn.onclick = ()=>{
  // ajax starts
  let xhr = new XMLHttpRequest(); //creating XML object
  //xhr.open takes many parameters but we are using only 3
  xhr.open("POST", "php/login.php", true);
  xhr.onload = ()=>{
     if(xhr.readyState === XMLHttpRequest.DONE){
        if(xhr.status === 200){
          let data = xhr.response;
          console.log(data);
          if(data == "success"){
            location.href = "./users.php";
          }else{
            errorText.textContent = data;
            errorText.style.display = "block";
          }
        }
     }
  }
  // the form data is send from ajax to php
  let formData = new FormData(form); //creating new formData object
  xhr.send(formData); //sending the form data to php
  // ajax ends

}