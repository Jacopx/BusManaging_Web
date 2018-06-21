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

    if(isset($_POST['field1']) && isset($_POST['field2']) && isset($_POST['field3']) && isset($_POST['field4']) && $secure_connection) {
        if ($_POST['field2'] < $_POST['field3']) {
            makeReservation($_POST['field1'], $_POST['field2'], $_POST['field3'], $_POST['field4']);
        }
    }

    function makeReservation($user, $start, $end, $number)
    {
        //@TODO: Verify that user exist
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

        $mysqli->autocommit(FALSE);
        $mysqli->begin_transaction();

        $stops = array();
        $attempt = 1;

        $stmt = $mysqli->prepare("SELECT * FROM Reservations ORDER BY start, end FOR UPDATE;");

        retry:
        try {
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result == null) {
                throw new Exception("Something goes wrong!");
            }

            if($result->num_rows <= 0) {
                $type = 0;
                $data = "Impossible getting stops";
                goto end;
            }

            while($row = $result->fetch_assoc()) {
                $addS = 1; $addE = 1;
                foreach($stops as $key => $value) {
                    if ($row["start"] == $value) {
                        $addS = 0;
                    }
                    if ($row["end"] == $value) {
                        $addE = 0;
                    }
                    if ($addS == 0 && $addE == 0) {
                        break;
                    }
                }
                if($addS == 1) {
                    array_push($stops, $row["start"]);
                }
                if($addE == 1) {
                    array_push($stops, $row["end"]);
                }
            }

            sort($stops);
            // Array_fill in order to allow empty segements
            $passNumber = array_fill(0, (count($stops) - 1), 0);

            $stmt->execute();
            $result = $stmt->get_result();

            if ($result == null) {
                throw new Exception("Something goes wrong!");
            }

            if($result->num_rows <= 0) {
                $type = 0;
                $data = "Impossible getting stops";
                goto end;
            }

            while($row = $result->fetch_assoc()) {
                for ($i = 0; $i < (count($stops) - 1); $i++) {

                    if ($row["start"] <= $stops[$i] && $row["end"] >=  $stops[($i + 1)]) {
                        if ($stops[$i] >= $start && $stops[$i + 1] <= $end) {
                            $passNumber[$i] += $row["seats"];
                        }
                    }
                }
            }

        } catch (Exception $e) {

            if ($attempt <= MAX_ATTEMPT) {
                $attempt++;
                sleep(TIMEOUT);
                goto retry;
            } else {
                $mysqli->rollback();
                $type = -1;
                $data = $e->getMessage();
                goto end;
            }

        }

        $stmt = $mysqli->prepare("INSERT INTO Reservations VALUES (?,?,?,?)");

        if (max($passNumber) + $number <= BUS_SIZE) {
            try {

                $stmt->bind_param("ssss", $user, $number, $start, $end);
                $stmt->execute();

                $result = $stmt->get_result();

                if ($result == NULL || $result === FALSE) {
                    throw new Exception("Insert not possible");
                }

                $type = 1;
                $data = "Reservation added";
                $mysqli->commit();
                goto end;

            } catch (Exception $e) {

                $type = 0;
                $data = $e->getMessage();
                $mysqli->rollback();
                goto end;

            }
        } else {
            $type = -1;
            $data = "Booking not possible, Not enough seats on the bus!";
            $mysqli->rollback();
            goto end;
        }

        end:
        $stmt->close();
        $mysqli->close();
        echo json_encode(array("t" => $type, "d" => $data));
        die();
    }