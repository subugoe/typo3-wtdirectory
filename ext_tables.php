<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['wt_directory']); // Get backend config

t3lib_extMgm::addStaticFile($_EXTKEY, 'static/defaultCSS/', 'Add default CSS');
t3lib_extMgm::addStaticFile($_EXTKEY, 'static/autocomplete/', 'AJAX Autocompleter');
t3lib_extMgm::addStaticFile($_EXTKEY, 'static/drilldown/', 'Category Drilldown');

// Add contact field to tt_news
$tmpColumns = array (
	'tx_wtdirectory_author' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:wt_directory/locallang_db.xml:tt_news.tx_wtdirectory_author',
		'config' => array (
			'type' => 'select',
			'items' => Array (
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
t3lib_div::loadTCA('tt_news');
t3lib_extMgm::addTCAcolumns('tt_news', $tmpColumns, 1);
t3lib_extMgm::addToAllTCAtypes('tt_news', 'tx_wtdirectory_author;;;;1-1-1');


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY . '_pi1'] = 'layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY . '_pi1'] = 'pi_flexform';
//$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY . '_pi2'] = 'layout,select_key';
//$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY . '_pi2'] = 'pi_flexform';

t3lib_extMgm::addPlugin (
	array(
		'LLL:EXT:wt_directory/locallang_db.xml:tt_content.list_type_pi1',
		$_EXTKEY . '_pi1',
		t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
	),
	'list_type'
);

// choose another flexform xml if countryfilter ist not checked
if ($confArr['countryFilter']) {
	t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_pi1', 'FILE:EXT:wt_directory/be/flexform_ds_pi1_countryfilter.xml');
} else {
	t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_pi1', 'FILE:EXT:wt_directory/be/flexform_ds_pi1.xml');
}

/*
t3lib_extMgm::addPlugin (
	array(
		'LLL:EXT:wt_directory/locallang_db.xml:tt_content.list_type_pi2',
		$_EXTKEY . '_pi2',
		t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
	),
	'list_type'
);

t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_pi2', 'FILE:EXT:wt_directory/be/flexform_ds_pi2.xml');
*/


if (TYPO3_MODE == 'BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_wtdirectory_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY) . 'pi1/class.tx_wtdirectory_pi1_wizicon.php';
	include_once(t3lib_extMgm::extPath('wt_directory') . 'be/class.user_be_fields.php'); // show all fields in database table tt_address
	include_once(t3lib_extMgm::extPath('wt_directory') . 'be/class.user_be_address.php'); // show tt_address values
	include_once(t3lib_extMgm::extPath('wt_directory') . 'be/class.user_be_abcfields.php'); // show all fields in database table tt_address
	include_once(t3lib_extMgm::extPath('wt_directory') . 'be/class.user_be_googlemapmsg.php'); // check if googlemap is installed
	include_once(t3lib_extMgm::extPath('wt_directory') . 'be/class.user_be_powermailmsg.php'); // check if powermail is installed
	include_once(t3lib_extMgm::extPath('tt_address') . 'class.tx_ttaddress_treeview.php'); // check tt_address categories from tt_address
}
?>