<?php
/**
 * SportsManagement playground legacy view bridge.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\PlaygroundModel;
use Diddipoeler\Component\SportsManagement\Site\Model\TeamsModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class sportsmanagementViewPlayground extends sportsmanagementView
{
    public function init()
    {
        sportsmanagementModelProject::setProjectID(
            $this->jinput->getInt('p', 0),
            $this->jinput->getInt('cfg_which_database', 0)
        );

        $factory = Factory::getApplication()
            ->bootComponent('com_sportsmanagement')
            ->getMVCFactory();

        $teamsModel = $factory->createModel('Teams', 'Site', ['ignore_request' => true]);
        $playgroundModel = $this->model instanceof PlaygroundModel
            ? $this->model
            : $factory->createModel('Playground', 'Site', ['ignore_request' => true]);

        if (!$teamsModel instanceof TeamsModel || !$playgroundModel instanceof PlaygroundModel) {
            throw new RuntimeException('Unable to create SportsManagement playground view models.');
        }

        $playgroundId = $this->jinput->getInt('pgid', 0);
        $projectId = $this->jinput->getInt('p', 0);

        $this->playground = PlaygroundModel::getPlayground($playgroundId, true);

        if (!$this->playground) {
            $this->address_string = '';
            $this->teams = [];
            $this->playgroundnotic = [];
            $this->games = [];
            $this->gamesteams = [];
            $this->playedgames = [];
            $this->playedgamesteams = [];
            $this->mapconfig = [];
            return;
        }

        $this->address_string = $playgroundModel->getAddressString($this->playground);
        $this->teams = $teamsModel->getTeamsByPlayground((int) $this->playground->id);
        $this->playgroundnotic = $playgroundModel->getPlaygroundNotic((int) $this->playground->id);
        $this->mapconfig = [];

        if (!empty($this->config['show_matches'])) {
            $this->games = $playgroundModel->getNextGames(
                $projectId,
                (int) $this->playground->id,
                false,
                !empty($this->config['show_all_projects'])
            );
            $this->gamesteams = $teamsModel->getTeamsFromMatches($this->games);
        } else {
            $this->games = [];
            $this->gamesteams = [];
        }

        if (!empty($this->config['show_played_matches'])) {
            $this->playedgames = $playgroundModel->getNextGames(
                $projectId,
                (int) $this->playground->id,
                true,
                !empty($this->config['show_all_projects'])
            );
            $this->playedgamesteams = $teamsModel->getTeamsFromMatches($this->playedgames);
        } else {
            $this->playedgames = [];
            $this->playedgamesteams = [];
        }

        if (!empty($this->config['show_maps'])) {
            if (!empty($this->config['use_which_map'])) {
                $this->mapconfig = sportsmanagementModelProject::getTemplateConfig(
                    'map',
                    $this->jinput->getInt('cfg_which_database', 0)
                );
            }

            if (!empty($this->mapconfig['map_kmlfile'])) {
                $this->geo = new JSMsimpleGMapGeocoder();
                $this->geo->genkml3file(
                    (int) $this->playground->id,
                    $this->address_string,
                    'playground',
                    (string) ($this->playground->picture ?? ''),
                    (string) ($this->playground->name ?? ''),
                    $this->playground->latitude ?? null,
                    $this->playground->longitude ?? null
                );
            }
        }

        $this->extended = sportsmanagementHelper::getExtended(
            (string) ($this->playground->extended ?? ''),
            'playground'
        );

        $pageTitle = Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_PAGE_TITLE');
        if (!empty($this->playground->name)) {
            $pageTitle .= ' - ' . $this->playground->name;
        }

        $this->document->setTitle($pageTitle);
        $this->document->addCustomTag(
            '<meta property="og:title" content="'
            . htmlspecialchars((string) ($this->playground->name ?? ''), ENT_QUOTES, 'UTF-8')
            . '"/>'
        );
        $this->document->addCustomTag(
            '<meta property="og:street-address" content="'
            . htmlspecialchars($this->address_string, ENT_QUOTES, 'UTF-8')
            . '"/>'
        );

        $this->document
            ->getWebAssetManager()
            ->registerAndUseStyle(
                'com_sportsmanagement.playground',
                'components/' . $this->option . '/assets/css/' . $this->view . '.css',
                ['version' => 'auto']
            );

        $this->headertitle = (string) ($this->playground->name ?? '');
    }
}
