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

        // Making connection with DB
        try {
            $mysqli = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB);
        } catch(Exception $e) {
            $type = 0;
            $data ="Internal error: connection to DB failed ";
            echo json_encode(array("t" => $type, "d" => $data));
            die();
        }

        // Start transaction
        $mysqli->autocommit(FALSE);
        $mysqli->begin_transaction();

        try {
            // Get user informations
            $stmt = $mysqli->prepare("SELECT * FROM Users WHERE user=? FOR UPDATE");
            $user_escape  = $mysqli->real_escape_string($user);
            $stmt->bind_param("s", $user_escape);
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
                    $hash = getToken(254);
                    $time = time() + TIMEOUT * 60;

                    $stmt = $mysqli->prepare("UPDATE Users SET token = ?, timestamp = ?  WHERE user = ?");
                    $stmt->bind_param("sis", $hash, $time, $user_escape);
                    $stmt->execute();

                    if($stmt->affected_rows === 0) {
                        throw new Exception("Update not performed!");
                    }

                    $type = 1;
                    $data = 'Password is valid!<br><br>Welcome ' . $user . ' <br>';

                    $cookie_name = 'polixbus_user';
                    $cookie_value = $user;
                    setcookie($cookie_name, $cookie_value, time() + (TIMEOUT*60), '/');

                    $cookie_name = 'polixbus_hash';
                    $cookie_value = $hash;
                    setcookie($cookie_name, $cookie_value, time() + (TIMEOUT*60), '/');

                    $mysqli->commit();

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
        $mysqli->close();
        echo json_encode(array("t" => $type, "d" => $data));
        die();
}

function getToken($length){
    $token = "";
    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
    $codeAlphabet.= "0123456789";
    $max = strlen($codeAlphabet); // edited

    for ($i=0; $i < $length; $i++) {
        $token .= $codeAlphabet[random_int(0, $max-1)];
    }

    return $token;
}