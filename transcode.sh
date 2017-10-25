#bash
FILE_NAME=$1
QUALITY=$2
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

output_file_cmd=$OUTPUT_FILE"/"$QUALITY"/"${BASE_FILENAME%".mp4"}"_"$QUALITY".mp4"
$input_file_cmd$quality_cmd$output_file_cmd
#ffmpeg -i ../videos/SampleVideo_1280x720_2mb.mp4 -b:v 1000k -b:a 128k -filter:v \"scale=w=640:h=36$

