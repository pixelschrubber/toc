<?php
declare(strict_types=1);

defined('TYPO3') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'toc',
    'TOC',
    'Table of Contents'
);
