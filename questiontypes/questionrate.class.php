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

class questionnaire_question_rate extends questionnaire_question_base {
    public function __construct($id = 0, $question = null, $context = null) {
        parent::__construct($id, $question, $context, 'questionnaire_response_rank');
    }

    protected function question_survey_display($data, $descendantsdata, $blankquestionnaire=false) {
        $disabled = '';
        if ($blankquestionnaire) {
            $disabled = ' disabled="disabled"';
        }
        if (!empty($data) && ( !isset($data->{'q'.$this->id}) || !is_array($data->{'q'.$this->id}) ) ) {
            $data->{'q'.$this->id} = array();
        }

        $isna = $this->precise == 1;
        $osgood = $this->precise == 3;

        // Check if rate question has one line only to display full width columns of choices.
        $nocontent = false;
        $nameddegrees = 0;
        $n = array();
        $v = array();
        $mods = array();
        $maxndlen = 0;
        foreach ($this->choices as $cid => $choice) {
            $content = $choice->content;
            if (!$nocontent && $content == '') {
                $nocontent = true;
            }
            // Check for number from 1 to 3 digits, followed by the equal sign = (to accomodate named degrees).
            if (preg_match("/^([0-9]{1,3})=(.*)$/", $content, $ndd)) {
                $n[$nameddegrees] = format_text($ndd[2], FORMAT_HTML);
                if (strlen($n[$nameddegrees]) > $maxndlen) {
                    $maxndlen = strlen($n[$nameddegrees]);
                }
                $v[$nameddegrees] = $ndd[1];
                $this->choices[$cid] = '';
                $nameddegrees++;
            } else {
                $contents = questionnaire_choice_values($content);
                if ($contents->modname) {
                    $choice->content = $contents->text;
                }
            }
        }

        // The 0.1% right margin is needed to avoid the horizontal scrollbar in Chrome!
        // A one-line rate question (no content) does not need to span more than 50%.
        $width = $nocontent ? "50%" : "99.9%";
        echo '<table style="width:'.$width.'">';
        echo '<tbody>';
        echo '<tr>';
        // If Osgood, adjust central columns to width of named degrees if any.
        if ($osgood) {
            if ($maxndlen < 4) {
                $width = '45%';
            } else if ($maxndlen < 13) {
                $width = '40%';
            } else {
                $width = '30%';
            }
            $nn = 100 - ($width * 2);
            $colwidth = ($nn / $this->length).'%';
            $textalign = 'right';
        } else if ($nocontent) {
            $width = '0%';
            $colwidth = (100 / $this->length).'%';
            $textalign = 'right';
        } else {
            $width = '59%';
            $colwidth = (40 / $this->length).'%';
            $textalign = 'left';
        }

        echo '<td style="width: '.$width.'"></td>';

        if ($isna) {
            $na = get_string('notapplicable', 'questionnaire');
        } else {
            $na = '';
        }
        if ($this->precise == 2) {
            $order = ' onclick="other_rate_uncheck(name, value)" ';
        } else {
            $order = '';
        }

        if ($this->precise != 2) {
            $nbchoices = count($this->choices) - $nameddegrees;
        } else { // If "No duplicate choices", can restrict nbchoices to number of rate items specified.
            $nbchoices = $this->length;
        }

        // Display empty td for Not yet answered column.
        if ($nbchoices > 1 && $this->precise != 2 && !$blankquestionnaire) {
            echo '<td></td>';
        }

        for ($j = 0; $j < $this->length; $j++) {
            if (isset($n[$j])) {
                $str = $n[$j];
                $val = $v[$j];
            } else {
                $str = $j + 1;
                $val = $j + 1;
            }
            if ($blankquestionnaire) {
                $val = '<br />('.$val.')';
            } else {
                $val = '';
            }
            echo '<td style="width:'.$colwidth.'; text-align:center;" class="smalltext">'.$str.$val.'</td>';
        }
        if ($na) {
            echo '<td style="width:'.$colwidth.'; text-align:center;" class="smalltext">'.$na.'</td>';
        }
        echo '</tr>';

        $num = 0;
        foreach ($this->choices as $cid => $choice) {
            $str = 'q'."{$this->id}_$cid";
            $num += (isset($data->$str) && ($data->$str != -999));
        }

        $notcomplete = false;
        if ( ($num != $nbchoices) && ($num != 0) ) {
            questionnaire_notify(get_string('checkallradiobuttons', 'questionnaire', $nbchoices));
            $notcomplete = true;
        }

        foreach ($this->choices as $cid => $choice) {
            if (isset($choice->content)) {
                $str = 'q'."{$this->id}_$cid";
                echo '<tr class="raterow">';
                $content = $choice->content;
                if ($osgood) {
                    list($content, $contentright) = preg_split('/[|]/', $content);
                }
                echo '<td style="text-align: '.$textalign.';">'.format_text($content, FORMAT_HTML).'&nbsp;</td>';
                $bg = 'c0 raterow';
                if ($nbchoices > 1 && $this->precise != 2  && !$blankquestionnaire) {
                    $checked = ' checked="checked"';
                    $completeclass = 'notanswered';
                    $title = '';
                    if ($notcomplete && isset($data->$str) && ($data->$str == -999)) {
                        $completeclass = 'notcompleted';
                        $title = get_string('pleasecomplete', 'questionnaire');
                    }
                    // Set value of notanswered button to -999 in order to eliminate it from form submit later on.
                    echo '<td title="'.$title.'" class="'.$completeclass.'" style="width:1%;"><input name="'.
                        $str.'" type="radio" value="-999" '.$checked.$order.' /></td>';
                }
                for ($j = 0; $j < $this->length + $isna; $j++) {
                    $checked = ((isset($data->$str) && ($j == $data->$str ||
                                 $j == $this->length && $data->$str == -1)) ? ' checked="checked"' : '');
                    $checked = '';
                    if (isset($data->$str) && ($j == $data->$str || $j == $this->length && $data->$str == -1)) {
                        $checked = ' checked="checked"';
                    }
                    echo '<td style="text-align:center" class="'.$bg.'">';
                    $i = $j + 1;
                    echo html_writer::tag('span', get_string('option', 'questionnaire', $i),
                        array('class' => 'accesshide'));
                    // If isna column then set na choice to -1 value.
                    $value = ($j < $this->length ? $j : - 1);
                    echo '<input name="'.$str.'" type="radio" value="'.$value .'"'.$checked.$disabled.$order.' /></td>';
                    if ($bg == 'c0 raterow') {
                        $bg = 'c1 raterow';
                    } else {
                        $bg = 'c0 raterow';
                    }
                }
                if ($osgood) {
                    echo '<td>&nbsp;'.format_text($contentright, FORMAT_HTML).'</td>';
                }
                echo '</tr>';
            }
        }
        echo '</tbody>';
        echo '</table>';
    }

    protected function response_survey_display($data) {
        static $uniquetag = 0;  // To make sure all radios have unique names.
        if (!isset($data->{'q'.$this->id}) || !is_array($data->{'q'.$this->id})) {
            $data->{'q'.$this->id} = array();
        }
        // Check if rate question has one line only to display full width columns of choices.
        $nocontent = false;
        foreach ($this->choices as $cid => $choice) {
            $content = $choice->content;
            if ($choice->content == '') {
                $nocontent = true;
                break;
            }
        }
        $width = $nocontent ? "50%" : "99.9%";

        echo '<table class="individual" border="0" cellspacing="1" cellpadding="0" style="width:'.$width.'">';
        echo '<tbody><tr>';
        $osgood = $this->precise == 3;
        $bg = 'c0';
        $nameddegrees = 0;
        $cidnamed = array();
        $n = array();
        // Max length of potential named degree in column head.
        $maxndlen = 0;
        foreach ($this->choices as $cid => $choice) {
            $content = $choice->content;
            if (preg_match("/^[0-9]{1,3}=/", $content, $ndd)) {
                $ndd = format_text(substr($content, strlen($ndd[0])), FORMAT_HTML);
                $n[$nameddegrees] = $ndd;
                if (strlen($ndd) > $maxndlen) {
                    $maxndlen = strlen($ndd);
                }
                $cidnamed[$cid] = true;
                $nameddegrees++;
            }
        }
        if ($osgood) {
            if ($maxndlen < 4) {
                $sidecolwidth = '45%';
            } else if ($maxndlen < 13) {
                $sidecolwidth = '40%';
            } else {
                $sidecolwidth = '30%';
            }
            echo '<td style="width: '.$sidecolwidth.'; text-align: right;"></td>';
            $nn = 100 - ($sidecolwidth * 2);
            $colwidth = ($nn / $this->length).'%';
            $textalign = 'right';
        } else {
            echo '<td style="width: 49%"></td>';
            $colwidth = (50 / $this->length).'%';
            $textalign = 'left';
        }
        for ($j = 0; $j < $this->length; $j++) {
            if (isset($n[$j])) {
                $str = $n[$j];
            } else {
                $str = $j + 1;
            }
            echo '<td style="width:'.$colwidth.'; text-align:center" class="'.$bg.' smalltext">'.$str.'</td>';
            if ($bg == 'c0') {
                $bg = 'c1';
            } else {
                $bg = 'c0';
            }
        }
        if ($this->precise == 1) {
            echo '<td style="width:'.$colwidth.'; text-align:center" class="'.$bg.'">'.
                get_string('notapplicable', 'questionnaire').'</td>';
        }
        if ($osgood) {
            echo '<td style="width:'.$sidecolwidth.'%;"></td>';
        }
        echo '</tr>';

        foreach ($this->choices as $cid => $choice) {
            // Do not print column names if named column exist.
            if (!array_key_exists($cid, $cidnamed)) {
                $str = 'q'."{$this->id}_$cid";
                echo '<tr>';
                $content = $choice->content;
                $contents = questionnaire_choice_values($content);
                if ($contents->modname) {
                    $content = $contents->text;
                }
                if ($osgood) {
                    list($content, $contentright) = preg_split('/[|]/', $content);
                }
                echo '<td style="text-align:'.$textalign.'">'.format_text($content, FORMAT_HTML).'&nbsp;</td>';
                $bg = 'c0';
                for ($j = 0; $j < $this->length; $j++) {
                    $checked = ((isset($data->$str) && ($j == $data->$str)) ? ' checked="checked"' : '');
                    // N/A column checked.
                    $checkedna = ((isset($data->$str) && ($data->$str == -1)) ? ' checked="checked"' : '');

                    if ($checked) {
                        echo '<td style="text-align:center;" class="selected">';
                        echo '<span class="selected">'.
                             '<input type="radio" name="'.$str.$j.$uniquetag++.'" checked="checked" /></span>';
                    } else {
                        echo '<td style="text-align:center;" class="'.$bg.'">';
                            echo '<span class="unselected">'.
                                 '<input type="radio" disabled="disabled" name="'.$str.$j.
                                    $uniquetag++.'" onclick="this.checked=false;" /></span>';
                    }
                    echo '</td>';
                    if ($bg == 'c0') {
                        $bg = 'c1';
                    } else {
                        $bg = 'c0';
                    }
                }
                if ($this->precise == 1) { // N/A column.
                    echo '<td style="width:auto; text-align:center;" class="'.$bg.'">';
                    if ($checkedna) {
                        echo '<span class="selected">'.
                             '<input type="radio" name="'.$str.$j.$uniquetag++.'na" checked="checked" /></span>';
                    } else {
                        echo '<span class="unselected">'.
                             '<input type="radio" name="'.$str.$uniquetag++.'na" onclick="this.checked=false;" /></span>';
                    }
                    echo '</td>';
                }
                if ($osgood) {
                    echo '<td>&nbsp;'.format_text($contentright, FORMAT_HTML).'</td>';
                }
                echo '</tr>';
            }
        }
        echo '</tbody></table>';
    }
}