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

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('wt_directory') . 'lib/class.wtdirectory_div.php'); // load div class
require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('wt_directory') . 'lib/class.wtdirectory_dynamicmarkers.php'); // file for dynamicmarker functions

class tx_wtdirectory_filter_cat extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {

	public $extKey = 'wt_directory'; // Extension key
	public $prefixId = 'tx_wtdirectory_pi1'; // Same as class name
	public $scriptRelPath = 'pi1/class.tx_wtdirectory_pi1.php'; // Path to any pi1 script for locallang

	/**
	 * Generate the Catfilter Output
	 *
	 * @param    array        TypoScript configuration
	 * @param    array        Plugin variables (GET and POST)
	 * @return    string        generated content
	 */
	public function main($conf, $piVars) {
		// Config
		global $TSFE;
		$this->cObj = $TSFE->cObj; // cObject
		$this->conf = $conf;
		$this->piVars = $piVars;
		$this->pi_loadLL();
		$this->outerArray = $this->markerArray = $this->subpartArray = array();
		$content_item = ''; // init
		$this->div = GeneralUtility::makeInstance('wtdirectory_div'); // Create new instance for div class
		$this->notAllowedCategories = GeneralUtility::trimExplode(',', $this->conf['filter.']['cat.']['disable'], 1); // some categories which are not allowed (via constants)
		$this->dynamicMarkers = GeneralUtility::makeInstance('tx_wtdirectory_dynamicmarkers'); // New object: TYPO3 dynamicmarker function
		$this->tmpl['filter']['cat'] = $this->cObj->getSubpart($this->cObj->fileResource($this->conf['template.']['search']), '###WTDIRECTORY_FILTER_CAT###'); // Load HTML Template
		$this->tmpl['filter']['item'] = $this->cObj->getSubpart($this->tmpl['filter']['cat'], '###ITEM###'); // work on subpart 1
		$this->languid = $GLOBALS['TSFE']->tmpl->setup['config.']['sys_language_uid'] ? $GLOBALS['TSFE']->tmpl->setup['config.']['sys_language_uid'] : 0; // current language uid

		// Let's go
		if (!$this->pi_getFFvalue($this->conf, 'enablecatchoose', 'list')) { // if category choose is disabled in flexform
			return '';
		}
		//$this->getDefaultCatChooser(); // old cat chooser
		$this->getDrilldownCatChooser(); // new cat chooser

		if (!empty($this->content)) {
			return $this->content;
		}

	}

	/**
	 * Generate the drilldown Catfilter Output
	 *
	 * @return    void
	 */
	private function getDrilldownCatChooser() {
		$content_item = '';
		$this->outerArray['###WTDIRECTORY_CAT_TITLE###'] = $this->pi_getLL('wtdirectory_cat_title', 'Search form'); // title
		$this->outerArray['###WTDIRECTORY_CAT_ACTION###'] = $this->conf['filter.']['cat.']['clearOldFilter'] == 0 ? htmlentities($this->pi_linkTP_keepPIvars_url(array('hash' => 1), 1)) : htmlentities($this->cObj->typolink('x', array('returnLast' => 'url', 'additionalParams' => '&' . $this->prefixId . '[hash]=1', 'parameter' => $GLOBALS['TSFE']->id, 'useCacheHash' => 1))); // target for form
		$this->outerArray['###WTDIRECTORY_CAT_METHOD###'] = 'post'; // form method
		$this->outerArray['###CAT_BREADCRUMB###'] = $this->div->createCatBreadcrumb($this);
		$this->cat = $this->div->getTreeCategories($this);

		foreach ((array)$this->cat as $level1 => $value1) {
			$this->markerArray['###WTDIRECTORY_CAT_UID###'] = $level1;
			$this->markerArray['###LEVEL###'] = '1';
			$this->markerArray['###WTDIRECTORY_CAT_TITLE###'] = $value1['title'];
			$this->markerArray['###WTDIRECTORY_CAT_SELECTED###'] = ($this->piVars['catfilter'] == $level1 ? ' selected="selected"' : '');
			$content_item .= $this->cObj->substituteMarkerArrayCached($this->tmpl['filter']['item'], $this->markerArray);

			foreach ((array)$this->cat[$level1]['_children'] as $level2 => $value2) {
				$this->markerArray['###WTDIRECTORY_CAT_UID###'] = $level2;
				$this->markerArray['###LEVEL###'] = '2';
				$this->markerArray['###WTDIRECTORY_CAT_TITLE###'] = $value2['title'];
				$this->markerArray['###WTDIRECTORY_CAT_SELECTED###'] = ($this->piVars['catfilter'] == $level2 ? ' selected="selected"' : '');
				$content_item .= $this->cObj->substituteMarkerArrayCached($this->tmpl['filter']['item'], $this->markerArray);

				foreach ((array)$this->cat[$level1]['_children'][$level2]['_children'] as $level3 => $value3) {
					$this->markerArray['###WTDIRECTORY_CAT_UID###'] = $level3;
					$this->markerArray['###LEVEL###'] = '3';
					$this->markerArray['###WTDIRECTORY_CAT_TITLE###'] = $value3['title'];
					$this->markerArray['###WTDIRECTORY_CAT_SELECTED###'] = ($this->piVars['catfilter'] == $level3 ? ' selected="selected"' : '');
					$content_item .= $this->cObj->substituteMarkerArrayCached($this->tmpl['filter']['item'], $this->markerArray);

					foreach ((array)$this->cat[$level1]['_children'][$level2]['_children'][$level3]['_children'] as $level4 => $value4) {
						$this->markerArray['###WTDIRECTORY_CAT_UID###'] = $level4;
						$this->markerArray['###LEVEL###'] = '4';
						$this->markerArray['###WTDIRECTORY_CAT_TITLE###'] = $value4['title'];
						$this->markerArray['###WTDIRECTORY_CAT_SELECTED###'] = ($this->piVars['catfilter'] == $level4 ? ' selected="selected"' : '');
						$content_item .= $this->cObj->substituteMarkerArrayCached($this->tmpl['filter']['item'], $this->markerArray);
					}
				}
			}

		}
		$this->subpartArray['###CONTENT###'] = $content_item;

		$this->content = $this->cObj->substituteMarkerArrayCached($this->tmpl['filter']['cat'], $this->outerArray, $this->subpartArray);
		$this->content = $this->dynamicMarkers->main($this->conf, $this->cObj, $this->content);
		$this->content = preg_replace('|###.*?###|i', '', $this->content);
	}

	/**
	 * Generate the default Catfilter Output
	 *
	 * @return    void
	 */
	private function getDefaultCatChooser() {
		$this->cat = $this->div->getCategories($this); // get tt_address categories in an array

		$this->outerArray['###WTDIRECTORY_CAT_TITLE###'] = $this->pi_getLL('wtdirectory_cat_title', 'Search form'); // title
		$this->outerArray['###WTDIRECTORY_CAT_ACTION###'] = $this->conf['filter.']['cat.']['clearOldFilter'] == 0 ? htmlentities($this->pi_linkTP_keepPIvars_url(array('hash' => 1), 1)) : htmlentities($this->cObj->typolink('x', array('returnLast' => 'url', 'additionalParams' => '&' . $this->prefixId . '[hash]=1', 'parameter' => $GLOBALS['TSFE']->id, 'useCacheHash' => 1))); // target for form
		$this->outerArray['###WTDIRECTORY_CAT_METHOD###'] = 'post'; // form method

		for ($i = 0; $i < count($this->cat); $i++) { // one loop for every chosen category
			if (
					(isset($this->notAllowedCategories) && !in_array($this->cat[$i], $this->notAllowedCategories))
					|| !isset($this->notAllowedCategories)
			) {
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery( // DB query
						'title, pid, uid',
						'tt_address_group',
						$where_clause = 'tt_address_group.uid = ' . $this->cat[$i] . $this->cObj->enableFields('tt_address_group'),
						$groupBy = '',
						$orderBy = '',
						$limit = 1
				);
				if ($res) {
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				}

				// Fill inner marker
				$this->markerArray['###WTDIRECTORY_CAT_TITLE###'] = $row['title']; // Title marker
				$tmp_addressgroup = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tt_address_group', array('pid' => $row['pid'], 'uid' => $row['uid'], 'title' => $row['title']), $this->languid, ($this->sys_language_mode == 'strict' ? 'hideNonTranslated' : '')); // language overlay
				if ($tmp_addressgroup['title']) { // overwrite addressgroup title with localized version
					$this->markerArray['###WTDIRECTORY_CAT_TITLE###'] = $tmp_addressgroup['title'];
				}

				$this->markerArray['###WTDIRECTORY_CAT_UID###'] = $this->cat[$i]; // uid marker
				$this->markerArray['###WTDIRECTORY_CAT_SELECTED###'] = ($this->piVars['catfilter'] == $this->cat[$i] ? ' selected="selected"' : ''); // uid marker

				if ($row['uid']) { // add all markers of this loop to the variable
					$content_item .= $this->cObj->substituteMarkerArrayCached($this->tmpl['filter']['item'], $this->markerArray);
				}
			}
		}
		$this->subpartArray['###CONTENT###'] = $content_item; // work on subpart 2

		$this->content = $this->cObj->substituteMarkerArrayCached($this->tmpl['filter']['cat'], $this->outerArray, $this->subpartArray); // substitute Marker in Template
		$this->content = $this->dynamicMarkers->main($this->conf, $this->cObj, $this->content); // Fill dynamic locallang or typoscript markers
		$this->content = preg_replace('|###.*?###|i', '', $this->content); // Finally clear not filled markers

	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_filter_cat.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_filter_cat.php']);
}

?>
