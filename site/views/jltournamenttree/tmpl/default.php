<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jltournamenttree
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    mod_sportsmanagement_calendar
 */

defined('_JEXEC') or die('Restricted access');

?>

<style type="text/css">
    div.jQBracket {
        font-family: "Arial";
        font-size: <?PHP echo $this->font_size;?>px;
        float: left;
        clear: both;
        position: relative;
        background-color: #333333;
        background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(<?PHP echo $this->color_from;?>), to(<?PHP echo $this->color_to;?>));
        background: -moz-linear-gradient(-90deg, <?PHP echo $this->color_from;?>, <?PHP echo $this->color_to;?>);
    }

    div.jQBracket .round {
        position: relative;
        /* breite der runde: 100*/
        width: <?PHP echo $this->jl_tree_bracket_round_width;?>px;
        margin-right: 40px;
        float: left;
    }

    div.jQBracket .team {
        position: relative;

        vertical-align: middle;
        display: table-cell;

        z-index: 1;
        float: left;
        background-color: #666666;
        color: white;
        /* breite der teams: 100*/
        width: <?PHP echo $this->jl_tree_bracket_round_width;?>px;
        height: 16px;
        border-radius: 5px;
        margin: 1px 0px;
        cursor: default;
        box-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4);
    }

    div.jQBracket .team b {
        font-weight: normal;
        padding-left: 3px;
        cursor: pointer;
        height: inherit;
        position: absolute;
        /* breite des teams b: 70*/
        width: <?PHP echo $this->jl_tree_bracket_teamb_width;?>px;
    }

</style>
<script type="text/javascript">

    jQuery.noConflict();

    jQuery(document).ready(function () {
        var demos = ['save', 'minimal', 'customHandlers', 'autoComplete', 'doubleElimination', 'big']
        demos.forEach(function (d) {
            var demo = jQuery('div#' + d)
            jQuery('<div class="demo"></div>').appendTo(demo)

        })
    })
</script>


<h3>Tournament Tree : <?PHP echo $this->projectname; ?></h3>
<label class="rangePicker">teamWidth: <span>200</span>; <input oninput="resize(this, 'teamWidth')" type="range" min="30"
                                                               max="300" step="1" value="200"/></label>
<label class="rangePicker">scoreWidth: <span>40</span>; <input oninput="resize(this, 'scoreWidth')" type="range"
                                                               min="20" max="100" step="1" value="40"/></label>
<label class="rangePicker">matchMargin: <span>40</span>; <input oninput="resize(this, 'matchMargin')" type="range"
                                                                min="0" max="100" step="1" value="40"/></label>
<label class="rangePicker">roundMargin: <span>100</span>; <input oninput="resize(this, 'roundMargin')" type="range"
                                                                 min="3" max="300" step="1" value="100"/></label>
<div id="big">

    <script type="text/javascript">

        var customRounds = {
            roundsheader: [
				<?PHP echo $this->bracket_rounds;
				?>
            ]
        }
    </script>

    <script type="text/javascript">

        var customData = {
            teams: [
				<?PHP echo $this->bracket_teams;?>
            ],
            results: [
				<?PHP echo $this->bracket_results;?>
            ]
        }

        /* Render function is called for each team label when data is changed, data
	  * contains the data object given in init and belonging to this slot.
	  *
	  * 'state' is one of the following strings:
	  * - empty-bye: No data or score and there won't team advancing to this place
	  * - empty-tbd: No data or score yet. A team will advance here later
	  * - entry-no-score: Data available, but no score given yet
	  * - entry-default-win: Data available, score will never be given as opponent is BYE
	  * - entry-complete: Data and score available
	  */
        function render_fn(container, data, score, state) {
            console.table(container);
            console.log('state : ' + state);
//console.log('name : ' + data.name );
//console.log('flag : ' + data.flag );  
            switch (state) {
                case "empty-bye":
                    container.append("No team")
                    return;
                case "empty-tbd":
                    container.append("Upcoming")
                    return;

                case "entry-no-score":
                case "entry-default-win":
                case "entry-complete":
//console.log('name : ' + data.name );
//console.log('flag : ' + data.flag );
//      container.append('<img src="'+data.flag+'" /> ').append(data.name)
                    return;
            }
        }

        /* Render function is called for each team label when data is changed, data
		* media/com_sportsmanagement/flags/'+data.flag+'.png"
		 * contains the data object given in init and belonging to this slot. */
        /*
		function render_fn(container, data, score) {
		  if (!data.flag || !data.name)
			return
		  container.append('<img src="'+data.flag+'" height="16"/> ').append(data.name)
		}
		*/

        /* Edit function is called when team label is clicked */
        function edit_fn(container, data, doneCb) {
            var input = jQuery('<input type="text">')
            input.val(data.name)
            container.html(input)
            input.focus()
            input.blur(function () {
                doneCb({flag: data.flag, name: input.val()})
            })
        }

        jQuery(document).ready(function () {

            jQuery('div#big .demo').bracket({
                init: customData,
                roundsheader: customRounds,
                jl_tree_bracket_width: <?PHP echo $this->jl_tree_bracket_width;?>,
                decorator: {
                    edit: edit_fn,
                    render: render_fn
                }
            })

        })

        function resize(target, propName) {
            resizeParameters[propName] = parseInt(target.value);
            target.previousElementSibling.textContent = target.value;
            updateResizeDemo();
        }
    </script>

</div>

<script type="text/javascript">
    // These are modified by the sliders
    var resizeParameters = {
        teamWidth: 200,
        scoreWidth: 40,
        matchMargin: 40,
        roundMargin: 100,
        init: customData
    };

    function updateResizeDemo() {
        jQuery('#big .demo').bracket(resizeParameters);
    }

    jQuery(updateResizeDemo)
    jQuery(document).ready(function () {
        var big = jQuery('#big div.demo')
        big.<?PHP echo $this->which_first_round;?>

    })

</script>


