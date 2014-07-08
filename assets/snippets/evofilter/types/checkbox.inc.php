<?php 
class checkbox{
	static $templates = array(
		'checkboxTpl'=> '<p><label><input type="checkbox" name="[+name+]" value="[+value+]" [+active+]/> [+value+] <sup>[+count+]</sup></label></p>',
		'checkboxOuterTpl'=> '<div class="side-block">
					<div class="pure-g">
						<div class="pure-u-15-24"><h2>[+title+]</h2></div>
						<div class="pure-u-9-24 text-right"> <span class="side-more reset_filter">Сбросить</span> </div>
					</div>
					<div class="check-filters">[+wrapper+]</div>
				</div>',
	);

	//генерим мисив id для фильтра 
	public static function calcIds($key,$value,$ids,$params) {	
		global $modx;
		$pr=$modx->db->config['table_prefix'];
		$inparent=!empty($params['parent'])?' AND cont.parent IN('.$params['parent'].')':'';
		$intemplate=!empty($params['template'])?' AND cont.template='.$params['template']:'';
		if(!empty($ids)) { //добавили в запрос уже ранее выбранные id что б сузить запрос
			$inIds = 'AND contentid IN('.implode(',', $ids).')';
			$ids = array();
		}
		$svalue = '"'.implode('","', explode(',', $value)).'"'; //пересобрали с кавычками
		$sql = 'SELECT contentid FROM '.$pr.'site_tmplvar_contentvalues as c
		LEFT join '.$pr.'site_tmplvars as t ON (c.tmplvarid=t.id)
		LEFT join '.$pr.'site_content as cont ON (cont.id=c.contentid)
		WHERE t.name="'.$key.'" '.$inparent.' '.$intemplate.' '.$inIds. ' AND `value` IN('.$svalue.')  order by value ASC';
		return $sql;
	}

	//формируем вывод в фильтрах
	public static function filters($row,$inIds,$params) {
		global $modx;
		$pr=$modx->db->config['table_prefix'];
		$inparent=!empty($params['parent'])?' AND cont.parent IN('.$params['parent'].')':'';
		$intemplate=!empty($params['template'])?' AND cont.template='.$params['template']:'';

		$checkboxTpl = isset($params['checkboxTpl']) ? $modx->getTpl($params['checkboxTpl']) : self::$templates['checkboxTpl'];
		$checkboxOuterTpl = isset($params['checkboxOuterTpl']) ? $modx->getTpl($params['checkboxOuterTpl']) : self::$templates['checkboxOuterTpl'];

		$result=$modx->db->query('SELECT COUNT(*) as cnt,value,tmplvarid FROM '.$pr.'site_tmplvar_contentvalues as c
			LEFT join '.$pr.'site_content as cont ON (cont.id=c.contentid)
			WHERE tmplvarid="'.$row['id'].'" '.$inparent.' '.$intemplate. ' '.$inIds. '
			GROUP BY value ORDER BY value ASC');

		while($inrow = $modx->db->getRow($result)){
			if (!empty($inrow['value'])){
				$checked = '';
				if (isset($_GET[$inrow['name']])){
					foreach ($_GET[$inrow['name']] as $names){
						if ($inrow['value'] == $names) {$checked = 'checked="checked"';}
					}
				}
				$items .= $modx->parseText($checkboxTpl, array('name'=> $row['name'], 'value' => $inrow['value'], 'active' => $checked, 'count' => $inrow['cnt']));
			}
		}
		return $modx->parseText($checkboxOuterTpl, array('title'=> $row['title'], 'wrapper' => $items));
	}		

	//генерим список ids для отдачи по отобранным фильтрам
	public static function outputIds($key,$value,$ids,$params) {
		global $modx;
		$pr=$modx->db->config['table_prefix'];
		$inparent=!empty($params['parent'])?' AND cont.parent IN('.$params['parent'].')':'';
		$intemplate=!empty($params['template'])?' AND cont.template='.$params['template']:'';

		$svalue = '"'.implode('","', explode(',', $value)).'"'; //пересобрали с кавычками
		if(!empty($ids)) { //добавили в запрос уже ранее выбранные id что б сузить запрос
			$inIds = 'AND contentid IN('.implode(',', $ids).')';
			$ids = array();
		}

		$result=$modx->db->query('SELECT contentid FROM '.$pr.'site_tmplvar_contentvalues as c
			LEFT join '.$pr.'site_tmplvars as t ON (c.tmplvarid=t.id) 
			LEFT join '.$pr.'site_content as cont ON (cont.id=c.contentid)
			WHERE t.name="'.$key.'" '.$inparent.' '.$intemplate. ' '.$inIds. ' AND `value` IN('.$svalue.')  order by value ASC');

		while($row = $modx->db->getRow($result)){
			$ids[] = $row['contentid']; 
		}
		return $ids;
	}
}
?>