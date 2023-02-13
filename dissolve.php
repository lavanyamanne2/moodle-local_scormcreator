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
require_once($CFG->dirroot . '/local/scormcreator/classes/locallib.php');

$instance = optional_param('id', 0, PARAM_INT);
$path = optional_param('path', '', PARAM_PATH);
$pageparams = array();

if ($path) {
    $pageparams['path'] = $path;
}

$imsid = required_param('delete', PARAM_INT);
$dirpath = required_param('dirpath', PARAM_RAW);

global $CFG, $USER, $DB, $OUTPUT, $PAGE, $instance, $imsid, $scormmaker, $dirpath;

$PAGE->set_url('/local/scormcreator/dissolve.php', array('imsid' => $imsid));

require_login();

$PAGE->set_pagelayout('admin');
$context = context_system::instance();
$PAGE->set_context( $context );

admin_externalpage_setup('dscorm', '', $pageparams);

$header = $SITE->fullname;
$PAGE->set_title(get_string('pluginname', 'local_scormcreator'));
$PAGE->set_heading($header);

$scormmaker = new local_scormcreator_scormlib($CFG, $DB);

/**
 * Initialization of dissolvation class.
 */
class local_scormcreator_dissolve {

    /**
     *
     * The local_scormcreator_mydir_delete() deletes the existing scorm directory.
     *
     * @param My_Type $path
     */
    public function local_scormcreator_mydir_delete($dirpath) {
        if (!empty ($dirpath) && is_dir ($dirpath) ) {
            // Upper dirs are not included to avoid disasters.
            $dir  = new RecursiveDirectoryIterator($dirpath, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $f) {
                if (is_file($f)) {
                    unlink($f);
                } else {
                    $emptydirs[] = $f;
                }
            }

            if (!empty($emptydirs)) {
                foreach ($emptydirs as $eachdir) {
                    rmdir($eachdir);
                }
            } 
			rmdir($dirpath);
        }
    }

    /**
     *
     * The clean_scorm() function deletes the temporary scorm.
     *
     * @param My_Type $imsid
     */
    public function local_scormcreator_cleanscorm($imsid) {

        global $DB, $CFG, $imsid, $scormmaker;

        // Calling the function local_scormcreator_deletedir($path) to clean up backend scorm folders.
        $scormmaker = new local_scormcreator_scormlib($CFG, $DB);
        $manifest = $scormmaker->local_scormcreator_manifest($imsid);
        foreach ($manifest as $m) {
            $scormname = $m->scorm_name;
            $removedir = $this->local_scormcreator_mydir_delete($CFG->tempdir.'/local_scormcreator/'.$scormname);

            // Clean database records.
            $delmanifest = $DB->delete_records('sc_manifest', array('id' => $imsid));
            $delpage = $DB->delete_records('sc_page', array('imsid' => $imsid));
            $delpageoptions = $DB->delete_records('sc_pageoptions', array('imsid' => $imsid));
            $delquiz = $DB->delete_records('sc_quiz', array('imsid' => $imsid));
            $delquizoptions = $DB->delete_records('sc_quizoptions', array('imsid' => $imsid));

            return array('delmanifest' => $delmanifest, 'delpage' => $delpage,
                         'delpageoptions' => $delpageoptions,
                         'delquiz' => $delquiz, 'delquizoptions' => $delquizoptions,
                         'removedir' => $removedir );
        }
    }
}

if ($act = new local_scormcreator_dissolve()) {
    $act->local_scormcreator_cleanscorm($imsid);
    redirect(new moodle_url('/local/scormcreator/dscorm.php'));
}

echo $OUTPUT->header();

echo $OUTPUT->footer();
