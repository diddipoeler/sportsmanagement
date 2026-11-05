<?php
/**
 * Native Joomla 5/6 frontend SportsManagement Referee view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Referee;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ExtendedDataHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter;
use Diddipoeler\Component\SportsManagement\Site\Model\RefereeModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;

final class HtmlView extends SportsManagementProjectHtmlView
{
    public ?object $person = null;
    public ?object $referee = null;
    public array $history = [];
    public array $games = [];
    public array $teams = [];
    public array $extended = [];
    public string $title = '';

    /**
     * Disable the historical presentation bootstrap for referee profiles.
     *
     * The native Joomla 5/6 referee view uses its namespaced model and
     * shared project view services, without the old global helper layer.
     *
     * @return bool False because legacy presentation is not required.
     */
    protected function usesLegacyPresentation(): bool
    {
        return false;
    }

    /**
     * Populate a referee's profile, history and optional match statistics.
     *
     * Requires the native RefereeModel, formats the referee's display name,
     * loads season history and optionally includes matches and team records.
     * Decodes any extended person fields and configures the page heading
     * and historical table classes for the existing template.
     *
     * @return void
     * @throws \RuntimeException If the native referee model is unavailable.
     */
    protected function prepareView(): void
    {
        $model = $this->getModel();

        if (!$model instanceof RefereeModel) {
            throw new \RuntimeException('Referee view requires RefereeModel.', 500);
        }

        $this->person = $model->getPerson();
        $this->referee = $model->getReferee();
        $this->history = $model->getHistory('ASC') ?: [];

        if ($this->referee !== null) {
            $formattedName = PersonNameFormatter::format(
                null,
                (string) ($this->referee->firstname ?? ''),
                (string) ($this->referee->nickname ?? ''),
                (string) ($this->referee->lastname ?? ''),
                (int) ($this->config['name_format'] ?? 0)
            );
            $this->title = Text::sprintf('COM_SPORTSMANAGEMENT_REFEREE_ABOUT_AS_A_REFEREE', $formattedName);
        } else {
            $this->title = Text::_('COM_SPORTSMANAGEMENT_REFEREE_UNKNOWN_PROJECT');
        }

        if (!empty($this->config['show_gameshistory'])) {
            $this->games = $model->getGames() ?: [];
            $this->teams = $model->getTeamsIndexedByProjectTeamId();
        }

        if ($this->person !== null) {
            $this->extended = ExtendedDataHelper::toArray((string) ($this->person->extended ?? ''));
        }

        $this->config['history_table_class'] = (string) ($this->config['history_table_class'] ?? 'table');
        $this->config['career_table_class'] = (string) ($this->config['career_table_class'] ?? 'table');
        $this->headertitle = $this->title;
    }
}
