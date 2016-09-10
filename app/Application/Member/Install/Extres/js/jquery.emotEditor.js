//表情编辑器----------------------------------------------------------------------
//1.0.3
(function($) {
	var emotRegStr = "<img([^\<\>(src=)])*src=\""+ _config['domainStatic'] +"images/emot/e([0-9]{0,}).gif\"([^\<\>])*>";
	var emotReg = new RegExp(emotRegStr, "ig");
	var methods = {
		init : function(options) {
			var defaults = {
				emot: false,
				charCount: false,
				focus: false,
				newLine: false,
				allowed: 140,
				warning: 25,
				emotPrefix: 'emot_',
				css: 'countercss',
				counterElement: 'span',
				warningCss: 'warning',
				exceededCss: 'exceeded',
				counterText: '',
				defaultText:'',
				defaultCss: ''
			};
			var options = $.extend(defaults, options);
			
			if(options.emot){
				var emotHtml = '<ul onselectstart="return false">';
				for (i = 100; i <= 155; i++) {
					emotHtml += '<li emotId=' + i + '><img src="' + _config['domainStatic'] + 'images/emot/e' + i + '.gif" class=\"emot_img\" /></li>'
				}
				emotHtml += '</ul>';
			}
			
			//更新字符统计
			function calculate($obj) {
				var $counterObj = $obj.next();
				var count = contentLength($obj.html());
				var available = options.allowed - count;
				if (available <= options.warning && available >= 0) {
					$counterObj.addClass(options.warningCss)
				} else {
					$counterObj.removeClass(options.warningCss)
				}
				if (available < 0) {
					$counterObj.addClass(options.exceededCss)
				} else {
					$counterObj.removeClass(options.exceededCss)
				}
				$counterObj.html(options.counterText + available)
			};
			
			return this.each(function(e) {
				var $obj = $(this);
				var obj = this;
				var objId = $obj.attr('id');
				var $baseObj = $obj.prev();
				var baseObj = $baseObj[0];
				var $emotObj = '';
				var emotMenuOpen = false;
				var isKeydown = false;
				var isCtrl = false;
				var pasting = false;
				var clicking = false;
				var defaultCss = '';
				var caretPos = -1;
				var objRange = [];
				if(options.defaultCss!=''){
					defaultCss = ' ' + options.defaultCss
				}
				else{
					defaultCss = '';
				}
				
				//转为编辑模式
				var j = 0;
				
				if($baseObj.length==0 || $baseObj.attr('edit') != 'editor_'+objId){
					if(isPad){
						$obj.hide().before('<textarea edit="editor_'+objId+'" class="' + $obj.attr("class") + defaultCss + '">'+options.defaultText+'</textarea>');
						$obj.attr("contenteditable", false);
						$baseObj = $obj.prev();
						baseObj = $baseObj[0];
						$baseObj.bind("focus", function(){
							if($baseObj.val()==options.defaultText){
								$baseObj.val('').removeClass(defaultCss);
							}
						})
						.bind("blur", function(){
							if($baseObj.val()==''){
								$baseObj.val(options.defaultText).addClass(defaultCss);
							}
						});
					}
					else{
						$obj.hide().before('<div edit="editor_'+objId+'" class="' + $obj.attr("class") + defaultCss + '" contenteditable="true">'+options.defaultText+'</div>');
						$obj.attr("contenteditable", false);
						$baseObj =  $obj.prev();
						$baseObj.bind("focus", function(){
							$baseObj.html(options.defaultText).attr("contenteditable", false).hide();
							$obj.attr("contenteditable", true).show();
							obj.focus();
							objRange = getRange(obj, objRange);
						});
						
						$baseObj[0].ondragover = function (e) {
							var e = e || window.event;
							e.dataTransfer.dropEffect = 'none';
							return false;
						};
					}
					
					
					//字符计数
					if(options.charCount){
						$obj.after('<' + options.counterElement + ' class="' + options.css + '">' + options.counterText + '</' + options.counterElement + '>');
						//预载一次字符统计
						calculate($obj);
					}
					
					//表情
					if(options.emot){
						$emotObj = $("#"+options.emotPrefix+$obj.attr('id'));
						var $emotMenu = null;
						$emotObj.click(function(e) {
							$(".emotMenu").hide();
							emotMenuOpen = true;
							if($emotMenu==null){
								$emotMenu = jQuery('<div />').addClass('emotMenu');
								$emotObj.after($emotMenu);
								$emotMenu.html(emotHtml).click(function(e){
									e.stopPropagation();
								})
								.find("ul>li").click(function(e) {
									var id = $(this).attr("emotId");
									
									if(isPad){
										baseObj.focus();
										var str = $baseObj.val();
										$baseObj.val();
										$baseObj.val(str+'[em:'+id+']');
										$obj.html(str+'[em:'+id+']');
									}
									else{
										var sHtml = "&nbsp;<img src=\""+ _config['domainStatic'] +"images/emot/e"+id+".gif\">&nbsp;";
										if(!objRange['range'] || $obj.is(":hidden") == true){
											$baseObj.attr("contenteditable", false).hide();
											$obj.attr("contenteditable", true).show();
											obj.focus();
											objRange = getRange(obj, objRange);
											if(browser.ie){
												var range = document.selection.createRange();
												range.pasteHTML(sHtml+'<br>');
												with(range){
													moveStart("character", -1);
													collapse();
													select();
												}
											}else{
												document.execCommand('InsertHtml', false, sHtml);
											}
										}
										else{
											obj.focus();
											if(document.activeElement != obj){
												objRange = getRange();
											}
											if(browser.ie){
												if(!objRange['range'].length==1){
													objRange['range'].select();
													objRange['range'].collapse(false);
													objRange['range'].pasteHTML(sHtml);
													objRange['range'].select();
													var str = $obj.html();
													var reg = new RegExp("([\S\s]*)("+emotRegStr+"&nbsp;)$", "ig");
													if(reg.test(str)){
														objRange['range'].pasteHTML('<br>');
														with(objRange['range']){
															moveStart("character", -1);
															collapse();
															select();
														}
													}
													else if(!/([\S\s]*)(<br>)$/i.test(str)){
														caretPos = getcaretPos(obj);
														$obj.append('<br>');
														setcaretPos(obj, caretPos);
														objRange = getRange(obj, objRange);
													}
												}
												else{
													objRange['range'](0).src = _config['domainStatic'] +"images/emot/e"+id+".gif";
													objRange['range'].select();
												}
											}else{
												var fragment = objRange['range'].createContextualFragment(sHtml);
												var oLastNode = fragment.lastChild;
												objRange['range'].collapse(false);
												//objRange['range'].deleteContents();
												objRange['range'].insertNode(fragment);
												objRange['range'].setEndAfter(oLastNode);//设置末尾位置
												objRange['range'].collapse(false);//合并范围至末尾
												objRange['sel'].removeAllRanges();//清除range 
												objRange['sel'].addRange(objRange['range']);//设置range
											}
										}
										objRange = getRange(obj, objRange);
									}
									
									$emotMenu.css("display", "none");
									if(options.charCount){
										calculate($obj);
									}
									e.stopPropagation();
								});
							}
							else{
								$emotMenu.css("display", "block");
							}
							e.stopPropagation();
						});
					}
					
					if(isPad){
						baseObj.onkeyup = function(){
							$obj.html($baseObj.val());
							if(options.charCount){
								calculate($obj);
							}
						}
						
						baseObj.onkeydown = function(e) {
							if(e.keyCode===13 && !options.newLine){
								return false;
							}
						}
					}
					else{
						var j = 0;
						
						obj.onclick = function(e){
							if(e && e.stopPropagation){
								e.stopPropagation();
							}
							else {
								window.event.cancelBubble = true;
							}
							
							if ( e && e.preventDefault ){
								e.preventDefault();
							}
							else {
								window.event.returnValue = false;
							}
							
							if(clicking){
								return false;
							}
							clicking = true;
							if(browser.ie){
								setTimeout(function(){ //ie下onclick获取焦点需要延时处理
									objRange = getRange(obj, objRange);
									$(".emotMenu").hide();
									clicking = false;
								},400);
							}
							else{
								objRange = getRange(obj, objRange);
								clicking = false;
							}
							if(emotMenuOpen){
								$emotMenu.css("display", "none");
								emotMenuOpen = false;
							}
							if(options.charCount){
								calculate($obj);
							}
							return false;
						}
						
						obj.onfocus = function(e) {
							//焦点负担太重，尽量不做任何处理
						};
						
						obj.onkeyup = function(e) {
							var e = e || event;
							var keyCode = e.keyCode;
							
							if(keyCode===17){ //ctrl
								isCtrl = false;
							}
							
							setTimeout(function(){
								if(keyCode>=32){
									objRange = getRange(obj, objRange);
									
									if(options.charCount){
										calculate($obj);
									}
								}
							},1);	
						};
						
						obj.onpaste = function(e){
							if(pasting){
								return false;
							}
							pasting = true;
							//ie webkit
							if(ua.match(/msie ([\d]+)/) || ua.match(/applewebkit\//)){
								var newNode = document.createElement("textarea");
								newNode.style.top = ($(document).scrollTop() + 30)+"px";
								newNode.style.left = "-10000px";
								newNode.style.height = "500px";
								newNode.style.width = "500px";
								newNode.style.position = "absolute";
								newNode.style.overflow = "hidden";
								$("body").append(newNode);
								if(ua.match(/msie ([\d]+)/)){
									var range = document.selection.createRange();
									newNode.focus();
									document.execCommand("paste");
									var str = $(newNode).val();

									if(range.length==1){
										var newNode = document.createTextNode(str);
										range(0).parentNode.replaceChild(newNode, range(0));//直接替换标签
										newNode = null;
									}
									else{
										obj.focus();
										str = str.replace(/&/g, "&amp;");
										str = str.replace(/</g, "&lt;");
										str = str.replace(/>/g, "&gt;");
										range.pasteHTML(str);
										range.select();
									}
									
									$(newNode).remove();
									setTimeout(function(){
										pasting = false;
										objRange = getRange(obj, objRange);
										if(options.charCount){
											calculate($obj);
										}
									},1);
									return false;
								}
								else if(ua.match(/applewebkit\//)) {
									var selection = window.getSelection();
									var range = selection.getRangeAt(0).cloneRange();
									
									newNode.focus();
									setTimeout(function(){
										var str = $(newNode).val();
										str = str.replace(/&/g, "&amp;");
										str = str.replace(/</g, "&lt;");
										str = str.replace(/>/g, "&gt;");
										
										$(newNode).remove();
										selection.removeAllRanges();//清除range
										selection.addRange(range);//设置range
										document.execCommand('InsertHtml', false, str);
										pasting = false;
										objRange = getRange(obj, objRange);
										if(options.charCount){
											calculate($obj);
										}
									},1);
								}
							}
							else{
								var selection = window.getSelection();
								var range = selection.getRangeAt(0).cloneRange();
								var newNode = document.createElement("div");
								newNode.innerHTML='\uFEFF\uFEFF';
								range.deleteContents();
								range.insertNode(newNode);
								range.selectNodeContents(newNode);
								range.collapse(false);//合并范围至末尾
								selection.removeAllRanges();//清除range 
								selection.addRange(range);//设置range
								setTimeout(function(){
									var str = newNode.innerHTML;
									str = str.replace(/<[^>]+>/g, "");
									obj.removeChild(newNode);
									document.execCommand('InsertHtml', false, str);
									pasting = false;
									objRange = getRange(obj, objRange);
									if(options.charCount){
										calculate($obj);
									}
								},1);
							}
						}
						
						obj.ondragstart = function (e) {
							return false;
						};
						
						obj.ondragover = function (e) {
							var e = e || window.event;
							e.dataTransfer.dropEffect = 'none';
							return false;
						};
						
						obj.onkeydown = function(e) {
							var e = e || event;
							if(isKeydown){
								return false;
							}
							isKeydown = true;
							//alert(e.keyCode);
							if(e.keyCode===17){ //ctrl
								isCtrl = true;
							}
							
							if(e.keyCode===13 || e.keyCode===32){
								if(browser.ie){
									var range = document.selection.createRange();
									var str = '';
									if(range.length==1){
										if(e.keyCode===13){
											if(!options.newLine){alert(1);isKeydown = false;return false;}
											var newNode = document.createElement("<br>");//创建br标签
										}
										else{
											var newNode = document.createTextNode("\u00a0");
										}
										range(0).parentNode.replaceChild(newNode, range(0));//直接替换标签
										newNode = null;
									}
									else{
										if(e.keyCode===13){
											if(!options.newLine){isKeydown = false;return false;}
											range.pasteHTML('<br>');
										}
										else{
											range.pasteHTML('&nbsp;');
										}
										range.select();
									}
									str = $obj.html();
									if(str!='' && !/([\S\s]*)(<br>)$/i.test(str)){
										caretPos = getcaretPos(obj);
										$obj.append('<br>');
										setcaretPos(obj, caretPos);
										objRange = getRange(obj, objRange);
									}
								}
								else{
									var selection = window.getSelection();
									if(selection.focusNode=="[object HTMLBodyElement]"){
										isKeydown = false;
										return false;
									}
									var range = selection.getRangeAt(0);
									if(e.keyCode===13){
										if(!options.newLine){isKeydown = false;return false;}
										var fragment = range.createContextualFragment('<br>\n');
									}
									else{
										var fragment = range.createContextualFragment('&nbsp;');
									}

									var oLastNode = fragment.lastChild;
									range.deleteContents();
									range.insertNode(fragment);
									if(e.keyCode===13){
										var str = $obj.html();
										if(!/([\S\s]*)(\n<br\>)$/i.test(str) && !/([\S\s]*)(<br\><br\>)$/i.test(str)){
											$obj.append('<br>');
										}
									}
									range.setEndAfter(oLastNode);//设置末尾位置
									range.collapse(false);//合并范围至末尾
									selection.removeAllRanges();//清除range 
									selection.addRange(range);//设置range
								}
								setTimeout(function(){
									objRange = getRange(obj, objRange);
									isKeydown = false;
									if(options.charCount){
										calculate($obj);
									}
								},1);
								return false;
							}
							else if(e.keyCode===8){ //退格键
								if(browser.ie){
									setTimeout(function(){
										str = $obj.html();
										if(str!='' && !/([\S\s]*)(<br>)$/i.test(str)){
											caretPos = getcaretPos(obj);
											$obj.append('<br>');
											setcaretPos(obj, caretPos);
											objRange = getRange(obj, objRange);
										}
									},1);						
								}
								setTimeout(function(){
									objRange = getRange(obj, objRange);
									isKeydown = false;
									if(options.charCount){
										calculate($obj);
									}
								},1);
							}
							else if(isCtrl &&e.keyCode===86){
								if(browser.opera){
									$.tipMessage('opera浏览器下，暂时不支持粘贴!', 2, 3000);
									isKeydown = false;
									return false;
								}
								isKeydown = false;
							}
							else{
								isKeydown = false;
							}
						}
						
						obj.oncontextmenu = function(){
							if(browser.opera){
								$.tipMessage('编辑器右键已被禁用!', 2, 3000);
								return false;
							}
						}
						
						if(options.focus){
							if($obj.is(":hidden") == true){
								$baseObj.attr("contenteditable", false).hide();
								$obj.attr("contenteditable", true).show();
							}
							obj.focus();
						}
						
						//卸载编辑器对像
						if(browser.ie==6){
							$(window).unload(function(){
								obj = null;
								$obj = null;
								$emotObj = null;
								$baseObj = null;
							});
						}
						
						$(document).bind('click', function(e){
							if(browser.ie && clicking){
								obj.focus();
							}
							if(emotMenuOpen){
								$emotMenu.css("display", "none");
								emotMenuOpen = false;
							}
						});
					}
				}
				else{
					if($obj.is(":hidden") == true){
						$obj.prev().attr("contenteditable", false).hide();
						$obj.attr("contenteditable", true).show();
					}
					if(isPad){
						baseObj.focus();
					}
					else{
						moveFocusEnd(obj);
					}
				}
			});
		},
		content: function(){
			var str = $(this).html();
			if(!isPad){
				str = str.replace(/(\t|\r|\n)+/ig, ""); //替换制表符
				str = str.replace(emotReg, '[em:$2]'); //替换表情
				
				str = str.replace(/<br>/ig, "\n"); //替换换行符
				str = str.replace(/&nbsp;/g, " ");//转换空格标记
				str = str.replace(/<[^>]+>/g, "");//去掉所有的html标记
				str = str.replace(/&lt;/g, "<"); //替换左尖括号
				str = str.replace(/&gt;/g, ">"); //替换右尖括号
			}
			return str;
		},
		focus: function(){
			var $obj = $(this);
			
			if(isPad){
				var $baseObj = $obj.prev();
				$baseObj[0].focus();
			}
			else{
				var obj = $obj[0];
				var caretPos = 0;
				if($obj.is(":hidden") == true){
					$obj.prev().attr("contenteditable", false).hide();
					$obj.attr("contenteditable", true).show();
					obj.focus();
				}
				else{
					obj.focus();
					if(getcaretPos(obj)<1){
						moveFocusEnd(obj);
						setTimeout(function(){ //webkit下触发click需要延时处理，才能获取正确位置
							$obj.triggerHandler('click');
						},100);
					}
				}
			}
			return this;
		},
		reset: function() {
			var $obj = $(this);
			var $baseObj = $obj.prev();
			if(isPad){
				$baseObj.val('');
				$obj.triggerHandler('blur');
			}
			else{
				$obj.hide().empty().blur().attr("contenteditable", false);
				$baseObj.attr("contenteditable", true).show();
				$obj.triggerHandler('click');
			}
			return this;
		},
		validCharLength: function(){
			return contentLength($(this).html(), 'stripping');
		}
	};
	
	$.fn.emotEditor = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		}
		else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		}
		else {
			alert('错误调用！');
		}
	}
	
	function contentLength(str, type){
		var strLength = 0;
		var lastBr = 0;
		if(isPad){
			str = str.replace(/\[em:([0-9])+\]/ig, " "); //剥离表情
			if(type=="stripping"){
				str = str.replace(/\s/ig, '');
			}
			strLength = str.length;
		}
		else{
			if(str.toLowerCase()!='<br>'){
				if(!browser.ie && /([\S\s]+)(<br>)$/i.test(str)){
					lastBr = 1;
				}
				str = str.replace(/(\t|\r|\n)+/ig, "");
				str = str.replace(/( )+/ig, " "); //转换空格标记
				str = str.replace(emotReg, " "); //剥离表情
				str = str.replace(/<br>/ig, " ");
				str = str.replace(/&nbsp;/ig, " "); //转换空格标记
				str = str.replace(/<[^>]+>/g, "");//去掉所有的html标记
				str = str.replace(/&lt;/g, "<"); //替换左尖括号
				str = str.replace(/&gt;/g, ">"); //替换右尖括号
				
				if(str.length==0){
					strLength = 0;
				}
				else if(type=="stripping"){
					strLength = str.replace(/\s/ig, '').length;
				}
				else if(browser.ie){
					strLength = str.length;
				}
				else{
					strLength = str.length-lastBr;
				}
			}
			else{
				if(!type=="stripping" && browser.ie){
					strLength = 1;
				}
			}
		}
		return strLength;
	}
	
	function countEmot(str){
		var emotMatches = lineMatches = new Array();
		var emotLength = 0;
		var lineLength = 0;

		emotMatches = str.match(emotReg);
		lineMatches = str.match(/(<br>)/ig);
		if(emotMatches==null){
			emotLength = 0;
		}
		else{
			emotLength = emotMatches.length;
		}
		if(lineMatches==null){
			lineLength = 0;
		}
		else{
			lineLength = lineMatches.length;
		}
		
		return emotLength + lineLength;
	}
	
	function moveFocusEnd(obj){
		var $obj = $(obj);
		var str = $obj.html();
		if(str!='' && str!='<br>'){
			//ie特殊处理
			if(browser.ie){
				var strLength = $obj.text().length + countEmot(str);
				if(str.substring(str.length-4).toLowerCase()=='<br>'){
					--strLength;
				}
			}
			else{
				var strLength = $obj.text().length;
			}
			
			setTimeout(function(){
				setcaretPos(obj, strLength);
			},100);
		}
		else{
			setTimeout(function(){
				obj.focus();
			},100);
		}
	}
	
	function getRange(obj, oldRange){
		var arr = [];
		if(document.activeElement != obj){
			return oldRange;
		}
		else{
			if(document.selection){//ie
				arr['range'] = document.selection.createRange();
				if(arr['range'].length==1){
					return oldRange;
				}
			}
			else{
				arr['sel'] = window.getSelection();
				if(arr['sel'].focusNode=="[object HTMLBodyElement]"){
					return oldRange;
				}
				arr['range'] = arr['sel'].getRangeAt(0).cloneRange();
			}
			return arr;
		}
	}

	function getcaretPos(element) {
		var caretPos = 0;
		if (document.selection) { // IE Support
			element.focus(); //将焦点至于element，并且光标位置不会发生改变
			var range = document.body.createTextRange();
			range.moveToElementText(element);
			var sel = document.selection.createRange();
			if(!sel.length==1){
				sel.setEndPoint("StartToStart", range);
				caretPos = contentLength(sel.htmlText);
			}
			else{
				var str = $(element).html();
				var strLength = $(element).text().length + countEmot(str);
				if(str.substring(str.length-4).toLowerCase()=='<br>'){
					--strLength;
				}
				caretPos = strLength;
			}
			return caretPos;
		}
		else if (window.getSelection) {
			var sel = window.getSelection();
			var rng = sel.getRangeAt(0).cloneRange();
			rng.setStart(element, 0);
			caretPos = rng.toString().length;
		}
		return caretPos;
	}
	
	function setcaretPos(element, location) {
		if(document.body.createTextRange) {
			var range = document.body.createTextRange();
			range.moveToElementText(element);
			range.collapse(true);
			range.move('character', location);
			range.select();
		}
		else if(window.getSelection) {
			var nodes = [];
			var getTextNode = function(node) {
				for (var i = 0; i < node.childNodes.length; i++) {
					if (node.childNodes[i].nodeType == 3) {
						nodes.push(node.childNodes[i]);
					} else {
						getTextNode(node.childNodes[i]);
					}
				}
			}

			getTextNode(element);
			var length = 0;
			for (var i = 0; i < nodes.length; i++) {
				length += nodes[i].textContent.length;
				if (length >= location) {
					length -= nodes[i].textContent.length;
					break;
				}
			}
			var sel = window.getSelection();
			sel.collapse(nodes[i], location - length);
			element.focus();
		}
	}
})(jQuery);