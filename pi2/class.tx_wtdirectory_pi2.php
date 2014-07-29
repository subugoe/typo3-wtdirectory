<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Stefan Busemannn <stefan.busemann@in2code.de>, in2code.de
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
use \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('wt_directory') . 'lib/class.wtdirectory_div.php'); // load div class
require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('wt_directory') . 'lib/class.wtdirectory_dynamicmarkers.php'); // file for dynamicmarker functions
if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('wt_doorman', 0)) require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('wt_doorman') . 'class.tx_wtdoorman_security.php'); // load security class


/**
 * Plugin 'wt_directory (filterviews)' for the 'wt_directory' extension.
 *
 * @author    Stefan Busemann <stefan.busemann@in2code.de>
 * @package    TYPO3
 * @subpackage    tx_wtdirectory
 */
class tx_wtdirectory_pi2 extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {

	var $extKey = 'wt_directory'; // The extension key.
	var $prefixId = 'tx_wtdirectory_pi2'; // Same as class name
	var $scriptRelPath = 'pi2/class.tx_wtdirectory_pi2.php'; // Path to this script relative to the extension dir.
	var $internal = array();
	var $content = '';
	var $filters = array(); // array that holds filters that are send to the list view of wt_directory


	function main($content, $conf) {
		// Config
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_initPIflexForm();
		$this->pi_USER_INT_obj = 1; // USER

		$this->dynamicMarkers = GeneralUtility::makeInstance('tx_wtdirectory_dynamicmarkers'); // New object: TYPO3 dynamicmarker function

		$this->setPageFilters();
		// loading post values from the drilldown view
		if ($this->piVars['level0']) {
			do {
				if (intval($this->piVars['level' . intval($i)]) == 0) {
					break;
				}
				$this->internal['drilldown']['level' . intval($i)] = intval($this->piVars['level' . intval($i)]);
				$i++;
			} while ($this->piVars['level' . intval($i)]);
		}

		// Read FlexForm configuration
		if ($this->cObj->data['pi_flexform']['data']) {
			foreach ($this->cObj->data['pi_flexform']['data'] as $sheetName => $sheet) {
				foreach ($sheet as $langName => $lang) {
					foreach (array_keys($lang) as $key) {
						$flexFormConf[$key] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], $key, $sheetName, $langName);
						if (!$flexFormConf[$key]) {
							unset($flexFormConf[$key]);
						}
					}
				}
			}
		}

		if (is_array($flexFormConf)) {
			$conf = GeneralUtility::array_merge($conf, $flexFormConf);
		}
		foreach ($conf as $key => $data) {
			if (substr($key, -1) == '.') {
				$this->conf[substr($key, 0, -1)] = $this->cObj->stdWrap($conf[substr($key, 0, -1)], $conf[$key]);
			} elseif (!isset($conf[$key . '.'])) {
				$this->conf[$key] = $conf[$key];
			}
		}
		// Instances and security function
		$this->div = GeneralUtility::makeInstance('wtdirectory_div'); // Create new instance for div class
		#$this->secure(); // Security options for piVars
		$this->check(); // Check if all is alright

		#$this->pi_getFFvalue($flexform, 'catMounts', 'sSelection')
		$flexform = $this->cObj->data['pi_flexform'];
		switch ($this->conf['showView']) {
			case 1:
				$this->content = $this->drillDown();
				break;
			case 2:
				$this->filters['catfilter'][] = $this->piVars['catfilter'];
				break;
			default:
				$this->content = 'no view selected';
		}
		// Main part
		return $this->pi_wrapInBaseClass($this->content); // return content
	}


	/**
	 * shows the drill down search view
	 *
	 * @return    [void]        no return valut
	 */
	function drillDown() {

		// get mount point
		if (!$this->conf['drillDown.']['root']) {
			return 'Sorry, no addresscategory root set. Please set up: plugin.tx_wtdirectory_pi2.drilldown.root';
		}

		// check if there are selected categories for the drilldown view
		$data = $GLOBALS['TSFE']->fe_user->getKey('ses', 'tx_wtdirectory_pi2');
		if ($this->piVars['country']) {
			$selected['country'] = (int)$this->piVars['country'];
		} else {
			$selected['country'] = $data[$GLOBALS['TSFE']->id]['country'];
		}

		if ($this->piVars['continent']) {
			$selected['continent'] = (int)$this->piVars['continent'];

			if ($this->piVars['continent'] <> $data[$GLOBALS['TSFE']->id]['continent']) {
				$selected['country'] = '';
			}
		} else {
			$selected['continent'] = $data[$GLOBALS['TSFE']->id]['continent'];
		}

		// store the selection in the session
		$data[$GLOBALS['TSFE']->id] = $selected;
		$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_wtdirectory_pi2', $data);

		if ($this->conf['debug.']['drillDown.']['piVars'] == 1) GeneralUtility::debug($this->piVars, 'piVars');
		if ($this->conf['debug.']['drillDown.']['conf'] == 1) GeneralUtility::debug($this->conf, 'conf');
		if ($this->conf['debug.']['drillDown.']['getKey'] == 1) GeneralUtility::debug($GLOBALS['TSFE']->fe_user->getKey('ses', 'tx_wtdirectory_pi2'));


		$rootCat = $this->conf['drillDown.']['root'];
		$rootCats = $this->getChildCategories($rootCat);

		$catArray = array();
		$catArray = $this->drillDown_getCategories($rootCats, $selected);

		if ($this->conf['drillDown.']['continent.']['selectorBox.']['displayAnEmptyOption.']['localLangLabel']) {
			$noSelectionLabel = $this->pi_getLL($this->conf['drillDown.']['continent.']['selectorBox.']['displayAnEmptyOption.']['localLangLabel']);
		} else {
			$noSelectionLabel = $this->conf['drillDown.']['continent.']['selectorBox.']['displayAnEmptyOption.']['label'];
		}

		if ($this->conf['drillDown.']['continent.']['selectorBox.']['css.']['class']) $CSSClass = ' class="' . $this->conf['drillDown.']['continent.']['selectorBox.']['css.']['class'] . '" ';

		if ($this->conf['drillDown.']['continent.']['selectorBox.']['css.']['id']) $CSSID = ' id="' . $this->conf['drillDown.']['continent.']['selectorBox.']['css.']['id'] . $key . '"';
		if ($this->conf['drillDown.']['continent.']['selectorBox.']['displayAnEmptyOption'] == 1) $displayAnEmptyOption = true;
		$box = $this->renderSelector($catArray, $selected, $this->prefixId . '[continent]', 0, $displayAnEmptyOption, false, ' onchange="doSubmit()" ' . $CSSClass . ' ' . $CSSID, $this->conf['drillDown.']['continent.']['selectorBox.']['option.'], $noSelectionLabel);
		$markerArray['###SELECTOR_CONTINENTS###'] = $this->cObj->stdWrap($box, $this->conf['drillDown.']['continent.']['selectorBox.']);

		$markerArray['###HEADER###'] = $this->cObj->stdWrap($this->pi_getLL('header'), $this->conf['drillDown.']['header.']);
		$markerArray['###LINKFORMTARGET###'] = $this->cObj->TYPOLINK('', $this->conf['drillDown.']['formtarget.']);
		#wtdirectoryFilter

		if ($selected['continent']) {
			if ($this->conf['drillDown.']['country.']['selectorBox.']['displayAnEmptyOption.']['localLangLabel']) {
				$noSelectionLabel = $this->pi_getLL($this->conf['drillDown.']['country.']['selectorBox.']['displayAnEmptyOption.']['localLangLabel']);
			} else {
				$noSelectionLabel = $this->conf['drillDown.']['country.']['selectorBox.']['displayAnEmptyOption.']['label'];
			}

			if ($this->conf['drillDown.']['country.']['selectorBox.']['css.']['class']) $CSSClass = ' class="' . $this->conf['drillDown.']['country.']['selectorBox.']['css.']['class'] . '" ';

			if ($this->conf['drillDown.']['country.']['selectorBox.']['css.']['id']) $CSSID = ' id="' . $this->conf['drillDown.']['country.']['selectorBox.']['css.']['id'] . $key . '"';
			if ($this->conf['drillDown.']['country.']['selectorBox.']['displayAnEmptyOption'] == 1) $displayAnEmptyOption = true;
			// List of countries which do have
			$additionalCatIDs = array();
			$rootCats = $this->getChildCategories($selected['continent'], $this->filters['catfilter']);
			$catArray = array();
			$catArray = $this->drillDown_getCategories($rootCats, $selected);
			$box = $this->renderSelector($catArray, $selected, $this->prefixId . '[country]', 0, $displayAnEmptyOption, false, ' onchange="doSubmit()" ' . $CSSClass . ' ' . $CSSID, $this->conf['drillDown.']['country.']['selectorBox.']['option.'], $noSelectionLabel);

			$markerArray['###SELECTOR_COUNTRIES###'] = $box;
		} else {
			$markerArray['###SELECTOR_COUNTRIES###'] = $this->pi_getLL('continent.selectorBox.emptyContinentLabel');
		}

		// MAKE HTML
		// check if a continent is selected
		$filePath = $this->conf['template.']['filters'];
		$templateCode = ContentObjectRenderer::getSubpart(ContentObjectRenderer::fileResource($filePath), '###WTDIRECTORY_FILTER_DRILLDOWN###');
		$this->content .= ContentObjectRenderer::substituteMarkerArray($templateCode, $markerArray);
		$this->content = $this->dynamicMarkers->main($this->conf, $this->cObj, $this->content); // Fill dynamic locallang or typoscript markers
		$this->content = preg_replace('|###.*?###|i', '', $this->content); // Finally clear not filled markers
		return $this->content;

	}


	/**
	 * build the availalbe categories for the drilldown view
	 *
	 * @return    [array]
	 */
	function drillDown_getCategories($cats, $selected) {

		foreach ($cats as $catID) {
			$returnCats[$catID] = $this->getCategoryTitleLocalized($catID);
		}

		// order categories
		if ($this->conf['drillDown.']['sortCategoriesByTitle'] == 1) {
			asort($returnCats);
		}

		return $returnCats;
	}


	// Function secure() uses wt_doorman to clear piVars
	function secure() {
		if (class_exists('tx_wtdoorman_security')) {

			// 2. settings for doorman
			$this->sec = GeneralUtility::makeInstance('tx_wtdoorman_security'); // Create new instance for security class
			$this->sec->secParams = array( // Allowed piVars type (int, text, alphanum, "value")
					'show' => 'int', // show should be integer
					'list' => '"all","none"', // list should be "all" or "none"
					'vCard' => 'int', // vCard should be integer
					'pointer' => 'int', // pointer should be integer
					'catfilter' => 'int', // catfilter should be integer
					'filter' => array(
							'*' => 'text' // every filter should be text
					),
					'ttnews' => 'int'
			);

			// 3. Add hook to manipulate secParams
			if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['piVars_hook']) {
				foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['piVars_hook'] as $_funcRef) {
					if ($_funcRef) {
						GeneralUtility::callUserFunction($_funcRef, $this->sec, $this);
					}
				}
			}

			// 4. Overwrite pivars now
			$this->piVars = $this->sec->sec($this->piVars); // overwrite piVars piVars from doorman class
		} else die ('Extension wt_doorman not found!');
	}


	// Function check() check if all is right
	function check() {
		if (count($this->conf) < 8 && empty($this->piVars['vCard'])) { // in the conf array are to less settings - so no ts is included to TYPO3
			die ('wt_directory typoscript not loaded!');
		}
	}

	/**
	 * Renders the drill down view
	 * @param    [array]    $catArray: array which holds arrays of the categories. Each array is a dropbox (cat level), key is the catUID, value the name of the categories
	 * @param    [array]    $selected: array which holds the categories, that are currently selected
	 * @return    [string]        ...
	 */

	/**
	 * renders a selector box
	 *
	 * @param    [array]            $options: all available elements
	 * @param    [string]        $selected: element, which is currently selected
	 * @param    [string]        $name: name of the element (also this is the post name)
	 * @param    [int]            $size: the size of the element
	 * @param    [boolean]        $no_selecetion: if true, an additional empty entry is rendered
	 * @param    [boolean]        $multiple: if true, multiple entries a possible, then a selector list is rendered instead of a combobox
	 * @return    [type]        ...
	 */
	function renderSelector($options, $selected, $name, $size = 1, $no_selecetion = true, $multiple = false, $additionalParams = '', $stdWrapConf = array(), $noSelectionLabel = '') {
		$is_selected = false;
		foreach ($options as $key => $option) {
			$sel = '';
			$label = $this->pi_getLL($option);
			if (!$label) $label = $option;
			if (is_array($selected)) {
				if (array_search($key, $selected) === FALSE) {
				} else {
					$sel = ' selected="selected"';
				}
			} else {
				if ($key == $selected) $sel = ' selected="selected"';
			}
			if ($sel <> '') $is_selected = true;
			$content .= '<option value="' . $key . '"' . $sel . '>' . $this->cObj->stdWrap($label, $stdWrapConf) . '</option>';
		}
		if ($is_selected == false) {
			$sel = ' selected="selected"';
		} else {
			$sel = '';
		}
		if ($no_selecetion === true) {
			$content = '<option value="noselection"' . $sel . '>' . $noSelectionLabel . '</option>' . $content;
		}
		if ($multiple) {
			return '<select name="' . $name . '[]" size="' . $size . '" multiple="multiple" ' . $additionalParams . '>' . $content . '</select>';
		} else {
			return '<select name="' . $name . '" size="' . $size . '" ' . $additionalParams . '>' . $content . '</select>';
		}

	}

	function getCategoryTitleLocalized($catID) {

		$row = $this->pi_getRecord('tt_address_group', $catID);
		$conf['sys_language_uid'] = $GLOBALS['TSFE']->sys_language_uid;

		$rowLocalized = $this->getRecordOverlay('tx_dam_cat', $row, $conf);
		// @todo edit ts value for titleLen and add a crop
		$title = trim($row['title']);
		if ($rowLocalized === False) {
			return $row['title'];
		} else {
			return $rowLocalized['title'];
		}

	}

	/**
	 * Creates language-overlay for records in general (where translation is found in records from the same table)
	 * In future versions this may support other overlays too (versions, ...)
	 *
	 * $conf = array(
	 *        'sys_language_uid' // sys_language uid of the wanted language
	 *        'lovl_mode' // Overlay mode. If "hideNonTranslated" then records without translation will not be returned un-translated but false
	 * )
	 *
	 * In FE mode sys_language_uid and lovl_mode will be get from TSFE automatically
	 *
	 * @param    string        Table name
	 * @param    array $row Record to overlay. Must containt uid, pid and $table]['ctrl']['languageField']
	 * @param    integer $conf Configuration array that defines the wanted overlay
	 * @param    string $mode TYPO3_MODE to be used: 'FE', 'BE'. Constant TYPO3_MODE is default. Special mode 'NONE' do not restrict queries.
	 * @return    mixed        Returns the input record, possibly overlaid with a translation. But if $OLmode is "hideNonTranslated" then it will return false if no translation is found.
	 */
	function getRecordOverlay($table, $row, $conf = array()) {
		global $TCA;


		$sys_language_content = intval($conf['sys_language_uid']);
		$OLmode = $conf['lovl_mode'];


		$sys_language_content = $sys_language_content ? $sys_language_content : $GLOBALS['TSFE']->sys_language_content;
		$OLmode = $OLmode ? $OLmode : $GLOBALS['TSFE']->sys_language_contentOL;


		if ($row['uid'] > 0 && $row['pid'] > 0) {
			if ($TCA[$table] && ($languageField = $TCA[$table]['ctrl']['languageField']) && ($transOrigPointerField = $TCA[$table]['ctrl']['transOrigPointerField'])) {
				if (!$TCA[$table]['ctrl']['transOrigPointerTable']) {

					$enableFields = ContentObjectRenderer::enableFields($table);

					// Will try to overlay a record only if the sys_language_content value is larger than zero.
					if ($sys_language_content > 0) {
						// Must be default language or [All], otherwise no overlaying:
						if ($row[$languageField] <= 0) {
							// Select overlay record:
							$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
									'*',
									$table,
									'pid=' . intval($row['pid']) .
									' AND ' . $languageField . '=' . intval($sys_language_content) .
									' AND ' . $transOrigPointerField . '=' . intval($row['uid']) .
									$enableFields,
									'',
									'',
									'1'
							);
							$olrow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

							$GLOBALS['TSFE']->sys_page->versionOL($table, $olrow);

							// Merge record content by traversing all fields:
							if (is_array($olrow)) {
								foreach ($row as $fN => $fV) {
									if ($fN != 'uid' && $fN != 'pid' && isset($olrow[$fN])) {
										if ($TCA[$table]['columns'][$fN]['l10n_mode'] !== 'exclude' && ($TCA[$table]['columns'][$fN]['l10n_mode'] !== 'mergeIfNotBlank' || strcmp(trim($olrow[$fN]), ''))) {
											$row[$fN] = $olrow[$fN];
										}
									}
								}
								$row['sys_language_uid'] = $olrow['sys_language_uid'];
								$row['_BASE_REC_UID'] = $row['uid'];
								$row['_LOCALIZED_UID'] = $olrow['uid'];


							} elseif ($OLmode === 'hideNonTranslated' && $row[$languageField] == 0) { // Unset, if non-translated records should be hidden. ONLY done if the source record really is default language and not [All] in which case it is allowed.
								$row = false;
							}

							// Otherwise, check if sys_language_content is different from the value of the record - that means a japanese site might try to display french content.
						} elseif ($sys_language_content != $row[$languageField]) {
							$row = false;
						}
					} else {
						// When default language is displayed, we never want to return a record carrying another language!:
						if ($row[$languageField] > 0) {
							$row = false;
						}
					}
				}
			}
		}

		return $row;
	}

	function getChildCategories($catID, $additionalCatIDs = array()) {
		if ($catID > 0) {
			$childs = array();
			if ($additionalCatIDs) {
				// get all tt_address uids which are belonging to the restricting category
				$additionalWhere = '';
				$SELECT = 'SELECT DISTINCT tt_address_group.uid ';
				$FROM = 'FROM tt_address_group, tt_address_group_mm ';
				$WHERE = 'WHERE parent_group = ' . $catID . ' AND tt_address_group_mm.uid_foreign = tt_address_group.uid ';
				foreach ($additionalCatIDs as $additionalCatID) {
					$WHERE .= 'AND tt_address_group_mm.uid_local IN (
								SELECT tt_address.uid
								FROM tt_address
								INNER JOIN tt_address_group_mm ON tt_address.uid = tt_address_group_mm.uid_local
								INNER JOIN tt_address_group ON tt_address_group_mm.uid_foreign = tt_address_group.uid
 								WHERE tt_address_group_mm.uid_foreign = ' . $additionalCatID . ')';
				}
				#GeneralUtility::debug($SELECT . $FROM . $WHERE);
				$res = $GLOBALS['TYPO3_DB']->sql_query($SELECT . $FROM . $WHERE);
			} else {
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tt_address_group', 'parent_group=' . $catID . $additionalWhere);
			}
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$childs[] = $row['uid'];
			}
			return $childs;
		} else return array();
	}


	/*
	 * function beforemain
	 *
	 * this function is called by the hook tx_wtdirectory_pi1->beforemain
	 *
	 */
	function beforemain($conf, &$pluginPiVars, $cObj, $plugin) {
		$this->pi_setPiVarDefaults();

		// load the ts setup, because it not available, during the hook call
		$this->conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_wtdirectory_pi2.'];

		// add country to catfilter
		if ((int)$this->piVars['country'] > 0) {
			$this->filters['catfilter'][] = $this->piVars['country'];
			$this->setPageFilters();
		}

		if ((int)$this->piVars['parentID']) {

			$cats = $this->getChildCategories($this->piVars['parentID']);
			foreach ($cats as $cat) {
				$this->filters['catfilterOR'][] = $cat;
			}
			$this->setPageFilters();
		}

		if ((int)$this->piVars['catfilter']) {
			$this->filters['catfilter'][] = $this->piVars['catfilter'];
		}

		// send catfilter to pi1
		if (count($this->filters['catfilter']) > 0) {
			// add exisiting piVars of catfilter from pi1 to the filter array
			foreach ($pluginPiVars['catfilter'] as $catID) {
				$this->filters['catfilter'][] = $catID;
			}
			$this->filters['catfilter'] = array_unique($this->filters['catfilter']);
			$pluginPiVars['catfilter'] = $this->filters['catfilter'];
		}

		// send catfilter to pi1
		if (count($this->filters['catfilterOR']) > 0) {
			$pluginPiVars['catfilterOR'] = $this->filters['catfilterOR'];
		}

		if ($this->conf['debug.']['beforemain.']['piVars'] == 1) GeneralUtility::debug($this->piVars);
		if ($this->conf['debug.']['beforemain.']['filters'] == 1) GeneralUtility::debug($this->filters);
		if ($this->conf['debug.']['beforemain.']['pluginPiVars'] == 1) GeneralUtility::debug($pluginPiVars);
		if ($this->conf['debug.']['beforemain.']['conf'] == 1) GeneralUtility::debug($this->conf);
	}

	/*
	 * function setPageFilters
	 *
	 * processes the ts options and sets the filters given by the current page
	 *
	 */
	function setPageFilters() {

		// load the ts part
		$pageFilters = $this->conf['pageFilters.'];

		// check if there is a filters defined for the current page
		if ($pageFilters[$GLOBALS['TSFE']->id . '.']) {

			// process each filter
			foreach ($pageFilters[$GLOBALS['TSFE']->id . '.'] as $filter => $value) {
				$this->filters[$filter][] = $value;
			}
		}
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/pi1/class.tx_wtdirectory_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/pi1/class.tx_wtdirectory_pi1.php']);
}

?>