<?php
// executed command should be
// php updateSqlTranscodeStatus.php fileName quality idx
// $videoTitle = $argv[1]; 
// $quality = $argv[2];
echo "Updating sql transcode status for ".$quality."...";
//$videoTitle = $argv[3];

// $servername = "localhost";
// $username = "team07";
// $password = "cs5248team07";
// $db = "team07";

$updateLogFile = fopen("updateSqlTranscodephp".$quality.".log", "w+") or die("Unable to open file!");
fwrite($updateLogFile, "videoTitle: ".$videoTitle."\n");
fwrite($updateLogFile, "quality: ".$quality."\n");
fwrite($updateLogFile, "videoTitle: ".$videoTitle."\n");


// Create connection
// $conn = mysqli_connect($servername, $username, $password, $db);

// Check connection
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// } else {
//     echo "Connected to DB.<br/>";
// }

$updateFindQuery = "SELECT * FROM UPLOAD_VIDEO WHERE VIDEO_TITLE = '" . $videoTitle . "';";

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

fwrite($updateLogFile, "transcodeColumn: ".$transcodeColumn."\n");

fwrite($updateLogFile, "Find query:\n".$updateFindQuery."\n");

$updateFindResult = $conn->query($updateFindQuery);
if ($updateFindResult->num_rows > 0) {
    // Existing record in database, update number of streamlets.
    fwrite($updateLogFile, "Find result number of rows: ".$updateFindResult->num_rows."\n");
    $row = $updateFindResult->fetch_assoc();
    $idx = $row["IDX"];
    $transcodedNumberOfStreamlets = $row[$transcodeColumn];
    $newNumberOfTranscoded = $transcodedNumberOfStreamlets + 1;

    $updateUpdateQuery = "UPDATE UPLOAD_VIDEO SET `$transcodeColumn` = $newNumberOfTranscoded  WHERE IDX = $idx;" ;
    fwrite($updateLogFile, "Update query: \n".$updateUpdateQuery."\n");
    $updateUpdateResult = $conn->query($updateUpdateQuery);

    echo "Performing update query...";
    if (!$updateUpdateResult) {
        echo "Update query failed. Reason: " . $conn->error."<br/>";
        fwrite ($updateLogFile,"Update query failed. Reason: " . $conn->error."\n");
        fwrite ($updateLogFile, "Update query:\n" . $updateUpdateQuery);
    } else {
        echo "Updated record successfully.";
        fwrite($updateLogFile, "Updated successfully.");
    }
}

// mysqli_close($conn);
fclose($updateLogFile);
?>