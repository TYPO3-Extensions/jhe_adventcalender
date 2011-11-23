<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Jari-Hermann Ernst, M.A. <jari-hermann.ernst@bad-gmbh.de>
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
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'Advent calender' for the 'jhe_adventcalender' extension.
 *
 * @author	Jari-Hermann Ernst, M.A. <jari-hermann.ernst@bad-gmbh.de>
 * @package	TYPO3
 * @subpackage	tx_jheadventcalender
 */
class tx_jheadventcalender_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_jheadventcalender_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_jheadventcalender_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'jhe_adventcalender';	// The extension key.
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj = 1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
	
		//get flexform data
		$this->pi_initPIFlexForm();
//t3lib_div::debug($this->cObj->data['pi_flexform']);
		$this->conf['image'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'image', 'sDEF');
		$this->conf['imageWidth'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'imageWidth', 'sDEF');
		$this->conf['imageHeight'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'imageHeight', 'sDEF');
		$this->conf['altText'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'altText', 'sDEF');
		
		$this->conf['usemap'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'usemap', 's_imagemap');
		$this->conf['imageMap'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'imageMap', 's_imagemap');
		
		for($i=1; $i< 25; $i++){
			$wicket = 'wicket' . $i;
			$this->conf['wicket'][$i] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],$wicket, 's_pids');
		}
		
		$this->conf['useajax'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'useajax', 's_additional');
		$this->conf['layerWidth'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'layerWidth', 's_additional');
		$this->conf['layerHeight'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'layerHeight', 's_additional');

		//rebuild image-map
		$imageMapArr = t3lib_div::xml2tree($this->conf['imageMap']);
		$imageMapAreaArr = $imageMapArr['map']['0']['ch']['area'];
		
		$map = '<map name="' . $this->conf['usemap'] . '" id="' . $this->conf['usemap'] . '">';
		$i=1;
		foreach($imageMapAreaArr as $area){
			$areaData = $area['attrs'];
			//create typolinks for area href
			//include_once(PATH_site.'typo3/sysext/cms/tslib/class.tslib_content.php');
			$cObj = t3lib_div::makeInstance('tslib_cObj');
			$hrefTarget = $cObj->typoLink_URL(array('parameter' => $this->conf['wicket'][$i]));

			if($hrefTarget){
				if($this->conf['useajax']){
					$map .= '<area shape="' . $areaData['shape'] . '" coords="' . $areaData['coords'] . ' " href="#" alt="' . $areaData['alt'] . '" onClick="openModal(\'#dialog\', \''. $this->conf['layerWidth'] .'\', \'' . $this->conf['layerHeight'] . '\', \'' . $this->conf['wicket'][$i] . '\')" />';
				} else {
					$map .= '<area shape="' . $areaData['shape'] . '" coords="' . $areaData['coords'] . ' " href="' . $hrefTarget . '" alt="' . $areaData['alt'] . '" />';
				}
			}
			$i++;
		}
		$map .= '</map>';
		
		//ajax functionality
		if($this->conf['useajax']){

			$javascript = '<script src="' . t3lib_extMgm::siteRelPath($this->extKey) . 'js/adventcalender.js"></script>';
			$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] .= $javascript;
						
			$css = '<link rel="stylesheet" type="text/css" href="' . t3lib_extMgm::siteRelPath($this->extKey) . 'css/ajax.css" />';
			$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] .= $css;

		}
		
		$content='
			<img src="' . $this->conf['image'] . '" alt="' . $this->conf['altText'] . '" width="' . $this->conf['imageWidth'] . '" height="' . $this->conf['imageHeight'] . '" border="0" usemap="#' . $this->conf['usemap'] . '" title="' . $this->conf['altText'] . '" />
			' . $map . '
			' . $ajaxPanel . '
		';
	
		return $this->pi_wrapInBaseClass($content);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_adventcalender/pi1/class.tx_jheadventcalender_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_adventcalender/pi1/class.tx_jheadventcalender_pi1.php']);
}

?>