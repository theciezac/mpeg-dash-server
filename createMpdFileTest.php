<?php
// $hostServer = $_SERVER['HTTP_HOST'];
// $httpDir = $hostServer . "/~team07";
// $httpVideoDir = $httpDir . "/video_repo";
// $hostServer = $argv[1]; // should be monterosa.d2.comp.nus.edu.sg
// $videoName = $argv[2];


$xml = new XmlWriter();
// $xml->openURI("video_repo/".$videoName."/".$videoName.".mpd");

$xml->openURI("test.mpd");
$xml->setIndent(TRUE);

$xml->startDocument("1.0", "UTF-8");

$xml->startElement("MPD");
$xml->writeAttribute("xmlns", "urn:mpeg:dash:schema:mpd:2011");
$xml->writeAttribute("minBufferTime", "PT1.5S");
$xml->writeAttribute("type", "static");
$xml->writeAttribute("mediaPresentationDuration", "PT3.000000S");
$xml->writeAttribute("profiles", "urn:mpeg:dash:profile:isoff-main:2011");
$xml->writeElement("BaseURL", "http://www-itec.uni-klu.ac.at/ftp/datasets/DASHDataset2014/BigBuckBunny/4sec/bunny_45226bps/");

$xml->startElement("Period");
$xml->writeAttribute("duration", "PT3.0S");

$xml->startElement("AdaptationSet");
$xml->writeAttribute("segmentAlignment", "true");
$xml->writeAttribute("maxWidth", "854");
$xml->writeAttribute("maxHeight", "480");
$xml->writeAttribute("maxFrameRate", "30");
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
$xml->writeAttribute("mimeType", "video/avc");
$xml->writeAttribute("codecs", "avc1,mp4a");
$xml->writeAttribute("width", "854");
$xml->writeAttribute("height", "480");
$xml->writeAttribute("frameRate", "30");
// $xml->writeAttribute("sar", "1:1");
$xml->writeAttribute("startWithSAP", "1");
$xml->writeAttribute("bandwidth", "2000000");

$xml->writeElement("BaseURL", "BigBuckBunny_4snonSeg.mp4");

$xml->startElement("SegmentBase");
$xml->writeAttribute("indexRangeExact", "true");
$xml->writeAttribute("indexRange", "891-2722");
$xml->startElement("Initialization");
$xml->writeAttribute("range", "0-890");
$xml->endElement();

$xml->endElement(); // SegmentBase

$xml->endElement(); // Representation

// $xml->startElement("Representation");
// $xml->writeAttribute("id", "HIGH");
// $xml->writeAttribute("mimeType", "video/mp4");
// $xml->writeAttribute("codecs", "avc1,mp4a");
// $xml->writeAttribute("width", "854");
// $xml->writeAttribute("height", "480");
// $xml->writeAttribute("frameRate", "30");
// // $xml->writeAttribute("sar", "1:1");
// $xml->writeAttribute("audioSamplingRate", "128000");
// $xml->writeAttribute("bandwidth", "2000000");

// $xml->startElement("SegmentList");
// $xml->writeAttribute("duration", "3");

// // TODO loop
// $xml->startElement("BaseURL");
// $xml->writeAttribute("media", "480p/"."SampleVideo_1280x720_1mb_480p".".mp4");
// $xml->endElement(); // SegmentURL

// $xml->endElement(); // SegmentList
// $xml->endElement(); // Representation HIGH

// $xml->startElement("Representation");
// $xml->writeAttribute("id", "MEDIUM");
// $xml->writeAttribute("mimeType", "video/mp4");
// $xml->writeAttribute("codecs", "avc1,mp4a");
// $xml->writeAttribute("width", "640");
// $xml->writeAttribute("height", "360");
// $xml->writeAttribute("frameRate", "30");
// // $xml->writeAttribute("sar", "1:1");
// $xml->writeAttribute("audioSamplingRate", "128000");
// $xml->writeAttribute("bandwidth", "1000000");

// $xml->startElement("SegmentList");
// $xml->writeAttribute("duration", "3");

// // TODO loop
// $xml->startElement("SegmentURL");
// $xml->writeAttribute("media", "360p/"."SampleVideo_1280x720_1mb_360p".".mp4");
// $xml->endElement(); // SegmentURL

// $xml->endElement(); // SegmentList
// $xml->endElement(); // Representation MEDIUM

// $xml->startElement("Representation");
// $xml->writeAttribute("id", "LOW");
// $xml->writeAttribute("mimeType", "video/mp4");
// $xml->writeAttribute("codecs", "avc1,mp4a");
// $xml->writeAttribute("width", "426");
// $xml->writeAttribute("height", "240");
// $xml->writeAttribute("frameRate", "30");
// // $xml->writeAttribute("sar", "1:1");
// $xml->writeAttribute("audioSamplingRate", "64000");
// $xml->writeAttribute("bandwidth", "1000000");

// $xml->startElement("SegmentList");
// $xml->writeAttribute("duration", "3");

// // TODO loop
// $xml->startElement("SegmentURL");
// $xml->writeAttribute("media", "240p/"."SampleVideo_1280x720_1mb_240p".".mp4");
// $xml->endElement(); // SegmentURL

// $xml->endElement(); // SegmentList
// $xml->endElement(); // Representation LOW

$xml->endElement(); // Adaptation set

$xml->endElement(); // Period
$xml->endElement(); // MPD
$xml->endDocument();
$xml->flush();
// shell_exec("")
?>