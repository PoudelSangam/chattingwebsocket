<?php
session_start();
include_once "config.php"; 
$fname = mysqli_real_escape_string($conn, $_POST['fname']);
$lname = mysqli_real_escape_string($conn, $_POST['lname']);
$email = mysqli_real_escape_string($conn, $_POST['email']); 
$password = mysqli_real_escape_string($conn, $_POST['password']);


if(!empty($fname) && !empty($lname) && !empty($email) && !empty($password)){
    // checking if email is valid or not 
    if(filter_var($email, FILTER_VALIDATE_EMAIL)){  //if email is valid
        //checking if the email already exists in database
        $sql = mysqli_query($conn, "SELECT * FROM users WHERE email = '{$email}'");
        if(mysqli_num_rows($sql) > 0){ // if email already exists
            echo "$email - This email already exists!";
        }else{
            //checking if user has uploaded file or not
            if(isset($_FILES['image'])){ //if file is uploaded
                $img_name = $_FILES['image']['name']; //getting uploaded img's name
                $img_type = $_FILES['image']['type']; //getting uploaded img's type
                $tmp_name = $_FILES['image']['tmp_name']; //this temporary name is used to save/move file in our folder

                //explode image and get the last extension like jpg png
                $img_explode = explode('.', $img_name);
                $img_ext = end($img_explode); //here we get the extension of an user uploaded img file
                $extensions = ['png', 'jpeg', 'jpg']; //these are some valid img extention and we have stored them in array
                if(in_array($img_ext, $extensions)){ //if user's uploaded img ext is matched with any array extensions
                    $time = time(); //this will return us current time..
                                    //we need this time because while uploading user img in our folder we rename user file with current time
                                    //so that all the file will have a unique name
                    //moving the user uploaded img to our particular folder.
                    $new_img_name = $time.$img_name;

                    if(move_uploaded_file($tmp_name, "images/".$new_img_name)){ //if user upload img move to out folder sucessfully
                        $status = "Active now"; //once user signed up then his status will be active now
                        $random_id = rand(time(), 10000000); //creating random id for user
                        // inserting all user data inside table
                        $sql2 = mysqli_query($conn, "INSERT INTO users (unique_id, fname, lname, email, password, img, status,rolee)
                                            VALUES ({$random_id}, '{$fname}', '{$lname}', '{$email}', '{$password}', '{$new_img_name}', '{$status}','User')");
                        if($sql2){ // if these data inserted
                            $sql3 = mysqli_query($conn, "SELECT * FROM users WHERE email = '{$email}'");
                            if(mysqli_num_rows($sql3) > 0){
                                $row = mysqli_fetch_assoc($sql3);
                                $_SESSION['unique_id'] = $row['unique_id']; //using this session we used user unique_id in other php file
                                echo "success";
                            }
                        }else{
                            echo "something went wrong!";
                        }
                    }else{
                        echo "Failed to move uploaded file.";
                    }
                }else{
                    echo "Please select an image with - jpeg, jpg, png!";
                }
            }else{
                echo "Please select an image!"; 
            }
        }
    }else{
        echo "$email - This is not a valid email!";
    }
}else{
    echo "All input fields are required!";
}
?>