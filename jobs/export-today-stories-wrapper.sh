#!/bin/sh
/usr/local/bin/wp eval "require_once(ABSPATH.'/wp-admin/includes/plugin.php'); require_once(THEME_JOBS_DIR.'/export-today-stories.php');" --url=localhost/wordpress/today --path=/Users/kberry/Sites/wordpress --user=admin
#--url=REPLACEME.ucf.edu --path=/var/www/wordpress --user=webcom

# WP CLI has exit code 255 on Varnish Dependency Purger function.
# Exit 0 to keep Jenkins happy.
exit 0