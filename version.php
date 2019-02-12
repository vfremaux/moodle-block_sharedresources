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

$plugin->version   = 2016011600;        // The current plugin version (Date: YYYYMMDDXX).
<<<<<<< HEAD
<<<<<<< HEAD
$plugin->requires  = 2017110800;        // Requires this Moodle version.
$plugin->component = 'block_sharedresources'; // Full name of the plugin (used for diagnostics).
$plugin->release = '3.4.0 Build(2013040102)';
=======
$plugin->requires  = 2018042700;        // Requires this Moodle version.
$plugin->component = 'block_sharedresources'; // Full name of the plugin (used for diagnostics).
$plugin->release = '3.5.0 Build(2013040102)';
>>>>>>> MOODLE_35_STABLE
=======
$plugin->requires  = 2018112800;        // Requires this Moodle version.
$plugin->component = 'block_sharedresources'; // Full name of the plugin (used for diagnostics).
$plugin->release = '3.6.0 Build(2013040102)';
>>>>>>> MOODLE_36_STABLE
$plugin->maturity = MATURITY_RC;
$plugin->dependencies = array('mod_sharedresource' => 2013032600);

// Non moodle attributes.
<<<<<<< HEAD
<<<<<<< HEAD
$plugin->codeincrement = '3.4.0001';
=======
$plugin->codeincrement = '3.5.0001';
>>>>>>> MOODLE_35_STABLE
=======
$plugin->codeincrement = '3.6.0001';
>>>>>>> MOODLE_36_STABLE
