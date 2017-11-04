<?php
// PARAMETERS EXPECTED
// $_POST["deviceId"];
// $_POST["videoTitle"];
// $_POST["streamlets"]
// $_FILES["fileToUpload"]["name"])

//header('Content-type: application/json');

$servername = "localhost";
$username = "team07";
$password = "cs5248team07";
$db = "team07";

$myfile = fopen("uploadphp.log", "w") or die("Unable to open file!");
fwrite($myfile, "deviceId: ".$_POST["deviceId"]."\n");
fwrite($myfile, "videoTitle: ".$_POST["videoTitle"]."\n");
fwrite($myfile, "(number of) streamlets: ".$_POST["streamlets"]."\n");

// ini_set ( string $varname , string $newvalue )
ini_set("upload_max_filesize", "40M");
ini_set("post_max_size", "40M");

// Create connection
$conn = mysqli_connect($servername, $username, $password, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    error_log("SQL connection failed", 0);
    fwrite($myfile, "SQL connection failed (Line " . __LINE__ . ")\n");
} else {
    echo "Connected to DB.<br/>";
}


$videoTitle =  $_POST["videoTitle"];

$target_dir = "video_repo/";
$target_file = $target_dir . $_POST["videoTitle"] . "/" . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

// Check if file already exists
if (file_exists($target_file)) {
    echo "Sorry, file ($target_file) already exists.<br/>";
    error_log("Failed - file already exists", 0);
    fwrite($myfile, "Failed - file already exists (Line " . __LINE__ . ")\n");

    $uploadOk = 0;
}
// Check file size
// if ($_FILES["fileToUpload"]["size"] > 4320000) {
//     echo "Sorry, your file is too large.<br/>";
//     error_log("Failed - file size too large", 0);
//     fwrite($myfile, "Failed - file size too large (Line " . __LINE__ . ")");
//     $uploadOk = 0;
// }
// Allow certain file formats
if($imageFileType == "mp4" || $imageFileType == "m4s" || $imageFileType = "ts") {
    echo "File type: " . $imageFileType . "<br/>";
} else {
    echo "Sorry, only MP4/M4S/TS file types are allowed.<br/>";
    error_log("Failed - file format invalid", 0); 
    fwrite($myfile, "Failed - file format invalid (Line " . __LINE__ . ")\n");

    $uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.<br/>";
    error_log("Failed - file not uploaded", 0);
        fwrite($myfile, "Failed - file not uploaded (Line " . __LINE__ . ")\n");

// if everything is ok, try to upload file
} else {
    $file_dir = "video_repo/" . $_POST["videoTitle"];
    if (!file_exists($file_dir)) {
        shell_exec ("mkdir " . $file_dir);
        shell_exec ("chmod 777 " . $file_dir);

        shell_exec ("mkdir " . $file_dir . "/240p");
        shell_exec ("chmod 777 " . $file_dir . "/240p");

        shell_exec ("mkdir " . $file_dir . "/360p");
        shell_exec ("chmod 777 " . $file_dir . "/360p");

        shell_exec ("mkdir " . $file_dir . "/480p");
        shell_exec ("chmod 777 " . $file_dir . "/480p");
    }
    $moveResult = move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);
    if ($moveResult) {
        echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.<br/>";
    } else {
        echo "Sorry, there was an error uploading your file.<br/>";
        error_log("Failed - error moving uploaded file.", 0);
        fwrite($myfile, "Failed - error moving uploaded file (Line " . __LINE__ . ")\n");
    }
}

$findQuery = "SELECT * FROM UPLOAD_VIDEO WHERE VIDEO_TITLE = '" . $_POST["videoTitle"]. "';";

$uploadedNumberOfStreamlets = 0;
$newNumberOfStreamlets = 0;

$insertQuery = "INSERT INTO UPLOAD_VIDEO (`IDX`, `UPLOAD_DEVICE_ID`, `VIDEO_TITLE`, `TOTAL_NUMBER_OF_STREAMLETS`, `UPLOADED_NUMBER_OF_STREAMLETS`, `LAST_UPLOAD_TIME`, `LAST_TRANSCODED_STREAMLET_240P`, `LAST_TRANSCODED_STREAMLET_360P`, `LAST_TRANSCODED_STREAMLET_480P`) VALUES (NULL, '" . $_POST["deviceId"] . "', '" . $_POST["videoTitle"] . "', " . $_POST["streamlets"] . ", '1', CURRENT_TIMESTAMP, '0', '0', '0');";

$findResult = $conn->query($findQuery);
if ($findResult->num_rows > 0) {
    // Existing record in database, update number of streamlets.
    $row = $findResult->fetch_assoc();
    $idx = $row["IDX"];
    $uploadedNumberOfStreamlets = $row["UPLOADED_NUMBER_OF_STREAMLETS"];
    $newNumberOfStreamlets = $uploadedNumberOfStreamlets + 1;

    $updateQuery = "UPDATE UPLOAD_VIDEO SET `UPLOADED_NUMBER_OF_STREAMLETS` = ". $newNumberOfStreamlets  ." WHERE IDX = ". $idx .";" ;
    $updateResult = $conn->query($updateQuery);

    echo "Performing update query...";
    if (!$updateResult) {
        echo "Update query failed. Reason: " . $conn->error."<br/>";
        error_log("Failed - update query", 0);
        fwrite($myfile, "Failed - update query. Reason: ". $conn->error. " (Line " . __LINE__ . ")\n");
        fwrite($myfile, $updateQuery."\n)");
        

    } else {
        echo "Updated record successfully.";
    }
} else {
    // No existing records in database, new video with first streamlet to be stored.
    $insertResult = $conn->query($insertQuery);
    echo "Performing insert query...";
    if (!$insertResult) {
        echo "Insert query failed. Reason: ".$conn->error."<br/>";
        fwrite($myfile, "Failed - insert query. Reason: ".$conn->error. " (Line " . __LINE__ . ")\n");
        fwrite($myfile, $insertQuery."\n)");

    } else {
        echo "Added record successfully.";
    }
}
echo "<br/></p><p>";

$transcodeCommand240p = "nohup ./transcode.sh " . $target_file . " 240p ". $file_dir . " &>> video_repo/" . $_POST["videoTitle"] . "/nohup.out &";
$transcodeCommand360p = "nohup ./transcode.sh " . $target_file . " 360p ". $file_dir . " &>> video_repo/" . $_POST["videoTitle"] . "/nohup.out &";
$transcodeCommand480p = "nohup ./transcode.sh " . $target_file . " 480p ". $file_dir . " &>> video_repo/" . $_POST["videoTitle"] . "/nohup.out &";

$runningtranscoderstr = shell_exec("echo running transcoder...");
echo $runningtranscoderstr;

$e1 = shell_exec($transcodeCommand240p);
echo $transcodeCommand240p;
echo $e1;

$e2 = shell_exec($transcodeCommand360p);
echo $transcodeCommand360p;
echo $e2;

$e3 = shell_exec($transcodeCommand480p);
echo $transcodeCommand480p;
echo $e3;

$createMpdFileCommand =  "php createMpdFile.php " . $_POST["videoTitle"];

exec($createMpdFileCommand);

echo "</p>";
mysqli_close($conn);
fclose($myfile);

?>