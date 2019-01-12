<?php

// The file is generated from /etc/cron.d/backup-size
// It's probably for cacti...

    $size = file_get_contents('/var/www/backup-size.dat');
 
    print "size:$size";
