<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 frontend Results model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\ResultsAccessModel;
use Diddipoeler\Component\SportsManagement\Site\Model\ResultsDataModel;
use Diddipoeler\Component\SportsManagement\Site\Model\ResultsEditModel;
use Diddipoeler\Component\SportsManagement\Site\Model\ResultsModel;
use Diddipoeler\Component\SportsManagement\Site\Pagination\JSMSportsmanagementPagination;

if (!class_exists(ResultsModel::class)) {
    foreach ([
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/ResultsDataModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/ResultsAccessModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/ResultsEditModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }

    if (!class_exists(JSMSportsmanagementPagination::class)) {
        $paginationFile = JPATH_SITE . '/components/com_sportsmanagement/src/Pagination/JSMSportsmanagementPagination.php';

        if (is_file($paginationFile)) {
            require_once $paginationFile;
        }
    }

    $nativeModel = JPATH_SITE . '/components/com_sportsmanagement/src/Model/ResultsModel.php';

    if (is_file($nativeModel)) {
        require_once $nativeModel;
    }
}

if (!class_exists(ResultsModel::class)) {
    throw new \RuntimeException('SportsManagement native Results model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelResults', false)) {
    class_alias(ResultsModel::class, 'sportsmanagementModelResults');
}
