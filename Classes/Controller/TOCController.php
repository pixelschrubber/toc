<?php

namespace ONEANDONE\Toc\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Extbase\Annotation as Extbase;


/**
 * Provides functionality to show a table of contents based on headlines in the current document
 *
 * @author Ulf Mayer
 */
class TOCController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

  protected $objectManager;
  
  private $extensionSettings;
  
  /**
   * tocRepository
   *
   * @Extbase\Inject
   * @var \ONEANDONE\Toc\Domain\Repository\TOCRepository
   */
  protected $tocRepository = NULL;   

  public function __construct() {
    $settings = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('toc');

    $extensionSettings = is_array($settings) ? $settings : array();
    $this->extensionSettings = $extensionSettings;
  }
    
  /**
   * action show
   * 
   */
  public function showAction() {
    $toc = $this->tocRepository;  
    if (!empty($toc)) {
      /* Pass extension settings to repository */
      $toc = $toc->find($this->extensionSettings);
      if (!empty($toc)) {
        $this->view->assignMultiple(Array(
            'toc' => $toc,
            'settings' => $this->extensionSettings
        ));
        $content = $this->view->render();
        return $content;
      }
    }    
  }
}
