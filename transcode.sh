#bash
FILE_NAME=$1
QUALITY=$2
VIDEO_DIR=$3
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
#ffmpeg -i ../videos/SampleVideo_1280x720_2mb.mp4 -b:v 1000k -b:a 128k -filter:v \"scale=w=640:h=360 -bsf:v h264_mp4toannexb  360p/SampleVideo_1280x720_2mb_360p.ts


## ONCE TRANSCODED, CONVERT EVERY VERSION INTO AN MPEG-2 TRANSPORT STREAM.
ts_input_file=$output_file_cmd
ts_output_file=${ts_input_file%".mp4"}".ts"
mp42ts $ts_input_file $ts_output_file

# UPDATE SQL TABLE


php updateSqlTranscodeStatus.php ${basename $VIDEO_DIR} $2 &;