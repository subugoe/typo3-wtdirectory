<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_wtdirectory_pi1.php', '_pi1', 'list_type', 0); // add USER_INT func
//t3lib_extMgm::addPItoST43($_EXTKEY, 'pi2/class.tx_wtdirectory_pi2.php', '_pi2', 'list_type', 1); // add USER func

##### Hook Section #####

// EID for autocomplete
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['wtdirectory_autocomplete'] = 'EXT:wt_directory/lib/class.wtdirectory_eid_autocomplete.php';

// Hook PM_FormWrapMarkerHook: If piVars pm_receiver > 0 than write email of receiver to session
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['powermail']['PM_FormWrapMarkerHook'][] = 'EXT:wt_directory/lib/class.wtdirectory_hook_powermailreceiver.php:tx_wtdirectory_powermailreceiver';

// Hook PM_SubmitEmailHook: Change E-Mail Receiver if there is any email in the session
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['powermail']['PM_SubmitEmailHook'][] = 'EXT:wt_directory/lib/class.wtdirectory_hook_powermailreceiver.php:tx_wtdirectory_powermailreceiver';

// Hook for wt_directory_pi1
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['wt_directory']['beforemain'][]  = 'EXT:wt_directory/pi2/class.tx_wtdirectory_pi2.php:tx_wtdirectory_pi2';
?>