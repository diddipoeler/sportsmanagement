<?php
/**
 * Zabuto layout compatibility alias.
 *
 * The historical Zabuto template only loaded third-party assets and did not render
 * SportsManagement events. Use the native Joomla 5/6 calendar layout instead.
 */
\defined('_JEXEC') or die;

require __DIR__ . '/default_jsm.php';
