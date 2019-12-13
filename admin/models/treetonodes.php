<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      treetonodes.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage models
 */
 
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementModelTreetonodes
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2017
 * @version $Id$
 * @access public
 */
class sportsmanagementModelTreetonodes extends JSMModelList
{

	/**
	 * sportsmanagementModelTreetonodes::__construct()
	 * 
	 * @param mixed $config
	 * @return void
	 */
	public function __construct($config = array())
	{
			parent::__construct($config);
        
		$limit = 130;
		$this->setState('limit',$limit);
	}

function savenode($node = NULL)
{
$date = Factory::getDate();
$user = Factory::getUser();
//$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . '<pre>'.print_r($node ,true).'</pre>'  , '');
foreach($node as $key => $value)
{
/** update node */
$object = new stdClass();
$object->id = $value->id;
//$object->title = $this->jsmdb->quote($value->title);
//$object->content = $this->jsmdb->quote($value->content);
$object->title = $value->title;
$object->content = $value->content;	
$object->team_id = $value->team_id;
$object->modified = $date->toSql();
$object->modified_by = $user->get('id');
try {
$result = $this->jsmdb->updateObject('#__sportsmanagement_treeto_node', $object, 'id');
} catch (Exception $e) {
$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . Text::_($e->getMessage()), 'Error');
$result = false;
}
            
/** insert node mit spiel id */            
if ( $value->match_id )
{
$object = new stdClass();            
$object->node_id = $value->id;            
$object->match_id = $value->match_id;
$object->modified = $date->toSql();
$object->modified_by = $user->get('id');            
try {           
$result = $this->jsmdb->insertObject('#__sportsmanagement_treeto_match', $object );            
} catch (Exception $e) {
// $this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . Text::_($e->getMessage()), '');
$result = false;
}
}
	
}
	
}
	
function getteamsprorunde($project_id=0,$treetows = NULL)
{
$matches = array();
/** alle runden */	
$this->jsmquery->clear();
$this->jsmquery->select('*');	
$this->jsmquery->from('#__sportsmanagement_round');
$this->jsmquery->where('project_id = ' . $project_id);	
$this->jsmquery->where('tournement = 1');
$this->jsmdb->setQuery($this->jsmquery);
$roundresult = $this->jsmdb->loadObjectList('id');
//$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . '<pre>'.print_r($roundresult,true).'</pre>'  , '');	

$this->jsmquery->clear();
$this->jsmquery->select('MAX(r.roundcode)');
$this->jsmquery->from('#__sportsmanagement_match AS m');
$this->jsmquery->join('INNER','#__sportsmanagement_round AS r ON r.id = m.round_id');
$this->jsmquery->where('r.project_id = ' . $project_id);
$this->jsmquery->where('r.tournement = 1');
$this->jsmquery->order('r.roundcode DESC');
$this->jsmdb->setQuery($this->jsmquery);
$maxresult = $this->jsmdb->loadResult();
//$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' maxresult <pre>'.print_r($maxresult ,true).'</pre>'  , '');	
$this->jsmquery->clear();
$this->jsmquery->select('MIN(r.roundcode)');
$this->jsmquery->from('#__sportsmanagement_match AS m');
$this->jsmquery->join('INNER','#__sportsmanagement_round AS r ON r.id = m.round_id');
$this->jsmquery->where('r.project_id = ' . $project_id);
$this->jsmquery->where('r.tournement = 1');
$this->jsmquery->order('r.roundcode DESC');
$this->jsmdb->setQuery($this->jsmquery);
$minresult = $this->jsmdb->loadResult();
//$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' minresult <pre>'.print_r($minresult ,true).'</pre>'  , '');
$this->jsmquery->clear();
$this->jsmquery->select('m.id,m.projectteam1_id,m.projectteam2_id,m.next_match_id,m.team_won,r.roundcode');
$this->jsmquery->from('#__sportsmanagement_match AS m');
$this->jsmquery->join('INNER','#__sportsmanagement_round AS r ON r.id = m.round_id');
$this->jsmquery->where('r.project_id = ' . $project_id);
$this->jsmquery->where('r.tournement = 1');
$this->jsmquery->order('r.roundcode DESC');
$this->jsmdb->setQuery($this->jsmquery);
$result = $this->jsmdb->loadObjectList('id');
//$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' tree <pre>'.print_r($treetows->tree_i,true).'</pre>'  , '');	
switch ($treetows->tree_i)
{
case 7:
$mannproroundcode[1] = 128;
$mannproroundcode[2] = 64;
$mannproroundcode[3] = 32;
$mannproroundcode[4] = 16;
$mannproroundcode[5] = 8;
$mannproroundcode[6] = 4;
$mannproroundcode[7] = 2;
$mannproroundcode[8] = 1;
break;		
case 6:
$mannproroundcode[1] = 64;
$mannproroundcode[2] = 32;
$mannproroundcode[3] = 16;
$mannproroundcode[4] = 8;
$mannproroundcode[5] = 4;
$mannproroundcode[6] = 2;
$mannproroundcode[7] = 1;
break;
case 5:
$mannproroundcode[1] = 32;
$mannproroundcode[2] = 16;
$mannproroundcode[3] = 8;
$mannproroundcode[4] = 4;
$mannproroundcode[5] = 2;
$mannproroundcode[6] = 1;
break;
case 4:
$mannproroundcode[1] = 16;
$mannproroundcode[2] = 8;
$mannproroundcode[3] = 4;
$mannproroundcode[4] = 2;
$mannproroundcode[5] = 1;
break;
case 3:
$mannproroundcode[1] = 8;
$mannproroundcode[2] = 4;
$mannproroundcode[3] = 2;
$mannproroundcode[4] = 1;
break;
case 2:
$mannproroundcode[1] = 4;
$mannproroundcode[2] = 2;
$mannproroundcode[3] = 1;
break;		
}
	
foreach ( $result as $key => $value ) if ($value->roundcode == $maxresult)
{
$startneu = $mannproroundcode[$value->roundcode];
$object = new stdClass();	
$object->team_id = $value->projectteam1_id;
$object->match_id = $key;	
$this->jsmquery->clear();
$this->jsmquery->select('t.name');
$this->jsmquery->from('#__sportsmanagement_team AS t');
$this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st on t.id = st.team_id');
$this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id ');      
$this->jsmquery->where('pt.id = ' . $value->projectteam1_id);
$this->jsmdb->setQuery($this->jsmquery);
$object->team_name = $this->jsmdb->loadResult();		
$object->roundcode = $value->roundcode;
$object->next_match_id = $value->next_match_id;
$object->team_won = $value->team_won;			
$matches[$startneu ] = $object;
$mannproroundcode[$value->roundcode] += 1;
$startneu = $mannproroundcode[$value->roundcode];
//$startneu++;
$object = new stdClass();	
$object->team_id = $value->projectteam2_id;
$object->match_id = $key;	
$this->jsmquery->clear();
$this->jsmquery->select('t.name');
$this->jsmquery->from('#__sportsmanagement_team AS t');
$this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st on t.id = st.team_id');
$this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id ');      
$this->jsmquery->where('pt.id = ' . $value->projectteam2_id);
$this->jsmdb->setQuery($this->jsmquery);
$object->team_name = $this->jsmdb->loadResult();		
$object->roundcode = $value->roundcode;
$object->next_match_id = $value->next_match_id;
$object->team_won = $value->team_won;			
$matches[$startneu ] = $object;
$mannproroundcode[$value->roundcode] += 1;
}	
//$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . '<pre>'.print_r($result,true).'</pre>'  , '');

for($i=$maxresult; $i > 0; $i--) {
$startround = $i - 1;
//$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' i <pre>'.print_r($i,true).'</pre>'  , '');
foreach ( $matches as $keymatches => $valuematches ) if ($valuematches->roundcode == $i)
{
//$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' startround <pre>'.print_r($startround ,true).'</pre>'  , '');
//$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' valuematches->team_id<pre>'.print_r($valuematches->team_id,true).'</pre>'  , '');
foreach ( $result as $key => $value ) if ( $value->team_won == $valuematches->team_id && $value->roundcode == $startround )
{

//$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' value<pre>'.print_r($value,true).'</pre>'  , '');

//if ( $value->roundcode == $startround )
//{

$startneu = $mannproroundcode[$value->roundcode];
$object = new stdClass();	
$object->team_id = $value->projectteam1_id;
$object->match_id = $key;	
$this->jsmquery->clear();
$this->jsmquery->select('t.name');
$this->jsmquery->from('#__sportsmanagement_team AS t');
$this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st on t.id = st.team_id');
$this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id ');      
$this->jsmquery->where('pt.id = ' . $value->projectteam1_id);
$this->jsmdb->setQuery($this->jsmquery);
$object->team_name = $this->jsmdb->loadResult();		
$object->roundcode = $value->roundcode;
$object->next_match_id = $value->next_match_id;
$object->team_won = $value->team_won;			
$matches[$startneu ] = $object;
$mannproroundcode[$value->roundcode] += 1;
$startneu = $mannproroundcode[$value->roundcode];
//$startneu++;
$object = new stdClass();	
$object->team_id = $value->projectteam2_id;
$object->match_id = $key;	
$this->jsmquery->clear();
$this->jsmquery->select('t.name');
$this->jsmquery->from('#__sportsmanagement_team AS t');
$this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st on t.id = st.team_id');
$this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id ');      
$this->jsmquery->where('pt.id = ' . $value->projectteam2_id);
$this->jsmdb->setQuery($this->jsmquery);
$object->team_name = $this->jsmdb->loadResult();		
$object->roundcode = $value->roundcode;
$object->next_match_id = $value->next_match_id;
$object->team_won = $value->team_won;			
$matches[$startneu ] = $object;
$mannproroundcode[$value->roundcode] += 1;
//}

	
}

	
}
}
	
//$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' matches <pre>'.print_r($matches,true).'</pre>'  , '');
return $matches;
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
		//$app = Factory::getApplication();

		// Adjust the context to support modal layouts.
		if($layout = $this->jsmjinput->get('layout'))
		{
			$this->context .= '.' . $layout;
		}
	}

	/**
	 * sportsmanagementModelTreetonodes::getStoreId()
	 * 
	 * @param string $id
	 * @return
	 */
	protected function getStoreId($id = '')
	{
		return parent::getStoreId($id);
	}

	/**
	 * sportsmanagementModelTreetonodes::getListQuery()
	 * 
	 * @return
	 */
	protected function getListQuery()
	{

		// Create a new query object.
//		$db = $this->getDbo();
//		$query = $db->getQuery(true);
//		$user = Factory::getUser();
		//$app = Factory::getApplication();
//		$jinput = $app->input;
//		$option = $jinput->getCmd('option');

		$project_id = $this->jsmapp->getUserState($this->jsmoption . '.pid');
		//$treeto_id = $this->jsmapp->getUserState($this->jsmoption . 'treeto_id');
        $treeto_id = $this->jsmjinput->get('tid');

		// Select the required fields from the table.
		$this->jsmquery->select($this->getState('list.select','a.*'));
		$this->jsmquery->from('#__sportsmanagement_treeto_node AS a');

		// join Project-team table
		$this->jsmquery->join('LEFT','#__sportsmanagement_project_team AS pt ON pt.id = a.team_id');
        $this->jsmquery->join('LEFT','#__sportsmanagement_season_team_id AS st on pt.team_id = st.id');  
		// join Team table
		$this->jsmquery->select('t.name AS team_name');
		$this->jsmquery->join('LEFT','#__sportsmanagement_team AS t ON t.id = st.team_id');
		// join treeto table
		$this->jsmquery->select('tt.tree_i AS tree_i');
		$this->jsmquery->join('LEFT','#__sportsmanagement_treeto AS tt ON tt.id = a.treeto_id');
		// join treeto match table
		$this->jsmquery->select('COUNT(ttm.id) AS countmatch');
		$this->jsmquery->join('LEFT','#__sportsmanagement_treeto_match AS ttm ON ttm.node_id = a.id');

		$this->jsmquery->where('a.treeto_id = ' . $treeto_id);

		$this->jsmquery->order('a.row');
		$this->jsmquery->group('a.id');

		return $this->jsmquery;
	}

	
    
	/**
	 * sportsmanagementModelTreetonodes::getMaxRound()
	 * 
	 * @param mixed $project_id
	 * @return
	 */
	function getMaxRound($project_id)
	{
		$result = 0;
		if($project_id > 0)
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('COUNT(roundcode)');
			$query->from('#__sportsmanagement_round');
			$query->where('project_id = ' . $project_id);
			$db->setQuery($query);
			$result = $db->loadResult();
		}
		return $result;
	}



	/**
	 * sportsmanagementModelTreetonodes::setRemoveNode()
	 * 
	 * @param mixed $post
	 * @return
	 */
	function setRemoveNode($post)
	{
		
		$treeto_id = $post['treeto_id'];

		$this->jsmquery->clear();
        $this->jsmquery = ' DELETE ttn, ttm ';
		$this->jsmquery .= ' FROM #__sportsmanagement_treeto_node AS ttn ';
		$this->jsmquery .= ' LEFT JOIN #__sportsmanagement_treeto_match AS ttm ON ttm.node_id=ttn.id ';
		$this->jsmquery .= ' WHERE ttn.treeto_id = ' . $treeto_id;
		$this->jsmquery .= ';';
		$this->jsmdb->setQuery($this->jsmquery);
		//$this->_db->query($query);

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

		return true;
	}

	/**
	 * UPDATE selected node as a leaf AND unpublish ALL children node
	 */
	function storeshortleaf($cid,$post)
	{
		$result = true;
		$project_id = $this->jsmjinput->get('pid');
		$tree_i = $post['tree_i'];
		$treeto_id = $post['treeto_id'];
		$global_fake = $post['global_fake'];

		// if user checked at least ONE node as leaf
		for($x = 0;$x < count($cid);$x ++)
		{
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

sportsmanagementModeldatabasetool::runJoomlaQuery();                             
                            
						}
					}
				}
			}
		}
		// 2, 4, 8, 16, 32, 64 teams, default leaf(ing)
		for($k = pow(2,$tree_i);$k < pow(2,$tree_i + 1);$k ++)
		{
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

sportsmanagementModeldatabasetool::runJoomlaQuery();            
            
		}
		
// Create an object for the record we are going to update.
$object = new stdClass();
// Must be a valid primary key value.
$object->id = $treeto_id;
$object->leafed = 3 ;
// Update their details in the users table using id as the primary key.
$resultupdate = $this->jsmdb->updateObject('#__sportsmanagement_treeto', $object, 'id');




		return $result;
	}


	
	/**
	 * sportsmanagementModelTreetonodes::storefinishleaf()
	 * 
	 * @param mixed $post
	 * @return
	 */
	function storefinishleaf($post)
	{
		
		$treeto_id 		= $post['treeto_id'];

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
	 * sportsmanagementModelTreetonodes::getProjectTeamsOptions()
	 * 
	 * @param integer $project_id
	 * @return
	 */
	function getProjectTeamsOptions($project_id=0)
	{
		        
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
			Log::add( $this->jsmdb->getErrorMsg());
			return false;
		}
		else
		{
			return $result;
		}
	}


	
	/**
	 * sportsmanagementModelTreetonodes::storeshort()
	 * 
	 * @param mixed $cid
	 * @param mixed $post
	 * @return
	 */
	function storeshort($cid,$post)
	{
//$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . '<pre>'.print_r($cid,true).'</pre>', '');		
//$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . '<pre>'.print_r($post,true).'</pre>', '');				
		for($x = 0;$x < count($cid);$x ++)
		{
		            
// Create an object for the record we are going to update.
$object = new stdClass();
// Must be a valid primary key value.
$object->id = $cid[$x];
$object->team_id = $post['team_id'.$cid[$x]] ;
			try {
// Update their details in the users table using id as the primary key.
$result = $this->jsmdb->updateObject('#__sportsmanagement_treeto_node', $object, 'id');
            } catch (Exception $e) {
                $this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . Text::_($e->getMessage()), 'Error');
                $result = false;
            }
            
		}
		return $result;
	}
}
