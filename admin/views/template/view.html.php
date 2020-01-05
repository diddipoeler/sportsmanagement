<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage template
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementViewTemplate
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewTemplate extends sportsmanagementView
{

	/**
	 * sportsmanagementViewTemplate::init()
	 * 
	 * @return
	 */
	public function init ()
	{
		$lists = array();
		$starttime = microtime();
	
		$this->project_id = $this->app->getUserState( "$this->option.pid", '0' );
		$mdlProject = BaseDatabaseModel::getInstance('Project', 'sportsmanagementModel');
		$project = $mdlProject->getProject($this->project_id);
        
		$templatepath = JPATH_COMPONENT_SITE.DIRECTORY_SEPARATOR.'settings';
		$xmlfile = $templatepath.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.$this->item->template.'.xml';
       
		$form = Form::getInstance($this->item->template, $xmlfile, array('control'=> 'params'));
		$form->bind($this->item->params);
      
		$this->form = $form;
        
        switch ( $this->form->getName() )
        {
            case 'ranking':
            $mdlProjecteams = BaseDatabaseModel::getInstance('Projectteams', 'sportsmanagementModel');
			$iProjectTeamsCount = $mdlProjecteams->getProjectTeamsCount($this->project_id);
			$this->teamscount = $iProjectTeamsCount;
			$this->form->setFieldAttribute('colors_ranking', 'rankingteams' , $iProjectTeamsCount);
            $this->form->setFieldAttribute('colors','type' , 'hidden');
            
            $colors = $this->form->getValue('colors');
            $colors_ranking = $this->form->getValue('colors_ranking');

if ( ComponentHelper::getParams($this->option)->get('show_debug_info_backend') )
{
$this->app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' colors_ranking -> '.TVarDumper::dump($colors_ranking,10,TRUE).''),'');
}
        
            $count = 1;    
            $teile = explode(";", $colors);    

            foreach($teile as $key => $value ) if ( $colors )
            {
            $teile2 = explode(",",$value);    
            if ( !isset($colors_ranking[$count]) )
            {
            $colors_ranking[$count]['von'] = '';
            $colors_ranking[$count]['bis'] = '';
            $colors_ranking[$count]['color'] = '';
            $colors_ranking[$count]['text'] = '';
            }
            
if ( ComponentHelper::getParams($this->option)->get('show_debug_info_backend') )
{
$this->app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' colors_ranking -> '.TVarDumper::dump($colors_ranking,10,TRUE).''),'');
}            
              
            if ( array_key_exists('von', $colors_ranking[$count]) &&
	       array_key_exists('bis', $colors_ranking[$count]) &&
		array_key_exists('color', $colors_ranking[$count]) &&
		array_key_exists('text', $colors_ranking[$count]) 
	       )
            {
            list($colors_ranking[$count]['von'], $colors_ranking[$count]['bis'], $colors_ranking[$count]['color'], $colors_ranking[$count]['text'] ) = $teile2;
            }  
            $count++;  
            }
            $this->form->setValue('colors_ranking', null, $colors_ranking);
            
            break;
        }

		$master_id = ($project->master_template) ? $project->master_template : '-1';
        $templates = array();
        $res = $this->model->getAllTemplatesList($project->id, $master_id);
        $templates = array_merge($templates, $res);
        $lists['templates'] = HTMLHelper::_('select.genericlist',$templates, 
		'new_id', 
		'class="inputbox" size="1" onchange="javascript: Joomla.submitbutton(\'templates.changetemplate\');"', 
		'value', 
		'text', 
		$this->item->id);
        
		$this->template = $this->item;
        
        $this->templatename = $this->form->getName();
		$this->project = $project;
		$this->lists = $lists;
        
/**
 * Load the language files for the contact integration
 */
		$jlang = Factory::getLanguage();
		$jlang->load('com_contact', JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load('com_contact', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load('com_contact', JPATH_ADMINISTRATOR, null, true);

	}
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
        $this->jinput->set('hidemainmenu', true);
        $this->jinput->set('pid', $this->project_id);
        $this->item->name = $this->item->template;
        $this->title = Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_EDIT',(Text::_($this->item->title)));
        $this->icon = 'template';
        parent::addToolbar();
	}
    

    		

}
?>
