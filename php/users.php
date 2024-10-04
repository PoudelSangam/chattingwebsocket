<?php
  session_start();
  include_once "config.php";
  $sql = mysqli_query($conn, "SELECT * FROM users");
  $output = "";

  //checks if there is any user available to chat
  if(mysqli_num_rows($sql) == 1 ){
      $output .= "No users are available to chat"; //if no users are available to chat
    


  }elseif(mysqli_num_rows($sql) > 0 ){ //else all the availabe users are shown
     include "data.php";
  }
  echo $output;


?>