<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2009 The Cacti Group                                 |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDTool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
 | This code is designed, written, and maintained by the Cacti Group. See  |
 | about.php and/or the AUTHORS file for specific developer information.   |
 +-------------------------------------------------------------------------+
 | http://www.cacti.net/                                                   |
 +-------------------------------------------------------------------------+
 */
# testing only
$no_http_headers = true;

chdir("../../../cacti-main");
include("./include/global.php");
include_once(CACTI_BASE_PATH . "/lib/rrd.php");
$rrd_info = rrdtool_function_info('154');
$class = rrdtool_cacti_compare('154', $rrd_info);
rrdtool_info2html($rrd_info, $class);
?>



# test rrd information function
# this will read rrd file information for existing file and
# compare it to the definitions in cacti for that file
# tests are based on a traffic data source

# start configuration
# path: http://localhost/workspace/cacti-main
# data source id: 154
# switch info mode on: info=1
# http://localhost/workspace/cacti-main/data_sources.php?action=data_source_edit&id=154&info=1

# the given rrd file is created by the following rrdtool statement
/usr/bin/rrdtool create /var/www/html/workspace/cacti-main/rra/foobar_traffic_in_154.rrd --step 300 DS:traffic_in:COUNTER:600:0:10000000 DS:traffic_out:COUNTER:600:0:10000000 RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 RRA:MAX:0.5:1:600 RRA:MAX:0.5:6:700 RRA:MAX:0.5:24:775 RRA:MAX:0.5:288:797

# step
/usr/bin/rrdtool create /var/www/html/workspace/cacti-main/rra/foobar_traffic_in_154.rrd --step 60 DS:traffic_in:COUNTER:600:0:10000000 DS:traffic_out:COUNTER:600:0:10000000 RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 RRA:MAX:0.5:1:600 RRA:MAX:0.5:6:700 RRA:MAX:0.5:24:775 RRA:MAX:0.5:288:797

# change ds name
/usr/bin/rrdtool create /var/www/html/workspace/cacti-main/rra/foobar_traffic_in_154.rrd --step 300 DS:traffic_wrong:COUNTER:600:0:10000000 DS:traffic_out:COUNTER:600:0:10000000 RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 RRA:MAX:0.5:1:600 RRA:MAX:0.5:6:700 RRA:MAX:0.5:24:775 RRA:MAX:0.5:288:797

# additional ds name
/usr/bin/rrdtool create /var/www/html/workspace/cacti-main/rra/foobar_traffic_in_154.rrd --step 300 DS:traffic_in:COUNTER:600:0:10000000 DS:traffic_out:COUNTER:600:0:10000000 DS:traffic_new:COUNTER:600:0:10000000 RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 RRA:MAX:0.5:1:600 RRA:MAX:0.5:6:700 RRA:MAX:0.5:24:775 RRA:MAX:0.5:288:797

# missing ds name
/usr/bin/rrdtool create /var/www/html/workspace/cacti-main/rra/foobar_traffic_in_154.rrd --step 300 DS:traffic_out:COUNTER:600:0:10000000 RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 RRA:MAX:0.5:1:600 RRA:MAX:0.5:6:700 RRA:MAX:0.5:24:775 RRA:MAX:0.5:288:797

# wrong ds type
/usr/bin/rrdtool create /var/www/html/workspace/cacti-main/rra/foobar_traffic_in_154.rrd --step 300 DS:traffic_in:GAUGE:600:0:10000000 DS:traffic_out:COUNTER:600:0:10000000 RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 RRA:MAX:0.5:1:600 RRA:MAX:0.5:6:700 RRA:MAX:0.5:24:775 RRA:MAX:0.5:288:797

# wrong heartbeat
/usr/bin/rrdtool create /var/www/html/workspace/cacti-main/rra/foobar_traffic_in_154.rrd --step 300 DS:traffic_in:COUNTER:450:0:10000000 DS:traffic_out:COUNTER:600:0:10000000 RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 RRA:MAX:0.5:1:600 RRA:MAX:0.5:6:700 RRA:MAX:0.5:24:775 RRA:MAX:0.5:288:797

# wrong min
/usr/bin/rrdtool create /var/www/html/workspace/cacti-main/rra/foobar_traffic_in_154.rrd --step 300 DS:traffic_in:COUNTER:600:1:10000000 DS:traffic_out:COUNTER:600:0:10000000 RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 RRA:MAX:0.5:1:600 RRA:MAX:0.5:6:700 RRA:MAX:0.5:24:775 RRA:MAX:0.5:288:797

# wrong max
/usr/bin/rrdtool create /var/www/html/workspace/cacti-main/rra/foobar_traffic_in_154.rrd --step 300 DS:traffic_in:COUNTER:600:0:1000000000 DS:traffic_out:COUNTER:600:0:10000000 RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 RRA:MAX:0.5:1:600 RRA:MAX:0.5:6:700 RRA:MAX:0.5:24:775 RRA:MAX:0.5:288:797

# xff
/usr/bin/rrdtool create /var/www/html/workspace/cacti-main/rra/foobar_traffic_in_154.rrd --step 300 DS:traffic_in:COUNTER:600:0:10000000 DS:traffic_out:COUNTER:600:0:10000000 RRA:AVERAGE:0.6:1:600 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 RRA:MAX:0.5:1:600 RRA:MAX:0.5:6:700 RRA:MAX:0.5:24:775 RRA:MAX:0.5:288:797

# additional CF level as a CF/STEPS dup
/usr/bin/rrdtool create /var/www/html/workspace/cacti-main/rra/foobar_traffic_in_154.rrd --step 300 DS:traffic_in:COUNTER:600:0:10000000 DS:traffic_out:COUNTER:600:0:10000000 RRA:AVERAGE:0.5:1:111 RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 RRA:MAX:0.5:1:600 RRA:MAX:0.5:6:700 RRA:MAX:0.5:24:775 RRA:MAX:0.5:288:797

# additional CF level
/usr/bin/rrdtool create /var/www/html/workspace/cacti-main/rra/foobar_traffic_in_154.rrd --step 300 DS:traffic_in:COUNTER:600:0:10000000 DS:traffic_out:COUNTER:600:0:10000000 RRA:AVERAGE:0.5:2:111 RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 RRA:MAX:0.5:1:600 RRA:MAX:0.5:6:700 RRA:MAX:0.5:24:775 RRA:MAX:0.5:288:797

# missing CF level
/usr/bin/rrdtool create /var/www/html/workspace/cacti-main/rra/foobar_traffic_in_154.rrd --step 300 DS:traffic_in:COUNTER:600:0:10000000 DS:traffic_out:COUNTER:600:0:10000000 RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 RRA:MAX:0.5:1:600 RRA:MAX:0.5:6:700 RRA:MAX:0.5:24:775 RRA:MAX:0.5:288:797

# different CDPs
/usr/bin/rrdtool create /var/www/html/workspace/cacti-main/rra/foobar_traffic_in_154.rrd --step 300 DS:traffic_in:COUNTER:600:0:10000000 DS:traffic_out:COUNTER:600:0:10000000 RRA:AVERAGE:0.5:1:666 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 RRA:MAX:0.5:1:600 RRA:MAX:0.5:6:700 RRA:MAX:0.5:24:775 RRA:MAX:0.5:288:797
/usr/bin/rrdtool create /var/www/html/workspace/cacti-main/rra/foobar_traffic_in_154.rrd --step 300 DS:traffic_in:COUNTER:600:0:10000000 DS:traffic_out:COUNTER:600:0:10000000 RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:6:654 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 RRA:MAX:0.5:1:600 RRA:MAX:0.5:6:700 RRA:MAX:0.5:24:775 RRA:MAX:0.5:288:797

# multiple differences
/usr/bin/rrdtool create /var/www/html/workspace/cacti-main/rra/foobar_traffic_in_154.rrd --step 200 DS:traffic_in:GAUGE:450:0:1000000 DS:traffic_out:COUNTER:600:1:10000000 RRA:AVERAGE:0.6:1:666 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 RRA:MAX:0.5:1:600 RRA:MAX:0.5:6:700 RRA:MAX:0.5:24:705 RRA:MAX:0.5:288:797


