/**
 * Mega Filter
 */

if( typeof Array.prototype.indexOf == 'undefined' ) {
	Array.prototype.indexOf = function(obj, start) {
		for( var i = ( start || 0 ), j = this.length; i < j; i++ ) {
			if( this[i] === obj ) {return i;}
		}
		
		return -1;
   };
};

var MegaFilter = function(){ };

MegaFilter.prototype = {
	
	/**
	 * Kontener filtrów
	 */
	_box: null,
			
	/**
	 * Opcje
	 */
	_options: null,
			
	/**
	 * @var int
	 */
	_timeoutAjax: null,
	
	_timeoutSearchFiled: null,
		
	/**
	* @var string
	*/
	_url			: null,
	
	/**
	 * Separator URL
	 * 
	 * @var string
	 */
	_urlSep			: null,
	
	/**
	 * Lista parametrów
	 *
	 * @var object
	 */
	_params			: null,
	
	/**
	 * Lista scrolls
	 * 
	 * @var array
	 */
	_scrolls		: null,
	
	/**
	 * Lista guzików
	 * 
	 * @var array
	 */
	_buttonsMore	: null,
	
	_liveFilters	: null,
	
	/**
	 * Kontener główny
	 *
	 * @var jQuery
	 */
	_jqContent		: null,
		
	/**
	 * Loader over results
	 *
	 * @var jQuery
	 */
	_jqLoader		: null,
	
	/**
	 * Loader over filter
	 * 
	 * @var jQuery
	 */
	_jqLoaderFilter	: null,
		
	/**
	 * ID kontenera głównego
	 *
	 * @var string
	 */
	_contentId		: '#content',
	
	/**
	 * Trwa oczekiwanie na odpowiedź serwera
	 * 
	 * @var bool
	 */
	_busy			: false,
	
	/**
	 * W trakcie ładowania danych z serwra wprowadzono zmiany
	 * 
	 * @var bool
	 */
	_waitingChanges	: false,
	
	/**
	 * Ostania odpowiedź serwera
	 * 
	 * @var string
	 */
	_lastResponse	: '',
	
	_refreshPrice : function(){},
	
	_inUrl : null,
	
	_isInit: false,
	
	_cache: null,
	
	_relativeScroll: null,
	
	////////////////////////////////////////////////////////////////////////////
	
	/**
	 * Inicjuj klasę
	 */
	init: function( box, options ) {
		var self = this,
			i;
			
		if( options.contentSelector )
			self._contentId = options.contentSelector;
		
		self._jqContent	= jQuery(self._contentId);
		
		if( ! self._jqContent.length ) {
			self._contentId = '#maincontent';
			
			self._jqContent	= jQuery(self._contentId);
		}
		
		self._scrolls		= [];
		self._buttonsMore	= [];
		self._liveFilters	= [];
		self._box			= box;
		self._options		= options;
		self._cache			= {
			'lastResponse'	: {},
			'mainContent'	: {}
		};
		
		self.initUrls();
		
		for( i in options.params ) {
			if( typeof options.params[i] == 'function' ) continue;
			
			if( typeof self._params[i] == 'undefined' ) {
				self._params[i] = options.params[i];
			}
		}
		
		for( i in self ) {
			if( i.indexOf( '_init' ) === 0 ) {
				self[i]();
			}
		}
		
		self._isInit = true;
	},
	
	_initResponsive: function(){
		var self	= this,
			column	= null,
			moved	= false,
			hidden	= true;
	
		if( self._box.hasClass( 'mfilter-column_left' ) ) {
			column = jQuery('#column-left');
		} else if( self._box.hasClass( 'mfilter-column_right' ) ) {
			column = jQuery('#column-right');
		} else {
			return;
		}
		
		var id = 'mfilter-free-container-' + jQuery('[id^="mfilter-free-container"]').length,
			cnt = jQuery('<div class="mfilter-free-container">')
				.prependTo( jQuery('body') ),
			btn = jQuery('<div class="mfilter-free-button">')
				.appendTo( cnt )
				.click(function(){
					if( hidden ) {
						cnt.animate({
							'marginLeft' : 0
						}, 500, function(){
							self._relativeScroll.refresh();
						});
					} else {
						cnt.animate({
							'marginLeft' : - cnt.outerWidth(true)
						}, 500);
					}
			
					hidden = ! hidden;
				}),
			cnt2 = jQuery('<div>')
				.css('overflow','hidden')
				.attr('id', id)
				.appendTo( cnt ),
			cnt3 = jQuery('<div>')
				.appendTo( cnt2 ),
			src = jQuery('<span class="mfilter-before-box">');
	
		self._relativeScroll = new IScroll( '#' + id, {
			bounce: false,
			scrollbars: true,
			mouseWheel: true,
			interactiveScrollbars: true,
			mouseWheelSpeed: 120
		});
		
		self._box.before( src );
		
		if( ! column.length )
			column = self._box.parent();
		
		function isVisible() {
			var visible = column.is(':visible'),
				height	= jQuery(window).height() - 50;
			
			if( visible && moved ) {
				cnt.hide();
				
				src.after( self._box );
				
				if( ! hidden )
					btn.trigger('click');
				
				moved = false;
			} else if( ! visible && ! moved ) {
				cnt.show();
				
				cnt3.append( self._box );
				
				moved = true;
			}
			
			if( moved ) {
				cnt2.css( 'max-height', height + 'px' );
				self._relativeScroll.refresh();
			}
		}
		
		jQuery(window).resize(function() {
			isVisible();
		});
		
		isVisible();
	},
	
	initUrls: function( url ) {
		var self = this;

		if( typeof url == 'undefined' )
			url	 = document.location.href.split('#')[0];		
		
		self._urlSep	= self._parseSep( url ).urlSep;
		self._url		= self._parseSep( url ).url;
		self._params	= self._parseUrl( url );
		self._inUrl		= self._parseUrl( url );
	},
	
	_initMfImage: function() {
		var self = this;
		
		self._box.find('.mfilter-image input').change(function(){
			var s = jQuery(this).is(':checked');
			
			jQuery(this).parent()[s?'addClass':'removeClass']('mfilter-image-checked');
		});
		
		self._box.find('.mfilter-image input:checked').parent().addClass('mfilter-image-checked');
	},
	
	_initBox: function() {
		var self = this;
		
		if( self._isInit ) return;
		
		if( self._box.hasClass( 'mfilter-content_top' ) ) {
			self._box.find('.mfilter-content > ul > li').each(function(i){
				if( i && i % 4 == 0 ) {
					jQuery(this).before('<li style="clear:both; display:block;"></li>')
				}
			});
		}
	},
	
	_initSearchFiled: function() {
		var self = this,
			searchField = self._box.find('input[id="mfilter-opts-search"]'),
			searchButton = self._box.find('[id="mfilter-opts-search_button"]');
			
		if( ! searchField.length )
			return;
			
		var refreshDelay = parseInt( searchField.unbind('keyup keydown click').attr('data-refresh-delay').toString().replace(/[^0-9]+/, '') ),
			lastValue = searchField.val();
		
		function clearInt() {
			if( self._timeoutSearchFiled )
				clearTimeout( self._timeoutSearchFiled );
			
			self._timeoutSearchFiled = null;
		}
	
		if( refreshDelay != '-1' ) {
			searchField.bind('keyup', function(e){
				if( jQuery(this).val() == lastValue )
					return;
				
				if( ! refreshDelay ) {
					self.ajax();
				} else if( e.keyCode != 13 ) {
					clearInt();
					
					self._timeoutSearchFiled = setTimeout(function(){
						self.ajax();
						
						self._timeoutSearchFiled = null;
					}, refreshDelay);
				}
				
				lastValue = searchField.val();
			});
		}
		
		searchField.bind('keydown', function(e){
			if( e.keyCode == 13 ) {
				clearInt();
				
				self.ajax();
				
				return false;
			}
		}).bind('keyup.mf_shv', function(){
			jQuery(this)[jQuery(this).val()?'addClass':'removeClass']('mfilter-search-has-value');
		}).trigger('keyup.mf_shv');
	
		searchButton.bind('click', function(){
			clearInt();
			
			self.ajax();
			
			return false;
		});
	},
	
	encode: function( string ) {
		string = string.replace( /,/g, 'LA==' );
		string = string.replace( /\[/g, 'Ww==' );
		string = string.replace( /\]/g, 'XQ==' );
		
		return string;
	},
	
	decode: function( string ) {
		string = string.replace( /LA==/g, ',' );
		string = string.replace( /Ww==/g, '[' );
		string = string.replace( /XQ==/g, ']' );
		
		return string;
	},
	
	_parseSep: function( url ) {
		var self = this,
			urlSep = null;
		
		if( typeof url == 'undefined' )
			url = self._url;
		
		if( url.indexOf( '?' ) > -1 ) {
			url		= url.split('?')[0];
			urlSep	= {
				'f' : '?',
				'n' : '&'
			};
		} else {
			if( ! self._options.smp.isInstalled || self._options.smp.disableConvertUrls ) {
				url		= self.parse_url( url );//.split('&')[0];
				url		= url.scheme + '://' + url.host + (url.port ? ':'+url.port: '') + url.path;
				url		= url.split('&')[0];
				urlSep	= {
					'f' : '?',
					'n' : '&'
				};
			} else {
				url		= url.split(';')[0];
				urlSep	= {
					'f' : ';',
					'n' : ';'
				};
			}
		}
		
		return {
			url : url,
			urlSep : urlSep
		};
	},
	
	/**
	 * Inicjuj kontener
	 */
	_initContent: function() {
		var self = this;
		
		self._jqContent
			.css('position', 'relative');
	},
	
	/**
	 * @return {scheme: 'http', host: 'hostname', user: 'username', pass: 'password', path: '/path', query: 'arg=value', fragment: 'anchor'}
	 */
	parse_url: function(str, component) {
		var query, key = ['source', 'scheme', 'authority', 'userInfo', 'user', 'pass', 'host', 'port',
			'relative', 'path', 'directory', 'file', 'query', 'fragment'
			],
			ini = (this.php_js && this.php_js.ini) || {},
			mode = (ini['phpjs.parse_url.mode'] &&
			ini['phpjs.parse_url.mode'].local_value) || 'php',
			parser = {
			php: /^(?:([^:\/?#]+):)?(?:\/\/()(?:(?:()(?:([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?()(?:(()(?:(?:[^?#\/]*\/)*)()(?:[^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
			strict: /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
			loose: /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/\/?)?((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/ // Added one optional slash to post-scheme to catch file:/// (should restrict this)
			};

		var m = parser[mode].exec(str),
			uri = {},
			i = 14;
		while (i--) {
			if (m[i]) {
			uri[key[i]] = m[i];
			}
		}

		if (component) {
			return uri[component.replace('PHP_URL_', '')
			.toLowerCase()];
		}
		if (mode !== 'php') {
			var name = (ini['phpjs.parse_url.queryKey'] &&
			ini['phpjs.parse_url.queryKey'].local_value) || 'queryKey';
			parser = /(?:^|&)([^&=]*)=?([^&]*)/g;
			uri[name] = {};
			query = uri[key[12]] || '';
			query.replace(parser, function($0, $1, $2) {
			if ($1) {
				uri[name][$1] = $2;
			}
			});
		}
		delete uri.source;
		return uri;
	},
	
	baseTypes: function() {
		var self	= this,
			types	= [];
		
		self._box.find('[data-base-type]').each(function(){
			types.push( jQuery(this).attr('data-base-type') );
		});
		
		return types;
	},
	
	_ajaxUrl: function( url ) {
		if( ! this._options.mijoshop )
			return url;
			
		return url + ( url.indexOf('?') > -1 ? '&' : '?' ) + 'option=com_mijoshop&format=raw';
	},
	
	/**
	 * Inicjuj informacje o ilości
	 */
	_initCountInfo: function() {
		var self = this;
		
		if( ! self._options.showNumberOfProducts || self._isInit )
			return;
		
		jQuery.ajax({
			'url'		: self._ajaxUrl( self.createUrl( self._options.ajaxInfoUrl, jQuery.extend( {}, self._params ) ) ),
			'type'		: 'GET',
			'data'		: {
				'mfilterIdx'	: self._options.idx,
				'mfilterRoute'	: self._options.route,
				'mfilterBTypes'	: self.baseTypes().join(',')
			},
			'success'	: function( response ) {
				self._parseInfo( response );
				
				self._cache.mainContent[document.location] = {
					'html'	: self._jqContent.html(),
					'json'	: response
				};
			},
			'error'		: function() {
				
			}
		});
		
		self._parseInfo('{"stock_status":[],"manufacturers":[],"rating":[],"attributes":[],"options":[],"filters":[]}',true);
	},
	
	_parseInfo: function( data, first ) {
		var self	= this,
			filters	= self.filters(),
			json	= jQuery.parseJSON( data );
		
		for( var i in json ) {
			switch( i ) {
				case 'price' : {
					var priceRange = self.getPriceRange();
					
					if( priceRange.min == self._options.priceMin && priceRange.max == self._options.priceMax ) {
						self._box.find('[id="mfilter-opts-price-min"]').val( json[i].min );
						self._box.find('[id="mfilter-opts-price-max"]').val( json[i].max );
					}
					
					self._options.priceMin = json[i].min;
					self._options.priceMax = json[i].max;
					
					self._refreshPrice();
					
					break;
				}
				case 'rating' :
				case 'attributes' :
				case 'filters' :
				case 'options' :
				case 'manufacturers' :
				case 'stock_status' : {
					self._box.find('.mfilter-filter-item.mfilter-' + i).each(function(){
						var $item		= jQuery(this),
							seo			= $item.attr('data-seo-name'),
							id			= $item.attr('data-id'),
							$items		= $item.find( '.mfilter-options .mfilter-option'),
							hidden		= 0;
						
						$items.each(function(){
							var $self		= jQuery(this),
								$input		= $self.find('input[type=checkbox],input[type=radio],select'),
								val			= $input.val(),
								$counter	= jQuery(this).find('.mfilter-counter'),
								text		= '',
								cnt			= json[i];
								
							if( id && typeof cnt[id] != 'undefined' ) {
								cnt = cnt[id];
							}
							
							if( $self.hasClass( 'mfilter-select' ) ) {
								var $options	= $input.find('option'),
									hOptions	= 0;
								
								$options.each(function(){
									var id = jQuery(this).attr('id');
									
									if( ! id ) return;
									
									id = id.split('-').pop();
									
									if( typeof cnt[id] != 'undefined' ) {
										jQuery(this).removeAttr('disabled').html( '(' + cnt[id] + ') ' + jQuery(this).val() );
									} else {
										jQuery(this).attr('disabled',true).html( '(0) ' + jQuery(this).val() );
										hOptions++;
									}
								});
								
								if( hOptions == $options.length )
									hidden++;
							} else {
								var idd = $input.attr('id').split('-').pop();
								
								if( typeof filters[seo] != 'undefined' ) {
									if( filters[seo].indexOf( encodeURIComponent( self.encode( val ) ) ) > -1 ) {
										$counter.addClass( 'mfilter-close' );
									} else {
										if( ! $item.hasClass( 'mfilter-radio' ) && ! $item.hasClass('mfilter-image_list_radio') /*&& base_type != 'option'*/ )
											text += '+';

										$counter.removeClass( 'mfilter-close' );
									}
								} else {
									$counter.removeClass( 'mfilter-close' );
								}
								
								$self.removeClass('mfilter-first-child mfilter-last-child mfilter-disabled mfilter-hide mfilter-visible');

								if( typeof cnt[idd] != 'undefined' && parseInt( cnt[idd] ) > 0 ) {
									text += cnt[idd];

									$self.addClass('mfilter-visible');
									$input.attr('disabled', false);
								} else {
									text = '0';
									hidden++;
									
									$self.addClass('mfilter-disabled');
									$input.attr('disabled',true);
										
									if( first !== true && self._options.hideInactiveValues ) {
										$self.addClass('mfilter-hide');
									}
								}

								$counter.text( text );
							}
						});
						
						if( first !== true && self._options.hideInactiveValues ) {
							if( self._options.hideInactiveValues ) {
								$item[hidden==$items.length?'addClass':'removeClass']('mfilter-hide');
								$item.find('.mfilter-option').not('.mfilter-hide,.mfilter-hide-by-live-filter').first().addClass('mfilter-first-child');
								$item.find('.mfilter-option').not('.mfilter-hide,.mfilter-hide-by-live-filter').last().addClass('mfilter-last-child');
							}
						}
					});
					
					break;
				}
			}
		}
						
		if( first !== true && ( self._options.hideInactiveValues || self._box.find('[data-display-live-filter!="0"]').length ) ) {							
			for( var s = 0; s < self._scrolls.length; s++ ) {
				self._scrolls[s].refresh();
			}

			for( var b = 0; b < self._buttonsMore.length; b++ ) {
				self._buttonsMore[b].refresh();
			}
			
			for( var f = 0; f < self._liveFilters.length; f++ ) {
				self._liveFilters[f].refresh();
			}

			if( self._relativeScroll != null ) {
				self._relativeScroll.refresh();
			}
		}
	},	
	
	_initAlwaysSearch: function() {
		var self	= this;
			
		function search() {
			self._jqContent.find('[name^=filter_],[name=search],[name=category_id],[name=sub_category],[name=description]').each(function(){
				var name	= jQuery(this).attr('name'),
					value	= jQuery(this).val(),
					type	= jQuery(this).attr('type');

				if( [ 'checkbox', 'radio' ].indexOf( type ) > -1 && ! jQuery(this).is(':checked') )
					value = '';
					
				if( name ) {
					self._inUrl[name] = value;
					self._params[name] = value;
				}
			});
			
			self.reload();
			//self.ajax();
		}
			
		jQuery('#button-search').unbind('click').click(function(e){
			e.preventDefault();
				
			search();
		});
			
		self._jqContent.find('input[name=filter_name],input[name=search]').unbind('keydown').unbind('keyup').bind('keydown', function(e){
			if( e.keyCode == 13 ) {
				e.preventDefault();
					
				search();
			}
		});
	},
		
	__initLoader: function() {
		this._jqLoader = jQuery('<span style="cursor: wait; z-index: 100; margin: 0; padding: 0; position: absolute; text-align: center; background-color: rgba(255,255,255,0.7);"></span>')
			.prependTo( this._jqContent )
			.html( '<img src="catalog/view/theme/default/stylesheet/mf/images/ajax-loader.gif" alt="" />' )
			.hide();
	},
	
	__initLoaderFilter: function() {
		this._jqLoaderFilter = jQuery('<span style="cursor: wait; z-index: 10000; margin: 0; padding: 0; position: absolute; background-color: rgba(255,255,255,0.7);"></span>')
			.prependTo( this._box )
			.hide();
	},
	
	/**
	 * Inicjuj wyświetlanie listy
	 */
	_initDisplayItems: function() {
		var self = this;
		
		self._box.find('.mfilter-filter-item').each(function(i){
			var _level0				= jQuery(this),
				type				= _level0.attr('data-type'),
				displayLiveFilter	= parseInt( _level0.attr('data-display-live-filter') ),
				displayListOfItems	= _level0.attr('data-display-list-of-items');
					
			if( ! displayListOfItems ) {
				displayListOfItems = self._options.displayListOfItems.type;
			}

			if( type == 'price' || type == 'rating' ) return;
			
			var wrapper = _level0.find('.mfilter-content-wrapper'),
				content	= _level0.find('> .mfilter-content-opts'),
				heading	= _level0.find('> .mfilter-heading'),
				idx = null;

			if( ! self._box.hasClass('mfilter-content_top') && heading.hasClass( 'mfilter-collapsed' ) ) {
				content.show();
			}
				
			if( displayListOfItems == 'scroll' ) {				
				if( wrapper.actual( 'outerHeight', { includeMargin: true } ) > self._options.displayListOfItems.maxHeight ) {
					wrapper
						.attr('id', 'mfilter-content-opts-' + i)
						.addClass('mfilter-iscroll')
						.css('max-height', self._options.displayListOfItems.maxHeight + 'px');
					
					(function(){
						var iscroll = new IScroll( '#mfilter-content-opts-' + i, {
								bounce: false,
								scrollbars: true,
								mouseWheel: true,
								interactiveScrollbars: true,
								mouseWheelSpeed: 120
							}),
							start = false;

						iscroll.on('moveStart', function(e){
							if( ! start ) return;
							
							self._relativeScroll._end(e);
							
							start = false;
						});

						iscroll.on('moveStop', function(e){
							if( start ) return;
							
							self._relativeScroll._start(e);
							
							start = true;
						});

						iscroll.on('scrollEnd', function(e){
							if( e == null ) return;
							
							self._relativeScroll._end(e);
							
							start = false;
						});

						idx = self._scrolls.length;

						self._scrolls.push({
							refresh: function(){
								iscroll.refresh();
							}
						});
					})();
				}
			} else if( displayListOfItems == 'button_more' && ! self._box.hasClass('mfilter-content_top') ) {
				self._buttonsMore.push((function( _level0 ){		
					function init( first ) {
						var lessHeight	= 0,
							moreHeight	= 0,
							show		= false,
							count		= 0,
							idx			= 0;
						
						_level0.find('.mfilter-option').each(function(i){
							var _this = jQuery(this);
							
							if( _this.hasClass('mfilter-hide') || _this.hasClass('mfilter-hide-by-live-filter') ) return;
							
							var h = _this.actual( 'outerHeight', { includeMargin: true } );

							moreHeight += h;

							if( idx >= self._options.displayListOfItems.limit_of_items ) {
								lessHeight += h;
								count++;
							}
							
							idx++;
						});

						lessHeight = moreHeight - lessHeight;

						if( count ) {
							wrapper.css('overflow','hidden').css('height', lessHeight+'px');
							
							function sh( show, force ) {
								if( force ) {
									wrapper.height( moreHeight );
								} else {
									wrapper.animate({
										'height' : ! show ? moreHeight : lessHeight
									}, 500, function(){
										if( self._relativeScroll != null )
											self._relativeScroll.refresh();
									});
								}
										
								_level0.find('a.mfilter-button-more').text(show?self._options.displayListOfItems.textMore.replace( '%s', count ):self._options.displayListOfItems.textLess);
							}

							_level0.find('.mfilter-content-opts').append(jQuery('<div>')
								.addClass( 'mfilter-button-more' )
								.append(jQuery('<a>')
									.attr( 'href', '#' )
									.addClass( 'mfilter-button-more' )
									.text( self._options.displayListOfItems.textMore.replace( '%s', count ) )
									.bind('click', function(){
										sh( show );

										show = ! show;
										
										wrapper[show?'addClass':'removeClass']('mfilter-slide-down');

										return false;
									})
								)
							);
						
							if( wrapper.hasClass('mfilter-slide-down') ) {
								sh( false, true );
								show = true;
							}
						}
					}
					
					init( true );
					
					idx = self._buttonsMore.length;
					
					return {
						refresh: function() {
							_level0.find('.mfilter-content-wrapper').removeAttr('style');
							_level0.find('.mfilter-button-more').remove();
							
							init();
						}
					};
				})( _level0 ));
			}
			
			(function(){
				if( displayLiveFilter < 1 || content.find('.mfilter-option').length < displayLiveFilter ) {
					displayLiveFilter = 0;
					
					return;
				}
				
				content.prepend(jQuery('<div class="mfilter-live-filter">')
					.append(jQuery('<input type="text" id="mfilter-live-filter-input-' + i + '" />'))
				);
				wrapper.find('> .mfilter-options > div').attr('id', 'mfilter-live-filter-list-' + i);

				_level0.addClass('mfilter-live-filter-init');

				jQuery('#mfilter-live-filter-list-' + i).liveFilter('#mfilter-live-filter-input-'+i, '.mfilter-visible,.mfilter-should-visible,.mfilter-disabled', {
					'filterChildSelector' : 'label',
					'after' : function(contains, containsNot){
						var list = jQuery('#mfilter-live-filter-list-' + i);

						contains.removeClass('mfilter-should-visible').addClass('mfilter-visible');
						containsNot.removeClass('mfilter-visible').addClass('mfilter-should-visible');

						list.find('> .mfilter-option').removeClass('mfilter-first-child mfilter-last-child');
						
						list.find('> .mfilter-option:not(.mfilter-hide):not(.mfilter-hide-by-live-filter):first').addClass('mfilter-first-child');
						list.find('> .mfilter-option:not(.mfilter-hide):not(.mfilter-hide-by-live-filter):last').addClass('mfilter-last-child');

						if( idx !== null ) {
							if( displayListOfItems == 'scroll' ) {
								self._scrolls[idx].refresh();
							} else if( displayListOfItems == 'button_more' ) {
								self._buttonsMore[idx].refresh();
							}
						}
						
						if( self._relativeScroll != null ) {
							self._relativeScroll.refresh();
						}
					}
				});
					
				_level0.addClass('mfilter-live-filter-init');
				
				self._liveFilters.push({
					refresh: function(){
						content.find('.mfilter-live-filter')[content.find('.mfilter-option:not(.mfilter-hide)').length <= displayLiveFilter?'hide':'show']();
					},
					check: function() {
						$('#mfilter-live-filter-input-'+i).trigger('keyup');
					}
				});
				
				self._liveFilters[self._liveFilters.length-1].refresh();
			})();

			if( ! self._box.hasClass('mfilter-content_top') && heading.hasClass( 'mfilter-collapsed' ) ) {
				content.hide();
			}
		});
	},
			
	/**
	 * Inicjuj nagłówki
	 */
	_initHeading: function() {
		var self = this;
		
		if( self._box.hasClass('mfilter-content_top') )
			return;
		
		self._box.find('.mfilter-heading').click(function(){
			var opts = jQuery(this).parent().find('> .mfilter-content-opts');

			if( jQuery(this).hasClass('mfilter-collapsed') ) {
				opts.slideDown('normal', function(){
					if( self._relativeScroll != null )
						self._relativeScroll.refresh();
				});
				jQuery(this).removeClass('mfilter-collapsed');
			} else {
				opts.slideUp('normal', function(){
					if( self._relativeScroll != null )
						self._relativeScroll.refresh();
				});
				jQuery(this).addClass('mfilter-collapsed');
			}
		});
	},
	
	_initCategoryRelated: function() {
		var self	= this;
		
		self._box.find('.mfilter-filter-item.mfilter-related').each(function(){
			var $li			= jQuery(this),
				seoName		= $li.attr('data-seo-name'),
				autoLevels	= $li.attr('data-auto-levels'),
				fields		= $li.find('select[data-type="category-related"]');
			
			fields.each(function(i){
				if( ! autoLevels && i == fields.length - 1 ) {
					jQuery(this).change(function(){
						self.runAjax();
					});
				} else {						
					function eChange( $self, id ) {
						var $this = $self.parent().attr('data-id', id),
							labels = $this.parent().attr('data-labels').split('#|#');
						
						$self.change(function(){
							var cat_id = $self.val();
							
							$next = $this.next().find('select');
							$parent = $next.parent();
							label = typeof labels[id+1] == 'undefined' ? $parent.attr('data-next-label') : labels[id+1];

							if( cat_id ) {
								$next.html('<option value="">' + self._options.text.loading + '</option>');
								$next.prop('selectedIndex', 0);

								jQuery.post( self._ajaxUrl( self._options.ajaxCategoryUrl ), { 'cat_id' : cat_id }, function( response ){
									var data = jQuery.parseJSON( response );

									if( data.length && autoLevels ) {
										var $li = jQuery('<li>');

										$this.after( $li );
										$next = jQuery('<select>').appendTo( $li );
										
										if( ! label )
											label = MegaFilterLang.text_select;

										eChange( $next, id+1 );
									}

									$next.html('<option value="">' + label + '</option>');
									$next.prop('selectedIndex', 0);

									for( var i = 0; i < data.length; i++ ) {
										$next.append( '<option value="' + data[i].id + '">' + data[i].name + '</option>' );
									}

									if( autoLevels ) {
										if( ! data.length )
											self.runAjax();
									} else if( ! data.length )
										self.runAjax();
								});
							}

							var $p = $parent;

							while( $p.length ) {
								if( autoLevels ) {
									var $t = $p;
									$p = $p.next();
									$t.remove();
								} else {
									$p.find('select').prop('selectedIndex', 0).find('option[value!=""]').remove();
									$p = $p.next();
								}
							}

							if( ! cat_id ) {
								var beforeVal	= self.urlToFilters()[seoName],
									afterVal	= self.filters()[seoName];

								if( typeof beforeVal == 'undefined' )
									beforeVal = [-1];

								if( typeof afterVal == 'undefined' )
									afterVal = [-1];

								if( beforeVal.toString() != afterVal.toString() )
									self.runAjax();
							}
						});
					}

					eChange( jQuery(this), i );
				}
			});
		});
	},
	
	/**
	 * Inicjuj zdarzenia
	 */
	_initEvents: function() {
		var self = this;
		
		function val( $input ) {
			var val = $input.val(),
				parent = $input.parent().parent();
			
			if( $input.attr('type') == 'checkbox' || $input.attr('type') == 'radio' ) {
				val = $input.is(':checked');
				
				if( ! self._options.showNumberOfProducts )
					parent.find('.mfilter-counter')[val?'addClass':'removeClass']('mfilter-close');
			}
			
			parent[val?'addClass':'removeClass']('mfilter-input-active');
		}
		
		self._box.find('input[type=checkbox],input[type=radio],select:not([data-type="category-related"])').change(function(){
			if( self._options['refreshResults'] != 'using_button' )
				self.runAjax();
			
			val(jQuery(this));
		});
		
		self._box.find('.mfilter-options .mfilter-option').each(function(){
			var input = jQuery(this).find('input[type=checkbox],input[type=radio]');
			
			if( ! input.length ) return;
			
			jQuery(this).find('.mfilter-counter').bind('click', function(){
				if( ! jQuery(this).hasClass( 'mfilter-close' ) ) return;
				
				input.attr('checked', false).trigger('change');
				//jQuery(this).removeClass('mfilter-close');
			});
		
			val(input);
		});
		
		self._box.find('.mfilter-button a').bind('click', function(){
			if( jQuery(this).hasClass( 'mfilter-button-reset' ) ) {
				self.resetFilters();
			}
			
			self.ajax();
			
			return false;
		});
	},
			
	/**
	 * Uruchom ładowanie
	 */
	runAjax: function() {
		var self = this;
				
		switch( self._options['refreshResults'] ) {
			case 'using_button' :
			case 'immediately' : {
				self.ajax();
					
				break;
			}
			case 'with_delay' : {
				if( self._timeoutAjax )
					clearTimeout( self._timeoutAjax );
					
				self._timeoutAjax = setTimeout(function(){
					self.ajax();
							
					self._timeoutAjax = null;
				}, self._options['refreshDelay'] );
					
				break;
			}
		}
	},
	
	/**
	 * Pobierz aktualny zakres cen
	 */
	getPriceRange: function() {
		var self		= this,
			minInput	= self._box.find('[id="mfilter-opts-price-min"]'),
			maxInput	= self._box.find('[id="mfilter-opts-price-max"]'),
			min			= minInput.val(),
			max			= maxInput.val();		
			
		if( ! /^[0-9]+$/.test( min ) || min < self._options.priceMin )
			min = self._options.priceMin;
		
		if( ! /^[0-9]+$/.test( max ) || max > self._options.priceMax )
			max = self._options.priceMax;			
		
		return {
			min : parseInt( min ),
			max : parseInt( max )
		};
	},
	
	/**
	 * Inicjuj przedział cenowy
	 */
	_initPrice: function() {
		var self		= this,
			priceRange	= self.getPriceRange(),
			filters		= self.urlToFilters(),
			minInput	= self._box.find('[id="mfilter-opts-price-min"]').unbind('change').bind('change', function(){
				changePrice();
			}).val( filters.price ? filters.price[0] : priceRange.min ),
			maxInput	= self._box.find('[id="mfilter-opts-price-max"]').unbind('change').bind('change', function(){
				changePrice();
			}).val( filters.price ? filters.price[1] : priceRange.max ),
			slider		= self._box.find('[id="mfilter-price-slider"]');
		
		self._refreshPrice = function( minMax ) {
			var priceRange = self.getPriceRange();
			
			if( priceRange.min < self._options.priceMin ) {
				priceRange.min = self._options.priceMin;
			}
			
			if( priceRange.max > self._options.priceMax ) {
				priceRange.max = self._options.priceMax;
			}
			
			if( priceRange.min > priceRange.max ) {
				priceRange.min = priceRange.max;
			}
			
			if( priceRange.min.toString() != minInput.val() ) {
				minInput.val( priceRange.min );
			}
			
			if( priceRange.max.toString() != maxInput.val() ) {
				maxInput.val( priceRange.max );
			}
			
			slider.slider( 'option', 'values', [ priceRange.min, priceRange.max ] );
			
			if( minMax !== false ) {
				slider.slider( 'option', 'min', self._options.priceMin );
				slider.slider( 'option', 'max', self._options.priceMax );
				slider.slider( 'value', slider.slider('value') );
			}
		}
			
		function changePrice() {
			self._refreshPrice( false );
			
			if( self._options['refreshResults'] != 'using_button' )
				self.runAjax();
		}
		
		slider.css({'margin':'10px 5px 0 5px'}).slider({
			range	: true,
			min		: self._options.priceMin ,
			max		: self._options.priceMax,
			values	: [ priceRange.min, priceRange.max ],
			slide	: function( e, ui ) {
				minInput.val( ui.values[0] );
				maxInput.val( ui.values[1] );
			},
			stop	: function( e, ui ) {
				if( self._options['refreshResults'] != 'using_button' )
					self.runAjax();
			}
		});
	},
	
	_initWindowOnPopState: function(){
		var self = this;
			
		if( self._isInit ) return;
			
		window.onpopstate = function(e){
			if( e.state ) {
				self.initUrls();
				self.setFiltersByUrl();
				
				self._render( e.state.html, e.state.json, true );
			} else if( typeof self._cache.mainContent[document.location] != 'undefined' ) {
				self.initUrls();
				self.setFiltersByUrl();
				
				self._render( self._cache.mainContent[document.location].html, self._cache.mainContent[document.location].json, true );				
			}
		}
	},
	
	setFiltersByUrl: function() {
		var self	= this,
			params	= self.urlToFilters();
		
		self.resetFilters();
		
		self._box.find('li[data-type]').each(function(){
			var _this	= jQuery(this),
				type	= _this.attr('data-type'),
				seoName	= _this.attr('data-seo-name'),
				value	= params[seoName];
			
			if( typeof value == 'undefined' )
				return;
					
			switch( type ) {
				case 'rating' :
				case 'stock_status' :
				case 'manufacturers' :
				case 'image' :
				case 'radio' :
				case 'image_list_radio' :
				case 'image_list_checkbox' :
				case 'checkbox' : {
					for( var i in value ) {
						if( typeof value[i] == 'function' ) continue;
						
						_this.find('input[value="' + value[i] + '"]').attr('checked', true).parent().parent().find('.mfilter-counter').addClass('mfilter-close');
					}
					
					break;
				}
				case 'select' : {
					_this.find('select option[value="' + value[0] + '"]').attr('selected', true);
						
					break;
				}
			}
		});
	},
	
	/**
	 * Pokaż loader
	 */
	_showLoader: function() {
		var self = this;
				
		if( self._jqLoader == null && self._options.showLoaderOverResults ) {
			self.__initLoader();
		}
		
		if( self._jqLoaderFilter == null && self._options.showLoaderOverFilter ) {
			self.__initLoaderFilter();
		}
		
		if( self._options.showLoaderOverResults ) {
			(function(){
				var w = self._jqContent.outerWidth(),
					h = self._jqContent.outerHeight(),
					j = self._jqContent.find('.product-list'),
					k = j.length ? j : self._jqContent.find('.product-grid'),
					l = k.length ? k : self._jqContent,
					t = k.length ? k.offset().top - 150 : l.offset().top;

				self._jqLoader
					.css('width', w + 'px')
					.css('height', h + 'px')
					.fadeTo('normal', 1)
					.find('img')
					.css('margin-top', t + 'px');
			})();
		}
		
		if( self._options.showLoaderOverFilter ) {
			(function(){
				var w = self._box.outerWidth(),
					h = self._box.outerHeight();
				
				self._jqLoaderFilter
					.css('width', w + 'px')
					.css('height', h + 'px')
					.fadeTo('normal',1);
			})();
		}
		
		if( self._options.autoScroll ) {
			jQuery('html,body').stop().animate({
				scrollTop: self._jqContent.offset().top + self._options.addPixelsFromTop
			}, 'low', function(){
				self._busy = false;
				self.render();
			});
		} else {
			self._busy = false;
			self.render();
		}
	},
	
	/**
	 * Ukryj loader
	 */
	_hideLoader: function() {
		var self = this;
		
		if( self._jqLoader !== null ) {		
			self._jqLoader.remove();
			self._jqLoader = null;
		}
		
		if( self._jqLoaderFilter !== null ) {
			self._jqLoaderFilter.remove();
			self._jqLoaderFilter = null;
		}
	},
	
	/**
	 * Pokaż wczytane dane
	 */
	render: function( history ) {
		var self = this;
		
		if( self._lastResponse === '' || self._busy )
			return;
		
		self._hideLoader();
		
		// usuń wszystkie linki do skryptów JS
		self._lastResponse = self._lastResponse.replace( /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '' );
		
		var tmp = jQuery('<tmp>').html( self._lastResponse ),
			content = tmp.find(self._contentId), // znajdź treść główną
			json = self._options.showNumberOfProducts ? tmp.find('#mfilter-json') : null; // informacje JSON zawierające dane o ilości produktów wg kategorii
			
		if( ! content.length )
			content = tmp.find('#mfilter-content-container');
			
		if( content.length ) {
			var styles = self._jqContent.html().match( /<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/gi );
			
			if( styles != null && styles.length ) {
				for( var i = 0; i < styles.length; i++ ) {
					jQuery('head:first').append( styles[i] );
				}
			}
			
			self._render( content.html(), self._options.showNumberOfProducts && json && json.length ? json.html() : null, history );
			
			self._lastResponse = '';
		} else {
			self.reload();
		}
	},
	
	_render: function( html, json, history ) {
		var self = this;
		
		if( json ) {
			self._parseInfo( json );
		}
		
		self.beforeRender( self._lastResponse, html, json );
			
		self._jqContent.html( html );
			
		if( history !== true ) {
			try {
				window.history.pushState({
					'html'	: html,
					'json'	: json
				}, '', self.createUrl() );
			} catch( e ) {}
		}
			
		if( self._box.hasClass( 'mfilter-content_top' ) ) {
			self._jqContent.prepend( self._box.removeClass('init') );
			self.init( self._box, self._options );
		}
			
		if( typeof jQuery.totalStorage == 'function' && jQuery.totalStorage('display') ) {
			display_MFP( jQuery.totalStorage('display') );
		} else if( typeof jQuery.cookie == 'function' && jQuery.cookie('display') ) {
			display_MFP( jQuery.cookie('display') );
		} else {
			display_MFP( 'list' );
		}
			
		for( var i in self ) {
			if( i.indexOf( '_initAlways' ) === 0 && typeof self[i] == 'function' ) {
				self[i]();
			}
		}
		
		for( var f = 0; f < self._liveFilters.length; f++ ) {
			self._liveFilters[f].check();
		}
		
		// Support for Product Quantity Extension (15186)
		if( typeof pq_initExt == 'function' )
			pq_initExt();
		
		self.afterRender( self._lastResponse, html, json );
	},
	
	beforeRequest: function(){},
	
	beforeRender: function(){},
	
	afterRender: function(){},
	
	/**
	 * Załaduj dane
	 */
	ajax: function() {
		var self = this;
		
		if( self._busy ) {
			self._waitingChanges = true;
			
			return;
		}
		
		var url		= [ self._options.routeProduct, self._options.routeHome ].indexOf( self._options.route ) > -1 ? self.createUrl( self._options.ajaxResultsUrl ) : self.createUrl(),
			cname	= url + self._options.idx;
		
		if( [ self._options.routeProduct, self._options.routeHome ].indexOf( self._options.route ) > -1 ) {
			window.location.href = url;return;
		}
		
		self._busy = true;
		self._lastResponse = '';
		self._showLoader();
		
		if( typeof self._params['page'] != 'undefined' )
			delete self._params['page'];
			
		if( typeof self._cache.lastResponse[cname] != 'undefined' ) {
			self._lastResponse = self._cache.lastResponse[cname];
			
			setTimeout(function(){
				self._busy = false;
				self.render();
			}, 100);
			
			return;
		}
		
		self.beforeRequest();
		
		jQuery.ajax({
			'type'		: 'GET',
			'url'		: self._ajaxUrl( url ),
			'timeout'	: 60 * 1000,
			'cache'		: false,
			'data'		: {
				'mfilterAjax'	: '1',
				'mfilterIdx'	: self._options.idx,
				'mfilterBTypes'	: self.baseTypes().join(',')
			},
			'success'	: function( response ) {
				self._busy = false;
				
				if( response ) {
					self._lastResponse	= response;
					self._cache.lastResponse[cname]	= response;
					self.render();
					
					if( self._waitingChanges ) {
						self._waitingChanges = false;
						self.ajax();
					}
				} else {
					self.reload();
				}
			},
			'error'		: function() {
				self.reload();
			}
		});
	},
	
	/**
	 * Utwórz pełny adres URL
	 */
	createUrl: function( url, attribs ) {
		var self	= this,
			params	= self.paramsToUrl( url, attribs ),
			filters	= self.filtersToUrl(),
			urlSep	= self._urlSep;
		
		if( typeof url == 'undefined' ) {
			url = self._url;
		} else {
			urlSep = self._parseSep( url.split('#')[0] ).urlSep;
			url = self._parseSep( url.split('#')[0] ).url;
		}
		
		if( params || filters ) {
			url += urlSep.f;
			
			if( params ) {
				url += params;
			}
			
			if( filters ) {
				if( params ) {
					url += urlSep.n;
				}
				
				url += 'mfp' + ( urlSep.n == '&' ? '=' : urlSep.n ) + filters;
			}
		}
		
		return url;
	},
	
	/**
	 * Sprawdź poprawność wpisanego zakresu cen
	 * 
	 * @return bool
	 */
	_validPrice: function( min, max ) {
		var self = this;
		
		min = parseInt( min );
		max = parseInt( max );
		
		if( min < self._options.priceMin )
			return false;
		
		if( max > self._options.priceMax )
			return false;
		
		if( min > max )
			return false;
		
		if( min == max && min == self._options.priceMin && max == self._options.priceMax )
			return false;
		
		return true;
	},
	
	/**
	 * Przekształć parametry z adresu URL na obiekt
	 * 
	 * @return object
	 */
	urlToFilters: function() {
		var self = this,
			params = {};
		
		if( ! self._params.mfp )
			return params;
		
		self._params.mfp =  decodeURIComponent( self._params.mfp );
		
		var matches = self._params.mfp.match( /[a-z0-9\-_]+\[[^\]]+\]/g );
		
		if( ! matches )
			return params;
		
		for( var i = 0; i < matches.length; i++ ) {
			var key = matches[i].match( /([a-z0-9\-_]+)\[[^\]]+\]/ )[1],
				val = matches[i].match( /[a-z0-9\-_]+\[([^\]]+)\]/ )[1].split(',');
			
			switch( key ) {
				case 'price' : {
					if( typeof val[0] != 'undefined' && val[1] != 'undefined' ) {
						if( self._validPrice( val[0], val[1] ) )
							params[key] = val;
					}
					
					break;
				}
				default : {
					params[key] = val;
				}
			}
		}
		
		return params;
	},
	
	resetFilters: function(){
		var self	= this;
		
		self._box.find('li[data-type]').each(function(){
			var _this	= jQuery(this),
				type	= _this.attr('data-type');
			
			switch( type ) {
				case 'image' : {
					_this.find('input[type=checkbox]:checked,input[type=radio]:checked').attr('checked', false).parent().removeClass('mfilter-image-checked');
					
					break;
				}
				case 'rating' :
				case 'stock_status' :
				case 'manufacturers' :
				case 'checkbox' : 
				case 'image_list_checkbox' : 
				case 'image_list_radio' : 
				case 'radio' : {
					_this.find('input[type=checkbox]:checked,input[type=radio]:checked').attr('checked', false);
					_this.find('.mfilter-counter').removeClass('mfilter-close');
					
					break;
				}
				case 'search_oc' :
				case 'search' : {
					_this.find('input[id="mfilter-opts-search"]').val( '' );
						
					break;
				}
				case 'price' : {
					_this.find('input[id="mfilter-opts-price-min"]').val( self._options.priceMin );
					_this.find('input[id="mfilter-opts-price-max"]').val( self._options.priceMax );
					_this.find('[id="mfilter-price-slider"]').each(function(){					
						jQuery(this).slider( 'option', 'min', self._options.priceMin );
						jQuery(this).slider( 'option', 'max', self._options.priceMax );
						jQuery(this).slider( 'option', 'values', [ self._options.priceMin, self._options.priceMax ] );
						jQuery(this).slider( 'value', jQuery(this).slider('value') );
					})
					
					break;
				}
				case 'related' :
				case 'select' : {
					_this.find('select option').removeAttr('selected');
					_this.find('select option:first').attr('selected', true);
					_this.find('select').prop('selectedIndex', 0);
					
					if( type == 'related' ) {
						_this.find('select').each(function(i){
							if( i ) {
								if( _this.attr('data-auto-levels') ) {
									jQuery(this).parent().remove();
								} else {
									jQuery(this).find('option[value!=""]').remove();
								}
							}
						});
					}
					
					break;
				}
			}
		});
	},
	
	/**
	 * Pobierz aktualne wartości filtrów
	 * 
	 * @return object
	 */
	filters: function() {
		var self	= this,
			params	= { },
			stockStatusExist = self._box.find('li[data-type="stock_status"]').length ? true : false;
				
		self._box.find('li[data-type]').each(function(){
			var _this	= jQuery(this),
				type	= _this.attr('data-type'),
				seoName	= _this.attr('data-seo-name');
					
			switch( type ) {
				case 'rating' :
				case 'stock_status' : 
				case 'manufacturers' :
				case 'image_list_checkbox' :
				case 'image' :
				case 'checkbox' : {
					_this.find('input[type=checkbox]:checked').each(function(){
						if( typeof params[seoName] == 'undefined' )
							params[seoName] = [];
						
						params[seoName].push( encodeURIComponent( self.encode( jQuery(this).val() ) ) );
					});
					
					break;
				}
				case 'image_list_radio' :
				case 'radio' : {
					_this.find('input[type=radio]:checked').each(function(){
						params[seoName] = [ encodeURIComponent( self.encode( jQuery(this).val() ) ) ];
					});
						
					break;
				}
				case 'price' : {
					var priceRange = self.getPriceRange();
					
					if( priceRange.min != self._options.priceMin || priceRange.max != self._options.priceMax ) {
						if( self._validPrice( priceRange.min, priceRange.max ) )
							params[seoName] = [ priceRange.min, priceRange.max ];
					}
					
					break;
				}
				case 'search_oc' :
				case 'search' : {
					_this.find('input[id="mfilter-opts-search"]').each(function(){
						if( jQuery(this).val() !== '' ) {
							params[seoName] = [ encodeURIComponent( self.encode( jQuery(this).val() ) ) ];
						}
					});
					
					break;
				}
				case 'select' : {
					_this.find('select').each(function(){
						if( jQuery(this).val() )
							params[seoName] = [ encodeURIComponent( self.encode( jQuery(this).val() ) ) ];
					});
						
					break;
				}
				case 'related' : {
					//if( _this.find('select:last').val() ) {
						_this.find('select').each(function(){
							var val = jQuery(this).val();
							
							if( val ) {							
								if( typeof params[seoName] == 'undefined' )
									params[seoName] = [];

								params[seoName].push( val );
							}
						});
					//}
				}
			}
		});
		
		// sprawdź czy domyślnie powinna być zaznaczona opcja "in stock"
		if( self._options.inStockDefaultSelected && typeof params['stock_status'] == 'undefined' ) {
			params['stock_status'] = stockStatusExist ? [] : [ self._options.inStockStatus ];
		}
		
		return params;
	},
			
	/**
	 * Utwórz URL na podstawie parametrów
	 */
	filtersToUrl: function() {
		var self	= this,
			url		= '',
			params	= self.filters();
				
		for( var i in params ) {
			url += url ? ',' : '';
			url += '' + i + '[' + params[i].join(',') + ']';
		}
			
		return url;
	},
	
	/**
	 * Przekształć parametry na adres URL
	 * 
	 * @return string
	 */
	paramsToUrl: function( url, attribs ) {
		var self	= this,
			str		= '',
			params	= typeof url == 'undefined' ? self._params : self._parseUrl( url, attribs ),
			urlSep	= typeof url == 'undefined' ? self._urlSep : self._parseSep( url ).urlSep;
		
		for( var i in params ) {
			if( [ 'mfilter-ajax', 'mfp', 'page' ].indexOf( i ) > -1 ) continue;
			if( typeof url == 'undefined' && typeof self._inUrl[i] == 'undefined' ) continue;
			
			str += str ? urlSep.n : '';
			str += i + ( urlSep.n == '&' ? '=' : urlSep.n ) + params[i];
		}
		
		return str;
	},
		
	/**
	 * @param url string
	 * @param attribs object
	 * @return object
	 */
	_parseUrl: function( url, attribs ) {		
		if( typeof attribs != 'object' )
			attribs = {
				'mfilter-ajax'	: '1'
			};
		
		if( typeof url == 'undefined' )
			return attribs;
			
		var self	= this,
			params, i, name, value, param;
			
		url = url.split('#')[0];
				
		if( url.indexOf( '?' ) > -1 || url.indexOf( '&' ) > -1 ) {
			params = typeof self.parse_url( url ).query != 'undefined' ? self.parse_url( url ).query.split('&') : url.split('&');//( url.indexOf( '?' ) > -1 ? url.split('?')[1] : url ).split('&');
			
			for( i = 0; i < params.length; i++ ) {
				if( params[i].indexOf( '=' ) < 0 ) continue;
				
				param	= params[i].split('=');
				name	= param[0];
				value	= param[1];

				if( ! name ) continue;

				attribs[name] = value;
			}
		} else {
			params	= url.split( ';' );
			name	= null;
				
			for( i = 1; i < params.length; i++ ) {
				if( name === null )
					name = params[i];
				else {
					attribs[name] = params[i];
					name = null;
				}
			}
		}
		
		return attribs;
	},
	
	/**
	 * Przeładuj stronę
	 */
	reload: function() {
		var self = this;
		
		window.location.href = self.createUrl();
	}
};
var MegaFilterLang = {};

function display_MFP(view) {
	if( typeof display == 'function' ) {
		display(view);
		return;
	}
	
	function save( v ) {
		if( typeof jQuery.totalStorage == 'function' ) {
			jQuery.totalStorage('display', v);
		} else if( typeof jQuery.cookie == 'function' ) {
			jQuery.cookie('display', v);
		}
	}
		
	if (view == 'list') {
		jQuery('.product-grid').attr('class', 'product-list');

		jQuery('.product-list > div').each(function(index, element) {
			html  = '<div class="right">';
			html += '  <div class="cart">' + jQuery(element).find('.cart').html() + '</div>';
			html += '  <div class="wishlist">' + jQuery(element).find('.wishlist').html() + '</div>';
			html += '  <div class="compare">' + jQuery(element).find('.compare').html() + '</div>';
			html += '</div>';			

			html += '<div class="left">';

			var image = jQuery(element).find('.image').html();

			if (image != null) { 
				html += '<div class="image">' + image + '</div>';
			}

			var price = jQuery(element).find('.price').html();

			if (price != null) {
				html += '<div class="price">' + price  + '</div>';
			}

			html += '  <div class="name">' + jQuery(element).find('.name').html() + '</div>';
			html += '  <div class="description">' + jQuery(element).find('.description').html() + '</div>';

			var rating = jQuery(element).find('.rating').html();

			if (rating != null) {
				html += '<div class="rating">' + rating + '</div>';
			}

			html += '</div>';

			jQuery(element).html(html);
		});		

		jQuery('.display').html('<b>' + MegaFilterLang.text_display + '</b> ' + MegaFilterLang.text_list + ' <b>/</b> <a onclick="display_MFP(\'grid\');">' + MegaFilterLang.text_grid + '</a>');

		save('list'); 
	} else {
		jQuery('.product-list').attr('class', 'product-grid');

		jQuery('.product-grid > div').each(function(index, element) {
			html = '';

			var image = jQuery(element).find('.image').html();

			if (image != null) {
				html += '<div class="image">' + image + '</div>';
			}

			html += '<div class="name">' + jQuery(element).find('.name').html() + '</div>';
			html += '<div class="description">' + jQuery(element).find('.description').html() + '</div>';

			var price = jQuery(element).find('.price').html();

			if (price != null) {
				html += '<div class="price">' + price  + '</div>';
			}

			var rating = jQuery(element).find('.rating').html();

			if (rating != null) {
				html += '<div class="rating">' + rating + '</div>';
			}

			html += '<div class="cart">' + jQuery(element).find('.cart').html() + '</div>';
			html += '<div class="wishlist">' + jQuery(element).find('.wishlist').html() + '</div>';
			html += '<div class="compare">' + jQuery(element).find('.compare').html() + '</div>';

			jQuery(element).html(html);
		});	

		jQuery('.display').html('<b>' + MegaFilterLang.text_display + '</b> <a onclick="display_MFP(\'list\');">' + MegaFilterLang.text_list + '</a> <b>/</b> ' + MegaFilterLang.text_grid );

		save('grid');
	}
}