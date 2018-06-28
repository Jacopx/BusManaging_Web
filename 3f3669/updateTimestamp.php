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
        updateTimestamp($_POST['field1'], $_POST['field2']);
    }

    function updateTimestamp($user, $hash) {
        $type = -3; $data = -3;

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
            $time = time() + TIMEOUT * 60;
            $stmt = $mysqli->prepare("UPDATE Users SET timestamp = ?  WHERE user = ? AND token = ?");
            $stmt->bind_param("iss", $time, $user, $hash);
            $stmt->execute();

            if($stmt->affected_rows === 0) {
                throw new Exception("Cookie update not performed!");
            } else {
                $type = 1;
                $data = "Updated";
            }

        } catch (Exception $e) {
            $type = 0;
            $data = $e->getMessage();
            $mysqli->rollback();
            goto end;
        }

        end:
        $mysqli->close();
        echo json_encode(array("t" => $type, "d" => $data));
        die();
    }