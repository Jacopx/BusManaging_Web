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
        signup($_POST['field1'], $_POST['field2']);
    }

    function signup($user, $pass) {
        //@TODO: Adding value verification before push in DB
        $type = -3; $data = -3;

//        // Data verification
//        $passPattern = "/^(?=.*[a-z])(?=.*[A-Z\d]).+$/";
//        if (preg_match($passPattern, $pass) && filter_var($user, FILTER_VALIDATE_EMAIL)) {
//            $type = 0;
//            $data = "Server Side data verification failed!";
//            echo json_encode(array("t" => $type, "d" => $data));
//            die();
//        }

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

            if($result->num_rows > 0) {

                $type = -1;
                $data = "User already registered!";
                goto end;

            }

            $hash = password_hash($pass, PASSWORD_DEFAULT);

            $stmt = $mysqli->prepare("INSERT INTO Users VALUES (?,?)");
            $stmt->bind_param("ss", $user, $hash);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception("Registration impossible");
            }

            // SUCCESS
            $type = 1;
            $data = "Signup successful";

        } catch (Exception $e) {
            $type = -1;
            $data = $e->getMessage();
            goto end;
        }

        end:
        $stmt->close();
        $mysqli->close();
        echo json_encode(array("t" => $type, "d" => $data));
        die();
    }