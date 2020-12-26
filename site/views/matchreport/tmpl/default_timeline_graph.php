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
<script src="//unpkg.com/vis-timeline@7.3.7/standalone/umd/vis-timeline-graph2d.min.js"></script>
  <link href="//unpkg.com/vis-timeline@7.3.7/styles/vis-timeline-graph2d.min.css" rel="stylesheet" type="text/css" />
  <style type="text/css">
    #visualization {
      width: 100%;
      height: 400px;
      border: 1px solid lightgray;
    }
  </style>

<div id="visualization"></div>
    
<script type="text/javascript">
  var container = document.getElementById('visualization');
  var items = new vis.DataSet();
  var customDate = new Date();
  var options = {
    start: 1,
    end: 90
  };
  var timeline = new vis.Timeline(container, items, options);


</script>
