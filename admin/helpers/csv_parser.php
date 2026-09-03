<?php
/**
 * Legacy parseCSV class-name compatibility bridge.
 *
 * The maintained PHP 8 implementation lives in csvhelper.php as JSMparseCSV.
 *
 * @version    0.4.3 beta (PHP 8 compatibility bridge)
 * @author     Jim Myhrberg (jim@zydev.info)
 * @copyright  Copyright (c) 2007 Jim Myhrberg
 * @license    MIT License; see the original parseCSV notice in csvhelper.php
 */

defined('_JEXEC') or die('Restricted access');

require_once __DIR__ . '/csvhelper.php';

if (!class_exists('parseCSV', false)) {
    class parseCSV extends JSMparseCSV
    {
    }
}
