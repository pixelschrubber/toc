<?php

namespace ONEANDONE\Toc\Domain\Repository;

/* * *************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
 * ************************************************************* */

/**
 * The repository for Posts
 */
class TOCRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

    protected $tocContent = array();
    private $extensionSettings;

    /**
     * Find headlines in tt_content for current page
     * @return array
     */
    public function find($extensionSettings) {
        $this->extensionSettings = $extensionSettings;
        $pageid = intval($GLOBALS['TSFE']->id);
        $lang = intval($GLOBALS['TSFE']->sys_language_uid);
        //@todo: sort by sorting?
        $where = "pid='" . $pageid . "' AND sys_language_uid='" . $lang . "' AND deleted=0 AND hidden=0";
        $queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $res = $queryBuilder->select('uid','header','CType','header_layout','bodytext','sorting','hidden','deleted')
        ->from('tt_content')
        ->where($where)
        ->execute();
        if ($res->rowCount() > 0 && intval($this->extensionSettings['Show-Amount']) <= $res->rowCount()) {
            while ($row = $res->fetch()) {
                if ($row['header']) {
                    // @todo: 0 is headline 2 in my setup, is this standard? need to get this information from somewhere
                    $row['header_layout'] = $row['header_layout'] == 0 ? 2 : $row['header_layout'];
                    $this->tocContent[] = array('level' => $row['header_layout'], 'content' => $row['header'], 'anchor' => $row['uid'], 'urlhash' => $this->format_uri($row['header']));
                }

                // Exclude specific content types
                $excludeTypes = \explode(',', $this->extensionSettings['Exclude-Contenttypes']);
                if (!in_array($row['CType'],$excludeTypes)) {
                    if ($row['bodytext']) {
                        $dom = new \DOMDocument();
                        $dom->recover = true;
                        $dom->strictErrorChecking = false;
                        // HTML output could be dirty, avoid warnings
                        @$dom->loadHTML($row['bodytext']);
                        $headlines = array("h1", "h2", "h3", "h4", "h5", "h6");
                        foreach ($headlines as $tag) {
                            foreach ($dom->getElementsByTagName($tag) as $item) {
                                $this->tocContent[] = array('level' => (int) str_replace("h", "", $item->tagName), 'content' => utf8_decode($item->nodeValue), 'anchor' => $row['uid'], 'urlhash' => $this->format_uri(utf8_decode($item->nodeValue)));
                            }
                        }
                    }
                }
            }
        }

        $this->excludeHeadlines();
        $this->excludeFirstHeadlines();
        $this->enableHierarchy();

        return $this->tocContent;
    }

    protected function excludeHeadlines() {
        if ($this->extensionSettings['Exclude-Headlines'] != '') {
            $tocContentCleaned = array();
            $exclude = \explode(',', \str_replace("h", "", $this->extensionSettings['Exclude-Headlines']));
            foreach ($this->tocContent as $headline) {
                if (!in_array($headline['level'], $exclude)) {
                    $tocContentCleaned[] = $headline;
                }
            }
            unset($headline);
            $this->tocContent = $tocContentCleaned;
        }
    }

    protected function excludeFirstHeadlines()
    {
        if ($this->extensionSettings['Exclude-First']) {
            array_shift($this->tocContent);
        }
    }

    /*
     * Enable Hierarchy
     */

    protected function enableHierarchy()
    {
        if ($this->extensionSettings['Enable-Hierarchy']) {

            // Reindex to avoid starting with 0, otherwise recursion won't work
            $this->tocContent = array_combine(range(1, count($this->tocContent)), array_values($this->tocContent));

            foreach ($this->tocContent as $key => $value) {
                $this->tocContent[$key]['id'] = (int) $key;
                $this->tocContent[$key]['parent'] = 0;

                if(isset($this->tocContent[$key - 1])) {

                    // actual level is higher than prev level...
                    if($this->tocContent[$key]['level'] > $this->tocContent[$key - 1]['level']) {
                        $this->tocContent[$key]['parent'] = $this->tocContent[$key - 1]['id'];
                    }

                    // actual and prev have the same level...
                    elseif($this->tocContent[$key]['level'] == $this->tocContent[$key - 1]['level']) {
                        $this->tocContent[$key]['parent'] = $this->tocContent[$key - 1]['parent'];
                    }

                    // prev level is higher than actual level...
                    elseif($this->tocContent[$key]['level'] < $this->tocContent[$key - 1]['level']) {
                        $indexDifference = 1;
                        $twinFound = false;
                        do {
                            if($this->tocContent[$key]['level'] == $this->tocContent[$key - $indexDifference]['level']) {
                                $this->tocContent[$key]['parent'] = $this->tocContent[$key - $indexDifference]['parent'];

                                $twinFound = true;
                            }
                            $indexDifference++;
                        } while ($twinFound === false and isset($this->tocContent[$key - $indexDifference]));
                    }
                }
            }

            // Reorder for parents
            $new = array();
            foreach ($this->tocContent as $a) {
                $new[$a['parent']][] = $a;
            }
            $this->tocContent = $this->createTree($new, $new[0]);
        }
    }


    /*
     * Helper function to enable recursion
     */

    protected function createTree(&$list, $parent)
    {
        $tree = array();
        foreach ($parent as $k => $l) {
            if (isset($list[$l['id']])) {
                $l['toc'] = $this->createTree($list, $list[$l['id']]);
            }
            $tree[] = $l;
        }
        return $tree;
    }

    /*
     * Helper function to format headline into uri hash
     */
    protected function format_uri($string, $separator = '-')
    {
        $accents_regex = '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i';
        $special_cases = array('&' => 'and', "'" => '');
        $string = mb_strtolower(trim($string), 'UTF-8');
        $string = str_replace(array_keys($special_cases), array_values($special_cases), $string);
        $string = preg_replace($accents_regex, '$1', htmlentities($string, ENT_QUOTES, 'UTF-8'));
        $string = preg_replace("/[^a-z0-9]/u", "$separator", $string);
        $string = preg_replace("/[$separator]+/u", "$separator", $string);
        return $string;
    }

}
