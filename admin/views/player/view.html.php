<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage player
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Uri\Uri; 
use Joomla\CMS\Language\Text;
use Joomla\CMS\Environment\Browser;
use Joomla\CMS\Factory;


/**
 * sportsmanagementViewplayer
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2019
 * @version $Id$
 * @access public
 */
class sportsmanagementViewplayer extends sportsmanagementView
{
	
	
	/**
	 * sportsmanagementViewplayer::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
               
/**
 * name für den titel setzen
 */
        $this->item->name = $this->item->lastname.' - '.$this->item->firstname;
        
        $this->form->setValue('sports_type_id', 'request', $this->item->sports_type_id);
        $this->form->setValue('position_id', 'request', $this->item->position_id);
        $this->form->setValue('agegroup_id', 'request', $this->item->agegroup_id);
        
        $this->form->setValue('person_art', 'request', $this->item->person_art);
        $this->form->setValue('person_id1', 'request', $this->item->person_id1);
        $this->form->setValue('person_id2', 'request', $this->item->person_id2);
        
        if ( $this->item->latitude == 255 )
        {
            $this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_NO_GEOCODE'),'Error');
            $this->map = false;
        }
        else
        {
            $this->map = true;
        }
        
	$isNew = $this->item->id == 0;
        if ( $this->item->birthday == '0000-00-00' )
        {
        $this->item->birthday = '';
        $this->form->setValue('birthday','');
        }
	if ( $this->item->deathday == '0000-00-00' )
        {
        $this->item->birthday = '';
        $this->form->setValue('deathday','');
        }
	if ( $this->item->injury_date_start == '0000-00-00' )
        {
        $this->item->injury_date_start = '';
        $this->form->setValue('injury_date_start','');
        }
	if ( $this->item->injury_date_end == '0000-00-00' )
        {
        $this->item->injury_date_end = '';
        $this->form->setValue('injury_date_end','');
        }
        
	if ( $this->item->susp_date_start == '0000-00-00' )
        {
        $this->item->susp_date_start = '';
        $this->form->setValue('susp_date_start','');
        }
	if ( $this->item->susp_date_end == '0000-00-00' )
        {
        $this->item->susp_date_end = '';
        $this->form->setValue('susp_date_end','');
        }
		
	if ( $this->item->away_date_start == '0000-00-00' )
        {
        $this->item->away_date_start = '';
        $this->form->setValue('away_date_start','');
        }
	if ( $this->item->away_date_end == '0000-00-00' )
        {
        $this->item->away_date_end = '';
        $this->form->setValue('away_date_end','');
        }
	
        $extended = sportsmanagementHelper::getExtended($this->item->extended, 'player');
	$this->extended =  $extended;
        $extendeduser = sportsmanagementHelper::getExtendedUser($this->item->extendeduser, 'player');		
	$this->extendeduser = $extendeduser;
	$this->checkextrafields = sportsmanagementHelper::checkUserExtraFields();
	$lists = array();
	if ( $this->checkextrafields )
	{
	$lists['ext_fields'] = sportsmanagementHelper::getUserExtraFields($this->item->id);
        }
        
	$this->lists = $lists;
	unset($lists);
    
	$person_age = sportsmanagementHelper::getAge($this->form->getValue('birthday'),$this->form->getValue('deathday'));
	$person_range = $this->model->getAgeGroupID($person_age);

	if ( $person_range )
 	{
	$this->form->setValue('agegroup_id', null, $person_range);
	}
    
 	$this->document->addScript(Uri::base().'components/'.$this->option.'/assets/js/sm_functions.js');
	
/**
 * Load the language files for the contact integration
 */
	$jlang = Factory::getLanguage();
	$jlang->load('com_contact', JPATH_ADMINISTRATOR, 'en-GB', true);
	$jlang->load('com_contact', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
	$jlang->load('com_contact', JPATH_ADMINISTRATOR, null, true);
        
$this->document->addScript('https://maps.googleapis.com/maps/api/js?libraries=places&language=de');
$this->document->addScript(Uri::base() . 'components/'.$this->option.'/assets/js/geocomplete.js');
$this->document->addScript(Uri::base() . 'components/'.$this->option.'/views/person/tmpl/edit.js');
    
	}
 
	

	/**
	 * sportsmanagementViewplayer::addToolBar()
	 * 
	 * @return void
	 */
	protected function addToolBar() 
	{
	$this->jinput->set('hidemainmenu', true);
	$isNew = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSON_EDIT') : $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSON_NEW');
	$this->icon = 'person';
	parent::addToolbar();
        
	}
    
	
}
