<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Jari-Hermann Ernst <jari-hermann.ernst@bad-gmbh.de>
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
if (!defined ('PATH_typo3conf')) die ('Could not access this script directly!');

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_tslib.'class.tslib_eidtools.php');
require_once(PATH_t3lib.'class.t3lib_div.php');

class ajax_adventcalender extends tslib_pibase {

	var $extKey = 'jhe_adventcalender';
	
	function init(){
		require_once(PATH_tslib.'class.tslib_content.php');
		require_once(PATH_t3lib.'class.t3lib_page.php');
		tslib_eidtools::connectDB();
		$GLOBALS['TSFE']->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		$GLOBALS['TSFE']->tmpl = t3lib_div::makeInstance('t3lib_TStemplate');
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
	}
	
	
	/**
	 * Main Methode
	 *
	 * @return string
	 */
	public function main() {
		$this->init();

		$feUserObject = tslib_eidtools::initFeUser();
		
		//retrieving GET data
		$pageID = t3lib_div::_GET('pageID');
	
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages', 'uid = ' . $pageID);
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			$pageName = $row['title'];
		}
		
		$selectContent = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tt_content', 'pid = ' . $pageID);
		while ($content = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($selectContent)){
			$contentTitle = $content['header'];
		}
		
		$link = $this->cObj->getTypoLink_URL($pageID);

		if($_SERVER['HTTPS'] == 'on') {
			$protocol = 'https://';
		} else {
			$protocol = 'http://';
		}
		$url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . $link;
		
		$fileContent = file_get_contents($url); 
          $pos1 = strpos($fileContent,'<body>') + strlen('<body>'); 
		$pos2 = strpos($fileContent,'</body>');
                                 
		$content = trim(substr($fileContent,$pos1,$pos2-$pos1));
			
		$return = array(
		    'pageTitle' => $pageName,
		    'contentTitle' => $contentTitle,
		    'url' => $url,
		    'code' => $content
		);

		return t3lib_div::array2json($return);
	}
}

$output = t3lib_div::makeInstance('ajax_adventcalender');
echo $output->main();
?>