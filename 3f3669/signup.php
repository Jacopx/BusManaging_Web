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
        $type = -3; $data = -3;

        // Data verification
        $passPattern = "/^(?=.*[a-z])(?=.*[A-Z\d]).+$/";
        if (!preg_match($passPattern, $pass) || !filter_var($user, FILTER_VALIDATE_EMAIL)) {
            $type = 0;
            $data = "Password and/or mail are not correct!";
            echo json_encode(array("t" => $type, "d" => $data));
            die();
        }

        // Making connection with DB
        try {
            $mysqli = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB);
        } catch(Exception $e) {
            $type = 0;
            $data ="Internal error: connection to DB failed ";
            echo json_encode(array("t" => $type, "d" => $data));
            die();
        }

        try {
            // Verify that user not already exist
            $stmt = $mysqli->prepare("SELECT * FROM Users WHERE user=?");
            $user_escape  = $mysqli->real_escape_string($user);
            $stmt->bind_param("s", $user_escape);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result == NULL || $result === FALSE) {
                throw new Exception("Impossible verify user!");
            }

            if($result->num_rows > 0) {
                throw new Exception("User already registered!");
            }

            $hash = password_hash($pass, PASSWORD_DEFAULT);

            // Insert the new user
            $stmt = $mysqli->prepare("INSERT INTO Users VALUES (?,?)");
            $user_escape  = $mysqli->real_escape_string($user);
            $hash_escape = $mysqli->real_escape_string($hash);
            $stmt->bind_param("ss", $user_escape, $hash_escape);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception("Registration impossible");
            } else {
                // SUCCESS
                $type = 1;
                $data = "Signup successful";
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