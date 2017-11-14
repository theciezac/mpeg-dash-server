<html>
<title>M3U8 videos available for playing</title>
<body>
<h1>List of M3U8 videos available for playing</h1>

<style>
html * {
    font-family: "Helvetica Neue", Helvetica, Arial, Sans-serif, Serif !important;
}
table, th, td {
    border: 1px solid black;
    padding: 5px;
}
</style>

<table style="border: 1px solid black">
    <tr>
        <th>S/N</th>
        <th style="max-width:400px">Video Title</th>
        <th>Link</th>
    </tr>

<?php
$m3u8jsonFile = file_get_contents("src/list.m3u8.json");

$m3u8json = json_decode($m3u8jsonFile, JSON_OBJECT_AS_ARRAY); // $json as array

$totalNoOfVideos = sizeof($m3u8json);

for ($videoNo = 0; $videoNo < $totalNoOfVideos; ++$videoNo) {
    echo 
        "<tr>
            <td>" . ($videoNo + 1) . "</td>
            <td>" . $m3u8json[$videoNo]["name"] . "</td>
            <td><a href=\"" . $m3u8json[$videoNo]["uri"] . "\" target=\"_blank\">Open in new tab</td>
        </tr>";
}

?>

</table>
</body>
</html>