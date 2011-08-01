<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Alex Kellner <alexander.kellner@in2code.de>, in2code.de
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
require_once(t3lib_extMgm::extPath('wt_directory') . 'lib/class.wtdirectory_dynamicmarkers.php'); // file for dynamicmarker functions

class tx_wtdirectory_filter_search extends tslib_pibase {

	public $extKey = 'wt_directory'; // Extension key
	public $prefixId = 'tx_wtdirectory_pi1';		// Same as class name
	public $scriptRelPath = 'pi1/class.tx_wtdirectory_pi1.php';	// Path to any pi1 script for locallang

	/**
	 * Generate the list view
	 *
	 * @param	array		TypoScript configuration
	 * @param	array		Plugin variables (GET and POST)
	 * @param	array		content Object
	 * @return	string		generated content
	 */
	public function main($conf, $piVars, $cObj) {

		// Config
    	$this->cObj = $cObj; // cObject
		$this->conf = $conf;
		$this->piVars = $piVars;
		$this->pi_loadLL();
		$this->dynamicMarkers = t3lib_div::makeInstance('tx_wtdirectory_dynamicmarkers'); // New object: TYPO3 dynamicmarker function
		$this->div = t3lib_div::makeInstance('wtdirectory_div'); // Create new instance for div class
		$this->tmpl = $this->markerArray = $this->markerArray2 = $this->markerArray3 = $this->outerArray = $this->subpartArray = $this->subpartArray2 = array(); $content_item = $content_item2 = ''; $i=0; // init
		$this->tmpl['filter']['search'] = $this->cObj->getSubpart($this->cObj->fileResource($this->conf['template.']['search']), '###WTDIRECTORY_FILTER_SEARCH###'); // Load HTML Template
		$this->tmpl['filter']['item'] = $this->cObj->getSubpart($this->tmpl['filter']['search'], '###ITEM###'); // work on subpart 1
		$this->tmpl['filter']['field_input'] = $this->cObj->getSubpart($this->cObj->fileResource($this->conf['template.']['search']), '###WTDIRECTORY_FILTER_SEARCH_INPUT###'); // work on subpart 1
		$this->tmpl['filter']['field_select'] = $this->cObj->getSubpart($this->cObj->fileResource($this->conf['template.']['search']), '###WTDIRECTORY_FILTER_SEARCH_SELECT###'); // work on subpart 1
		$this->tmpl['filter']['item_select'] = $this->cObj->getSubpart($this->tmpl['filter']['field_select'], '###ITEM_SELECT###'); // work on subpart 1
		$this->searchfields = t3lib_div::trimExplode(',', $this->pi_getFFvalue($this->conf, 'search', 'list'), 1); // take searchfieldlist as an array
		$selectfields = t3lib_div::trimExplode(',', $this->conf['filter.']['select'], 1);

		// Fill marker outside loop
		$this->outerArray['###WTDIRECTORY_SEARCH_SUBMITVALUE###'] = $this->pi_getLL('wtdirectory_search_submitbutton', 'go'); // value or submit button
		$this->outerArray['###WTDIRECTORY_SEARCH_TITLE###'] = $this->pi_getLL('wtdirectory_search_title', 'Search form'); // title
		$this->outerArray['###WTDIRECTORY_SEARCH_METHOD###'] = 'post'; // method
		$this->outerArray['###WTDIRECTORY_SEARCH_ACTION###'] = htmlentities($this->pi_linkTP_keepPIvars_url(array('hash' => 1), 1)); // target url for form

		// Fill markers within loop
		foreach ($this->searchfields as $value) { // one loop for every needed searchfield
			$this->markerArray2['###WTDIRECTORY_SEARCH_NAME###'] = $value; // Value of be field
			$this->markerArray2['###WTDIRECTORY_SEARCH_TYPE###'] = 'text'; // only text fields
			$this->markerArray2['###WTDIRECTORY_SEARCH_VALUE###'] = ($this->piVars['filter'][$value] ? $this->piVars['filter'][$value] : ''); // method
			$this->markerArray2['###WTDIRECTORY_SEARCH_LABEL###'] = $this->pi_getLL('wtdirectory_ttaddress_' . $value, ucfirst($value)); // Label for field
			$this->markerArray2['###WTDIRECTORY_SEARCH_PATH###'] = t3lib_div::getIndpEnv('TYPO3_SITE_URL'); //relative serverpath
			$this->markerArray2['###WTDIRECTORY_PID###'] = ($this->cObj->data['pages'] > 0 ? $this->pi_getPidList($this->cObj->data['pages'], $this->cObj->data['recursive']) : ''); // selected pid
			$this->markerArray2['###WTDIRECTORY_CAT###'] = ($this->pi_getFFvalue($this->conf, 'cat_join', 'mainconfig') > 0 && $this->pi_getFFvalue($this->conf, 'category', 'mainconfig') != '' ? $this->pi_getFFvalue($this->conf, 'category', 'mainconfig') : ''); // selected categories

			if (!in_array($value, $selectfields)) { // should be an input field
				$this->markerArray['###FIELD###'] = $this->cObj->substituteMarkerArrayCached($this->tmpl['filter']['field_input'], $this->markerArray2, array());
			} else { // should be a selectbox
				$fieldValues = $this->div->getAllValuesFromField($value, $cObj, $conf); // Get array with all field values
				
				$content_item2 = '';
				for ($i = 0; $i < count($fieldValues); $i++) { // one loop for every Fieldvalue
					$this->markerArray3['###VALUE###'] = $fieldValues[$i];
					$this->markerArray3['###LABEL###'] = $fieldValues[$i];
					if ($value == 'country') { // if country
						$this->markerArray3['###LABEL###'] = $this->div->getCountryFromCountryCode($fieldValues[$i], $this); // possible rewrite from static_info_tables
					}
					if ($this->piVars['filter'][$value] == $fieldValues[$i]) {
						$this->markerArray3['###SELECTED###'] = ' selected="selected"';
					} else {
						$this->markerArray3['###SELECTED###'] = '';
					}
					$content_item2 .= $this->cObj->substituteMarkerArrayCached($this->tmpl['filter']['item_select'], $this->markerArray3);
				}
				$this->subpartArray2['###CONTENT_SELECT###'] = $content_item2;

				$this->markerArray['###FIELD###'] = $this->cObj->substituteMarkerArrayCached($this->tmpl['filter']['field_select'], $this->markerArray2, $this->subpartArray2);
			}

			$content_item .= $this->cObj->substituteMarkerArrayCached($this->tmpl['filter']['item'], $this->markerArray); // add all markers of this loop to the variable
			$i++; // increase counter
		}
		$this->subpartArray['###CONTENT###'] = $content_item; // work on subpart 2

		$this->content = $this->cObj->substituteMarkerArrayCached($this->tmpl['filter']['search'], $this->outerArray, $this->subpartArray); // substitute Marker in Template
		$this->content = $this->dynamicMarkers->main($this->conf, $this->cObj, $this->content); // Fill dynamic locallang or typoscript markers
		$this->content = preg_replace("|###.*?###|i", "", $this->content); // Finally clear not filled markers

		if (!empty($this->content) && $i) {
			return $this->content;
		}
    }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_filter_search.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_filter_search.php']);
}

?>