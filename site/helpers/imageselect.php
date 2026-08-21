<?php
/**
 * SportsManagement legacy image-select compatibility bridge.
 */
\defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\ImageSelectHelper;

if (!class_exists(ImageSelectHelper::class)) {
    $nativeHelper = JPATH_SITE . '/components/com_sportsmanagement/src/Helper/ImageSelectHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (class_exists(ImageSelectHelper::class) && !class_exists('ImageSelectSM', false)) {
    class_alias(ImageSelectHelper::class, 'ImageSelectSM');
}
