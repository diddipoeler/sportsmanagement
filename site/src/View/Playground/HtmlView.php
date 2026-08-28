<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Playground;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ExtendedFormHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\PlaygroundKmlHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\PlaygroundModel;
use Diddipoeler\Component\SportsManagement\Site\Model\TeamsModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** Native Joomla 5/6 frontend view for playground details. */
final class HtmlView extends SportsManagementProjectHtmlView
{
    public ?object $playground = null;
    public string $address_string = '';
    public array $teams = [];
    public array $playgroundnotic = [];
    public array $games = [];
    public array $gamesteams = [];
    public array $playedgames = [];
    public array $playedgamesteams = [];
    public array $mapconfig = [];
    public Form|false $extended = false;
    public string $kmlpath = '';
    public string $kmlfile = '';

    protected function requiresLegacyPresentationDependencies(): bool
    {
        return false;
    }

    protected function prepareView(): void
    {
        $model = $this->getModel();

        if (!$model instanceof PlaygroundModel) {
            throw new \RuntimeException('Playground view requires PlaygroundModel.', 500);
        }

        $factory = $this->getApplication()
            ->bootComponent('com_sportsmanagement')
            ->getMVCFactory();
        $teamsModel = $factory->createModel('Teams', 'Site', ['ignore_request' => true]);

        if (!$teamsModel instanceof TeamsModel) {
            throw new \RuntimeException('Playground view requires TeamsModel.', 500);
        }

        $playgroundId = max(0, $this->input->getInt('pgid', PlaygroundModel::$playgroundid));
        $projectId = max(0, $this->input->getInt('p', PlaygroundModel::$projectid));
        $this->playground = PlaygroundModel::getPlayground($playgroundId, true);

        if (!$this->playground) {
            return;
        }

        $playgroundId = (int) $this->playground->id;
        $this->address_string = $model->getAddressString($this->playground);
        $this->teams = $teamsModel->getTeamsByPlayground($playgroundId);
        $this->playgroundnotic = $model->getPlaygroundNotic($playgroundId);

        if (!empty($this->config['show_matches'])) {
            $this->games = $model->getNextGames(
                $projectId,
                $playgroundId,
                false,
                !empty($this->config['show_all_projects'])
            );
            $this->gamesteams = $teamsModel->getTeamsFromMatches($this->games);
        }

        if (!empty($this->config['show_played_matches'])) {
            $this->playedgames = $model->getNextGames(
                $projectId,
                $playgroundId,
                true,
                !empty($this->config['show_all_projects'])
            );
            $this->playedgamesteams = $teamsModel->getTeamsFromMatches($this->playedgames);
        }

        if (!empty($this->config['show_maps']) && !empty($this->config['use_which_map'])) {
            $this->mapconfig = $model->getTemplateConfig('map');
        }

        if (!empty($this->mapconfig['map_kmlfile'])) {
            $latitude = is_numeric($this->playground->latitude ?? null)
                ? (float) $this->playground->latitude
                : null;
            $longitude = is_numeric($this->playground->longitude ?? null)
                ? (float) $this->playground->longitude
                : null;

            if (PlaygroundKmlHelper::write(
                $playgroundId,
                $this->address_string,
                (string) ($this->playground->name ?? ''),
                $latitude,
                $longitude
            )) {
                $this->kmlfile = $playgroundId . '-playground.kml';
                $this->kmlpath = Uri::root() . 'tmp/' . $this->kmlfile;
            }
        }

        $this->extended = ExtendedFormHelper::load(
            (string) ($this->playground->extended ?? ''),
            'playground'
        );

        $name = trim((string) ($this->playground->name ?? ''));
        $this->headertitle = $name;

        $document = $this->getDocument();
        $pageTitle = Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_PAGE_TITLE');

        if ($name !== '') {
            $pageTitle .= ' - ' . $name;
        }

        $document->setTitle($pageTitle);
        $document->addCustomTag(
            '<meta property="og:title" content="'
            . htmlspecialchars($name, ENT_QUOTES, 'UTF-8')
            . '"/>'
        );
        $document->addCustomTag(
            '<meta property="og:street-address" content="'
            . htmlspecialchars($this->address_string, ENT_QUOTES, 'UTF-8')
            . '"/>'
        );

        $document->getWebAssetManager()->registerAndUseStyle(
            'com_sportsmanagement.playground',
            'components/com_sportsmanagement/assets/css/playground.css',
            ['version' => 'auto']
        );
    }
}
