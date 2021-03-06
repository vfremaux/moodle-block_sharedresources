<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version details.
 *
 * @package    block_sharedresources
 * @category   blocks
 * @copyright  2008 Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

<<<<<<< HEAD
$plugin->version   = 2016011600;        // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2019051100;        // Requires this Moodle version.
$plugin->component = 'block_sharedresources'; // Full name of the plugin (used for diagnostics).
$plugin->release = '3.7.0 Build(2013040102)';
=======
$plugin->version   = 2020100100;        // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2020060900;        // Requires this Moodle version.
$plugin->component = 'block_sharedresources'; // Full name of the plugin (used for diagnostics).
$plugin->release = '3.9.0 Build(2020100100)';
>>>>>>> MOODLE_39_STABLE
$plugin->maturity = MATURITY_RC;
$plugin->dependencies = array('mod_sharedresource' => 2013032600);
$plugin->supports = [38,39];

// Non moodle attributes.
<<<<<<< HEAD
$plugin->codeincrement = '3.7.0001';
=======
$plugin->codeincrement = '3.9.0002';
>>>>>>> MOODLE_39_STABLE
