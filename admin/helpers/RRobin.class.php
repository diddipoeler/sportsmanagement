<?php
/**
 * Porter-Berger round-robin schedule helper used by SportsManagement.
 *
 * Original work Copyright (c) Nicholas Mossor Rathmann
 * <nicholas.rathmann@gmail.com> 2008. All Rights Reserved.
 * Modified by julien.vonthron@gmail.com and gladiator.sp@gmail.com.
 *
 * @version    4.24.00
 * @author     Nicholas Mossor Rathmann, Julien Vonthron, gladiator.sp@gmail.com
 * @copyright  Copyright (c) 2008 Nicholas Mossor Rathmann and contributors
 * @license    GNU General Public License version 3 or later
 */
defined('_JEXEC') or die('Restricted access');

/**
 * RRobin
 *
 * @package
 * @author    Nicholas Mossor Rathmann
 * @copyright 2008
 * @version   $Id$
 * @access    public
 */
class RRobin
{

	/* Properties */

	public $tour = array();
	public $tot_games = 0;

	// Pair 2->n/2.
	private $upper = array();
	private $lower = array();
	// Pair 1.
	private $fixed = 0;     // Locked competitor.
	private $rot_out = 1;   // Rotated out competitor to play against locked competitor.

	/* Methods */

	/**
	 * Generate the initial Berger round-robin schedule for a competitor list.
	 * Resets previous rounds on a successful new request, creates a ghost slot
	 * for odd team counts and rotates the pairing grid once per round.
	 * Invalid input leaves the previous schedule unchanged.
	 *
	 * @param array $list Zero-indexed competitor list with at least three entries.
	 * @return bool True when a new schedule was generated.
	 */
	public function create($list = array())
	{

		// Test input
		if (!is_array($list))
		{
			return false;
		}
		$n = count($list);
		if ($n < 3)
		{
			return false;
		}

		// A reused generator must not retain rounds from a larger schedule.
		$this->tour = array();
		$this->tot_games = ($n / 2) * ($n - 1);

		// Initial array content
		// Gladiator 20-03-2009
		//
		if ($n % 2)
		{ // Odd

			$this->lower = array();
			$this->upper = array();

			$n++;

			for ($i = 0; $i < ($n / 2) - 1; $i++)
			{
				array_push($this->upper, $this->calcUpperElement($i, $n));
			}

			for ($i = 0; $i < ($n / 2) - 1; $i++)
			{
				array_push($this->lower, $this->calcLowerElement($i, $n));
			}

			$this->fixed   = -1; // fixed player as ghost player
			$this->rot_out = 0;
		}
		else
		{ // Even

			$this->lower = array();
			$this->upper = array();

			for ($i = 0; $i < ($n / 2) - 1; $i++)
			{
				array_push($this->upper, $this->calcUpperElement($i, $n));
			}

			for ($i = 0; $i < ($n / 2) - 1; $i++)
			{
				array_push($this->lower, $this->calcLowerElement($i, $n));
			}

			$this->fixed   = $n - 1;
			$this->rot_out = 0;
		}

		// Generate games
		// Gladiator 20-03-2009
		//
		for ($round = 0; $round < $n - 1; $round++)
		{
			$this->tour[$round] = array();
			if ($this->rot_out != -1) // If not ghost player.
			{
				if ($this->fixed != -1)
				{
					if ($this->rot_out >= $n / 2) // if rot_out has white color => is supposed to play home
					{
						array_push($this->tour[$round], array($list[$this->rot_out], $list[$this->fixed]));
					}
					else
					{
						array_push($this->tour[$round], array($list[$this->fixed], $list[$this->rot_out]));
					}
				}
				else
				{
					array_push($this->tour[$round], array($list[$this->rot_out]));
				}
			}
			for ($i = 0; $i < count($this->upper); $i++)
			{
				if ($this->upper[$i] != -1 && $this->lower[$i] != -1) // If not ghost player.
				{
					if ($i % 2 == 0)
					{
						array_push($this->tour[$round], array($list[$this->upper[$i]], $list[$this->lower[$i]]));
					}
					else
					{
						array_push($this->tour[$round], array($list[$this->lower[$i]], $list[$this->upper[$i]]));
					}
				}
			}

			$this->rotate();
		}

		return true;
	}

	// Rotate arrays

	/**
	 * Calculate an upper-row competitor index for the Berger pairing grid.
	 * Handles even and odd grid positions to generate the initial round-robin rotation.
	 * @param int $i Zero-based position within the upper row.
	 * @param int $n Even number of slots including a possible bye.
	 * @return int Index into the original competitor list or ghost slot.
	 */
	private function calcUpperElement($i, $n)
	{
		// "i" Even:
		if ($i % 2 != 1)
		{
			$x = $n / 2;
			$y = $i / 2;
		}
		// "i" Odd
		else if ($i % 2 == 1)
		{
			$x = $n;
			$y = ($i + 1) / 2;
		}

		return ($x - $y - 1);
	}

	// Gladiator 20-03-2009

	/**
	 * Calculate the complementary lower-row index in the Berger grid.
	 * Alternates the pairing arithmetic between odd and even positions.
	 * @param int $i Zero-based position within the lower row.
	 * @param int $n Even number of slots including a possible bye.
	 * @return int Opposing competitor index or ghost slot.
	 */
	private function calcLowerElement($i, $n)
	{
		// "i" Even
		if ($i % 2 != 1)
		{
			$x = $n / 2;
			$y = ($i / 2) + 1;
		}
		// "i" Odd
		else if ($i % 2 == 1)
		{
			$x = 1;
			$y = ($i + 1) / 2;
		}

		return ($x + $y - 1);
	}

	// Gladiator 20-03-2009

	/**
	 * Advance the pairing grid to the next round-robin round.
	 * Rotates the two competitor rows around the fixed opponent while maintaining slot order.
	 * @return void Rotation updates the helper's internal state.
	 */
	private function rotate()
	{
		array_unshift($this->upper, $this->rot_out);         // insert rot_out at the beginig of upper
		array_push($this->lower, array_pop($this->upper));  // pop element from upper and push it on lower
		$this->rot_out = array_shift($this->lower);         // rot_out = last element of lower
	}

	/**
	 * Build a complete competition schedule for the requested number of legs.
	 * Concatenates each leg's rounds, reversing home/away pairings on alternate legs.
	 * @param int $nb_legs Number of legs to create (one or more).
	 * @return array Generated match rounds for all requested legs.
	 */
	function getSchedule($nb_legs = 1)
	{
		$result = array();
		for ($i = 0; $i < $nb_legs; $i++)
		{
			$result = array_merge($result, $this->getLeg($i));
		}

		return $result;
	}

	/**
	 * Build the match rounds for a single leg of a competition.
	 * Reverses match direction and round order on odd-numbered return legs.
	 * @param int $leg_number Zero-based leg index.
	 * @return array Scheduled rounds for the requested leg.
	 */
	function getLeg($leg_number)
	{
		$nb_single_rounds = count($this->tour);
		$leg              = array();
		foreach ($this->tour AS $k => $round)
		{
			foreach ($round as $game)
			{
				if ($leg_number % 2)
				{ // return leg, reverse games
					$leg[$k + $nb_single_rounds * $leg_number][] = array_reverse($game);
				}
				else
				{
					$leg[$k + $nb_single_rounds * $leg_number][] = $game;
				}
			}
		}

		if ($leg_number % 2)
		{ // reverse rounds in leg
			$leg = array_reverse($leg);
		}

		return $leg;
	}
}
