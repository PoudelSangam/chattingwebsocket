<?php
  session_start();
  include_once "config.php"; 
  $email = mysqli_real_escape_string($conn, $_POST['email']); 
  $password = mysqli_real_escape_string($conn, $_POST['password']);
  
  if(!empty($email) && !empty($password)){
    // checking if user exists in the database
      $sql = mysqli_query($conn, "SELECT * FROM users WHERE email = '{$email}' AND password = '{$password}' AND rolee='User'");
      if(mysqli_num_rows($sql) > 0) { // if user exists
        $row = mysqli_fetch_assoc($sql);
        $_SESSION['unique_id'] = $row['unique_id']; // using this session we used user unique_id in other php file
        echo "success";
      }else{
        echo "Email or Password is incorrect OR You are Therapist OR you haven't sign up yet...";
      }

  }else{
    echo "All input fields are required";
  }

?>