<?php
/**
 * Default layout compatibility wrapper.
 *
 * The Joomla 5/6 implementation lives in native.php so existing module
 * instances and template overrides selecting "default" remain functional.
 */
\defined('_JEXEC') or die;

require __DIR__ . '/native.php';
