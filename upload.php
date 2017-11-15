<?php
/**
 * Script to handle uploading of video files
 * @param $_POST["deviceId"];
 * @param $_POST["videoTitle"];
 * @param $_POST["totalStreamlets"]
 * @param $_POST["streamletNo"];
 * @param $_FILES["fileToUpload"]["name"])
 */

if (empty($_POST["deviceId"]) || empty($_POST["videoTitle"] || empty($_POST["totalStreamlets"] || empty($_POST["streamletNo"])))) {
    exit("Expecting parameters deviceId, videoTitle, totalStreamlets, streamletNo");
}

//header('Content-type: application/json');
define ('SITE_ROOT', realpath(dirname(__FILE__)));
$servername = "localhost";
$username = "team07";
$password = "cs5248team07";
$db = "team07";

$myfile = fopen("uploadphp.log", "w+") or die("Unable to open uploadphp.log!");
fwrite($myfile, "deviceId: ".$_POST["deviceId"]."\n");
fwrite($myfile, "videoTitle: ".$_POST["videoTitle"]."\n");
fwrite($myfile, "Total streamlets: ".$_POST["totalStreamlets"]."\n");
fwrite($myfile, "Streamlet no: ".$_POST["streamletNo"]."\n");

// Create connection
$conn = mysqli_connect($servername, $username, $password, $db);

// buffer all upcoming output
ob_start();

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    error_log("SQL connection failed", 0);
    fwrite($myfile, "SQL connection failed (Line " . __LINE__ . ")\n");
} else {
    echo "Connected to DB.<br/>";
}

$videoTitle =  basename($_POST["videoTitle"], ".mp4");

$target_dir = "video_repo/";
$target_file = $target_dir . $videoTitle . "/" . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

// Check if file already exists
if (file_exists($target_file)) {
    echo "Sorry, file ($target_file) already exists.<br/>";
    error_log("Failed - file already exists", 0);
    fwrite($myfile, "Failed - file already exists (Line " . __LINE__ . ")\n");

    $uploadOk = 0;
}

// Allow certain file formats
if($imageFileType == "mp4" || $imageFileType == "m4s" || $imageFileType == "ts") {
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
    $file_dir = SITE_ROOT."/video_repo/" . $videoTitle;
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
    $moveResult = move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], SITE_ROOT."/".$target_file);
    if ($moveResult) {
        echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.<br/>";
    } else {
        echo "Sorry, there was an error uploading your file.<br/>";
        error_log("Failed - error moving uploaded file.", 0);
        fwrite($myfile, "Failed - error moving uploaded file (Line " . __LINE__ . ")\n");
    }
}

// get the size of the output
$outputSize = ob_get_length();

// send headers to tell the browser to close the connection
header("Content-Length: $outputSize");
header('Connection: close');

// Flush all output
fflush($myfile);
ob_end_flush();
ob_flush();
flush();

// close current session
// if (session_id()) session_write_close();

// Start background processes

$findQuery = "SELECT * FROM UPLOAD_VIDEO WHERE VIDEO_TITLE = '" . $videoTitle. "';";

$uploadedNumberOfStreamlets = 0;
$newNumberOfStreamlets = 0;

$insertQuery = "INSERT INTO UPLOAD_VIDEO (`IDX`, `UPLOAD_DEVICE_ID`, `VIDEO_TITLE`, `TOTAL_NUMBER_OF_STREAMLETS`, `UPLOADED_NUMBER_OF_STREAMLETS`, `LAST_UPLOAD_TIME`, `LAST_TRANSCODED_STREAMLET_240P`, `LAST_TRANSCODED_STREAMLET_360P`, `LAST_TRANSCODED_STREAMLET_480P`) VALUES (NULL, '" . $_POST["deviceId"] . "', '" . $videoTitle . "', " . $_POST["totalStreamlets"] . ", '1', CURRENT_TIMESTAMP, '0', '0', '0');";

$findResult = $conn->query($findQuery);
if ($findResult->num_rows > 0) {
    // Existing record in database, update number of streamlets.
    $row = $findResult->fetch_assoc();
    $idx = $row["IDX"];
    $uploadedNumberOfStreamlets = $row["UPLOADED_NUMBER_OF_STREAMLETS"];

    $newNumberOfStreamletsStr = shell_exec("ls -l $file_dir/*.mp4 | ws -l");
    $newNumberOfStreamlets = (integer) $uploadedNumberOfStreamlets;

    $updateQuery = "UPDATE UPLOAD_VIDEO SET `UPLOADED_NUMBER_OF_STREAMLETS` = ". $newNumberOfStreamlets  ." WHERE IDX = ". $idx .";" ;
    $updateResult = $conn->query($updateQuery);

    fwrite($myfile, "Performing update query...\n");
    if (!$updateResult) {
        error_log("Failed - update query", 0);
        fwrite($myfile, "Failed - update query. Reason: ". $conn->error. " (Line " . __LINE__ . ")\n");
        fwrite($myfile, $updateQuery."\n)");
    } else {
        fwrite($myfile, "Updated SQL record successfully.\n");
    }
} else {
    // No existing records in database, new video with first streamlet to be stored.
    $insertResult = $conn->query($insertQuery);
    if (!$insertResult) {
        fwrite($myfile, "Failed - insert query. Reason: ".$conn->error. " (Line " . __LINE__ . ")\n");
        fwrite($myfile, $insertQuery."\n)");

    } else {
        fwrite($myfile, "Added SQL record successfully.\n");
    }
}

$initialAvailableVideos = 0;
// Retrieve initial count of AVAILABLE_VIDEOS
$initialAvailableResult = $conn->query("SELECT COUNT(*) FROM AVAILABLE_VIDEOS;");
if (!$initialAvailableResult) {
    fwrite($myfile, "Query for inital number of available videos failed.\n");
} else {
    while ($row = $initialAvailableResult->fetch_assoc()) {
        fwrite($myfile, "Total number of available videos: ". $row["COUNT(*)"] . "\n");
        $initialAvailableVideos = $row["COUNT(*)"];
    }
}

$transcodeCommand240p = SITE_ROOT."/transcode.sh " . $target_file . " 240p ". $file_dir . " ". $_POST["streamletNo"]; 
$transcodeCommand360p = SITE_ROOT."/transcode.sh " . $target_file . " 360p ". $file_dir . " ". $_POST["streamletNo"];
$transcodeCommand480p = SITE_ROOT."/transcode.sh " . $target_file . " 480p ". $file_dir . " ". $_POST["streamletNo"];

fwrite($myfile, "Transcode command: ". $transcodeCommand240p . "\n");
exec($transcodeCommand240p, $output240p, $status240p);
fwrite($myfile, "Transcode 240p exit status ".$status240p.", output: ".$output240p[0]."\n");
if (end($output240p) == "[0][0]") {
    $quality = "240p";
    include("updateSqlTranscodeStatus.php");
}

exec($transcodeCommand360p, $output360p, $status360p);
fwrite($myfile, "Transcode 360p exit status ".$status360p.", output: ".$output360p[0]."\n");
if (end($output360p) == "[0][0]") {
    $quality = "360p";
    include("updateSqlTranscodeStatus.php");
}
exec($transcodeCommand480p, $output480p, $status480p);

fwrite($myfile, "Transcode 480p exit status ".$status480p.", output: ".$output480p[0]."\n");
if (end($output480p) == "[0][0]") {
    $quality = "480p";
    include("updateSqlTranscodeStatus.php");
}

fwrite($myfile, "Uploading and processing of video streamlet $streamletNo completed.\n");

$finalAvailableVideos = 0;
// Retrieve final count of AVAILABLE_VIDEOS
$finalAvailableResult = $conn->query("SELECT COUNT(*) FROM AVAILABLE_VIDEOS;");
if (!$finalAvailableResult) {
    fwrite($myfile, "Query for final number of available videos failed.\n");
} else {
    while ($row = $finalAvailableResult->fetch_assoc()) {
        fwrite($myfile, "Total number of available videos: ". $row["COUNT(*)"] . "\n");
        $finalAvailableVideos = $row["COUNT(*)"];
    }
}

if ($finalAvailableVideos - $initialAvailableVideos == 0) {
    fwrite($myfile, "No change in total number of available videos.\n");
} else {
    fwrite($myfile, "New available video. To create playlists...\n");
    createM3U8VariantPlaylist($videoTitle);
    fflush($myfile);
    createMpdPlaylist($videoTitle);
    fflush($myfile);
    fwrite($myfile, "Uploading and processing of video [$videoTitle] completed.\n");
}

/**
 * 1. Generates the m3u8 playlists for 240p, 360p and 480p streams
 * 2. Generates the variant playlist for the video
 * 3. Update to list.m3u8.json (read by HTML/PHP file m3u8list.php)
 */
function createM3U8VariantPlaylist($videoName) {
    global $myfile;
    $videoDir =  "video_repo/". $videoName;

    $playlist240pDir = $videoDir . "/240p/240p.m3u8";
    $m3u8_240p = fopen($playlist240pDir, "a+") or fwrite($myfile, "Unable to write to $playlist240pDir!\n");

    $playlist360pDir = $videoDir . "/360p/360p.m3u8";
    $m3u8_360p = fopen($playlist360pDir, "a+") or fwrite($myfile, "Unable to write to $playlist360pDir!\n");

    $playlist480pDir = $videoDir . "/480p/480p.m3u8";
    $m3u8_480p = fopen($playlist480pDir, "a+") or fwrite($myfile, "Unable to write to $playlist480pDir!\n");

    // read helper file
    $lines240p = file("video_repo/$videoName/240p/tslist.txt"); // returns array with each line in each array element
    fwrite($myfile, "Number of lines in $videoName/240p/tslist.txt: ". sizeof($lines240p) . "\n");

    $totalStreamlets = sizeof($lines240p);
    
    foreach ($lines240p as &$arrline) {
        $arrline = explode(" ", $arrline);
    }
    unset($arrline);

    // for each streamlet
    for ($streamletNo = 0; $streamletNo < $totalStreamlets; ++$streamletNo) {
        $streamlet240pArr = getStreamletResult($videoName, $streamletNo, 240);
        $streamlet360pArr = getStreamletResult($videoName, $streamletNo, 360);
        $streamlet480pArr = getStreamletResult($videoName, $streamletNo, 480);

        fwrite($m3u8_240p, "#EXTINF:".$streamlet240pArr["duration"]."\n");
        fwrite($m3u8_240p, $streamlet240pArr["uri"] . "\n");

        fwrite($m3u8_360p, "#EXTINF:".$streamlet360pArr["duration"]."\n");
        fwrite($m3u8_360p, $streamlet360pArr["uri"] . "\n");

        fwrite($m3u8_480p, "#EXTINF:".$streamlet480pArr["duration"]."\n");
        fwrite($m3u8_480p, $streamlet480pArr["uri"] . "\n");
    }

    fwrite($m3u8_240p, "#EXT-X-ENDLIST");
    fclose($m3u8_240p);

    fwrite($m3u8_360p, "#EXT-X-ENDLIST");
    fclose($m3u8_360p);

    fwrite($m3u8_480p, "#EXT-X-ENDLIST");
    fclose($m3u8_480p);

    fwrite($myfile, "Completed quality playlists.\n");
    
    $videoM3U8Dir = $videoDir."/".$videoName.".m3u8";
    $videoM3U8File = fopen($videoM3U8Dir, "w+") or die ("Unable to create $videoM3U8File");
    fwrite($videoM3U8File, "#EXTM3U\n");
    fwrite($videoM3U8file, "\#EXT-X-INDEPENDENT-SEGMENTS\n");
    fwrite($videoM3U8File, "#EXT-X-STREAM-INF:PROGRAM-ID=1,BANDWIDTH=2800000,CODECS=\"avc1.64001F,mp4a.40.2\",RESOLUTION=854x480,FRAME-RATE=30.000\n");
    fwrite($videoM3U8File, "480p/480p.m3u8\n");

    fwrite($videoM3U8File, "#EXT-X-STREAM-INF:PROGRAM-ID=1,BANDWIDTH=2000000,CODECS=\"avc1.64001F,mp4a.40.2\",RESOLUTION=640x360,FRAME-RATE=30.000\n");
    fwrite($videoM3U8File, "360p/360p.m3u8\n");

    fwrite($videoM3U8File, "#EXT-X-STREAM-INF:PROGRAM-ID=1,BANDWIDTH=900000,CODECS=\"avc1.64001F,mp4a.40.2\",RESOLUTION=426x240,FRAME-RATE=30.000\n");
    fwrite($videoM3U8File, "240p/240p.m3u8\n");

    fclose($videoM3U8File);

    // Add to src/list.m3u8.json
    $fullVideoUri = "http://monterosa.d2.comp.nus.edu.sg/~team07/video_repo/$videoName/$videoName.m3u8";

    $json = file_get_contents("src/list.m3u8.json");
    $originalJsonDecode = json_decode($json, JSON_OBJECT_AS_ARRAY);
    fwrite($myfile, "Existing size in JSON list: " . sizeof($originalJsonDecode) ."\n");
    $newJsonEntry = array(
        "name" => $videoName,
        "uri" => $fullVideoUri
    );
    array_unshift($originalJsonDecode, $newJsonEntry);
    fwrite($myfile, "JSON entry prepared.\n");
    
    $newJsonStr = json_encode($originalJsonDecode, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    fwrite($myfile, "New Json Str: " . $newJsonStr . "\n");
    $newJsonFile = fopen("src/list.m3u8.json", "w+") or fwrite($myfile, "Unable to open list.m3u8.json!");
    fwrite($newJsonFile, $newJsonStr . "\n");
    fclose($newJsonFile);
} // function createM3U8VariantPlaylist($videoName)

/**
 * 1. Generates the MPD playlist for the video
 * 2. Update to list.media.json (read by player app)
 */
function createMpdPlaylist($videoName) {
    global $myfile, $mpd;
    $videoDir = "video_repo/".$videoName;
    
    // READ INI FILE
    $mpdIniFile = parse_ini_file("mpd.ini"); // returns array of configurations
    $videoCodec = $mpdIniFile["videoCodec"];
    $minBufferTimeSec = $mpdIniFile["minBufferTimeSec"];
    $baseUrlPrefix = $mpdIniFile["baseUrlPrefix"];

    // GET TOTAL VIDEO DURATION
    $lines240p = file("video_repo/".$videoName."/240p/mp4list.txt"); // returns array with each line in each array element
    fwrite($myfile, "Number of lines in $videoName/240p/mp4list.txt: " . sizeof($lines240p) . "\n");

    $totalStreamlets = sizeof($lines240p);
    
    foreach ($lines240p as &$arrline) {
        $arrline = explode(" ", $arrline);
    }
    unset($arrline);

    // $lines240p is now an array
    $duration = 0;

    for ($line = 0; $line < $totalStreamlets; ++$line) {
        $duration += floatval($lines240p[$line][2]);
    }

    fwrite($myfile, "Total duration of video in seconds: " . $duration . "\n");

    $videoMpdDir = $videoDir."/".$videoName.".mpd";
    
    $mpd = new XmlWriter();
    $mpd->openURI($videoMpdDir);
    $mpd->setIndent(TRUE);

    $mpd->startDocument("1.0", "UTF-8");

    $mpd->startElement("MPD");
        $mpd->writeAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
        $mpd->writeAttribute("xmlns", "urn:mpeg:dash:schema:mpd:2011");
        $mpd->writeAttribute("xsi:schemaLocation", "urn:mpeg:dash:schema:mpd:2011 DASH-MPD.xsd");
        $mpd->writeAttribute("type", "static");
        $mpd->writeAttribute("mediaPresentationDuration", "PT".$duration."S");
        $mpd->writeAttribute("minBufferTime", "PT".$minBufferTimeSec."S");
        $mpd->writeAttribute("profiles", "urn:mpeg:dash:profile:isoff-on-demand:2011");

    for ($streamletNo = 0; $streamletNo < $totalStreamlets; ++$streamletNo) {
        // each will return directory of streamlet file for streamlet number
        $streamlet240p = searchStreamletNo($videoName, $streamletNo, 240);
        $streamlet360p = searchStreamletNo($videoName, $streamletNo, 360);
        $streamlet480p = searchStreamletNo($videoName, $streamletNo, 480);

        generateAdaptationSets($videoCodec, $streamletNo, $streamlet480p, $streamlet360p, $streamlet240p);
    }

    $mpd->endElement(); // MPD
    $mpd->endDocument();
    $mpd->flush();

    // Add MPD playlist to list.media.json
    $fullVideoUri = "http://monterosa.d2.comp.nus.edu.sg/~team07/video_repo/$videoName/$videoName.mpd";

    $json = file_get_contents("src/list.media.json");
    $originalJsonDecode = json_decode($json, JSON_OBJECT_AS_ARRAY);
    fwrite($myfile, "Existing size in JSON list: " . sizeof($originalJsonDecode) ."\n");
    $newJsonEntry = array(
        "name" => $videoName,
        "uri" => $fullVideoUri
    );
    array_unshift($originalJsonDecode, $newJsonEntry);
    fwrite($myfile, "JSON entry prepared.\n");
    
    $newJsonStr = json_encode($originalJsonDecode, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    fwrite($myfile, "New Json Str: " . $newJsonStr);
    $newJsonFile = fopen("src/list.media.json", "w+") or die("Unable to open list.media.json!");
    fwrite($newJsonFile, $newJsonStr);
    fclose($newJsonFile);
} // function createMpdPlaylist($videoName)

/**
 * @param integer $quality(240, 360, 480)
 * @return array single element array with uri and duration
 */
function getStreamletResult($videoName, $streamletNo, $quality) {
    global $myfile;
    $helperFile = file("video_repo/".$videoName."/".$quality."p/tslist.txt");
    $totalStreamlets = sizeof($helperFile);

    foreach($helperFile as &$arrline) {
        $arrline = explode(" ", $arrline);
    }
    unset($arrline); // break reference

    $found = -1;
    $streamletNoStr = (string) $streamletNo;
    fwrite($myfile, "[$quality] Searching for streamlet " . $streamletNoStr . "...\n");
    for ($lineNo = 0; $lineNo < $totalStreamlets; ++$lineNo) {
        $found = array_search($streamletNoStr, $helperFile[$lineNo]);
        if ($found === 0) {
            fwrite($myfile, "Found streamlet " . $streamletNoStr . " in line " . ($lineNo+1) . "\n");
            fwrite($myfile, "URI of streamlet " . $streamletNoStr . " is " . $helperFile[$lineNo][1] . "\n");
            $returnObj = array(
                "uri" => trim($helperFile[$lineNo][1]),
                "duration" => trim($helperFile[$lineNo][2])
            );
            return $returnObj;
        } else {
            fwrite($myfile, "Not on line ". ($lineNo+1).".\n");
            $found = -1;
        }
    }
} // function getStreamletResult($videoName, $streamletNo, $quality)


/**
 * Helper function to retrieve the full URL of the streamlet number stored in the helper file.
 * @param string $videoName
 * @param integer $streamletNo
 * @param integer $quality
 */
function searchStreamletNo($videoName, $streamletNo, $quality) {
    global $myfile;
    $helperFile = file("video_repo/".$videoName."/".$quality."p/mp4list.txt");

    $totalStreamlets = sizeof($helperFile);

    foreach($helperFile as &$arrline) {
        $arrline = explode(" ", $arrline);
    }
    unset($arrline); // break reference

    $found = -1;
    $streamletNoStr = (string) $streamletNo;
    fwrite($myfile, "[$quality] Searching for streamlet " . $streamletNoStr . "...\n");
    for ($lineNo = 0; $lineNo < $totalStreamlets; ++$lineNo) {
        $found = array_search($streamletNoStr, $helperFile[$lineNo]);
        if ($found === 0) {
            fwrite($myfile, "Found streamlet " . $streamletNoStr . " in line " . ($lineNo+1) . "\n");
            fwrite($myfile, "URL of streamlet " . $streamletNoStr . " is " . $helperFile[$lineNo][1] . "\n");
            return trim($helperFile[$lineNo][1]);
        } else {
            fwrite($myfile, "Not on line ". ($lineNo+1).".\n");
            $found = -1;
        }
    }
} // function searchStreamletNo($videoName, $streamletNo, $quality)

function generateAdaptationSets($videoCodec, $streamletNo, $streamlet480p, $streamlet360p, $streamlet240p) {
    global $myfile, $mpd;
    $mpd->startElement("Period");
    $mpd->writeAttribute("id", ($streamletNo + 1));
    $mpd->startElement("AdaptationSet");
        $mpd->writeAttribute("mimeType", "video/mp4");
        $mpd->writeAttribute("codecs", $videoCodec);
        $mpd->writeAttribute("subsegmentAlignment", "true");
        $mpd->writeAttribute("subsegmentStartsWithSAP", "1");

        $mpd->startElement("Representation");
            $mpd->writeAttribute("id", "480p");
            $mpd->writeAttribute("bandwidth", "2800000");
            $mpd->writeAttribute("width", "854");
            $mpd->writeAttribute("height", "480");

            $mpd->writeElement("BaseURL", $streamlet480p);

        $mpd->endElement(); // Representation

        $mpd->startElement("Representation");
            $mpd->writeAttribute("id", "360p");
            $mpd->writeAttribute("bandwidth", "2000000");
            $mpd->writeAttribute("width", "640");
            $mpd->writeAttribute("height", "360");

            $mpd->writeElement("BaseURL", $streamlet360p);

        $mpd->endElement(); // Representation

        $mpd->startElement("Representation");
            $mpd->writeAttribute("id", "240p");
            $mpd->writeAttribute("bandwidth", "1000000");
            $mpd->writeAttribute("width", "426");
            $mpd->writeAttribute("height", "240");

            $mpd->writeElement("BaseURL", $streamlet240p);

        $mpd->endElement(); // Representation
    $mpd->endElement(); // AdaptationSet
$mpd->endElement(); // Period
} // function generateAdaptationSets($streamletNo, $streamlet480p, $streamlet360p, $streamlet240p)

mysqli_close($conn);
fclose($myfile);

?>