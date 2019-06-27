<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      editmatch.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage editmatch
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * sportsmanagementControllerEditMatch
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class sportsmanagementControllerEditMatch extends FormController {

    /**
     * sportsmanagementControllerEditMatch::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    function __construct($config = array()) {
        parent::__construct($config);
    }

/**
 * sportsmanagementControllerEditMatch::savestats()
 * 
 * @return void
 */
function savestats()
{
$app = Factory::getApplication();
$post = $app->input->post->getArray(array());	
$model = $this->getModel('editmatch');
$return = $model->savestats($post);  

    $link = $_SERVER['HTTP_REFERER'];
        $msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_UPDATE_STATS');

        $this->setRedirect($link, $msg);    
}
    
     /**
      * sportsmanagementControllerEditMatch::cancel()
      * 
      * @return
      */
     public function cancel()
        {
            $msg = 'cancel';
            $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component',$msg);
 
                return true;
        }
    
    /**
     * sportsmanagementControllerEditMatch::getModel()
     * 
     * @param string $name
     * @param string $prefix
     * @param mixed $config
     * @return
     */
    public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true)) {
        return parent::getModel($name, $prefix, array('ignore_request' => false));
    }

    /**
     * sportsmanagementControllerEditMatch::saveReferees()
     * 
     * @return void
     */
    function saveReferees() {
        $app = Factory::getApplication();
        $post = $app->input->post->getArray(array());

        $model = $this->getModel('editmatch');
        $return = $model->updateReferees($post);

         $link = $_SERVER['HTTP_REFERER'];
        $msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_REFEREES');

        $this->setRedirect($link, $msg);
    }

    
    /**
     * sportsmanagementControllerEditMatch::saveroster()
     * 
     * @return void
     */
    function saveroster() {
        $app = Factory::getApplication();
        $post = $app->input->post->getArray(array());

        $model = $this->getModel('editmatch');
        $return = $model->updateRoster($post);
        $return = $model->updateStaff($post);

        $link = $_SERVER['HTTP_REFERER'];
        $msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED');

        $this->setRedirect($link, $msg);
    }

    /**
     * sportsmanagementControllerEditMatch::saveshort()
     * 
     * @return void
     */
    function saveshort() {
        $app = Factory::getApplication();
        $date = Factory::getDate();
        $user = Factory::getUser();
        $post = Factory::getApplication()->input->post->getArray(array());
        $option = Factory::getApplication()->input->getCmd('option');
        
        /** Ein Datenbankobjekt beziehen */
        $db = Factory::getDbo();
        
        /** Set the values */
        $data['team1_bonus'] = NULL;
        $data['team2_bonus'] = NULL;
        $data['team1_legs'] = NULL;
        $data['team2_legs'] = NULL;
        
        $data['modified'] = $date->toSql();
        $data['modified_by'] = $user->get('id');
        $data['id'] = $post['matchid'];

        $data['cancel'] = $post['cancel'];
        $data['cancel_reason'] = $post['cancel_reason'];
        $data['playground_id'] = $post['playground_id'];
        $data['overtime'] = $post['overtime'];
        $data['count_result'] = $post['count_result'];
        $data['alt_decision'] = $post['alt_decision'];
        $data['team_won'] = $post['team_won'];
        $data['preview'] = $post['preview'];
        if ( $post['team1_bonus'] != '' ) {
        $data['team1_bonus'] = $post['team1_bonus'];
        }
            if ( $post['team2_bonus'] != '' ) {
        $data['team2_bonus'] = $post['team2_bonus'];
            }
                if ( $post['team1_legs'] != '' ) {
        $data['team1_legs'] = $post['team1_legs'];
                }
                    if ( $post['team2_legs'] != '' ) {
        $data['team2_legs'] = $post['team2_legs'];
                    }
        $data['match_result_detail'] = $post['match_result_detail'];

        $data['show_report'] = $post['show_report'];

        $data['summary'] = $post['summary'];
        $data['old_match_id'] = $post['old_match_id'];
        $data['new_match_id'] = $post['new_match_id'];

        if (isset($post['extended']) && is_array($post['extended'])) {
            /** Convert the extended field to a string. */
            $parameter = new Registry;
            $parameter->loadArray($post['extended']);
            $data['extended'] = (string) $parameter;
        }

        $data['team1_result_decision'] = $post['team1_result_decision'];
        $data['team2_result_decision'] = $post['team2_result_decision'];
        $data['decision_info'] = $post['decision_info'];

/**
 * Create an object for the record we are going to update.
 */
        $object = new stdClass();
        foreach ($data as $key => $value) {
            $object->$key = $value;
        }

/**
 * Update their details in the table using id as the primary key.
 */
        $result_update = Factory::getDbo()->updateObject('#__sportsmanagement_match', $object, 'id', true);
        $link = $_SERVER['HTTP_REFERER'];
        $msg = sprintf(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_SAVED'), $post['matchid']);
        $this->setRedirect($link, $msg);
    }

}

?>
