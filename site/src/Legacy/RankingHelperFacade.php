<?php
namespace Diddipoeler\Component\SportsManagement\Site\Legacy;

\defined('_JEXEC') or die;

/**
 * Backward-compatible public name for the ranking helper bridge.
 *
 * This file intentionally contains no class declaration. On upgraded Joomla
 * installations it can be reached through both the component autoloader and a
 * legacy include path. Keeping it alias-only makes repeated or recursive loads
 * harmless while existing RankingHelperFacade references continue to work.
 */
if (!class_exists(RankingHelperFacade::class, false)) {
    class_alias(RankingLegacyHelper::class, RankingHelperFacade::class);
}
