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
require_once(t3lib_extMgm::extPath('wt_directory').'lib/class.wtdirectory_dynamicmarkers.php'); // file for dynamicmarker functions


class tx_wtdirectory_pagebrowser extends tslib_pibase {

	var $extKey = 'wt_directory'; // Extension key
	var $prefixId = 'tx_wtdirectory_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_wtdirectory_pi1.php';	// Path to any pi1 script for locallang
	
	function main($conf, $piVars, $cObj, $pbarray) {
		// Config
		$this->conf = $conf;
		$this->piVars = $piVars;
		$this->cObj = $cObj;
		$this->pbarray = $pbarray;
		$this->markerArray = array();
		$this->tmpl = array ('pagebrowser' => $this->cObj->getSubpart($this->cObj->fileResource($this->conf['template.']['pagebrowser']),'###WTDIRECTORY_PAGEBROWSER###')); // Load HTML Template for pagebrowser
		$this->dynamicMarkers = t3lib_div::makeInstance('tx_wtdirectory_dynamicmarkers'); // New object: TYPO3 dynamicmarker function
		
		// let's go
		$this->markerArray['###CURRENT_MIN###'] = $this->pbarray['pointer'] + 1; // Current page: From
		$this->markerArray['###CURRENT_MAX###'] = $this->pbarray['pointer'] + $this->pbarray['overall_cur']; // Current page: up to
		$this->markerArray['###OVERALL###'] = $this->pbarray['overall']; // Overall addresses
		$this->conf['pagebrowser.']['special.']['userFunc.'] = $this->pbarray; // config for pagebrowser userfunc
		if ($this->conf['list.']['perPage'] < $this->pbarray['overall']) $this->markerArray['###PAGELINKS###'] = $this->cObj->cObjGetSingle($this->conf['pagebrowser'], $this->conf['pagebrowser.']); // Pagebrowser menu (show only if needed)
		
		$this->content = $this->cObj->substituteMarkerArrayCached($this->tmpl['pagebrowser'], $this->markerArray); // substitute Marker in Template
		$this->content = $this->dynamicMarkers->main($this->conf, $this->cObj, $this->content); // Fill dynamic locallang or typoscript markers
		$this->content = preg_replace("|###.*?###|i","",$this->content); // Finally clear not filled markers
		if (!empty($this->content) && $this->pbarray['overall'] > 0) return $this->content; // return only if results
		
    }	
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_pagebrowser.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_pagebrowser.php']);
}

?>