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

// list all tt_address entries (within a special pid - if startingpoint was set)
class user_be_address {
	
	function main(&$params, $pObj)	{
		// config
		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['wt_directory']); // Get config from localconf.php
		$uid = preg_replace('/[^0-9]/', '', substr($pObj->returnUrl, strpos($pObj->returnUrl, '[tt_content]'))); // strange thing to get to the tt_content uid of wt_directory plugin
		$startingpoint_array = explode('|', $pObj->cachedTSconfig['tt_content:' . $uid]['_THIS_ROW']['pages']); // step1: another strange thing to get to the startingpoint
		$startingpoint = preg_replace('/[^0-9]/', '', $startingpoint_array[0]); // step2: another strange thing to get to the startingpoint
		$whereadd = (is_numeric($startingpoint) && $startingpoint > 0 ? ' AND tt_address.pid IN (' . $startingpoint . ')' : ''); // addition for where clause
		if ($confArr['companyNames'] != 1) { // show names
			$orderBy = 'tt_address.last_name ASC';
		} else {
			$orderBy = 'tt_address.company ASC';
		}
		$i=0;
		
		// DB query
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
		    'tt_address.uid, tt_address.name, tt_address.last_name, tt_address.first_name, tt_address.company',
		    'tt_address',
		    $where_clause = '1' . $whereadd . ' AND tt_address.deleted = 0',
		    $groupBy = '',
		    $orderBy .= ', tt_address.uid ASC',
		    $limit = ''
		);
		if ($res) { // If there is a result
		    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) { // one loop for every db entry
				if ($confArr['companyNames'] != 1) { // show names
					if (!empty($row['first_name']) && !empty($row['last_name'])) { // if lastname and firstname are existing
						$params['items'][$i]['0'] = $row['last_name'] . ', ' . $row['first_name']; // take lastname, firstname
					} elseif (!empty($row['name'])) { // take name
						$params['items'][$i]['0'] = $row['name']; // take name
					}
				} else { // show company
					$params['items'][$i]['0'] = $row['company']; // take company
				}
		        $params['items'][$i]['0'] .= ' (' . $row['uid'] . ')'; // in every case - add uid
		        $params['items'][$i]['1'] = $row['uid']; // Option value
		        $i++; // increase counter
		    }
		}
   
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/be/class.user_be_address.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/be/class.user_be_address.php']);
}

?>