<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Editmatch;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\EditmatchEventViewDataService;
use Diddipoeler\Component\SportsManagement\Site\Service\EditmatchViewDataService;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;

trait EditmatchEventViewTrait
{
    public ?object $teams = null;
    public array $rosters = ['home' => [], 'away' => []];
    public array $matchevents = [];
    public array $matchcommentary = [];
    public int $default_name_format = 14;
    public string $default_name_dropdown_list_order = 'lastname';

    private function prepareEventLayout(EditmatchViewDataService $viewService): void
    {
        if (!$this->match) {
            return;
        }

        $eventService = $this->eventViewDataService();
        $matchId = (int) $this->match->id;
        $teams = $eventService->getMatchTeams($matchId);

        if (!$teams) {
            throw new \RuntimeException('SportsManagement match teams are unavailable.', 404);
        }

        $this->teams = $teams;
        $this->default_name_dropdown_list_order = (string) $this->params->get(
            'cfg_be_name_dropdown_list_order',
            'lastname'
        );
        $this->default_name_format = (int) $this->params->get('name_format', 14);

        $teamList = [
            HTMLHelper::_('select.option', (int) $teams->projectteam1_id, (string) $teams->team1),
            HTMLHelper::_('select.option', (int) $teams->projectteam2_id, (string) $teams->team2),
        ];
        $this->lists['teams'] = HTMLHelper::_(
            'select.genericlist',
            $teamList,
            'team_id',
            'onchange="updatePlayerSelect();" class="inputbox select-team"'
        );

        $events = $eventService->getEventsOptions($this->project_id);

        if ($events === []) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_EVENTS_POS'), Log::WARNING, 'jsmerror');
        }

        $this->lists['events'] = HTMLHelper::_(
            'select.genericlist',
            $events,
            'event_type_id',
            'class="inputbox select-event"'
        );

        $homeRoster = $viewService->getMatchPersons((int) $teams->projectteam1_id, $matchId);
        $awayRoster = $viewService->getMatchPersons((int) $teams->projectteam2_id, $matchId);
        $this->rosters = [
            'home' => $homeRoster,
            'away' => $awayRoster,
        ];

        $this->lists['homeroster'] = HTMLHelper::_(
            'select.genericlist',
            $this->personOptions($homeRoster),
            (string) $teams->projectteam1_id,
            'class="inputbox" size="1"',
            'value',
            'text'
        );
        $this->lists['awayroster'] = HTMLHelper::_(
            'select.genericlist',
            $this->personOptions($awayRoster),
            (string) $teams->projectteam2_id,
            'class="inputbox" size="1"',
            'value',
            'text'
        );

        $this->matchcommentary = $eventService->getMatchCommentary($matchId);
        $this->matchevents = $eventService->getMatchEvents($matchId);

        $document = $this->getDocument();
        $document->addScript(
            Uri::base() . 'administrator/components/com_sportsmanagement/assets/js/diddioeler.js'
        );
        $document->addScriptDeclaration(
            "\n"
            . "var baseajaxurl = '" . Uri::root() . "index.php?option=com_sportsmanagement';\n"
            . 'var matchid = ' . $matchId . ";\n"
            . 'var useeventtime = ' . $this->useeventtime . ";\n"
            . 'var doubleevents = ' . $this->doubleevents . ";\n"
            . 'var projecttime = ' . $this->eventsprojecttime . ";\n"
            . "var str_delete = '" . addslashes(Text::_('JACTION_DELETE')) . "';\n"
        );
    }

    /** @param array<int|string,object> $roster */
    private function personOptions(array $roster): array
    {
        $options = [];

        foreach ($roster as $player) {
            $name = \sportsmanagementHelper::formatName(
                null,
                (string) ($player->firstname ?? ''),
                (string) ($player->nickname ?? ''),
                (string) ($player->lastname ?? ''),
                $this->default_name_format
            );

            if ($this->default_name_dropdown_list_order === 'position') {
                $name = '(' . Text::_((string) ($player->positionname ?? '')) . ') - ' . $name;
            }

            $options[] = HTMLHelper::_(
                'select.option',
                (int) ($player->value ?? 0),
                $name
            );
        }

        return $options;
    }

    private function eventViewDataService(): EditmatchEventViewDataService
    {
        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
        $selector = $this->input->getInt(
            'cfg_which_database',
            (int) $this->app->getUserState('com_sportsmanagement.cfg_which_database', 0)
        );
        $selectedSportsDatabase = SportsManagementDatabaseResolver::resolve($joomlaDatabase, $selector);
        $componentSportsDatabase = SportsManagementDatabaseResolver::resolve($joomlaDatabase, 0);

        return new EditmatchEventViewDataService(
            $joomlaDatabase,
            $selectedSportsDatabase,
            $componentSportsDatabase
        );
    }
}
