<?php
    include 'base.php';

    if(isset($_SERVER['HTTPS'])) {
        if ($_SERVER['HTTPS'] == "on") {
            $secure_connection = true;
        } else {
            echo "Connection NOT SECURE!!";
            die();
        }
    }

    if(isset($_POST['field1']) && isset($_POST['field2']) && $secure_connection) {
        login($_POST['field1'], $_POST['field2']);
    }

    function login($user, $pass) {
        $type = -3; $data = -3;

        try {
            $mysqli = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB);
        } catch(Exception $e) {
            $type = 0;
            $data ="Internal error: connection to DB failed ";
            echo json_encode(array("t" => $type, "d" => $data));
            die();
        }

        $stmt = $mysqli->prepare("SELECT * FROM Users WHERE user=?");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows === 0) {
            $type = -2;
            $data = "User not found!";
            echo json_encode(array("t" => $type, "d" => $data));
            $stmt->close();
            $mysqli->close();
            die();
        }

        while($row = $result->fetch_assoc()) {
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
                $data = "Wrong password!";
                echo json_encode(array("t" => $type, "d" => $data));
                $stmt->close();
                $mysqli->close();
                die();
            }
        }

        $stmt->close();
        $mysqli->close();
        echo json_encode(array("t" => $type, "d" => $data));
        die();
}