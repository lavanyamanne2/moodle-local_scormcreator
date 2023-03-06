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

$instance = optional_param('id', 0, PARAM_INT);
$path = optional_param('path', '', PARAM_PATH);
$pageparams = array();

if ($path) {
    $pageparams['path'] = $path;
}

$imsid = required_param('imsid', PARAM_INT);

global $CFG, $USER, $DB, $OUTPUT, $PAGE, $instance, $imsid, $scormmaker;

$PAGE->set_url('/local/scormcreator/editmanifest.php', array('imsid' => $imsid));

require_login();

$PAGE->set_pagelayout('admin');
$context = context_system::instance();
$PAGE->set_context($context);

admin_externalpage_setup('cscorm', '', $pageparams);

$header = $SITE->fullname;
$PAGE->set_title(get_string('pluginname', 'local_scormcreator'));
$PAGE->set_heading($header);

$scormmaker = new local_scormcreator_scorm_lib();

/**
 * Initialization of editmanifest_form class.
 */
class local_scormcreator_editmanifest_form extends moodleform {

    /**
     *
     * The definition() function defines the form elements.
     *
     */
    public function definition() {

        global $DB, $CFG, $PAGE, $context, $imsid, $scormmaker;
        $mform = $this->_form;

        // Form header.
        $mform->addElement('header', 'mformheader', get_string('mformheader', 'local_scormcreator'));

        $manifest = $scormmaker->local_scormcreator_manifest($imsid);
        foreach ($manifest as $m) {
            $seriestitle = $m->seriestitle;
        }

        // IMSID (hidden).
        $mform->addElement('hidden', 'imsid');
        $mform->setType('imsid', PARAM_INT);
        $mform->setDefault('imsid', $imsid);

        // Series title.
        $mform->addElement('text', 'seriestitle', get_string('seriestitle', 'local_scormcreator'), 'maxlength="1333"');
        $mform->setType('seriestitle', PARAM_RAW);
        $mform->addRule('seriestitle', get_string('required', 'local_scormcreator'), 'required', null, 'client');
        $mform->addRule('seriestitle', get_string('maximumchars', 'local_scormcreator', 1333), 'maxlength', 1333, 'client');
        $mform->addHelpButton('seriestitle', 'seriestitle', 'local_scormcreator');
        $mform->setDefault('seriestitle', $seriestitle);

        // Session title.
        $mform->addElement('text', 'sessiontitle', get_string('sessiontitle', 'local_scormcreator'), 'maxlength="1333"');
        $mform->setType('sessiontitle', PARAM_RAW);
        $mform->addRule('sessiontitle', get_string('required', 'local_scormcreator'), 'required', null, 'client');
        $mform->addRule('sessiontitle', get_string('maximumchars', 'local_scormcreator', 1333), 'maxlength', 1333, 'client');
        $mform->addHelpButton('sessiontitle', 'sessiontitle', 'local_scormcreator');
        $mform->setDefault('sessiontitle', $m->sestitle);

        // Resource ID.
        $resource = array();
        $options = array("pattern = '^[0-9]{2}$'", 'maxlength' => '2', 'size' => '1');
        $resource[] = $mform->createElement ('static', '', '', get_string('series', 'local_scormcreator'));
        $resource[] = $mform->createElement('text', 'resourceidone', get_string('resourceidone', 'local_scormcreator'), $options);
        $resource[] = $mform->createElement ('static', '', '', get_string('session', 'local_scormcreator'));
        $resource[] = $mform->createElement('text', 'resourceidtwo', '', $options, get_string('enable'));
        $mform->addGroup($resource, 'resource', get_string('resourceidone', 'local_scormcreator'), ' ', false);
        $mform->addRule('resource', get_string('required', 'local_scormcreator'), 'required', null, 'client');
        $mform->addHelpButton('resource', 'resourceidone', 'local_scormcreator');
        $mform->setType('resourceidone', PARAM_CLEANHTML);
        $mform->setType('resourceidtwo', PARAM_CLEANHTML);
        $mform->addElement ('static', '', '', get_string('resourcetext', 'local_scormcreator'));
        $mform->setDefault('resourceidone', $m->rid1);
        $mform->setDefault('resourceidtwo', $m->rid2);

        // Landing page.
        $mform->addElement('text', 'landingpage', get_string('landingpage', 'local_scormcreator'));
        $mform->setType('landingpage', PARAM_RAW);
        $mform->addRule('landingpage', get_string('required', 'local_scormcreator'), 'required', null, 'client');
        $mform->addHelpButton('landingpage', 'landingpage', 'local_scormcreator');
        $mform->addElement ('static', '', '', get_string('landingpagetext', 'local_scormcreator'));
        $mform->setDefault('landingpage', $m->landingpage);

        // Template.
        if ($m->template == 'bluemint') {
            $options = array (1 => get_string('temp_bluemint', 'local_scormcreator'));
        } else if ($m->template == 'classic') {
            $options = array (2 => get_string('temp_classic', 'local_scormcreator'));
        } else if ($m->template == 'monolight') {
            $options = array (3 => get_string('temp_mono', 'local_scormcreator'));
        } else if ($m->template == 'varsity') {
            $options = array (4 => get_string('temp_vat', 'local_scormcreator'));
        }

        $mform->addElement('select', 'template', get_string('template', 'local_scormcreator'), $options);
        $mform->setType('template', PARAM_INT);
        $mform->addRule('template', get_string('required', 'local_scormcreator'), 'required', null, 'client');
        $mform->addHelpButton('template', 'template', 'local_scormcreator');

        // Page count.
        $mform->addElement('text', 'pagecount', get_string('pagecount', 'local_scormcreator'), array('maxlength=3',
                           'disabled' => 'disabled'));
        $mform->setType('pagecount', PARAM_INT);
        $mform->addRule('pagecount', get_string('maximumchars', 'local_scormcreator', 3), 'maxlength', 3, 'client');
        $mform->addHelpButton('pagecount', 'pagecount', 'local_scormcreator');
        $mform->setDefault('pagecount', $m->pagecount);

        // Logo.
        $logooptions = array('subdirs' => 0, 'maxbytes' => '', 'context' => $context,
                             'accepted_types' => array('.png', '.jpg'),
                             'return_types' => FILE_INTERNAL | FILE_EXTERNAL);
        $mform->addElement('filemanager', 'logo', get_string('logo', 'local_scormcreator'), null, $logooptions);
        $mform->addRule('logo', get_string('required', 'local_scormcreator'), 'required', null, 'client');
        $mform->setType('logo', PARAM_RAW);
        $mform->addHelpButton('logo', 'logo', 'local_scormcreator');
        $mform->setDefault('logo', $m->logo);

        // Timemodified (hidden).
        $timemodified = time();
        $mform->addElement('hidden', 'timemodified');
        $mform->setType('timemodified', PARAM_INT);
        $mform->setDefault('timemodified', $timemodified);

        // Action buttons.
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton',
                         get_string('content_button', 'local_scormcreator'));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', '', false);
    }

    /**
     *
     * The validation() function defines the form validation.
     *
     * @param My_Type $data
     * @param My_Type $files
     */
    public function validation ($data, $files) {

        global $DB, $CFG;
        $errors = parent::validation($data, $files);
        list($instance, $plugin, $context) = $this->_customdata;

        // Condition for Landing page.
        if (($data['landingpage'] != "launch.htm") && ($data['landingpage'] != "launch.html") &&
            ($data['landingpage'] != "index.htm") &&
            ($data['landingpage'] != "index.html")) {
            $errors['landingpage'] = get_string('error_landingpage', 'local_scormcreator');
        }

        // Condition for template.
        if (($data['template'] <= 0)) {
            $errors['template'] = get_string('error_template', 'local_scormcreator');
        }
        return $errors;
    }
}

$mform = new local_scormcreator_editmanifest_form();

// Form Submission.
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/index.php'));

} else if ($mform->is_submitted()) {

    if ($data = $mform->get_data()) {

        // Condition for Landing page.
        if (($data->landingpage != "launch.htm") && ($data->landingpage != "launch.html") && ($data->landingpage != "index.htm") &&
           ($data->landingpage != "index.html")) {
            $errors['landingpage'] = get_string('error_landingpage', 'local_scormcreator');
        }

        // Condition for template.
        if (($data->template <= 0)) {
            $errors['template'] = get_string('error_template', 'local_scormcreator');
        }

        if ($data->template == 1) {
            $data->template = 'bluemint';
        } else if ($data->template == 2) {
            $data->template = 'classic';
        } else if ($data->template == 3) {
            $data->template = 'monolight';
        } else if ($data->template == 4) {
            $data->template = 'varsity';
        }

        // Update data to sc_manifest.
        $timemodified = time();
        $record = new stdClass();
        $record->id = $data->imsid;
        $record->seriestitle = $data->seriestitle;
        $record->sestitle = $data->sessiontitle;
        $record->rid1 = $data->resourceidone;
        $record->rid2 = $data->resourceidtwo;
        $record->landingpage = $data->landingpage;
        $record->template = $data->template;
        $record->pagecount = $data->pagecount;
        $record->logo = $data->logo;
        $record->timemodified = $timemodified;

        if ($DB->record_exists('local_scormcreator_manifest', array('id' => $imsid))) {
            $DB->update_record('local_scormcreator_manifest', $record);
        }
        redirect(new moodle_url('/local/scormcreator/edit/editpage.php', array('imsid' => $imsid)));
    }
}

echo $OUTPUT->header();

$title = get_string('mpageheader', 'local_scormcreator');
echo $OUTPUT->heading_with_help($title, 'mpageheader', 'local_scormcreator');

$mform->display();

echo $OUTPUT->footer();
