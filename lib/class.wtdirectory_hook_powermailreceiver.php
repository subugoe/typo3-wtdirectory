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

class tx_wtdirectory_powermailreceiver extends tslib_pibase {

	var $extKey = 'wt_directory'; // Extension key
	var $prefixId = 'tx_wtdirectory_pi1'; // Same as class name
	var $scriptRelPath = 'pi1/class.tx_wtdirectory_pi1.php';	// Path to any script in pi1 for locallang
	
	
	// Function PM_FormWrapMarkerHook() to set email receiver in session for powermail (if form is rendered in powermail)
    function PM_FormWrapMarkerHook($OuterMarkerArray, $subpartArray, $conf, $obj) {
		
		if ($this->piVars['pm_receiver'] > 0) { // if pm_receiver is set in piVars
			// Get email from tt_address
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( // DB query
				'email',
				'tt_address',
				$where_clause = 'tt_address.uid = '.intval($this->piVars['pm_receiver']).tslib_cObj::enableFields('tt_address'),
				$groupBy = '',
				$orderBy = '',
				$limit = 1
			);
			if ($res) $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res); // Result in array
			
			if (t3lib_div::validEmail($row['email'])) { // if this is a valid email address
				
				// Set Session
				$GLOBALS['TSFE']->fe_user->setKey('ses', $this->extKey.'_'.$GLOBALS['TSFE']->id, array('powermailreceiver' => $row['email']));
				$GLOBALS['TSFE']->storeSessionData(); // Save session
			}
			
		} else { // pm_receiver is not set in piVars
			
			$rec_array = $GLOBALS['TSFE']->fe_user->getKey('ses', $this->extKey.'_'.$GLOBALS['TSFE']->id); // get session values
			$pm_array1 = $GLOBALS['TSFE']->fe_user->getKey('ses', 'powermail_'.($obj->cObj->data['_LOCALIZED_UID'] > 0 ? $obj->cObj->data['_LOCALIZED_UID'] : $obj->cObj->data['uid']));
			$pm_array2 = $GLOBALS['TSFE']->fe_user->getKey('ses', 'powermail_'.($obj->cObj->data['_LOCALIZED_UID'] > 0 ? $obj->cObj->data['_LOCALIZED_UID'] : $obj->cObj->data['pid']));
			
			if ($rec_array['powermailreceiver'] && empty($pm_array1) && empty($pm_array2)) { // there is an old value in the session and no value in the powermail session (should not be cleared if user goes back from confirm to form)
				// Clear Session
				$GLOBALS['TSFE']->fe_user->setKey('ses', $this->extKey.'_'.$GLOBALS['TSFE']->id, array()); // empty array
				$GLOBALS['TSFE']->storeSessionData(); // Save session to overwrite session
			}
				
		}
    }
	
	
	// Function PM_SubmitEmailHook() to change powermail email receiver if value in session (if submit.php is rendered in powermail)
    function PM_SubmitEmailHook($subpart, &$maildata, $sessiondata, $markerArray, &$obj) {
		
		if ($subpart == 'recipient_mail') { // work only if mail to receiver
			$rec_array = $GLOBALS['TSFE']->fe_user->getKey('ses', $this->extKey.'_'.$GLOBALS['TSFE']->id); // get email from wt_directory session
			
			if (t3lib_div::validEmail($rec_array['powermailreceiver'])) { // if this is a valid email address
				$maildata['receiver'] = $rec_array['powermailreceiver']; // change email receiver
				$obj->MainReceiver = $rec_array['powermailreceiver']; // change email receiver (save db values)
			}
		}
    }
	
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_hook_powermailreceiver.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_hook_powermailreceiver.php']);
}

?>