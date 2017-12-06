<?php

defined('_JEXEC') or die;



class sportsmanagementModelTreetonodes extends JSMModelList
{



	public function __construct($config = array())
	{
			parent::__construct($config);
//        $this->jsmdb = sportsmanagementHelper::getDBConnection();
//        parent::setDbo($this->jsmdb);
//        $this->jsmquery = $this->jsmdb->getQuery(true);        
////
////		// Register Extra tasks
//////		$this->registerTask('add','display');
//////		$this->registerTask('edit','display');
//////		$this->registerTask('apply','save');
////        
//        // Reference global application object
//        $this->jsmapp = JFactory::getApplication();
//        // JInput object
//        $this->jsmjinput = $this->jsmapp->input;
//        $this->jsmoption = $this->jsmjinput->getCmd('option');
//        $this->jsmdocument = JFactory::getDocument();
        
        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsmjinput<br><pre>'.print_r($this->jsmjinput,true).'</pre>'),'Notice');
        
		$limit = 130;
		$this->setState('limit',$limit);
	}

	
    
    /**
     * sportsmanagementModelTreetonodes::populateState()
     * 
     * @param mixed $ordering
     * @param mixed $direction
     * @return void
     */
    protected function populateState($ordering = null,$direction = null)
	{
		$app = JFactory::getApplication();

		// Adjust the context to support modal layouts.
		if($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}
	}

	protected function getStoreId($id = '')
	{
		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{

		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();
		//$app = JFactory::getApplication();
//		$jinput = $app->input;
//		$option = $jinput->getCmd('option');

		$project_id = $this->jsmapp->getUserState($this->jsmoption . '.pid');
		//$treeto_id = $this->jsmapp->getUserState($this->jsmoption . 'treeto_id');
        $treeto_id = $this->jsmjinput->get('tid');

		// Select the required fields from the table.
		$query->select($this->getState('list.select','a.*'));
		$query->from('#__sportsmanagement_treeto_node AS a');

		// join Project-team table
		$query->join('LEFT','#__sportsmanagement_project_team AS pt ON pt.id = a.team_id');
        $query->join('LEFT','#__sportsmanagement_season_team_id AS st on pt.team_id = st.id');  
		// join Team table
		$query->select('t.name AS team_name');
		$query->join('LEFT','#__sportsmanagement_team AS t ON t.id = st.team_id');
		// join treeto table
		$query->select('tt.tree_i AS tree_i');
		$query->join('LEFT','#__sportsmanagement_treeto AS tt ON tt.id = a.treeto_id');
		// join treeto match table
		$query->select('COUNT(ttm.id) AS countmatch');
		$query->join('LEFT','#__sportsmanagement_treeto_match AS ttm ON ttm.node_id = a.id');

		$query->where('a.treeto_id = ' . $treeto_id);

		$query->order('a.row');
		$query->group('a.id');

//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');

		return $query;
	}

	
    
	function getMaxRound($project_id)
	{
		$result = 0;
		if($project_id > 0)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('COUNT(roundcode)');
			$query->from('#__sportsmanagement_round');
			$query->where('project_id = ' . $project_id);
			$db->setQuery($query);
			$result = $db->loadResult();
		}
		return $result;
	}



	function setRemoveNode($post)
	{
		//$app = JFactory::getApplication();
//		$jinput = $app->input;
//		$option = $jinput->getCmd('option');
		// $treeto_id = $app->getUserState($option . 'treeto_id');
		$treeto_id = $post['treeto_id'];

//// delete all custom keys for user 1001.
//$conditions = array(
//    $db->quoteName('user_id') . ' = 1001', 
//    $db->quoteName('profile_key') . ' = ' . $db->quote('custom.%')
//);
//$query->delete($db->quoteName('#__user_profiles'));
//$query->where($conditions);

		$this->jsmquery->clear();
        $this->jsmquery = ' DELETE ttn, ttm ';
		$this->jsmquery .= ' FROM #__sportsmanagement_treeto_node AS ttn ';
		$this->jsmquery .= ' LEFT JOIN #__sportsmanagement_treeto_match AS ttm ON ttm.node_id=ttn.id ';
		$this->jsmquery .= ' WHERE ttn.treeto_id = ' . $treeto_id;
		$this->jsmquery .= ';';
		$this->jsmdb->setQuery($this->jsmquery);
		//$this->_db->query($query);
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query dump<br><pre>'.print_r($this->jsmquery,true).'</pre>'),'Error');
sportsmanagementModeldatabasetool::runJoomlaQuery(); 

// Create an object for the record we are going to update.
$object = new stdClass();
// Must be a valid primary key value.
$object->id = $treeto_id;
$object->tree_i = 0;
$object->global_bestof = 1;
$object->global_matchday = 0;
$object->global_known = 0;
$object->global_fake = 0;
$object->mirror = 0;
$object->hide = 0;
$object->leafed = 0;
// Update their details in the users table using id as the primary key.
$result = $this->jsmdb->updateObject('#__sportsmanagement_treeto', $object, 'id');

//		$query = ' UPDATE #__sportsmanagement_treeto AS tt ';
//		$query .= ' SET ';
//		$query .= ' tt.tree_i = 0 ';
//		$query .= ' ,tt.global_bestof = 1 ';
//		$query .= ' ,tt.global_matchday = 0 ';
//		$query .= ' ,tt.global_known = 0 ';
//		$query .= ' ,tt.global_fake = 0 ';
//		$query .= ' ,tt.mirror = 0 ';
//		$query .= ' ,tt.hide = 0 ';
//		$query .= ' ,tt.leafed = 0 ';
//		$query .= ' WHERE tt.id = ' . $treeto_id;
//		$query .= ';';
//		$this->_db->setQuery($query);
//		$this->_db->query($query);

		return true;
	}

	/**
	 * UPDATE selected node as a leaf AND unpublish ALL children node
	 */
	function storeshortleaf($cid,$post)
	{
		$result = true;

		//$app = JFactory::getApplication();
//		$jinput = $app->input;
//		$option = $jinput->getCmd('option');
		$project_id = $this->jsmjinput->get('pid');

		$tree_i = $post['tree_i'];
		$treeto_id = $post['treeto_id'];
		$global_fake = $post['global_fake'];

//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' cid<br><pre>'.print_r($cid,true).'</pre>'),'Error');
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'Error');

		//$db = JFactory::getDbo();

		// if user checked at least ONE node as leaf
		for($x = 0;$x < count($cid);$x ++)
		{
			// leaf(ing) this node
//			$query = $db->getQuery(true);
//			$query->update('#__sportsmanagement_treeto_node');
//			$query->set('is_leaf = 1');
//			$query->where('id=' . $cid[$x]);
//			$db->setQuery($query);
//			$db->execute();

			// find index of checked node
// Create an object for the record we are going to update.
$object = new stdClass();
// Must be a valid primary key value.
$object->id = $cid[$x];
$object->is_leaf = 1 ;
// Update their details in the users table using id as the primary key.
$result = $this->jsmdb->updateObject('#__sportsmanagement_treeto_node', $object, 'id');

			//$db->getQuery(true);
            $this->jsmquery->clear();
			$this->jsmquery->select('node');
			$this->jsmquery->from('#__sportsmanagement_treeto_node');
			$this->jsmquery->where('id=' . $cid[$x]);
			$this->jsmdb->setQuery($this->jsmquery);
			//$db->execute();
			$resultleafnode = $this->jsmdb->loadResult();
			// unpublish children node
			if($resultleafnode < (pow(2,$tree_i)))
			{
				for($y = 1;$y <= ($tree_i - 1);$y ++)
				{
					$childleft = (pow(2,$y)) * $resultleafnode;
					$childright = ((pow(2,$y)) * ($resultleafnode + 1)) - 1;
					for($z = $childleft;$z <= $childright;$z ++)
					{
						if($z < pow(2,$tree_i + 1))
						{
//							$query = $db->getQuery(true);
//							$query->update('#__sportsmanagement_treeto_node');
//							$query->set('published=0');
//							$query->where('(node= ' . $z . ' AND treeto_id = ' . $treeto_id . ')');
//							$db->setQuery($query);
//							$db->execute();

$this->jsmquery->clear();
// Fields to update.
$fields = array(
    $this->jsmdb->quoteName('published') . ' = 0'
);
// Conditions for which records should be updated.
$conditions = array(
    $this->jsmdb->quoteName('node') . ' = ' . $this->jsmdb->quote($z), 
    $this->jsmdb->quoteName('treeto_id') . ' = ' . $this->jsmdb->quote($treeto_id)
);
$this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_treeto_node'))->set($fields)->where($conditions);
$this->jsmdb->setQuery($this->jsmquery);
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'Notice');
sportsmanagementModeldatabasetool::runJoomlaQuery();                             
                            
						}
					}
				}
			}
		}
		// 2, 4, 8, 16, 32, 64 teams, default leaf(ing)
		for($k = pow(2,$tree_i);$k < pow(2,$tree_i + 1);$k ++)
		{
		//	$query = $db->getQuery(true);
//			$query->update('#__sportsmanagement_treeto_node');
//			$query->set('is_leaf=1');
//			$query->where('(node= ' . $k . ' AND treeto_id = ' . $treeto_id . ')');
//			$db->setQuery($query);
//			$db->execute();

$this->jsmquery->clear();
// Fields to update.
$fields = array(
    $this->jsmdb->quoteName('is_leaf') . ' = 1'
);
// Conditions for which records should be updated.
$conditions = array(
    $this->jsmdb->quoteName('node') . ' = ' . $this->jsmdb->quote($k), 
    $this->jsmdb->quoteName('treeto_id') . ' = ' . $this->jsmdb->quote($treeto_id)
);
$this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_treeto_node'))->set($fields)->where($conditions);
$this->jsmdb->setQuery($this->jsmquery);
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'Notice');
sportsmanagementModeldatabasetool::runJoomlaQuery();            
            
		}
		// only for menu
//		$query = $db->getQuery(true);
//		$query->update('#__sportsmanagement_treeto');
//		$query->set('leafed=3');
//		$query->where('id = ' . $treeto_id);
//		$db->setQuery($query);
//		$db->execute();

// Create an object for the record we are going to update.
$object = new stdClass();
// Must be a valid primary key value.
$object->id = $treeto_id;
$object->leafed = 3 ;
// Update their details in the users table using id as the primary key.
$resultupdate = $this->jsmdb->updateObject('#__sportsmanagement_treeto', $object, 'id');

//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' resultupdate<br><pre>'.print_r($resultupdate,true).'</pre>'),'');


		return $result;
	}


	/**
	 *
	 */
	function storefinishleaf($post)
	{
		//$app 	= JFactory::getApplication();
//		$jinput = $app->input;
//		$option = $jinput->getCmd('option');
		//$db 	= JFactory::getDbo();

//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'');

//		$project_id 	= $this->jsmapp->getUserState($this->jsmoption.'project');
//		$tree_i 		= $post['tree_i'];
		$treeto_id 		= $post['treeto_id'];
//		$global_known 	= $post['global_known'];
//		$global_bestof 	= $post['global_bestof'];

//		$query = $db->getQuery(true);
//		$query->update('#__sportsmanagement_treeto');
//		$query->set('leafed = 1');
//		$query->where('id = '.$treeto_id);
//
//		$db->setQuery($query);
//		$db->execute($query);

// Create an object for the record we are going to update.
$object = new stdClass();
// Must be a valid primary key value.
$object->id = $treeto_id;
$object->leafed = 1;
// Update their details in the users table using id as the primary key.
$resultupdate = $this->jsmdb->updateObject('#__sportsmanagement_treeto', $object, 'id');

		return true;
	}


	/**
	 *
	 */
	function getProjectTeamsOptions($project_id=0)
	{
		//$app = JFactory::getApplication();
//		$jinput = $app->input;
//		$option = $jinput->getCmd('option');
		//$project_id = $this->jsmapp->getUserState($this->jsmoption.'.pid');
        
        if ( !$project_id )
        {
        $project_id = $this->jsmjinput->get('pid');
        }
        $this->jsmquery->clear();

		$this->jsmquery->select('pt.id AS value');
        $this->jsmquery->select(' CASE WHEN CHAR_LENGTH(t.name) < 45 THEN t.name ELSE t.middle_name END AS text ');
        $this->jsmquery->from('#__sportsmanagement_team AS t');
				$this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st on t.id = st.team_id');
                $this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id ');      
				//. ' LEFT JOIN #__sportsmanagement_project_team AS pt ON pt.team_id = t.id '
				$this->jsmquery->where('pt.project_id = ' . $project_id);

				$this->jsmquery->order('text ASC');


        
                
		$this->jsmdb->setQuery($this->jsmquery);
		$result = $this->jsmdb->loadObjectList();
		if($result === FALSE)
		{
			JError::raiseError(0,$this->jsmdb->getErrorMsg());
			return false;
		}
		else
		{
			return $result;
		}
	}


	/**
	 *
	 */
	function storeshort($cid,$post)
	{
		//$app = JFactory::getApplication();
//		$jinput = $app->input;
//		$option = $jinput->getCmd('option');
//		$result = true;
//		$post = JFactory::getApplication()->input->get('post');
//		$db = JFactory::getDbo();

		for($x = 0;$x < count($cid);$x ++)
		{
		//	$query = $db->getQuery(true);
//			$query->update('#__sportsmanagement_treeto_node');
//			$query->set('team_id = '.$post['team_id'.$cid[$x]]);
//			$query->where('id = '.$cid[$x]);
//			$db->setQuery($query);
//			if(! $db->execute())
//			{
//				$this->setError($db->getErrorMsg());
//				$result = false;
//			}
            
// Create an object for the record we are going to update.
$object = new stdClass();
// Must be a valid primary key value.
$object->id = $cid[$x];
$object->team_id = $post['team_id'.$cid[$x]] ;
// Update their details in the users table using id as the primary key.
$result = $this->jsmdb->updateObject('#__sportsmanagement_treeto_node', $object, 'id');
            
            
		}
		return $result;
	}
}
