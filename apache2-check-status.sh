#!/bin/bash

## para registrar crontab como root:
#  https://www.cyberciti.biz/faq/linux-execute-cron-job-after-system-reboot/
#  @reboot sleep 300 /usr/bin/apache2-check-status.sh

while true
do
    sleep 1
    RETVAL=0
    /usr/sbin/apachectl configtest > /dev/null 2>&1
    RETVAL=$?

    #echo $RETVAL
    if [ $RETVAL -ne  0 ]; then
        echo "Apache2 archivo de configuracion inalido"
        continue
    fi

    status=""
    status=$(curl -I -s  localhost:80 | grep HTTP)
    if [ "$status" == "" ]; then
        service apache2 restart
        #echo "apache2 restart";
    fi
done
