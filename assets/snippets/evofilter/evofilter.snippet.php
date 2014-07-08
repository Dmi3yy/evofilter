<?php
/* evoFilter
 * version: 0.3.2
 * autor: dmi3yy
 *
 * --рефактор под ООП
 * --Учитывает неопубликованные и удаленные ($inparent=!empty($parent)?' AND cont.parent IN('.$parent.') AND published=1 AND deleted=0':'';)
 * --Сделать фильтр что б мог работать и с несколькими переменными в ТВ(||) 
 * --Добавить еще 1 тип фильтра Цвет(тоже что и чекбоксы только с доп выводом цвета)
 * --добавить проверку что б удалить товары все без цены
 */
//fix  modx->getTpl;
if(method_exists($modx,'getTpl')){ 
   	function getTpl($tpl) {
   		global $modx;
   		return $modx->getTpl($tpl);
   	}
}else{
	function getTpl($tpl){
		global $modx;
		if(strpos($tpl,'@CODE:')!==false){
			$tpl=str_replace('@CODE:','',$tpl);
		}else {
			$tpl=$modx->getChunk($tpl);
		}
		return $tpl;
	}
}


//параметры по умолчанию 	
$filters = isset($filters) ? $filters : 'evoFilter';	
$params['parent'] = isset($parent) ? $parent : $modx->documentIdentifier;
$type = isset($type) ? $type : 'filters';
$outerTpl = isset($outerTpl) ? getTpl($outerTpl) : '<form id="filter" class="pure-form velo-form filter-form" action="[~[*id*]~]"> [+wrapper+]	</form>';
if (isset($select)){
	$select = ''; 
}else{
    $select = array();
}

//получаем значение ТВ-параметра с настройками фильтра и формируем масив для проверки get параметров
$filters = $modx->getTemplateVar($filters);
$filters = $filters['value'];
$filtersArr = json_decode($filters, true);

if (!$filtersArr['fieldValue']) return; 
$filters = array();
$filtersType = array();
foreach ($filtersArr['fieldValue'] as $value) {
	$filters[] = $value['name'];
	$filtersType[$value['name']] = $value['type'];
}

//подключаем типы фильтров
switch ($type) {
	case 'filters':

		//предготовка масивов id-документов
		$filteredids = array();
		foreach ($_GET as $key => $value) {
			$key=$modx->db->escape($key);
			$value=$modx->db->escape($value);
			if (in_array($key, $filters)){
				//проверка на тип фильтра 
				require_once(MODX_BASE_PATH .'assets/snippets/evofilter/types/'.$filtersType[$key].'.inc.php');
				${$filtersType[$key]} = new $filtersType[$key];
        		$sql = ${$filtersType[$key]}::calcIds($key,$value,$select,$params);

				$result=$modx->db->query($sql);
				//TODO: дописать проверку на отсутствие цены
				
				while($row = $modx->db->getRow($result)){
					$filteredids[$key][] = $row['contentid']; 
				}
				if (!empty($filteredids[$key])) $filteredids[$key] = array_unique($filteredids[$key]);
			}
		}
	
		//Готовим оформление фильтров общее
		$output = ''; 
		foreach ($filtersArr['fieldValue'] as $row) {
			$row['id'] = $modx->db->getValue($modx->db->select("*", $modx->getFullTableName('site_tmplvars'),  "`name`='" . $row['name'] ."'")); 
			$items = '';
			
			$inIds = array();
			foreach($filteredids as $key => $value){	
				if ($key != $row['name']) {	
					if(!empty($inIds)) {
						$inIds = array_intersect($inIds, $value);
					}else{ 
						$inIds = $value ;
					}
				} 
			}
			$inIds = implode(',', array_unique($inIds)); 
			$inIds = ($inIds != '')?'AND contentid IN('.$inIds.')':'';		
			//тут разбивка на типы фильтров 
			require_once(MODX_BASE_PATH .'assets/snippets/evofilter/types/'.$row['type'].'.inc.php');
			${$row['type']} = new $row['type'];
        	$output .= ${$row['type']}::filters($row,$inIds,$params);

		}  
		if($output != ''){
			$output = $modx->parseText($outerTpl, array('wrapper' => $output));
		}
		return $output;
		break;
	
	case 'ids':
		$output = '';
		$ids = array();
		
		foreach ($_GET as $key => $value) {
			$key=$modx->db->escape($key);
			$value=$modx->db->escape($value);
			if (in_array($key, $filters)){
				require_once(MODX_BASE_PATH .'assets/snippets/evofilter/types/'.$filtersType[$key].'.inc.php');
				${$filtersType[$key]} = new $filtersType[$key];
        		$ids = ${$filtersType[$key]}::outputIds($key,$value,$ids,$params);
			}
		}	
		
		$ids = array_unique($ids);
		$output = implode(',', $ids);
		return $output;
		break;
}
?>