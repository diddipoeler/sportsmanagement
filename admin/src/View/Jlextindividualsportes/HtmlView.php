<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextindividualsportes;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextindividualsportesModel;
use Diddipoeler\Component\SportsManagement\Administrator\Service\IndividualMatchPairingService;
use Diddipoeler\Component\SportsManagement\Administrator\Service\IndividualMatchViewService;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Uri\Uri;

/** Joomla 5/6 administrator view for individual-sport match rows. */
final class HtmlView extends BaseHtmlView
{
    public $state;
    public $pagination;
    public int $total = 0;
    public array $matches = [];
    public array $singlematches = [];
    public array $lists = [];
    public array $ProjectTeams = [];
    public array $getHomePlayer = [];
    public array $getAwayPlayer = [];
    public array $homeplayers = [];
    public array $awayplayers = [];
    public array $homeplayers_position = [];
    public array $awayplayers_position = [];
    public array $show_matches = [];
    public array $match_generated = [];
    public ?object $projectws = null;
    public ?object $roundws = null;
    public ?object $project = null;
    public string $sortDirection = 'ASC';
    public string $sortColumn = 'mc.id';
    public string $request_url = '';
    public string $view = 'jlextindividualsportes';
    public int $project_id = 0;
    public int $pid = 0;
    public int $id = 0;
    public int $match_id = 0;
    public int $rid = 0;
    public int $projectteam1_id = 0;
    public int $projectteam2_id = 0;
    public int $massadd = 0;
    public int $generate_matches = 0;
    public int $close = 0;
    public bool $debug = false;

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof JlextindividualsportesModel) {
            throw new \RuntimeException('JlextindividualsportesModel is required.');
        }

        $app = Factory::getApplication();
        $input = $app->getInput();
        $this->request_url = Uri::getInstance()->toString();
        $this->pid = $input->getInt('pid', (int) $app->getUserState('com_sportsmanagement.pid', 0));
        $this->project_id = $this->pid;
        $this->id = $input->getInt('id', 0);
        $this->match_id = $this->id;
        $this->rid = $input->getInt('rid', (int) $app->getUserState('com_sportsmanagement.rid', 0));
        $this->projectteam1_id = $input->getInt('team1', 0);
        $this->projectteam2_id = $input->getInt('team2', 0);
        $this->massadd = $input->getInt('massadd', 0);
        $this->close = $input->getInt('close', 0);
        $this->debug = (bool) $app->get('debug', false);

        $service = new IndividualMatchViewService($model->getSportsManagementDatabase());
        $this->projectws = $service->getProject($this->pid);
        $this->project = $this->projectws;

        if (!$this->projectws) {
            throw new \RuntimeException(Text::_('JGLOBAL_NO_MATCHING_RESULTS'), 404);
        }

        $seasonId = (int) ($this->projectws->season_id ?? 0);
        $app->setUserState('com_sportsmanagement.pid', $this->pid);
        $app->setUserState('com_sportsmanagement.rid', $this->rid);
        $app->setUserState('com_sportsmanagement.season_id', $seasonId);
        $this->roundws = $service->getRound($this->rid)
            ?: (object) ['id' => 0, 'project_id' => $this->pid, 'name' => '', 'round_date_first' => ''];

        $layout = preg_replace('/_(3|4)$/', '', (string) $this->getLayout());

        if ($layout === 'generate') {
            $this->prepareGenerate($service, $seasonId);
        } else {
            $this->prepareDefault($model, $service, $app->getIdentity()->id);
        }

        parent::display($tpl);
    }

    private function prepareDefault(
        JlextindividualsportesModel $model,
        IndividualMatchViewService $service,
        int $userId
    ): void {
        $this->state = $this->get('State');
        $this->sortDirection = (string) $this->state->get('list.direction', 'ASC');
        $this->sortColumn = (string) $this->state->get('list.ordering', 'mc.id');

        $model->checkGames(
            $this->projectws,
            $this->match_id,
            $this->rid,
            $this->projectteam1_id,
            $this->projectteam2_id
        );

        if ((string) ($this->projectws->sports_type_name ?? '') === 'COM_SPORTSMANAGEMENT_ST_GOLF_BILLARD') {
            $modified = gmdate('Y-m-d H:i:s');
            $service->ensureGolfBillardSingles(
                $this->match_id,
                $this->rid,
                $this->projectteam1_id,
                $this->projectteam2_id,
                $userId,
                $modified
            );
        }

        $this->singlematches = $service->getSingleMatches($this->match_id);
        $this->matches = $this->get('Items') ?: [];
        $this->total = (int) $this->get('Total');
        $this->pagination = $this->get('Pagination');

        $homePlayers = $model->getPlayer($this->projectteam1_id, $this->pid);
        $awayPlayers = $model->getPlayer($this->projectteam2_id, $this->pid);
        $placeholder = HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM_PLAYER'));
        $this->lists = [
            'search_mode' => '',
            'homeplayer' => array_merge([$placeholder], $homePlayers),
            'awayplayer' => array_merge([$placeholder], $awayPlayers),
            'match_result_type' => [
                HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_RT')),
                HTMLHelper::_('select.option', 1, Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_OT')),
                HTMLHelper::_('select.option', 2, Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_SO')),
            ],
        ];
        $this->ProjectTeams = $model->getProjectTeams($this->pid) ?: [];
        $this->getHomePlayer = $homePlayers ?: [$this->temporaryPlayer()];
        $this->getAwayPlayer = $awayPlayers ?: [$this->temporaryPlayer()];
        $this->setLayout('default');
    }

    private function prepareGenerate(IndividualMatchViewService $service, int $seasonId): void
    {
        $this->homeplayers = $service->getProjectTeamPlayers($seasonId, $this->projectteam1_id);
        $this->awayplayers = $service->getProjectTeamPlayers($seasonId, $this->projectteam2_id);
        $homeRoster = $service->getMatchRosterPlayers($seasonId, $this->projectteam1_id, $this->match_id);
        $awayRoster = $service->getMatchRosterPlayers($seasonId, $this->projectteam2_id, $this->match_id);
        $this->homeplayers_position = $this->positionMap($homeRoster);
        $this->awayplayers_position = $this->positionMap($awayRoster);
        $this->generate_matches = count($homeRoster);

        $pairing = new IndividualMatchPairingService();
        $this->match_generated = $pairing->patterns();
        $this->show_matches = $pairing->build(
            (int) ($this->projectws->match_generated ?? 0),
            $this->homeplayers_position,
            $this->awayplayers_position,
            count($homeRoster),
            count($awayRoster)
        );

        if (!$this->show_matches && ($homeRoster || $awayRoster)) {
            Log::add(
                sprintf('No individual-match pairing card for %d home and %d away players.', count($homeRoster), count($awayRoster)),
                Log::WARNING,
                'jsmerror'
            );
        }

        $this->setLayout('default_generate');
    }

    /** @return array<string,int> */
    private function positionMap(array $rows): array
    {
        $positions = [];

        foreach ($rows as $row) {
            $name = trim((string) ($row->project_position_name ?? ''));
            $playerId = (int) ($row->teamplayer_id ?? 0);
            if ($name !== '' && $playerId > 0) {
                $positions[$name] = $playerId;
            }
        }

        return $positions;
    }

    private function temporaryPlayer(): object
    {
        return (object) ['value' => 0, 'text' => 'TempPlayer'];
    }
}
