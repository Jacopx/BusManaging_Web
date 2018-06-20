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
        getReservation($_POST['field1']);
    }

    function getReservation($logged) {
        //@TODO: Adding Deadlock prevention
        $type = -1;
        $conn = mysqli_connect(SQL_HOST, SQL_USER, SQL_PASS);
        $stops = array();

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

        // Getting stops
        $sql = "SELECT start, end FROM Reservations;";
        $result1 = mysqli_query($conn, $sql);

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
        } else {
            $type = 0;
            $data = "Impossible getting starting places";
            echo json_encode(array("t" => $type, "d" => $data));
            die();
        }

        sort($stops);
        $segments = array();

        for ($i = 0; $i < (count($stops) - 1); $i++) {
            array_push($segments, $stops[$i] . " --> " . $stops[($i + 1)]);
        }

        // Array_fill in order to allow empty segements
        $passNumber = array_fill(0, (count($stops) - 1), 0);
        $rowString = array_fill(0, (count($stops) - 1), " ");
        $startPoint = -1;
        $endPoint = -1;

        // Getting users and preparing all for printing
        $sql = "SELECT * FROM Reservations ORDER BY start, end;";
        $result3 = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result3) > 0) {
            // output data of each row
            while($row = mysqli_fetch_assoc($result3)) {

                    for ($i = 0; $i < (count($stops) - 1); $i++) {

                        if ($row["start"] <= $stops[$i] && $row["end"] >=  $stops[($i + 1)]) {

                            if ($row["start"] == $stops[$i] && $logged == $row["user"]) {
                                $startPoint = $i;
                            }
                            if  ($row["end"] == $stops[($i + 1)] && $logged == $row["user"]) {
                                $endPoint = $i;
                            }

                            if (key_exists($i, $passNumber) && key_exists($i, $rowString)) {
                                $passNumber[$i] += $row["seats"];
                                $rowString[$i] = $rowString[$i] . $row["user"] . " (" . $row["seats"] . " passengers) ";
                            }
                        }

                    }

            }
        } else {
            $type = 0;
            $data = "Impossible preparing output";
            echo json_encode(array("t" => $type, "d" => $data));
            die();
        }

        $data = "<table>";
        if ($logged != "") {
            $data = $data . "<tr><th>Track</th><th>Total</th><th>Users</th></tr>";
        } else {
            $data = $data . "<tr><th>Track</th><th>Total</th></tr>";
        }

        for ($i = 0; $i < count($segments); $i++) {
            if ($logged != "") {
                if ($startPoint == $i) {
                    $data = $data . "<tr bgcolor=\"#00ff00\"><td>" . $segments[$i] . "</td><td>" . $passNumber[$i] . "</td><td>" . $rowString[$i] . "</td></tr>";
                } else if ($i == $endPoint) {
                    $data = $data . "<tr bgcolor=\"#ff6666\"><td>" . $segments[$i] . "</td><td>" . $passNumber[$i] . "</td><td>" . $rowString[$i] . "</td></tr>";
                } else {
                    $data = $data . "<tr><td>" . $segments[$i] . "</td><td>" . $passNumber[$i] . "</td><td>" . $rowString[$i] . "</td></tr>";
                }
            } else {
                $data = $data . "<tr><td>" . $segments[$i] . "</td><td>" . $passNumber[$i] . "</td></tr>";
            }

        }

        if($i == count($segments)) {
            $type = 1;
        }

        $data = $data . "</table>";

        echo json_encode(array("t" => $type, "d" => $data));
    }