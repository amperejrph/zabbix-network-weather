<?php
/*
	zabbixhost:hostname:itemkey
	itemkey such as 'ping item'
*/
class WeatherMapDataSource_zabbixhost extends WeatherMapDataSource {

	function Init(&$map)
	{
		return(TRUE);
	}

	function Recognise($targetstring)
	{
		return preg_match("/^zabbixhost:(.+):(.*)$/",$targetstring,$matches) ? true : false;
	}

	function ReadData($targetstring, &$map, &$item)
	{
		$data[IN] = 'unknown';
		$data[OUT] = 'unknown';
		$data_time = 0;
		$item->add_hint('status', 'disable');
		
		if(preg_match("/^zabbixhost:(.+):(.*)$/",$targetstring,$matches))
		{
			$host_name = $matches[1];
			$item_key = $matches[2];
			if (empty($item_key)) $item_key = 'icmpping';
			
			$weathermap_dir = getcwd();
			chdir('..');
			require_once('include/config.inc.php');
			
			$options = array(
				'nopermissions' => true,
				'filter' => array('host' => $host_name, 'key_' => $item_key),
				'output' => 'extend'
			);
			$item_ = array_shift(CItem::get($options));
			if(empty($item_)) warn("ZabbixItem ReadData: Not found item for host '$host_name' and key_ '$item_key'");
			
			chdir($weathermap_dir);
			
			$data[IN] = $data[OUT] = $item_['lastvalue'];
			$data_time = $item_['lastclock'];
			switch ($data[IN]) {
				case '':
					$item->add_hint('status', 'unknown');
					break;
				case 0:
					$item->add_hint('status', 'off');
					break;
				case 1:
					$item->add_hint('status', 'on');
					break;
			}
		}
		debug ("ZabbixHost ReadData: Returning (".($data[IN]===NULL?'NULL':$data[IN]).",".($data[OUT]===NULL?'NULL':$data[OUT]).",$data_time)\n");
		return array($data[IN], $data[OUT], $data_time);
	}
}
?>
