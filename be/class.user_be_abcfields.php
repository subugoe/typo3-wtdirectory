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


class user_be_abcfields {
	var $notAllowedFields = array('uid','pid','tstamp','hidden','deleted'); // fields which are not allowed to show
	
	function main(&$params,&$pObj)	{
	
		$res = mysql_query('SHOW COLUMNS FROM tt_address'); // mysql query
		if ($res) { // If there is a result
			$i=1;
			// First option is empty
			$params['items'][0]['0'] = $pObj->sL('LLL:EXT:wt_directory/locallang_db.xml:pi_flexform.empty'); // Option name
			$params['items'][0]['1'] = ''; // Option value
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) { // One loop for every result
				if($row['Field'] && !in_array($row['Field'],$this->notAllowedFields)) {
					// Generate name
					$curName = ($pObj->sL('LLL:EXT:tt_address/locallang_tca.xml:tt_address.'.$row['Field']) ? $pObj->sL('LLL:EXT:tt_address/locallang_tca.xml:tt_address.'.$row['Field']) : $pObj->sL('LLL:EXT:lang/locallang_general.xml:LGL.'.$row['Field'])); // Get name from tt_address locallang
					if(!$curName) $curName = ucfirst($row['Field']); // take Fieldname, if no name
					$curName = str_replace(':','',$curName); // remove ':'
					
					// Manipulate options
					$params['items'][$i]['0'] = $curName; // Option name
					$params['items'][$i]['1'] = $row['Field']; // Option value
				}
				$i++;
			}
			if($emails) $emails = substr(trim($emails), 0, -1); // delete last ,
		}
   
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/be/class.user_be_abcfields.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/be/class.user_be_abcfields.php']);
}

?>