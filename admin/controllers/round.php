<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
 
/**
 * SportsManagement Controller
 */
class sportsmanagementControllerround extends JControllerForm
{

function massadd()
	{
		// Check for request forgeries
		JRequest::checkToken() or die('COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN');
		$post = JRequest::get('post');
		$model = $this->getModel('round');
        $model->massadd();
        
        /*
		$add_round_count=(int)$post['add_round_count'];
		$max=0;
		if ($add_round_count > 0) // Only MassAdd a number of new and empty rounds
		{
			$max=$model->getMaxRound($post['project_id']);
			$max++;
			$i=0;
			for ($x=0; $x < $add_round_count; $x++)
			{
				$i++;
				$post['roundcode']=$max;
				$post['name']=JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_CTRL_ROUND_NAME',$max);
//echo '<pre>'.print_r($post,true).'</pre>';
				if ($model->store($post))
				{
					$msg=JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_CTRL_ROUNDS_ADDED',$i);
				}
				else
				{
					$msg=JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_CTRL_ERROR_ADD').$model->getError();
				}
				$max++;
			}
		}
        
		$link='index.php?option=com_sportsmanagement&view=rounds';
		$this->setRedirect($link,$msg);
        */
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
	}

}
