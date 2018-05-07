<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi/class.tx_toc.php', '', 'list-type', 1);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'ONEANDONE.' . $_EXTKEY,
	'TOC',
	array(
		'TOC' => 'show',
	),
	// non-cacheable actions
	array(
	)
);
