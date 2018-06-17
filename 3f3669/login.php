<?php
    include 'base.php';

    if(isset($_POST['field1']) && isset($_POST['field2'])) {
        login($_POST['field1'], $_POST['field2']);
    }

    function login($user, $pass) {
        $type = -3; $data = -3;
        $conn = mysqli_connect(SQL_HOST, SQL_USER, SQL_PASS);

        if (mysqli_connect_errno()) {
            $type = 0;
            $data ="Internal error: connection to DB failed ". mysqli_connect_error();
            echo json_encode(array("t" => $type, "d" => $data));
            die();
        }
        if (!mysqli_select_db($conn, SQL_DB)) {
            $type = 0;
            $data = "Internal error: selection of DB failed";
            echo json_encode(array("t" => $type, "d" => $data));
            die();
        }

        $sql = "SELECT * FROM Users WHERE user='$user'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            // output data of each row
            while($row = mysqli_fetch_assoc($result)) {

                if (password_verify($pass, $row["pass"])) {
                    // SUCCESS
                    $type = 1;
                    $data = 'Password is valid!<br><br>Welcome ' . $user . ' <br>';

                    $cookie_name = 'polixbus_user';
                    $cookie_value = $user;
                    setcookie($cookie_name, $cookie_value, time() + (TIMEOUT*60), '/');

                    $cookie_name = 'polixbus_hash';
                    $cookie_value = $row["pass"];
                    setcookie($cookie_name, $cookie_value, time() + (TIMEOUT*60), '/');

                    break;

                } else {
                    // FAIL - WRONG PASSWORD
                    $type = -1;
                    $data = 0;
                    break;
                }

            }
        } else {
            // FAIL - USER NOT FOUND
            $type = -2;
            $data = 0;
        }

        echo json_encode(array("t" => $type, "d" => $data));
    }