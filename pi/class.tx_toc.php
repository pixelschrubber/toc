<?php

use \TYPO3\CMS\Frontend\Plugin\AbstractPlugin as AbstractPlugin;

class Tx_Toc extends AbstractPlugin {
    const EXT_KEY = 'toc';

    public function main() {
        if (defined('TYPO3_MODE') && TYPO3_MODE === 'FE') {
            $toccontroller = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('ONEANDONE\\Toc\\Controller\\TOCController');
            $toccontroller->showAction();
        }
    }
}