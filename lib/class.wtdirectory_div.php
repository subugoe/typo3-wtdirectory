<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Alexander Kellner <alexander.kellner@einpraegsam.net>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_tslib . 'class.tslib_pibase.php');

class wtdirectory_div extends tslib_pibase {

	public $extKey = 'wt_directory'; // Extension key
	public $prefixId = 'tx_wtdirectory_pi1'; // Same as class name
	public $scriptRelPath = 'pi1/class.tx_wtdirectory_pi1.php';	// Path to any script in pi1 for locallang
	
	/**
	 * Function clearName() to disable not allowed letters (only A-Z and 0-9 allowed) (e.g. Perfect Extension -> perfectextension)
	 *
	 * @param	string		Any string
	 * @param	boolean		Lower String required?
	 * @param	integer		Cut after X signs?
	 * @return	string		Cleaned String
	 */
	public function clearName($string, $strtolower = 0, $cut = 0) {
		$string = preg_replace('/[^a-zA-Z0-9]/', '', $string); // replace not allowed letters with nothing
		if ($strtolower) {
			$string = strtolower($string); // string to lower if active
		}
		if ($cut) {
			$string = substr($string,0,$cut); // cut after X signs if active
		}
		
		if (isset($string)) {
			return $string;
		}
	}
	
	/**
	 * Returns a list of tt_address Fields
	 *
	 * @return	array		List of tt_address fields
	 */
	public function getAddressFields() {
		// config
		$fieldarray = array(); // init
		$notAllowedFields = array ( // fields which are not allowed to show
			'uid',
			'pid',
			'tstamp',
			'hidden',
			'deleted'
		);
		
		// query
		$res = mysql_query('SHOW COLUMNS FROM tt_address'); // mysql query
		if ($res) { // If there is a result
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) { // One loop for every result
				if($row['Field'] && !in_array($row['Field'], $notAllowedFields)) {
					$fieldarray[] = $row['Field']; // add fieldname to array
				}
			}
			if (!empty($fieldarray)) {
				return $fieldarray;
			}
		}
	}
	
	/**
	 * Function addFilterParams returns params from current setted piVars (like &tx_wtdirectory_pi1[filter][name]=x&tx_wt...)
	 *
	 * @param	array		Plugin Variables
	 * @return	string		Part of a GET Param
	 */
	public function addFilterParams($piVars) {
		if (!isset($piVars['filter']) && !isset($piVars['radialsearch']) && !isset($piVars['catfilter'])) { // if piVars not set
			return '';
		}
		
		$content = ''; // init
		
		// &tx_wtdirectory_pi1[filter]
		foreach ((array) $piVars['filter'] as $key => $value) { // one loop for every filter
			if (empty($value)) {
				continue;
			}
			$content .= '&' . $this->prefixId . '[filter][' . $key . ']=' . $value;
		}
		
		// &tx_wtdirectory_pi1[radialsearch]
		foreach ((array) $piVars['radialsearch'] as $key => $value) { // one loop for every filter
			if (empty($value)) {
				continue;
			}
			$content .= '&' . $this->prefixId . '[radialsearch][' . $key . ']=' . $value;
		}
		
		// &tx_wtdirectory_pi1[catfilter]
		if (isset($piVars['catfilter'])) {
			$content .= '&' . $this->prefixId . '[catfilter]=' . $piVars['catfilter'];
		}
		
		return $content;
	}
	
	/**
	 * Function marker2value() replaces ###WTDIRECTORY_TTADDRESS_NAME### with its value from database
	 *
	 * @param	string		Any Content String
	 * @param	array		array with values
	 * @return	string		Replaced Content String
	 */ 
	public function marker2value($string, $row) {
		$this->row = $row; // database array
		
		$string = preg_replace_callback ( // Automaticly replace ###UID55### with value from session to use markers in query strings
			'#\#\#\#WTDIRECTORY_TTADDRESS_(.*)\#\#\##Uis', // regulare expression
			array($this, 'replaceIt'), // open function
			$string // current string
		);
	
		return $string;
	}
	
	/**
	 * Function replaceIt() is used for the callback function to replace ###WTDIRECTORY_TTADDRESS_NAME## with value
	 *
	 * @param	array		matches
	 * @return	string		marker content
	 */
	public function replaceIt($field) {
		if (isset($this->row[strtolower($field[1])])) {
			return $this->row[strtolower($field[1])]; // return name (e.g.)
		}
	}
	
	/**
	 * Function piVars2string() helps for a simiular function like keepPiVars and generates a string from current piVars: &var1=1&var2=1
	 *
	 * @return	string		string as GET part
	 */
	public function piVars2string() {
		$content = '';
		
		if (count($this->piVars) > 0) { // only if piVars are set
			foreach ($this->piVars as $key => $value) { // one loop for every first level piVar
				if (!is_array($value)) { // first level
					$content .= '&' . $this->prefixId . '[' . $key . ']=' . $value; // add string for current piVar
				} else { // second level
					foreach ($value as $key2 => $value2) { // one loop for every second level piVar
						if (!is_array($value2)) { // second level
							$content .= '&' . $this->prefixId . '[' . $key . '][' . $key2 . ']=' . $value2; // add string for current piVar
						}
					}
				}
			}
		}
		
		if (!empty($content)) {
			return $content;
		}
	}
	
	/**
	 * Function linker() generates link (email and url) from pure text string within an email or url ('test www.test.de test' => 'test <a href="http://www.test.de">www.test.de</a> test')
	 *
	 * @param	string		Any URL
	 * @param	string		Additional Parameters
	 * @return	string		Linked URL
	 */
    public function linker($link, $additionalParams = '') {
		$link = str_replace("http://www.", "www.", $link);
        $link = str_replace("www.", "http://www.", $link);
        $link = preg_replace("/([\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/i", "<a href=\"$1\"$additionalParams>$1</a>", $link);
        $link = preg_replace("/([\w-?&;#~=\.\/]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?))/i", "<a href=\"mailto:$1\"$additionalParams>$1</a>", $link);
    	
        return $link;
    }

	/**
	 * Function getAddressgroups() lists addressgroups of current tt_address
	 *
	 * @param	integer		Cat Uid
	 * @param	array		TypoScript
	 * @param	object		Content Object
	 * @return	string		Groups
	 */
	public function getAddressgroups($uid, $conf, $cObj) {
		// config
		$this->languid = $GLOBALS['TSFE']->tmpl->setup['config.']['sys_language_uid'] ? $GLOBALS['TSFE']->tmpl->setup['config.']['sys_language_uid'] : 0; // current language uid
		$groups = "";
		$query = array();
		
		// let's go
		if($uid > 0) { // if uid exists
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
				$query['select'] = 'tt_address_group.title, tt_address_group.pid, tt_address_group.uid',
				$query['from'] = 'tt_address_group LEFT JOIN tt_address_group_mm on (tt_address_group_mm.uid_foreign = tt_address_group.uid)',
				$query['where'] = 'tt_address_group_mm.uid_local =' . intval($uid) . ' AND tt_address_group.uid = tt_address_group_mm.uid_foreign',
				$query['orderby'] = 'tt_address_group.title'
			);
			if ($res) { // if there are results
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) { // one loop for every addressgroup
					if ($row['title']) { // if title available
						$tmp_addressgroup = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tt_address_group', array('pid' => $row['pid'], 'uid' => $row['uid'], 'title' => $row['title']), $this->languid, ($this->sys_language_mode == 'strict' ? 'hideNonTranslated' : '')); // language overlay
						$row['title'] = $tmp_addressgroup['title']; // overwrite addressgroup title with localized version
					}
					
					$groups .= $cObj->wrap($row['title'], $conf['wrap.']['addressgroup']); // wrap each group
				}
				if (!empty($groups)) {
					return $groups; // return
				}
			}
		}
	}

	/**
	 * Function conditions4DetailLink() returns true or false if one of the defined fields are filled
	 *
	 * @param	array		Row Array
	 * @param	string		Kind of action
	 * @param	array		TypoScript
	 * @return	boolean
	 */
	public function conditions4DetailLink($row, $what, $conf) {
		if ($conf['morelink_detail.']['condition']) { // if there is an entry in constants
			$allow = 0; // don't allow at the beginning
			$check4fields = t3lib_div::trimExplode(',', $conf['morelink_detail.']['condition'], 1); // like array('fax', 'mobile')
			
			for ($i=0; $i < count($check4fields); $i++ ) { // one loop for every field which should be checked
				if ($row[$check4fields[$i]]) { // if there is an entry
					$allow = 1; // allowed
				}
			}
			
			if ($allow) {
				return true; // if it's allowed, return true
			} else {
				return false; // if it's not allowed, return false
			}
		} else { // no entry, so always show detaillink
			return true;
		}
	}

	/**
	 * Function setPiVars() will set some piVars from conditions (like filter|last_name=a%,filter|last_name=b%)
	 *
	 * @param	array		GET or POST Params from wt_directory
	 * @param	array		TypoScript
	 * @return	void
	 */
	public function setPiVars(&$piVars, $conf) {
		if ($piVars['list'] != 'all' && $conf['filter.']['start'] && count($piVars)==0) { // if tx_wtdirectory_pi1[list]=all is not set AND filter.start is set in constants AND there are no other piVars
			if (strpos($conf['filter.']['start'], 'shownone') === false) { // startfilter was set
				$tmp_startfilter1 = t3lib_div::trimExplode(',', $conf['filter.']['start'], 1); // split at comma (result e.g. filter|last_name=a%)
				for ($i=0; $i < count($tmp_startfilter1); $i++) { // one loop for every filter to set
					$tmp_startfilter2 = t3lib_div::trimExplode('=', $tmp_startfilter1[$i], 1); // split at = (result e.g. filter|last_name)
					$tmp_startfilter3 = t3lib_div::trimExplode('|', $tmp_startfilter2[0], 1); // split at | (result e.g. filter)
					if (count($tmp_startfilter3) == 1) { // piVar in first level
						$piVars[$tmp_startfilter3[0]] = $tmp_startfilter2[1]; // set piVars like tx_wtdirectory_pi1[filter][last_name]=a%
					} else { // piVar in second level
						$piVars[$tmp_startfilter3[0]][$tmp_startfilter3[1]] = $tmp_startfilter2[1]; // set piVars like tx_wtdirectory_pi1[filter][last_name]=a%
					}
				}
			} else { // nothing should be shown at the beginning
				$piVars['list'] = 'none'; // set tx_wtdirectory_pi1[list]=none
			}
		}
	}

	/**
	 * Odd or Even function
	 *
	 * @param	integer		Any Integer
	 * @return	boolean
	 */
	public function alternate($int = 0) {
		if ($int % 2 != 0) { // odd or even
			return false; // return false
		} else { 
			return true; // return true
		}
	}

	/**
	 * Function getAddressFromNews() gets tt_news uid and returns tt_address uid
	 *
	 * @param	integer		tt_news Uid
	 * @return	integer		Address Uid
	 */
	public function getAddressFromNews($uid) {
		if ($uid > 0) { // if there is an uid given
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( // DB query
				'tx_wtdirectory_author auid',
				'tt_news',
				$where_clause = 'uid = ' . intval($uid),
				$groupBy = '',
				$orderBy = '',
				$limit = 1
			);
			if ($res) $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res); // Result in array
			if ($row['auid'] > 0) {
				return $row['auid'];
			}
		}
	}

	/**
	 * Check if current tt_address uid is allowed to show (is in allowed startpage, is in allowed categories)
	 * Used in class.tx_wtdirectory_pi1_detail.php, class.tx_wtdirectory_pi1_vcard.php	 
	 *
	 * @param	string		$uid: Uid that should be checked
	 * @param	object		$pObj: Parent Object
	 * @return	boolean		0/1
	 */
	public function allowedDetailUID($uid, $pObj) {
		// config
		$this->cObj = $pObj->cObj;
		$this->conf = $pObj->conf;
		$this->piVars = $pObj->piVars;
		$cat = array();
		
		// 1. check if requestet tt_address record is in startingpath
		$row = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows (
			'pid', 
			'tt_address', 
			'uid = ' . intval($uid) . $this->cObj->enableFields('tt_address'),
			'', 
			'', 
			1
		);
		if (!isset($row[0]['pid']) || $row[0]['pid'] < 1) { // if there is no pid
			return false; // return 0
		}
		if (!t3lib_div::inList($this->pi_getPidList($this->cObj->data['pages'], $this->cObj->data['recursive']), $row[0]['pid'])) { // if the pid is not within the startingpath
			return false; // return 0
		}
		
		// 2. check if requestet tt_address record is in allowed categories
		if ($this->pi_getFFvalue($this->conf, 'cat_join', 'mainconfig') == 1) { // if catmode == 1 (show only selected categories)
			
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( // DB query
				'tt_address_group.uid',
				'tt_address LEFT JOIN tt_address_group_mm on (tt_address.uid = tt_address_group_mm.uid_local) LEFT JOIN tt_address_group on (tt_address_group_mm.uid_foreign = tt_address_group.uid)',
				'tt_address.uid = ' . intval($uid) . $this->cObj->enableFields('tt_address'),
				'',
				'',
				100000
			);
			if ($res) { // If there is a result
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) { // one loop for every category to current tt_address record
					$cat[] = $row['uid']; // give current uid to array
				}
			}
			
			$allowed_cat = t3lib_div::trimExplode(',', $this->pi_getFFvalue($this->conf, 'category', 'mainconfig'), 1); // array with allowed categories
			
			if ($this->pi_getFFvalue($this->conf, 'category', 'mainconfig') == '') { // if there is no category chosen in backend
				return false; // return 0
			}
			if (count(array_intersect($cat, $allowed_cat)) == 0) { // if there are no congruent values in both arrays
				return false; // return 0
			}
		}
		
		return true; // no errors before, return 1
	}

	/**
	 * Return array with list of tt_address categories for category dropdown
	 *
	 * @param	object		$pObj: Parent Object
	 * @return	array		$cat: Array with all categories
	 */
	public function getCategories($pObj) {
		// config
		$this->conf = $pObj->conf; 
		$this->cObj = $pObj->cObj;
		$cat = array();
		
		// let's go
		$cat = t3lib_div::trimExplode(',', $this->pi_getFFvalue($this->conf, 'category', 'mainconfig'), 1); // all chosen categories in an array
		
		if ($this->conf['filter.']['cat.']['showAllInDropdown']) { // if showAllInDropdown was set via constants
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( // DB query
				'tt_address_group.uid',
				'
					tt_address
					LEFT JOIN tt_address_group_mm on tt_address.uid = tt_address_group_mm.uid_local
					LEFT JOIN tt_address_group on tt_address_group_mm.uid_foreign = tt_address_group.uid
				',
				'1' . $this->cObj->enableFields('tt_address_group'),
				'tt_address_group.uid',
				'',
				100000
			);
			if ($res) { // If there is a result
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) { // one loop for every category to current tt_address record
					if ($row['uid'] > 0 && !in_array($row['uid'], $cat)) { // if there is a uid and no douplicated uids in array wanted (I don't know why group_by will not work)
						$cat[] = $row['uid']; // add uid
					}
				}
			}
		}

		return $cat;
	}
	
	/**
	 * Return base64 string of a given image
	 *
	 * @param	string		$img: Path to image
	 * @return	string		$code: base64 code of an image
	 */
	public function encodeBase64($img) {
		if (!empty($img) && file_exists($img)) { // if file really exists
			$code = t3lib_div::getURL($img); // read image
			$code = base64_encode($code); // encode code
			return $code; // return
		}
	}

	/**
	 * UTF8 en- or decode function
	 *
	 * @param    array        $row: All values
	 * @param    object        $pObj: Parent Object
	 * @return    array        $row: Encoded values
	 */
	public function utf8($row, $pObj) {
	    if ($pObj->conf['vCard.']['utf8'] != '') { // if coding function is set
	        foreach ((array) $row as $key => $string) { // one loop for every value in array
	            if ($pObj->conf['vCard.']['utf8'] == 'utf8encode') $row[$key] = utf8_encode($string);
	            elseif ($pObj->conf['vCard.']['utf8'] == 'utf8decode') $row[$key] = utf8_decode($string);
	        }
	    }
	
	    return $row;
	}

	/**
	 * Get all field values from the database
	 *
	 * @param    string		Field name
	 * @param    object		Content Object
	 * @param    array		TypoScript Configuration
	 * @return   array		Array with field values
	 */
	public function getAllValuesFromField($field, $cObj, $conf) {
		$tree = t3lib_div::makeInstance('t3lib_queryGenerator'); // make instance for query generator class
		$arr = array();
		$table = 'tt_address';
		if ($field == 'addressgroup') { // rewrite table and field for grouptitle
			$table = 'tt_address_group';
			$field = 'title';
		}
		$pids = $tree->getTreeList($cObj->data['pages'], $cObj->data['recursive'], 0, 1);

		// Create Table Query
		$select = $table . '.' . $field;
		$from = '
			tt_address
			LEFT JOIN tt_address_group_mm on tt_address.uid = tt_address_group_mm.uid_local
			LEFT JOIN tt_address_group on tt_address_group_mm.uid_foreign = tt_address_group.uid
		';
		$where = '1';
		$where .= (!empty($pids) ? ' AND ' . $table . '.pid IN (' . $pids . ')' : '');
		if (t3lib_extMgm::isLoaded('static_info_tables', 0) && $this->pi_getFFvalue($conf, 'countryfilter', 'mainconfig')) { // countryfilter only
			$allowedCountries = t3lib_div::trimExplode(',', $this->pi_getFFvalue($conf, 'countryfilter', 'mainconfig'), 1);
			$where .= ' AND (';
			for ($i = 0; $i < count($allowedCountries); $i++) { // one loop for every chosen country
				$fields = $this->getCountriesTitlesFromUid($allowedCountries[$i]);
				foreach ((array) $fields as $key => $value) {
					$where .= 'tt_address.country = "' . $value . '"';
					$where .= ' OR ';
				}
			}
			$where .= '0)';
		}
		$where .= $cObj->enableFields('tt_address');
		$groupby = $table . '.' . $field;
		$orderby = $table . '.' . $field;
		$limit = 100000;
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, $groupby, $orderby, $limit);
		if ($res) { // If there is a result
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) { // one loop for every category to current tt_address record
				$arr[] = $row[$field];
			}
		}

		return $arr;
	}

	/**
	 * Return array tree with list of tt_address categories for category dropdown
	 *
	 * @param	object		$pObj: Parent Object
	 * @return	array		$cat: Array with all categories
	 */
	public function getTreeCategories($pObj) {
		// config
		$this->conf = $pObj->conf;
		$this->cObj = $pObj->cObj;
		$this->cat = $this->pi_getFFvalue($this->conf, 'category', 'mainconfig');
		$notAllowedCategories = $this->conf['filter.']['cat.']['disable']; // not allowed categories
		$arr = array();

		// let's go
		$select = 'tt_address_group.uid, tt_address_group.title, tt_address_group.pid';
		$from = 'tt_address_group';
		$wherePrefix = '1';
		$wherePrefix .= ' AND tt_address_group.uid IN (' . $this->intList($this->cat) . ')';
		if (!empty($notAllowedCategories)) {
			$wherePrefix .= ' AND tt_address_group.uid NOT IN (' . $this->intList($notAllowedCategories) . ')';
		}
		$wherePrefix .= $this->cObj->enableFields('tt_address_group');
		$where = $wherePrefix . ' AND tt_address_group.parent_group = 0';
		$groupby = 'tt_address_group.uid';
		$orderby = 'tt_address_group.title';
		$limit = 100000;

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, $groupby, $orderby, $limit);
		if (!$res) {
			return;
		}

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$arr[$row['uid']] = array(
				'title' => $row['title'],
				//'_children' => ''
			);

			$where = $wherePrefix . ' AND tt_address_group.parent_group = ' . $row['uid'];
			$res2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, $groupby, $orderby, $limit);
			while ($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2)) {
				$arr[$row['uid']]['_children'][$row2['uid']] = array(
					'title' => $row2['title'],
					//'_children' => ''
				);

				$where = $wherePrefix . ' AND tt_address_group.parent_group = ' . $row2['uid'];
				$res3 = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, $groupby, $orderby, $limit);
				while ($row3 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res3)) {
					$arr[$row['uid']]['_children'][$row2['uid']]['_children'][$row3['uid']] = array(
						'title' => $row3['title'],
						//'_children' => ''
					);

					$where = $wherePrefix . ' AND tt_address_group.parent_group = ' . $row3['uid'];
					$res4 = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, $groupby, $orderby, $limit);
					while ($row4 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res4)) {
						$arr[$row['uid']]['_children'][$row2['uid']]['_children'][$row3['uid']]['_children'][$row4['uid']] = array(
							'title' => $row4['title'],
							//'_children' => ''
						);
					}
				}
			}
		}

		if ($this->conf['filter.']['startlevel'] > 0) { // check for another startlevel
			$arr = $arr[$this->conf['filter.']['startlevel']]['_children'];
		}
		return $arr;
	}

	/**
	 * Generate commaseparated list of cats for the drilldown
	 *
	 * @param	object		Parent object
	 * @return	string		Comma separated list of groups like "group1,group5,group6"
	 */
	public function createCatBreadcrumb($pObj) {
		if (intval($pObj->piVars['catfilter']) === 0) {
			return '';
		}
		$cats = array();
		$string = '';

		$cats[] = $pObj->piVars['catfilter'];
		$parent1 = $this->getParentGroupByUid($pObj->piVars['catfilter'], $pObj); // get uid of parent group
		if ($parent1) {
			$cats[] = $parent1;
		}
		$parent2 = $this->getParentGroupByUid($parent1, $pObj); // get uid of parent group
		if ($parent2) {
			$cats[] = $parent2;
		}
		$parent3 = $this->getParentGroupByUid($parent2, $pObj); // get uid of parent group
		if ($parent3) {
			$cats[] = $parent3;
		}
		$parent4 = $this->getParentGroupByUid($parent3, $pObj); // get uid of parent group
		if ($parent4) {
			$cats[] = $parent4;
		}

		$cats = array_reverse($cats); // reverse array
		for ($i = 0; $i < count($cats); $i++) {
			$string .= 'group' . $cats[$i] . ',';
		}
		return t3lib_div::rm_endcomma($string);
	}

	/**
	 * Get uid of parent group if exists
	 *
	 * @param	integer		Current Group UID
	 * @param	object		Parent Object
	 * @return	integer		Parent Group UID or false
	 */
	private function getParentGroupByUid($uid, $pObj) {
		if (intval($uid) === 0) {
			return false;
		}
		$select = 'tt_address_group.parent_group';
		$from = 'tt_address_group';
		$where = '1';
		$where .= ' AND uid = ' . intval($uid);
		$where .= $pObj->cObj->enableFields('tt_address_group');
		$groupby = '';
		$orderby = '';
		$limit = 1;

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, $groupby, $orderby, $limit);
		if (!$res) {
			return;
		}
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

		if ($row['parent_group'] > 0 && $row['parent_group'] != $pObj->conf['filter.']['startlevel']) {
			return $row['parent_group'];
		} else {
			return false;
		}
	}

	/**
	 * Commaseparated list with integer values
	 *
	 * @param	string		Commaseparated list
	 * @return	string		Cleaned Commaseparated list
	 */
	public function intList($list) {
		$intList = t3lib_div::intExplode(',', $list, 1);

		return implode(',', $intList);
	}

	/**
	 * Get localized country title from ISO code (uk => United Kingdom, de => Germany)
	 *
	 * @param	string		ISO Code from country "de" or "uk"
	 * @param	object		Parent Object
	 * @return	string		Localized title from country
	 */
	public function getCountryFromCountryCode($iso, $pObj) {
		if ( // stop if
			$pObj->conf['enable.']['static_info_tables'] == 0  || // rewrite turned off in constants
			strlen($iso) > 3 || // this could not be an ISO code
			!t3lib_extMgm::isLoaded('static_info_tables', 0) // static_info_tables not loaded
		) {
			return $iso;
		}

		// fe engine from static_info_tables
		require_once(t3lib_extMgm::extPath('static_info_tables') . 'pi1/class.tx_staticinfotables_pi1.php');
		$staticInfoObj = &t3lib_div::getUserObj('&tx_staticinfotables_pi1');
		if ($staticInfoObj->needsInit()){
			$staticInfoObj->init();
		}
		$title = $staticInfoObj->getStaticInfoName('COUNTRIES', $iso); // get title from static_info_tables

		return $title;
	}

	/**
	 * Get latitude and longitude from german ZIP Code
	 *
	 * @param	string		URL for geocode table
	 * @return	array		Array with Latitude and Longitude and the Cities Name like
	 *						01067 => 
	 *							lat => 1.12351
	 *							lng => 45.4541
	 *							title => Dresden
	 */
	public function getCoordinatesFromZip($geocodeurl = 'http://fa-technik.adfc.de/code/opengeodb/PLZ.tab') {
		$arr = array();
		$table = t3lib_div::getUrl($geocodeurl); // read url
		$lines = t3lib_div::trimExplode("\n", $table, 1); // split every line
		for ($i = 0; $i < count($lines); $i++) { // one loop for every line
			$line = explode("\t", $lines[$i]);
			$arr[$line[1]] = array (
				'title' => $line[4], // Name of the City
				'lng' => $line[2], // Latitude
				'lat' => $line[3] // Longitude
			);
		}
		
		return $arr;
	}

	/**
	 * Read all country titles from a given static_countries uid
	 *
	 * @param	integer		Uid of a value of static_countries
	 * @return	array		All titles from a country (DE, DEU, Germany, Deutschland)
	 */
	public function getCountriesTitlesFromUid($uid) {
		if (!t3lib_extMgm::isLoaded('static_info_tables', 0)) {
			return array();
		}
		
		// read all relevant field from static_countries
		$fields = array();
		$allFields = $GLOBALS['TYPO3_DB']->admin_get_fields('static_countries');
		foreach ((array) $allFields as $field => $value) {
			// cn_iso_2, cn_iso_3
			if (stristr($field, 'cn_iso_')) {
				$fields[] = $field;
			}
			
			// cn_short_en, cn_short_de
			if (stristr($field, 'cn_short_')) {
				$fields[] = $field;
			}
		}
		
		// get all titles from a given url
		$select = implode(',', $fields);
		$from = 'static_countries';
		$where = 'uid = ' . intval($uid);
		$groupby = '';
		$orderby = '';
		$limit = 1;
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, $groupby, $orderby, $limit);
		if ($res) {
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			return $row;
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_div.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_div.php']);
}

?>