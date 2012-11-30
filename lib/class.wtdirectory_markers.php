<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Alexander Kellner <alexander.kellner@in2code.de>, in2code.de
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

class wtdirectory_markers extends tslib_pibase {

	public $extKey = 'wt_directory'; // Extension key
	public $prefixId = 'tx_wtdirectory_pi1'; // Same as class name
	public $scriptRelPath = 'pi1/class.tx_wtdirectory_pi1_detail.php';	// Path to any script in pi1 for locallang
	private $notNeeded = array( // not allowed fields in whole field list
		'pid',
		'uid',
		'hidden',
		'deleted'
	);

	/**
	 * Function makeMarkers() makes markers from row (uid => ###WTDIRECTORY_UID###)
	 *
	 * @param	string		should contains 'detail' or 'list' to load the right html template
	 * @param	array		TypoScript
	 * @param	array		contains db values
	 * @param	array		contains allowed fields (from flexform)
	 * @param	array		contains related GET and POST params
	 * @return	string		generated content
	 */
	public function makeMarkers($what = '', $conf = array(), $row = array(), $allowedArray = array(), $piVars = array() ) {

		// config
		global $TSFE;
    	$this->cObj = $TSFE->cObj; // cObject
		$this->conf = $conf;
		$this->piVars = $piVars;
		$this->pi_loadLL();
		$this->pi_initPIflexForm();
		$this->tmpl = $markerArray = $markerArrayAll = $wrappedSubpartArray = $subpartArray = array(); $i = 0; // init
        $this->tmpl['all']['all'] = $this->cObj->getSubpart($this->cObj->fileResource($this->conf['template.']['ALLmarker']),'###WTDIRECTORY_ALL_' . strtoupper($what) . '###'); // Load HTML Template: ALL (works on subpart ###WTDIRECTORY_ALL###)
		$this->tmpl['all']['item'] = $this->cObj->getSubpart($this->tmpl['all']['all'],"###ITEM###"); // Load HTML Template: ALL (works on subpart ###ITEM###)
		$this->div = t3lib_div::makeInstance('wtdirectory_div'); // Create new instance for div class
		$this->cObj->start($row, 'tt_address'); // enable .field in typoscript for tt_address

		// 1. Fill marker "all": ###WTDIRECTORY_SPECIAL_ALL###
		// 1.1 If some fields where added to show only this fields
		if (!empty($allowedArray)) {
			foreach ($allowedArray as $key => $value) { // one loop for every db field
				if (($row[$value] && $this->conf['enable.']['hideDescription'] == 1) || $this->conf['enable.']['hideDescription'] == 0) { // Only if not empty when hide description activated
					$markerArrayAll['###WTDIRECTORY_LABEL###'] = $this->pi_getLL('wtdirectory_ttaddress_' . $value, ucfirst($value)); // label from locallang or take key if locallang empty
					$markerArrayAll['###WTDIRECTORY_KEY###'] = $this->div->clearName($value); // Add key for CSS
					$markerArrayAll['###WTDIRECTORY_VALUE###'] = ''; // clean
					$markerArrayAll['###ALTERNATE###'] = ($this->div->alternate($i) ? 'even' : 'odd'); // alternate
					$markerArrayAll['###WTDIRECTORY_VALUE###'] = $this->cObj->cObjGetSingle($this->conf[$what . '.']['field.'][$value], $this->conf[$what . '.']['field.'][$value . '.']); // value
					$i++; // increase counter
					$content_item .= $this->cObj->substituteMarkerArrayCached($this->tmpl['all']['item'], $markerArrayAll); // Add
				}
				else {
				}
			}
			$subpartArray['###CONTENT###'] = $content_item; // ###WTDIRECTORY_SPECIAL_ALL###

			// Global markers
			$OuterMarkerArray['###WTDIRECTORY_SPECIAL_BACKLINK_LABEL###'] = $this->pi_getLL('wtdirectory_special_backlink_label'); // "Back to listview"
			if ($this->div->conditions4DetailLink($row, $what, $this->conf)) { // "more..."
				$OuterMarkerArray['###WTDIRECTORY_SPECIAL_DETAILLINK_LABEL###'] = $this->pi_getLL('wtdirectory_special_detaillink_label');
			}
			$wrappedSubpartArray['###WTDIRECTORY_SPECIAL_BACKLINK###'] = array( '<a href="' . $this->pi_linkTP_keepPIvars_url(array('show' => ''), 1, 0, ($this->pi_getFFvalue($this->conf, 'target', 'detail') ? $this->pi_getFFvalue($this->conf, 'target', 'detail') : $GLOBALS["TSFE"]->id)) . '">', '</a>');
			if ($this->div->conditions4DetailLink($row, $what, $this->conf)) { // Link to same page with uid (Singleview)
				$wrappedSubpartArray['###WTDIRECTORY_SPECIAL_DETAILLINK###'] = $this->cObj->typolinkWrap( array("parameter" => ($this->pi_getFFvalue($this->conf, 'target', 'list') ? $this->pi_getFFvalue($this->conf, 'target', 'list') : $GLOBALS["TSFE"]->id), 'additionalParams' => '&' . $this->prefixId . '[show]=' . $row['ttaddress_uid'] . ($this->conf['filter.']['list.']['clearOldFilter'] == 0 ? $this->div->piVars2string() : '') . ($this->conf['enable.']['googlemapOnDetail'] == 1 && t3lib_extMgm::isLoaded('rggooglemap',0) ? '&tx_rggooglemap_pi1[poi]=' . $row['ttaddress_uid'] : ''), "useCacheHash" => 1) );
			}
			if (($this->conf['enable.']['vCardForList'] == 1 && $what == 'list') || ($this->conf['enable.']['vCardForDetail'] == 1 && $what == 'detail')) { // only if vcard enabled in constants
				$OuterMarkerArray['###WTDIRECTORY_VCARD_ICON###'] = $this->conf['label.']['vCard']; // Image for vcard icon
				$wrappedSubpartArray['###WTDIRECTORY_VCARD_LINK###'] = $this->cObj->typolinkWrap( array("parameter" => $GLOBALS["TSFE"]->id, 'additionalParams' => '&type=3134&' . $this->prefixId . '[vCard]=' . ($what == 'list' ? $row['ttaddress_uid'] : $row['uid']), "useCacheHash" => 1) ); // Link to same page with ?tx_wtdirectory_pi1[vCard]=uid
			}
			if (($this->conf['enable.']['powermailForList'] == 1 && $what == 'list') || ($this->conf['enable.']['powermailForDetail'] == 1 && $what == 'detail')) { // only if powermail link enabled in constants
				if ($this->pi_getFFvalue($this->conf, 'target', 'powermail') > 0) $OuterMarkerArray['###WTDIRECTORY_POWERMAIL_ICON###'] = $this->conf['label.']['powermail']; // Image for powermail icon
				if ($this->pi_getFFvalue($this->conf, 'target', 'powermail') > 0) $wrappedSubpartArray['###WTDIRECTORY_POWERMAIL_LINK###'] = $this->cObj->typolinkWrap( array("parameter" => $this->pi_getFFvalue($this->conf, 'target', 'powermail'), 'additionalParams' => '&' . $this->prefixId . '[pm_receiver]=' . ($what == 'list' ? $row['ttaddress_uid'] : $row['uid']), "useCacheHash" => 1) ); // Link to same page with powermailform with ?tx_wtdirectory_pi1[pm_receiver]=uid
			}
			if ($this->pi_getFFvalue($this->conf, 'enable', 'googlemap') == 1 && t3lib_extMgm::isLoaded('rggooglemap',0)) { // only if googlemap enabled in flexform && rggooglemap is installed
				$OuterMarkerArray['###WTDIRECTORY_GOOGLEMAP_LABEL###'] = $this->pi_getLL('wtdirectory_googlemaplink_label', 'Show in map'); // "Show in map"
				$wrappedSubpartArray['###WTDIRECTORY_GOOGLEMAP_LINK###'] = $this->cObj->typolinkWrap ( array ( "parameter" => ($this->pi_getFFvalue($this->conf, 'target', 'googlemap') ? $this->pi_getFFvalue($this->conf, 'target', 'googlemap') : $GLOBALS["TSFE"]->id), 'additionalParams' => $this->div->addFilterParams($this->piVars) . ($this->piVars['show'] ? '&' . $this->prefixId . '[show]=' . $this->piVars['show'] : '') . '&tx_rggooglemap_pi1[poi]=' . ($what == 'list' ? $row['ttaddress_uid'] : $row['uid']), "useCacheHash" => 1 ) );  // Link to target page with tt_address uid for googlmaps
			}

			$markerArray['###WTDIRECTORY_SPECIAL_ALL###'] = $this->cObj->substituteMarkerArrayCached($this->tmpl['all']['all'], $OuterMarkerArray, $subpartArray, $wrappedSubpartArray); // Fill ###WTDIRECTORY_SPECIAL_ALL###

		// 1.2 No fields to show where added, so show all
		} else {
			if (!empty($row)) {
				foreach ($row as $key => $value) { // one loop for every db field

					if (!in_array($key, $this->notNeeded)) { // if current field is allowed (deleted, hidden, etc..  not allowed)
						if (($value && $this->conf['enable.']['hideDescription'] == 1) || $this->conf['enable.']['hideDescription'] == 0) { // Only if not empty when hide description activated
							$markerArrayAll['###WTDIRECTORY_LABEL###'] = $this->pi_getLL('wtdirectory_ttaddress_' . $key, ucfirst($key)); // label from locallang or take key if locallang empty
							$markerArrayAll['###WTDIRECTORY_KEY###'] = $this->div->clearName($key); // Add key for CSS
							$markerArrayAll['###WTDIRECTORY_VALUE###'] = ''; // clean
							$markerArrayAll['###ALTERNATE###'] = ($this->div->alternate($i) ? 'even' : 'odd'); // alternate
							$markerArrayAll['###WTDIRECTORY_VALUE###'] = $this->cObj->cObjGetSingle($this->conf[$what . '.']['field.'][$key], $this->conf[$what . '.']['field.'][$key . '.']); // value
							if ($markerArrayAll['###WTDIRECTORY_VALUE###']=='') {
								// take db value
								$markerArrayAll['###WTDIRECTORY_VALUE###'] = $value;
							}
							$i++; // increase counter
							$content_item .= $this->cObj->substituteMarkerArrayCached($this->tmpl['all']['item'], $markerArrayAll); // Add
						} else {
#							t3lib_div::debug($key);
						}
					}
				}
				$subpartArray['###CONTENT###'] = $content_item; // ###WTDIRECTORY_SPECIAL_ALL###
				// Global markers outer
				$OuterMarkerArray['###WTDIRECTORY_SPECIAL_BACKLINK_LABEL###'] = $this->pi_getLL('wtdirectory_special_backlink_label'); // "Back to listview"
				if ($this->div->conditions4DetailLink($row, $what, $this->conf)) { // "more..."
					$OuterMarkerArray['###WTDIRECTORY_SPECIAL_DETAILLINK_LABEL###'] = $this->pi_getLL('wtdirectory_special_detaillink_label');
				}
				$wrappedSubpartArray['###WTDIRECTORY_SPECIAL_BACKLINK###'] = array( '<a href="' . $this->pi_linkTP_keepPIvars_url(array('show' => ''), 1, 0, ($this->pi_getFFvalue($this->conf, 'target', 'detail') ? $this->pi_getFFvalue($this->conf, 'target', 'detail') : $GLOBALS["TSFE"]->id)) . '">', '</a>');
				if ($this->div->conditions4DetailLink($row, $what, $this->conf)) { // Link to same page with uid (Singleview)
					$wrappedSubpartArray['###WTDIRECTORY_SPECIAL_DETAILLINK###'] = $this->cObj->typolinkWrap( array("parameter" => ($this->pi_getFFvalue($this->conf, 'target', 'list') ? $this->pi_getFFvalue($this->conf, 'target', 'list') : $GLOBALS["TSFE"]->id), 'additionalParams' => '&' . $this->prefixId . '[show]=' . $row['ttaddress_uid'] . ($this->conf['filter.']['list.']['clearOldFilter'] == 0 ? $this->div->piVars2string() : '') . ($this->conf['enable.']['googlemapOnDetail'] == 1 && t3lib_extMgm::isLoaded('rggooglemap',0) ? '&tx_rggooglemap_pi1[poi]=' . $row['ttaddress_uid'] : ''), "useCacheHash" => 1) );
				}
				if (($this->conf['enable.']['vCardForList'] == 1 && $what == 'list') || ($this->conf['enable.']['vCardForDetail'] == 1 && $what == 'detail')) { // only if vcard enabled in constants
					$OuterMarkerArray['###WTDIRECTORY_VCARD_ICON###'] = $this->conf['label.']['vCard']; // Image for vcard icon
					$wrappedSubpartArray['###WTDIRECTORY_VCARD_LINK###'] = $this->cObj->typolinkWrap( array("parameter" => $GLOBALS["TSFE"]->id, 'additionalParams' => '&type=3134&' . $this->prefixId . '[vCard]=' . ($what == 'list' ? $row['ttaddress_uid'] : $row['uid']), "useCacheHash" => 1) ); // Link to same page with ?tx_wtdirectory_pi1[vCard]=uid
				}
				if (($this->conf['enable.']['powermailForList'] == 1 && $what == 'list') || ($this->conf['enable.']['powermailForDetail'] == 1 && $what == 'detail')) { // only if powermail link enabled in constants
					if ($this->pi_getFFvalue($this->conf, 'target', 'powermail') > 0) $OuterMarkerArray['###WTDIRECTORY_POWERMAIL_ICON###'] = $this->conf['label.']['powermail']; // Image for powermail icon
					if ($this->pi_getFFvalue($this->conf, 'target', 'powermail') > 0) $wrappedSubpartArray['###WTDIRECTORY_POWERMAIL_LINK###'] = $this->cObj->typolinkWrap( array("parameter" => $this->pi_getFFvalue($this->conf, 'target', 'powermail'), 'additionalParams' => '&' . $this->prefixId . '[pm_receiver]=' . ($what == 'list' ? $row['ttaddress_uid'] : $row['uid']), "useCacheHash" => 1) ); // Link to same page with powermailform with ?tx_wtdirectory_pi1[pm_receiver]=uid
				}
				if ($this->pi_getFFvalue($this->conf, 'enable', 'googlemap') == 1 && t3lib_extMgm::isLoaded('rggooglemap',0)) { // only if googlemap enabled in flexform && rggooglemap is installed
					$OuterMarkerArray['###WTDIRECTORY_GOOGLEMAP_LABEL###'] = $this->pi_getLL('wtdirectory_googlemaplink_label', 'Show in map'); // "Show in map"
					$wrappedSubpartArray['###WTDIRECTORY_GOOGLEMAP_LINK###'] = $this->cObj->typolinkWrap ( array ( "parameter" => ($this->pi_getFFvalue($this->conf, 'target', 'googlemap') ? $this->pi_getFFvalue($this->conf, 'target', 'googlemap') : $GLOBALS["TSFE"]->id), 'additionalParams' => $this->div->addFilterParams($this->piVars) . ($this->piVars['show'] ? '&' . $this->prefixId . '[show]=' . $this->piVars['show'] : '') . '&tx_rggooglemap_pi1[poi]=' . ($what == 'list' ? $row['ttaddress_uid'] : $row['uid']), "useCacheHash" => 1 ) );  // Link to target page with tt_address uid for googlmaps
				}

				$markerArray['###WTDIRECTORY_SPECIAL_ALL###'] = $this->cObj->substituteMarkerArrayCached($this->tmpl['all']['all'], $OuterMarkerArray, $subpartArray, $wrappedSubpartArray); // Fill ###WTDIRECTORY_SPECIAL_ALL###
			}
		}


		// 2. Fill individual marker
		if (!empty($row)) { // If row is set
			foreach ($this->conf[$what . '.']['field.'] as $key => $value) { // one loop for every db field
				if (!stristr($key, '.')) { // only if no . is in ts
					$markerArray['###WTDIRECTORY_' . strtoupper($key) . '###'] = $this->cObj->cObjGetSingle($this->conf[$what . '.']['field.'][$key], $this->conf[$what . '.']['field.'][$key . '.']); // value
				}
			}
		}

		// FIXME Hack for adding group titles
		$markerArray['###WTDIRECTORY_TT_ADDRESS_GROUP_TITLE###'] = $row['tt_address_group_title'];
		// 3. Fill global markers
		$markerArray['###WTDIRECTORY_SPECIAL_BACKLINK_LABEL###'] = $this->pi_getLL('wtdirectory_special_backlink_label'); // "Back to listview"
		if ($this->div->conditions4DetailLink($row, $what, $this->conf)) { // "more..."
			$markerArray['###WTDIRECTORY_SPECIAL_DETAILLINK_LABEL###'] = $this->pi_getLL('wtdirectory_special_detaillink_label');
		}

		if (!empty($markerArray)) {
			return $markerArray;
		}
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_markers.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_markers.php']);
}

?>