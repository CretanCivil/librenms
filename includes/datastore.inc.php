<?php
/*
 * LibreNMS abstract data storage interface to both rrdtool & influxdb
 *
 * Copyright (c) 2016 Paul D. Gear <paul@librenms.org>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Filter all elements with keys that start with 'rrd_'
 *
 * @param array $arr input array
 * @return array Copy of $arr with all keys beginning with 'rrd_' removed.
 */
function rrd_array_filter($arr)
{
    $result = array();
    foreach ($arr as $k => $v) {
        if (strpos($k, 'rrd_') === 0) {
            continue;
        }
        $result[$k] = $v;
    }
    return $result;
} // rrd_array_filter


/**
 * Datastore-independent function which should be used for all polled metrics.
 *
 * RRD Tags:
 *   rrd_def     RrdDefinition
 *   rrd_name    array|string: the rrd filename, will be processed with rrd_name()
 *   rrd_oldname array|string: old rrd filename to rename, will be processed with rrd_name()
 *   rrd_step             int: rrd step, defaults to 300
 *
 * @param array $device
 * @param string $measurement Name of this measurement
 * @param array $tags tags for the data (or to control rrdtool)
 * @param array|mixed $fields The data to update in an associative array, the order must be consistent with rrd_def,
 *                            single values are allowed and will be paired with $measurement
 */
function data_update($device, $measurement, $tags, $fields)
{
    global $g_metric_data,$config;
    if($config['poll2agent']) {
        $pattern = $config['install_dir'] . '/metrics/*.inc.php';
        foreach (glob($pattern) as $file) {
            include $file;
        }
        unset($tags['rrd_def']);
        unset($tags['rrd_name']);
        //unset($tags['mib']);

        //$tags['host'] = 'snmp70';
        //$tags['uid'] = '20';

        foreach($tags as &$value) {
            $value = preg_replace("/[^\x{4e00}-\x{9fa5}A-Za-z0-9\.\-\/\xC2\xA0]/u","",$value);
        }
        $measurement = $measurement;//"snmp.".


        if (!is_array($fields)) {
            if ($fields != null && floatval($fields) !== null && !is_nan($fields)) {
                $point = new stdClass();
                $point->metric = $measurement;
                $point->tags = $tags;
                $point->value = floatval($fields);

                array_push($g_metric_data,$point);
            }
        } else {
            foreach ($fields as $k => $v) {

                if (floatval($v) !== null && $v != null && !is_nan($v)) {
                    $point = new stdClass();
                    $point->metric = $measurement.".".$k;
                    $point->tags = $tags;
                    $point->value = floatval($v);

                    array_push($g_metric_data,$point);
                }
            }
        }

        return;
    }



    // convenience conversion to allow calling with a single value, so, e.g., these are equivalent:
    // data_update($device, 'mymeasurement', $tags, 1234);
    //     AND
    // data_update($device, 'mymeasurement', $tags, array('mymeasurement' => 1234));
    if (!is_array($fields)) {
        $fields = array($measurement => $fields);
    }

    // rrdtool_data_update() will only use the tags it deems relevant, so we pass all of them.
    // However, influxdb saves all tags, so we filter out the ones beginning with 'rrd_'.

    rrdtool_data_update($device, $measurement, $tags, $fields);
    influx_update($device, $measurement, rrd_array_filter($tags), $fields);
    graphite_update($device, $measurement, $tags, $fields);
} // data_update
