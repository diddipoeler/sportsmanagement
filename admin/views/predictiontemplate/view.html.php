<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictiontemplate
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text; 
use Joomla\CMS\Factory; 

//jimport( 'joomla.application.component.view' );
jimport('joomla.form.form');


/**
 * sportsmanagementViewPredictionTemplate
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementViewPredictionTemplate extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewPredictionTemplate::init()
	 * 
	 * @return
	 */
	public function init ()
	{
//		// Reference global application object
//		$app = Factory::getApplication();
//        // JInput object
//		$jinput = $app->input;
//		$option = $jinput->getCmd('option');
//		$uri = Factory::getURI();
//		$user = Factory::getUser();
//		$app = Factory::getApplication();
//		$model = $this->getModel();
//		$lists = array();
//		$starttime = microtime(); 
		
//        $item = $this->get('Item');
//		$this->item = $item;
        
		$templatepath = JPATH_COMPONENT_SITE.DS.'settings';
		$xmlfile = $templatepath.DS.'default'.DS.$item->template.'.xml';
        
        //$app->enqueueMessage(Text::_('sportsmanagementViewTemplate xmlfile<br><pre>'.print_r($xmlfile,true).'</pre>'),'Notice');
        
		$form = JForm::getInstance($item->template, $xmlfile,array('control'=> 'params'));
		//$form->bind($jRegistry);
		$form->bind($item->params);
        // Assign the Data
		$this->form = $form;
        
		$script = $this->get('Script');
		$this->script = $script;
        
        //$this->prediction_id = $jinput->get('predid', 0, '');
        //$this->prediction_id = $jinput->request->get('predid', 0, 'INT');
		$this->prediction_id = $app->getUserState( "$option.prediction_id", '0' );
        //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' prediction_id<br><pre>'.print_r($this->prediction_id,true).'</pre>'),'Notice');
        //$this->prediction_id = $app->getUserState( "$option.predid", '0' );
//        $predictionGame = $model->getPredictionGame( $this->prediction_id );
		$this->predictionGame = $model->getPredictionGame( $this->prediction_id );

	}

	

  /**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
		
        $jinput = Factory::getApplication()->input;
        $jinput->set('hidemainmenu', true);
        $isNew = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_PREDICTIONTEMPLATE_EDIT') : $this->title = Text::_('COM_SPORTSMANAGEMENT_PREDICTIONTEMPLATE_NEW');
        $this->icon = 'predtemplate';
        
        $this->item->name = $this->item->template;

        parent::addToolbar();

	}		
	
}
?>