function tx_wtdirectory_autocomplete(ctrlId, acId, pid, cat, field, path) {
	document.observe('dom:loaded', function() {
		new Ajax.Autocompleter(ctrlId, acId, path + 'index.php', {
			parameters: 'eID=wtdirectory_autocomplete&pid=' + pid + '&field=' + field + '&cat=' + cat,
			paramName: 'search'
		});
	});
}