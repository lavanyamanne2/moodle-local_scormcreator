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

$instance = optional_param('id', 0, PARAM_INT);
$path = optional_param('path', '', PARAM_PATH);
$pageparams = array();

if ($path) {
    $pageparams['path'] = $path;
}

global $CFG, $USER, $DB, $OUTPUT, $context, $PAGE, $instance;

$PAGE->set_url('/local/scormcreator/manifest_form.php');

require_login();

$PAGE->set_pagelayout('admin');
$context = context_system::instance();
$PAGE->set_context($context);

admin_externalpage_setup('cscorm', '', $pageparams);

$header = $SITE->fullname;
$PAGE->set_title(get_string('pluginname', 'local_scormcreator'));
$PAGE->set_heading($header);

/**
 * Initialization of manifest_form class.
 */
class local_scormcreator_manifest_form extends moodleform {

    /**
     *
     * The definition() function defines the form elements.
     *
     */
    public function definition() {

        global $DB, $CFG, $PAGE, $context, $imsid, $instance;
        $mform = $this->_form;

        // Form header.
        $mform->addElement('header', 'mformheader', get_string('mformheader', 'local_scormcreator'));

        /* Series title
           Rule types: Must not be empty, characters should not exceed 1333.
         */
        $mform->addElement('text', 'seriestitle', get_string('seriestitle', 'local_scormcreator'), array('size' => 40,
                           'maxlength="1333"', 'pattern' => '[A-Za-z0-9-|:~`!@#$%^+&,)-=}({:;>.|<@?/<!&$_ ]+'));
        $mform->setType('seriestitle', PARAM_RAW);
        $mform->addRule('seriestitle', get_string('required', 'local_scormcreator'), 'required', null, 'client');
        $mform->addRule('seriestitle', get_string('maximumchars', 'local_scormcreator', 1333), 'maxlength', 1333, 'client');
        $mform->addHelpButton('seriestitle', 'seriestitle', 'local_scormcreator');

        /* Session title
           Rule types: Must not be empty, Characters should not exceed 1333.
         */
        $mform->addElement('text', 'sessiontitle', get_string('sessiontitle', 'local_scormcreator'), array('size' => 40,
                           'maxlength = "1333"', 'pattern' => '[A-Za-z0-9-|:~`!@#$%^+&,)-=}({:;>.|<@?/<!&$_ ]+'));
        $mform->setType('sessiontitle', PARAM_RAW);
        $mform->addRule('sessiontitle', get_string('required', 'local_scormcreator'), 'required', null, 'client');
        $mform->addRule('sessiontitle', get_string('maximumchars', 'local_scormcreator', 1333), 'maxlength', 1333, 'client');
        $mform->addHelpButton('sessiontitle', 'sessiontitle', 'local_scormcreator');

        /* 3. Resource ID
           Rule types: Must not be empty, Must force with the pattern "resource_00_00".
         */
        $resource = array();
        $options = array("pattern = '^[0-9]{2}$'", 'maxlength' => '2', 'size' => '1');
        $resource[] = $mform->createElement ('static', '', '', get_string('series', 'local_scormcreator'));
        $resource[] = $mform->createElement('text', 'resourceidone', get_string('resourceidone', 'local_scormcreator'),
                      $options);
        $resource[] = $mform->createElement ('static', '', '', get_string('session', 'local_scormcreator'));
        $resource[] = $mform->createElement('text', 'resourceidtwo', '', $options, get_string('enable'));
        $mform->addGroup($resource, 'resource', get_string('resourceidone', 'local_scormcreator'), ' ', false);
        $mform->addRule('resource', get_string('required', 'local_scormcreator'), 'required', null, 'client');
        $mform->addHelpButton('resource', 'resourceidone', 'local_scormcreator');
        $mform->setType('resourceidone', PARAM_CLEANHTML);
        $mform->setType('resourceidtwo', PARAM_CLEANHTML);
        $mform->addElement ('static', '', '', get_string('resourcetext', 'local_scormcreator'));

        /* Landing page
           Rule types: (1) Must not be empty. (2) Input must match with text: launch.htm/launch.html/index.htm/index.html
           (This logic is written in function validation($data, $files)).
         */
        $mform->addElement('text', 'landingpage', get_string('landingpage', 'local_scormcreator'),
                           array('size' => 40, 'maxlength = "1333"'));
        $mform->setType('landingpage', PARAM_RAW);
        $mform->addRule('landingpage', get_string('required', 'local_scormcreator'), 'required', null, 'client');
        $mform->addHelpButton('landingpage', 'landingpage', 'local_scormcreator');
        $mform->addElement ('static', '', '', get_string('landingpagetext', 'local_scormcreator'));

        /* Template
           Rule types: Must not be empty.
         */
        $options = array(0 => get_string('temp_select', 'local_scormcreator'),
                         1 => get_string('temp_bluemint', 'local_scormcreator'),
                         2 => get_string('temp_classic', 'local_scormcreator'),
                         3 => get_string('temp_mono', 'local_scormcreator'),
                         4 => get_string('temp_vat', 'local_scormcreator') );
        $mform->addElement('select', 'template', get_string('template', 'local_scormcreator'), $options);
        $mform->setType('template', PARAM_INT);
        $mform->addRule('template', get_string('required', 'local_scormcreator'), 'required', null, 'client');
        $mform->addHelpButton('template', 'template', 'local_scormcreator');

        // Page count.
        $mform->addElement('text', 'pagecount', get_string('pagecount', 'local_scormcreator'),
                           array('size' => 40, 'maxlength = "1333"'));
        $mform->setType('pagecount', PARAM_INT);
        $mform->addRule('pagecount', get_string('required', 'local_scormcreator'), 'required', null, 'client');
        $mform->addRule('pagecount', get_string('maximumchars', 'local_scormcreator', 3), 'maxlength', 3, 'client');
        $mform->addHelpButton('pagecount', 'pagecount', 'local_scormcreator');

        // Logo.
        $logooptions = array('subdirs' => 0, 'maxbytes' => '', 'context' => $context,
                             'accepted_types' => array('.png'), 'return_types' => FILE_INTERNAL | FILE_EXTERNAL);
        $mform->addElement('filemanager', 'logo', get_string('logo', 'local_scormcreator'), null, $logooptions);
        $mform->addRule('logo', get_string('required', 'local_scormcreator'), 'required', null, 'client');
        $mform->setType('logo', PARAM_RAW);
        $mform->addHelpButton('logo', 'logo', 'local_scormcreator');

        // Timemodified (hidden).
        $timemodified = time();
        $mform->addElement('hidden', 'timemodified');
        $mform->setType('timemodified', PARAM_INT);
        $mform->setDefault('timemodified', $timemodified);

        // Action buttons.
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('content_button', 'local_scormcreator'));
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
    public function validation($data, $files) {

        global $DB, $CFG;
        $errors = parent::validation($data, $files);
        list($instance, $plugin, $context) = $this->_customdata;

        // Condition for Landing page.
        if (($data['landingpage'] != "launch.htm") && ($data['landingpage'] != "launch.html") &&
            ($data['landingpage'] != "index.htm") && ($data['landingpage'] != "index.html")) {
            $errors['landingpage'] = get_string('error_landingpage', 'local_scormcreator');
        }

        // Condition for template.
        if (($data['template'] <= 0)) {
            $errors['template'] = get_string('error_template', 'local_scormcreator');
        }
        return $errors;
    }
}

// Save the logo attributes.
$logooptions = array('subdirs' => 0, 'maxbytes' => '', 'context' => $context,
                     'accepted_types' => array('.png', '.jpg'), 'return_types' => FILE_INTERNAL | FILE_EXTERNAL);

$mform = new local_scormcreator_manifest_form();

// Form submission.
if ($mform->is_cancelled()) {

    redirect(new moodle_url('/index.php'));

} else if ($mform->is_submitted()) {

    if ($data = $mform->get_data()) {

        // Serverside validation for the landing page.
        if (($data->landingpage != "launch.htm") && ($data->landingpage != "launch.html") &&
            ($data->landingpage != "index.htm") && ($data->landingpage != "index.html")) {
            $errors['landingpage'] = get_string('error_landingpage', 'local_scormcreator');
        }

        // Serverside validation for the template.
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

        $data->seriestitle = $data->seriestitle;
        $data->sestitle = $data->sessiontitle;
        $data->rid1 = $data->resourceidone;
        $data->rid2 = $data->resourceidtwo;
        $data->landingpage = $data->landingpage;
        $data->pagecount = $data->pagecount;
        $data->logo = $data->logo;
        $data->id = $DB->insert_record('local_scormcreator_manifest', $data);

        // Save the logo file.
        $scormmaker = new local_scormcreator_scorm_lib();
        $getlogo = $scormmaker->local_scormcreator_manifest($data->id);
        foreach ($getlogo as $gl) {
            file_save_draft_area_files($gl->logo, $context->id, 'local_scormcreator', 'logo', '0', $logooptions);
            redirect(new moodle_url('/local/scormcreator/pagecontext_form.php', array('imsid' => $data->id)));
        }
    }
}

echo $OUTPUT->header();

$title = get_string('mpageheader', 'local_scormcreator');
echo $OUTPUT->heading_with_help($title, 'mpageheader', 'local_scormcreator');

$mform->display();

echo $OUTPUT->footer();
