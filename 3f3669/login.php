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
        try {

            $stmt->bind_param("s", $user);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result == NULL || $result === FALSE) {
                throw new Exception("Impossible verify user!");
            }

            if($result->num_rows === 0) {
                throw new Exception("User not found!");
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
                    throw new Exception("Wrong password!");
                }
            }

        } catch (Exception $e) {
            $type = 0;
            $data = $e->getMessage();
            $mysqli->rollback();
            goto end;
        }


        end:
        $stmt->close();
        $mysqli->close();
        echo json_encode(array("t" => $type, "d" => $data));
        die();
}