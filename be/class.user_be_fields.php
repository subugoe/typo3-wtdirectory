<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Alex Kellner <alexander.kellner@in2code.de>, in2code.de
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

/**
 * Show fields in the backend
 *
 * @author	Alex Kellner <alexander.kellner@in2code.de>, in2code.de
 * @package	TYPO3
 * @subpackage	user_be_fields
 */
class user_be_fields {

	/**
	 * Don't show this fields in the selector
	 *
	 * @var array
	 */
	private $notAllowedFields = array( // fields which are not allowed to show
		'uid',
		'pid',
		'hidden',
		'deleted'
	);

	/**
	 * Manipulate params array for field selecting in flexform
	 *
	 * @param	array		Selector params
	 * @param	array		Parent object
	 * @return	void
	 */
	public function main(&$params, &$pObj)	{
		$res = mysql_query('SHOW COLUMNS FROM tt_address'); // mysql query
		if ($res) { // If there is a result
			$i = 0; // init counter
			$this->tsconfig = t3lib_BEfunc::getModTSconfig($params['row']['pid'], 'wt_directory'); // get tsconfig from backend

			if ($params['config']['itemsProcFuncArg'] == 'searchAll') {
				// add the "all" value
				$params['items'][$i]['0'] = $pObj->sL('LLL:EXT:wt_directory/locallang_db.xml:pi_flexform.search_all'); // Option name
				$params['items'][$i]['1'] = 'all'; // Option value
			}

			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) { // One loop for every result
				if ($row['Field'] && !in_array($row['Field'], $this->notAllowedFields)) {
					$i++;
					// Generate name
					$curName = ($pObj->sL('LLL:EXT:tt_address/locallang_tca.xml:tt_address.' . $row['Field']) ? $pObj->sL('LLL:EXT:tt_address/locallang_tca.xml:tt_address.' . $row['Field']) : $pObj->sL('LLL:EXT:lang/locallang_general.xml:LGL.' . $row['Field'])); // Get name from tt_address locallang
					if ($row['Field'] == 'tstamp') { // if timestamp field
						$curName = $pObj->sL('LLL:EXT:wt_directory/locallang_db.xml:tt_address.' . $row['Field']); // get from wt_directory locallang
					}
					if ($this->tsconfig['properties']['label.'][$row['Field']]) {
						$curName = $this->tsconfig['properties']['label.'][$row['Field']]; // overwrite name with value from tsconfig
					}
					if (!$curName) $curName = '"' . $row['Field'] . '"'; // take Fieldname, if no name
					$curName = str_replace(':', '', $curName); // remove ':'

					// Manipulate options
					$params['items'][$i]['0'] = $curName; // Option name
					$params['items'][$i]['1'] = $row['Field']; // Option value
				}

			}

			$params['items'][] = array(
				'0' => 'GroupTitle',
				'1' => 'tt_address_group_title'
			);

		}

	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/be/class.user_be_fields.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/be/class.user_be_fields.php']);
}

?>