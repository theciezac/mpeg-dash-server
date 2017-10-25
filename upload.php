<?php
//header('Content-type: application/json');
include 'store.php';

$servername = "localhost";
$username = "team07";
$password = "cs5248team07";
$db = "team07";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Connected to DB.<br/>";
}

echo "Number of streamlets: " . $_POST["streamlets"] . "<br/>";
echo "Device ID: " . $_POST["deviceId"] . "<br/>";

$target_dir = "video_repo/";
$target_file = $target_dir . $_POST["videoTitle"] . "/" . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

// Check if file already exists
if (file_exists($target_file)) {
    echo "Sorry, file ($target_file) already exists.<br/>";
    $uploadOk = 0;
}
// Check file size
if ($_FILES["fileToUpload"]["size"] > 2160000) {
    echo "Sorry, your file is too large.<br/>";
    $uploadOk = 0;
}
// Allow certain file formats
if($imageFileType == "mp4" || $imageFileType == "m4s" || $imageFileType = "ts") {
    echo "File type: " . $imageFileType . "<br/>";
} else {
    echo "Sorry, only MP4/M4S/TS file types are allowed.<br/>";
    $uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.<br/>";
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
    }
}

$findQuery = "SELECT * FROM UPLOAD_VIDEO WHERE VIDEO_TITLE = '" . $_POST["videoTitle"]. "';";


//UPDATE `upload_video` SET `LAST_UPLOADED_STREAMLET` = '2' WHERE `upload_video`.`IDX` = 1


$uploadedNumberOfStreamlets = 0;
$newNumberOfStreamlets = 0;

$insertQuery = "INSERT INTO UPLOAD_VIDEO (`IDX`, `UPLOAD_DEVICE_ID`, `VIDEO_TITLE`, `TOTAL_NUMBER_OF_STREAMLETS`, `UPLOADED_NUMBER_OF_STREAMLETS`, `LAST_UPLOAD_TIME`, `LAST_TRANSCODED_STREAMLET_240P`, `LAST_TRANSCODED_STREAMLET_360P`, `LAST_TRANSCODED_STREAMLET_480P`) VALUES (NULL, " . $_POST["deviceId"] . ", '" . $_POST["videoTitle"] . "', " . $_POST["streamlets"] . ", '1', CURRENT_TIMESTAMP, '0', '0', '0');";

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
    } else {
        echo "Updated record successfully.";
    }
} else {
    // No existing records in database, new video with first streamlet to be stored.
    $insertResult = $conn->query($insertQuery);
    echo "Performing insert query...";
    if (!$insertResult) {
        echo "Insert query failed. Reason: ".$conn->error."<br/>";
    } else {
        echo "Added record successfully.";
    }
}
echo "<br/></p><p>";

//$transcodeThread = new RunScriptThread();
$transcodeCommand240p = "nohup ./transcode.sh " . $target_file . " 240p &>> video_repo/" . $_POST["videoTitle"] . "/nohup.out &";
$transcodeCommand360p = "nohup ./transcode.sh " . $target_file . " 360p &>> video_repo/" . $_POST["videoTitle"] . "/nohup.out &";
$transcodeCommand480p = "nohup ./transcode.sh " . $target_file . " 480p &>> video_repo/" . $_POST["videoTitle"] . "/nohup.out &";

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


echo "</p>";
mysqli_close($conn);
?>