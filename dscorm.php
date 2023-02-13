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

require('../../config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->dirroot . '/local/scormcreator/classes/lib.php');
require_once($CFG->dirroot . '/local/scormcreator/classes/locallib.php');

$instance = optional_param('id', 0, PARAM_INT);
$path = optional_param('path', '', PARAM_PATH);
$pageparams = array();

if ($path) {
    $pageparams['path'] = $path;
}

global $CFG, $USER, $DB, $OUTPUT, $PAGE, $instance, $scormdata, $context;

$PAGE->set_url('/local/scormcreator/dscorm.php');

require_login();

$PAGE->set_pagelayout('admin');
$context = context_system::instance();
$PAGE->set_context( $context );

admin_externalpage_setup('dscorm', '', $pageparams);

$header = $SITE->fullname;
$PAGE->set_title(get_string('pluginname', 'local_scormcreator'));
$PAGE->set_heading($header);

echo $OUTPUT->header();

$scormmaker = new local_scormcreator_scormlib($CFG, $DB);

$scormdata = new flexible_table('tableviewer');
$scormdata->define_columns(array('Name', 'Time Created', 'Download', ' '));
$scormdata->define_headers(array('Name', 'Time Created', 'Download', ' '));
$scormdata->define_baseurl($PAGE->url);
$scormdata->setup();

$scormtitle = $DB->get_recordset_sql("SELECT scm.id, scm.template, scm.scorm_name, scm.timemodified
                                      FROM {sc_manifest} scm WHERE scm.scorm_name != ''");
foreach ($scormtitle as $st) {

    $date = new DateTime();
    $date->setTimestamp(intval($st->timemodified));

	$stemp = $st->template;
    $scormdata->add_data(array(
        $st->scorm_name,
        userdate($date->getTimestamp()),
        '<a href="'.$CFG->tempdir.'/local_scormcreator/'.$st->scorm_name.'/'.$st->scorm_name.'.zip" download="'.$st->scorm_name.'.zip"
            title="'.get_string('sload', 'local_scormcreator').'">'.$st->scorm_name.'.zip</a>',
        '<a href="'.$CFG->wwwroot.'/local/scormcreator/edit/editmanifest.php?imsid='.$st->id.'" title="Edit SCORM">Edit</a> /
         <a href="'.$CFG->wwwroot.'/local/scormcreator/dissolve.php?imsid='.$st->id.'" title="Delete SCORM">Delete</a>'));
}

$scormdata->print_html();

echo $OUTPUT->footer();
