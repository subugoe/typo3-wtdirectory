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

use \TYPO3\CMS\Core\Utility\GeneralUtility;

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('wt_directory') . 'lib/class.wtdirectory_dynamicmarkers.php'); // file for dynamicmarker functions


class tx_wtdirectory_filter_abc extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {

	var $extKey = 'wt_directory'; // Extension key
	var $prefixId = 'tx_wtdirectory_pi1'; // Same as class name
	var $scriptRelPath = 'pi1/class.tx_wtdirectory_pi1.php'; // Path to any script in pi1 for locallang

	function main($conf, $piVars, $query_pid, $query_cat) {

		// Config
		global $TSFE;
		$this->cObj = $TSFE->cObj; // cObject
		$this->conf = $conf;
		$this->piVars = $piVars;
		$this->query_cat = $query_cat;
		$this->query_pid = $query_pid;
		$this->pi_loadLL();
		$this->dynamicMarkers = GeneralUtility::makeInstance('tx_wtdirectory_dynamicmarkers'); // New object: TYPO3 dynamicmarker function
		$this->tmpl = $this->markerArray = array();
		$this->filter = ''; // init
		$this->tmpl['filter']['abc'] = $this->cObj->getSubpart($this->cObj->fileResource($this->conf['template.']['search']), '###WTDIRECTORY_FILTER_ABC###'); // Load HTML Template

		// let's go
		if ($this->pi_getFFvalue($this->conf, 'abc', 'list') != '') { // if abc should be shown
			if (!empty($this->piVars['catfilter'])) { // if catfilter is set
				if (is_array($this->piVars['catfilter'])) {
					foreach ($this->piVars['catfilter'] as $catID) {
						$this->filter .= ' AND tt_address.uid IN (SELECT tt_address.uid FROM tt_address INNER JOIN tt_address_group_mm ON tt_address.uid = tt_address_group_mm.uid_local WHERE tt_address_group_mm.uid_foreign= ' . $catID . ')';
					}
				} else {
					// no array, so query with an and statement
					$this->filter .= ' AND tt_address_group.uid = ' . $this->piVars['catfilter']; // if catfilter set, add where clause
				}
			}

			$this->markerArray['###WTDIRECTORY_ABC_ALL###'] = $this->show_all(); // Link all
			$this->markerArray['###WTDIRECTORY_ABC_ABC###'] = $this->show_abc(); // Link abc
			$this->markerArray['###WTDIRECTORY_ABC_0-9###'] = $this->show_numbers(); // Link numbers
			$this->content = $this->cObj->substituteMarkerArrayCached($this->tmpl['filter']['abc'], $this->markerArray); // substitute Marker in Template
			$this->content = $this->dynamicMarkers->main($this->conf, $this->cObj, $this->content); // Fill dynamic locallang or typoscript markers
			$this->content = preg_replace("|###.*?###|i", "", $this->content); // Finally clear not filled markers
		}

		if (!empty($this->content)) return $this->content;

	}


	// Function show_abc() to generate ABC list
	function show_abc() {
		$content = ''; // init

		for ($a = A; $a != AA; $a++) { // ABC loop

			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery( // DB query
					'tt_address.uid uid',
					'tt_address LEFT JOIN tt_address_group_mm on (tt_address.uid = tt_address_group_mm.uid_local) LEFT JOIN tt_address_group on (tt_address_group_mm.uid_foreign = tt_address_group.uid)',
					$where_clause = 'tt_address.' . $this->pi_getFFvalue($this->conf, 'abc', 'list') . ' LIKE "' . $a . '%"' . $this->query_pid . $this->query_cat . $this->filter . $this->cObj->enableFields('tt_address'),
					$groupBy = 'tt_address.uid',
					$orderBy = '',
					$limit = '1'
			);
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

			// Generate Return string
			$content .= '<span class="wtdirectory_abc_letter' . ($this->piVars['filter'][$this->pi_getFFvalue($this->conf, 'abc', 'list')] == strtolower($a) . '%' ? ' wtdirectory_abc_letter_act' : '') . '">';
			if ($row['uid']) { // If result (link with letter)
				$content .= $this->pi_linkTP_keepPIvars($a, array('filter' => array($this->pi_getFFvalue($this->conf, 'abc', 'list') => htmlentities(strtolower($a) . '%'))), 1); // Generate link for each sign
			} else { // no result: letter only link
				$content .= $a;
			}
			$content .= '</span>' . "\n";

		}
		if (!empty($content)) return $content;
	}


	// Function show_all() generates link same page without piVars
	function show_all() {
		$content = '<span class="wtdirectory_abc_letter_all' . ($this->piVars['list']['all'] || count($this->piVars) == 0 ? ' wtdirectory_abc_letter_all_act' : '') . '">';
		$content .= $this->cObj->typolink($this->pi_getLL('wtdirectory_ll_abclist_all', 'All'), array('parameter' => $GLOBALS['TSFE']->id, "useCacheHash" => 1, 'additionalParams' => $this->pi_getFFvalue($this->conf, 'shownone', 'mainconfig') ? '&' . $this->prefixId . '[list]=all' : ''));
		$content .= '</span>';
		$content .= "\n";

		if (!empty($content)) return $content;
	}


	// Function show_numbers() to generate numbers link
	function show_numbers() {
		$content = '';
		$query_numbers = ''; // init

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery( // DB query
				'tt_address.uid uid',
				'tt_address LEFT JOIN tt_address_group_mm on (tt_address.uid = tt_address_group_mm.uid_local) LEFT JOIN tt_address_group on (tt_address_group_mm.uid_foreign = tt_address_group.uid)',
				#$where_clause = 'tt_address.' . $this->pi_getFFvalue($this->conf, 'abc', 'list') . ' < "@%"' . $this->query_pid . $this->query_cat . $this->filter . $this->cObj->enableFields('tt_address'),
				$where_clause = 'tt_address.' . $this->pi_getFFvalue($this->conf, 'abc', 'list') . ' RLIKE "^[0-9]."' . $this->query_pid . $this->query_cat . $this->filter . $this->cObj->enableFields('tt_address'),
				$groupBy = 'tt_address.uid',
				$orderBy = '',
				$limit = '1'
		);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

		// Generate Return string
		if ($row['uid'] > 0) { // If result
			$content .= $this->pi_linkTP_keepPIvars($this->pi_getLL('wtdirectory_ll_abclist_numbers', '0-9'), array('filter' => array($this->pi_getFFvalue($this->conf, 'abc', 'list') => "@")), 1); // Generate link for 0-9
		} else { // if no result (no link)
			$content = $this->pi_getLL('wtdirectory_ll_abclist_numbers', '0-9') . "\n";
		}

		if (!empty($content)) return $content;
	}


}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_filter_abc.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_filter_abc.php']);
}

?>