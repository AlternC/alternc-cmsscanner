PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin
# Every 5 minutes, do cmsscanner actions
*/5 * * * *	   alterncpanel		/usr/bin/flock -n /var/lock/cmsscanner.lock -c /usr/lib/alternc/update_cmsscanner

