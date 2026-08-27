<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage tournamentbracket
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\TournamentbracketModel;

class sportsmanagementViewtournamentbracket extends sportsmanagementView
{
    function init()
    {
        if (!class_exists(TournamentbracketModel::class)) {
            require_once JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php';
            require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/TournamentbracketModel.php';
        }

        $model = new TournamentbracketModel();
        $this->model = $model;

        $bracket = $model->gettournamentbracket($this->jinput->getInt('p', 0));
        $defaults = [
            'elfmeter' => ['[null,null,null,""]'],
            'teams' => '[]',
            'results' => '[]',
            'runden' => '[]',
        ];

        $this->bracket = is_array($bracket) ? array_replace($defaults, $bracket) : $defaults;

        if (empty($this->bracket['elfmeter']) || !is_array($this->bracket['elfmeter'])) {
            $this->bracket['elfmeter'] = $defaults['elfmeter'];
        }
    }
}
