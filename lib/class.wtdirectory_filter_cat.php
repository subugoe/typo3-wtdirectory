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

require_once(PATH_tslib . 'class.tslib_pibase.php'); // include pibase
require_once(t3lib_extMgm::extPath('wt_directory') . 'lib/class.wtdirectory_div.php'); // load div class
require_once(t3lib_extMgm::extPath('wt_directory') . 'lib/class.wtdirectory_dynamicmarkers.php'); // file for dynamicmarker functions

class tx_wtdirectory_filter_cat extends tslib_pibase {

	var $extKey = 'wt_directory'; // Extension key
	var $prefixId = 'tx_wtdirectory_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_wtdirectory_pi1.php';	// Path to any pi1 script for locallang
	
	function main($conf, $piVars) {
		// Config
		global $TSFE;
    	$this->cObj = $TSFE->cObj; // cObject
		$this->conf = $conf;
		$this->piVars = $piVars;
		$this->pi_loadLL();
		$this->outerArray = array(); $this->markerArray = array(); $this->subpartArray = array(); $content_item = ''; // init
		$this->div = t3lib_div::makeInstance('wtdirectory_div'); // Create new instance for div class
		$this->cat = $this->div->getCategories($this); // get tt_address categories in an array
		$this->notAllowedCategories = t3lib_div::trimExplode(',', $this->conf['filter.']['cat.']['disable'], 1); // some categories which are not allowed (via constants)
		$this->dynamicMarkers = t3lib_div::makeInstance('tx_wtdirectory_dynamicmarkers'); // New object: TYPO3 dynamicmarker function
		$this->tmpl['filter']['cat'] = $this->cObj->getSubpart($this->cObj->fileResource($this->conf['template.']['search']),'###WTDIRECTORY_FILTER_CAT###'); // Load HTML Template
		$this->tmpl['filter']['item'] = $this->cObj->getSubpart($this->tmpl['filter']['cat'], '###ITEM###'); // work on subpart 1
		$this->languid = $GLOBALS['TSFE']->tmpl->setup['config.']['sys_language_uid'] ? $GLOBALS['TSFE']->tmpl->setup['config.']['sys_language_uid'] : 0; // current language uid
		
		// Let's go
		if ($this->pi_getFFvalue($this->conf, 'enablecatchoose', 'list') && count($this->cat) > 1) { // if category choose is enabled in flexform and there is more than only one category chosen
			$this->outerArray['###WTDIRECTORY_CAT_TITLE###'] = $this->pi_getLL('wtdirectory_cat_title', 'Search form'); // title
			$this->outerArray['###WTDIRECTORY_CAT_ACTION###'] = $this->conf['filter.']['cat.']['clearOldFilter'] == 0 ? htmlentities($this->pi_linkTP_keepPIvars_url(array('hash' => 1), 1)) : htmlentities($this->cObj->typolink('x', array('returnLast' => 'url', 'additionalParams' => '&'.$this->prefixId.'[hash]=1', 'parameter' => $GLOBALS['TSFE']->id, 'useCacheHash' => 1))); // target for form
			$this->outerArray['###WTDIRECTORY_CAT_METHOD###'] = 'post'; // form method
			
			for ($i=0; $i<count($this->cat); $i++) { // one loop for every chosen category
				if ( (isset($this->notAllowedCategories) && !in_array($this->cat[$i], $this->notAllowedCategories)) || !isset($this->notAllowedCategories) ) {
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( // DB query
						'title, pid, uid',
						'tt_address_group',
						$where_clause = 'tt_address_group.uid = '.$this->cat[$i].$this->cObj->enableFields('tt_address_group'),
						$groupBy = '',
						$orderBy = '',
						$limit = 1
					);
					if ($res) $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res); // Result in array
					
					// Fill inner marker
					$this->markerArray['###WTDIRECTORY_CAT_TITLE###'] = $row['title']; // Title marker
					$tmp_addressgroup = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tt_address_group', array('pid' => $row['pid'], 'uid' => $row['uid'], 'title' => $row['title']), $this->languid, ($this->sys_language_mode == 'strict' ? 'hideNonTranslated' : '')); // language overlay
					if ($tmp_addressgroup['title']) $this->markerArray['###WTDIRECTORY_CAT_TITLE###'] = $tmp_addressgroup['title']; // overwrite addressgroup title with localized version
					
					$this->markerArray['###WTDIRECTORY_CAT_UID###'] = $this->cat[$i]; // uid marker
					$this->markerArray['###WTDIRECTORY_CAT_SELECTED###'] = ($this->piVars['catfilter'] == $this->cat[$i] ? ' selected="selected"' : ''); // uid marker
					$content_item .= $this->cObj->substituteMarkerArrayCached($this->tmpl['filter']['item'], $this->markerArray); // add all markers of this loop to the variable
				}
			}
			$this->subpartArray['###CONTENT###'] = $content_item; // work on subpart 2
			
			$this->content = $this->cObj->substituteMarkerArrayCached($this->tmpl['filter']['cat'], $this->outerArray, $this->subpartArray); // substitute Marker in Template
			$this->content = $this->dynamicMarkers->main($this->conf, $this->cObj, $this->content); // Fill dynamic locallang or typoscript markers
			$this->content = preg_replace("|###.*?###|i", "", $this->content); // Finally clear not filled markers
		}
		
		if (!empty($this->content)) return $this->content;
		
    }
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_filter_cat.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_filter_cat.php']);
}

?>