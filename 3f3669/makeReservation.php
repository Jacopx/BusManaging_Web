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
        $type = -1; $data = -1;

        // Making connection with DB
        try {
            $mysqli = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB);
        } catch (Exception $e) {
            $type = 0;
            $data = "Internal error: connection to DB failed ";
            echo json_encode(array("t" => $type, "d" => $data));
            die();
        }

        try {
            // Verify that user exist
            $stmt = $mysqli->prepare("SELECT COUNT(*) FROM Users WHERE user=?;");
            $user_escape  = $mysqli->real_escape_string($user);
            $stmt->bind_param("s", $user_escape);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result == NULL || $result === FALSE) {
                throw new Exception("Unable to check that user exist!");
            }

            if($result->num_rows === 0) {
                throw new Exception("User verify failed!");
            }

            while($row = $result->fetch_assoc()) {
                if ($row["COUNT(*)"] != 1) {
                    throw new Exception("User not found in DB!");
                }
            }

            // Verify if user have already a reservation
            $stmt = $mysqli->prepare("SELECT COUNT(*) FROM Reservations WHERE user=?;");
            $stmt->bind_param("s", $user_escape);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result == NULL || $result === FALSE) {
                throw new Exception("Unable to verify reservation!");
            }

            if($result->num_rows === 0) {
                throw new Exception("Reservation verify failed!");
            }

            while($row = $result->fetch_assoc()) {
                if ($row["COUNT(*)"] == 1) {
                    throw new Exception("Delete reservation before make a new one!");
                }
            }
        } catch (Exception $e) {
            $type = -1;
            $data = $e->getMessage();
            $mysqli->rollback();
            goto end;
        }

        // Start transaction
        $mysqli->autocommit(FALSE);
        $mysqli->begin_transaction();

        $stops = array();
        $attempt = 1;



        retry:
        try {
            // Getting stops
            $stmt = $mysqli->prepare("SELECT * FROM Reservations ORDER BY start, end FOR UPDATE;");
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result == null) {
                throw new Exception("Something goes wrong!");
            }

            if($result->num_rows <= 0) {
                throw new Exception("Impossible getting stops");
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

            // Ensure that stops are sorted, not require, but preferred
            sort($stops);
            // Array_fill in order to allow empty segements
            $passNumber = array_fill(0, (count($stops) - 1), 0);

            // Re-executed statement for stops
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result == null) {
                throw new Exception("Something goes wrong!");
            }

            if($result->num_rows <= 0) {
                throw new Exception("Impossible getting stops!");
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

        if (max($passNumber) + $number <= BUS_SIZE) {
            try {
                // Insert new reservation in DB
                $stmt = $mysqli->prepare("INSERT INTO Reservations VALUES (?,?,?,?);");
                $number_escape = $mysqli->real_escape_string($number);
                $start_escape = $mysqli->real_escape_string($start);
                $end_escape = $mysqli->real_escape_string($end);
                $stmt->bind_param("ssss", $user_escape, $number_escape, $start_escape, $end_escape);
                $stmt->execute();

                if ($stmt->affected_rows === 0) {
                    throw new Exception("Insert not possible");
                } else {
                    // SUCCESS
                    $type = 1;
                    $data = "Reservation added";
                    $mysqli->commit();
                    goto end;
                }

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

        // ENDING
        end:
        $mysqli->close();
        echo json_encode(array("t" => $type, "d" => $data));
        die();
    }