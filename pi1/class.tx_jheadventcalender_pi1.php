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
	 * @return			The content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj = 1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
	
		//get flexform data
		$this->pi_initPIFlexForm();

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
		$this->conf['snowFlakeColor'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'snowFlakeColor', 's_snowstorm');
		$this->conf['snowFlakeMinSize'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'snowFlakeMinSize', 's_snowstorm');
		$this->conf['snowFlakeMaxSize'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'snowFlakeMaxSize', 's_snowstorm');
        $this->conf['snowTimeForNewFlake'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'snowTimeForNewFlake', 's_snowstorm');

		//rebuild image-map
		$imageMapArr = t3lib_div::xml2tree($this->conf['imageMap']);
		$imageMapAreaArr = $imageMapArr['map']['0']['ch']['area'];
		
		$map = '<map name="' . $this->conf['usemap'] . '" id="' . $this->conf['usemap'] . '">';
		$i=1;
		foreach($imageMapAreaArr as $area){
			$areaData = $area['attrs'];
			//create typolinks for area href
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
			$user =  $GLOBALS['TSFE']->fe_user->user['username'];

			$this->addJqueryLibrary();
			
			if($this->conf['usesnow']){
				$javascript .= '<script src="' . t3lib_extMgm::siteRelPath($this->extKey) . 'res/js/snow/jquery.snow.js"></script>';
                $javascript .= '<script src="' . t3lib_extMgm::siteRelPath($this->extKey) . 'res/js/jquery/jquery.cookie.js"></script>';
				$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] .= $javascript;
			}

			$js = '
				<script type="text/javascript">

				function clearVariables(){
					document.getElementById(\'dialog\').style.top = "";
					document.getElementById(\'dialog\').style.left = "";
					document.getElementById(\'dialogheader\').innerHTML = "";
					document.getElementById(\'dialogcontent\').innerHTML = "";
				}';
			if($this->conf['usesnow']){
				$js .= '
					var snowFlakeColor = \'' . $this->conf['snowFlakeColor'] . '\';
					var flakeMinSize = ' . $this->conf['snowFlakeMinSize'] . ';
					var flakeMaxSize = ' . $this->conf['snowFlakeMaxSize'] . ';
                    var timeForNewFlake = ' . $this->conf['snowTimeForNewFlake'] . ';
                    ';
			}
			
			$js .= '
				$(document).ready(function(){';
                
            if($this->conf['usesnow']){
                $js .= '
                    $.fn.snow({ 
                        minSize: flakeMinSize, 
                        maxSize: flakeMaxSize, 
                        newOn: timeForNewFlake, 
                        flakeColor: snowFlakeColor
                    });';
            }

			$js .= '		$(\'<div id="boxes"><div id="dialog" class="window" style="width: ' . $this->conf['layerWidth'] . 'px;height:' . $this->conf['layerHeight'] . 'px;"><div id="dialogheader"></div><div id="dialogcontent"></div></div><div id="mask"></div></div>\').appendTo(\'body\');
							
					$(\'area\').click(function(e){
						e.preventDefault();
						var id = $(this).attr(\'id\');
						var username = \'' . $user . '\';';
						
            if($this->conf['usesnow']){
                $js .= '            
                        $.fn.snow({ 
                            minSize: flakeMinSize, 
                            maxSize: flakeMaxSize, 
                            newOn: timeForNewFlake, 
                            flakeColor: snowFlakeColor,
                            appendTo: \'#mask\'
                        });';
            }

            $js .= '            $(\'#dialogcontent\').append(\'<div id="ajax-loader"><img src="' . t3lib_extMgm::siteRelPath($this->extKey) . 'res/img/ajax-loader.gif" /></div>\');

						var winH = $(window).height();
						var winW = $(window).width();
						$(\'#dialog\').css(\'top\',  winH/2-$(\'#dialog\').height()/2);
						$(\'#dialog\').css(\'left\', winW/2-$(\'#dialog\').width()/2);
						$(\'#dialog\').css(\'width\', ' . $this->conf['layerWidth'] . ');
						$(\'#dialog\').css(\'min-height\', ' . $this->conf['layerHeight'] . ');
						$(\'#dialog\').css(\'height\', \'auto\');

						var maskHeight = $(document).height();
						var maskWidth = $(window).width();
						$(\'#mask\').css({\'width\':maskWidth,\'height\':maskHeight});

						$(\'#mask\').fadeIn(' . $this->conf['modalFadeInTime'] . ');
						$(\'#mask\').fadeTo("slow",0.8);
						$(\'#dialog\').fadeIn(' . $this->conf['dialogFadeInTime'] . ');

						$.ajax({
							url: \'?eID=adventcalender\',
							type: \'GET\',
							data: \'pageID=\' + id + \'&user=\' + username,
							dataType: \'json\',
							success: function(result) {
								$(\'#ajax-loader\').hide();
								$(\'#dialogheader\').html(\'<h2>\' + result.pageTitle + \'</h2><div id="dialogclose"><img src="' . t3lib_extMgm::siteRelPath($this->extKey) . 'res/img/bt_close.gif" width="25" height="25" alt="schliessen..."</div>\');
								$(\'#dialogcontent\').html(result.code);
								if($(document).height() < $(\'#dialog\').height()){
									maskHeight = $(\'#dialog\').height(); 
								} else {
									maskHeight = $(document).height();
								}
								$(\'#mask\').css({\'width\':maskWidth,\'height\':maskHeight});
							}
						});
					});

					//if mask is clicked
					$(\'#mask\').click(function () {';
            
            if($this->conf['usesnow']){
                $js .= '
                        $.fn.stopsnow(\'#mask\');
                        $.fn.stopsnow(\'body\');';
            }
   
             $js .= '           $(this).fadeOut(' . $this->conf['modalDialogFadeOutTime'] . ');
						$(\'.window\').fadeOut(' . $this->conf['modalDialogFadeOutTime'] . ');
						window.setTimeout(\'clearVariables()\',' . 500 . ');
					});

					//if close button is clicked
					$(\'#dialogclose\').live(\'click\', function(){';
             
             if($this->conf['usesnow']){
                 $js .= '
                        $.fn.stopsnow(\'#mask\');
                        $.fn.stopsnow(\'body\');';
             }
             $js .= ' $(\'#mask, .window\').fadeOut(' . $this->conf['modalDialogFadeOutTime'] . ');
						window.setTimeout(\'clearVariables()\',' . 500 . ');
					});
				});
				</script>
			';
			
			$css = '<link rel="stylesheet" type="text/css" href="' . t3lib_extMgm::siteRelPath($this->extKey) . 'res/css/ajax.css" />';
			$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] .= $css;
		}
		
		$content='
			' . $js . '
			<img src="' . $this->conf['image'] . '" alt="' . $this->conf['altText'] . '" width="' . $this->conf['imageWidth'] . '" height="' . $this->conf['imageHeight'] . '" border="0" usemap="#' . $this->conf['usemap'] . '" title="' . $this->conf['altText'] . '" />
			' . $map . '
		';
	
		return $this->pi_wrapInBaseClass($content);
	}
	
	/**
	 * Adds the jquery library
	 *
	 * @return			The correct header script part for including the jquery library - if necessary
	 */
	function addJqueryLibrary(){
		// checks if t3jquery is loaded
		if (t3lib_extMgm::isLoaded('t3jquery')) {
			require_once(t3lib_extMgm::extPath('t3jquery').'class.tx_t3jquery.php');
		}
		// if t3jquery is loaded and the custom Library had been created
		if (T3JQUERY === true) {
			tx_t3jquery::addJqJS();
		} else {
			// if none of the previous is true, you need to include your own library
			// just as an example in this way
			$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] .= '<script language="JavaScript" src="' . t3lib_extMgm::extRelPath($this->extKey) . 'res/js/jquery/jquery-1.5.1.min.js"></script>';
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_adventcalender/pi1/class.tx_jheadventcalender_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_adventcalender/pi1/class.tx_jheadventcalender_pi1.php']);
}

?>