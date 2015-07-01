<?php


defined('_JEXEC') or die();

class sportsmanagementControllerjsmgcalendarImport extends JControllerLegacy 
{

/**
	 * Class Constructor
	 *
	 * @param	array	$config		An optional associative array of configuration settings.
	 * @return	void
	 * @since	1.5
	 */
	function __construct($config = array())
	{
	   // Initialise variables.
		$app = JFactory::getApplication();
		parent::__construct($config);

	$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getTask<br><pre>'.print_r($this->getTask(),true).'</pre>'),'Notice');
	}
    
	public function import() 
    {
    $msg = JText::_( 'COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_IMPORT' );
	$this->setRedirect( 'index.php?option=com_sportsmanagement&view=jsmgcalendarimport&layout=login', $msg );    
    }    
    
    
    public function login() 
    {
    $model = $this->getModel('jsmgcalendarImport');
    //$model->login();
    $model->import();
        
        $msg = JText::_( 'COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_IMPORT' );
	$this->setRedirect( 'index.php?option=com_sportsmanagement&view=jsmgcalendarimport&layout=login', $msg );
    }

    
    public function save() 
    {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('jsmgcalendarImport');

		if ($model->store()) {
			$msg = JText::_('JLIB_APPLICATION_SAVE_SUCCESS');
		} else {
			$msg = JText::_('JLIB_APPLICATION_ERROR_SAVE_FAILED');
		}

		$link = 'index.php?option=com_sportsmanagement&view=jsmgcalendars';
		$this->setRedirect($link, $msg);
	}

	public function cancel() 
    {
		$msg = JText::_( 'Operation cancelled' );
		$this->setRedirect( 'index.php?option=com_sportsmanagement&view=jsmgcalendars', $msg );
	}
    
}