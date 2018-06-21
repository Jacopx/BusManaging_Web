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

        $stmt = $mysqli->prepare("SELECT * FROM Users WHERE user=?");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows === 0) {
            $type = -2;
            $data = "Cookie error, user not found!";
            goto end;
        }

        while($row = $result->fetch_assoc()) {
            if ($hash == $row["pass"]) {
                $type = 1;
                $data = "Welcome, " . $user . "<br>";
            } else {
                $type = 1;
                $data = "Cookie error, password not match";
            }
        }

        end:
        $stmt->close();
        $mysqli->close();
        echo json_encode(array("t" => $type, "d" => $data));
        die();
    }