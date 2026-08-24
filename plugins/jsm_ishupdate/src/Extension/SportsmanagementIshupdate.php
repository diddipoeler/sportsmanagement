<?php
namespace Diddipoeler\Plugin\System\SportsmanagementIshupdate\Extension;

\defined('_JEXEC') or die;

use Diddipoeler\Plugin\System\SportsmanagementIshupdate\Service\InlineHockeyUpdateService;
use Joomla\CMS\Event\Application\AfterRouteEvent;
use Joomla\CMS\Event\Application\BeforeRenderEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;

/**
 * Joomla 5/6 system plugin that refreshes Inline Hockey match data when needed.
 */
final class SportsmanagementIshupdate extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    protected $autoloadLanguage = true;

    private int $projectId = 0;
    private int $matchesToUpdate = 0;
    private int $matchTimestamp = 0;

    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterRoute' => 'onAfterRoute',
            'onBeforeRender' => 'onBeforeRender',
        ];
    }

    public function onAfterRoute(AfterRouteEvent $event): void
    {
        $app = $this->getApplication();
        $input = $app->getInput();

        if (!$app->isClient('site') || $input->getCmd('option') !== 'com_sportsmanagement') {
            return;
        }

        $this->projectId = $input->getInt('p', 0);

        if ($this->projectId <= 0) {
            return;
        }

        $this->matchTimestamp = time() + (4 * 60 * 60);
        $this->matchesToUpdate = $this->countMatchesToUpdate();

        $app->enqueueMessage(
            sprintf('Es müssen [ <strong>%d</strong> ] Spiele aktualisiert werden !', $this->matchesToUpdate),
            'notice'
        );

        if ($this->matchesToUpdate > 0) {
            $this->updateInlineHockeyMatches();
        }
    }

    public function onBeforeRender(BeforeRenderEvent $event): void
    {
        $app = $this->getApplication();

        if (
            !$app->isClient('site')
            || $app->getInput()->getCmd('option') !== 'com_sportsmanagement'
            || $this->projectId <= 0
            || $this->matchesToUpdate <= 0
        ) {
            return;
        }

        $remaining = $this->countMatchesToUpdate();
        $updated = max(0, $this->matchesToUpdate - $remaining);

        $app->enqueueMessage(
            sprintf('Es wurden [ <strong>%d</strong> ] Spiele aktualisiert !', $updated),
            'notice'
        );
    }

    private function countMatchesToUpdate(): int
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id')
            )
            ->where($db->quoteName('p.id') . ' = :projectId')
            ->where($db->quoteName('m.team1_result') . ' IS NULL')
            ->where($db->quoteName('m.match_timestamp') . ' < :matchTimestamp')
            ->bind(':projectId', $this->projectId, ParameterType::INTEGER)
            ->bind(':matchTimestamp', $this->matchTimestamp, ParameterType::INTEGER);

        try {
            $db->setQuery($query);

            return (int) $db->loadResult();
        } catch (\Throwable $exception) {
            $this->getApplication()->enqueueMessage(
                __METHOD__ . ': ' . $exception->getMessage(),
                'error'
            );

            return 0;
        }
    }

    private function updateInlineHockeyMatches(): void
    {
        try {
            (new InlineHockeyUpdateService($this->getDatabase()))->updateProject($this->projectId);
        } catch (\Throwable $exception) {
            if ((int) $this->params->get('load_debug', 1) === 1) {
                $this->getApplication()->enqueueMessage(
                    'Inline-Hockey update failed: ' . $exception->getMessage(),
                    'warning'
                );
            }
        }
    }
}
