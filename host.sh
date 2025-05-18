#!/bin/bash

cd /home/kevsipirdp/Desktop/CommonServices || exit
php -S 0.0.0.0:8000 > logs/server.log 2>&1 &

#to stop, run: pkill -f "php -S"
#or stop anything using port 8000: sudo fuser -k 8000/tcp
#or ps aux | grep "php -S"
#then kill <PID> manuall
