<?php
/**
 * Native Joomla 5/6 frontend SportsManagement Tournamentbracket view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Tournamentbracket;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\TournamentbracketModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;

/** Native Joomla 5/6 frontend tournament bracket view. */
final class HtmlView extends SportsManagementProjectHtmlView
{
    public array $bracket = [
        'elfmeter' => ['[null,null,null,""]'],
        'teams' => '[]',
        'results' => '[]',
        'runden' => '[]',
    ];

    protected function prepareView(): void
    {
        $model = $this->getModel();

        if (!$model instanceof TournamentbracketModel) {
            throw new \RuntimeException('Tournamentbracket view requires TournamentbracketModel.', 500);
        }

        $defaults = $this->bracket;
        $bracket = $model->gettournamentbracket($model->getProjectId());
        $this->bracket = is_array($bracket) ? array_replace($defaults, $bracket) : $defaults;

        if (empty($this->bracket['elfmeter']) || !is_array($this->bracket['elfmeter'])) {
            $this->bracket['elfmeter'] = $defaults['elfmeter'];
        }
    }
}
