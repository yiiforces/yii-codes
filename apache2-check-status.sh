#!/bin/bash

## para registrar crontab como root:
#  https://www.cyberciti.biz/faq/linux-execute-cron-job-after-system-reboot/
#  @reboot /usr/bin/apache2-check-status.sh
sleep 60
while true
do
	echo "init a2 watchdog loop" > /tmp/a2ck.log;
    
    RETVAL=0
    /usr/sbin/apachectl configtest > /dev/null 2>&1
    RETVAL=$?

    #echo $RETVAL
    if [ $RETVAL -ne  0 ]; then
        echo "Apache2 archivo de configuracion inalido" >> /tmp/a2ck.log
        continue
    fi

    status=$(curl -I -s  localhost:80 | grep HTTP)
    if [ "$status" != "" ]; then
        echo "http headers "$status  >> /tmp/a2ck.log
        #echo "http headers "$status;
        sleep 1
        continue;
    fi

    status=$(service apache2 status | grep 'Active: active (running)')
    if [ "$status" != "" ]; then
        echo "apache2 restart" >> /tmp/a2ck.log
        #echo "apache2 restart"
		/etc/init.d/apache2 restart
    else
        echo "apache2 start" >> /tmp/a2ck.log
        #echo "apache2 start"
        /etc/init.d/apache2 start
    fi
    sleep 5
done
