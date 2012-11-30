<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2011 Alexander Kellner <alexander.kellner@in2code.de>
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

require_once(PATH_tslib . 'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('wt_directory') . 'lib/class.wtdirectory_div.php'); // load div class
require_once(t3lib_extMgm::extPath('wt_directory') . 'lib/class.wtdirectory_markers.php'); // load markers class
require_once(t3lib_extMgm::extPath('wt_directory') . 'lib/class.wtdirectory_filter_abc.php'); // load abc filter class
require_once(t3lib_extMgm::extPath('wt_directory') . 'lib/class.wtdirectory_filter_search.php'); // load search filter class
require_once(t3lib_extMgm::extPath('wt_directory') . 'lib/class.wtdirectory_filter_cat.php'); // load category filter class
require_once(t3lib_extMgm::extPath('wt_directory') . 'lib/class.wtdirectory_filter_radialsearch.php'); // load radialsearch class
require_once(t3lib_extMgm::extPath('wt_directory') . 'lib/class.wtdirectory_pagebrowser.php'); // load pagebrowser class
require_once(t3lib_extMgm::extPath('wt_directory') . 'lib/class.wtdirectory_dynamicmarkers.php'); // file for dynamicmarker functions

class tx_wtdirectory_pi1_list extends tslib_pibase {

	public $extKey = 'wt_directory'; // Extension key
	public $prefixId = 'tx_wtdirectory_pi1';		// Same as class name
	public $scriptRelPath = 'pi1/class.tx_wtdirectory_pi1_list.php';	// Path to this script relative to the extension dir.
	private $vCardType = '3134'; // type for vCard

	/**
	 * Generate the list view
	 *
	 * @param	array		TypoScript configuration
	 * @param	array		Plugin variables (GET and POST)
	 * @param	array		content Object
	 * @return	string		generated content
	 */
	public function main($conf, $piVars, $cObj) {
		// Config
    	$this->cObj = $cObj; // cObject
		$this->conf = $conf;
		$this->piVars = $piVars;
		$this->pi_loadLL();
		$this->tmpl = $this->wrappedSubpartArray = $this->query = array();
		$this->content = '';
		$i = $result = 0; // init
		$this->div = t3lib_div::makeInstance('wtdirectory_div'); // Create new instance for div class
		$this->markers = t3lib_div::makeInstance('wtdirectory_markers'); // Create new instance for div class
		$this->filter_abc = t3lib_div::makeInstance('tx_wtdirectory_filter_abc'); // Create new instance for abcfilter class
		$this->filter_search = t3lib_div::makeInstance('tx_wtdirectory_filter_search'); // Create new instance for searchfilter class
		$this->filter_cat = t3lib_div::makeInstance('tx_wtdirectory_filter_cat'); // Create new instance for catfilter class
		$this->filter_radialsearch = t3lib_div::makeInstance('tx_wtdirectory_filter_radialsearch'); // Create new instance for radialsearch class
		$this->pagebrowser = t3lib_div::makeInstance('tx_wtdirectory_pagebrowser'); // Create new instance for pagebrowser class
		$this->dynamicMarkers = t3lib_div::makeInstance('tx_wtdirectory_dynamicmarkers'); // New object: TYPO3 dynamicmarker function
		$this->tmpl['list']['all'] = $this->cObj->getSubpart($this->cObj->fileResource($this->conf['template.']['list']), '###WTDIRECTORY_LIST###'); // Load HTML Template
		$this->tmpl['list']['item'] = $this->cObj->getSubpart($this->tmpl['list']['all'], '###ITEM###'); // work on subpart 2
		$this->languid = $GLOBALS['TSFE']->tmpl->setup['config.']['sys_language_uid'] ? $GLOBALS['TSFE']->tmpl->setup['config.']['sys_language_uid'] : 0; // current language uid

		if ($this->conf['debug.']['beforemain.']['piVars'] == 1) {
			t3lib_div::debug($this->piVars, 'wt_directory: piVars');
		}
		if ($this->conf['debug.']['beforemain.']['conf'] == 1) {
			t3lib_div::debug($this->conf, 'wt_directory: TypoScript');
		}

		if (!$this->pi_getFFvalue($this->conf, 'shownone', 'mainconfig') || count($this->piVars) > 0) { // default mode (show entries at the beginning)
			// Define WHERE clause for db query
			$this->setFilter();
			if ($this->piVars['pointer'] > $this->overall()) { // Set pointer to 0 if pointer is too high and senseless
				$this->piVars['pointer'] = 0;
			}
			$this->limit = ($this->piVars['pointer'] > 0 ? $this->piVars['pointer'] : 0) . ',' . $this->conf['list.']['perPage']; // set limit for sql query

			$find_in_set = "'" . $this->pi_getFFvalue($this->conf, 'addresspool', 'mainconfig') . "'";

			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( // DB query
				$this->query['select'] = 'tt_address.*, tt_address.uid ttaddress_uid, tt_address_group.title tt_address_group_title, tt_address_group.uid tt_address_group_uid, tt_address_group.pid tt_address_group_pid',
				$this->query['from'] = 'tt_address LEFT JOIN tt_address_group_mm on (tt_address.uid = tt_address_group_mm.uid_local) LEFT JOIN tt_address_group on (tt_address_group_mm.uid_foreign = tt_address_group.uid)',
				$this->query['where'] = $this->filter . $this->query_pid . $this->query_cat . $this->cObj->enableFields('tt_address'),
				$this->query['groupby'],
				$this->query['orderby'] = (empty($this->conf['list.']['orderby'])) ? 'FIND_IN_SET(tt_address.uid,' . $find_in_set.')' :  addslashes($this->conf['list.']['orderby']),
				$this->query['limit'] = $this->limit
			);

			if ($this->conf['debug.']['beforemain.']['sql'] == 1) {
				t3lib_div::debug($GLOBALS['TYPO3_DB']->debug_lastBuiltQuery);
			}
			$num = $GLOBALS['TYPO3_DB']->sql_num_rows($res); // numbers of all entries
			if ($res) { // If there is a result
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) { // One loop for every tt_address entry

					$row['addressgroup'] = $this->div->getAddressgroups($row['ttaddress_uid'], $this->conf, $this->cObj); // Overwrite group name
					$allowedFields = t3lib_div::trimExplode(',', $this->pi_getFFvalue($this->conf, 'field', 'list'), 1);
					$row['country'] = $this->div->getCountryFromCountryCode($row['country'], $this); // rewrite Lang ISO Code with Country Title from static_info_tables
					if ($currentAddressGroup <> $row['tt_address_group_title']) {
						// store the new group 
						$currentAddressGroup = $row['tt_address_group_title'];
						$this->InnerMarkerArray['###WTDIRECTORY_TT_ADDRESS_GROUP_TITLE###'] = $this->cObj->stdWrap($row['tt_address_group_title'],$this->conf['list.']['tt_address_group_title.']);
						$row['tt_address_group_title']= $this->InnerMarkerArray['###WTDIRECTORY_TT_ADDRESS_GROUP_TITLE###'];
					} else {
						// return nothing, because it is still the same group
						$this->InnerMarkerArray['###WTDIRECTORY_TT_ADDRESS_GROUP_TITLE###'] = '';
						$row['tt_address_group_title']= '';
					}
					$this->InnerMarkerArray = $this->markers->makeMarkers('list', $this->conf, $row, $allowedFields, $this->piVars); // get markerArray

					// render Addressgroup header if the header has changed (works only, if the list is primary rendered by the titel of the addressgroup
					$this->InnerMarkerArray['###WTDIRECTORY_VCARD_ICON###'] = $this->conf['label.']['vCard']; // Image for vcard icon
					$this->InnerMarkerArray['###WTDIRECTORY_POWERMAIL_ICON###'] = $this->conf['label.']['powermail']; // Image for powermail icon

					if ($this->div->conditions4DetailLink($row, 'list', $this->conf)) {

						$linkConf = $this->conf['list.']['links.']['detaillink.'];

						// check for configuration from the flexform
						if ($this->pi_getFFvalue($this->conf, 'target', 'list')) {
							$linkConf['parameter'] = $this->pi_getFFvalue($this->conf, 'target', 'list');
							unset ($linkConf['parameter.']['data']);
						} else {
							if (!$linkConf['parameter']) {
								$linkConf['parameter'] = $GLOBALS['TSFE']->id;
							}
						}
						$linkConf['additionalParams'] = '&' . $this->prefixId . '[show]=' . $row['ttaddress_uid'];
						if ($linkConf['useGoogleMapLink'] == 1) {
							$linkConf['additionalParams'] .= '&tx_rggooglemap_pi1[poi]=' . $row['ttaddress_uid'];
						}
						$this->wrappedSubpartArray['###WTDIRECTORY_SPECIAL_DETAILLINK###'] = $this->cObj->typolinkWrap($linkConf);
					} else {
						$this->wrappedSubpartArray['###WTDIRECTORY_SPECIAL_DETAILLINK###'] = array(); // clean
					}
					$this->wrappedSubpartArray['###WTDIRECTORY_VCARD_LINK###'] = $this->cObj->typolinkWrap( array('parameter' => $GLOBALS['TSFE']->id, 'additionalParams' => '&type=' . $this->vCardType . '&' . $this->prefixId . '[vCard]=' . $row['ttaddress_uid'], 'useCacheHash' => 1) ); // Link to same page without GET params (vCard)
					if ($this->pi_getFFvalue($this->conf, 'target', 'powermail')) { // Link to powermail page
						$this->wrappedSubpartArray['###WTDIRECTORY_POWERMAIL_LINK###'] = $this->cObj->typolinkWrap( array('parameter' => $this->pi_getFFvalue($this->conf, 'target', 'powermail'), 'additionalParams' => '&' . $this->prefixId . '[pm_receiver]=' . $row['ttaddress_uid'], 'useCacheHash' => 1) );
					}
					if (t3lib_extMgm::isLoaded('rggooglemap', 0)) { // Link to target page with tt_address uid for googlmaps
						$this->wrappedSubpartArray['###WTDIRECTORY_GOOGLEMAP_LINK###'] = $this->cObj->typolinkWrap( array('parameter' => ($this->pi_getFFvalue($this->conf, 'target', 'googlemap') ? $this->pi_getFFvalue($this->conf, 'target', 'googlemap') : $GLOBALS['TSFE']->id), 'additionalParams' => $this->div->addFilterParams($this->piVars) . '&tx_rggooglemap_pi1[poi]=' . $row['ttaddress_uid'], 'useCacheHash' => 1) );
					}
					if ($this->div->alternate($i)) {
						$this->InnerMarkerArray['###WTDIRECTORY_ALTERNATE###'] = 'odd';
					} else {
						$this->InnerMarkerArray['###WTDIRECTORY_ALTERNATE###'] = 'even';
					}

					$this->content_item .= $this->cObj->substituteMarkerArrayCached($this->tmpl['list']['item'], $this->InnerMarkerArray, array(), $this->wrappedSubpartArray);
					$result = 1; // min 1 result
					$i++; // increase counter
				}
			}
			$this->OuterSubpartArray['###WTDIRECTORY_PAGEBROWSER###'] = $this->pagebrowser->main($this->conf, $this->piVars, $this->cObj, array('overall' => $this->overall(), 'overall_cur' => $num, 'pointer' => ($this->piVars['pointer'] > 0 ? $this->piVars['pointer'] : 0), 'perPage' => $this->conf['list.']['perPage']) );

		}

		$this->subpartArray = array('###CONTENT###' => $this->content_item); // work on subpart 3

		$tmp_OuterSubpartArray = $this->markers->makeMarkers('list', $this->conf, $row, t3lib_div::trimExplode(',', $this->pi_getFFvalue($this->conf, 'field', 'list'), 1), $this->piVars); // get markerArray
		$this->OuterSubpartArray = array_merge((array) $this->OuterSubpartArray, (array) $tmp_OuterSubpartArray); // add markers array to existing array
		if (!$result) { // no results
			if (!$this->pi_getFFvalue($this->conf, 'shownone', 'mainconfig') || count($this->piVars) > 0) { // default mode
				$this->OuterSubpartArray['###WTDIRECTORY_FILTER_NORESULTS###'] = '<span class="wtdirectory_noaddresses wtdirectory_noaddresses_notfound">' . $this->pi_getLL('wtdirectory_error_nolist', 'No addresses found.') . '</span>'; // no result message
			} else { // don't show something at the beginning
				$this->OuterSubpartArray['###WTDIRECTORY_FILTER_NORESULTS###'] = '<span class="wtdirectory_noaddresses wtdirectory_noaddresses_start">' . $this->pi_getLL('wtdirectory_error_makechoice', 'Please make your choice.') . '</span>';;
			}
		}
		$this->OuterSubpartArray['###WTDIRECTORY_FILTER_ABC###'] = $this->filter_abc->main($this->conf, $this->piVars, $this->query_pid, $this->query_cat); // include ABC filter
		$this->OuterSubpartArray['###WTDIRECTORY_FILTER_SEARCH###'] = $this->filter_search->main($this->conf, $this->piVars, $this->cObj); // include SEARCH filter
		$this->OuterSubpartArray['###WTDIRECTORY_FILTER_CAT###'] = $this->filter_cat->main($this->conf, $this->piVars); // include CAT filter
		$this->OuterSubpartArray['###WTDIRECTORY_FILTER_RADIALSEARCH###'] = $this->filter_radialsearch->main($this); // include Radial Search filter

		$this->hook('list'); // add hook to manipulate list view
		$this->content .= $this->cObj->substituteMarkerArrayCached($this->tmpl['list']['all'], $this->OuterSubpartArray, $this->subpartArray); // substitute Marker in Template
		$this->content = $this->dynamicMarkers->main($this->conf, $this->cObj, $this->content); // Fill dynamic locallang or typoscript markers
		$this->content = preg_replace('|###.*?###|i', '', $this->content); // Finally clear not filled markers

		return $this->content; // return HTML

    }

	/**
	 * Function overall() gives the number of all addresses
	 *
	 * @return	integer		overall items
	 */
	private function overall() {
		if ($this->conf['list.']['groupBy']) {
				if ($this->conf['list.']['groupBy']=='DISABLED') {
					$this->query['groupby']='';
				}
				else {
					$this->query['groupby'] = $this->conf['list.']['groupBy'];
				}
			}
			else {
				$this->query['groupby'] = 'tt_address.uid';
		}
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( // DB query
			$this->query['select'] = '*',
			$this->query['from'] = 'tt_address LEFT JOIN tt_address_group_mm on (tt_address.uid = tt_address_group_mm.uid_local) LEFT JOIN tt_address_group on (tt_address_group_mm.uid_foreign = tt_address_group.uid)',
			$this->query['where'] = $this->filter . $this->query_pid . $this->query_cat . $this->cObj->enableFields('tt_address'),
			$this->query['groupby'],
			$this->query['orderby'] = '',
			$this->query['limit'] = ''
		);
		$num = $GLOBALS['TYPO3_DB']->sql_num_rows($res); // numbers of all entries

		if (!empty($num)) {
			return $num;
		}
	}

	/**
	 * Creates queries for where clause (and respects given filter variables)
	 *
	 * @return	void
	 */
	private function setFilter() {
		// config
		$this->query_pid = ($this->cObj->data['pages'] > 0 ? ' AND tt_address.pid IN (' . $this->pi_getPidList($this->cObj->data['pages'], $this->cObj->data['recursive']) . ')' : ''); // where clause with pid
		$this->query_cat = ($this->pi_getFFvalue($this->conf, 'cat_join', 'mainconfig') > 0 && $this->pi_getFFvalue($this->conf, 'category', 'mainconfig') != '' ? ' AND tt_address_group.uid IN(' . $this->pi_getFFvalue($this->conf, 'category', 'mainconfig') . ')' : ''); // where clause for tt_address_group
		if ($this->pi_getFFvalue($this->conf, 'addresspool', 'mainconfig') != '') { // just use an addresspool
			$this->query_cat = ''; // clean category filter
			$this->query_pid = ' AND tt_address.uid IN (' . $this->pi_getFFvalue($this->conf, 'addresspool', 'mainconfig') . ')'; // addresspool filter
		}
		if ($this->pi_getFFvalue($this->conf, 'contact', 'tt_news') && $this->div->getAddressFromNews($this->piVars['ttnews']) > 0) { // if tt_news contactperson activated && contactperson was set
			$this->query_cat = ''; // clean category filter
			$this->query_pid = ' AND tt_address.uid = ' . $this->div->getAddressFromNews($this->piVars['ttnews']); // addresspool filter
		}
		$this->filter = ''; $set = 0; // init

		// let's go
		if (empty($this->piVars['filter'])) { // no filter set

			$this->filter = '1 = 1'; // default value (WHERE 1=1)

		} else { // filter set

			$filters = $this->piVars['filter'];
			if ($filters['all']) {
				$searchAllFields = explode(',', $this->conf['searchAllFields']);

				foreach ($searchAllFields as $field) {
					if (!empty($filters['all'])) {
						if ($filters['all'] == '@') { // 0-9
							#$this->filter .= 'tt_address.' . $key . ' < "@%" AND '; // add this filter to query
							$searchAllFilters[] = 'tt_address.' . $field . ' RLIKE "^[0-9]."'; // add this filter to query
							$set = 1; // min 1 filter was set
						} elseif ($filters['all'] == str_replace('%', '', $filters['all'])) { // without % like a word or a name
							$searchAllFilters[] =  'tt_address.' . $field . ' LIKE "%' . $filters['all'] . '%"'; // add this filter to query
							$set = 1; // min 1 filter was set
						} else { // value like a% or e%
							$searchAllFilters[] = 'tt_address.' . $field . ' LIKE "' . $filters['all'] . '"'; // add this filter to query
							$set = 1; // min 1 filter was set
						}
					}
				}

				$this->filter .= '(' . implode(' OR ', $searchAllFilters) .  ') AND ';
				unset($filters['all']);
			}

			if (is_array($filters)) { // if is array
				foreach ($filters as $key => $value) { // one loop for every filter
					if (!empty($value)) {
						if ($key != 'addressgroup') {
							if ($value == '@') { // 0-9
								#$this->filter .= 'tt_address.' . $key . ' < "@%" AND '; // add this filter to query
								$this->filter .= 'tt_address.' . $key . ' RLIKE "^[0-9]." AND '; // add this filter to query
								$set = 1; // min 1 filter was set
							} elseif ($value == str_replace('%', '', $value)) { // without % like a word or a name
								$this->filter .= 'tt_address.' . $key . ' LIKE "%' . $value . '%" AND '; // add this filter to query
								$set = 1; // min 1 filter was set
							} else { // value like a% or e%
								$this->filter .= 'tt_address.' . $key . ' LIKE "' . $value . '" AND '; // add this filter to query
								$set = 1; // min 1 filter was set
							}
						} else { // search filter for addressgroup title
							$this->filter .= 'tt_address_group.title LIKE "%' . $value . '%" AND '; // add this filter to query
							$set = 1; // min 1 filter was set
						}
					}
				}
			}
			if ($set == 0) { // default value (WHERE 1=1)
				$this->filter = '1 = 1';
			} else { // delete last " AND " of whole query
				$this->filter = substr(trim($this->filter), 0, -4);
			}
		}
		
		// RADIAL SEARCH Filter
		if (isset($this->piVars['radialsearch']['radius']) && isset($this->piVars['radialsearch']['zip'])) {
			$coordinates = $this->div->getCoordinatesFromZip(); // get lat and lng from German ZIP code
			if (isset($coordinates[$this->piVars['radialsearch']['zip']])) { // only if given zip is in table
				$this->filter .= ' AND (ACOS(SIN(RADIANS(' . $coordinates[$this->piVars['radialsearch']['zip']]['lat'] . ')) * SIN(RADIANS(tt_address.tx_rggooglemap_lat)) + COS(RADIANS(' . $coordinates[$this->piVars['radialsearch']['zip']]['lat'] . ')) * COS(RADIANS(tt_address.tx_rggooglemap_lat)) * COS(RADIANS(' . $coordinates[$this->piVars['radialsearch']['zip']]['lng'] . ') - RADIANS(tt_address.tx_rggooglemap_lng))) * 6380 < ' . intval($this->piVars['radialsearch']['radius']) . ')';
			}
		}

		// CATEGORY Filter
		if (!empty($this->piVars['catfilter'])) {
			if (is_array($this->piVars['catfilter'])) {
				foreach ($this->piVars['catfilter'] as $catID) {
					$this->filter .= ' AND tt_address.uid IN (SELECT tt_address.uid FROM tt_address INNER JOIN tt_address_group_mm ON tt_address.uid = tt_address_group_mm.uid_local WHERE tt_address_group_mm.uid_foreign= '. $catID  .')';
				}
			} else {
				// no array, so query with an and statement
				$this->filter .= ' AND tt_address_group.uid = ' . $this->piVars['catfilter']; // if catfilter set, add where clause
				$this->filter .= ' AND tt_address.uid IN (SELECT tt_address.uid FROM tt_address INNER JOIN tt_address_group_mm ON tt_address.uid = tt_address_group_mm.uid_local WHERE tt_address_group_mm.uid_foreign= '. $this->piVars['catfilter'] .')';
			}
		}
		
		// Countryfilter
		if (t3lib_extMgm::isLoaded('static_info_tables', 0) && $this->pi_getFFvalue($this->conf, 'countryfilter', 'mainconfig')) {
			$countries = t3lib_div::trimExplode(',', $this->pi_getFFvalue($this->conf, 'countryfilter', 'mainconfig'), 1); // get countries
			$this->filter .= ' AND (';
			for ($i = 0; $i < count($countries); $i++) { // one loop for every chosen country
				$fields = $this->div->getCountriesTitlesFromUid($countries[$i]);
				foreach ((array) $fields as $key => $value) {
					$this->filter .= 'tt_address.country = "' . $value . '"';
					$this->filter .= ' OR ';
				}
			}
			$this->filter .= '0)';
		}

		// OR Catfilter
		if (!empty($this->piVars['catfilterOR'])) {
			if (is_array($this->piVars['catfilterOR'])) {
				$cats = implode(',', $this->piVars['catfilterOR']);
				$this->filter .= ' AND tt_address_group_mm.uid_foreign IN ('. $cats   .')';
			}
		}

		// group by
		if ($this->conf['list.']['groupBy']) {
			if (strtolower($this->conf['list.']['groupBy']) == 'disabled') {
				$this->query['groupby'] = '';
			} else {
				$this->query['groupby'] = $this->conf['list.']['groupBy'];
			}
		} else {
			$this->query['groupby'] = 'tt_address.uid';
		}

		$this->hook('filter'); // add hook to manipulate filters
	}

	/**
	 * Adds hook
	 *
	 * @param	string		name of the hook
	 * @return	void
	 */
	public function hook($hookname) {
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey][$hookname])) { // Adds hook for processing
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey][$hookname] as $_classRef) {
				$_procObj = &t3lib_div::getUserObj($_classRef);
				$_procObj->mainList($this->conf, $this->piVars, $this->cObj, $this);
			}
		}
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/pi1/class.tx_wtdirectory_pi1_list.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/pi1/class.tx_wtdirectory_pi1_list.php']);
}

?>