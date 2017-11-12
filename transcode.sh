#bash
url_base="http://monterosa.d2.comp.nus.edu.sg/~team07/"
FILE_NAME=$1
QUALITY=$2
VIDEO_DIR=$3
STREAMLET_NUMBER=$4
input_file_cmd="ffmpeg -i "$FILE_NAME
quality_cmd=""
case $QUALITY in
"240p") quality_cmd=" -b:v:700k -b:a:64k -filter:v scale=426:240 " ;;
"360p") quality_cmd=" -b:v:1000k -b:a:128k -filter:v scale=640:360 " ;;
"480p") quality_cmd=" -b:v:2000k -b:a:128k -filter:v scale=854:480 " ;;
*) ;;
esac

OUTPUT_FILE=$(dirname "${FILE_NAME}")
BASE_FILENAME=$(basename "${FILE_NAME}")


# TRANSCODE to mp4 USING FFMPEG
output_file_cmd=$OUTPUT_FILE"/"$QUALITY"/"$BASE_FILENAME
#%".mp4"}"_"$QUALITY".mp4"
echo "Transcode command: "$input_file_cmd$quality_cmd$output_file_cmd >> $OUTPUT_FILE"/"$QUALITY"/"transcodeLog.log
echo "Output: " >> $OUTPUT_FILE"/"$QUALITY"/"transcodeLog.log
$input_file_cmd$quality_cmd$output_file_cmd >> $OUTPUT_FILE"/"$QUALITY"/"transcodeLog.log
ffmpeg_transcode_exit_status=$?
#ffmpeg -i ../videos/SampleVideo_1280x720_2mb.mp4 -b:v 1000k -b:a 128k -filter:v \"scale=w=640:h=360 -bsf:v h264_mp4toannexb  360p/SampleVideo_1280x720_2mb_360p.ts

#APPEND TO TXT FILE TO LIST STREAMLETS IN ORDER
mp4_streamlet_duration_in_seconds=$(ffprobe -v error -select_streams v:0 -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 $output_file_cmd)
echo $STREAMLET_NUMBER $url_base$output_file_cmd $mp4_streamlet_duration_in_seconds >> $OUTPUT_FILE/$QUALITY/mp4list.txt 

## ONCE TRANSCODED, CONVERT EVERY VERSION INTO AN MPEG-2 TRANSPORT STREAM.
ts_input_file=$output_file_cmd
ts_output_file=${ts_input_file%".mp4"}".ts"
echo "Convert command: "mp42ts $ts_input_file $ts_output_file >> $OUTPUT_FILE"/"$QUALITY"/"transcodeLog.log
echo "Output: " >> $OUTPUT_FILE"/"$QUALITY"/"transcodeLog.log
mp42ts $ts_input_file $ts_output_file >> $OUTPUT_FILE"/"$QUALITY"/"transcodeLog.log
mp42ts_convert_exit_status=$?

#APPEND TO FILE TO LIST STREAMLETS IN ORDER
m3u8file=$OUTPUT_FILE/$QUALITY/$QUALITY.m3u8
if [ ! -f $m3u8file ]
then
echo \#EXTM3U >> $m3u8file
echo \#EXT-X-VERSION=3 >> $m3u8file
echo \#EXT-X-MEDIA-SEQUENCE:0 >> $m3u8file
echo \#EXT-X-PLAYLIST-TYPE:VOD >> $m3u8file
chmod 777 $m3u8file
fi

ts_streamlet_duration_in_seconds=$(ffprobe -v error -select_streams v:0 -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 $ts_output_file)
echo $STREAMLET_NUMBER $url_base$ts_output_file $ts_streamlet_duration_in_seconds >> $OUTPUT_FILE/$QUALITY/tslist.txt
echo $(basename "${ts_output_file}") >> $m3u8file
#SEND BACK TO upload.php
echo "["$ffmpeg_transcode_exit_status"]["$mp42ts_convert_exit_status"]"
