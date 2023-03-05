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
 * SCORM-CREATOR
 *
 * @package    local_scormcreator
 * @copyright  2023 Lavanya Manne
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig ) {
    $ADMIN->add('root', new admin_category('scormcreator', get_string('pluginname', 'local_scormcreator')));
    $ADMIN->add('scormcreator', new admin_externalpage('cscorm', get_string('cscorm', 'local_scormcreator'),
                 new moodle_url('/local/scormcreator/manifest_form.php')));
    $ADMIN->add('scormcreator', new admin_externalpage('dscorm', get_string('dscorm', 'local_scormcreator'),
                 new moodle_url('/local/scormcreator/dscorm.php')));
}
