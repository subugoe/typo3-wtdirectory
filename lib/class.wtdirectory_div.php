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

require_once(PATH_tslib.'class.tslib_pibase.php');

class wtdirectory_div extends tslib_pibase {

	var $extKey = 'wt_directory'; // Extension key
	var $prefixId = 'tx_wtdirectory_pi1'; // Same as class name
	var $scriptRelPath = 'pi1/class.tx_wtdirectory_pi1.php';	// Path to any script in pi1 for locallang
	
	
	// Function linker() generates link (email and url) from pure text string within an email or url ('test www.test.de test' => 'test <a href="http://www.test.de">www.test.de</a> test')
    function linker($link,$additinalParams = '') {
		$link = str_replace("http://www.","www.",$link);
        $link = str_replace("www.","http://www.",$link);
        $link = preg_replace("/([\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/i","<a href=\"$1\"$additinalParams>$1</a>", $link);
        $link = preg_replace("/([\w-?&;#~=\.\/]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?))/i","<a href=\"mailto:$1\"$additinalParams>$1</a>",$link);
    	
        return $link;
    }
	
	
	// Function clearName() to disable not allowed letters (only A-Z and 0-9 allowed) (e.g. Perfect Extension -> perfectextension)
	function clearName($string,$strtolower = 0,$cut = 0) {
		$string = preg_replace("/[^a-zA-Z0-9]/","",$string); // replace not allowed letters with nothing
		if($strtolower) $string = strtolower($string); // string to lower if active
		if($cut) $string = substr($string,0,$cut); // cut after X signs if active
		
		if(isset($string)) return $string;
	}
	
	
	// Function getAddressFields() returns tt_address fieldlist in an array
	function getAddressFields() {
		// config
		$fieldarray = array(); // init
		$notAllowedFields = array(
			'uid',
			'pid',
			'tstamp',
			'hidden',
			'deleted'
		); // fields which are not allowed to show
		
		// query
		$res = mysql_query('SHOW COLUMNS FROM tt_address'); // mysql query
		if ($res) { // If there is a result
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) { // One loop for every result
				if($row['Field'] && !in_array($row['Field'], $notAllowedFields)) {
					$fieldarray[] = $row['Field']; // add fieldname to array
				}
			}
			if (!empty($fieldarray)) return $fieldarray;
		}
	}
	
	
	// Function addFilterParams returns params from current setted piVars (like &tx_wtdirectory_pi1[filter][name]=x&tx_wt...)
	function addFilterParams($piVars) {
		if (isset($piVars['filter']) && is_array($piVars['filter'])) { // if filter piVars set
			$content = ''; // init
			foreach ($piVars['filter'] as $key => $value) { // one loop for every filter
				$content .= '&'.$this->prefixId.'[filter]['.$key.']='.$value;
			}
			if (!empty($content)) return $content;
		}
	}
	
	
	// Function marker2value() replaces ###WTDIRECTORY_TTADDRESS_NAME### with its value from database
	function marker2value($string, $row) {
		$this->row = $row; // database array
		
		$string = preg_replace_callback ( // Automaticly replace ###UID55### with value from session to use markers in query strings
			'#\#\#\#WTDIRECTORY_TTADDRESS_(.*)\#\#\##Uis', // regulare expression
			array($this,'replaceIt'), // open function
			$string // current string
		);
	
		return $string;
	}
	
	
	// Function replaceIt() is used for the callback function to replace ###WTDIRECTORY_TTADDRESS_NAME## with value
	function replaceIt($field) {
		if (isset($this->row[strtolower($field[1])])) {
			return $this->row[strtolower($field[1])]; // return name (e.g.)
		}
	}
	
	
	// Function piVars2string() helps for a simiular function like keepPiVars and generates a string from current piVars: &var1=1&var2=1
	function piVars2string() {
		$content = '';
		
		if (count($this->piVars) > 0) { // only if piVars are set
			foreach ($this->piVars as $key => $value) { // one loop for every first level piVar
				if (!is_array($value)) { // first level
					$content .= '&'.$this->prefixId.'['.$key.']='.$value; // add string for current piVar
				} else { // second level
					foreach ($value as $key2 => $value2) { // one loop for every second level piVar
						if (!is_array($value2)) { // second level
							$content .= '&'.$this->prefixId.'['.$key.']['.$key2.']='.$value2; // add string for current piVar
						}
					}
				}
			}
		}
		
		if (!empty($content)) return $content;
	}
	
	
	// Function getAddressgroups() lists addressgroups of current tt_address
	function getAddressgroups($uid, $conf, $cObj) {
		// config
		$this->languid = $GLOBALS['TSFE']->tmpl->setup['config.']['sys_language_uid'] ? $GLOBALS['TSFE']->tmpl->setup['config.']['sys_language_uid'] : 0; // current language uid
		$groups = "";
		$query = array();
		
		// let's go
		if($uid > 0) { // if uid exists
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
				$query['select'] = 'tt_address_group.title, tt_address_group.pid, tt_address_group.uid',
				$query['from'] = 'tt_address_group LEFT JOIN tt_address_group_mm on (tt_address_group_mm.uid_foreign = tt_address_group.uid)',
				$query['where'] = 'tt_address_group_mm.uid_local ='.$uid.' AND tt_address_group.uid = tt_address_group_mm.uid_foreign',
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
				if (!empty($groups)) return $groups; // return
			}
		}
	}
	
	
	// Function conditions4DetailLink() returns true or false if one of the defined fields are filled
	function conditions4DetailLink($row, $what, $conf) {
		if ($conf['morelink_detail.']['condition']) { // if there is an entry in constants
			$allow = 0; // don't allow at the beginning
			$check4fields = t3lib_div::trimExplode(',', $conf['morelink_detail.']['condition'], 1); // like array('fax', 'mobile')
			
			for ($i=0; $i < count($check4fields); $i++ ) { // one loop for every field which should be checked
				if ($row[$check4fields[$i]]) { // if there is an entry
					$allow = 1; // allowed
				}
			}
			
			if ($allow) return true; // if it's allowed, return true
			else return false; // if it's not allowed, return false
		} else { // no entry, so always show detaillink
			return true;
		}
	}
	
	
	// Function setPiVars() will set some piVars from conditions (like filter|last_name=a%,filter|last_name=b%)
	function setPiVars(&$piVars, $conf) {
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
	
	
	// Function alternate() checks if a number is odd or not
	function alternate($int = 0) {
		if ($int % 2 != 0) { // odd or even
			return false; // return false
		} else { 
			return true; // return true
		}
	}
	
	
	// Function getAddressFromNews() gets tt_news uid and returns tt_address uid
	function getAddressFromNews($uid) {
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
			if ($row['auid'] > 0) return $row['auid'];
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
	function allowedDetailUID($uid, $pObj) {
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
	function getCategories($pObj) {
		// config
		$this->conf = $pObj->conf; 
		$this->cObj = $pObj->cObj;
		$cat = array();
		
		// let's go
		$cat = t3lib_div::trimExplode(',', $this->pi_getFFvalue($this->conf, 'category', 'mainconfig'), 1); // all chosen categories in an array
		
		if ($this->conf['filter.']['cat.']['showAllInDropdown']) { // if showAllInDropdown was set via constants
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( // DB query
				'tt_address_group.uid',
				'tt_address LEFT JOIN tt_address_group_mm on (tt_address.uid = tt_address_group_mm.uid_local) LEFT JOIN tt_address_group on (tt_address_group_mm.uid_foreign = tt_address_group.uid)',
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
	function encodeBase64($img) {
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
	function utf8($row, $pObj) {
	
	    if ($pObj->conf['vCard.']['utf8'] != '') { // if coding function is set
	        foreach ((array) $row as $key => $string) { // one loop for every value in array
	            if ($pObj->conf['vCard.']['utf8'] == 'utf8encode') $row[$key] = utf8_encode($string);
	            elseif ($pObj->conf['vCard.']['utf8'] == 'utf8decode') $row[$key] = utf8_decode($string);
	        }
	    }
	
	    return $row;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_div.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_div.php']);
}

?>