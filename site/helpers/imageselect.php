<?php
/**
 * SportsManagement legacy image-select compatibility bridge.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\ImageSelectHelper;

if (!class_exists(ImageSelectHelper::class)) {
    $nativeHelper = JPATH_SITE . '/components/com_sportsmanagement/src/Helper/ImageSelectHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (!class_exists(ImageSelectHelper::class)) {
    throw new \RuntimeException('SportsManagement native image-select helper could not be loaded.', 500);
}

if (!class_exists('ImageSelectSM', false)) {
    class_alias(ImageSelectHelper::class, 'ImageSelectSM');
}
