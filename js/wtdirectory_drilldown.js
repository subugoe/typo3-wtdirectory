jQuery(document).ready(function() {

	var catName = 'tx_wtdirectory_pi1[catfilter]';
	var initialGroups = $('#cat_container').val();

	// initial settings
	init();

	// sort selectors
	sortSelectors();

	// level 1 was chosed
	$('.startlevel').live('change', function() {
		//hideDeepOptions(1); // show only level 1
		level1($(this).children('option:selected')); // show level 1
		cloneFilterToDefaultSearch($(this).val()); // clone filter settings to a hidden field
	});

	// level 2 was chosed
	$('.select_level2').live('change', function() {
		hideDeepOptions(2); // show only level 1
		level2($(this).children('option:selected')); // show level 2
		cloneFilterToDefaultSearch($(this).val()); // clone filter settings to a hidden field
	});

	// level 3 was chosed
	$('.select_level3').live('change', function() {
		hideDeepOptions(3); // show only level 1
		level3($(this).children('option:selected')); // show level 3
		cloneFilterToDefaultSearch($(this).val()); // clone filter settings to a hidden field
	});

	// level 4 was chosed
	$('.select_level4').live('change', function() {
		hideDeepOptions(4); // show only level 1
		level4($(this).children('option:selected')); // show level 4
		cloneFilterToDefaultSearch($(this).val()); // clone filter settings to a hidden field
	});

	/**
	 * If level1 was chosen
	 *
	 * @parem	object		current element
	 * @return	void
	 */
	function level1(thiselement) {
		hideDeepOptions(1); // show only level 1

		thiselement.attr('selected', 'selected'); // select current element (only important for init)
		var id = getGroupFromClass(thiselement.attr('class')); // get id from current option (group4)
		var element = $('select.initial option.' + id); // select this dom element
		var selector = createSelector(2, element); // create selector for level 2
		if (selector) { // if there will be another selector
			thiselement.parent().after(selector);
			$('.select_level2 option').show();
		}
	}

	/**
	 * If level2 was chosen
	 *
	 * @parem	object		current element
	 * @return	void
	 */
	function level2(thiselement) {
		hideDeepOptions(2); // hide all children options
		$('.wtdirectory_filter_cat select').attr('name', 'dummy'); // delete old name
		$('.wtdirectory_filter_cat select.select_level2').attr('name', catName); // set Name

		thiselement.attr('selected', 'selected'); // select current element (only important for init)
		var id = getGroupFromClass(thiselement.attr('class')); // get id from current option (group4)
		var element = $('select.initial option.' + id); // select this dom element
		var selector = createSelector(3, element); // create selector for level 3
		if (selector) { // if there will be another selector
			thiselement.parent().after(selector);
			$('.select_level3 option').show();
		}
	}

	/**
	 * If level3 was chosen
	 *
	 * @parem	object		current element
	 * @return	void
	 */
	function level3(thiselement) {
		hideDeepOptions(3); // hide all children options
		$('.wtdirectory_filter_cat select').attr('name', 'dummy'); // delete old name
		$('.wtdirectory_filter_cat select.select_level3').attr('name', catName); // set Name

		thiselement.attr('selected', 'selected'); // select current element (only important for init)
		var id = getGroupFromClass(thiselement.attr('class')); // get id from current option (group4)
		var element = $('select.initial option.' + id); // select this dom element
		var selector = createSelector(4, element); // create selector for level 3
		if (selector) { // if there will be another selector
			thiselement.parent().after(selector);
			$('.select_level4 option').show();
		}
	}

	/**
	 * If level4 was chosen
	 *
	 * @parem	object		current element
	 * @return	void
	 */
	function level4(thiselement) {
		$('.wtdirectory_filter_cat select').attr('name', 'dummy'); // delete old name
		$('.wtdirectory_filter_cat select.select_level4').attr('name', catName); // set Name
		thiselement.attr('selected', 'selected'); // select current element (only important for init)
	}
	
	/**
	 * hide children options AND remove children selectors
	 *
	 * @parem	integer		current level (if level 1, so hide 2,3,4,5 and so on...)
	 * @return	void
	 */
	function hideDeepOptions(level) {
		if (level == 1) {
			$('.startlevel .level2, .startlevel .level3, .startlevel .level4').remove();
			$('.wtdirectory_filter_cat .select_level2, .wtdirectory_filter_cat .select_level3, .wtdirectory_filter_cat .select_level4').remove();
			$('.startlevel').attr('name', catName);
			$('.startlevel option').removeAttr('selected');
		} else if (level == 2) {
			$('.startlevel .level3, .startlevel .level4').hide();
			$('.wtdirectory_filter_cat .select_level3, .wtdirectory_filter_cat .select_level4').remove();
			$('.select_level2').attr('name', catName);
		} else if (level == 3) {
			$('.startlevel .level4').hide();
			$('.wtdirectory_filter_cat .select_level4').remove();
			$('.select_level3').attr('name', catName);
		}
	}

	/**
	 * Creates HTML for a further selector
	 *
	 * @param	integer		Level to create for
	 * @param	object		Current DOM object (selected option)
	 * @return	string		Created Selector
	 */
	function createSelector(level, currentOption) {
		var options = ''; // init options string
		var content = ''; // init content string
		var done = 0; // don't stop loop by default
		var used = 0; // don't return something by default

		options += $(currentOption).siblings('.nolevel').outerOption(); // start with "Please Choose"
		$(currentOption).nextAll().each(function(i) {
			if ($(this).hasClass('level' + level) && !done) { // only children (level 2) needed
				options += $(this).outerOption(); // add html code
				used = 1; // return allowed
			}
			if ($(this).hasClass('level' + (level - 1))) { // up to the next siblings element
				done = 1; // stop loop
			}
		});

		content += '<select name="dummy" class="select_level' + level + '">';
		content += options;
		content += '</select>';

		if (used) { // only if there is min one option
			return content;
		}
	}

	/**
	 * Returns Group id from given class
	 *
	 * @parem	string		like "group4 level1"
	 * @return	string		first part of the string
	 */
	function getGroupFromClass(classes) {
		if (classes == undefined) {
			return '';
		}
		classesArr = classes.split(' ');
		return classesArr[0];
	}

	/**
	 * Clone this settings to the default search hidden field (so only one submit button could be used)
	 *
	 * @param	string		Chosen Value
	 * @return	void
	 */
	function cloneFilterToDefaultSearch(value) {
		if ($('.wtdirectory_filter_search input')) { // if there is a default search area
			$('.container_catfilter').remove();
			var hidden = '<input type="hidden" value="' + value + '" name="tx_wtdirectory_pi1[catfilter]" class="container_catfilter" />';
			$('.wtdirectory_filter_search form').append(hidden);
		}
	}

	/**
	 * Sort all selectors
	 *
	 * @return	void
	 */
	function sortSelectors() {
		$('#country').sortOptionsByText(); // sort by value
		if (navigator.appName != "Microsoft Internet Explorer") {
			var tmp_option = $('#country option[selected=selected]').val(); // get value of option with selected="selected"
			if (tmp_option != '' && tmp_option != undefined) { // value
				$('#country').val(tmp_option); // set selectedindex to this element
			} else {
				$('#country').val(''); // set selectedindex to the empty element (first element)
			}
		}
	}

	/**
	 * Initial settings
	 *
	 * @return	void
	 */
	function init() {
		$('select.initial').after($('select.initial').outerSelect()); // clone initial select
		$('select.initial:first').hide().attr('name', 'dummy');
		$('select.initial:last').addClass('startlevel').removeClass('initial');
		if ($('.wtdirectory_filter_search input')) { // if there is a default search area
			$('.wtdirectory_filter_cat_submit').hide(); // hide submit button
		}

		$('.startlevel').removeAttr('onchange'); // remove onchange attribute in selector
		hideDeepOptions(1); // show only level 1

		if (initialGroups != '') {
			var initialGroupsArr = initialGroups.split(','); // get array from initial groups
			$('select.startlevel option.' + initialGroupsArr[0]).attr('selected', 'selected'); // select first level
			level1($('select.startlevel option.' + initialGroupsArr[0])); // show second level
			if (initialGroupsArr[1] != '' && initialGroupsArr[1] != undefined) {
				level2($('select.select_level2 option.' + initialGroupsArr[1])); // show third level
			}
			if (initialGroupsArr[2] != '' && initialGroupsArr[2] != undefined) {
				level3($('select.select_level3 option.' + initialGroupsArr[2])); // show fourth level
			}
			if (initialGroupsArr[3] != '' && initialGroupsArr[3] != undefined) {
				level4($('select.select_level4 option.' + initialGroupsArr[3])); // select fourth level
			}
		}
	}
});

/**
 * like html() but for an outer wrap
 *
 * @param	object		any DOM object
 * @return	void
 */
$.fn.outerOption = function(val) {
	if (val) {
		$(val).insertBefore(this);
		$(this).remove();
	} else {
		return $("<option>").append($(this).clone()).html();
	}
}

/**
 * like html() but for an outer wrap
 *
 * @param	object		any DOM object
 * @return	void
 */
$.fn.outerSelect = function(val) {
	if (val) {
		$(val).insertBefore(this);
		$(this).remove();
	} else {
		return $("<select>").append($(this).clone()).html();
	}
}

/**
 * Sort options in a selector
 *
 * @return	void
 */
$.fn.sort = function() {
	return this.pushStack([].sort.apply(this, arguments), []);
};
$.fn.sortOptions = function(sortCallback) {
	jQuery('option', this).sort(sortCallback).appendTo(this);
	return this;
};
$.fn.sortOptionsByText = function() {
	var byTextSortCallback = function(x, y) {
		var xText = jQuery(x).text().toUpperCase();
		var yText = jQuery(y).text().toUpperCase();
		var xVal = jQuery(x).val();
		var yVal = jQuery(y).val();
		if (xVal == '') { // empty value at first
			return -1;
		}
		if (yVal == '') { // empty value at first
			return 1;
		}
		return (xText < yText) ? -1 : (xText > yText) ? 1 : 0;
	};
	return this.sortOptions(byTextSortCallback);
};