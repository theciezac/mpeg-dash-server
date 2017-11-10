#bash
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
$input_file_cmd$quality_cmd$output_file_cmd
ffmpeg_transcode_exit_status=$?
#ffmpeg -i ../videos/SampleVideo_1280x720_2mb.mp4 -b:v 1000k -b:a 128k -filter:v \"scale=w=640:h=360 -bsf:v h264_mp4toannexb  360p/SampleVideo_1280x720_2mb_360p.ts

#APPEND TO TXT FILE TO LIST STREAMLETS IN ORDER
echo $STREAMLET_NUMBER $output_file_cmd >> $OUTPUT_FILE/$QUALITY/mp4list.txt 

## ONCE TRANSCODED, CONVERT EVERY VERSION INTO AN MPEG-2 TRANSPORT STREAM.
ts_input_file=$output_file_cmd
ts_output_file=${ts_input_file%".mp4"}".ts"
mp42ts $ts_input_file $ts_output_file
mp42ts_convert_exit_status=$?

#APPEND TO TXT FILE TO LIST STREAMLETS IN ORDER
echo $STREAMLET_NUMBER $ts_output_file >> $OUTPUT_FILE/$QUALITY/tslist.txt

#SEND BACK TO upload.php
echo "["$ffmpeg_transcode_exit_status"]["$mp42ts_convert_exit_status"]"


# # UPDATE SQL TABLE
# video_title=$(basename ${VIDEO_DIR})
# /usr/bin/php /home/team07/public_html/updateSqlTranscodeStatus.php $video_title $QUALITY >> /home/team07/public_html/updateCmd$QUALITY.log
# echo "\$QUALITY: "$QUALITY"<br/>"
# echo "Command is: /usr/bin/php /home/team07/public_html/updateSqlTranscodeStatus.php " $video_title " "$QUALITY " >> /home/team07/public_html/updateCmd"$QUALITY".log<br/>"
# echo "Result: "$? "<br/>" 