<?php
// executed command should be
// php updateSqlTranscodeStatus.php fileName quality idx
$videoTitle = $argv[1]; 
$quality = $argv[2];
$videoTitle = $argv[3];

$servername = "localhost";
$username = "team07";
$password = "cs5248team07";
$db = "team07";

$myfile = fopen("updateSqlTranscodephp.log", "w") or die("Unable to open file!");
fwrite($myfile, "videoTitle: ".$videoTitle."\n");
fwrite($myfile, "quality: ".$quality."\n");
fwrite($myfile, "videoTitle: ".$videoTitle."\n");


// Create connection
$conn = mysqli_connect($servername, $username, $password, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Connected to DB.<br/>";
}

$findQuery = "SELECT * FROM UPLOAD_VIDEO WHERE VIDEO_TITLE = '" . $videoTitle . "';";

switch ($quality) {
    case "480p":
    $transcodeColumn = "LAST_TRANSCODED_STREAMLET_480P";
    break;
    case "360p":
    $transcodeColumn = "LAST_TRANSCODED_STREAMLET_360P";
    break;
    case "240p":
    $transcodeColumn = "LAST_TRANSCODED_STREAMLET_240P";
    break;
    default:
    break;
}
$findResult = $conn->query($findQuery);
if ($findResult->num_rows > 0) {
    // Existing record in database, update number of streamlets.
    $row = $findResult->fetch_assoc();
    $idx = $row["IDX"];
    $transcodedNumberOfStreamlets = $row[$transcodeColumn];
    $newNumberOfTranscoded = $transcodedNumberOfStreamlets + 1;

    $updateQuery = "UPDATE UPLOAD_VIDEO SET `. $transcodeColumn .` = ". $newNumberOfStreamlets  ." WHERE IDX = ". $idx .";" ;
    $updateResult = $conn->query($updateQuery);

    echo "Performing update query...";
    if (!$updateResult) {
        echo "Update query failed. Reason: " . $conn->error."<br/>";
    } else {
        echo "Updated record successfully.";
    }
}

mysqli_close($conn);
fclose($myfile);
?>