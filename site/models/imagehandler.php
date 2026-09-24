<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 frontend Imagehandler model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\ImagehandlerModel;

if (!class_exists(ImagehandlerModel::class)) {
    $nativeModel = JPATH_SITE . '/components/com_sportsmanagement/src/Model/ImagehandlerModel.php';

    if (is_file($nativeModel)) {
        require_once $nativeModel;
    }
}

if (!class_exists(ImagehandlerModel::class)) {
    throw new \RuntimeException('SportsManagement native Imagehandler model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelImagehandler', false)) {
    class_alias(ImagehandlerModel::class, 'sportsmanagementModelImagehandler');
}
