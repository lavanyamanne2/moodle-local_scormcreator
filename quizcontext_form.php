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
require_once($CFG->dirroot.'/mod/page/locallib.php');

$instance = optional_param('id', 0, PARAM_INT);
$path = optional_param('path', '', PARAM_PATH);
$pageparams = array();

if ($path) {
    $pageparams['path'] = $path;
}

$imsid = required_param('imsid', PARAM_INT);

global $CFG, $USER, $DB, $OUTPUT, $PAGE, $instance, $imsid, $scormmaker;

$PAGE->set_url('/local/scormcreator/quizcontext_form.php', array('imsid' => $imsid));

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
 * Initialization of quiz_form class.
 */
class local_scormcreator_quizcontext_form extends moodleform {

    /**
     *
     * The definition() function defines the form elements.
     *
     */
    public function definition() {

        global $DB, $CFG, $PAGE, $context, $imsid, $instance;
        $mform = $this->_form;

        list($instance, $plugin, $context) = $this->_customdata;

        // Form header.
        $mform->addElement('header', 'qformheaderone', get_string('qformheaderone', 'local_scormcreator'));

        // IMSID (hidden).
        $mform->addElement('hidden', 'imsid');
        $mform->setType('imsid', PARAM_INT);
        $mform->setDefault('imsid', $imsid);

        // Quiz title.
        $mform->addElement('text', 'qtitle', get_string('qtitle', 'local_scormcreator'),
                           array('size' => 40, 'maxlength' => '1333',
                           'pattern' => '[A-Za-z0-9-|:~`!@#$%^+&,)-=}({:;>.|<@?/<!&$_ ]+'), );
        $mform->setType('qtitle', PARAM_RAW);
        if (empty($instance->id)) {
            $mform->addRule('qtitle', get_string('required'), 'required', null, 'client');
        }
        $mform->addRule('qtitle', get_string('qtitlechars', 'local_scormcreator', 1333), 'maxlength', 1333, 'server');
        $mform->addHelpButton('qtitle', 'qtitle', 'local_scormcreator');

        // Description.
        $mform->addElement('editor', 'page', get_string('description', 'local_scormcreator'), null,
                            page_get_editor_options($context));
        $mform->setType('page', PARAM_RAW);
        $mform->addHelpButton('page', 'description', 'local_scormcreator');

        // Form header two.
        $repeatarray = array();
        $repeateloptions = array();
        $repeatarray[] = $mform->createElement('header', 'questionno', get_string('questionno', 'local_scormcreator'));
        $repeateloptions['questionno']['advanced'] = true;

        // Question type.
        $options = array(1 => get_string('mul', 'local_scormcreator'), 2 => get_string('TF', 'local_scormcreator'));
        $repeatarray[] = $mform->createElement('select', 'questiontype',
                         get_string('questiontypeno', 'local_scormcreator'), $options);
        $repeateloptions['questiontype']['rule'] = 'required';
        $repeateloptions['questiontype']['type'] = PARAM_INT;
        $repeateloptions['questiontype']['helpbutton'] = array('questiontype', 'local_scormcreator');
        $mform->setType('questiontype', PARAM_CLEANHTML);
        $mform->setType('questiontypeid', PARAM_INT);

        // Question text.
        $repeatarray[] = $mform->createElement('text', 'question', get_string('questionno', 'local_scormcreator'),
                         array('size' => 40, 'pattern' => '[A-Za-z0-9-|:~`!@#$%^+&,)-=}({:;>.|<@?/<!&$_ ]+' ));
        $repeateloptions['question']['rule'] = 'required';
        $repeateloptions['question']['type'] = PARAM_RAW;
        $repeateloptions['question']['helpbutton'] = array('question', 'local_scormcreator');
        $mform->setType('question', PARAM_CLEANHTML);
        $mform->setType('questionid', PARAM_INT);

        // Question correct.
        $repeatarray[] = $mform->createElement('text', 'qcorrect', get_string('qcorrectno', 'local_scormcreator'),
                         array('size' => 40, 'pattern' => '[A-Za-z0-9-|:~`!@#$%^+&,)-=}({:;>.|<@?/<!&$_ ]+' ));
        $repeateloptions['qcorrect']['rule'] = 'required';
        $repeateloptions['qcorrect']['type'] = PARAM_RAW;
        $repeateloptions['qcorrect']['helpbutton'] = array('qcorrect', 'local_scormcreator');
        $mform->setType('qcorrect', PARAM_CLEANHTML);
        $mform->setType('qcorrectid', PARAM_INT);

        // Question incorrect1.
        $repeatarray[] = $mform->createElement('text', 'qincorrect1', get_string('qincorrect1no', 'local_scormcreator'),
                         array('size' => 40, 'pattern' => '[A-Za-z0-9-|:~`!@#$%^+&,)-=}({:;>.|<@?/<!&$_ ]+' ));
        $repeateloptions['qincorrect1']['rule'] = 'required';
        $repeateloptions['qincorrect1']['type'] = PARAM_RAW;
        $repeateloptions['qincorrect1']['helpbutton'] = array('qincorrect1', 'local_scormcreator');
        $mform->setType('qincorrect1', PARAM_CLEANHTML);
        $mform->setType('qincorrect1id', PARAM_INT);

        // Question incorrect2.
        $repeatarray[] = $mform->createElement('text', 'qincorrect2', get_string('qincorrect2no', 'local_scormcreator'),
                         array('size' => 40, 'pattern' => '[A-Za-z0-9-|:~`!@#$%^+&,)-=}({:;>.|<@?/<!&$_ ]+' ));
        $repeateloptions['qincorrect2']['disabledif'] = array('questiontype', 'eq', 2);
        $repeateloptions['qincorrect2']['type'] = PARAM_RAW;
        $repeateloptions['qincorrect2']['default'] = null;
        $repeateloptions['qincorrect2']['helpbutton'] = array('qincorrect2', 'local_scormcreator');
        $mform->setType('qincorrect2', PARAM_CLEANHTML);
        $mform->setType('qincorrect2id', PARAM_INT);

        // Question incorrect3.
        $repeatarray[] = $mform->createElement('text', 'qincorrect3', get_string('qincorrect3no', 'local_scormcreator'),
                         array('size' => 40, 'pattern' => '[A-Za-z0-9-|:~`!@#$%^+&,)-=}({:;>.|<@?/<!&$_ ]+' ));
        $repeateloptions['qincorrect3']['disabledif'] = array('questiontype', 'eq', 2);
        $repeateloptions['qincorrect3']['default'] = null;
        $repeateloptions['qincorrect3']['helpbutton'] = array('qincorrect3', 'local_scormcreator');
        $mform->setType('qincorrect3', PARAM_CLEANHTML);
        $repeateloptions['qincorrect3']['type'] = PARAM_RAW;
        $mform->setType('qincorrect3id', PARAM_INT);

        if ($instance) {
            $repeatno = $DB->count_records('sc_quizoptions', array('quizid' => $instance));
            $repeatno += 6;
        } else {
            $repeatno = 1;
        }

        $this->repeat_elements($repeatarray, $repeatno, $repeateloptions, 'option_repeats', 'option_add_fields', 1,
               get_string('addquestion', 'local_scormcreator'), null, true);

        // Action buttons.
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('crscorm', 'local_scormcreator'));
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
        return $errors;
    }
}

$mform = new local_scormcreator_quizcontext_form();

// Form submission.
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

        // Save quizoptions to sc_quizoptions if imsid doesn't exists.
        if (!$DB->record_exists('local_scormcreator_qoptions', array('imsid' => $imsid))) {

            // Qtitle, Description, Question Type, Question, Question correct, Question incorrect1,
            // Question incorrect2, Question incorrect3.
            foreach ($data->question as $index => $value) {
                $value = trim($value);
                if (isset($value) && $value <> '') {
                    $quizoption = new stdClass();
                    $timemodified = time();
                    $quizoption->imsid = $imsid;
                    $quizoption->quizid = $quizid;
                    $quizoption->qtitle = $data->qtitle;
                    $quizoption->description = $data->page['text'];
                    $quizoption->descriptionformat = $data->page['format'];
                    $quizoption->question = $data->question[$index];
                    $quizoption->qtype = $data->questiontype[$index];
                    $quizoption->qcorrect = $data->qcorrect[$index];
                    $quizoption->qincorrect1 = $data->qincorrect1[$index];
                    $quizoption->qincorrect2 = $data->qincorrect2[$index];
                    $quizoption->qincorrect3 = $data->qincorrect3[$index];
                    $quizoption->timemodified = $timemodified;
                    $DB->insert_record('local_scormcreator_qoptions', $quizoption);
                }
            }
        }
        redirect(new moodle_url('/local/scormcreator/publish.php', array('imsid' => $imsid)));
    }
}

echo $OUTPUT->header();

$title = get_string('qpageheader', 'local_scormcreator');
echo $OUTPUT->heading_with_help($title, 'qpageheader', 'local_scormcreator');
$mform->display();

echo $OUTPUT->footer();
