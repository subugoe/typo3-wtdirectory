<?php

########################################################################
# Extension Manager/Repository config file for ext "wt_directory".
#
# Auto generated 18-12-2012 12:51
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'wt_directory',
	'description' => 'tt_address list with detail view, filter, vcard export, googlemap- and powermail link. Contact (tt_address) in tt_news detailview. All configurable via typoscript (follower of sp_directory)',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '2.0.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'tt_news',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Alex Kellner',
	'author_email' => 'alexander.kellner@in2code.de',
	'author_company' => 'in2code.de',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'wt_doorman' => '1.3.0-',
			'tt_address' => '2.0.0-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:62:{s:21:"ext_conf_template.txt";s:4:"07a6";s:12:"ext_icon.gif";s:4:"95a3";s:17:"ext_localconf.php";s:4:"3e13";s:14:"ext_tables.php";s:4:"2002";s:14:"ext_tables.sql";s:4:"2dff";s:28:"ext_typoscript_constants.txt";s:4:"c7d3";s:24:"ext_typoscript_setup.txt";s:4:"c255";s:13:"locallang.xml";s:4:"a6c4";s:16:"locallang_db.xml";s:4:"eb86";s:30:"be/class.user_be_abcfields.php";s:4:"3803";s:28:"be/class.user_be_address.php";s:4:"2129";s:27:"be/class.user_be_fields.php";s:4:"f03a";s:33:"be/class.user_be_googlemapmsg.php";s:4:"3968";s:33:"be/class.user_be_powermailmsg.php";s:4:"3775";s:22:"be/flexform_ds_pi1.xml";s:4:"9e99";s:36:"be/flexform_ds_pi1_countryfilter.xml";s:4:"abd1";s:22:"be/flexform_ds_pi2.xml";s:4:"4029";s:15:"css/default.css";s:4:"7de3";s:14:"doc/manual.sxw";s:4:"e67a";s:19:"files/icon_cell.gif";s:4:"914a";s:20:"files/icon_error.gif";s:4:"88b3";s:18:"files/icon_fax.gif";s:4:"a32a";s:21:"files/icon_female.gif";s:4:"364c";s:19:"files/icon_mail.gif";s:4:"160b";s:19:"files/icon_male.gif";s:4:"4efb";s:17:"files/icon_ok.gif";s:4:"9ac1";s:20:"files/icon_phone.gif";s:4:"fa94";s:18:"files/icon_web.gif";s:4:"b2d5";s:30:"js/wtdirectory_autocomplete.js";s:4:"71c8";s:27:"js/wtdirectory_drilldown.js";s:4:"98fb";s:42:"lib/class.user_wtdirectory_pagebrowser.php";s:4:"675f";s:29:"lib/class.wtdirectory_div.php";s:4:"bec3";s:40:"lib/class.wtdirectory_dynamicmarkers.php";s:4:"ddb1";s:42:"lib/class.wtdirectory_eid_autocomplete.php";s:4:"a381";s:36:"lib/class.wtdirectory_filter_abc.php";s:4:"eb77";s:36:"lib/class.wtdirectory_filter_cat.php";s:4:"b34d";s:45:"lib/class.wtdirectory_filter_radialsearch.php";s:4:"b3b1";s:39:"lib/class.wtdirectory_filter_search.php";s:4:"d3cb";s:48:"lib/class.wtdirectory_hook_powermailreceiver.php";s:4:"c008";s:33:"lib/class.wtdirectory_markers.php";s:4:"1054";s:37:"lib/class.wtdirectory_pagebrowser.php";s:4:"eb19";s:14:"pi1/ce_wiz.gif";s:4:"1813";s:32:"pi1/class.tx_wtdirectory_pi1.php";s:4:"30c7";s:39:"pi1/class.tx_wtdirectory_pi1_detail.php";s:4:"7880";s:37:"pi1/class.tx_wtdirectory_pi1_list.php";s:4:"3101";s:38:"pi1/class.tx_wtdirectory_pi1_vcard.php";s:4:"0973";s:40:"pi1/class.tx_wtdirectory_pi1_wizicon.php";s:4:"b257";s:13:"pi1/clear.gif";s:4:"cc11";s:17:"pi1/locallang.xml";s:4:"dcfe";s:32:"pi2/class.tx_wtdirectory_pi2.php";s:4:"7f8a";s:17:"pi2/locallang.xml";s:4:"3ca0";s:29:"static/autocomplete/setup.txt";s:4:"ad76";s:27:"static/defaultCSS/setup.txt";s:4:"f9f3";s:26:"static/drilldown/setup.txt";s:4:"fe04";s:26:"templates/tmpl_detail.html";s:4:"2cc5";s:27:"templates/tmpl_filters.html";s:4:"0568";s:24:"templates/tmpl_list.html";s:4:"0ce2";s:29:"templates/tmpl_markerall.html";s:4:"1f29";s:31:"templates/tmpl_pagebrowser.html";s:4:"d614";s:33:"templates/tmpl_pi2_drilldown.html";s:4:"b05f";s:26:"templates/tmpl_search.html";s:4:"0b05";s:25:"templates/tmpl_vcard.html";s:4:"640c";}',
);

?>