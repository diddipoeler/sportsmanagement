<?php
/**
 * Native Joomla 5/6 route helper for SportsManagement prediction views.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

final class PredictionRouteHelper
{
    public static function member(
        int $predictionId,
        int $userId = 0,
        ?string $task = null,
        int $projectId = 0,
        int $groupId = 0,
        int $roundId = 0,
        int $database = 0
    ): string {
        $view = $task === 'edit' ? 'predictionuser' : 'predictionusers';
        $parameters = [
            'cfg_which_database' => $database,
            'prediction_id' => $predictionId,
            'pggroup' => $groupId,
            'pj' => $projectId,
            'r' => $roundId,
            'uid' => $userId,
        ];

        if ($task === 'edit') {
            $parameters['layout'] = 'edit';
        }

        return SiteRouteHelper::view($view, $parameters);
    }

    public static function entry(
        int $predictionId,
        int $userId = 0,
        int $projectId = 0,
        int $groupId = 0,
        int $roundId = 0,
        int $database = 0,
        array $extra = []
    ): string {
        $parameters = [
            'cfg_which_database' => $database,
            'prediction_id' => $predictionId,
            'pggroup' => $groupId,
            'pj' => $projectId,
            'r' => $roundId,
            'uid' => max(0, $userId),
        ] + $extra;

        return SiteRouteHelper::view('predictionentry', $parameters);
    }
}
