<?php


// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

//jimport( 'joomla.application.component.model' );
//require_once ( JPATH_COMPONENT . DS . 'models' . DS . 'item.php' );

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

class sportsmanagementModelTreeto extends JModelAdmin
{
	function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if ( empty( $this->_data ) )
		{
			$query ='	SELECT tt.*
					FROM #__joomleague_treeto AS tt
					WHERE tt.id = ' . (int) $this->_id;

			$this->_db->setQuery( $query );
			$this->_data = $this->_db->loadObject();
			return (boolean) $this->_data;
		}
		return true;
	}

	function _initData()
	{
		// Lets load the content if it doesn't already exist
		if ( empty( $this->_data ) )
		{
			$treeto = new stdClass();
			$treeto->id					= 0;
			$treeto->project_id			= 0;
			$treeto->division_id		= 0;
			$treeto->tree_i				= 0;
			$treeto->name				= null;
			$treeto->global_bestof		= 0;
			$treeto->global_matchday	= 0;
			$treeto->global_known		= 0;
			$treeto->global_fake		= 0;
			$treeto->leafed				= 0;
			$treeto->mirror				= 0;
			$treeto->hide				= 0;
			$treeto->trophypic			= null;
			$treeto->extended			= null;
			$treeto->published			= 0;
			$treeto->checked_out		= 0;
			$treeto->checked_out_time	= 0;
			$treeto->modified			= null;
			$treeto->modified_by		= null;

			$this->_data			= $treeto;
			return (boolean) $this->_data;
		}
		return true;
	}

	function deleteOne($project_id)
	{
		if ($project_id > 0)
		{
			$query='SELECT id FROM #__joomleague_treeto WHERE project_id='.$project_id;
			$this->_db->setQuery($query);
			if (!$result=$this->_db->loadColumn())
			{
				if ($this->_db->getErrorNum() > 0)
				{
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
			$this->delete($result);
		}
		return true;
	}

	function delete(&$pks=array())
	{
		if (count($pks))
		{
			$cids=implode(',',$pks);

			$query= ' DELETE tt, ttn, ttm ';
			$query .= ' FROM #__joomleague_treeto AS tt ';
			$query .= ' LEFT JOIN #__joomleague_treeto_node AS ttn ON ttn.treeto_id=tt.id ';
			$query .= ' LEFT JOIN #__joomleague_treeto_match AS ttm ON ttm.node_id=ttn.id ';
			$query .= ' WHERE tt.id IN (' . $cids . ')';
			$this->_db->setQuery($query);
			if (!$this->_db->query())
			{
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			return parent::delete($pks);
		}
		return true;
	}

	function setGenerateNode()
	{
		$post	= 			JRequest::get( 'post' );
		$treeto_id = 		(int) $post['id'];
		$tree_i = 			(int) $post['tree_i'];
		$global_bestof = 	(int) $post['global_bestof'];
		$global_matchday = 	(int) $post['global_matchday'];
		$global_known = 	(int) $post['global_known'];
		$global_fake = 		(int) $post['global_fake'];
		if($tree_i == 0) //nothing selected in dropdown
		{
			return false;
		}
		elseif($tree_i > 0)
		{
			//data(global parameters) to treeto
			$query = ' UPDATE #__joomleague_treeto AS tt ';
			$query .= ' SET ';
			$query .= ' global_bestof = '. $global_bestof ;
			$query .= ' ,global_matchday = '. $global_matchday ;
			$query .= ' ,global_known = '. $global_known ;
			$query .= ' ,global_fake = '. $global_fake ;
			$query .= ' ,leafed = '. 2 ;
			$query .= ' ,tree_i = '. $tree_i ;
			$query .= ' WHERE tt.id = ' . $treeto_id ;
			$query .= ';';
			$this->_db->setQuery( $query );
			$this->_db->query( $query );
			// nodes to treeto_node
			for($nod=1;$nod<=((pow(2,$tree_i+1))-1);$nod++)
			{
				$i=$tree_i;
				$x=$nod;
				$ii=pow(2,$i);
				$row=$ii;

				while($x>1)
				{
					if($x>=(pow(2,$i)))
					{
						if(($x)%2==1)
						{
							$row+=$ii*(1/(pow(2,$i)));
							$i--;
						}
						else
						{
							$row-=$ii*(1/(pow(2,$i)));
							$i--;
						}
						$x=floor($x/2);
					}
					else
					{
						$i--;
					}
				}
				$query = ' INSERT INTO #__joomleague_treeto_node ';
				$query .= ' SET ';
				$query .= ' treeto_id = ' . $treeto_id ;
				$query .= ' ,node = ' . $nod ;
				$query .= ' ,row = ' . $row ;
				$query .= ' ,bestof = ' . $global_bestof ;
				$query .= ';';
				$this->_db->setQuery( $query );
				$this->_db->query( $query );
			}
			return true;
		}
	}
	/**
	 * Returns a Table object, always creating it
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'treeto', $prefix = 'table', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.7
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_sportsmanagement.'.$this->name, $this->name,
				array('load_data' => $loadData) );
		if (empty($form))
		{
			return false;
		}
		return $form;
	}

	public function getScript()
	{
		return 'administrator/components/com_sportsmanagement/models/forms/treeto.js';
	}
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.7
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.'.$this->name.'.data', array());
		if (empty($data))
		{
			$data = $this->getData();
		}
		return $data;
	}
}
?>