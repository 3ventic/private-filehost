#!/bin/bash

FILEHOSTROOT="/path/to/web/root"

cd $FILEHOSTROOT
for f in *.gif; do
	if [ ! -f ${f/.gif/.mp4} ]; then
		SIZE1=$(stat -c %s $f)
		sleep 10
		SIZE2=$(stat -c %s $f)
		if [[ "$SIZE1" -eq "$SIZE2" ]]; then
			ffmpeg -i $f -codec:v libx264 -preset placebo -an -pix_fmt yuv420p ${f/.gif/.mp4}
		fi
	fi
done

for f in *.mp4; do
	if [ ! -f ${f/.mp4/.gif} ]; then
		SIZE1=$(stat -c %s $f)
		sleep 10
		SIZE2=$(stat -c %s $f)
		if [[ "$SIZE1" -eq "$SIZE2" ]]; then
			mkdir /tmp/frames
			ffmpeg -i $f -r 30 /tmp/frames/frame%04d.png
			convert -delay 5 -loop 0 /tmp/frames/frame*.png ${f/.mp4/.gif}
			rm -rf /tmp/frames
		fi
	fi
done

# change user if required, or comment out
chown filehost:filehost $FILEHOSTROOT/*.mp4 -R
chown filehost:filehost $FILEHOSTROOT/*.gif -R
