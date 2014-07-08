<?php
$settings['display'] = 'horizontal';
$settings['fields'] = array(
	'title' => array(
		'caption' => 'Название',
		'type' => 'text',
		'width' => '200'
	),
	'name' => array(
		'caption' => 'Параметр фильтра',
		'type' => 'dropdown',
		'elements' => '@SELECT name FROM [+PREFIX+]site_tmplvars WHERE `category` IN(20,13) ORDER BY name ASC',
		'width' => '200'
	),
	'type' => array(
		'caption' => 'Тип фильтра',
		'type' => 'dropdown',
		'elements' => 'Цена==price||Наличие==store||Чекбоксы==checkbox||Селект==select',
		'width' => '200'
	)
);
$settings['configuration'] = array(
	'enablePaste' => FALSE,
	'enableClear' => FALSE,
	'csvseparator' => ','
);
?>
