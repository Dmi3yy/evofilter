<?php 
class select{
	static $templates = array(
		'selectTpl'=> '<option value="[+value+]" [+active+]/>[+value+] ([+count+])</option>',
		'selectOuterTpl'=> '<section><h3>[+title+]</h3><span class="reset_filter">Сбросить</span><select name="[+name+]" style="width:205px"><option value="">-Все-</option>[+wrapper+]</select></section>',
	);
	//генерим мисив id для фильтра 
	public static function calcIds($key,$value,$params) {	
		global $modx;
		$pr=$modx->db->config['table_prefix'];
		$inparent=!empty($params['parent'])?' AND cont.parent IN('.$params['parent'].')':'';
		$intemplate=!empty($params['template'])?' AND cont.template='.$params['template']:'';

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

		$selectTpl = isset($params['selectTpl']) ? getTpl($params['selectTpl']) : self::$templates['selectTpl'];
		$selectOuterTpl = isset($params['selectOuterTpl']) ? getTpl($params['selectOuterTpl']) : self::$templates['selectOuterTpl'];

		$result=$modx->db->query('SELECT COUNT(*) as cnt,value,tmplvarid FROM '.$pr.'site_tmplvar_contentvalues as c
			LEFT join '.$pr.'site_content as cont ON (cont.id=c.contentid)
			WHERE tmplvarid="'.$row['id'].'" '.$inparent.' '.$intemplate. ' '.$inIds. '
			GROUP BY value ORDER BY value ASC');

		while($inrow = $modx->db->getRow($result)){
			if (!empty($inrow['value'])){
				$checked = '';
				if (isset($_GET[$inrow['name']])){
					foreach ($_GET[$inrow['name']] as $names){
						if ($inrow['value'] == $names) {$checked = 'selected="selected"';}
					}
				}
				$items .= $modx->parseText($selectTpl, array('name'=> $row['name'], 'value' => $inrow['value'], 'active' => $checked, 'count' => $inrow['cnt']));
			}
		}
		return $modx->parseText($selectOuterTpl, array('title'=> $row['title'], 'name'=> $row['name'], 'wrapper' => $items));
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