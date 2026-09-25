<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 tournament bracket model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\TournamentbracketModel;

if (!class_exists(TournamentbracketModel::class)) {
    foreach ([
        JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/TournamentbracketModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(TournamentbracketModel::class)) {
    throw new \RuntimeException('SportsManagement native Tournamentbracket model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModeltournamentbracket', false)) {
    class_alias(TournamentbracketModel::class, 'sportsmanagementModeltournamentbracket');
}
