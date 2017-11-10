<?php
fwrite($myfile, "Updating sql transcode status for ".$quality."...\n");

$updateLogFile = fopen("updateSqlTranscodephp".$quality.".log", "w+") or die("Unable to open file!");
fwrite($updateLogFile, "videoTitle: ".$videoTitle."\n");
fwrite($updateLogFile, "quality: ".$quality."\n");
fwrite($updateLogFile, "videoTitle: ".$videoTitle."\n");

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
fwrite($updateLogFile, "Find query statement:\n".$updateFindQuery."\n");

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

    fwrite($myfile, "Performing update query...\n");
    if (!$updateUpdateResult) {
        fwrite ($updateLogFile,"Update query failed. Reason: " . $conn->error."\n");
        fwrite ($updateLogFile, "Update query:\n" . $updateUpdateQuery);
    } else {
        fwrite($updateLogFile, "Updated successfully.");
    }
}

fclose($updateLogFile);
?>