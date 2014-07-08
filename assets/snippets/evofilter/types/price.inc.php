<?php 
class price{
	static $templates = array(
		'priceTpl'=> '<label for="amount1">от</label>
                <input type="text" id="amount1" value="" name="min" />
                <label for="amount2">до</label>
                <input type="text" id="amount2" value="" name="max" />
                <div id="slider-range"></div>
				<script>
				$(function() {
	$( "#slider-range" ).slider({
		range: true,
		min: [+min+],
		max: [+max+],
		values: [ [+min+], [+max+] ],
		slide: function( event, ui ) {
			$( "#amount1" ).val(ui.values[ 0 ]);
			$( "#amount2" ).val(ui.values[ 1 ]);
		},
		change: function(event, ui) {
            $("#filter").submit();
        }
		});
	$( "#amount1" ).val($("#slider-range" ).slider( "values", 0 ));
	$( "#amount2" ).val($("#slider-range" ).slider( "values", 1 ));
	
});
</script>
',
		
		
		'priceOuterTpl'=> '<div class="side-block">
					<div class="pure-g">
						<div class="pure-u-1-2"><h2>[+title+]</h2></div>
						<div class="pure-u-1-2 text-right"> <span class="side-more reset_filter">Сбросить</span> </div>
					</div>
					<div class="slide-filter">[+wrapper+]</div>
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
		$val = explode(";", $value);
		$val[1] = isset($val[1]) ? $val[1]:$val[0];
		$sql = 'SELECT contentid FROM '.$pr.'site_tmplvar_contentvalues as c
			LEFT join '.$pr.'site_tmplvars as t ON (c.tmplvarid=t.id)
			LEFT join '.$pr.'site_content as cont ON (cont.id=c.contentid)
			WHERE t.name="'.$key.'" '.$inparent.' '.$intemplate. ' '.$inIds. ' AND value BETWEEN '.$val[0].' AND '.$val[1].' order by value ASC';
		return $sql;
	}

	//формируем вывод в фильтрах
	public static function filters($row,$inIds,$params) {
		global $modx;
		$pr=$modx->db->config['table_prefix'];
		$inparent=!empty($params['parent'])?' AND cont.parent IN('.$params['parent'].')':'';
		$intemplate=!empty($params['template'])?' AND cont.template='.$params['template']:'';

		$priceTpl = isset($params['priceTpl']) ? getTpl($params['priceTpl']) : self::$templates['priceTpl'];
		$priceOuterTpl = isset($params['priceOuterTpl']) ? getTpl($params['priceOuterTpl']) : self::$templates['priceOuterTpl'];

		$resm=$modx->db->query('SELECT min( cast(value as decimal) ) as value FROM '.$pr.'site_tmplvar_contentvalues as c
			LEFT join '.$pr.'site_content as cont ON (cont.id=c.contentid)
			WHERE tmplvarid="'.$row['id'].'" '.$inparent.' '.$intemplate. ' order by value ASC');

		$rowm = $modx->db->getRow($resm);
		$row['min'] =  $rowm['value'];

		$resm=$modx->db->query('SELECT max( cast(value as decimal) ) as value FROM '.$pr.'site_tmplvar_contentvalues as c
			LEFT join '.$pr.'site_content as cont ON (cont.id=c.contentid)
			WHERE tmplvarid="'.$row['id'].'" '.$inparent.' '.$intemplate. ' order by value ASC');

		$rowm = $modx->db->getRow($resm);
		$row['max'] =  $rowm['value'];

		$items .= $modx->parseText($priceTpl, array('name'=> $row['name'], 'min' => $row['min'],'max' => $row['max']));
		return $modx->parseText($priceOuterTpl, array('title'=> $row['title'], 'wrapper' => $items));
	}		

	//генерим список ids для отдачи по отобранным фильтрам
	public static function outputIds($key,$value,$ids,$params) {
		global $modx;
		$pr=$modx->db->config['table_prefix'];
		$inparent=!empty($params['parent'])?' AND cont.parent IN('.$params['parent'].')':'';
		$intemplate=!empty($params['template'])?' AND cont.template='.$params['template']:'';

		$val = explode(";", $value);
		$val[1] = isset($val[1])?$val[1]:$val[0];
		if(!empty($ids)) { //добавили в запрос уже ранее выбранные id что б сузить запрос
			$inIds = 'AND contentid IN('.implode(',', $ids).')';
			$ids = array();
		}
		$result=$modx->db->query('SELECT contentid FROM '.$pr.'site_tmplvar_contentvalues as c
			LEFT join '.$pr.'site_tmplvars as t ON (c.tmplvarid=t.id) 
			LEFT join '.$pr.'site_content as cont ON (cont.id=c.contentid)
			WHERE t.name="'.$key.'" '.$inparent.' '.$intemplate. ' '.$inIds. ' AND value BETWEEN '.$val[0].' AND '.$val[1].' order by value ASC');	
		while($row = $modx->db->getRow($result)){
			$ids[] = $row['contentid']; 
		}	
		return $ids;
	}
}
?>		