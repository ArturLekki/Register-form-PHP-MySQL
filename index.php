<?php
    session_start();
    if(isset($_POST['email'])) 
    {
        $wszystko_ok=true; 
        $email=$_POST['email']; 
        $emailB=filter_var($email,FILTER_SANITIZE_EMAIL); 
        if((filter_var($emailB,FILTER_VALIDATE_EMAIL)==false) || ($emailB!=$email)) 
        {
            $wszystko_ok=false;
            $_SESSION['e_email']='<div style="color:red;">Put correct email adress</div>';
        }
        $haslo1=$_POST['pass1'];
        $haslo2=$_POST['pass2'];
        if((strlen($haslo1)<4) || (strlen($haslo1)>20))
        {
            $wszystko_ok=false;
            $_SESSION['e_haslo']='<div style="color:red;">The password must be between 4 and 20 characters long</div>';
        }

        if($haslo1!=$haslo2)
        {
            $wszystko_ok=false;
            $_SESSION['e_haslo']='<div style="color:red;">Passwords must be identical</div>';
        }
        $haslo_hash=password_hash($haslo1,PASSWORD_DEFAULT);

        require_once "connect.php"; 
        mysqli_report(MYSQLI_REPORT_STRICT); 
        try
        {
            $DBconnect=new mysqli($DBhost,$DBlogin,$DBpass,$DBname);
            if($DBconnect->connect_errno!=0)
            {
                throw new Exception(mysqli_connect_errno());
            }
            else
            {
                $rezultat=$DBconnect->query("SELECT id FROM users WHERE email='$email'");
                if(!$rezultat) throw new Exception($DBconnect->error);
                $ile_takich_maili=$rezultat->num_rows;
                if($ile_takich_maili>0)
                {
                    $wszystko_ok=false;
                    $_SESSION['e_email']='<div style="color:red">User exists already</div>';
                }
                if($wszystko_ok==true)
                {
                   if($DBconnect->query("INSERT INTO users (email,pass) VALUES('$email','$haslo_hash')"))
                    {
                        $_SESSION['udanarejestracja']=true;
                        $_SESSION['Welcome']='<div style="color:green">Accout created!</div>';
                        header("Location: index.php");
                    }
                    else
                    {
                       throw new Exception($DBconnect->error);
                    }
                }
                $DBconnect->close();
            }
        }
        catch(Exception $e)
        {
            echo '<div style="color:red;">Unable to connect to Database</div>';
        }
    }
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <form method="POST">
        Email<br/>
        <input type="email" name="email"/><br/>
            <?php if(isset($_SESSION['e_email']))
                {
                    echo $_SESSION['e_email'];
                    unset($_SESSION['e_email']);
                }
            ?>
        Password<br/>
        <input type="password" name="pass1"/><br/>
            <?php if(isset($_SESSION['e_haslo']))
                {
                    echo $_SESSION['e_haslo'];
                    unset($_SESSION['e_haslo']);
                }
            ?>
        Repeat password<br/>
        <input type="password" name="pass2"/><br/>
        <input type="submit" value="Register"/>
    </form>
    <?php
        if(isset($_SESSION['Welcome']))
        {
            echo $_SESSION['Welcome'];
        }
    ?>
</body>
</html>
