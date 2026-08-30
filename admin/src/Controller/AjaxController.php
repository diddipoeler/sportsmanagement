<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/** Native read-only JSON controller for administrator option endpoints. */
final class AjaxController extends BaseController
{
    public function predictionid(): void
    {
        $input = $this->getApplication()->getInput();
        $this->respondModel('getPredictionId', [
            $input->getInt('cfg_which_database'),
            $input->getBool('required'),
            $input->getBool('slug'),
        ]);
    }

    public function predictiongroups(): void
    {
        $input = $this->getApplication()->getInput();
        $this->respondModel('getPredictionGroups', [
            $input->getInt('prediction_id'),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    public function predictionpj(): void
    {
        $input = $this->getApplication()->getInput();
        $this->respondModel('getPredictionPj', [
            $input->getInt('prediction_id'),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    public function locationzipcodeoptions(): void
    {
        $input = $this->getApplication()->getInput();
        $this->respondModel('getlocationzipcodeoptions', [
            $input->getString('zipcode'),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
            $input->getCmd('country'),
        ]);
    }

    public function countryleagueoptions(): void
    {
        $input = $this->getApplication()->getInput();
        $this->respondModel('getcountryleagueoptions', [
            $input->getCmd('search_nation'),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    public function countryzipcodeoptions(): void
    {
        $input = $this->getApplication()->getInput();
        $this->respondModel('getcountryzipcodeoptions', [
            $input->getCmd('country'),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    public function personcontactid(): void
    {
        $input = $this->getApplication()->getInput();
        $this->respondModel('getpersoncontactid', [
            $input->getInt('show_user_profile'),
            $input->getBool('required'),
        ]);
    }

    public function projects(): void
    {
        $input = $this->getApplication()->getInput();
        $this->respondModel('getProjects', [
            $input->get('s', 0, 'raw'),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    public function seasons(): void
    {
        $input = $this->getApplication()->getInput();
        $this->respondModel('getseasons', [
            $input->getInt('cfg_which_database'),
            $input->getBool('required'),
            $input->getBool('slug'),
        ]);
    }

    public function personlistoptions(): void
    {
        $input = $this->getApplication()->getInput();
        $this->respondModel('getpersonlistoptions', [
            $input->getInt('person_art'),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    public function personlistoptionsprojectteam(): void
    {
        $input = $this->getApplication()->getInput();
        $this->respondModel('getpersonlistoptionsprojectteam', [
            $input->getInt('person_art'),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    public function personpositionoptions(): void
    {
        $input = $this->getApplication()->getInput();
        $this->respondModel('getpersonpositionoptions', [
            $input->getInt('sports_type_id'),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    public function personagegroupoptions(): void
    {
        $input = $this->getApplication()->getInput();
        $this->respondModel('getpersonagegroupoptions', [
            $input->getInt('sports_type_id'),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
            $input->getInt('project'),
            $input->getCmd('country'),
        ]);
    }

    public function predictionmembersoptions(): void
    {
        $input = $this->getApplication()->getInput();
        $this->respondModel('getpredictionmembersoptions', [
            $input->getInt('prediction_id'),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    public function projectdivisionsoptions(): void
    {
        $this->respondProjectOption('getProjectDivisionsOptions');
    }

    public function projecteventsoptions(): void
    {
        $this->respondProjectOption('getProjectEventsOptions');
    }

    public function projectteamsbydivisionoptions(): void
    {
        $input = $this->getApplication()->getInput();
        $this->respondModel('getProjectTeamsByDivisionOptions', [
            $input->get('p', 0, 'raw'),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
            $input->getInt('division'),
        ]);
    }

    public function projectsbysportstypesoptions(): void
    {
        $input = $this->getApplication()->getInput();
        $this->respondModel('getProjectsBySportsTypesOptions', [
            $input->getInt('sportstype'),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    public function agegroupsbysportstypesoptions(): void
    {
        $input = $this->getApplication()->getInput();
        $this->respondModel('getAgeGroupsBySportsTypesOptions', [
            $input->getInt('sportstype'),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    public function projectsbycluboptions(): void
    {
        $input = $this->getApplication()->getInput();
        $this->respondModel('getProjectsByClubOptions', [
            $input->getInt('cid'),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    public function projectteamoptions(): void
    {
        $input = $this->getApplication()->getInput();
        $this->respondModel('getProjectTeamOptions', [
            $input->get('p', 0, 'raw'),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
            $input->get('club_id', null, 'raw'),
        ]);
    }

    public function projectteamsptidoptions(): void
    {
        $this->respondProjectOption('getProjectTeamPtidOptions');
    }

    public function projectplayeroptions(): void
    {
        $this->respondProjectOption('getProjectPlayerOptions');
    }

    public function projectstaffoptions(): void
    {
        $this->respondProjectOption('getProjectStaffOptions');
    }

    public function projectcluboptions(): void
    {
        $this->respondProjectOption('getProjectClubOptions');
    }

    public function projectstatsoptions(): void
    {
        $this->respondProjectOption('getProjectStatsOptions');
    }

    public function matchesoptions(): void
    {
        $this->respondProjectOption('getMatchesOptions');
    }

    public function refereesoptions(): void
    {
        $this->respondProjectOption('getRefereesOptions');
    }

    public function projectroundoptions(): void
    {
        $input = $this->getApplication()->getInput();
        $this->respondModel('getProjectRoundOptions', [
            $input->get('p', 0, 'raw'),
            $input->getBool('required'),
            $input->getBool('slug'),
            'ASC',
            null,
            $input->getBool('dbase'),
        ]);
    }

    public function projecttreenodeoptions(): void
    {
        $this->respondProjectOption('getProjectTreenodeOptions');
    }

    public function sportstypesoptions(): void
    {
        $input = $this->getApplication()->getInput();
        $this->respondModel('getsportstypes', [
            $input->getInt('cfg_which_database'),
            $input->getBool('required'),
            $input->getBool('slug'),
        ]);
    }

    private function respondProjectOption(string $method): void
    {
        $input = $this->getApplication()->getInput();
        $this->respondModel($method, [
            $input->get('p', 0, 'raw'),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    private function respondModel(string $method, array $arguments): void
    {
        $model = $this->getModel('Ajax', 'Administrator', ['ignore_request' => true]);
        $result = [];

        if ($model !== false && is_callable([$model, $method])) {
            $result = $model->{$method}(...$arguments);
        }

        $app = $this->getApplication();
        $app->getDocument()->setMimeEncoding('application/json');
        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $app->close();
    }
}
