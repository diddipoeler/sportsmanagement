<?php
namespace Diddipoeler\Component\SportsManagement\Site\Legacy;

\defined('_JEXEC') or die;

/**
 * Backward-compatible loader for the ranking helper facade name.
 *
 * This file intentionally contains neither a class declaration nor class_alias().
 * On upgraded Joomla installations it can be reached recursively through both
 * the component autoloader and legacy include paths. Loading the implementation
 * from its separate PSR-4 file lets that file register the compatibility alias
 * only after its own class declaration has completed.
 */
if (!class_exists(RankingHelperFacade::class, false)) {
    class_exists(RankingLegacyHelper::class);
}
