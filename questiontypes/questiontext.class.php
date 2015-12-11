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
 * This file contains the parent class for questionnaire question types.
 *
 * @author Mike Churchward
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package questiontypes
 */

class questionnaire_question_text extends questionnaire_question_base {

    protected function responseclass() {
        return 'questionnaire_response_text';
    }

    protected function helpname() {
        return 'textbox';
    }

    protected function question_survey_display($data, $descendantsdata, $blankquestionnaire=false) {
    // Text Box.
        echo '<input onkeypress="return event.keyCode != 13;" type="text" size="'.$this->length.'" name="q'.$this->id.'"'.
             ($this->precise > 0 ? ' maxlength="'.$this->precise.'"' : '').' value="'.
             (isset($data->{'q'.$this->id}) ? stripslashes($data->{'q'.$this->id}) : '').
             '" id="' . $this->type . $this->id . '" />';
    }

    protected function response_survey_display($data) {
        $response = isset($data->{'q'.$this->id}) ? $data->{'q'.$this->id} : '';
        echo '<div class="response text"><span class="selected">'.$response.'</span></div>';
    }

    protected function form_length(MoodleQuickForm $mform, $helptext = '') {
        return parent::form_length($mform, 'fieldlength');
    }

    protected function form_precise(MoodleQuickForm $mform, $helptext = '') {
        return parent::form_precise($mform, 'maxtextlength');
    }
}