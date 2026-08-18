<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage predictionrules
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class sportsmanagementModelPredictionRules extends BaseDatabaseModel
{
    public function __construct()
    {
        parent::__construct();

        $input = Factory::getApplication()->getInput();
        $roundId = $input->getInt('r', 0);

        new sportsmanagementModelPrediction();

        sportsmanagementModelPrediction::$roundID = $roundId;
        sportsmanagementModelPrediction::$pjID = $input->getInt('pj', 0);
        sportsmanagementModelPrediction::$from = $input->getInt('from', $roundId);
        sportsmanagementModelPrediction::$to = $input->getInt('to', $roundId);
        sportsmanagementModelPrediction::$predictionGameID = $input->getInt('prediction_id', 0);
        sportsmanagementModelPrediction::$predictionMemberID = $input->getInt('uid', 0);
        sportsmanagementModelPrediction::$joomlaUserID = $input->getInt('juid', 0);
        sportsmanagementModelPrediction::$pggroup = $input->getInt('pggroup', 0);
        sportsmanagementModelPrediction::$pggrouprank = $input->getInt('pggrouprank', 0);
        sportsmanagementModelPrediction::$isNewMember = $input->getInt('s', 0);
        sportsmanagementModelPrediction::$tippEntryDone = $input->getInt('eok', 0);
        sportsmanagementModelPrediction::$type = $input->getInt('type', 0);
        sportsmanagementModelPrediction::$page = max(1, $input->getInt('page', 1));
    }
}
