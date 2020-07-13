<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage club
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Environment\Browser;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementViewClub
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewClub extends sportsmanagementView
{

	/**
	 * sportsmanagementViewClub::init()
	 *
	 * @return
	 */
	public function init()
	{
		$this->document->addScript('https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js');
		$starttime  = microtime();
		$this->tmpl = $this->jinput->get('tmpl');

		if ($this->item->latitude != 255)
		{
			$this->googlemap = true;
		}
		else
		{
			$this->googlemap = false;
		}

if ( $this->item->logo_big == 'images/com_sportsmanagement/database/clubs/large/placeholder_150.png' )
{
$this->form->setValue('logo_big','',ComponentHelper::getParams('com_sportsmanagement')->get('ph_logo_big',''));	
$this->item->logo_big = ComponentHelper::getParams('com_sportsmanagement')->get('ph_logo_big','');  
}
if ( $this->item->logo_middle == 'images/com_sportsmanagement/database/clubs/medium/placeholder_50.png' )
{
$this->form->setValue('logo_middle','',ComponentHelper::getParams('com_sportsmanagement')->get('ph_logo_medium',''));	
$this->item->logo_middle = ComponentHelper::getParams('com_sportsmanagement')->get('ph_logo_medium','');  
}		
if ( $this->item->logo_small == 'images/com_sportsmanagement/database/clubs/small/placeholder_small.png' )
{
$this->form->setValue('logo_small','',ComponentHelper::getParams('com_sportsmanagement')->get('ph_logo_small',''));	
$this->item->logo_small = ComponentHelper::getParams('com_sportsmanagement')->get('ph_logo_small','');  
}		
		
		if ($this->item->id)
		{
			/** Alles ok */
			if ($this->item->founded == '0000-00-00')
			{
				$this->item->founded = '';
				$this->form->setValue('founded', '');
			}

			if ($this->item->dissolved == '0000-00-00')
			{
				$this->item->dissolved = '';
				$this->form->setValue('dissolved', '');
			}
		}
		else
		{
			$this->form->setValue('founded', '');
			$this->form->setValue('dissolved', '');
		}

		if ($this->item->latitude == 255)
		{
			$this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_NO_GEOCODE'), 'Error');
			$this->map = false;
		}
		else
		{
			$this->map = true;
		}

		$extended           = sportsmanagementHelper::getExtended($this->item->extended, 'club');
		$this->extended     = $extended;
		$extendeduser       = sportsmanagementHelper::getExtendedUser($this->item->extendeduser, 'club');
		$this->extendeduser = $extendeduser;

		$this->checkextrafields = sportsmanagementHelper::checkUserExtraFields();
		$lists                  = array();

		if ($this->checkextrafields)
		{
			$lists['ext_fields'] = sportsmanagementHelper::getUserExtraFields($this->item->id);
		}

		/** die mannschaften zum verein */
		if ($this->item->id)
		{
			$this->teamsofclub = $this->model->teamsofclub($this->item->id);
		}

		$this->lists = $lists;

		$this->document->addScript('http://maps.googleapis.com/maps/api/js?libraries=places&language=de');
		$this->document->addScript(Uri::base() . 'components/' . $this->option . '/assets/js/geocomplete.js');

		if (version_compare(JSM_JVERSION, '4', 'eq'))
		{
		}
		else
		{
			$this->document->addScript(Uri::base() . 'components/' . $this->option . '/views/club/tmpl/edit.js');
		}

		if (PluginHelper::isEnabled('system', 'jsm_soccerway'))
		{
			$this->document->addScript(Uri::base() . 'components/' . $this->option . '/views/club/tmpl/soccerway.js');
		}

		$params          = ComponentHelper::getParams($this->option);
		$opencagedataapi = $params->get('opencagedata_api_clientid');
        $auto_completion_club_name = $params->get('auto_completion_club_name');
        
        if ( $auto_completion_club_name && $this->item->founded_year && is_numeric($this->item->founded_year) )
		{
		if (preg_match("/".$this->item->founded_year."/i", $this->item->name ))
		{
		/** Es wurde eine Übereinstimmung gefunden */
		}
        else
        {
        $this->item->name = $this->item->name.' '.$this->item->founded_year.' e.V.'; 
        $this->form->setValue('name','', $this->item->name);   
        }  
        }
        
        if ( !$this->item->founded_year )
        {
            $this->item->name = 'kein';
            $this->form->setValue('founded_year','', 'kein');
        }

//$this->app->enqueueMessage(Text::_($this->item->name), 'Error');
//$this->app->enqueueMessage(Text::_($this->item->founded_year), 'Error');

		if ($opencagedataapi)
		{
			$javascript = "\n";
			$javascript .= 'var opencage = "https://api.opencagedata.com/geocode/v1/json?key=' . $opencagedataapi . '&pretty=1&no_annotations=1&q=";' . "\n";
			$javascript .= 'var opencagekey = "' . $opencagedataapi . '";' . "\n";
			$javascript .= "\n";
			$this->document->addScriptDeclaration($javascript);
		}
		else
		{
			$javascript = "\n";
			$javascript .= 'var opencage = "";' . "\n";
			$javascript .= 'var opencagekey = "";' . "\n";
			$javascript .= "\n";
			$this->document->addScriptDeclaration($javascript);
		}
	}

	/**
	 * sportsmanagementViewClub::addToolBar()
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		$this->jinput->set('hidemainmenu', true);
		$isNew      = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_EDIT') : $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_ADD_NEW');
		$this->icon = 'club';
		parent::addToolbar();
	}

}
