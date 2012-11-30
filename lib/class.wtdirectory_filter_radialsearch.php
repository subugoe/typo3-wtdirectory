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
require_once(t3lib_extMgm::extPath('wt_directory') . 'lib/class.wtdirectory_div.php'); // load div class
require_once(t3lib_extMgm::extPath('wt_directory') . 'lib/class.wtdirectory_dynamicmarkers.php'); // file for dynamicmarker functions

class tx_wtdirectory_filter_radialsearch extends tslib_pibase {

	public $extKey = 'wt_directory'; // Extension key
	public $prefixId = 'tx_wtdirectory_pi1'; // Same as class name
	public $scriptRelPath = 'pi1/class.tx_wtdirectory_pi1.php';	// Path to any pi1 script for locallang
	private $mode = 'radialsearch';

	/**
	 * Generate the Radial Search Output
	 *
	 * @param	object		Parent Object
	 * @return	string		generated content
	 */
	public function main($pObj) {
		// Config
    	$this->cObj = $pObj->cObj;
		$this->conf = $pObj->conf;
		$this->piVars = $pObj->piVars;
		$this->content = ''; $this->tmpl = $this->markerArray = array();
		$this->pi_loadLL();
		$this->div = t3lib_div::makeInstance('wtdirectory_div'); // Create new instance for div class
		$this->dynamicMarkers = t3lib_div::makeInstance('tx_wtdirectory_dynamicmarkers'); // New object: TYPO3 dynamicmarker function
		$this->tmpl['filter'][$this->mode] = $this->cObj->getSubpart($this->cObj->fileResource($this->conf['template.']['search']), '###WTDIRECTORY_FILTER_' . strtoupper($this->mode) . '###'); // Load HTML Template
		
		// stop process if not needed
		if (
			$this->pi_getFFvalue($this->conf, $this->mode, 'list') != '1' || // if radialsearch is turned off
			!t3lib_extMgm::isLoaded('rggooglemap', 0) // if googlemap is not installed
		) {
			return '';
		}
		
		// Let's go
		$this->markerArray['###WTDIRECTORY_ACTION###'] = $this->conf['filter.']['radialsearch.']['clearOldFilter'] == 0 ? htmlentities($this->pi_linkTP_keepPIvars_url(array('hash' => 1), 1)) : htmlentities($this->cObj->typolink('x', array('returnLast' => 'url', 'additionalParams' => '&' . $this->prefixId . '[hash]=1', 'parameter' => $GLOBALS['TSFE']->id, 'useCacheHash' => 1))); // target for form
		$this->markerArray['###WTDIRECTORY_METHOD###'] = 'post'; // form method
		if (isset($this->piVars['radialsearch']['radius']) && intval($this->piVars['radialsearch']['radius'])) {
			$this->markerArray['###SELECTED_' . intval($this->piVars['radialsearch']['radius']) . '###'] = ' selected="selected"';
		}
		$this->markerArray['###WTDIRECTORY_RADIALSEARCH_ZIP_VALUE###'] = $this->piVars['radialsearch']['zip'];
		
		// Return
		$this->content = $this->cObj->substituteMarkerArrayCached($this->tmpl['filter'][$this->mode], $this->markerArray); // substitute Marker in Template
		$this->content = $this->dynamicMarkers->main($this->conf, $this->cObj, $this->content); // Fill dynamic locallang or typoscript markers
		$this->content = preg_replace('|###.*?###|i', '', $this->content); // Finally clear not filled markers
		return $this->content;
    }
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_filter_radialsearch.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_filter_radialsearch.php']);
}

?>