<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      router.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage
 */

defined('_JEXEC') or die;
use Joomla\CMS\Component\Router\RouterBase;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

if (!class_exists('sportsmanagementHelperRoute')) {
/**
 * add the classes for handling
 */
JLoader::import('components.com_sportsmanagement.helpers.route', JPATH_SITE);     
}

/**
 * SportsmanagementRouter
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2019
 * @version $Id$
 * @access public
 */
class SportsmanagementRouter extends RouterBase
{
	
	/**
	 * SportsmanagementRouter::build()
     * Build SEF URL
	 * 
	 * @param mixed $query
	 * @return
	 */
	public function build(&$query)
	{
		$segments = array();
		
//echo __METHOD__.' '.__LINE__.' query <pre>'.print_r($query,true).'</pre>';

	// Get menu item
	$menuitem = null;

	if (isset($query ['Itemid']))
	{
		static $menuitems = array();
		$Itemid = $query ['Itemid'] = (int) $query ['Itemid'];

		if (!isset($menuitems[$Itemid]))
		{
			$menuitems[$Itemid] = Factory::getApplication()->getMenu()->getItem($Itemid);

			if (!$menuitems[$Itemid])
			{
				// Itemid doesn't exist or is invalid
				unset($query ['Itemid']);
			}
		}

		$menuitem = $menuitems[$Itemid];
	}	
		
	// Safety check: we need view in order to create SEF URLs
	if (!isset($menuitem->query['view']) && empty($query ['view']))
	{
		return $segments;
	}	
		
	// Get view for later use (query wins menu item)
	$view = isset($query ['view']) ? (string) preg_replace('/[^a-z]/', '', $query ['view']) : $menuitem->query ['view'];	

// Get default values for URI variables 
 	if (isset(sportsmanagementHelperRoute::$views[$view])) 
 	{ 
 		$defaults = sportsmanagementHelperRoute::$views[$view]; 
 	} 
//echo __METHOD__.' '.__LINE__.' defaults <pre>'.print_r($defaults,true).'</pre>';
		
		
		$segments [] = $view;
		unset($query['view']);
	// Check all URI variables and remove those which aren't needed
	foreach ($query as $var => $value)
	{
		//if (isset($defaults [$var]) && !isset($menuitem->query [$var]) ) 
		if (isset($defaults [$var]) ) 
 		{ 
        $segments [] = $value;
 			// Remove URI variable which has default value 
 			unset($query [$var]); 
 		} 
		elseif (isset($menuitem->query [$var]) && $value == $menuitem->query [$var] && $var != 'Itemid' && $var != 'option')
		{
			// Remove URI variable which has the same value as menu item
			unset($query [$var]);
		}
	}	
	
//echo __METHOD__.' '.__LINE__.' query <pre>'.print_r($query,true).'</pre>';
		
		/*
		$segments [] = $view;
		unset($query['view']);
		$segments [] = (int) $query['cfg_which_database'];
		unset($query['cfg_which_database']);
		$segments [] = $query['s'];
		unset($query['s']);
		$segments [] = $query['p'];
		unset($query['p']);
		*/
		
//Factory::getApplication()->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ .' segments <pre>'.print_r($segments,true).'</pre>'), '');		
		
		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   3.3
	 */
	public function parse(&$segments)
	{
		
	// Get current menu item and get query variables from it
	$active = Factory::getApplication()->getMenu()->getActive();
	$vars   = isset($active->query) ? $active->query : array('view' => 'home');

//echo __METHOD__.' '.__LINE__.' segments <pre>'.print_r($segments,true).'</pre>';
//echo __METHOD__.' '.__LINE__.' query <pre>'.print_r($active->query,true).'</pre>';
//echo __METHOD__.' '.__LINE__.' vars <pre>'.print_r($vars,true).'</pre>';		
		
	if (empty($vars['view']) || $vars['view'] == 'home' || $vars['view'] == 'entrypage')
	{
		$vars['view'] = '';
	}
	
// Get default values for URI variables 
 	if (isset(sportsmanagementHelperRoute::$views[$segments[0]])) 
 	{ 
 		$defaults = sportsmanagementHelperRoute::$views[$segments[0]]; 
 	} 
//echo __METHOD__.' '.__LINE__.' defaults <pre>'.print_r($defaults,true).'</pre>';		
		
      
      if ( $defaults )
      {
        $vars['view'] = $segments[0];
      $count = 1;  
      foreach ( $defaults as $key => $value )  
      {
	      /*
      if ( $active->query )  
      {
        
      }
        else
        {
		*/
      //  echo __METHOD__.' '.__LINE__.' key <pre>'.print_r($key,true).'</pre>';		
      $vars[$key] = $segments[$count];  
        //}
      $count++;  
      }
        
        
      }
      
      
      
      
	// Handle all segments
		$count = 0;
	while (($segment = array_shift($segments)) !== null)
	{
		/*
		// Skip //
		if (!$segment)
		{
			//continue;
		}
		
//echo __METHOD__.' '.__LINE__.' segment <pre>'.print_r($segment,true).'</pre>';
		
		switch ($count)
{
case 0:
$vars['view'] = $segment;
break;
case 1:
$vars['cfg_which_database'] = $segment;
break;
case 2:
$vars['s'] = $segment;
break;
case 3:
$vars['p'] = $segment;
break;			
}
		$count++;
		*/
	}
		
		
//Factory::getApplication()->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ .' vars <pre>'.print_r($vars,true).'</pre>'), '');				
		
		return $vars;
	}
}

/**
 * Build the route for the com_banners component
 *
 * This function is a proxy for the new router interface
 * for old SEF extensions.
 *
 * @param   array  &$query  An array of URL arguments
 *
 * @return  array  The URL arguments to use to assemble the subsequent URL.
 *
 * @since   3.3
 * @deprecated  4.0  Use Class based routers instead
 */
function SportsmanagementBuildRoute(&$query)
{
	$router = new SportsmanagementRouter;

	return $router->build($query);
}

/**
 * Parse the segments of a URL.
 *
 * This function is a proxy for the new router interface
 * for old SEF extensions.
 *
 * @param   array  $segments  The segments of the URL to parse.
 *
 * @return  array  The URL attributes to be used by the application.
 *
 * @since   3.3
 * @deprecated  4.0  Use Class based routers instead
 */
function SportsmanagementParseRoute($segments)
{
	$router = new SportsmanagementRouter;

	return $router->parse($segments);
}
