<?php
namespace Diddipoeler\Module\SportsManagementAjaxTopNavigationMenu\Site\Helper;

\defined('_JEXEC') or die;

/**
 * Temporary class-name bridge to the remaining legacy query/link helper.
 *
 * The native dispatcher injects application and database dependencies directly
 * into the compatibility helper, so no adapter implementation is required.
 */
if (!class_exists(__NAMESPACE__ . '\\NativeAjaxTopNavigationLegacyAdapter', false)) {
    class_alias(
        \modSportsmanagementAjaxTopNavigationMenuHelper::class,
        __NAMESPACE__ . '\\NativeAjaxTopNavigationLegacyAdapter'
    );
}
