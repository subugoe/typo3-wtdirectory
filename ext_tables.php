<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['wt_directory']); // Get backend config

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/defaultCSS/', 'Add default CSS');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/autocomplete/', 'AJAX Autocompleter');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/drilldown/', 'Category Drilldown');

// Add contact field to tt_news
$tmpColumns = array(
		'tx_wtdirectory_author' => array(
				'exclude' => 1,
				'label' => 'LLL:EXT:wt_directory/locallang_db.xml:tt_news.tx_wtdirectory_author',
				'config' => array(
						'type' => 'select',
						'items' => Array(
								array(
										'',
										0
								),
						),
						'foreign_table' => 'tt_address',
						'foreign_table_where' => 'ORDER BY tt_address.last_name',
						'size' => 1,
						'minitems' => 0,
						'maxitems' => 1,
				)
		),
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_news', $tmpColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_news', 'tx_wtdirectory_author;;;;1-1-1');


$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY . '_pi1'] = 'layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY . '_pi1'] = 'pi_flexform';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
		array(
				'LLL:EXT:wt_directory/locallang_db.xml:tt_content.list_type_pi1',
				$_EXTKEY . '_pi1',
				\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'ext_icon.gif'
		),
		'list_type'
);

// choose another flexform xml if countryfilter ist not checked
if ($confArr['countryFilter']) {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($_EXTKEY . '_pi1', 'FILE:EXT:wt_directory/be/flexform_ds_pi1_countryfilter.xml');
} else {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($_EXTKEY . '_pi1', 'FILE:EXT:wt_directory/be/flexform_ds_pi1.xml');
}

if (TYPO3_MODE == 'BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_wtdirectory_pi1_wizicon'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'pi1/class.tx_wtdirectory_pi1_wizicon.php';
	include_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('wt_directory') . 'be/class.user_be_fields.php'); // show all fields in database table tt_address
	include_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('wt_directory') . 'be/class.user_be_address.php'); // show tt_address values
	include_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('wt_directory') . 'be/class.user_be_abcfields.php'); // show all fields in database table tt_address
	include_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('wt_directory') . 'be/class.user_be_googlemapmsg.php'); // check if googlemap is installed
	include_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('wt_directory') . 'be/class.user_be_powermailmsg.php'); // check if powermail is installed
	include_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('tt_address') . 'class.tx_ttaddress_treeview.php'); // check tt_address categories from tt_address
}
?>