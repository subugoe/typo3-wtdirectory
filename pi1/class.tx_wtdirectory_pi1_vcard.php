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
require_once(t3lib_extMgm::extPath('wt_directory') . 'lib/class.wtdirectory_div.php'); // load div class
require_once(t3lib_extMgm::extPath('wt_directory') . 'lib/class.wtdirectory_dynamicmarkers.php'); // file for dynamicmarker functions

class tx_wtdirectory_pi1_vcard extends tslib_pibase {

	var $extKey = 'wt_directory'; // Extension key
	var $prefixId      = 'tx_wtdirectory_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_wtdirectory_pi1_vcard.php';	// Path to this script relative to the extension dir.
	var $mode = 'vcard';
	
	function main($conf, $piVars, $cObj) {
        
		// config
    	$this->cObj = $cObj; // cObject
		$this->piVars = $piVars; // make it global
		$this->conf = $conf; // make it global
		$this->pi_loadLL();
		$this->tmpl = $this->markerArray = array(); // init
		$this->tmpl[$this->mode] = $this->cObj->getSubpart($this->cObj->fileResource($this->conf['template.'][$this->mode]), '###WTDIRECTORY_' . strtoupper($this->mode) . '###'); // Load HTML Template
		$this->div = t3lib_div::makeInstance('wtdirectory_div'); // Create new instance for vcard class
		$this->dynamicMarkers = t3lib_div::makeInstance('tx_wtdirectory_dynamicmarkers'); // New object: TYPO3 dynamicmarker function
			
		// Give me all datas of tt_address
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( // DB query
			'*',
			'tt_address',
			$where_clause = 'tt_address.uid = ' . $this->piVars['vCard'] . $this->cObj->enableFields('tt_address'),
			$groupBy = '',
			$orderBy = '',
			$limit = 1
		);
		if ($res) $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res); // Result in array
		
		#if ($row['uid'] > 0 && $this->div->allowedDetailUID($this->piVars['vCard'], $this)) { // address found
		if ($row['uid'] > 0) { // address found
			$this->cObj->start($row, 'tt_address'); // enable .field in typoscript for tt_address
			
			if (isset($this->conf['vCard.']) && is_array($this->conf['vCard.'])) { // if set via ts
				foreach ((array) $this->conf['vCard.'] as $key => $value) {
					if (strpos($key, '.')) {
						continue;
					}
					$this->markerArray['###' . strtoupper($key) . '###'] = $this->cObj->cObjGetSingle($this->conf['vCard.'][$key], $this->conf['vCard.'][$key . '.']); // add current field to markerArray
				}
			}
			$this->markerArray = $this->div->utf8($this->markerArray, $this); // utf8 en- and decode function
			$this->markerArray['###IMAGE_BASE64###'] = $this->div->encodeBase64($this->conf['path.']['ttaddress_pictures'] . $row['image']); // new marker: IMAGE_BASE64 for image in vCards
			
			$this->hook(); // add hook
			$this->content = $this->cObj->substituteMarkerArrayCached($this->tmpl[$this->mode], $this->markerArray); // substitute Marker in Template
			$this->content = $this->dynamicMarkers->main($this->conf, $this->cObj, $this->content); // Fill dynamic locallang or typoscript markers
			$this->content = preg_replace('|###.*?###|i', '', $this->content); // Finally clear not filled markers
			$this->content = trim($this->content);
			return array('content' => $this->content, 'filename' => $this->cObj->cObjGetSingle($this->conf['vCard.']['filename'], $this->conf['vCard.']['filename.']));
		
		} else { // no address to uid found
			die ($this->extKey . ' vCard error'); // die script
		}
		
    }
	

	// Function hook() adds hook
	function hook() {
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey][$this->mode])) { // Adds hook for processing
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey][$this->mode] as $_classRef) {
				$_procObj = &t3lib_div::getUserObj($_classRef);
				$_procObj->detail($this->markerArray, $this->conf, $this->piVars, $this->cObj, $this);
			}
		}
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/pi1/class.tx_wtdirectory_pi1_vcard.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/pi1/class.tx_wtdirectory_pi1_vcard.php']);
}

?>