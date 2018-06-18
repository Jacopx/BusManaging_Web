<?php
include 'base.php';

if(isset($_POST['field1']) && isset($_POST['field2']) && isset($_POST['field3']) && isset($_POST['field4'])) {
    makeReservation($_POST['field1'], $_POST['field2'], $_POST['field3'], $_POST['field4']);
}

function makeReservation($user, $start, $end, $number) {
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

    $sql = "SELECT SUM(seats) FROM Reservations WHERE start>='$start' AND end<='$end'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        // output data of each row
        while($row = mysqli_fetch_assoc($result)) {
            if (($row["SUM(seats)"] + $number) <= BUS_SIZE) {
                $allow = 1;
            } else {
                // @TODO: Fix seats count
                $type = -1;
                $data = "Adding not possible! Not enough seats on the bus!";
                echo json_encode(array("t" => $type, "d" => $data));
                die();
            }
        }
    } else {
        $type = 0;
        $data = "Internal error: user not found";
    }

    if ($allow == 1) {
        $sql = "INSERT INTO Reservations VALUES ('$user','$number','$start','$end')";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            $type = 1;
            $data = "Reservation added";
        } else {
            $type = -2;
            $data = "Reservation ERROR!";
        }
    }

    echo json_encode(array("t" => $type, "d" => $data));
}