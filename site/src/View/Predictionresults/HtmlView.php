<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Predictionresults;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\PredictionresultsModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementPredictionHtmlView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pagination\Pagination;

final class HtmlView extends SportsManagementPredictionHtmlView
{
    public ?object $resultsProject = null;
    public array $items = [];
    public array $matches = [];
    public array $projectOptions = [];
    public array $roundOptions = [];
    public array $groupOptions = [];
    public ?Pagination $pagination = null;
    public int $currentPredictionMemberID = 0;
    public int $selectedRoundID = 0;
    public int $limit = 0;
    public int $limitstart = 0;
    public int $ausgabestart = 0;
    public int $ausgabeende = 0;
    private PredictionresultsModel $resultsModel;

    public function __construct($config = [])
    {
        $config['template_path'] = JPATH_SITE . '/components/com_sportsmanagement/views/predictionresults/tmpl';
        parent::__construct($config);
    }

    protected function prepareView(): void
    {
        $model = $this->getModel();
        if (!$model instanceof PredictionresultsModel) {
            throw new \RuntimeException('Prediction results view requires PredictionresultsModel.', 500);
        }
        $this->resultsModel = $model;

        $this->config = array_merge($model->getPredictionTemplateConfig('predictionentry'), $this->config);
        $this->config += [
            'table_class' => 'table',
            'table_class_responsive' => 'table-responsive',
            'seperator' => ':',
            'show_all_user' => 0,
            'show_user_icon' => 0,
            'show_user_icon_width' => 50,
            'show_pred_group' => 0,
            'show_points' => 1,
            'show_average_points' => 1,
            'show_team_names' => 1,
            'show_logo_small_overview' => 'logo_small',
            'club_logo_height' => 20,
            'show_help' => 0,
            'show_scoring' => 1,
        ];

        $this->resultsProject = $model->getResultsProject();
        if (!$this->predictionGame || !$this->resultsProject) {
            $this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_PRED_PREDICTION_NOT_EXISTING'), 'error');
            return;
        }

        $this->projectID = $model->getProjectId();
        $this->selectedRoundID = $model->getSelectedRoundId($this->config);
        $this->roundID = $this->selectedRoundID;
        $this->currentPredictionMemberID = $model->getCurrentPredictionMemberNumericId();
        $this->projectOptions = $model->getProjectOptions();
        $this->roundOptions = $model->getRoundOptions($this->config);
        $this->groupOptions = $model->getPredictionGroupList();

        $data = $model->getResultsData($this->config, $this->configavatar);
        $this->items = $data['rows'];
        $this->matches = $data['matches'];
        $this->pagination = $model->getPagination((int) $data['total']);
        $this->limit = $model->getLimit();
        $this->limitstart = $model->getLimitStart();
        $this->ausgabestart = $this->limitstart + 1;
        $this->ausgabeende = $this->limitstart + $this->limit;
        $this->headertitle = Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_TITLE');
        $this->getDocument()->setTitle($this->headertitle);

        if (!class_exists('JSMCountries', false)) {
            require_once JPATH_SITE . '/components/com_sportsmanagement/helpers/countries.php';
        }
    }

    public function scoreExample(int $home, int $away, int $tipp, int $tippHome, int $tippAway, bool $joker = false): int
    {
        return $this->resultsModel->scoreExample($home, $away, $tipp, $tippHome, $tippAway, $joker);
    }

    public function memberAvatar(object $member): string
    {
        $name = (string) (($member->aliasName ?? '') ?: ($member->name ?? ''));
        $isOwnProfile = (int) ($member->pmID ?? 0) === $this->currentPredictionMemberID;
        $picture = (string) ($member->avatar ?? '');
        if ((!$isOwnProfile && empty($member->show_profile)) || $picture === '' || !$this->localImageExists($picture)) {
            $picture = 'images/com_sportsmanagement/database/placeholders/placeholder_150_2.png';
        }

        return HTMLHelper::image(
            $picture,
            $name,
            [
                'title' => $name,
                'width' => max(1, (int) ($this->config['show_user_icon_width'] ?? 50)),
            ]
        );
    }

    public function memberName(array $row): string
    {
        $member = $row['member'];
        $name = (string) (($member->aliasName ?? '') ?: ($member->name ?? ''));
        $canOpenProfile = !empty($member->show_profile) || (int) ($row['pmID'] ?? 0) === $this->currentPredictionMemberID;

        if (!empty($this->config['link_name_to']) && $canOpenProfile && class_exists('JSMPredictionHelperRoute')) {
            $link = \JSMPredictionHelperRoute::getPredictionMemberRoute($this->predictionGameID, (int) $row['pmID']);
            return HTMLHelper::link($link, htmlspecialchars($name, ENT_QUOTES, 'UTF-8'));
        }

        return htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    }

    public function teamVisual(object $match, string $side): string
    {
        $prefix = $side === 'away' ? 'away' : 'home';
        $name = (string) ($match->{$prefix . 'Name'} ?? '');
        $mode = (string) ($this->config['show_logo_small_overview'] ?? '');

        if ($mode === 'country_flag') {
            $country = (string) ($match->{$prefix . 'Country'} ?? '');
            if ($country !== '' && class_exists('JSMCountries')) {
                return (string) \JSMCountries::getCountryFlag($country);
            }
            return htmlspecialchars($country, ENT_QUOTES, 'UTF-8');
        }

        if (in_array($mode, ['logo_small', 'logo_middle', 'logo_big'], true)) {
            $picture = (string) ($match->{$prefix . 'Logo'} ?? '');
            if ($picture === '' || !$this->localImageExists($picture)) {
                $picture = 'images/com_sportsmanagement/database/placeholders/placeholder_small.gif';
            }
            return HTMLHelper::image(
                $picture,
                $name,
                [
                    'title' => $name,
                    'height' => max(1, (int) ($this->config['club_logo_height'] ?? 20)),
                ]
            );
        }

        return '';
    }

    private function localImageExists(string $picture): bool
    {
        if (preg_match('#^https?://#i', $picture)) {
            return true;
        }
        return is_file(JPATH_ROOT . '/' . ltrim($picture, '/'));
    }
}
