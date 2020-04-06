<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       predictiongame.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

require_once JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'predictiongames.php';

/**
 * sportsmanagementModelPredictionGame
 *
 * @package 
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelPredictionGame extends JSMModelAdmin
{
  
    /**
     * Method to save the form data.
     *
     * @param  array    The form data.
     * @return boolean    True on success.
     * @since  1.6
     */
    public function save($data)
    {
          $post = Factory::getApplication()->input->post->getArray(array());
          // Set the values
          $data['modified'] = $this->jsmdate->toSql();
          $data['modified_by'] = $this->jsmuser->get('id');
     

     
          // zuerst sichern, damit wir bei einer neuanlage die id haben
        try{
              $result = parent::save($data);
        }
        catch (Exception $e)
        {
            $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
            $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getCode()), 'error');
            $result = false;
        }    
     
        //       $this->jsmapp->enqueueMessage(__METHOD__.' '.__LINE__.' '.'result '.$result,'');
     
        if ($result ) {
            $id =  (int) $this->getState($this->getName().'.id');
            $isNew = $this->getState($this->getName() . '.new');
            $data['id'] = $id;
          
            if ($isNew ) {
                //Here you can do other tasks with your newly saved record...
                $this->jsmapp->enqueueMessage(Text::plural(strtoupper($this->jsmoption) . '_N_ITEMS_CREATED', $id), '');
            }
         
            self::storePredictionAdmins($data);
            self::storePredictionProjects($data);
        }
        
      
     
     
          return $result;  
    } 
  
    /**
     * sportsmanagementModelPredictionGame::import()
     *
     * @return void
     */
    function import()
    {
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
    }

  

    /**
     * Method to return a prediction game item array
     * sportsmanagementModelPredictionGame::getPredictionGame()
     *
     * @param  integer $id
     * @return
     */
    function getPredictionGame($id=0)
    {
        //	   $app = Factory::getApplication();
        //        $option = Factory::getApplication()->input->getCmd('option');
        //        // Create a new query object.		
        //		$db = sportsmanagementHelper::getDBConnection();
        //		$query = $db->getQuery(true);
      
        if ($id ) {
            // Select some fields
            $this->jsmquery->clear();
            $this->jsmquery->select('*');
            $this->jsmquery->from('#__sportsmanagement_prediction_game');
            $this->jsmquery->where('id = ' . $id);

            $this->jsmdb->setQuery($this->jsmquery);
            if (!$result = $this->jsmdb->loadObject() ) {
                       sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->jsmdb->getErrorMsg(), __LINE__);
                       return false;
            }
            else
            {
                       return $result;
            }
        }
        else
        {
            return false;
        }
    }

    /**
    * Method to return a prediction project array
    *
    * @access public
    * @return array
    * @since  0.1
    */
    function getPredictionProjectIDs($prediction_id=0)
    {
          //$app = Factory::getApplication();
        //        $option = Factory::getApplication()->input->getCmd('option');
        //        // Create a new query object.		
        //		$db = sportsmanagementHelper::getDBConnection();
        //		$query = $db->getQuery(true);
      
        if ($prediction_id ) {
            // Select some fields
            $this->jsmquery->clear();
            $this->jsmquery->select('project_id');
            $this->jsmquery->from('#__sportsmanagement_prediction_project');
            $this->jsmquery->where('prediction_id = ' . $prediction_id);

            $this->jsmdb->setQuery($this->jsmquery);
      
            if(version_compare(JVERSION, '3.0.0', 'ge')) {
                // Joomla! 3.0 code here
                return $this->jsmdb->loadColumn();
            }
            elseif(version_compare(JVERSION, '2.5.0', 'ge')) {
                        // Joomla! 2.5 code here
                          return $this->jsmdb->loadResultArray();
            }

        }
        else
        {
            return false;
        }

    }


    /**
     * sportsmanagementModelPredictionGame::storePredictionAdmins()
     *
     * @param  mixed $data
     * @return
     */
    function storePredictionAdmins($data)
    {
         $option = Factory::getApplication()->input->getCmd('option');
        $app    = Factory::getApplication();
          // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true);
      
         $result    = true;
        $peid    = ( isset($data['user_ids']) ? $data['user_ids'] : array() );
        ArrayHelper::toInteger($peid);
        $peids = implode(',', $peid);

        $query = 'DELETE FROM #__sportsmanagement_prediction_admin WHERE prediction_id = ' . $data['id'];
        if (count($peid) ) { $query .= ' AND user_id NOT IN (' . $peids . ')';
        }
        //echo $query . '<br />';
        $this->_db->setQuery($query);
        if(!$this->_db->execute() ) {
            sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
            $result = false;
        }
  

        for ( $x = 0; $x < count($peid); $x++ )
        {
            $query = "INSERT IGNORE INTO #__sportsmanagement_prediction_admin ( prediction_id, user_id ) VALUES ( '" . $data['id'] . "', '" . $peid[$x] . "' )";
            //echo $query . '<br />';
            $this->_db->setQuery($query);
            if (!$this->_db->execute() ) {
                sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
                $result= false;
            }
        }
      
        if ($result ) {
            $app->enqueueMessage(Text::_('Admins zum Tippspiel gespeichern'), 'Notice');
        }
  

        return $result;
    }

    /**
     * sportsmanagementModelPredictionGame::storePredictionProjects()
     *
     * @param  mixed $data
     * @return
     */
    function storePredictionProjects($data)
    {
     
         $result = true;
        $peid = (isset($data['project_ids']) ? $data['project_ids'] : array());
        ArrayHelper::toInteger($peid);
        $peids = implode(',', $peid);

        $this->jsmquery = 'DELETE FROM #__sportsmanagement_prediction_project WHERE prediction_id = ' . $data['id'];
        if (count($peid)) {$this->jsmquery .= ' AND project_id NOT IN (' . $peids . ')';
        }
        try{
              $this->jsmdb->setQuery($this->jsmquery);
              $this->jsmdb->execute();
              $result = true;
        }
        catch (Exception $e)
        {
            $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
            $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getCode()), 'error');
            $result = false;
        }

        for ($x=0; $x < count($peid); $x++)
        {
            $this->jsmquery = "INSERT IGNORE INTO #__sportsmanagement_prediction_project (prediction_id,project_id) VALUES ('" . $data['id'] . "','" . $peid[$x] . "')";
            try{
                 $this->jsmdb->setQuery($this->jsmquery);
                 $this->jsmdb->execute();
                 $result = true;
            }
            catch (Exception $e)
            {
                $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
                $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getCode()), 'error');
                $result = false;
            }
        }
      
        if ($result ) {
            $this->jsmapp->enqueueMessage(Text::_('Projekte zum Tippspiel gespeichern'), 'Notice');
        }

        return $result;
    }

  

  

    /**
     * Method to remove selected items
     * from prediction_admin
     *
     * @access public
     * @return boolean    True on success
     * @since  0.1
     */

    function deletePredictionAdmins($cid=array())
    {
          $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
      
        if (count($cid) ) {
            ArrayHelper::toInteger($cid);
            $cids = implode(',', $cid);
            $query->delete()->from('#__sportsmanagement_prediction_admin')->where('prediction_id IN ('.$cids.')');
            $db->setQuery($query);
            if (!$db->execute() ) {
                sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
                return false;
            }
        }
        return true;
    }

    /**
     * Method to remove selected items
     * from prediction_project
     *
     * @access public
     * @return boolean    True on success
     * @since  0.1
     */

    function deletePredictionProjects($cid=array())
    {
          $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
      
        if (count($cid) ) {
            ArrayHelper::toInteger($cid);
            $cids = implode(',', $cid);
            $query->delete()->from('#__sportsmanagement_prediction_project')->where('prediction_id IN ('.$cids.')');

            $db->setQuery($query);
            if (!$db->execute() ) {
                sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
                return false;
            }
        }
        return true;
    }

    /**
     * Method to remove selected items
     * from prediction_member
     *
     * @access public
     * @return boolean    True on success
     * @since  0.1
     */

    function deletePredictionMembers($cid=array())
    {
          $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
      
        if (count($cid) ) {
            ArrayHelper::toInteger($cid);
            $cids = implode(',', $cid);
            $query->delete()->from('#__sportsmanagement_prediction_member')->where('prediction_id IN ('.$cids.')');
            $db->setQuery($query);
            if (!$db->execute() ) {
                sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
                return false;
            }
        }
        return true;
    }

    /**
     * Method to remove selected items
     * from prediction_result
     *
     * @access public
     * @return boolean    True on success
     * @since  0.1
     */

    function deletePredictionResults($cid=array())
    {
          $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
      
        if (count($cid) ) {
            ArrayHelper::toInteger($cid);
            $cids = implode(',', $cid);
            $query->delete()->from('#__sportsmanagement_prediction_result')->where('prediction_id IN ('.$cids.')');
            $db->setQuery($query);
            if (!$db->execute() ) {
                sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
                return false;
            }
        }
        return true;
    }


    /**
     * Method to update prediction project settings
     *
     * @access public
     * @return boolean    True on success
     */
    function savePredictionProjectSettings($data)
    {
          $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
      
         $result    = true;

        if (!isset($data['points_tipp_champ']) ) { $data['points_tipp_champ'] =                $data['old_points_tipp_champ'];
        }
  
        if (!isset($data['league_champ']) ) { $data['league_champ'] =                $data['old_league_champ'];
        }

        if (!isset($data['points_tipp_joker']) ) { $data['points_tipp_joker'] =                $data['old_points_tipp_joker'];
        }
        if (!isset($data['points_correct_result_joker']) ) { $data['points_correct_result_joker'] =    $data['old_points_correct_result_joker'];
        }
        if (!isset($data['points_correct_diff_joker']) ) { $data['points_correct_diff_joker'] =        $data['old_points_correct_diff_joker'];
        }
        if (!isset($data['points_correct_draw_joker']) ) { $data['points_correct_draw_joker'] =        $data['old_points_correct_draw_joker'];
        }
        if (!isset($data['points_correct_tendence_joker']) ) { $data['points_correct_tendence_joker'] =    $data['old_points_correct_tendence_joker'];
        }

        if (!isset($data['joker_limit'])
            || $data['joker_limit'] < 1
        ) { $data['joker_limit'] = 0;
        }

        //		$query = 	"	UPDATE	#__sportsmanagement_prediction_project
        //						SET
        //								prediction_id='"					. $data['prediction_id'] .					"',
        //								project_id='"						. $data['project_id'] .						"',
        //								mode='"								. $data['mode'] .							"',
        //								overview='"							. $data['overview'] .						"',
        //								points_tipp='"						. $data['points_tipp'] .					"',
        //								points_tipp_joker='"				. $data['points_tipp_joker'] .				"',
        //								points_tipp_champ='"				. $data['points_tipp_champ'] .				"',
        //								points_correct_result='"			. $data['points_correct_result'] .			"',
        //								points_correct_result_joker='"		. $data['points_correct_result_joker'] .	"',
        //								points_correct_diff='"				. $data['points_correct_diff'] .			"',
        //								points_correct_diff_joker='"		. $data['points_correct_diff_joker'] .		"',
        //								points_correct_draw='"				. $data['points_correct_draw'] .			"',
        //								points_correct_draw_joker='"		. $data['points_correct_draw_joker'] .		"',
        //								points_correct_tendence='"			. $data['points_correct_tendence'] .		"',
        //								points_correct_tendence_joker='"	. $data['points_correct_tendence_joker'] .	"',
        //								joker='"							. $data['joker'] .							"',
        //								joker_limit='"						. $data['joker_limit'] .					"',
        //                league_champ='"						. $data['league_champ'] .					"',
        //								champ='"							. $data['champ'] .							"',
        //								published='"						. $data['published'] .						"'
        //						WHERE id='" . $data['id'] . "'
        //					";


        // Must be a valid primary key value.
           $object = new stdClass();
           $object->id = $data['id'];
           $object->prediction_id = $data['prediction_id'];
        $object->project_id = $data['project_id'];
        $object->mode = $data['mode'];
        $object->overview = $data['overview'];
        $object->points_tipp = $data['points_tipp'];
        $object->points_tipp_joker = $data['points_tipp_joker'];
        $object->points_tipp_champ = $data['points_tipp_champ'];
        $object->points_correct_result = $data['points_correct_result'];
        $object->points_correct_result_joker = $data['points_correct_result_joker'];
        $object->points_correct_diff = $data['points_correct_diff'];
        $object->points_correct_diff_joker = $data['points_correct_diff_joker'];
        $object->points_correct_draw = $data['points_correct_draw'];
        $object->points_correct_draw_joker = $data['points_correct_draw_joker'];
        $object->points_correct_tendence = $data['points_correct_tendence'];
        $object->points_correct_tendence_joker = $data['points_correct_tendence_joker'];
        $object->joker = $data['joker'];
        $object->joker_limit = $data['joker_limit'];
           $object->league_champ = $data['league_champ'];
        $object->champ = $data['champ'];
        $object->published = $data['published'];
                                      
      
           // Update their details in the table using id as the primary key.
           $result = Factory::getDbo()->updateObject('#__sportsmanagement_prediction_project', $object, 'id');
      
        //$this->_db->setQuery( $query );
        if (!$result ) {
               $result= false;
        }

        return $result;
    }

    /**
     * Method to rebuild the points of all prediction projects
     * of the selected Prediction Game
     *
     * @access public
     * @return boolean    True on success
     */
    function rebuildPredictionProjectSPoints($cid)
    {
          $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
      
         $result    = true;

        foreach ($cid AS $predictonID)
        {
             // Select some fields
              $query->clear();
              $query->select('pp.id');
              $query->from('#__sportsmanagement_prediction_project AS pp ');
              $query->where('pp.prediction_id = ' . (int) $predictonID);

            $db->setQuery($query);
            if(version_compare(JVERSION, '3.0.0', 'ge')) {
                // Joomla! 3.0 code here
                $result = $db->loadColumn();
            }
            elseif(version_compare(JVERSION, '2.5.0', 'ge')) {
                  // Joomla! 2.5 code here
                 $result = $db->loadResultArray();
            }

            if ($predictionProjectIDList = $result ) {
                foreach ($predictionProjectIDList AS $predictionProjectID)
                {
                    // Select some fields
                    $query->clear();
                    $query->select('pp.*');
                    $query->from('#__sportsmanagement_prediction_project AS pp ');
                    $query->where('pp.id = ' . (int) $predictionProjectID);

                    $db->setQuery($query);
                    $predictionProject = $db->loadObject();
                  
                    $query->clear();
                    $query->select('pr.*');
                    $query->select('m.team1_result,m.team2_result,m.team1_result_decision,m.team2_result_decision');
                    $query->from('#__sportsmanagement_prediction_result AS pr ');
                    $query->join('LEFT', '#__sportsmanagement_match AS m ON m.id = pr.match_id');
                    $query->where('pr.prediction_id = ' . (int) $predictonID);
                    $query->where('pr.project_id = ' . (int) $predictionProject->project_id);
                  
                    $db->setQuery($query);
                    $predictionProjectResultList = $db->loadObjectList();

                    foreach ($predictionProjectResultList AS $predictionProjectResult)
                    {
                        $result_home    = $predictionProjectResult->team1_result;
                        $result_away    = $predictionProjectResult->team2_result;

                        $result_dHome    = $predictionProjectResult->team1_result_decision;
                        $result_dAway    = $predictionProjectResult->team2_result_decision;

                        $tipp_home    = $predictionProjectResult->tipp_home;
                        $tipp_away    = $predictionProjectResult->tipp_away;

                        $tipp        = $predictionProjectResult->tipp;
                        $joker        = $predictionProjectResult->joker;

                        $points        = $predictionProjectResult->points;
                        $top        = $predictionProjectResult->top;
                        $diff        = $predictionProjectResult->diff;
                        $tend        = $predictionProjectResult->tend;

                        if($tipp_home>$tipp_away) {
                            $tipp='1';
                        }
                        elseif($tipp_home<$tipp_away) {
                            $tipp='2';
                        }
                        elseif(!is_null($tipp_home)&&!is_null($tipp_away)) {
                            $tipp='0';
                        }
                        else
                                    {
                            $tipp=null;
                        }

                        $points        = null;
                        $top        = null;
                        $diff        = null;
                        $tend        = null;

                        if (!is_null($tipp_home)&&!is_null($tipp_away)) {
                            if ($predictionProject->mode==1)    // TOTO prediction Mode
                            {
                                $points=$tipp;
                            }
                            else    // Standard prediction Mode
                            {
                                if ($joker)    // Member took a Joker for this prediction
                                {
                                    if (($result_home==$tipp_home)&&($result_away==$tipp_away)) {
                                        //Prediction Result is the same as the match result / Top Tipp
                                        $points = $predictionProject->points_correct_result_joker;
                                        $top=1;
                                    }
                                    elseif(($result_home==$result_away)&&($result_home - $result_away)==($tipp_home - $tipp_away)) {
                                        //Prediction Result is not the same as the match result but the correct difference between home and
                                        //away result was tipped and the matchresult is draw
                                        $points = $predictionProject->points_correct_draw_joker;
                                        $diff=1;
                                    }
                                    elseif(($result_home - $result_away)==($tipp_home - $tipp_away)) {
                                        //Prediction Result is not the same as the match result but the correct difference between home and
                                        //away result was tipped
                                        $points = $predictionProject->points_correct_diff_joker;
                                        $diff=1;
                                    }
                                    elseif (((($result_home - $result_away)>0)&&(($tipp_home - $tipp_away)>0))
                                        || ((($result_home - $result_away)<0)&&(($tipp_home - $tipp_away)<0))
                                    ) {
                                        //Prediction Result is not the same as the match result but the tendence of the result is correct
                                        $points = $predictionProject->points_correct_tendence_joker;
                                        $tend=1;
                                    }
                                    else
                                    {
                                        //Prediction Result is totally wrong but we check if there is a point to give
                                        $points = $predictionProject->points_tipp_joker;
                                    }
                                }
                                else    // No Joker was used for this prediction
                                {
                                    if (($result_home==$tipp_home)&&($result_away==$tipp_away)) {
                                        //Prediction Result is the same as the match result / Top Tipp
                                        $points = $predictionProject->points_correct_result;
                                        $top=1;
                                    }
                                    elseif(($result_home==$result_away)&&($result_home - $result_away)==($tipp_home - $tipp_away)) {
                                        //Prediction Result is not the same as the match result but the correct difference between home and
                                        //away result was tipped and the matchresult is draw
                                        $points = $predictionProject->points_correct_draw;
                                        $diff=1;
                                    }
                                    elseif(($result_home - $result_away)==($tipp_home - $tipp_away)) {
                                        //Prediction Result is not the same as the match result but the correct difference between home and
                                        //away result was tipped
                                        $points = $predictionProject->points_correct_diff;
                                        $diff=1;
                                    }
                                    elseif (((($result_home - $result_away)>0)&&(($tipp_home - $tipp_away)>0))
                                        || ((($result_home - $result_away)<0)&&(($tipp_home - $tipp_away)<0))
                                    ) {
                                        //Prediction Result is not the same as the match result but the tendence of the result is correct
                                        $points = $predictionProject->points_correct_tendence;
                                        $tend=1;
                                    }
                                    else
                                    {
                                        //Prediction Result is totally wrong but we check if there is a point to give
                                        $points = $predictionProject->points_tipp;
                                    }
                                }
                            }
                        }

                        //						$query =	"	UPDATE	#__sportsmanagement_prediction_result
                        //
                        //										SET
                        //											tipp_home=" .	((!is_null($tipp_home))	? "'".$tipp_home."'"	: 'NULL') . ",
                        //											tipp_away=" .	((!is_null($tipp_away))	? "'".$tipp_away."'"	: 'NULL') . ",
                        //											tipp=" .		((!is_null($tipp))		? "'".$tipp."'"			: 'NULL') . ",
                        //											joker=" .		((!is_null($joker))		? "'".$joker."'"		: 'NULL') . ",
                        //											points=" .		((!is_null($points))	? "'".$points."'"		: 'NULL') . ",
                        //											top=" .			((!is_null($top))		? "'".$top."'"			: 'NULL') . ",
                        //											diff=" .		((!is_null($diff))		? "'".$diff."'"			: 'NULL') . ",
                        //											tend=" .		((!is_null($tend))		? "'".$tend."'"			: 'NULL') . "
                        //										WHERE id=".$predictionProjectResult->id;
                                      
                        // Must be a valid primary key value.
                          $object = new stdClass();
                          $object->id = $predictionProjectResult->id;
                          $object->tipp_home = $tipp_home;
                        $object->tipp_away = $tipp_away;
                        $object->tipp = $tipp;
                        $object->joker = $joker;
                        $object->points = $points;
                        $object->top = $top;
                        $object->diff = $diff;
                        $object->tend = $tend;
                        // Update their details in the table using id as the primary key.
                          $result = Factory::getDbo()->updateObject('#__sportsmanagement_prediction_result', $object, 'id');
      
                        //echo "<br />$query<br />";
                        //$this->_db->setQuery($query);
                        if (!$result) {
                            $result = false;
                        }
                    }
                }
            }
        }

        return $result;
    }

}
?>
