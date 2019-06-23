#!/bin/sh

# stop process gracefully
quit()
{
    # kill nginx
    echo 'stopping nginx'
    kill -TERM $(ps aux |grep nginx |grep -v grep |awk '{ print $1 }')

    # kill php-fpm
    echo 'stopping php-fpm'
    kill -QUIT $(ps aux |grep php-fpm |grep -v grep |awk '{ print $1 }')

    exit 0
}

trap 'quit' QUIT

# Start nginx
nginx
status=$?
if [ $status -ne 0 ]; then
  echo "Failed to start nginx: $status"
  exit $status
fi

# Start php-fpm
docker-php-entrypoint php-fpm &

"$@"

# Naive check runs checks once a minute to see if either of the processes exited.
# This illustrates part of the heavy lifting you need to do if you want to run
# more than one service in a container. The container exits with an error
# if it detects that either of the processes has exited.
# Otherwise it loops forever, waking up every 1 seconds

while sleep 1; do
  ps aux |grep nginx |grep -q -v grep
  NGINX_STATUS=$?
  ps aux |grep php-fpm |grep -q -v grep
  PHP_FPM_STATUS=$?
  # If the greps above find anything, they exit with 0 status
  # If they are not both 0, then something is wrong
  if [ $NGINX_STATUS -ne 0 -o $PHP_FPM_STATUS -ne 0 ]; then
    echo "One of the processes has already exited."
    exit 1
  fi
done