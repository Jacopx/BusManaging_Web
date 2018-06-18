<?php
include 'base.php';

if(isset($_POST['field1']) && isset($_POST['field2'])) {
    signup($_POST['field1'], $_POST['field2']);
}

function signup($user, $pass) {
    //@TODO: Adding value verification before push in DB
    $type = -3; $data = -3;
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

    $sql = "SELECT * FROM Users WHERE user='$user'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        // output data of each row
        while($row = mysqli_fetch_assoc($result)) {
            // FAIL
            $type = -1;
            $data = 0;
            break;
        }
    } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $sql = "INSERT INTO Users VALUES ('$user','$hash')";

        if (mysqli_query($conn, $sql)) {
            // SUCCESS
            $type = 1;
            $data = 0;
        } else {
            // FAIL
            $type = -2;
            $data = "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
    }

    echo json_encode(array("t" => $type, "d" => $data));
}