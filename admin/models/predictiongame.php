<?php



// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');


class sportsmanagementModelPredictionGame extends JModelAdmin
{
	
/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param	array	$data	An array of input data.
	 * @param	string	$key	The name of the key for the primary key.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Check specific edit permission then general edit permission.
		return JFactory::getUser()->authorise('core.edit', 'com_sportsmanagement.message.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
	}
    
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'predictiongame', $prefix = 'sportsmanagementTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}
    
	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true) 
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $cfg_which_media_tool = JComponentHelper::getParams($option)->get('cfg_which_media_tool',0);
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelagegroup getForm cfg_which_media_tool<br><pre>'.print_r($cfg_which_media_tool,true).'</pre>'),'Notice');
        // Get the form.
		$form = $this->loadForm('com_sportsmanagement.predictiongame', 'predictiongame', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
              
        
		return $form;
	}
    
	/**
	 * Method to get the script that have to be included on the form
	 *
	 * @return string	Script files
	 */
	public function getScript() 
	{
		return 'administrator/components/com_sportsmanagement/models/forms/sportsmanagement.js';
	}
    
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.predictiongame.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
		}
		return $data;
	}
	
    
    /**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function save($data)
	{
	   $option = JRequest::getCmd('option');
	$mainframe	= JFactory::getApplication();
    // Get a db connection.
        $db = JFactory::getDbo();
       $post=JRequest::get('post');
       
       //$mainframe->enqueueMessage(JText::_('sportsmanagementModelPredictionGame save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
       //$mainframe->enqueueMessage(JText::_('sportsmanagementModelPredictionGame post<br><pre>'.print_r($post,true).'</pre>'),'Notice');
       
       self::storePredictionAdmins($data);
       self::storePredictionProjects($data);
       
       // Proceed with the save
		return parent::save($data);   
    }   
    
    function import()
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        
        $mainframe->enqueueMessage(JText::_('sportsmanagementModelPredictionGame import<br><pre>'.print_r($option,true).'</pre>'   ),'');
        
    }
  
	

	/**
	* Method to return a prediction game item array
	*
	* @access  public
	* @return  object
	*/
	function getPredictionGame($id)
	{
		$query = '	SELECT	*
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_game
					WHERE id = ' .  $id;

		$this->_db->setQuery( $query );
		if ( !$result = $this->_db->loadObject() )
		{
			$this->setError( $this->_db->getErrorMsg() );
			return false;
		}
		else
		{
			return $result;
		}
	}

	/**
	* Method to return a prediction project array
	*
	* @access  public
	* @return  array
	* @since 0.1
	*/
	function getPredictionProjectIDs($prediction_id)
	{
		$query = 'SELECT project_id 
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_project 
					WHERE prediction_id=' . $prediction_id;
		$this->_db->setQuery($query);
		return $this->_db->loadResultArray();
	}

	function getPredictionProject()
	{
		$pred_project_id = JRequest::getVar('prediction_project');
		$query = '	SELECT	pro.*,
							joo.name AS project_name
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_project AS pro
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS joo ON joo.id = pro.project_id
					WHERE pro.id=' . (int) $pred_project_id;
		$this->_db->setQuery($query);
		if (!$result = $this->_db->loadObject())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		else
		{
			return $result;
		}
	}

	function getAdmins($prediction_id,$list=false)
	{
		$as_what = '';
		if ( $list )
		{
			$as_what = ' AS value';
		}
		$query = "SELECT user_id" . $as_what . " 
					FROM #__".COM_SPORTSMANAGEMENT_TABLE."_prediction_admin 
					WHERE prediction_id = " . $prediction_id;
//echo $query . '<br />';
		$this->_db->setQuery( $query );
		if ( $list )
		{
			return $this->_db->loadObjectList();
		}
		else
		{
			return $this->_db->loadResultArray();
		}
	}





  function getProjectTeams($project_id)
  {
  $query = "	SELECT	pt.id ,
							t.name

					FROM #__joomleague_project_team as pt
					inner join #__joomleague_team as t
          on pt.team_id = t.id
          where pt.project_id = $project_id
          ORDER by t.name
          ";

		$this->_db->setQuery($query);

		if (!$result = $this->_db->loadObjectList())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		else
		{
			return $result;
		}
  
  }
  
	

	function storePredictionAdmins($data)
	{
 		$option = JRequest::getCmd('option');
	$mainframe	= JFactory::getApplication();
         $result	= true;
		$peid	= ( isset( $data['user_ids'] ) ? $data['user_ids'] : array() );
		JArrayHelper::toInteger( $peid );
		$peids = implode( ',', $peid );

		$query = 'DELETE FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_admin WHERE prediction_id = ' . $data['id'];
		if ( count( $peid ) ) { $query .= ' AND user_id NOT IN (' . $peids . ')'; }
//echo $query . '<br />';
		$this->_db->setQuery( $query );
		if( !$this->_db->query() )
		{
			$this->setError( $this->_db->getErrorMsg() );
			$result = false;
		}
	

		for ( $x = 0; $x < count( $peid ); $x++ )
		{
			$query = "INSERT IGNORE INTO #__".COM_SPORTSMANAGEMENT_TABLE."_prediction_admin ( prediction_id, user_id ) VALUES ( '" . $data['id'] . "', '" . $peid[$x] . "' )";
//echo $query . '<br />';
			$this->_db->setQuery( $query );
			if ( !$this->_db->query() )
			{
				$this->setError( $this->_db->getErrorMsg() );
				$result= false;
			}
		}
        
        if ( $result )
        {
            $mainframe->enqueueMessage(JText::_('Admins zum Tippspiel gespeichern'),'Notice');
        }
	

		return $result;
	}

	function storePredictionProjects($data)
	{
 		$option = JRequest::getCmd('option');
	$mainframe	= JFactory::getApplication();
         $result	= true;
		$peid	= (isset($data['project_ids']) ? $data['project_ids'] : array());
		JArrayHelper::toInteger($peid);
		$peids = implode(',',$peid);

		$query = 'DELETE FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_project WHERE prediction_id = ' . $data['id'];
		if (count($peid)){$query .= ' AND project_id NOT IN (' . $peids . ')';}
		$this->_db->setQuery($query);
		if(!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			$result = false;
		}

		for ($x=0; $x < count($peid); $x++)
		{
			$query = "INSERT IGNORE INTO #__".COM_SPORTSMANAGEMENT_TABLE."_prediction_project (prediction_id,project_id) VALUES ('" . $data['id'] . "','" . $peid[$x] . "')";
			$this->_db->setQuery($query);
			if (!$this->_db->query())
			{
				$this->setError($this->_db->getErrorMsg());
				$result= false;
			}
		}
        
        if ( $result )
        {
            $mainframe->enqueueMessage(JText::_('Projekte zum Tippspiel gespeichern'),'Notice');
        }

		return $result;
	}

	

	

	/**
	 * Method to remove selected items
	 * from #__joomleague_prediction_admin
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */

	function deletePredictionAdmins($cid=array())
	{
		if ( count( $cid ) )
		{
			JArrayHelper::toInteger( $cid );
			$cids = implode( ',', $cid );
			$query = 'DELETE FROM #__joomleague_prediction_admin WHERE prediction_id IN ( ' . $cids . ' )';
//echo $query . '<br />'; return true;
			$this->_db->setQuery( $query );
			if ( !$this->_db->query() )
			{
				$this->setError( $this->_db->getErrorMsg() );
				return false;
			}
		}
		return true;
	}

	/**
	 * Method to remove selected items
	 * from #__joomleague_prediction_project
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */

	function deletePredictionProjects($cid=array())
	{
		if ( count( $cid ) )
		{
			JArrayHelper::toInteger( $cid );
			$cids = implode( ',', $cid );
			$query = 'DELETE FROM #__joomleague_prediction_project WHERE prediction_id IN ( ' . $cids . ' )';
//echo $query . '<br />'; return true;
			$this->_db->setQuery( $query );
			if ( !$this->_db->query() )
			{
				$this->setError( $this->_db->getErrorMsg() );
				return false;
			}
		}
		return true;
	}

	/**
	 * Method to remove selected items
	 * from #__joomleague_prediction_member
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */

	function deletePredictionMembers($cid=array())
	{
		if ( count( $cid ) )
		{
			JArrayHelper::toInteger( $cid );
			$cids = implode( ',', $cid );
			$query = 'DELETE FROM #__joomleague_prediction_member WHERE prediction_id IN ( ' . $cids . ' )';
			$this->_db->setQuery( $query );
			if ( !$this->_db->query() )
			{
				$this->setError( $this->_db->getErrorMsg() );
				return false;
			}
		}
		return true;
	}

	/**
	 * Method to remove selected items
	 * from #__joomleague_prediction_result
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */

	function deletePredictionResults($cid=array())
	{
		if ( count( $cid ) )
		{
			JArrayHelper::toInteger( $cid );
			$cids = implode( ',', $cid );
			$query = 'DELETE FROM #__joomleague_prediction_result WHERE prediction_id IN ( ' . $cids . ' )';
			$this->_db->setQuery( $query );
			if ( !$this->_db->query() )
			{
				$this->setError( $this->_db->getErrorMsg() );
				return false;
			}
		}
		return true;
	}


	/**
	 * Method to update prediction project settings
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	function savePredictionProjectSettings($data)
	{
 		$result	= true;

		if ( !isset( $data['points_tipp_champ'] ) )				{ $data['points_tipp_champ'] =				$data['old_points_tipp_champ']; }
    
    if ( !isset( $data['league_champ'] ) )				{ $data['league_champ'] =				$data['old_league_champ']; }

		if ( !isset( $data['points_tipp_joker'] ) )				{ $data['points_tipp_joker'] =				$data['old_points_tipp_joker']; }
		if ( !isset( $data['points_correct_result_joker'] ) )	{ $data['points_correct_result_joker'] =	$data['old_points_correct_result_joker']; }
		if ( !isset( $data['points_correct_diff_joker'] ) )		{ $data['points_correct_diff_joker'] =		$data['old_points_correct_diff_joker']; }
		if ( !isset( $data['points_correct_draw_joker'] ) )		{ $data['points_correct_draw_joker'] =		$data['old_points_correct_draw_joker']; }
		if ( !isset( $data['points_correct_tendence_joker'] ) )	{ $data['points_correct_tendence_joker'] =	$data['old_points_correct_tendence_joker']; }

		if ( !isset( $data['joker_limit'] ) ||
			 $data['joker_limit'] < 1 )							{ $data['joker_limit'] = 0; }

		$query = 	"	UPDATE	#__joomleague_prediction_project
						SET
								prediction_id='"					. $data['prediction_id'] .					"',
								project_id='"						. $data['project_id'] .						"',
								mode='"								. $data['mode'] .							"',
								overview='"							. $data['overview'] .						"',
								points_tipp='"						. $data['points_tipp'] .					"',
								points_tipp_joker='"				. $data['points_tipp_joker'] .				"',
								points_tipp_champ='"				. $data['points_tipp_champ'] .				"',
								points_correct_result='"			. $data['points_correct_result'] .			"',
								points_correct_result_joker='"		. $data['points_correct_result_joker'] .	"',
								points_correct_diff='"				. $data['points_correct_diff'] .			"',
								points_correct_diff_joker='"		. $data['points_correct_diff_joker'] .		"',
								points_correct_draw='"				. $data['points_correct_draw'] .			"',
								points_correct_draw_joker='"		. $data['points_correct_draw_joker'] .		"',
								points_correct_tendence='"			. $data['points_correct_tendence'] .		"',
								points_correct_tendence_joker='"	. $data['points_correct_tendence_joker'] .	"',
								joker='"							. $data['joker'] .							"',
								joker_limit='"						. $data['joker_limit'] .					"',
                league_champ='"						. $data['league_champ'] .					"',
								champ='"							. $data['champ'] .							"',
								published='"						. $data['published'] .						"'
						WHERE id='" . $data['id'] . "'
					";
//echo $query . '<br />'; return true;

		$this->_db->setQuery( $query );
		if ( !$this->_db->query() )
		{
			$this->setError( $this->_db->getErrorMsg() );
			$result= false;
		}

		return $result;
	}

	/**
	 * Method to rebuild the points of all prediction projects
	 * of the selected Prediction Game
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	function rebuildPredictionProjectSPoints($cid)
	{
 		$result	= true;

		//JArrayHelper::toInteger($cid);

		foreach ($cid AS $predictonID)
		{
			$query = 'SELECT pp.id FROM #__joomleague_prediction_project AS pp WHERE pp.prediction_id=' . (int) $predictonID;
			$this->_db->setQuery($query);
			if ($predictionProjectIDList = $this->_db->loadResultArray())
			{
				foreach ($predictionProjectIDList AS $predictionProjectID)
				{
					$query = 'SELECT pp.* FROM #__joomleague_prediction_project AS pp WHERE pp.id=' . (int) $predictionProjectID;
					$this->_db->setQuery($query);
					$predictionProject = $this->_db->loadObject();

					$query = '	SELECT	pr.*,
										m.team1_result,
										m.team2_result,
										m.team1_result_decision,
										m.team2_result_decision
								FROM #__joomleague_prediction_result AS pr
								LEFT JOIN #__joomleague_match AS m ON m.id=pr.match_id
								WHERE	pr.prediction_id=' . (int) $predictonID . ' AND
										pr.project_id=' . (int) $predictionProject->project_id;
					$this->_db->setQuery($query);
					$predictionProjectResultList = $this->_db->loadObjectList();

					foreach ($predictionProjectResultList AS $predictionProjectResult)
					{
						//echo '<br /><pre>~' . print_r($predictionProjectResult,true) . '~</pre><br />';

						$result_home	= $predictionProjectResult->team1_result;
						$result_away	= $predictionProjectResult->team2_result;

						$result_dHome	= $predictionProjectResult->team1_result_decision;
						$result_dAway	= $predictionProjectResult->team2_result_decision;

						$tipp_home	= $predictionProjectResult->tipp_home;
						$tipp_away	= $predictionProjectResult->tipp_away;

						$tipp		= $predictionProjectResult->tipp;
						$joker		= $predictionProjectResult->joker;

						$points		= $predictionProjectResult->points;
						$top		= $predictionProjectResult->top;
						$diff		= $predictionProjectResult->diff;
						$tend		= $predictionProjectResult->tend;

						if($tipp_home>$tipp_away){$tipp='1';}elseif($tipp_home<$tipp_away){$tipp='2';}elseif(!is_null($tipp_home)&&!is_null($tipp_away)){$tipp='0';}else{$tipp=null;}

						$points		= null;
						$top		= null;
						$diff		= null;
						$tend		= null;

						if (!is_null($tipp_home)&&!is_null($tipp_away))
						{
							if ($predictionProject->mode==1)	// TOTO prediction Mode
							{
								$points=$tipp;
							}
							else	// Standard prediction Mode
							{
								if ($joker)	// Member took a Joker for this prediction
								{
									if (($result_home==$tipp_home)&&($result_away==$tipp_away))
									{
										//Prediction Result is the same as the match result / Top Tipp
										$points = $predictionProject->points_correct_result_joker;
										$top=1;
									}
									elseif(($result_home==$result_away)&&($result_home - $result_away)==($tipp_home - $tipp_away))
									{
										//Prediction Result is not the same as the match result but the correct difference between home and
										//away result was tipped and the matchresult is draw
										$points = $predictionProject->points_correct_draw_joker;
										$diff=1;
									}
									elseif(($result_home - $result_away)==($tipp_home - $tipp_away))
									{
										//Prediction Result is not the same as the match result but the correct difference between home and
										//away result was tipped
										$points = $predictionProject->points_correct_diff_joker;
										$diff=1;
									}
									elseif (((($result_home - $result_away)>0)&&(($tipp_home - $tipp_away)>0)) ||
											 ((($result_home - $result_away)<0)&&(($tipp_home - $tipp_away)<0)))
									{
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
								else	// No Joker was used for this prediction
								{
									if (($result_home==$tipp_home)&&($result_away==$tipp_away))
									{
										//Prediction Result is the same as the match result / Top Tipp
										$points = $predictionProject->points_correct_result;
										$top=1;
									}
									elseif(($result_home==$result_away)&&($result_home - $result_away)==($tipp_home - $tipp_away))
									{
										//Prediction Result is not the same as the match result but the correct difference between home and
										//away result was tipped and the matchresult is draw
										$points = $predictionProject->points_correct_draw;
										$diff=1;
									}
									elseif(($result_home - $result_away)==($tipp_home - $tipp_away))
									{
										//Prediction Result is not the same as the match result but the correct difference between home and
										//away result was tipped
										$points = $predictionProject->points_correct_diff;
										$diff=1;
									}
									elseif (((($result_home - $result_away)>0)&&(($tipp_home - $tipp_away)>0)) ||
											 ((($result_home - $result_away)<0)&&(($tipp_home - $tipp_away)<0)))
									{
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

						$query =	"	UPDATE	#__joomleague_prediction_result

										SET
											tipp_home=" .	((!is_null($tipp_home))	? "'".$tipp_home."'"	: 'NULL') . ",
											tipp_away=" .	((!is_null($tipp_away))	? "'".$tipp_away."'"	: 'NULL') . ",
											tipp=" .		((!is_null($tipp))		? "'".$tipp."'"			: 'NULL') . ",
											joker=" .		((!is_null($joker))		? "'".$joker."'"		: 'NULL') . ",
											points=" .		((!is_null($points))	? "'".$points."'"		: 'NULL') . ",
											top=" .			((!is_null($top))		? "'".$top."'"			: 'NULL') . ",
											diff=" .		((!is_null($diff))		? "'".$diff."'"			: 'NULL') . ",
											tend=" .		((!is_null($tend))		? "'".$tend."'"			: 'NULL') . "
										WHERE id=".$predictionProjectResult->id;
						//echo "<br />$query<br />";
						$this->_db->setQuery($query);
						if (!$this->_db->query()){$this->setError($this->_db->getErrorMsg());$result= false;}
					}
				}
			}
		}

		return $result;
	}

}
?>