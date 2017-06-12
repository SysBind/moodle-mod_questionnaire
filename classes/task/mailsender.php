<?php
namespace mod_questionnaire\task;


class mailsender extends \core\task\scheduled_task
{

    public function get_name()
    {
        return get_string('crontaskmail', 'mod_questionnaire');
    }

    public function execute()
    {
        global $CFG;
        require_once($CFG->dirroot . '/mod/questionnaire/locallib.php');

        questionnaire_cron();
    }
}

?>