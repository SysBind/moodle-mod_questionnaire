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

class questionnaire_question_essay extends questionnaire_question_base {

    protected function responseclass() {
        return 'questionnaire_response_text';
    }

    protected function question_survey_display($data, $descendantsdata, $blankquestionnaire=false) {
        // Essay.
        // Columns and rows default values.
        $cols = 80;
        $rows = 15;
        // Use HTML editor or not?
        if ($this->precise == 0) {
            $canusehtmleditor = true;
            $rows = $this->length == 0 ? $rows : $this->length;
        } else {
            $canusehtmleditor = false;
            // Prior to version 2.6, "precise" was used for rows number.
            $rows = $this->precise > 1 ? $this->precise : $this->length;
        }
        $name = 'q'.$this->id;
        if (isset($data->{'q'.$this->id})) {
            $value = $data->{'q'.$this->id};
        } else {
            $value = '';
        }
        if ($canusehtmleditor) {
            $editor = editors_get_preferred_editor();
            $editor->use_editor($name, questionnaire_get_editor_options($this->context));
            $texteditor = html_writer::tag('textarea', $value,
                            array('id' => $name, 'name' => $name, 'rows' => $rows, 'cols' => $cols));
        } else {
            $editor = FORMAT_PLAIN;
            $texteditor = html_writer::tag('textarea', $value,
                            array('id' => $name, 'name' => $name, 'rows' => $rows, 'cols' => $cols));
        }
        echo $texteditor;
    }

    protected function response_survey_display($data) {
        echo '<div class="response text">';
        echo((!empty($data->{'q'.$this->id}) ? $data->{'q'.$this->id} : '&nbsp;'));
        echo '</div>';
    }
}