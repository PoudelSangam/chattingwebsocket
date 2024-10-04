<?php
    $conn = mysqli_connect("localhost", "root", "", "chat");
    if(!$conn){
      echo "Database connected Successfully" . mysqli_connect_error();
    }

?>