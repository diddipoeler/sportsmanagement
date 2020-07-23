<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage matchreport
 * @file       default_timeline_graph.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * https://github.com/visjs/vis-timeline
 * https://unpkg.com/browse/vis-timeline@5.0.0/dist/
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
//echo $this->playgroundheight;
?>
<script type="text/javascript" src="https://unpkg.com/browse/vis-timeline@5.0.0/dist/vis-timeline-graph2d.min.js"></script>
  <link href="https://unpkg.com/browse/vis-timeline@5.0.0/dist/vis-timeline-graph2d.css" rel="stylesheet" type="text/css" />
  <style type="text/css">
    #visualization {
      width: 600px;
      height: 400px;
      border: 1px solid lightgray;
    }
  </style>

