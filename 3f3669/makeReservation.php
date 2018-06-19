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
        makeReservation($_POST['field1'], $_POST['field2'], $_POST['field3'], $_POST['field4']);
    }

    function makeReservation($user, $start, $end, $number) {
        //@TODO: Verify START BEFORE END
        //@TODO: Using prepared statement
        $type = -1; $data = -1; $allow = 0;
        $conn = mysqli_connect(SQL_HOST, SQL_USER, SQL_PASS);

        if (mysqli_connect_errno()) {
            $type = 0;
            $data ="Internal error: connection to DB failed ". mysqli_connect_error();
            echo json_encode(array("t" => $type, "d" => $data));
            die();
        }
        if (!mysqli_select_db($conn, SQL_DB)) {
            $type = 0;
            $data = "Internal error: selection of DB failed";
            echo json_encode(array("t" => $type, "d" => $data));
            die();
        }

        mysqli_autocommit($conn, FALSE);
        mysqli_query($conn, "START TRANSACTION;");

        if ((getSeats($conn, $start, $end) + $number) <= BUS_SIZE) {
            $allow = 1;
        } else {
            mysqli_rollback($conn);
            $type = -1;
            $data = "Adding not possible! Not enough seats on the bus!";
            echo json_encode(array("t" => $type, "d" => $data));
            mysqli_close($conn);
            die();
        }

        sleep(10);

        if ($allow == 1) {
            try {
                $sql = "INSERT INTO Reservations VALUES ('$user','$number','$start','$end')";
                $result = mysqli_query($conn, $sql);

                if(!$result)
                    throw new Exception("Insert failed");

                if ($result) {
                    $type = 1;
                    $data = "Reservation added";
                } else {
                    $type = -2;
                    $data = "Reservation ERROR!";
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
        }

        mysqli_close($conn);

        echo json_encode(array("t" => $type, "d" => $data));
    }

    function getSeats($conn, $start, $end) {
        $stops = array();
        $attemp = 0;

        retry:

        try {
            // Getting starting stops
            $sql = "SELECT start FROM Reservations ORDER BY start FOR UPDATE;";
            $result1 = mysqli_query($conn, $sql);

            if(!$result1)
                throw new Exception("Get reservation START failed");

            if (mysqli_num_rows($result1) > 0) {
                // output data of each row
                while($row = mysqli_fetch_assoc($result1)) {
                    $add = 1;
                    foreach($stops as $key => $value) {
                        if ($row["start"] == $value) {
                            $add = 0;
                            break;
                        }
                    }
                    if($add == 1) {
                        array_push($stops, $row["start"]);
                    }
                }
            }
        } catch (Exception $e) {
            if($attemp <= 3) {
                $attemp++;
                sleep(2);
                goto retry;
            } else {
                mysqli_rollback($conn);
                $type = 0;
                $data = $e->getMessage();
                echo json_encode(array("t" => $type, "d" => $data));
                mysqli_close($conn);
                die();
            }
        }

        try {
            // Getting ending stops
            $sql = "SELECT end FROM Reservations ORDER BY end FOR UPDATE;";
            $result2 = mysqli_query($conn, $sql);

            if(!$result2)
                throw new Exception("Get reservation END failed");

            if (mysqli_num_rows($result2) > 0) {
                // output data of each row
                while($row = mysqli_fetch_assoc($result2)) {
                    $add = 1;
                    foreach($stops as $key => $value) {
                        if ($row["end"] == $value) {
                            $add = 0;
                            break;
                        }
                    }
                    if($add == 1) {
                        array_push($stops, $row["end"]);
                    }
                }
            }
        } catch (Exception $e) {
            if($attemp <= 3) {
                $attemp++;
                sleep(2);
                goto retry;
            } else {
                mysqli_rollback($conn);
                $type = 0;
                $data = $e->getMessage();
                echo json_encode(array("t" => $type, "d" => $data));
                mysqli_close($conn);
                die();
            }
        }

        sort($stops);
        // Array_fill in order to allow empty segements
        $passNumber = array_fill(0, (count($stops) - 1), 0);

        try {
            // Getting users and preparing all for printing
            $sql = "SELECT * FROM Reservations ORDER BY start, end FOR UPDATE;";
            $result3 = mysqli_query($conn, $sql);

            if(!$result3)
                throw new Exception("Get reservation list failed");

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
            if($attemp <= 3) {
                $attemp++;
                sleep(2);
                goto retry;
            } else {
                mysqli_rollback($conn);
                $type = 0;
                $data = $e->getMessage();
                echo json_encode(array("t" => $type, "d" => $data));
                mysqli_close($conn);
                die();
            }
        }

        return max($passNumber);
    }