<?php

$os = getHostOS($device);
if ($os != $device['os']) {
    log_event('Device OS changed ' . $device['os'] . " => $os", $device, 'system', 3);
    $device['os'] = $os;
    $sql = dbUpdate(array('os' => $os), 'devices', 'device_id=?', array($device['device_id']));

    if (!isset($config['os'][$device['os']])) {
        load_os($device);
    }

    echo "Changed OS! : $os\n";
}

postData2api(json_encode(array('os' => $os,'logo' => getImageName($device, false))),'deviceos','device_id='.$device['device_id']);

update_device_logo($device);
