<?php
/**
 * Created by PhpStorm.
 * User: wangxiaohua
 * Date: 2017/5/17
 * Time: 下午1:28
 */

$metrics_name_map['ucd_cpu'] = 'system.cpu';
$metrics_name_map['ucd_mem'] = 'system.mem';
$metrics_name_map['ucd_load'] = 'system.load';
$metrics_name_map['ucd_diskio'] = 'system.disk.io';

$metrics_name_map['storage'] = 'system.disk';
$metrics_name_map['processors'] = 'system.processor';

$metrics_name_map['mempool'] = 'system.mem';
$metrics_name_map['ipSystemStats'] = 'system.net.ip';

$metrics_name_map['hr_processes'] = 'system.proc';
$metrics_name_map['hr_users'] = 'system.core.user';

$metrics_name_map['uptime'] = 'system.uptime';

$metrics_name_map['toner'] = 'system.printer.toner';

if(isset($metrics_name_map[$measurement])) {
    $measurement = $metrics_name_map[$measurement];
}
