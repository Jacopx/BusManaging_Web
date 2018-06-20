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

    function makeReservation($user, $start, $end, $number) {
        //@TODO: Verify that user exist
        //@TODO: Using prepared statement
        $type = -1; $data = -1;
        $conn = mysqli_connect(SQL_HOST, SQL_USER, SQL_PASS);

        if (mysqli_connect_errno()) {
            $type = 0;
            $data ="Internal error: connection to DB failed ". mysqli_connect_error();
            echo json_encode(array("t" => $type, "d" => $data));
            exit();
        }
        if (!mysqli_select_db($conn, SQL_DB)) {
            $type = 0;
            $data = "Internal error: selection of DB failed";
            echo json_encode(array("t" => $type, "d" => $data));
            exit();
        }

        mysqli_autocommit($conn, FALSE);
        mysqli_query($conn, "START TRANSACTION;");

        if ((getSeats($conn, $start, $end) + $number) <= BUS_SIZE) {
            try {
                $sql = "INSERT INTO Reservations VALUES ('$user','$number','$start','$end')";
                $result = mysqli_query($conn, $sql);

                if(!$result) {
                    throw new Exception("Insert failed");
                } else {
                    $type = 1;
                    $data = "Reservation added";
                }

                mysqli_commit($conn);

            } catch (Exception $e) {
                mysqli_rollback($conn);
                $type = 0;
                $data = $e->getMessage();
                echo json_encode(array("t" => $type, "d" => $data));
                mysqli_close($conn);
                die();
            }
        } else {
            mysqli_rollback($conn);
            $type = -1;
            $data = "Booking not possible, Not enough seats on the bus!";
            echo json_encode(array("t" => $type, "d" => $data));
            mysqli_close($conn);
            die();
        }

        mysqli_close($conn);

        echo json_encode(array("t" => $type, "d" => $data));
    }

    function getSeats($conn, $start, $end) {
        $stops = array();
        $attempt = 1;

        retry:
        try {
            // Getting stops
            $sql = "SELECT start, end FROM Reservations FOR UPDATE;";
            $result1 = mysqli_query($conn, $sql);

            if(!$result1)
                throw new Exception("Booking NOT possible!");

            if (mysqli_num_rows($result1) > 0) {
                // output data of each row
                while($row = mysqli_fetch_assoc($result1)) {
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
            }

            sort($stops);
            // Array_fill in order to allow empty segements
            $passNumber = array_fill(0, (count($stops) - 1), 0);

            // Getting users and preparing all for printing
            $sql = "SELECT * FROM Reservations ORDER BY start, end FOR UPDATE;";
            $result3 = mysqli_query($conn, $sql);

            if(!$result3)
                throw new Exception("Booking NOT possible!");

            if (mysqli_num_rows($result3) > 0) {
                // output data of each row
                while($row = mysqli_fetch_assoc($result3)) {

                    for ($i = 0; $i < (count($stops) - 1); $i++) {

                        if ($row["start"] <= $stops[$i] && $row["end"] >=  $stops[($i + 1)]) {
                            if ($stops[$i] >= $start && $stops[$i + 1] <= $end) {
                                $passNumber[$i] += $row["seats"];
                            }
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
                mysqli_rollback($conn);
                $type = -1;
                $data = $e->getMessage();
                echo json_encode(array("t" => $type, "d" => $data));
                mysqli_close($conn);
                die();
            }

        }

        return max($passNumber);
    }