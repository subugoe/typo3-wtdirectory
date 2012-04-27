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

require_once(PATH_tslib . 'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('wt_directory') . 'lib/class.wtdirectory_div.php'); // load div class
require_once(t3lib_extMgm::extPath('wt_directory') . 'lib/class.wtdirectory_markers.php'); // load markers class
require_once(t3lib_extMgm::extPath('wt_directory') . 'lib/class.wtdirectory_dynamicmarkers.php'); // file for dynamicmarker functions

class tx_wtdirectory_pi1_detail extends tslib_pibase {

	public $extKey = 'wt_directory'; // Extension key
	public $prefixId = 'tx_wtdirectory_pi1';		// Same as class name
	public $scriptRelPath = 'pi1/class.tx_wtdirectory_pi1_detail.php';	// Path to this script relative to the extension dir.
	private $vCardType = '3134'; // type for vCard

	/**
	 * Generate the detail view
	 *
	 * @param	array		TypoScript configuration
	 * @param	array		Plugin variables (GET and POST)
	 * @param	array		content Object
	 * @return	string		generated content
	 */
	public function main($conf, $piVars, $cObj) {
		// Config
		$this->cObj = $cObj; // cObject
		$this->conf = $conf; // make it global
		$this->piVars = $piVars; // make it global
		$this->pi_loadLL();
		$this->div = t3lib_div::makeInstance('wtdirectory_div'); // Create new instance for div class
		$this->markers = t3lib_div::makeInstance('wtdirectory_markers'); // Create new instance for div class
		$this->dynamicMarkers = t3lib_div::makeInstance('tx_wtdirectory_dynamicmarkers'); // New object: TYPO3 dynamicmarker function
		$this->tmpl = array(); // init
		$this->tmpl['detail'] = $this->cObj->getSubpart($this->cObj->fileResource($this->conf['template.']['detail']), '###WTDIRECTORY_DETAIL###'); // Load HTML Template
		$this->languid = $GLOBALS['TSFE']->tmpl->setup['config.']['sys_language_uid'] ? $GLOBALS['TSFE']->tmpl->setup['config.']['sys_language_uid'] : 0; // current language uid

		$addressUid = $this->piVars['show']; // given uid
		if ($addressUid > 0) { // if show param is set
			if ($this->div->allowedDetailUID($addressUid, $this)) { // if given uid is allowed to show
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( // DB query
					$query['select'] = 'tt_address.*, tt_address_group.title tt_address_group_title, tt_address_group.uid tt_address_group_uid, tt_address_group.pid tt_address_group_pid',
					$query['from'] = 'tt_address LEFT JOIN tt_address_group_mm ON (tt_address.uid = tt_address_group_mm.uid_local) LEFT JOIN tt_address_group ON (tt_address_group_mm.uid_foreign = tt_address_group.uid)',
					$query['where'] = 'tt_address.uid = ' . $addressUid . $this->cObj->enableFields('tt_address'),
					$query['groupby'] = 'tt_address.uid',
					$query['orderby'] = 'tt_address_group_mm.sorting',
					$query['limit'] = 1
				);
				if ($res) $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res); // Result in array

				if ($row['uid'] > 0) { // address found
					$groups = $this->div->getAddressgroups($addressUid, $this->conf, $this->cObj); // Overwrite group name
					$row['addressgroup'] = $groupsStr = implode('', $groups); // implode group array

					if ($this->conf['detail.']['field.']['addressgroup.']['field'] == 'addressgroup_uids' && !empty($groups)) {
						foreach ($groups as $groupUid => $groupTitle) {
							$row['addressgroup_uids'] .= $this->cObj->wrap($groupUid, $conf['wrap.']['addressgroup']); // wrap each group
						}
					}

					$row['country'] = $this->div->getCountryFromCountryCode($row['country'], $this); // rewrite Lang ISO Code with Country Title from static_info_tables

					if ($this->conf['detail.']['title']) { // Page title
						$GLOBALS['TSFE']->page['title'] = $this->div->marker2value($this->conf['detail.']['title'], $row); // set pagetitle
						$GLOBALS['TSFE']->indexedDocTitle = $this->div->marker2value($this->conf['detail.']['title'], $row); // set pagetitle for indexed search
					}

					// Markers
					$this->markerArray = $this->markers->makeMarkers('detail', $this->conf, $row, t3lib_div::trimExplode(',', $this->pi_getFFvalue($this->conf, 'field', 'detail'), 1), $this->piVars); // get markerArray
					$this->markerArray['###WTDIRECTORY_VCARD_ICON###'] = $this->conf['label.']['vCard']; // Image for vcard icon
					$this->markerArray['###WTDIRECTORY_POWERMAIL_ICON###'] = $this->conf['label.']['powermail']; // Image for powermail icon
					$this->wrappedSubpartArray['###WTDIRECTORY_VCARD_LINK###'] = $this->cObj->typolinkWrap( array('parameter' => $GLOBALS['TSFE']->id, 'additionalParams' => '&type=' . $this->vCardType . '&' . $this->prefixId . '[vCard]=' . $row['uid'], 'useCacheHash' => 1) ); // Link to same page with uid for vCard
					if ($this->pi_getFFvalue($this->conf, 'target', 'powermail')) $this->wrappedSubpartArray['###WTDIRECTORY_POWERMAIL_LINK###'] = $this->cObj->typolinkWrap( array('parameter' => $this->pi_getFFvalue($this->conf, 'target', 'powermail'), 'additionalParams' => '&' . $this->prefixId . '[pm_receiver]=' . $row['uid'], 'useCacheHash' => 1) ); // Link to powermail page with uid for receiver manipulation
					//$this->wrappedSubpartArray['###WTDIRECTORY_SPECIAL_BACKLINK###'] = $this->cObj->typolinkWrap( array('parameter' => ($this->pi_getFFvalue($this->conf, 'target', 'detail') ? $this->pi_getFFvalue($this->conf, 'target', 'detail') : $GLOBALS['TSFE']->id), 'useCacheHash' => 1) ); // Link to same page without GET params (Listview)
					$this->wrappedSubpartArray['###WTDIRECTORY_SPECIAL_BACKLINK###'] = array( '<a href="' . $this->pi_linkTP_keepPIvars_url(array('show' => ''), 1, 0, ($this->pi_getFFvalue($this->conf, 'target', 'detail') ? $this->pi_getFFvalue($this->conf, 'target', 'detail') : $GLOBALS["TSFE"]->id)) . '">', '</a>');
					if (t3lib_extMgm::isLoaded('rggooglemap',0)) $this->wrappedSubpartArray['###WTDIRECTORY_GOOGLEMAP_LINK###'] = $this->cObj->typolinkWrap( array('parameter' => ($this->pi_getFFvalue($this->conf, 'target', 'googlemap') ? $this->pi_getFFvalue($this->conf, 'target', 'googlemap') : $GLOBALS['TSFE']->id), 'additionalParams' => ($addressUid ? '&' . $this->prefixId . '[show]=' . $addressUid : '') . '&tx_rggooglemap_pi1[poi]=' . $row['uid'], 'useCacheHash' => 1) ); // Link to target page with tt_address uid for googlmaps

					$this->hook(); // add hook
					$this->content = $this->cObj->substituteMarkerArrayCached($this->tmpl['detail'], $this->markerArray, array(), $this->wrappedSubpartArray); // substitute Marker in Template
					$this->content = $this->dynamicMarkers->main($this->conf, $this->cObj, $this->content); // Fill dynamic locallang or typoscript markers
					$this->content = preg_replace('|###.*?###|i', '', $this->content); // Finally clear not filled markers

				} else { // no address to uid found
					$this->content = '<div class="wtdirectory_error wtdirectory_error_single">' . $this->pi_getLL('wtdirectory_error_nodetail') . '</div>';
				}

			} else { // detail uid is not allowed
				$this->content = '<div class="wtdirectory_error wtdirectory_error_forbiddenuid">' . $this->pi_getLL('wtdirectory_error_nodetail_forbidden', 'Given uid is not allowed to show') . '</div>';
			}

		}

		$this->emailRedirect($row['email']); // redirect to email

		if (!empty($this->content)) return $this->content;

	}


	/**
	 * Function emailRedirect() opens Outlook Window with email for current address
	 *
	 * @param	string		email
	 * @return	void
	 */
	public function emailRedirect($email) {
		if ($this->conf['detail.']['emailredirect'] == 1 && t3lib_div::validEmail($email)) { // only if email redirect activated and correct email
			header('HTTP/1.1 302 Moved Temporarily');
			header('Location: mailto:'.$email);
			header('Connection: close');
		}
	}


	/**
	 * Adds hook
	 *
	 * @param	string		name of the hook
	 * @return	void
	 */
	public function hook() {
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['detail'])) { // Adds hook for processing
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['detail'] as $_classRef) {
				$_procObj = &t3lib_div::getUserObj($_classRef);
				$_procObj->detail($this->markerArray, $this->conf, $this->piVars, $this->cObj, $this);
			}
		}
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/pi1/class.tx_wtdirectory_pi1_detail.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/pi1/class.tx_wtdirectory_pi1_detail.php']);
}

?>