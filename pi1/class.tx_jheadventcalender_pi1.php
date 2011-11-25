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
		$this->conf['modalFadeInTime'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'ajaxModalFadeInTime', 's_additional');
		$this->conf['dialogFadeInTime'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'ajaxDialogFadeInTime', 's_additional');
		$this->conf['modalDialogFadeOutTime'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'ajaxModalDialogFadeOutTime', 's_additional');
		
		$this->conf['usesnow'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'snowUsage', 's_snowstorm');
		$this->conf['snowColor'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'snowColor', 's_snowstorm');
		$this->conf['snowCharacter'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'snowCharacter', 's_snowstorm');
		$this->conf['snowUseTwinkleEffect'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'snowUseTwinkleEffect', 's_snowstorm');
		$this->conf['snowZIndex'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'snowZIndex', 's_snowstorm');
		$this->conf['snowFlakeWidth'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'snowFlakeWidth', 's_snowstorm');
		$this->conf['snowFlakeHeight'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'snowFlakeHeight', 's_snowstorm');

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
					$map .= '
						<area shape="' . $areaData['shape'] . '"
						 coords="' . $areaData['coords'] . ' " 
						 href=""  
						 id="' . $this->conf['wicket'][$i] . '"
						 alt="' . $areaData['alt'] . '" 
						/>';
				} else {
					$map .= '<area shape="' . $areaData['shape'] . '" coords="' . $areaData['coords'] . ' " href="' . $hrefTarget . '" alt="' . $areaData['alt'] . '" />';
				}
			}
			$i++;
		}
		$map .= '</map>';
		
		//ajax functionality
		if($this->conf['useajax']){
			if($this->conf['usesnow']){
				$javascript = '<script src="' . t3lib_extMgm::siteRelPath($this->extKey) . 'js/snowstorm.js"></script>';
				$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] .= $javascript;
			}
			
			$js = '
				<script type="text/javascript">
				
				function clearVariables(){
					document.getElementById(\'dialog\').style.top = "";
					document.getElementById(\'dialog\').style.left = "";
					document.getElementById(\'dialog\').innerHTML = "";
				}';
			if($this->conf['usesnow']){
				$js .= '
					snowStorm.snowColor = \'' . $this->conf['snowColor'] . '\';
					snowStorm.snowCharacter = \'' . $this->conf['snowCharacter'] . '\';
					snowStorm.useTwinkleEffect = ' . $this->conf['snowUseTwinkleEffect'] . ';
					snowStorm.zIndex = ' . $this->conf['snowZIndex'] . ';
					snowStorm.flakeWidth = ' . $this->conf['snowFlakeWidth'] . ';
					snowStorm.flakeHeight = ' . $this->conf['snowFlakeHeight'] . ';

					function stopSnowing(){
						snowStorm.stop();
					}';
			}
			
			$js .= '

				$(document).ready(function(){
					$(\'<div id="boxes"><div id="dialog" class="window" style="width: ' . $this->conf['layerWidth'] . 'px;height:' . $this->conf['layerHeight'] . 'px;"></div><div id="mask"></div></div>\').appendTo(\'body\');
				
					$(\'area\').click(function(e){
						e.preventDefault();
						var id = $(this).attr(\'id\');
												
						$.ajax({
							url: \'?eID=adventcalender\',
							type: \'GET\',
							data: \'pageID=\' + id,
							dataType: \'json\',
							success: function(result) {
								//alert(result.code);
								$(\'#dialog\').html(\'<h3>\' + result.pageTitle + \'<a id="dialogclose" href="#">Close it</a></h3><div>\' + result.code + \'<div>\');
							}
						});

						var maskHeight = $(document).height();
						var maskWidth = $(window).width();
						$(\'#mask\').css({\'width\':maskWidth,\'height\':maskHeight});

						var winH = $(window).height();
						var winW = $(window).width();
						$(\'#dialog\').css(\'top\',  winH/2-$(\'#dialog\').height()/2);
						$(\'#dialog\').css(\'left\', winW/2-$(\'#dialog\').width()/2);
						$(\'#dialog\').css(\'width\', ' . $this->conf['layerWidth'] . ');
						$(\'#dialog\').css(\'min-height\', ' . $this->conf['layerHeight'] . ');
						$(\'#dialog\').css(\'height\', \'auto\');
						
						$(\'#mask\').fadeIn(' . $this->conf['modalFadeInTime'] . ');
						$(\'#mask\').fadeTo("slow",0.8);
						$(\'#dialog\').fadeIn(' . $this->conf['dialogFadeInTime'] . ');

					});
					
					//if close button is clicked
					$(\'a#dialogclose\').click(function (e) {
						//Cancel the link behavior
						e.preventDefault();
						alert(\'Fenster schliessen...\');
						$(\'#mask, .window\').fadeOut(' . $this->conf['modalDialogFadeOutTime'] . ');
					}); 
					
					//if mask is clicked
					$(\'#mask\').click(function () {';
			if($this->conf['usesnow']){
				$js .= '
							stopSnowing();
					';
			}
			
			$js .= '
						$(this).fadeOut(' . $this->conf['modalDialogFadeOutTime'] . ');
						$(\'.window\').fadeOut(' . $this->conf['modalDialogFadeOutTime'] . ');
						window.setTimeout(\'clearVariables()\',' . 500 . ');
					});
				});
				</script>
			';
			
			$css = '<link rel="stylesheet" type="text/css" href="' . t3lib_extMgm::siteRelPath($this->extKey) . 'css/ajax.css" />';
			$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] .= $css;

		}
		
		$content='
			' . $js . '
			<img src="' . $this->conf['image'] . '" alt="' . $this->conf['altText'] . '" width="' . $this->conf['imageWidth'] . '" height="' . $this->conf['imageHeight'] . '" border="0" usemap="#' . $this->conf['usemap'] . '" title="' . $this->conf['altText'] . '" />
			' . $map . '
		';
	
		return $this->pi_wrapInBaseClass($content);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_adventcalender/pi1/class.tx_jheadventcalender_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_adventcalender/pi1/class.tx_jheadventcalender_pi1.php']);
}

?>