<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.00
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_new_project
 * @file       helper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;

JLoader::import('components.com_sportsmanagement.helpers.countries', JPATH_SITE);
JLoader::import('components.com_sportsmanagement.helpers.route', JPATH_SITE);

/**
 * Check for component
 */
if ( ComponentHelper::getComponent('com_autotweet', true)->enabled )
{
include_once JPATH_ADMINISTRATOR . '/components/com_autotweet/helpers/autotweetbase.php';
//include_once JPATH_SITE . '/plugins/system/autotweetcontent/autotweetcontent.php';
//$mdl     = BaseDatabaseModel::getInstance("AutotweetContent", "PlgSystem");  
$plugin = PluginHelper::getPlugin('system', 'autotweetcontent');  
  
  
//Factory::getApplication()->enqueueMessage( '<pre>'. print_r($plugin,true) . '</pre>', 'notice');      
  
}  


/**
 * modJSMNewProjectHelper
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2015
 * @version   $Id$
 * @access    public
 */
class modJSMNewProjectHelper
{

	/**
	 * modJSMNewProjectHelper::getData()
	 *
	 * @return
	 */
	public static function getData($new_project_article, $mycategory)
	{
		// Reference global application object
		$app    = Factory::getApplication();
		$date   = Factory::getDate();
		$user   = Factory::getUser();
		$db     = Factory::getDBO();
		$query  = $db->getQuery(true);
		$result = array();

		$heutestart = date("Y-m-d");
		$heuteende  = date("Y-m-d");

		$heutestart .= ' 00:00:00';
		$heuteende  .= ' 23:59:00';

		$query->select('pro.id,pro.name,pro.current_round as roundcode,CONCAT_WS(\':\',pro.id,pro.alias) AS project_slug,le.name as liganame,le.country');
		$query->select('le.picture as league_picture,pro.picture as project_picture');
		$query->from('#__sportsmanagement_project as pro');
		$query->join('INNER', '#__sportsmanagement_league as le on le.id = pro.league_id');
		$query->where('pro.modified BETWEEN ' . $db->Quote('' . $heutestart . '') . ' AND ' . $db->Quote('' . $heuteende . ''));
        $query->where('pro.published = 1');
		$query->order('pro.name ASC');

		$db->setQuery($query);
		$anzahl = $db->loadObjectList();

		foreach ($anzahl as $row)
		{
          
          //$app->enqueueMessage($row->roundcode, 'notice');
          //$app->enqueueMessage( 'komponente '.   ComponentHelper::getComponent('com_autotweet', true)->enabled, 'notice');
          
			if ($row->roundcode)
			{
				$query->clear();
				$query->select('r.name,CONCAT_WS(\':\',r.id,r.alias) AS round_slug');
				$query->from('#__sportsmanagement_round as r');
				$query->where('r.project_id = ' . $row->id);
				$query->where('r.id = ' . $row->roundcode);

				$db->setQuery($query);

				$result2        = $db->loadObject();
				$row->roundcode = $result2->round_slug;
			}

			$temp            = new stdClass;
			$temp->name      = $row->name;
			$temp->liganame  = $row->liganame;
			$temp->roundcode = $row->roundcode;

			// $temp->id = $row->id;
			$temp->id      = $row->project_slug;
			$temp->country = $row->country;
			$result[]      = $temp;
			$result        = array_merge($result);

			/** soll ein artikel erstellt werden ? */
			if ($new_project_article)
			{
				$query->clear();
				$query->select('id');
				$query->from('#__content');
				//$query->where('title LIKE ' . $db->Quote('' . $row->name . '') );
				$query->where('xreference = ' . $row->id );
				$query->where('created BETWEEN ' . $db->Quote('' . $heutestart . '') . ' AND ' . $db->Quote('' . $heuteende . ''));
				$db->setQuery($query);
				$article = $db->loadObject();

				if ($article)
				{
                 // $app->enqueueMessage($article->id, 'notice');
				}
				else
				{
					// Create and populate an object.
					$profile           = new stdClass;
					$profile->title    = $row->name;
					$profile->alias    = OutputFilter::stringURLSafe($row->name);
					$profile->catid    = $mycategory;
					$profile->xreference = $row->id;
					$profile->state    = 1;
					$profile->access   = 1;
					$profile->featured = 1;
					$profile->language = '*';

					$profile->created     = $date->toSql();
					//$profile->created_by  = $user->get('id');
                  $profile->created_by  = 62;
					$profile->modified    = $date->toSql();
					$profile->modified_by = $user->get('id');
/*
					$createroute = array("option"             => "com_sportsmanagement",
					                     "view"               => "resultsranking",
					                     "cfg_which_database" => 0,
					                     "s"                  => 0,
					                     "p"                  => $row->id,
					                     "r"                  => $row->roundcode);

					$query = sportsmanagementHelperRoute::buildQuery($createroute);
					$link  = Route::_('index.php?' . $query, false);
                    */
$routeparameter                       = array();
				$routeparameter['cfg_which_database'] = $app->input->getInt('cfg_which_database', ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database', 0));
				$routeparameter['s']                  = $app->input->getInt('s', 0);
				$routeparameter['p']                  = $row->id;
				$routeparameter['r']                  = $row->roundcode;
				$routeparameter['division']           = 0;
				$routeparameter['mode']               = 0;
				$routeparameter['order']              = 0;
				$routeparameter['layout']             = 0;
				$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute("resultsranking", $routeparameter);                    
                    
                    
if ( !$row->project_picture )
{
$row->project_picture = ComponentHelper::getParams('com_sportsmanagement')->get('ph_project', '');	
}
if ( !$row->league_picture )
{
$row->league_picture = ComponentHelper::getParams('com_sportsmanagement')->get('ph_project', '');	
}					
                    

					$profile->introtext = '<p><a href="' . $link . '">
<img src="' . $row->league_picture . '" alt="' . $row->liganame . '" style="float: left;" width="100" height="auto" />
' . $row->name . ' - ( ' . $row->liganame . ' )</a> neu angelegt/aktualisiert.
<img src="' . $row->project_picture . '" alt="' . $row->name . '" style="float: right;" width="100" height="auto" />
</p>';

					$profile->publish_up = $date->toSql();

					$resultinsert = Factory::getDbo()->insertObject('#__content', $profile);

					if ($resultinsert)
					{
						// Create and populate an object.
                      $article_id = $db->insertid();
                      //$app->enqueueMessage( '<pre>'. print_r($article_id,true) . '</pre>', 'notice');                        
						$profile             = new stdClass;
						$profile->content_id = $article_id;
						$profile->ordering   = $article_id;
						$resultfrontpage     = Factory::getDbo()->insertObject('#__content_frontpage', $profile);
                        
                      
if ( ComponentHelper::getComponent('com_autotweet', true)->enabled )
{                        
$query->clear();
$query->select('*');
$query->from('#__content');
$query->where('id = ' . $article_id );
$db->setQuery($query);
$article = $db->loadObject();                        
//$app->enqueueMessage( '<pre>'. print_r($article,true) . '</pre>', 'notice');    
  
//$mdl     = BaseDatabaseModel::getInstance("AutotweetContent", "PlgSystem");  
Factory::getApplication()->triggerEvent('onContentAfterSave', array('com_content.article',$article,1) ); // postArticle
//Factory::getApplication()->triggerEvent('postArticle', array($article) );
  
  /*
$autotweet_insert             = new stdClass;
$autotweet_insert->ref_id = $article_id; 
$autotweet_insert->plugin = 'autotweetcontent';
$autotweet_insert->message = $article->title;
$autotweet_insert->title = $article->title;  
$autotweet_insert->fulltext = $article->introtext;
$autotweet_insert->postdate = $date->toSql();  
$autotweet_insert->channel_id = 1;
$result_tweet_insert     = Factory::getDbo()->insertObject('#__autotweet_posts', $autotweet_insert);  
  */
  
  
  /*
$autotweet_insert = new stdClass;
$autotweet_insert->ref_id = $article_id; 
$autotweet_insert->plugin = 'autotweetcontent';  
$autotweet_insert->priority = 9;  
$autotweet_insert->publish_up = $date->toSql();   
$autotweet_insert->description = $article->title;
$autotweet_insert->typeinfo = 1;
//$autotweet_insert->url = ;
$autotweet_insert->image_url = Uri::root().$row->league_picture;
$autotweet_insert->native_object = json_encode($article);
$autotweet_insert->created = $date->toSql();
$autotweet_insert->created_by = 62;
$autotweet_insert->published = 1;
$autotweet_insert->checked_out_time = '0000-00-00 00:00:00';
  
$result_tweet_insert = Factory::getDbo()->insertObject('#__autotweet_requests', $autotweet_insert);    
*/  
  
/*
  parent::onContentAfterSave($context, $article, $isNew);

		if ((($context == 'com_content.article'
  */
}                       
                        
					}
				}
			}
		}

		return $result;

	}


}
