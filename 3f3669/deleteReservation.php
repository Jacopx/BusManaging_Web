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

    if(isset($_POST['field1']) && $secure_connection) {
        deleteReservation($_POST['field1']);
    }

    function deleteReservation($logged)
    {
        $type = -1;
        $data = -1;

        try {
            $mysqli = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB);
        } catch (Exception $e) {
            $type = 0;
            $data = "Internal error: connection to DB failed ";
            echo json_encode(array("t" => $type, "d" => $data));
            die();
        }

        $stmt = $mysqli->prepare("DELETE FROM Reservations WHERE user=?");
        try {
            $stmt->bind_param("s", $logged);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception("Impossible delete reservation!");
            }

            $type = 1;
            $data = "Delete correctly performed!";

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