<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');
 
/**
 * SportsManagements Controller
 */
class sportsmanagementControllerrounds extends JControllerAdmin
{
	
  
  function massadd()
	{
		/*
        // Check for request forgeries
		JRequest::checkToken() or die('COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN');
		$post = JRequest::get('post');
		$model = $this->getModel('round');
        $model->massadd();
        */
        
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
        
        $model = $this->getModel();
       $model->massadd();
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
	}
    
  /**
	 * Method to update checked rounds
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
    function saveshort()
	{
	   $model = $this->getModel();
       $model->saveshort();
       $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
    } 
  
  
  /**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'Round', $prefix = 'sportsmanagementModel') 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	


	
}