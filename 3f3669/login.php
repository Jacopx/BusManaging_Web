<?php
    include 'base.php';

    if(isset($_POST['field1']) && isset($_POST['field2'])) {
        login($_POST['field1'], $_POST['field2']);
    }

    function login($user, $pass) {
        $conn = mysqli_connect(SQL_HOST, SQL_USER, SQL_PASS);

        if (mysqli_connect_errno()) {
            die("Internal error: connection to DB failed ".
                mysqli_connect_error());
        }
        if (!mysqli_select_db($conn, SQL_DB)) {
            die("Internal error: selection of DB failed");
        }

        $sql = "SELECT * FROM Users WHERE user='$user'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            // output data of each row
            while($row = mysqli_fetch_assoc($result)) {

                if (password_verify($pass, $row["pass"])) {
                    echo 'Password is valid!<br><br>Welcome ' . $user . ' <br>';

                    $cookie_name = 'polixbus_user';
                    $cookie_value = $user;
                    setcookie($cookie_name, $cookie_value, time() + (5*60), '/');

                    $cookie_name = 'polixbus_hash';
                    $cookie_value = $row["pass"];
                    setcookie($cookie_name, $cookie_value, time() + (5*60), '/');

                } else {
                    echo 'Invalid password';
                }

            }
        } else {
            echo "USER NOT FOUND";
        }
    }