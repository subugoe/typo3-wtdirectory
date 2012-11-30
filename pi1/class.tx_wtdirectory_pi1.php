<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2011 Alexander Kellner <alexander.kellner@in2code.de>, in2code.de
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
require_once(t3lib_extMgm::extPath('wt_directory') . 'pi1/class.tx_wtdirectory_pi1_list.php'); // load listview class
require_once(t3lib_extMgm::extPath('wt_directory') . 'pi1/class.tx_wtdirectory_pi1_detail.php'); // load detailview class
require_once(t3lib_extMgm::extPath('wt_directory') . 'pi1/class.tx_wtdirectory_pi1_vcard.php'); // load vcard class
if (t3lib_extMgm::isLoaded('wt_doorman', 0)) {
	require_once(t3lib_extMgm::extPath('wt_doorman') . 'class.tx_wtdoorman_security.php'); // load security class
}

/**
 * Plugin 'wt_directory (tt_address list and detail view)' for the 'wt_directory' extension.
 *
 * @author	Alexander Kellner <alexander.kellner@in2code.de>, in2code.de
 * @package	TYPO3
 * @subpackage	tx_wtdirectory
 */
class tx_wtdirectory_pi1 extends tslib_pibase {

	public $extKey        = 'wt_directory';	// The extension key.
	public $prefixId      = 'tx_wtdirectory_pi1';		// Same as class name
	public $scriptRelPath = 'pi1/class.tx_wtdirectory_pi1.php';	// Path to this script relative to the extension dir.

	/**
	 * Generate the main output for wt_directory
	 *
	 * @param	string		content
	 * @param	array		TypoScript configuration
	 * @return	string		generated content
	 */
	public function main($content, $conf) {
		// Config
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_initPIflexForm();
		$this->pi_USER_INT_obj = 0;	// USER
		$this->conf = array_merge($this->conf, (array) $this->cObj->data['pi_flexform']); // add flexform array to conf array
		// Instances and security function
		$this->div = t3lib_div::makeInstance('wtdirectory_div'); // Create new instance for div class
		$this->secure(); // Security options for piVars
		$this->check(); // Check if all is alright
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['beforemain'])) { // Adds hook for processing
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['beforemain'] as $_classRef) {
				$_procObj = &t3lib_div::getUserObj($_classRef);
				$_procObj->beforemain($this->conf, $this->piVars, $this->cObj, $this);
			}
		}
		// Main part
		if (!empty($this->piVars['vCard'])) { // if vCard GET param was set: vcard export

			$this->vCard = t3lib_div::makeInstance('tx_wtdirectory_pi1_vcard'); // Create new instance for vCard class
			$this->vCardArray = $this->vCard->main($this->conf, $this->piVars, $this->cObj); // vCard Download
			$this->content = $this->vCardArray['content'];
			$this->hook(); // add hook

			header('Content-type: text/directory');

			if ($GLOBALS['TSFE']->renderCharset == 'utf-8') {
				header('Content-Disposition: attachment; filename="' . strtolower(utf8_decode($this->vCardArray['filename'])) . '"');
			} else {
				header('Content-Disposition: attachment; filename="' . strtolower($this->vCardArray['filename']) . '"');
			}
			header('Pragma: public');
			if (!empty($this->content)) {
				return $this->content; // return content
			}

		} else { // in all other cases: default view

			switch (empty($this->piVars['show'])) { // piVar show not set?
				case 1: // Not set: show list view

					// check if view is configured by typoscript
					if ($this->conf['showView']) {
						switch ($this->conf['showView']) {
							case 'detailView':
								$this->detailView = t3lib_div::makeInstance('tx_wtdirectory_pi1_detail'); // Create new instance for detail class
								$this->content = $this->detailView->main($this->conf, $this->piVars, $this->cObj); // Detail view
								break;
						}
					}

					$this->listView = t3lib_div::makeInstance('tx_wtdirectory_pi1_list'); // Create new instance for list class
					$this->content = $this->listView->main($this->conf, $this->piVars, $this->cObj); // List view
					break;

				default: // piVars set: detail view
					$this->detailView = t3lib_div::makeInstance('tx_wtdirectory_pi1_detail'); // Create new instance for detail class
					$this->content = $this->detailView->main($this->conf, $this->piVars, $this->cObj); // Detail view
					break;
			}

			$this->hook(); // add hook
			if (!empty($this->content)) {
				return $this->pi_wrapInBaseClass($this->content); // return content
			}
		}

	}

	/**
	 * Function secure() uses wt_doorman to clear piVars
	 *
	 * @return	void
	 */
	private function secure() {
		if (class_exists('tx_wtdoorman_security')) {
			// 1. Get values from tt_news
			$varsFromNews = t3lib_div::_GP('tx_ttnews');
			if (isset($varsFromNews['tt_news'])) $this->piVars['ttnews'] = $varsFromNews['tt_news'];

			// 2. settings for doorman
			$this->sec = t3lib_div::makeInstance('tx_wtdoorman_security'); // Create new instance for security class
			$this->sec->secParams = array ( // Allowed piVars type (int, text, alphanum, "value")
				'show' => 'int', // show should be integer
				'list' => '"all","none"', // list should be "all" or "none"
				'vCard' => 'int', // vCard should be integer
				'pointer' => 'int', // pointer should be integer
				'catfilter' => 'int', // catfilter should be integer
				'filter' => array (
					'*' => 'text' // every filter should be text
				),
				'radialsearch' => array (
					'*' => 'text' // every filter should be text
				),
				'ttnews' => 'int'
			);

			// 3. Add hook to manipulate secParams
			if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['piVars_hook']) {
			   foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['piVars_hook'] as $_funcRef) {
				  if ($_funcRef) {
					 t3lib_div::callUserFunction($_funcRef, $this->sec, $this);
				  }
			   }
			}

			// 4. Overwrite pivars now
			$this->piVars = $this->sec->sec($this->piVars); // overwrite piVars piVars from doorman class
		} else {
			die ('Extension wt_doorman not found!');
		}
	}

	/**
	 * Function check() check if all is right
	 *
	 * @return	void
	 */
	private function check() {
		if (count($this->conf) < 10 && empty($this->piVars['vCard'])) { // in the conf array are to less settings - so no ts is included to TYPO3
			die ('wt_directory typoscript not loaded!');
		}
	}

	/**
	 * Function hook() adds hooks
	 *
	 * @return	void
	 */
	private function hook() {
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['main'])) { // Adds hook for processing
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['main'] as $_classRef) {
				$_procObj = &t3lib_div::getUserObj($_classRef);
				$_procObj->main($this->content, $this->conf, $this->piVars, $this->cObj, $this);
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/pi1/class.tx_wtdirectory_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/pi1/class.tx_wtdirectory_pi1.php']);
}

?>