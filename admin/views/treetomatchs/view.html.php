<?php
/**
 * @copyright	Copyright (C) 2006-2014 joomleague.at. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the Joomleague component
 *
 * @package	JoomLeague
 * @since	0.1
 */
class JoomleagueViewTreetomatchs extends JLGView
{

	public function init ()
	{
		if ($this->getLayout() == 'editlist')
		{
			$this->_displayEditlist($tpl);
			return;
		}

		if ($this->getLayout()=='default')
		{
			$this->_displayDefault($tpl);
			return;
		}
		parent::display($tpl);
	}

	function _displayEditlist($tpl)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$project_id = $mainframe->getUserState( $option . 'project' );
		$node_id = $mainframe->getUserState( $option . 'node_id' );
		
		$uri = JFactory::getURI();

		$treetomatchs = $this->get('Data');
		$total = $this->get('Total');
		$model = $this->getModel();

		$projectws = $this->get('Data','project');
		$nodews = $this->get('Data','node');
		//build the html select list for node assigned matches
		$ress = array();
		$res1 = array();
		$notusedmatches = array();

		if ($ress =& $model->getNodeMatches($node_id))
		{
			$matcheslist=array();
			foreach($ress as $res)
			{
				if(empty($res1->info))
				{
					$node_matcheslist[] = JHtmlSelect::option($res->value,$res->text);
				}
				else
				{
					$node_matcheslist[] = JHtmlSelect::option($res->value,$res->text.' ('.$res->info.')');
				}
			}

			$lists['node_matches'] = JHtmlSelect::genericlist($node_matcheslist, 'node_matcheslist[]',
	' style="width:250px; height:300px;" class="inputbox" multiple="true" size="'.min(30,count($ress)).'"',
				'value',
				'text');
		}
		else
		{
			$lists['node_matches']= '<select name="node_matcheslist[]" id="node_matcheslist" style="width:250px; height:300px;" class="inputbox" multiple="true" size="10"></select>';
		}

		if ($ress1 =& $model->getMatches())
		{
			if ($ress =& $model->getNodeMatches($node_id))
			{
				foreach ($ress1 as $res1)
				{
					$used=0;
					foreach ($ress as $res)
					{
						if ($res1->value == $res->value){$used=1;}
					}

					if ($used == 0 && !empty($res1->info)){
						$notusedmatches[]=JHtmlSelect::option($res1->value,$res1->text.' ('.$res1->info.')');
					}
					elseif($used == 0 && empty($res1->info))
					{
						$notusedmatches[] = JHtmlSelect::option($res1->value,$res1->text);
					}
				}
			}
			else
			{
				foreach ($ress1 as $res1)
				{
					if(empty($res1->info))
					{
						$notusedmatches[] = JHtmlSelect::option($res1->value,$res1->text);
					}
					else
					{
						$notusedmatches[] = JHtmlSelect::option($res1->value,$res1->text.' ('.$res1->info.')');
					}
				}
			}
		}
		else
		{
			JError::raiseWarning('ERROR_CODE','<br />'.JText::_('COM_JOOMLEAGUE_ADMIN_TREETOMATCH_ADD_MATCH').'<br /><br />');
		}

		//build the html select list for matches
		if (count($notusedmatches) > 0)
		{
			$lists['matches'] = JHtmlSelect::genericlist( $notusedmatches,
				'matcheslist[]',
	' style="width:250px; height:300px;" class="inputbox" multiple="true" size="'.min(30,count($notusedmatches)).'"',
			'value',
			'text');
		}
		else
		{
			$lists['matches'] = '<select name="matcheslist[]" id="matcheslist" style="width:250px; height:300px;" class="inputbox" multiple="true" size="10"></select>';
		}

		unset($res);
		unset($res1);
		unset($notusedmatches);

		$this->assignRef('user',JFactory::getUser());
		$this->assignRef('lists',$lists);
		$this->assignRef('treetomatchs',$treetomatchs);
		$this->assignRef('projectws',$projectws);
		$this->assignRef('nodews',$nodews);
		$this->assignRef('pagination',$pagination);
		$this->assignRef('request_url',$uri->toString());

		parent::display($tpl);
	}

	function _displayDefault($tpl)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$uri = JFactory::getURI();

		$match = $this->get('Data');
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');

		$model = $this->getModel();
		$projectws = $this->get('Data','project');
		$nodews = $this->get('Data','node');

		$this->assignRef('match',$match);
		$this->assignRef('projectws',$projectws);
		$this->assignRef('nodews',$nodews);
		$this->assignRef('total',$total);
		$this->assignRef('pagination',$pagination);
		$this->assignRef('request_url',$uri->toString());

		parent::display($tpl);
	}

}
?>