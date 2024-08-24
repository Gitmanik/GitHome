#!/bin/sh
/usr/local/bin/supercronic -quiet -overlapping 'private/crontab' &
P1=$!
apache2-foreground &
P2=$!
wait $P1 $P2