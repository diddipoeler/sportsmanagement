<?php
/**
 * Joomla 5/6 bridge for the remaining legacy project XML graph.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use ReflectionMethod;
use RuntimeException;

/**
 * Initialise the historical importer without steps 16+ and then continue only
 * with the still-unmigrated project graph (21-35) using native conversion maps.
 */
final class LegacyProjectContinuationService
{
    /** @var array<int, string> */
    private const LEGACY_STEPS = [
        21 => '_importTeamPlayer',
        22 => '_importTeamStaff',
        23 => '_importTeamTraining',
        24 => '_importRounds',
        25 => '_importMatches',
        26 => '_importMatchPlayer',
        27 => '_importMatchStaff',
        28 => '_importMatchReferee',
        29 => '_importMatchEvent',
        30 => '_importPositionStatistic',
        31 => '_importMatchStaffStatistic',
        32 => '_importMatchStatistic',
        33 => '_importTreetos',
        34 => '_importTreetonode',
        35 => '_importTreetomatch',
    ];

    /**
     * @param array<string, mixed> $post
     * @param array<string, array<int, int>> $maps
     * @param array<string, string> $messages
     *
     * @return array<string, mixed>|false
     */
    public function continue(
        object $legacy,
        array $post,
        array $maps,
        array $messages,
        string $targetStep
    ): array|false {
        $params = ComponentHelper::getParams('com_sportsmanagement');
        $configuredStep = (string) $params->get('backend_xmlimport_step', 1);

        // Let the legacy importer initialise its form-derived state and rebuild
        // the already-safe conversion maps through step 15. Steps 16-19 have
        // been written natively and must not run a second time.
        $params->set('backend_xmlimport_step', '15');

        try {
            $result = $legacy->importData($post);
        } finally {
            $params->set('backend_xmlimport_step', $configuredStep);
        }

        if ($result === false) {
            return false;
        }

        foreach ($maps as $property => $map) {
            $legacy->{$property} = $map;
        }

        if (!isset($legacy->_success_text) || !is_array($legacy->_success_text)) {
            $legacy->_success_text = [];
        }

        foreach ($messages as $key => $message) {
            $legacy->_success_text[$key] = $message;
        }

        foreach (self::LEGACY_STEPS as $step => $methodName) {
            if (!version_compare($targetStep, (string) $step, 'ge')) {
                continue;
            }

            if ($this->invokeLegacyMethod($legacy, $methodName) === false) {
                return is_array($legacy->_success_text) ? $legacy->_success_text : [];
            }
        }

        if (version_compare($targetStep, '21', 'ge')) {
            // importData() already ran this finalisation once at the temporary
            // step 15. Run it again after the continued graph so rows created by
            // steps 21-35 receive the same historical post-processing.
            $this->invokeLegacyMethod($legacy, 'setNewDataStructur');

            $model = BaseDatabaseModel::getInstance('databasetool', 'sportsmanagementModel');

            if ($model && method_exists($model, 'setNewPicturePath')) {
                $model->setNewPicturePath();
            }
        }

        return is_array($legacy->_success_text) ? $legacy->_success_text : [];
    }

    private function invokeLegacyMethod(object $legacy, string $methodName): mixed
    {
        try {
            $method = new ReflectionMethod($legacy, $methodName);
            $method->setAccessible(true);

            return $method->invoke($legacy);
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'Unable to continue legacy XML project step ' . $methodName . ': ' . $e->getMessage(),
                500,
                $e
            );
        }
    }
}
