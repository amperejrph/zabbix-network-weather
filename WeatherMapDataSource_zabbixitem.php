<?php
// Zabbix Pluggable datasource for PHP Weathermap 0.9
// - read a pair of values from a database, and return it
// Actually the plugin look only in history_uint table
// TARGET zabbixitem:host_in:item_key_in:host_out:item_key_out

class WeatherMapDataSource_zabbixitem extends WeatherMapDataSource {
	
	function Init(&$map)
	{
		return(TRUE);
	}
	
	function Recognise($targetstring)
	{
		return preg_match("/^zabbixitem:(.+):(.+):(.+):(.+)$/",$targetstring,$matches) ? true : false;
	}
	
	function ReadData($targetstring, &$map, &$item)
	{
		$data[IN] = NULL;
		$data[OUT] = NULL;
		$data_time = 0;

		if(preg_match("/^zabbixitem:(.+):(.+):(.+):(.+)$/",$targetstring,$matches))
		{
			$host_in = $matches[1];
			$item_key_in = $matches[2];
			$host_out = $matches[3];
			$item_key_out = $matches[4];
			
			$weathermap_dir = getcwd();
			chdir('..');
			require_once('include/config.inc.php');
			
			$options = array(
				'nopermissions' => true,
				'filter' => array('host' => $host_in, 'key_' => $item_key_in),
				'output' => 'extend'
			);
			$item_in = array_shift(CItem::get($options));
			if(empty($item_in)) warn("ZabbixItem ReadData: Not found item for host '$host_in' and key_ '$item_key_in'");
			
			$options = array(
				'nopermissions' => true,
				'filter' => array('host' => $host_out, 'key_' => $item_key_out),
				'output' => 'extend'
			);
			$item_out = array_shift(CItem::get($options));
			if(empty($item_out)) warn("ZabbixItem ReadData: Not found item for host '$host_out' and key_ '$item_key_out'");
			
			$options = array(
				'nopermissions' => true,
				'history' => $item_in['value_type'],
				'itemids' => array($item_in['itemid']),
				'sortfield' => 'clock',
				'sortorder' => 'DESC',
				'limit' => 1,
				'output' => 'extend'
			);
			$history_in = array_shift(CHistory::get($options));
			if(empty($history_in)) warn("ZabbixItem ReadData: Not found history for item at host '$host_in' and key_ '$item_key_in'");
			
			$options = array(
				'nopermissions' => true,
				'history' => $item_out['value_type'],
				'itemids' => array($item_out['itemid']),
				'sortfield' => 'clock',
				'sortorder' => 'DESC',
				'limit' => 1,
				'output' => 'extend'
			);
			$history_out = array_shift(CHistory::get($options));
			if(empty($history_out)) warn("ZabbixItem ReadData: Not found history for item at host '$host_out' and key_ '$item_key_out'");
			
			chdir($weathermap_dir);
			
			$data[IN] = $history_in['value'];
			$data[OUT] = $history_out['value'];
			$data_time = $history_out['clock'];
		}
		debug ("ZabbixItem ReadData: Returning (".($data[IN]===NULL?'NULL':$data[IN]).",".($data[OUT]===NULL?'NULL':$data[OUT]).",$data_time)\n");
		return array($data[IN], $data[OUT], $data_time);
	}
}
?>
