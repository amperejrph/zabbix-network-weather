<?php
/*
** ZABBIX
** Copyright (C) 2000-2010 SIA Zabbix
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
**/
?>
<?php
require_once('include/config.inc.php');

$page['file']	= 'zab_chart.php';
$page['type']	= PAGE_TYPE_IMAGE;
?>
<?php
//		VAR			TYPE	OPTIONAL FLAGS	VALIDATION	EXCEPTION
	$fields=array(
		'host'=>		array(T_ZBX_STR, O_MAND,null,	null,		null),
		'item'=>		array(T_ZBX_STR, O_MAND,null,	null,		null),
		'color'=>		array(T_ZBX_CLR, O_OPT, null,	null,		null),
		'drawtype'=>	array(T_ZBX_CLR, O_OPT, null,	null,		null),
		
		'period'=>		array(T_ZBX_INT, O_OPT,	null,	BETWEEN(ZBX_MIN_PERIOD,ZBX_MAX_PERIOD),	null),
		'from'=>		array(T_ZBX_INT, O_OPT,	null,	'{}>=0',	null),
		'width'=>		array(T_ZBX_INT, O_OPT,	null,	'{}>0',		null),
		'height'=>		array(T_ZBX_INT, O_OPT,	null,	'{}>0',		null),
		'border'=>		array(T_ZBX_INT, O_OPT,	null,	IN('0,1'),	null),
		'legend'=>		array(T_ZBX_INT, O_OPT,	null,	IN('0,1'),	null),
		'stime'=>		array(T_ZBX_STR, O_OPT,	P_SYS,	null,		null)
	);

	$res = check_fields($fields);
?>
<?php
	$items = array();
	if (is_array($_REQUEST['host'])) {
		for ($i = 0; $i < count($_REQUEST['host']); $i++) {
			$options = array(
				'filter' => array('host' => $_REQUEST['host'][$i], 'key_' => $_REQUEST['item'][$i]),
				'preservekeys' => 1
			);
			$db_data = CItem::get($options);
			if(empty($db_data)) access_deny();
			$items[] = array_shift(array_keys($db_data));
		}
	}
	else {
		$options = array(
			'filter' => array('host' => $_REQUEST['host'], 'key_' => $_REQUEST['item']),
			'preservekeys' => 1
		);
		$db_data = CItem::get($options);
		if(empty($db_data)) access_deny();
		$items[] = array_shift(array_keys($db_data));
	}
	
	$graph = new CChart();

	$effectiveperiod = navigation_bar_calc('web.item.graph',$_REQUEST['itemid']);

	$header = '';
	if (is_array($_REQUEST['host']))
		for ($i = 0; $i < count($_REQUEST['host']); $i++) {
			if ($i) $header .= ' - ';
			$header .= $_REQUEST['host'][$i].' ('.$_REQUEST['item'][$i].')';
		}
	else
		$header .= $_REQUEST['host'].' ('.$_REQUEST['item'].')';
	$graph->setHeader($header);
	
	if(isset($_REQUEST['period']))		$graph->setPeriod($_REQUEST['period']);
	if(isset($_REQUEST['from']))		$graph->setFrom($_REQUEST['from']);
	if(isset($_REQUEST['width']))		$graph->setWidth($_REQUEST['width']);
	if(isset($_REQUEST['height']))		$graph->setHeight($_REQUEST['height']);
	if(isset($_REQUEST['border']))		$graph->setBorder(0);
	if(isset($_REQUEST['legend']))		$graph->drawLegend = $_REQUEST['legend'];
	if(isset($_REQUEST['stime']))		$graph->setSTime($_REQUEST['stime']);
	
	for ($i = 0; $i < count($items); $i++) {
		$graph->addItem($items[$i], GRAPH_YAXIS_SIDE_DEFAULT, CALC_FNC_ALL, (isset($_REQUEST['color'][$i]) ? $_REQUEST['color'][$i] : null), (isset($_REQUEST['drawtype'][$i]) ? $_REQUEST['drawtype'][$i] : null));
	}
	
	$graph->draw();
?>