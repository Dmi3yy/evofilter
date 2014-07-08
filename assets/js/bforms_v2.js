/**
*
*
*
*
*
*/
    
    function is_array(inputArray) {
      return inputArray && !(inputArray.propertyIsEnumerable('length')) && typeof inputArray === 'object' && typeof inputArray.length === 'number';
    }
	
(function( $ ){
var url = decodeURIComponent(window.location.href);
window.history.replaceState('Object', 'Title', url);
	var GET = decodeURIComponent(window.location.search.slice(1))
        .split('&')
        .reduce(function _reduce (a,b) {
          b = b.split('=');
          if (a[b[0]]) {
            if (is_array(a[b[0]])) {
              a[b[0]].push(b[1])
                }
            else {
              var arr=[];
              arr.push(a[b[0]]);
              arr.push( b[1]);
              a[b[0]]=arr;
            }
            
          } else {a[b[0]] = b[1];}
          return a;
        }, {});
		
  var methods = {
	defaults:{
		delimiter:','
	},
    reset : function( options ) { 
		var setting={
			radio:true,
			checkbox:true,
			select:true,
			textarea:true,
			input_text:true
		}
		
		options = $.extend(setting,options);
		
		if (options.radio){
			$(this).find(':radio').each(function(){
				$(this).prop('checked', false);
			});
		}
		
		if (options.checkbox){
			$(this).find(':checkbox').each(function(){
				$(this).prop('checked', false);
			});
		}
		if (options.input_text){
			$(this).find('input:text').each(function(){
				var value=$(this).attr('placeholder')||'';
				$(this).val(value);
			});
		}
		if (options.textarea){
			$(this).find('textarea').each(function(){
				var value=$(this).attr('placeholder')||'';
				$(this).val(value);
			});
		}
		if (options.select){
			$(this).find('select').each(function(){
				$(this).val( $(this).prop('defaultSelected') );
			});
		}
    },
    set_from_request : function( options) {
		$(this).find(' :input').each(function(){
			var val = $(this).val();
			var name = $(this).attr('name');  
			
			switch($(this).prop('type')){
			  case 'text':
				if (GET[name]){
				  $(this).val(GET[name]);
				}
				$(this).data('oldvalue',val);
				break;
			  case 'radio':
				if (GET[name]){
				  if (GET[name].indexOf(val) !== -1) $(this).prop('checked', true); 
				}
				break;
			  case 'checkbox':
				if (GET[name]){
					if ($.inArray( val,GET[name].split(',') ) > -1) {
						$(this).prop('checked', true); 
					}
				}
				break;
			  case 'select-one':
				if (GET[name]){
				$('option',this).filter(function(){return $(this).val() == GET[name];}).prop('selected',true);
				  /*$(this).find('option').each(function(){
					$(this).filter(function() {
						var string = '||||'+GET[name].replace('+',' ').split(methods.defaults.delimiter).join('||||')+'||||';
					  return GET[name].indexOf('||||'+$(this).val()+'||||')!== -1;
					  
					}).attr('selected', true);
				  });*/
				}
				break;   
			}
		});
    },
    send : function(){
	
	},
	onsubmit:function( options ) {
		options = $.extend({delimiter:methods.defaults.delimiter},options);
	
		var url=[];
		var index=0;
		var url=[];
		var names = [];
		var form = $(this);
		$.each( form.find(':input'), function(){var myname= this.name;if( $.inArray( myname, names ) < 0 ){
		names[myname]=$(this).prop('type');
		}});

		for (var key in names){ 

		var el=form.find(':input[name='+key+']'); 
		switch(names[key]){
		  case 'text':
			if (el.val()) url.push( key +'='+ el.val() );
			break;
		  case 'radio':
			var val = form.find(':input[name='+key+']:checked').val();
			if (val) url.push( key +'='+ val );
			break;
		  case 'checkbox':
			var tmp = form.find(':input[name='+key+']:checked').map(function () {return this.value;}).get();
			if (index < tmp.length) url.push(key +'='+tmp.join(options.delimiter));
			break;
		  case 'select-one':
			if (el.val()) url.push( key +'='+ el.val() );
			break;   
		}
		}         
		
		if(options.link){
			uri=$('.filter_submit').attr('href').split('?');
		} else {
			uri=form.attr('action').split('?');
		}
		if (url[0]) url='?'+url.join('&'); 
		var URL=uri[0]+url;                     
	
		if(options.link) form.find(options.link).attr('href',URL);
		window.location.href=URL;
    },
    update : function( content ) {
      // future TODO
    },
	
	defaulted: function(options){
		options = $.extend(methods.defaults,options);
		$(this).click(function(){
			$(this).parent('section').BForms('reset');
			options.callback();
			//console.log(options);
			//return false;
		});
	}
  };

  $.fn.BForms = function( method ) {
    if ( methods[method] ) {
      return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
    } else if ( typeof method === 'object' || ! method ) {
      return methods.init.apply( this, arguments );
    } else {
      $.error( 'Метод с именем ' +  method + ' не существует для jQuery.BForms' );
    } 
  };

})( jQuery );
/*
$('.params form').BForms('reset',{radio:false}); 

$('#filter').BForms('set_from_request');

    $('#filter .filter_submit').click(function(){
      $('#filter').BForms('onsubmit',{link:'.filter_submit'});
    });
*/