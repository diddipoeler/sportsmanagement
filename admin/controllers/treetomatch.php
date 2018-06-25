<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      treetomatch.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage controllers
 */
 
defined('_JEXEC') or die;


/**
 * sportsmanagementControllerTreetomatch
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2018
 * @version $Id$
 * @access public
 */
class sportsmanagementControllerTreetomatch extends JControllerForm
{

	/**
	 * sportsmanagementControllerTreetomatch::__construct()
	 * 
	 * @param mixed $config
	 * @return void
	 */
	public function __construct($config = array())
	{
	//	$app = JFactory::getApplication();
//		$jinput = $app->input;
//		$jinput->set('layout','form');
        
        parent::__construct($config);
        // Reference global application object
        $this->jsmapp = JFactory::getApplication();
        // JInput object
        $this->jsmjinput = $this->jsmapp->input;
        $this->jsmoption = $this->jsmjinput->getCmd('option');
        $this->jsmdocument = JFactory::getDocument();
	}


    
    
    /**
     * sportsmanagementControllerTreetomatch::save_matcheslist()
     * 
     * @return void
     */
    function save_matcheslist()
    {
        $msg = '';
    $post = $this->jsmjinput->post->getArray();    
    $cid = $this->jsmjinput->get('cid',array(),'array');
    $post['id'] = $this->jsmjinput->get('nid');
    
//    $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' pid<br><pre>'.print_r($post,true).'</pre>'),'Notice');    
//    $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' cid<br><pre>'.print_r($cid,true).'</pre>'),'Notice');
    
		$model = $this->getModel('treetomatchs');
		if($model->store($post))
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_CTRL_SAVED');
		}
		else
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_CTRL_ERROR_SAVE') . $model->getError();
		}

    $link = 'index.php?option=com_sportsmanagement&view=treetomatchs&layout=editlist&nid=' . $this->jsmjinput->get('nid').'&tid='.$this->jsmjinput->get('tid').'&pid='.$this->jsmjinput->get('pid');    
    $this->setRedirect($link,$msg);
    
    
    
    }
    
    
    
}
