#evoFilter
Ссылка на документацию: http://docs.evolution-cms.com/Extras/Snippets/evoFilter

###Ближайшие планы: 
- исправить работу js библиотеки для правильной работы слайдера  (критично особенно если несколько слайдеров на страничке)
- Jquery UI slider (пока работает только с jSlider) (не критично)
— Возможность работы когда в 1м тв может быть несколько значений (критично)
— Ajax как минимум самого фильтра, а то сейчас просто через ребут страницы (критично)
- Привести в порядок базовые шаблоны

### C учетом что с донатом у нас в сообществе очень туго, попробую развивать решение на платной поддержке.  
А так же как доделаем репозиторий под платные дополнения то он появиться и там. 

### Примеры кода: 
####Cкрипты:
	<script src="assets/js/bforms_v2.js"></script> 
	<script src="assets/js/jslider/js/jquery.slider.min.js"></script> 
	<link   href="assets/js/jslider/js/jquery.slider.min.css" rel="stylesheet"type="text/css" />
	<script>
	$('.reset_filter').BForms('defaulted',{callback:function(){
		$('#filter').BForms('onsubmit');}
	});
	
	$('#filter').BForms('set_from_request');
	
	$('#filter :input').change(function(){
		$('#filter').BForms('onsubmit');
	});
	</script>

#### Вызов фильтра: 
			[!evoFilter? 
				&parent=`[*id*]`
				&template=`5`
				&type=`filters`	
				&outerTpl=`@CODE:<div class="catalog_filters"><h2>Фильтр</h2><form id="filter" action="[~[*id*]~]"> [+wrapper+]	</form></div><hr />`
			!]
#### Вызов результата: 
			[!DocLister? 
				&debug=`0`
				&display=`20` 
				&depth=`10` 
				&tpl=`catalog_thumbs`
				&orderBy=`price ASC`
				&tvSortType=`UNSIGNED`
		    	&parents=`[*id*]`
				&documents=`[!evoFilter? &parent=`[*id*]` &template=`5` &type=`ids` !]`
				&ignoreEmpty=`1`
			!]			
