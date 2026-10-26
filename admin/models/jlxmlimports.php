<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 JLXMLImports model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlxmlimportsModel;

if (!class_exists(JlxmlimportsModel::class)) {
    $nativeModel = __DIR__ . '/../src/Model/JlxmlimportsModel.php';

    if (!is_file($nativeModel)) {
        throw new \RuntimeException('SportsManagement native Jlxmlimports model file is missing.', 500);
    }

    require_once $nativeModel;
}

if (!class_exists(JlxmlimportsModel::class)) {
    throw new \RuntimeException('SportsManagement native Jlxmlimports model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelJLXMLImports', false)) {
    class_alias(JlxmlimportsModel::class, 'sportsmanagementModelJLXMLImports');
}
