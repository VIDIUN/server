#!/bin/bash

#
# batchMgr      This shell script takes care of starting and stopping a Vidiun Batch Service
#
# chkconfig: 2345 13 87
# description: Vidiun Batch

### BEGIN INIT INFO
# Provides:          vidiun-batch
# Required-Start:    $local_fs $remote_fs $network
# Required-Stop:     $local_fs $remote_fs $network
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# X-Interactive:     true
# Short-Description: Start/stop Vidiun batch server
# Description:       Control the Vidiun batch server.
### END INIT INFO


# Source function library.
. /etc/vidiun.d/system.ini

# Define variables
BATCHDIR=$APP_DIR/batch
BATCHEXE=VGenericBatchMgr.class.php
CONFIG_FILE=$APP_DIR/configurations/batch
LOCKFILE="$BASE_DIR/var/run/batch.pid"
GREEN='\e[1;32m'
RED='\e[1;31m'
NORMAL='\e[0m'

if [ $# -ne 1 ]; then
    echo "Usage: $0 [start|stop|force-stop|restart|status]"
    exit 1  
fi

echo_status() {
    echo -n "$1"
    if [ $2 -eq 0 ]; then
        echo -e "\t[$GREEN    OK    $NORMAL]"
    else
        echo -e "\t[$RED  FAILED  $NORMAL]"
    fi
}

get_pids() {
    KP=`ps axf | awk '!/\\_ / {b=0} /php [K]GenericBatchMgr.class.php/ {b=1} b{print $1}'|xargs`
    if [ -s "$LOCKFILE" ]; then
        KP_PARENT=`cat $LOCKFILE 2>/dev/null`
    else
        KP_PARENT=`pgrep -P 1 -f [K]GenericBatchMgr.class.php|xargs 2>/dev/null`
        echo_status "No pid file found at $LOCKFILE getting pid by pgrep [$KP_PARENT]" 0
    fi
}

start() {
    if [ -r $BASE_DIR/maintenance ]; then
        echo "Server is on maintenance mode - batchMgr will not start!"
        return 1
    fi
    
    get_pids
    if [ -r "$LOCKFILE" ]; then
        kill -0 $KP_PARENT > /dev/null
        if [ $? -eq 0 ]; then
            echo_status "Service Batch Manager already running [$KP_PARENT]" 0
            return 0
        else
            echo "Service Batch Manager isn't running but stale lock file exists"
            echo "Removing stale lock file $LOCKFILE"
            rm -f $LOCKFILE
            start_scheduler
            return $?
        fi
    elif [ -n "$KP" ]; then
        echo "Batch Manager is running as $KP without $LOCKFILE"
        start_scheduler
        return $?
    else
        start_scheduler
        return $? 
    fi
}

start_scheduler() {
    echo -n "Starting Batch Manager."
    cd $BATCHDIR
    echo -n "."
    mkdir -p $BASE_DIR/var/run
    chown $OS_VIDIUN_USER:$OS_VIDIUN_USER $BASE_DIR/var/run
    su $OS_VIDIUN_USER -c "nohup $PHP_BIN $BATCHEXE $PHP_BIN $CONFIG_FILE >> $LOG_DIR/vidiun_batch.log 2>&1 &"
    echo -n "."
    if [ "$?" -eq 0 ]; then
        echo ". "
        sleep 1
        get_pids
        echo_status "Batch Manager started with PID $KP_PARENT" 0
        return 0
    else
        echo ". "
        echo_status "Failed to start Batch Manager" 1
        return 1
    fi
}

show_status() {
    get_pids
    if [ -n "$KP" ]; then
        echo_status "Batch Manager running with PID $KP_PARENT" 0
        return 0
    else
        echo_status "Service Batch Manager isn't running" 1
        return 1
    fi
}

stop() {
    echo "Stopping Batch Manager.... "
    get_pids
    SIGNAL=$1
    if [ -n "$KP" ]; then
        if [ -r $BASE_DIR/keepAlive ]; then
            echo "Server is on Keep Alive mode - workers won't be killed!"
            echo_status "Killing Batch Manager with PID $KP_PARENT" 0
            kill -s $SIGNAL $KP_PARENT > /dev/null
        else
            echo_status "Killing Batch Manager with PID $KP_PARENT and related workers" 0
            kill -s $SIGNAL $KP > /dev/null
        fi
        if [ -e "$LOCKFILE" ]; then
            rm -f $LOCKFILE
        fi
        RC=$?
    else
        echo_status "Service Batch Manager not running" 1
        RC=1
    fi
    return $RC
}

case "$1" in
    start)
        start
        ;;
    stop)
        stop 15
        ;;
    force-stop)
        stop 9
        ;;
    status)
        show_status
        ;;
    restart)
        stop 15
        start
        ;;
    *)
        echo "Usage: [start|stop|force-stop|restart|status]"
        exit 1
        ;;
esac
exit $?
