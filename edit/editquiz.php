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
require_once($CFG->dirroot.'/mod/page/locallib.php');

$instance = optional_param('id', 0, PARAM_INT);
$path = optional_param('path', '', PARAM_PATH);
$pageparams = array();

if ($path) {
    $pageparams['path'] = $path;
}

$imsid = required_param('imsid', PARAM_INT);

global $CFG, $USER, $DB, $OUTPUT, $PAGE, $instance, $imsid;

$PAGE->set_url('/local/scormcreator/editquiz.php', array('imsid' => $imsid));

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
 * Initialization of editquiz_form class.
 */
class local_scormcreator_editquiz_form extends moodleform {

    /**
     *
     * The definition() function defines the form elements.
     *
     */
    public function definition() {

        global $DB, $CFG, $PAGE, $context, $imsid, $instance, $scormmaker;
        $mform = $this->_form;

        // Get quizoptions.
        $getquizoptions = $scormmaker->local_scormcreator_quizoptions($imsid);
        $quizcount = count($getquizoptions);

        // Form header.
        $mform->addElement('header', 'qformheaderone', get_string('qformheaderone', 'local_scormcreator'));

        // IMSID (hidden).
        $mform->addElement('hidden', 'imsid');
        $mform->setType('imsid', PARAM_INT);
        $mform->setDefault('imsid', $imsid);

        // Quiz title.
        $mform->addElement('text', 'qtitle', get_string('qtitle', 'local_scormcreator'),
                array('maxlength' => '1333', 'pattern' => '[A-Za-z0-9-|):;!{[#%^&*?-|\/<>.}]~=(@!&$_ ]+'));
        $mform->setType('qtitle', PARAM_RAW);
        if (empty($instance->id)) {
            $mform->addRule('qtitle', get_string('required'), 'required', null, 'client');
        }
        $mform->addRule('qtitle', get_string('qtitlechars', 'local_scormcreator', 1333), 'maxlength', 1333, 'server');
        $mform->addHelpButton('qtitle', 'qtitle', 'local_scormcreator');
        // Set default value on edit mode.
        foreach ($getquizoptions as $go) {
            $mform->setDefault('qtitle', $go->qtitle);
        }

        // Description.
        foreach ($getquizoptions as $go) {
            $desc = $go->description;
        }
        $mform->addElement('editor', 'page', get_string('description', 'local_scormcreator'), null,
                           page_get_editor_options($context));
        $mform->setType('page', PARAM_RAW);
        $mform->addHelpButton('page', 'description', 'local_scormcreator');
        foreach ($getquizoptions as $go) {
            $desc = $go->description;
        }
        $mform->setDefault('page', array('text' => $desc, 'format' => FORMAT_HTML));

        // Form header two.
        $repeatarray = array();
        $repeateloptions = array();
        $repeatarray[] = $mform->createElement('header', 'questionno', get_string('questionno', 'local_scormcreator'));
        $repeateloptions['questionno']['advanced'] = true;

        // Question type.
        $options = array(1 => get_string('mul', 'local_scormcreator'),
                         2 => get_string('TF', 'local_scormcreator'));
        $repeatarray[] = $mform->createElement('select', 'questiontype',
                         get_string('questiontypeno', 'local_scormcreator'), $options);
        $repeateloptions['questiontype']['rule'] = 'required';
        $repeateloptions['questiontype']['type'] = PARAM_INT;
        $repeateloptions['questiontype']['helpbutton'] = array('questiontype', 'local_scormcreator');
        $mform->setType('questiontype', PARAM_CLEANHTML);
        $mform->setType('questiontypeid', PARAM_INT);
        // Set default value on edit mode.
        $i = 0;
        foreach ($getquizoptions as $go) {
            $mform->setDefault('questiontype['.$i.']', $go->qtype);
            $i++;
        }

        // Question text.
        $repeatarray[] = $mform->createElement('text', 'question', get_string('questionno', 'local_scormcreator'),
                         array('size' => '30', 'pattern' => '[A-Za-z0-9-|:~"!@#$%^+&,)\-=}({:;">.|<@?/<!&$_ ]+' ));
        $repeateloptions['question']['rule'] = 'required';
        $repeateloptions['question']['type'] = PARAM_RAW;
        $repeateloptions['question']['helpbutton'] = array('question', 'local_scormcreator');
        $mform->setType('question', PARAM_CLEANHTML);
        $mform->setType('questionid', PARAM_INT);
        // Set default value on edit mode.
        $i = 0;
        foreach ($getquizoptions as $go) {
            $mform->setDefault('question['.$i.']', $go->question);
            $i++;
        }

        // Question correct.
        $repeatarray[] = $mform->createElement('text', 'qcorrect', get_string('qcorrectno', 'local_scormcreator'),
                         array('size' => '30', 'pattern' => '[A-Za-z0-9-|:~"!@#$%^+&,)\-=}({:;">.|<@?/<!&$_ ]+' ));
        $repeateloptions['qcorrect']['rule'] = 'required';
        $repeateloptions['qcorrect']['type'] = PARAM_RAW;
        $repeateloptions['qcorrect']['helpbutton'] = array('qcorrect', 'local_scormcreator');
        $mform->setType('qcorrect', PARAM_CLEANHTML);
        $mform->setType('qcorrectid', PARAM_INT);
        // Set default value on edit mode.
        $i = 0;
        foreach ($getquizoptions as $go) {
            $mform->setDefault('qcorrect['.$i.']', $go->qcorrect);
            $i++;
        }

        // Question incorrect1.
        $repeatarray[] = $mform->createElement('text', 'qincorrect1', get_string('qincorrect1no', 'local_scormcreator'),
                         array('size' => '30', 'pattern' => '[A-Za-z0-9-|:~"!@#$%^+&,)\-=}({:;">.|<@?/<!&$_ ]+' ));
        $repeateloptions['qincorrect1']['rule'] = 'required';
        $repeateloptions['qincorrect1']['type'] = PARAM_RAW;
        $repeateloptions['qincorrect1']['helpbutton'] = array('qincorrect1', 'local_scormcreator');
        $mform->setType('qincorrect1', PARAM_CLEANHTML);
        $mform->setType('qincorrect1id', PARAM_INT);
        // Set default value on edit mode.
        $i = 0;
        foreach ($getquizoptions as $go) {
            $mform->setDefault('qincorrect1['.$i.']', $go->qincorrect1);
            $i++;
        }

        // Question incorrect2.
        $repeatarray[] = $mform->createElement('text', 'qincorrect2', get_string('qincorrect2no', 'local_scormcreator'),
                         array('size' => '30', 'pattern' => '[A-Za-z0-9-|:~"!@#$%^+&,)\-=}({:;">.|<@?/<!&$_ ]+' ));
        $repeateloptions['qincorrect2']['disabledif'] = array('questiontype', 'eq', 2);
        $repeateloptions['qincorrect2']['type'] = PARAM_RAW;
        $repeateloptions['qincorrect2']['helpbutton'] = array('qincorrect2', 'local_scormcreator');
        $mform->setType('qincorrect2', PARAM_CLEANHTML);
        $mform->setType('qincorrect2id', PARAM_INT);
        // Set default value on edit mode.
        $i = 0;
        foreach ($getquizoptions as $go) {
            $mform->setDefault('qincorrect2['.$i.']', $go->qincorrect2);
            $i++;
        }

        // Question incorrect3.
        $repeatarray[] = $mform->createElement('text', 'qincorrect3', get_string('qincorrect3no', 'local_scormcreator'),
                         array('size' => '30', 'pattern' => '[A-Za-z0-9-|:~"!@#$%^+&,)\-=}({:;">.|<@?/<!&$_ ]+' ));
        $repeateloptions['qincorrect3']['disabledif'] = array('questiontype', 'eq', 2);
        $repeateloptions['qincorrect3']['helpbutton'] = array('qincorrect3', 'local_scormcreator');
        $mform->setType('qincorrect3', PARAM_CLEANHTML);
        $repeateloptions['qincorrect3']['type'] = PARAM_RAW;
        $mform->setType('qincorrect3id', PARAM_INT);
        // Set default value on edit mode.
        $i = 0;
        foreach ($getquizoptions as $go) {
            $mform->setDefault('qincorrect3['.$i.']', $go->qincorrect3);
            $i++;
        }

        $repeatno = $quizcount;

        $this->repeat_elements($repeatarray, $repeatno, $repeateloptions, 'option_repeats', 'option_add_fields', 1,
                               get_string('addquestion', 'local_scormcreator'), null, true);

        // Action buttons.
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('erscorm', 'local_scormcreator'));
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
        return $errors;
    }
}

$mform = new local_scormcreator_editquiz_form();

// Form Submission.
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/index.php'));

} else if ($mform->is_submitted()) {

    if ($data = $mform->get_data()) {

        // Save quiz to sc_quiz if imsid doesn't exists.
        if (!$DB->record_exists('local_scormcreator_quiz', array('imsid' => $imsid))) {

            $quiz = new stdClass();
            $timemodified = time();
            $quiz->imsid = $imsid;
            $quiz->timemodified = $timemodified;
            $DB->insert_record('local_scormcreator_quiz', $quiz);
        }

        // Get pageid.
        $getquiz = $scormmaker->local_scormcreator_quiz($imsid);
        foreach ($getquiz as $gq) {
            $quizid = $gq->id;
        }

        // Creating a dummy $data->id to differentiate between INSERT and UPDATE.
        $getquizoptions = $scormmaker->local_scormcreator_quizoptions($imsid);
        $id = array();
        foreach ($getquizoptions as $go) {
            $id[] = $go->id;
        }
        $data->id = $id;

        // INSERT quizoptions to sc_quizoptions if id doesn't exists.
        foreach ($data->question as $index => $value) {
            $value = trim($value);
            if (isset($value) && $value <> '') {

                $quizoption = new stdClass();
                $timemodified = time();
                $quizoption->question = $data->question[$index];
                $quizoption->id = $data->id[$index];
                $quizoption->imsid = $imsid;
                $quizoption->quizid = $quizid;
                $quizoption->qtitle = $data->qtitle;
                $quizoption->description = $data->page['text'];
                $quizoption->descriptionformat = $data->page['format'];
                $quizoption->qtype = $data->questiontype[$index];
                $quizoption->qcorrect = $data->qcorrect[$index];
                $quizoption->qincorrect1 = $data->qincorrect1[$index];
                $quizoption->qincorrect2 = $data->qincorrect2[$index];
                $quizoption->qincorrect3 = $data->qincorrect3[$index];
                $quizoption->timemodified = $timemodified;
                if (!$DB->record_exists('local_scormcreator_qoptions', array('id' => $quizoption->id))) {
                    $DB->insert_record('local_scormcreator_qoptions', $quizoption);
                }
            }
        }

        // UPDATE quizoptions to sc_quizoptions if id exists.
        foreach ($data->question as $index => $value) {
            $value = trim($value);
            if (isset($value) && $value <> '') {

                $quizoption = new stdClass();
                $timemodified = time();
                $quizoption->question = $data->question[$index];
                $quizoption->id = $data->id[$index];
                $quizoption->imsid = $imsid;
                $quizoption->quizid = $quizid;
                $quizoption->qtitle = $data->qtitle;
                $quizoption->description = $data->page['text'];
                $quizoption->descriptionformat = $data->page['format'];
                $quizoption->qtype = $data->questiontype[$index];
                $quizoption->qcorrect = $data->qcorrect[$index];
                $quizoption->qincorrect1 = $data->qincorrect1[$index];
                $quizoption->qincorrect2 = $data->qincorrect2[$index];
                $quizoption->qincorrect3 = $data->qincorrect3[$index];
                $quizoption->timemodified = $timemodified;
                if ($DB->record_exists('local_scormcreator_qoptions', array('id' => $quizoption->id))) {
                    $DB->update_record('local_scormcreator_qoptions', $quizoption);
                    redirect(new moodle_url('/local/scormcreator/edit/editscorm.php', array('imsid' => $imsid)));
                }
            }
        }
    }
}

echo $OUTPUT->header();

$title = get_string('qpageheader', 'local_scormcreator');
echo $OUTPUT->heading_with_help($title, 'qpageheader', 'local_scormcreator');

$mform->display();

echo $OUTPUT->footer();
