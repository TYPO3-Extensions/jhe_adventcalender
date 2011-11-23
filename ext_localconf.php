<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_jheadventcalender_pi1.php', '_pi1', 'list_type', 0);

$TYPO3_CONF_VARS['FE']['eID_include']['adventcalender'] = 'EXT:jhe_adventcalender/ajax/class.ajax_adventcalender.php';
?>