<?php
// $hostServer = $_SERVER['HTTP_HOST'];
// $httpDir = $hostServer . "/~team07";
// $httpVideoDir = $httpDir . "/video_repo";
$hostServer = $argv[1]; // should be monterosa.d2.comp.nus.edu.sg
$videoName = $argv[2];


$xml = new XmlWriter();
$xml->openURI("video_repo/".$videoName."/".$videoName.".mpd");
$xml->setIndent(TRUE);

$xml->startDocument("1.0", "UTF-8");

$xml->startElement("MPD");
$xml->writeAttribute("xmlns", "urn:mpeg:dash:schema:mpd:2011");
$xml->writeAttribute("minBufferTime", "PT5.333S");
$xml->writeAttribute("type", "static");
$xml->writeAttribute("mediaPresentationDuration", "PT5.333S");
$xml->writeAttribute("profiles", "urn:mpeg:dash:profile:isoff-on-demand:2011");
$xml->writeElement("BaseURL", "http://".$hostServer."/~team07/video_repo/".$videoName."/480p");

$xml->startElement("Period");
$xml->writeAttribute("duration", "PT5.333S");

$xml->startElement("AdaptationSet");
$xml->writeAttribute("segmentAlignment", "true");
$xml->writeAttribute("maxWidth", "854");
$xml->writeAttribute("maxHeight", "480");
$xml->writeAttribute("maxFrameRate", "25");
$xml->writeAttribute("subsegmentStartsWithSAP", "1");
// // $xml->writeAttribute("par", "16:9");

// $xml->startElement("ContentComponent");
// $xml->writeAttribute("id", "1");
// $xml->writeAttribute("contentType", "video");
// $xml->endElement(); // ContentComponent

// $xml->startElement("ContentComponent");
// $xml->writeAttribute("id", "2");
// $xml->writeAttribute("contentType", "audio");
// $xml->endElement(); // ContentComponent

$xml->startElement("Representation");
$xml->writeAttribute("id", "480p");
$xml->writeAttribute("mimeType", "video/mp4");
$xml->writeAttribute("codecs", "avc1.64001E");
$xml->writeAttribute("width", "854");
$xml->writeAttribute("height", "480");
$xml->writeAttribute("frameRate", "35");
$xml->writeAttribute("startWithSAP", "1");
$xml->writeAttribute("bandwidth", "2000000");

$xml->writeElement("BaseURL", "SampleVideo_1280x720_1mb_480p".".mp4");

$xml->startElement("SegmentBase");
$xml->writeAttribute("indexRangeExact", "true");
$xml->writeAttribute("indexRange", "891-2722");
$xml->startElement("Initialization");
$xml->writeAttribute("range", "0-890");
$xml->endElement();

$xml->endElement(); // SegmentBase

$xml->endElement(); // Representation

$xml->endElement(); // Adaptation set

$xml->endElement(); // Period
$xml->endElement(); // MPD
$xml->endDocument();
$xml->flush();
// shell_exec("")
?>