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

require('../../../config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/mod/page/locallib.php'); // Defines function page_get_editor_options().
require_once($CFG->dirroot . '/local/scormcreator/classes/locallib.php');

$instance = optional_param('id', 0, PARAM_INT);
$path = optional_param('path', '', PARAM_PATH);
$pageparams = array();

if ($path) {
    $pageparams['path'] = $path;
}

$imsid = required_param('imsid', PARAM_INT);

global $CFG, $USER, $DB, $OUTPUT, $PAGE, $instance, $imsid, $scormmaker;

$PAGE->set_url('/local/scormcreator/editquiz.php', array('imsid' => $imsid));

require_login();

$PAGE->set_pagelayout('admin');
$context = context_system::instance();
$PAGE->set_context($context);

admin_externalpage_setup('cscorm', '', $pageparams);

$header = $SITE->fullname;
$PAGE->set_title(get_string('pluginname', 'local_scormcreator'));
$PAGE->set_heading($header);

// On editmode, delete if the directory exists.

$scormmaker = new local_scormcreator_scormlib($CFG, $DB);

$manifest = $scormmaker->local_scormcreator_manifest($imsid);

foreach ($manifest as $m) {
    $scormname = $m->scorm_name;
    $mid = $m->id;
    if ($imsid == $mid) {
        $scormdir = $CFG->tempdir.'/local_scormcreator/'.$scormname;
        if (is_dir($scormdir)) {
            $scormmaker->local_scormcreator_deletedir($scormdir);
        }
        redirect(new moodle_url('/local/scormcreator/publish.php', array('imsid' => $imsid)));
    }
}
