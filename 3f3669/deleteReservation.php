<?php
include 'base.php';

if(isset($_POST['field1'])) {
    deleteReservation($_POST['field1']);
}

function deleteReservation($logged) {
    $type = -1; $data = -1;
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

    $sql = "DELETE FROM Reservations WHERE user='$logged'";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        $type = 1;
        $data = "Delete correctly performed!";
    } else {
        $type = 0;
        $data = "Internal error: user not found";
    }

    echo json_encode(array("t" => $type, "d" => $data));
}