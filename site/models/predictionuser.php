<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage predictionuser
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\FormModel;

class sportsmanagementModelPredictionUser extends FormModel
{
    public int $predictionGameID = 0;
    public int $predictionMemberID = 0;
    public int $edit_modus = 0;
    public int $cfg_which_database = 0;

    public function __construct()
    {
        parent::__construct();

        $app = Factory::getApplication();
        $input = $app->getInput();

        new sportsmanagementModelPrediction();

        $roundId = $input->getInt('r', 0);
        $projectId = $input->getInt('pj', 0);
        $predictionId = $input->getInt('prediction_id', 0);

        $this->edit_modus = $input->getInt('edit_modus', 0);
        $this->cfg_which_database = $input->getInt('cfg_which_database', 0);
        $this->predictionGameID = $predictionId;
        $this->predictionMemberID = $input->getInt('uid', 0);

        sportsmanagementModelPrediction::$roundID = $roundId;
        sportsmanagementModelPrediction::$pjID = $projectId;
        sportsmanagementModelPrediction::$from = $input->getInt('from', $roundId);
        sportsmanagementModelPrediction::$to = $input->getInt('to', $roundId);
        sportsmanagementModelPrediction::$predictionGameID = $predictionId;
        sportsmanagementModelPrediction::$predictionMemberID = $this->predictionMemberID;
        sportsmanagementModelPrediction::$joomlaUserID = $input->getInt('juid', 0);
        sportsmanagementModelPrediction::$pggroup = $input->getInt('pggroup', 0);
        sportsmanagementModelPrediction::$pggrouprank = $input->getInt('pggrouprank', 0);
        sportsmanagementModelPrediction::$isNewMember = $input->getInt('s', 0);
        sportsmanagementModelPrediction::$tippEntryDone = $input->getInt('eok', 0);
        sportsmanagementModelPrediction::$type = $input->getInt('type', 0);
        sportsmanagementModelPrediction::$page = max(1, $input->getInt('page', 1));

        if ($this->edit_modus && $this->predictionMemberID === 0) {
            $identity = $app->getIdentity();
            $userId = (int) $identity->id;

            if ($userId > 0) {
                sportsmanagementModelPrediction::$joomlaUserID = $userId;
                $predictionMemberId = (int) $this->getpredictionmemberid($userId, $predictionId);
                $redirect = JSMPredictionHelperRoute::getPredictionMemberRoute(
                    $predictionId,
                    $predictionMemberId,
                    'edit',
                    $projectId,
                    (int) sportsmanagementModelPrediction::$pggroup,
                    (int) sportsmanagementModelPrediction::$roundID,
                    $this->cfg_which_database
                );
                $app->redirect($redirect);
            }
        }
    }

    public function getpredictionmemberid($user_id = 0, $prediction_id = 0)
    {
        $userId = (int) $user_id;
        $predictionId = (int) $prediction_id;

        if ($userId <= 0 || $predictionId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('pm.id'))
            ->from($db->quoteName('#__sportsmanagement_prediction_member', 'pm'))
            ->where($db->quoteName('pm.prediction_id') . ' = ' . $predictionId)
            ->where($db->quoteName('pm.user_id') . ' = ' . $userId);
        $db->setQuery($query, 0, 1);

        return (int) $db->loadResult();
    }

    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm(
            'com_sportsmanagement.' . $this->name,
            $this->name,
            ['load_data' => $loadData]
        );

        return $form ?: false;
    }
}
