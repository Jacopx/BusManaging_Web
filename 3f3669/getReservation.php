<?php
include 'base.php';

if(isset($_POST['field1'])) {
    getReservation($_POST['field1']);
}

function getReservation($logged) {
    $type = -1; $data = -1;
    $conn = mysqli_connect(SQL_HOST, SQL_USER, SQL_PASS);
    $stops = array();

    if (mysqli_connect_errno()) {
        $type = 0;
        $data ="Internal error: connection to DB failed ". mysqli_connect_error();
    }
    if (!mysqli_select_db($conn, SQL_DB)) {
        $type = 0;
        $data = "Internal error: selection of DB failed";
    }

    // Getting starting stops
    $sql = "SELECT start FROM Reservation ORDER BY start;";
    $result1 = mysqli_query($conn, $sql);

    echo "after query 1";

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
    } else {
        $type = 0;
        $data = "Impossible getting starting places";
        echo json_encode(array("t" => $type, "d" => $data));
    }

    // Getting ending stops
    $sql = "SELECT end FROM Reservation ORDER BY end;";
    $result2 = mysqli_query($conn, $sql);

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
    } else {
        $type = 0;
        $data = "Impossible getting ending places";
        echo json_encode(array("t" => $type, "d" => $data));
    }

    sort($stops);
    $segments = array();

    foreach($stops as $key => $value) {
        array_push($segments, $stops[$key] . " --> " . $stops[$key + 1]);
    }

    $passNumber = array();
    $rowString = array();

    // Getting users and preparing all for printing
    $sql = "SELECT * FROM Reservation ORDER BY start, end;";
    $result3 = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result3) > 0) {
        // output data of each row
        while($row = mysqli_fetch_assoc($result3)) {
                for ($i = 0; $i <= count($stops); $i++) {
                    if ($row["start"] <= $stops[$i] && $row["end"] >=  $stops[$i + 1]) {
                        $passNumber[$i] += $row["seats"];
                        $rowString[$i] += $row["user"] . " (" . $row["seats"] . " passengers) ";
//                        array_push($passNumber, $row["seats"]);
//                        array_push($rowString, $row["user"] . " (" . $row["seats"] . " passengers) ");

                    }
                }
        }
    } else {
        $type = 0;
        $data = "Impossible preparing output";
        echo json_encode(array("t" => $type, "d" => $data));
    }

    for ($i = 0; $i <= count($segments); $i++) {
        if ($logged == 1) {
            $data = $data . "<tr><td>" . $segments[$i] . "</td><td>Total: " . $passNumber[$i] . "</td><td>" . $rowString[$i] . "</td></tr>";
        } else {
            $data = $data . "<tr><td>" . $segments[$i] . "</td><td>Total: " . $passNumber[$i];
        }

    }

    if($i == count($segments)) {
        $type = 1;
    }

    echo json_encode(array("t" => $type, "d" => $data));
}