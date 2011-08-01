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

require_once(PATH_t3lib . 'class.t3lib_befunc.php');
require_once(PATH_t3lib . 'stddb/tables.php');
require_once(PATH_tslib . 'class.tslib_pibase.php');

class tx_wtdirectory_autocomplete extends tslib_pibase {

	private $extKey = 'wt_directory'; // Extension key
	private $limit = 100; // limit for db query
	private $content = '';
	
	/**
	 * Function main() to set email receiver in session for powermail (if form is rendered in powermail)
	 *
	 * @return	string		List for autocompleete
	 */
    public function main() {
		// config
		tslib_eidtools::connectDB();
		$this->pid = htmlentities(t3lib_div::_GP('pid')); // GET param
		$this->cat = htmlentities(t3lib_div::_GP('cat')); // GET param
		$this->field = htmlentities(t3lib_div::_GP('field')); // GET param
		$this->search = htmlentities(trim(t3lib_div::_GP('search'))); // GET param
		if (empty($this->field)) {
			return $this->wrapToList($this->wrapToList('ERROR: No database field given'), 'ul'); // check if field was given otherwise return error
		}
		if ($this->field == 'all') {
			// todo support for autocomplete
			return '';
		}
		// Let's go
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( // DB query
			'DISTINCT tt_address.' . $this->field,
			'
				tt_address 
				LEFT JOIN tt_address_group_mm on (tt_address.uid = tt_address_group_mm.uid_local) 
				LEFT JOIN tt_address_group on (tt_address_group_mm.uid_foreign = tt_address_group.uid)
			',
			$this->whereClause() . ' AND tt_address.deleted = 0 AND tt_address.hidden = 0',
			$this->query['groupby'] = 'tt_address.uid, tt_address.' . $this->field,
			$this->query['orderby'] = 'tt_address.' . $this->field,
			$this->query['limit'] = $this->limit
		);

		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['wt_directory']);
		$utf8Decode = $extConf['utf8Decode'];
		if ($res) { // If there is a result
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) { // One loop for every tt_address entry
				if ($utf8Decode) {
					$this->content .= $this->wrapToList(utf8_decode($row[$this->field]));
				}
				else {
					$this->content .= $this->wrapToList($row[$this->field]);
				}
			}
		}
		$this->content = $this->wrapToList($this->content, 'ul');

		return $this->content;
    }

	/**
	 * Generate the where clause for the main function
	 *
	 * @return	string		Where Clause
	 */
	private function whereClause() {
		$where = '1';
		if (!empty($this->pid)) {
			$where .= ' AND tt_address.pid IN(' . $this->pid . ')';
		}
		if (!empty($this->cat)) {
			$where .= ' AND tt_address_group.uid IN(' . $this->cat . ')';
		}
		if (!empty($this->search)) {
			$where .= ' AND tt_address.' . $this->field . ' LIKE "%' . $this->search . '%"';
		}

		return $where;
	}

	/**
	 * Wraps string with html tag
	 *
	 * @param	string		Non-Wrapped string
	 * @return	string		Wrapped string
	 */
	private function wrapToList($string, $mode = 'li') {
		if ($mode == 'li') {
			$tag = 'li';
		} else {
			$tag = 'ul';
		}

		return '<' . $tag . '>' . $string . '</' . $tag . '>';
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_eid_autocomplete.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/lib/class.wtdirectory_eid_autocomplete.php']);
}

$SOBE = t3lib_div::makeInstance('tx_wtdirectory_autocomplete'); // make instance
echo $SOBE->main(); // print content
?>