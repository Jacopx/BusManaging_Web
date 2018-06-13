<?php
include 'base.php';

if(isset($_POST['field1']) && isset($_POST['field2'])) {
    signup($_POST['field1'], $_POST['field2']);
}

function signup($user, $pass) {
    $type = -3; $data = -3;
    $conn = mysqli_connect(SQL_HOST, SQL_USER, SQL_PASS);

    if (mysqli_connect_errno()) {
        die("Internal error: connection to DB failed ".
            mysqli_connect_error());
    }
    if (!mysqli_select_db($conn, SQL_DB)) {
        die("Internal error: selection of DB failed");
    }

    $sql = "SELECT * FROM Users WHERE user='$user'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        // output data of each row
        while($row = mysqli_fetch_assoc($result)) {
            $type = -1;
            $data = 0;
            break;
        }
    } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $sql = "INSERT INTO Users VALUES ('$user','$hash')";

        if (mysqli_query($conn, $sql)) {
            $type = 1;
            $data = 0;
        } else {
            $type = -2;
            $data = "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
    }

    echo json_encode(array("t" => $type, "d" => $data));
}