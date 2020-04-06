<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       sportsmanagement_comments.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage plugins
 */

defined('_JEXEC') or die;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;

/**
 * plgContentSportsmanagement_Comments
 * 
 * @package 
 * @author    Dieter Plöger
 * @copyright 2019
 * @version   $Id$
 * @access    public
 */
class plgContentSportsmanagement_Comments extends JPlugin
{

    public $params = null;
    
    /**
     * plgContentSportsmanagement_Comments::__construct()
     * 
     * @param  mixed $subject
     * @param  mixed $config
     * @return
     */
    public function __construct(&$subject, $config = array())
    {
        $app = Factory::getApplication();
        $jcomments_exists = file_exists(JPATH_SITE.'/components/com_jcomments/jcomments.php');
        
        if (!$jcomments_exists && $app->isSite()) {
            return false;
        }
        
        parent::__construct($subject);
        
        if (isset($config['params'])) {
            if ($config['params'] instanceof Registry) {
                $this->params = $config['params'];
            }
            else
            {
                $this->params = new Registry;
                $this->params->loadString($config['params']);
            }
        }
        
        JPlugin::loadLanguage('plg_sportsmanagement_comments', JPATH_ADMINISTRATOR);
    }

    /**
     * adds comments to match reports
     *
     * @param  object match
     * @param  string title
     * @return boolean true on success
     */
    public function onMatchReportComments(&$match, $title, &$html)
    {
        $separate_comments = $this->params->get('separate_comments', 0);

        if ($separate_comments) {
            $comments = JPATH_SITE.'/components/com_jcomments/jcomments.php';
            if (file_exists($comments)) {
                include_once $comments;
                $html = '<div>'.JComments::show($match->id, 'com_sportsmanagement_matchreport', $title).'</div>';
                return true;
            }
            return false;
        }
    }

    /**
     * adds comments to match preview
     *
     * @param  object match
     * @param  string title
     * @return boolean true on success
     */
    public function onNextMatchComments(&$match, $title, &$html)
    {
        $separate_comments = $this->params->get('separate_comments', 0);

        if ($separate_comments) {
            $comments = JPATH_SITE.'/components/com_jcomments/jcomments.php';
            if (file_exists($comments)) {
                include_once $comments;
                $html = '<div>'.JComments::show($match->id, 'com_sportsmanagement_nextmatch', $title).'</div>';
                return true;
            }
            return false;
        }
    }

    /**
     * adds comments to a match (independent if they were made before or after the match)
     *
     * @param  object match
     * @param  string title
     * @return boolean true on success
     */
    public function onMatchComments(&$match, $title, &$html)
    {
        $separate_comments = $this->params->get('separate_comments', 0);
        
        if ($separate_comments == 0) {

            $comments = JPATH_SITE.'/components/com_jcomments/jcomments.php';
            if (file_exists($comments)) {
                include_once $comments;
                $html = '<div>'.JComments::show($match->id, 'com_sportsmanagement', $title).'</div>';
                return true;
            }
            return false;
        }
    }
}
