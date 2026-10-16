<?php

include "db.php";

if($_SERVER["REQUEST_METHOD"] === "POST"){

    $name=$_POST["name"];
    $department=$_POST["department"];
    $email=$_POST["email"];
    $password=$_POST["password"];
    $accountType=$_POST["account_type"];

    $password=password_hash($password, PASSWORD_DEFAULT);

    $sql="INSERT INTO users(name,department,email,password,account_type)
          VALUES (?,?,?,?,?)";

    $stmt=$conn->prepare($sql);

    $stmt->bind_param(
        "sssss",
        $name,
        $department,
        $email,
        $password,
        $accountType
    );

    try {

        if($stmt->execute()){
            echo "Registration successful";
        }

    } catch(mysqli_sql_exception $e) {

        if($e->getCode() === 1062){
            echo "Email already registered";
        }else{
            echo "Registration failed";
        }

    }
}

?>