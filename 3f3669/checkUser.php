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
        checkCookie($_POST['field1'], $_POST['field2']);
    }

    function checkCookie($user, $hash) {
        $type = -1; $data = -1;

        try {
            $mysqli = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB);
        } catch(Exception $e) {
            $type = 0;
            $data ="Internal error: connection to DB failed ";
            echo json_encode(array("t" => $type, "d" => $data));
            die();
        }

        try {
            // Get user data
            $stmt = $mysqli->prepare("SELECT * FROM Users WHERE user=?");
            $user_escape  = $mysqli->real_escape_string($user);
            $stmt->bind_param("s", $user_escape);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result == NULL || $result === FALSE) {
                throw new Exception("Error reading user!");
            }

            if($result->num_rows === 0) {
                throw new Exception("Cookie error, user not found!");
            }

            while($row = $result->fetch_assoc()) {
                if ($hash == $row["token"]) {
                    $time = time();
                    if ($time <= $row["timestamp"]) {
                        // SUCCESS
                        $type = 1;
                        $data = "Welcome, " . $user . "<br>";
                    } else {
                        throw new Exception("Cookie error, the session is expired!");
                    }
                } else {
                    throw new Exception("Cookie error, password not match!");
                }
            }
        } catch (Exception $e) {
            $type = -1;
            $data = $e->getMessage();
            goto end;
        }

        // ENDING
        end:
        $mysqli->close();
        echo json_encode(array("t" => $type, "d" => $data));
        die();
    }