/*
 * Mysql Ajax Table Editor
 *
 * Copyright (c) 2014 Chris Kitchen <info@mysqlajaxtableeditor.com>
 * All rights reserved.
 *
 * See COPYING file for license information.
 *
 * Download the latest version from
 * http://www.mysqlajaxtableeditor.com
 */

// Set jsPath var and include the language file if it hasn't been included
var scripts = document.getElementsByTagName('script');
var script = scripts[scripts.length - 1];
var pathWithFile = script.getAttribute("src");
var jsPath = pathWithFile.indexOf('/') >= 0 ? pathWithFile.match(/^(.+)\//)[0] : '';
if(typeof(mateDeleteMsg) == 'undefined' || mateDeleteMsg == null) {
	document.write('<script type="text/javascript" src="' + jsPath + 'lang/lang_vars-en.js"></scr' + 'ipt>');
}

// Only needed if using cookie storage (see init options below).
if(typeof($.cookie) == 'undefined' || $.cookie == null) {
	document.write('<script type="text/javascript" src="' + jsPath + 'jquery/js/jquery.cookie.js"></scr' + 'ipt>');
}

if(typeof($.localStorage) == 'undefined' || $.localStorage == null) {
	document.write('<script type="text/javascript" src="' + jsPath + 'jquery/js/jquery.storageapi.min.js"></scr' + 'ipt>');
}

if(typeof($.toJSON) == 'undefined' || $.toJSON == null) {
	document.write('<script type="text/javascript" src="' + jsPath + 'jquery/js/jquery.json.min.js"></scr' + 'ipt>');
}

var mateHashes = {};

var mate = (function(instanceName) {
	
	var defaultTooltipFadeTime = 2500;
		
	var instanceName = instanceName;

	var hashKey = Base64.encode(document.URL.split('#')[0] + '_' + instanceName);
	
	var	displayObjProps = function(object) {
			var properties = '';
			for(var property in object) {
				properties += property+':\n'+object[property]+'\n';
			}
			alert(properties);
		};

		
	return {
		
		ajaxInfo: {hash: '', history: false, intervalId: '', interval: 100, url: '', ajaxLoaderSelector: '#ajaxLoader1', displayErrors: 'inline'},
		
		mateSubmitEmptyUpload: false,
		
		tooltipFadeTime: 2500,

		storage: null,
		
		init: function(sessionData, options) {
			
			options = options || {};
			sessionData = sessionData || {};

			var settings = {
				updateHash: true
				, sessionStorage: false
				, cookieStorage: false
			}

			$.extend(settings, options);

			if(settings.cookieStorage) {
				this.storage = $.cookieStorage;
			}
			else if(settings.sessionStorage) {
				this.storage = $.sessionStorage;
			}
			else {
				this.storage = $.localStorage;
			}
			
			if(this.ajaxInfo.history == false) {
				this.toAjaxTableEditor('update_html','');
			} 
			else {
				mateHashes[instanceName] = {info: '', action: 'update_html', sessionData: sessionData};
				this.initializeHistory();

				if(settings.updateHash) {
					var storedHash = this.storage.get(hashKey);
					storedHash = typeof storedHash == 'string' ? storedHash : '';
					var currentHash = window.location.hash;
					currentHash = currentHash.replace('#','');

					if(storedHash.length == 0) {
						storedHash = Base64.encode($.toJSON(mateHashes));
					}
					
					// Update url in stored hash if needed (to update get variables)
					var storedHashInfo = $.parseJSON(Base64.decode(storedHash));
					if(typeof storedHashInfo[instanceName]['url'] !== 'undefined') {
						if(storedHashInfo[instanceName]['url'] != this.ajaxInfo.url) {
							storedHashInfo[instanceName]['url'] = this.ajaxInfo.url;
							storedHash = Base64.encode($.toJSON(storedHashInfo));
						}
					}

					if(storedHash == currentHash) {
						this.handleHashChange();
					}
					else {
						var forwardUrl = window.location.href.substr(0, window.location.href.indexOf('#'));
						window.location.href = forwardUrl + '#' + storedHash;
					}
				}
			}
		},

		setAjaxInfo: function(info) {
			$.extend(this.ajaxInfo,info);
		},
		
		toAjaxTableEditor: function(action, info, options) {
			var self = this;
			options = options == null ? {} : options;
			options.url = options.url == null ? this.ajaxInfo.url : options.url;
			options.updateHistory = options.updateHistory == null ? false : options.updateHistory;
			
			var sessionData = options.sessionData == null ? this.getSessionData() : options.sessionData;

			if(this.ajaxInfo.history && options.updateHistory) {
				mateHashes[instanceName] = {info: info, action: action, sessionData: sessionData, url: options.url};
				var hash = Base64.encode($.toJSON(mateHashes));
				self.storage.set(hashKey, hash);
				window.location.hash = hash;
			} else {
				self.doAjaxRequest(action, info, sessionData, options.url, options);
			}
		},

		doAjaxRequest: function(action, info, sessionData, url, options) {
			options = options == null ? {} : options;
			var self = this;
			var ajaxData = {};
			ajaxData.action = action;
			ajaxData.info = info;
			ajaxData.sessionData = sessionData;
			url = url == null ? this.ajaxInfo.url : url;
			// console.log(url);
			var json = encodeURIComponent($.toJSON(ajaxData));
			if($(self.ajaxInfo.ajaxLoaderSelector) != null) {
				$(self.ajaxInfo.ajaxLoaderSelector).removeClass('hidden');
			}
			$.ajax({
				url: url,
				type:'post',
				data: 'json='+json,
				success: 
				function(responseText) {
					if(responseText.error) { alert(responseText.error); return; }
					try {
						var jsonObject = $.parseJSON(responseText);
						self.displayInfo(jsonObject);
						if(options.onCompleteFun) {
							options.onCompleteFun();
						}
					} catch (exc) {
						// Un-comment for debugging
						responseText = self.br2nl(responseText);
						var errMsg = 'Invalid json sent back from server. Error: ' + exc + '<br />';
						errMsg += 'Server sent: ' + responseText;
						if(self.ajaxInfo.displayErrors == 'inline') {
							$('#' + instanceName + 'tableLayer').prepend(errMsg);
						} else if(self.ajaxInfo.displayErrors == 'alert') {
							alert(errMsg);
						}
						console.log(responseText);
					}
					// Un-comment to view json returned from server
					//console.log(transport.responseText);
				},
				error: function(){ alert(mateErrAjaxUrl) }
			});
		},
		
		br2nl: function(str) {
			return str.replace(/<br\s*\/?>/mg,"\n");
		},
		
		displayInfo: function(info) {
			var self = this;
			var i = 0;
			while(info[i] != null) {
				if(info[i].layer_id != null && info[i].layer_id.length > 0 && $(info[i].layer_id).length == 0) {
					// Un-comment for debugging
					alert(info[i].layer_id + ' does not exist');
				} else {
					if(info[i].where == 'innerHTML') {
						$(info[i].layer_id).html(info[i].value);
					} else if(info[i].where == 'remove') {
						$(info[i].layer_id).remove();
					} else if(info[i].where == 'append') {
						$(info[i].layer_id).append(info[i].value);
					} else if(info[i].where == 'replace') {
						$(info[i].layer_id).replaceWith(info[i].value);
					} else if(info[i].where == 'value') {
						$(info[i].layer_id).val(info[i].value);
					} else if(info[i].where == 'javascript') {
						try {
							eval(info[i].value);
						} catch(e) {
							// Un-comment for debugging
							alert(info[i].value+' did not execute correctly. Error: '+e);
						}
					}
				}
				i = i + 1;
			}
			if($(self.ajaxInfo.ajaxLoaderSelector) != null) {
				$(self.ajaxInfo.ajaxLoaderSelector).addClass('hidden');
			}
		},
		
		checkForHashChange: function() {
			var newHash = window.location.hash;
			newHash = newHash.replace('#','');
			if(newHash != this.ajaxInfo.hash && newHash.length > 0) {
				this.handleHashChange();
			}
		},

		addIntervalCallback: function() {
			if(this.ajaxInfo.history && this.ajaxInfo.intervalId.length == 0) {
				this.ajaxInfo.intervalId = setInterval(instanceName + '.checkForHashChange()',this.ajaxInfo.interval);
			}
			else if(!this.ajaxInfo.history && this.ajaxInfo.intervalId > 0) {
				clearInterval(this.ajaxInfo.intervalId);
			}
		},

		initializeHistory: function() {
			if(history && history.pushState) {
				this.addPopState();
			}
			else {
				this.addIntervalCallback()
			}
		},

		handleHashChange: function() {
			var newHash = window.location.hash;
			newHash = newHash.replace("#","");
			this.ajaxInfo.hash = newHash;
			newHash = Base64.decode(newHash);
			mateHashes = this.tryParseJson(newHash);
			var data = mateHashes[instanceName];
			this.doAjaxRequest(data.action, data.info, data.sessionData, data.url);
		},

		addPopState: function() {
			var eventName = 'popstate';
			if(this.isIe()) {
				eventName = 'hashchange';
			}
			window.addEventListener(eventName, function(event) {
				window[instanceName].handleHashChange();
			});
		},

		isIe: function () {

			var ua = window.navigator.userAgent;
			var msie = ua.indexOf("MSIE ");

			if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
				return true
			}
			return false;
		},
		
		storeSessionData: function(sessionData) {
			var data = this.storage.get(hashKey);
			if(typeof data == 'string' && data.length > 0) {
				data = this.tryParseJson(Base64.decode(data));
				data[instanceName]['sessionData'] = sessionData;
			}
			else {
				data = {};
				data[instanceName] = {info: '', action: 'update_html', sessionData: sessionData};
			}
			this.storage.set(hashKey, Base64.encode($.toJSON(data)));
		},
		
		getSessionData: function() {
			var data = this.storage.get(hashKey);
			if(typeof data == 'string' && data.length > 0) {
				var data = this.tryParseJson(Base64.decode(data));
				data = data[instanceName];
				return data.sessionData;
			}
			return null;
		},

		tryParseJson: function(data) {
			try {
				data = $.parseJSON(data);
				return data;
			}
			catch(exc) {
				var errMsg = 'Error parsing json. Try clearing your browser cache and refresh the page.';
				$('#' + instanceName + 'tableLayer').prepend(errMsg);
				console.log('Could not parse the following json: ' + data);
				console.log('Parse exception: ' + exc);
			}
		},
		
		handleSearch: function() {
			var info = $('#' + instanceName + 'searchString').val();
			this.toAjaxTableEditor('handle_search',info,{updateHistory: true});
		},
		
		clearSearch: function() {
			$('#' + instanceName + 'searchString').val('');	
			this.toAjaxTableEditor('handle_search','',{updateHistory: true});
		},
		
		confirmDeleteRow: function(id,mateRowNum) {
			if(confirm(mateDeleteMsg)) {
				var info = {};
				info[mateRowNum] = id;
				this.toAjaxTableEditor('delete_rows',info);
			}
		},
		
		disableButtons: function() {
			$('#' + instanceName + 'addRowButtons button, #' + instanceName + 'editRowButtons button').each(function(index,btn) {
				$(btn).prop('disabled',true);
			});
		},
		
		enableButtons: function() {
			$('#' + instanceName + 'addRowButtons button, #' + instanceName + 'editRowButtons button').each(function(index,btn){
				$(btn).prop('disabled',false);
			});
		},
		
		gatherInputInfo: function(mateRowNum) {
			mateRowNum = mateRowNum == null ? '' : mateRowNum;
			var info = {};
			var self = this;
			var formId = instanceName + 'add_edit_form' + mateRowNum;
			$('#' + formId + ' :input').each(function(i,inputElem) {
				var inputId = $(inputElem).attr('id');
				if(inputId.length > 0 && $(inputElem).prop('disabled') == false) {
					if($(inputElem).attr('type') == 'radio') {
						if($(inputElem).is(':checked')) {
							info[inputId] = $(inputElem).val();
						}
					} else if(typeof(CKEDITOR) != 'undefined' && CKEDITOR.instances[inputId]) {
						info[inputId] = CKEDITOR.instances[inputId].getData();
					} else {
						info[inputId] = $(inputElem).val();
					}
				}
			});
			return info;
		},
		
		addHiddenForm: function(formId,formHtml) {
			if($('#' + formId).length == 0) {
				$('#' + instanceName+'hiddenFormLayer').append(formHtml);
			}
		},
		
		editRow: function(id) {
			this.toAjaxTableEditor('edit_row',id,{updateHistory: true});
		},
		
		moveFormInputs: function(mateRowNum) {
			if(mateRowNum == null) {
				mateRowNum = '';
				var rowId = instanceName + 'add_in_place_row';
			} else {
				var rowId = instanceName + 'row_' + mateRowNum;
			}
			$('#' + rowId).css('height', $('#' + rowId).height()+'px').css('width',$('#' + rowId).width()+'px');
			var rowSel = '#'+rowId;
			$('#' + instanceName + 'add_edit_form'+mateRowNum).html('');
			$(rowSel+' td,' + rowSel + ' input[type=hidden]').each(function(i,elem) {
				$('#' + instanceName + 'add_edit_form' + mateRowNum).append(elem);
			});
		},
		
		displayFormInputs: function(mateRowNum) {
			if(mateRowNum == null) {
				mateRowNum = '';
				var rowId = instanceName + 'add_in_place_row';
			} else {
				var rowId = instanceName + 'row_' + mateRowNum;
			}
			var formSel = '#'+instanceName+'add_edit_form'+mateRowNum;
			$('#' + rowId).html('');
			$(formSel+' td, '+formSel+' input[type=hidden]').each(function(index,elem) {
				$('#' + rowId).append(elem);
			});
		},
		
		updateRow: function(id) {
			this.disableButtons();
			var info = this.gatherInputInfo();
			info['old_primary_key_value'] = id;
			this.toAjaxTableEditor('update_row',info);
		},
		
		updateMultRows: function(idArr) {
			if(confirm(mateUpdateMultMsg.replace(/#num_rows#/,idArr.length))) {
				//disableButtons();
				var info = new Object();
				info.idArr = idArr;
				info.inputInfo = this.gatherInputInfo();
				this.toAjaxTableEditor('update_mult_rows',info);
			}
		},
		
		insertRow: function() {
			this.disableButtons();
			var info = this.gatherInputInfo();
			this.toAjaxTableEditor('insert_row',info);
		},
		
		enterPressed: function(e) {
			var characterCode;
			// NN4 specific code
			if(e && e.which) {
				e = e;
				characterCode = e.which;
			// IE specific code
			} else {
				e = e;
				characterCode = e.keyCode;
			}
			// Enter key is 13
			if(characterCode == 13) {
				return true;
			} else {
				return false;
			}
		},
		
		handleAdvancedSearch: function(numSearches,addCriteria) {
			addCriteria = addCriteria == null ? false : addCriteria;
			var i;
			var info = {addCriteria: addCriteria};
			for(i = 0; i < numSearches; i++) {
				info[i] = new Object();
				info[i]['cols'] = $('#' + instanceName + 'as_cols_' + i).val();
				info[i]['opts'] = $('#' + instanceName + 'as_opts_' + i).val();
				info[i]['strs'] = $('#' + instanceName + 'as_strs_' + i).val();
			}
			this.toAjaxTableEditor('advanced_search',info,{updateHistory: true});
		},
		
		selectCbs: function(mainCb) {
			if($(mainCb).is(':checked')) {
				this.selectAllCbs();
			} else {
				this.deselectAllCbs();
			}
		},
		
		selectAllCbs: function() {
			var self = this;
			$('input.' + instanceName + '_rowCb').each(function(index,cb) {
				if($(cb).prop('disabled') == false) {
					$(cb).prop('checked',true);
					self.changeRowStyle(cb);
				}
			});
		},
		
		deselectAllCbs: function() {
			var self = this;
			$('input.' + instanceName + '_rowCb').each(function(index,cb) {
				if($(cb).prop('disabled') == false) {
					$(cb).prop('checked',false);
					self.changeRowStyle(cb);
				}
			});
		},
		
		changeRowStyle: function(cb) {
			var idParts = $(cb).attr('id').split('_');
			var id = idParts[idParts.length - 1];
			if($(cb).is(':checked')) {
				$('#' + instanceName + 'row_' + id).addClass('selected');
			} else {
				$('#' + instanceName + 'row_' + id).removeClass('selected');				
			}
			//$('#' + instanceName + 'row_' + id + ' td').toggleClass('ui-state-default');
		},
		
		checkBoxClicked: function(cb,event) {
			event.stopPropagation()
			this.changeRowStyle(cb);
		},
		
		rowClicked: function(id) {
			var cb = $('#' + instanceName + 'cb_' + id);
			if($(cb).is(':checked')) {
				$(cb).prop('checked',false);
			} else {
				$(cb).prop('checked',true);
			}
			this.changeRowStyle(cb);
		},
		
		userButtonClicked: function(buttonKey,confirmMsg) {
			var info = new Object();
			var selectedRows = this.getSelectedRows();
			info['buttonKey'] = buttonKey;
			info['checkboxes'] = selectedRows;
			var numRows = Object.size(selectedRows);
			if(numRows == 0) {
				this.showTooltipMsg(mateSelectRow);
			} else if(confirmMsg.length > 0) {
				if(confirm(confirmMsg)) {
					this.toAjaxTableEditor('user_button_clicked',info);
				}
			} else {
				this.toAjaxTableEditor('user_button_clicked',info);
			}
		},
		
		userIconClicked: function(action,info,confirmMsg) {
			if(confirmMsg.length > 0) {
				if(confirm(confirmMsg)) {
					this.toAjaxTableEditor(action,info);
				}
			} else {
				this.toAjaxTableEditor(action,info);
			}
		},
		
		deleteRows: function() {
			var selectedRows = this.getSelectedRows();
			var numRows = Object.size(selectedRows);
			if(numRows == 0) {
				this.showTooltipMsg(mateSelectRow);
			} else {
				var confirmMsg;
				if(numRows == 1) {
					confirmMsg = mateDeleteMsg;
				} else {
					confirmMsg = mateDeleteMultMsg.replace(/#num_rows#/,numRows);
				} if(confirm(confirmMsg)) {
					this.toAjaxTableEditor('delete_rows',selectedRows);
				}
			}
		},
		
		copyRows: function() {
			var info = {'last_row_num': this.getLastRowNum(), 'rows_to_copy': this.getSelectedRows()};
			var numRows = Object.size(info['rows_to_copy']);
			if(numRows == 0) {
				this.showTooltipMsg(mateSelectRow);
			} else {
				this.toAjaxTableEditor('copy_rows',info);
			}
		},
		
		viewRows: function() {
			var selectedRows = this.getSelectedRows();
			var numRows = Object.size(selectedRows);
			if(numRows == 0) {
				this.showTooltipMsg(mateSelectRow);
			} else {
				if(numRows == 1) {
					for(var key in selectedRows) {
						var selectedRow = selectedRows[key];
					}
					this.toAjaxTableEditor('view_row',selectedRow,{updateHistory: true});
				} else {
					this.showTooltipMsg(mateView1Row);
				}
			}
		},
		
		editRows: function() {
			var selectedRows = this.getSelectedRows();
			var numRows = Object.size(selectedRows);
			if(numRows == 0) {
				this.showTooltipMsg(mateSelectRow);
			} else {
				if(numRows == 1) {
					for(var key in selectedRows) {
						var selectedRow = selectedRows[key];
					}
					this.toAjaxTableEditor('edit_row',selectedRow,{updateHistory: true});
				} else {
					this.toAjaxTableEditor('edit_mult_rows',selectedRows,{updateHistory: true});
				}
			}
		},
		
		getSelectedRows: function() {
			var selectedRows = {};
			var counter = 0;
			$('input.' + instanceName + '_rowCb').each(function(index,cb) {
				if($(cb).is(':checked')) {
					var idArr = $(cb).attr('id').split(/_/);
					var mateRowNum = idArr[idArr.length - 1];
					selectedRows[mateRowNum] = $(cb).val();
				}
			});
			return selectedRows;
		},
		
		formatDate: function(dateStr,dateFormat) {
			var date = new Date(dateStr.substring(0,4),dateStr.substring(5,7) - 1,dateStr.substring(8,10),dateStr.substring(11,13),dateStr.substring(14,16),dateStr.substring(17,19));
			info = new Object();
			info["disp_date"] = date.print(dateFormat);
			info["php_date"] = dateStr;
			info["js_date"] = date;
			return info;
		},
		
		resetDate: function(id) {
			$('#' + id).val('0000-00-00');
			$('#' + instanceName + 'show_'+id).html(mateNoDate);
		},
		
		resetScrollTop: function() {
			document.documentElement.scrollTop = 0;
			document.body.scrollTop = 0;
		},
		
		updateHiddenColumns: function() {
			var info = {};
			$('#'+instanceName+'show_hide_cb_layer input[type=checkbox]').each(function(index,input) {
				info[$(input).val()] = $(input).is(':checked');
			});
			this.toAjaxTableEditor('update_hidden_columns',info);
		},
		
		showHideColumn: function(cb,col) {
			if($(cb).is(':checked')) {
				this.toAjaxTableEditor('show_column',col);
			} else {
				this.toAjaxTableEditor('hide_column',col);
			}
		},
		
		disableEnableInput: function(column,cb) {
			column = '#' + column;
			if($(cb).is(':checked')) {
				$(column).prop('disabled',false);
				if($(column + '_req_mark') != null) {
					$(column + '_req_mark').css('display','inline');
				}
			} else {
				$(column).prop('disabled',true);
				if($(column + '_req_mark') != null) {
					$(column + '_req_mark').css('display','none');
				}
			}
		},
		
		updateCheckBoxValue: function(cb,checkedValue,unCheckedValue) {
			if($(cb).is(':checked')) {
				$(cb).val(checkedValue);
			} else {
				$(cb).val(unCheckedValue);
			}
		},
		
		displayFilters: function(filterPosition) {
			if($('#' + instanceName+'header_row') != null) {
				var cellWidth = 0;
				var inputWidth = 0;
				var html = '<tr id="'+instanceName+'filter_row" style="display: table-row;">';
				var headerRow = $('#' + instanceName+'header_row');
				var table = $(headerRow).parent();
				var headerCells = $('#' + instanceName+'header_row td');
				$(headerCells).each( function (index,td) {
					cellWidth = $(td).width();
					html += '<td style="width: '+cellWidth+'px;">';
					if($(td).attr('filterCol')) {
						var filterCol = $(td).attr('filterCol');
						var filterStr = $(td).attr('filterStr');
						inputWidth = cellWidth * .9;
						inputWidth = Math.round(inputWidth);
						html += '<input id="'+instanceName+'filter_'+filterCol+'" class="filterInput" filterCol="'+filterCol+'" type="text" style="width: '+inputWidth+'px;" value="'+filterStr+'" onKeyUp="if(' + instanceName + '.enterPressed(event)){' + instanceName + '.handleFilterSearch(this); return false;}" />';
					} else {
						html += '&nbsp;';
					}
					html += '</td>';
				});
				html += '</tr>';
				if(filterPosition == 'top-before' || filterPosition == 'top') {
					$(headerRow).before(html);
				} else if(filterPosition == 'top-after') {
					$(headerRow).after(html);
				} else {
					$(table).append(html);
				}
			}
		},
		
		removeRows: function(rowNums) {
			$(rowNums).each(function (index,rowNum) {
				$('#' + instanceName+'row_'+rowNum).remove();
			});
			this.updateEvenOddClasses();
			var increment = rowNums.length * -1;
			this.updateRecNums(increment);
			this.deselectAllCbs();
		},
		
		updateEvenOddClasses: function() {
			var className;
			var log = '';
			$('#' + instanceName+'header_row').siblings().each( function(i,val) {
				if($(this).hasClass('even') || $(this).hasClass('odd')) {
					$(this).removeClass('even odd');
					className = (i % 2) != 0 ? 'even' : 'odd';
					$(this).addClass(className);
				}
			});
		},
		
		updateRecNums: function(increment) {
			increment = increment == null ? 1 : increment;
			var endRecNum = parseInt($('#' + instanceName+'end_rec_num').text().replace(/,/g,''),10) + increment;
			$('#' + instanceName+'end_rec_num').html(this.numberWithCommas(endRecNum));
			$('#' + instanceName+'total_rec_num').html(this.numberWithCommas(parseInt($('#' + instanceName+'total_rec_num').text().replace(/,/g,''),10) + increment));
			if(endRecNum == 0) {
				$('#' + instanceName+'start_rec_num').html(this.numberWithCommas(endRecNum));				
			}
		},
		
		numberWithCommas: function(x) {
			return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		},
		
		copyRow: function(id,mateRowNum) {
			var info = {'rows_to_copy': {}, 'mate_row_num': mateRowNum, 'last_row_num': this.getLastRowNum()};
			info['rows_to_copy'][mateRowNum] = id;
			this.toAjaxTableEditor('copy_rows',info);
		},
		
		getLastRowNum: function() {
			return $('#' + instanceName+'last_row_num').val().length == 0 ? 0 : $('#' + instanceName+'last_row_num').val();
		},
		
		handleFilterSearch: function(currentInput) {
			var info = {};
			var filters = {};
			var counter = 0;
			$('.filterInput').each(
				function(index,input) {
					filters[counter] = {filterStr: $(input).val(), filterCol: $(input).attr('filterCol')};
					counter++;
				}
			);
			info.filters = filters;
			if($(currentInput) != null) {
				info.currentFilterId = $(currentInput).attr('id');
			}
			this.toAjaxTableEditor('handle_filter_search',info,{updateHistory: true});
		},
		
		handleExport: function(url) {
			var sessionData = this.getSessionData();
			window.location = url+'&session_data='+encodeURIComponent(sessionData);
		},
		
		getSessionCookieName: function() {
			return sessionCookieName;
		},
		
		showTooltipMsg: function(msgHtml,msgType,fadeTime) {
			fadeTime = fadeTime == null ? defaultTooltipFadeTime : fadeTime;
			msgClass = msgType == 'msg' ? 'mateTooltipMsgDiv' : 'mateTooltipErrDiv';
			var tooltipAttrs = {
				'class': 'mateTooltipDiv ' + msgClass,
				'html': msgHtml,
			};
			var lastTooltip = $('div.mateTooltipDiv').last();
			if($(lastTooltip).html() == msgHtml) {
				$(lastTooltip).stop().css("opacity","1").fadeOut(fadeTime,function() { $(this).remove(); });
			} else {
				if($(lastTooltip).is(':visible')) {
					var msgTop = $(lastTooltip).css('top').replace(/[^-\d\.]/g, '');
					var msgHeight = $(lastTooltip).css('height').replace(/[^-\d\.]/g, '');
					var msgTopNew = parseInt(msgTop) + parseInt(msgHeight) + 30;
					tooltipAttrs.style = 'top: '+msgTopNew+'px';
				}
				var tooltipDiv = $('<div/>',tooltipAttrs);	
				$('body').append(tooltipDiv);
				$(tooltipDiv).fadeIn().fadeOut(fadeTime,function() { $(this).remove(); });
				$(tooltipDiv).hover(
					function() { $(tooltipDiv).stop().css("opacity","1"); },
					function() { $(tooltipDiv).fadeOut(fadeTime,function() { $(this).remove(); }); }
				);
			}
		},
		
		drawNewRowInPlace: function(html,drawAfterRowNum) {
			if(drawAfterRowNum == null || drawAfterRowNum.length == 0) {
				$($('#' + instanceName+'table_form table tr').get().reverse()).each(function(index,tRow) {
					var rowId = $(tRow).attr('id');
					if(rowId != instanceName+'filter_row' && rowId != instanceName+'add_in_place_row') {
						$('#' + rowId).after(html);
						return false;
					}
				});
			} else {
				$('#' + instanceName+'row_' + drawAfterRowNum).after(html);
			}
			this.updateRecNums();
			this.updateEvenOddClasses();
			this.deselectAllCbs();
		},
		
	};
	
});

var Base64 = {
 
	// private property
	_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
 
	// public method for encoding
	encode : function (input) {
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;
 
		input = Base64._utf8_encode(input);
 
		while (i < input.length) {
 
			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);
 
			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;
 
			if (isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}
 
			output = output +
			this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
			this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);
 
		}
 
		return output;
	},
 
	// public method for decoding
	decode : function (input) {
		var output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;
 
		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
 
		while (i < input.length) {
 
			enc1 = this._keyStr.indexOf(input.charAt(i++));
			enc2 = this._keyStr.indexOf(input.charAt(i++));
			enc3 = this._keyStr.indexOf(input.charAt(i++));
			enc4 = this._keyStr.indexOf(input.charAt(i++));
 
			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;
 
			output = output + String.fromCharCode(chr1);
 
			if (enc3 != 64) {
				output = output + String.fromCharCode(chr2);
			}
			if (enc4 != 64) {
				output = output + String.fromCharCode(chr3);
			}
 
		}
 
		output = Base64._utf8_decode(output);
 
		return output;
 
	},
 
	// private method for UTF-8 encoding
	_utf8_encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";
 
		for (var n = 0; n < string.length; n++) {
 
			var c = string.charCodeAt(n);
 
			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}
 
		}
 
		return utftext;
	},
 
	// private method for UTF-8 decoding
	_utf8_decode : function (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;
 
		while ( i < utftext.length ) {
 
			c = utftext.charCodeAt(i);
 
			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			}
			else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			}
			else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}
 
		}
 
		return string;
	}
 
}

Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

