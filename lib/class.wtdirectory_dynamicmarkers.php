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

class tx_wtdirectory_dynamicmarkers extends tslib_pibase {

    var $extKey = 'wt_directory';
    var $scriptRelPath = 'pi1/class.tx_wtdirectory_pi1.php';    // Path to pi1 to get locallang.xml from pi1 folder
    var $locallangmarker_prefix = array('WTDIRECTORY_LL_','wtdirectory_ll_'); // prefix for automatic locallangmarker
    var $typoscriptmarker_prefix = array('WTDIRECTORY_TS_','dynamicTyposcript'); // prefix for automatic typoscriptmarker
    
	
	// Function main() to replace typoscript- and locallang markers
	function main($conf, $cObj, $content, $enable = 1) {
		// config
		$this->conf = $conf;
		$this->cObj = $cObj;
		$this->content = $content;
		$this->pi_loadLL();
		
		if ($enable == 1) { // could be disabled for testing
		
			// let's go
			// 1. replace locallang markers
			$this->content = preg_replace_callback ( // Automaticly fill locallangmarkers with fitting value of locallang.xml
				'#\#\#\#'.$this->locallangmarker_prefix[0].'(.*)\#\#\##Uis', // regulare expression
				array($this, 'DynamicLocalLangMarker'), // open function
				$this->content // current content
			);
			
			// 2. replace typoscript markers
			$this->content = preg_replace_callback ( // Automaticly fill locallangmarkers with fitting value of locallang.xml
				'#\#\#\#'.$this->typoscriptmarker_prefix[0].'(.*)\#\#\##Uis', // regulare expression
				array($this, 'DynamicTyposcriptMarker'), // open function
				$this->content // current content
			);
			
		}
		
		if (!empty($this->content)) return $this->content;
	}
    
    
    // Function DynamicLocalLangMarker() to get automaticly a marker from locallang.xml (###LOCALLANG_BLABLA### from locallang.xml: locallangmarker_blabla)
    function DynamicLocalLangMarker($array) {
		if (!empty($array[1])) $string = $this->pi_getLL(strtolower($this->locallangmarker_prefix[1].$array[1]), '<i>'.strtolower($array[1]).'</i>'); // search for a fitting entry in locallang.xml or typoscript
        
		if (!empty($string)) return $string;
    }
    
    
    // Function DynamicTyposcriptMarker() to get automaticly a marker from typoscript 
    function DynamicTyposcriptMarker($array) {
		if ($this->conf['dynamicTyposcript.'][strtolower($array[1])]) { // If there is a fitting entry in typoscript
			$string = $this->cObj->cObjGetSingle($this->conf[$this->typoscriptmarker_prefix[1].'.'][strtolower($array[1])], $this->conf[$this->typoscriptmarker_prefix[1].'.'][strtolower($array[1]).'.']); // fill string with typoscript value
		}
        
		if (!empty($string)) return $string;
    }
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_dynamicmarkers.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_dynamicmarkers.php']);
}

?>