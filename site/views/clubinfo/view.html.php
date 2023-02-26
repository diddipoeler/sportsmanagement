<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 *
 * boostrap tree
 * http://jsfiddle.net/jhfrench/GpdgF/
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * sportsmanagementViewClubInfo
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewClubInfo extends sportsmanagementView
{

	/**
	 * sportsmanagementViewClubInfo::init()
	 *
	 * @return void
	 */
	function init()
	{
	   
		$this->checkextrafields = sportsmanagementHelper::checkUserExtraFields('frontend', sportsmanagementModelClubInfo::$cfg_which_database,Factory::getApplication()->input->get('view'));
		$this->mapconfig = array();
		$this->club = sportsmanagementModelClubInfo::getClub(1);

		if ($this->checkextrafields)
		{
			$this->extrafields = sportsmanagementHelper::getUserExtraFields($this->club->id, 'frontend', sportsmanagementModelClubInfo::$cfg_which_database,Factory::getApplication()->input->get('view'));
		}

		$lat = '';
		$lng = '';

		$this->clubassoc = sportsmanagementModelClubInfo::getClubAssociation($this->club->associations);
		$this->extended  = sportsmanagementHelper::getExtended($this->club->extended, 'club', 'ini', true);
		$this->teams     = sportsmanagementModelClubInfo::getTeamsByClubId($this->config['show_teams_of_club']);

		if (sportsmanagementModelClubInfo::$projectid)
		{
			$this->stadiums    = sportsmanagementModelClubInfo::getStadiums();
			$this->playgrounds = sportsmanagementModelClubInfo::getPlaygrounds();
		}

		$this->showediticon   = sportsmanagementModelProject::hasEditPermission('club.edit');
		$this->address_string = sportsmanagementModelClubInfo::getAddressString();

		if ($this->config['show_club_rssfeed'])
		{
			$mod_name     = "mod_jw_srfr";
			$rssfeeditems = '';

			$rssfeedlink = $this->extended['COM_SPORTSMANAGEMENT_CLUB_RSS_FEED'];

			if ($rssfeedlink)
			{
				$this->rssfeeditems = sportsmanagementModelClubInfo::getRssFeeds($rssfeedlink, $this->overallconfig['rssitems']);
			}
			else
			{
				$this->rssfeeditems = $rssfeeditems;
			}
		}

		if ($this->config['show_maps'])
		{
			/** leaflet benutzen */
			if ($this->config['use_which_map'])
			{
				$this->mapconfig = sportsmanagementModelProject::getTemplateConfig('map', $this->jinput->getInt('cfg_which_database', 0));
			}

			/** kml file generieren */
			if ($this->mapconfig['map_kmlfile'])
			{
				$this->geo = new JSMsimpleGMapGeocoder;
				$this->geo->genkml3file($this->club->id, $this->address_string, 'club', $this->club->logo_big, $this->club->name, $this->club->latitude, $this->club->longitude);
			}
		}

		$this->show_debug_info = ComponentHelper::getParams($this->option)->get('show_debug_info', 0);

		$pageTitle = Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_PAGE_TITLE');

		if (isset($this->club))
		{
			$pageTitle .= ': ' . $this->club->name;
		}

		$this->modid = $this->club->id;
		/** clubhistory	 */
		$this->clubhistory     = sportsmanagementModelClubInfo::getClubHistory($this->club->id);
		$this->clubhistoryhtml = sportsmanagementModelClubInfo::getClubHistoryHTML($this->club->id);
        
        if ( $this->club->new_club_id )
        {
        sportsmanagementModelClubInfo::$club = NULL;    
        $this->new_club = sportsmanagementModelClubInfo::getClub(0,$this->club->new_club_id);  
          
        $this->clubhistoryhtml = '<ul>';  
          
          
        $link       = sportsmanagementHelperRoute::getClubInfoRoute($this->project->id, $this->new_club->id, null, Factory::getApplication()->input->getInt('cfg_which_database', 0) );  
        $imageTitle = Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_HISTORY_FROM');
        $this->clubhistoryhtml .= HTMLHelper::_('image', 'media/com_sportsmanagement/jl_images/club_from.png', $imageTitle, 'title= "' . $imageTitle . '"');
		$this->clubhistoryhtml .= "&nbsp;";
		$this->clubhistoryhtml .= HTMLHelper::link($link,  $this->new_club->name);
        $this->clubhistoryhtml .= '<ul>';  
        $this->clubhistoryhtml .= sportsmanagementModelClubInfo::getClubHistoryHTML($this->club->new_club_id);
        $this->clubhistoryhtml .= '</ul>';  
        $this->clubhistoryhtml .= '</ul>';  
        
//echo __LINE__.' clubhistoryhtml<pre>'.print_r($this->clubhistoryhtml,true).'</pre>';
//echo 'new_club<pre>'.print_r($this->new_club,true).'</pre>';
        }

		$this->clubhistoryfamilytree = sportsmanagementModelClubInfo::fbTreeRecurse($this->club->id, '', array(), sportsmanagementModelClubInfo::$tree_fusion, 10, 0, 1);
		$this->genfamilytree         = sportsmanagementModelClubInfo::generateTree($this->club->id, $this->config['show_bootstrap_tree']);
		$this->familytree            = sportsmanagementModelClubInfo::$historyhtmltree;

		/** clubhistorytree	 */
		$this->clubhistorytree     = sportsmanagementModelClubInfo::getClubHistoryTree($this->club->id, $this->club->new_club_id);
		$this->clubhistorysorttree = sportsmanagementModelClubInfo::getSortClubHistoryTree($this->clubhistorytree, $this->club->id, $this->club->name);

		$historyobj = sportsmanagementModelClubInfo::$historyobj;

		if (!$historyobj)
		{
			$this->clubhistorysorttree = '';
		}

		if ($this->config['show_bootstrap_tree'])
		{
			$this->document->addStyleSheet(Uri::base() . 'components/' . $this->option . '/assets/css/bootstrap-familytree.css');
		}
		else
		{
			$javascript = "\n";
			$javascript .= "
jQuery(function ($) {
    $('.tree li:has(ul)').addClass('parent_li').find(' > span').attr('title', 'Collapse this branch');
    $('.tree li.parent_li > span').on('click', function (e) {
        var children = $(this).parent('li.parent_li').find(' > ul > li');
        if (children.is(\":visible\")) {
            children.hide('fast');
            $(this).attr('title', 'Expand this branch').find(' > i').addClass('icon-plus-sign').removeClass('icon-minus-sign');
        } else {
            children.show('fast');
            $(this).attr('title', 'Collapse this branch').find(' > i').addClass('icon-minus-sign').removeClass('icon-plus-sign');
        }
        e.stopPropagation();
    });
});


";

			$this->document->addScriptDeclaration($javascript);
			$this->document->addStyleSheet(Uri::base() . 'components/' . $this->option . '/assets/css/bootstrap-tree2.css');
		}

		$this->document->setTitle($pageTitle);

		if (!isset($this->config['table_class']))
		{
			$this->config['table_class'] = 'table';
		}
		
		
        

	}
}
