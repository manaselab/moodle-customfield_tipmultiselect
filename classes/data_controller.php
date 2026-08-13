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
 * Data controller for customfield_tipmultiselect.
 *
 * @package    customfield_tipmultiselect
 * @copyright  2026 3iPunt (contacte@tresipunt.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_tipmultiselect;

use local_tipmultiselect\api;
use MoodleQuickForm;
use stdClass;

/**
 * Data controller storing the selection as a comma-wrapped CSV of option ids.
 *
 * The value is stored in charvalue (e.g. ",3,7,12,"); encoding and decoding
 * go through the local_tipmultiselect API so the convention is shared with
 * the profile field plugin.
 *
 * @package    customfield_tipmultiselect
 * @copyright  2026 3iPunt (contacte@tresipunt.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_controller extends \core_customfield\data_controller {
    /** @var int Maximum length of the charvalue column. */
    private const CHARVALUE_MAX_LENGTH = 1333;

    /**
     * Returns the name of the customfield_data column used by this type.
     *
     * @return string The column name.
     */
    public function datafield(): string {
        return 'charvalue';
    }

    /**
     * Returns the default value as stored in the database.
     *
     * @return string An empty selection.
     */
    public function get_default_value() {
        return '';
    }

    /**
     * Adds the autocomplete element to the instance form.
     *
     * Stored options that are hidden (or no longer exist in the category)
     * are added to the list so an unrelated save never drops them.
     *
     * @param  MoodleQuickForm $mform The form.
     * @return void
     */
    public function instance_form_definition(MoodleQuickForm $mform) {
        /** @var field_controller $field */
        $field = $this->get_field();
        $categoryid = $field->get_category_id();
        $options = $categoryid > 0 ? api::get_options($categoryid) : [];

        $storedids = api::decode_ids((string) $this->get_value());
        $missing = array_diff($storedids, array_keys($options));
        if ($missing !== []) {
            $options += api::get_option_names($missing);
        }

        $elementname = $this->get_form_element_name();
        $mform->addElement(
            'autocomplete',
            $elementname,
            $field->get_formatted_name(),
            $options,
            ['multiple' => true]
        );

        if ($field->get_configdata_property('required')) {
            $mform->addRule($elementname, null, 'required', null, 'client');
        }
    }

    /**
     * Validates the submitted selection.
     *
     * @param  array $data  Submitted data.
     * @param  array $files Submitted files.
     * @return array Validation errors indexed by element name.
     */
    public function instance_form_validation(array $data, array $files): array {
        $errors = parent::instance_form_validation($data, $files);
        $elementname = $this->get_form_element_name();
        $submitted = (array) ($data[$elementname] ?? []);

        if ($this->get_field()->get_configdata_property('required') && $submitted === []) {
            $errors[$elementname] = get_string('err_required', 'form');
        }

        if (strlen(api::encode_ids($submitted)) > self::CHARVALUE_MAX_LENGTH) {
            $errors[$elementname] = get_string('errortoomanyselections', 'customfield_tipmultiselect');
        }

        return $errors;
    }

    /**
     * Converts the submitted array of ids into the stored CSV before saving.
     *
     * Forged ids and ids from other categories are discarded; hidden
     * options that exist in the category are kept.
     *
     * @param  stdClass $datanew Data coming from the form.
     * @return void
     */
    public function instance_form_save(stdClass $datanew) {
        $elementname = $this->get_form_element_name();
        if (!property_exists($datanew, $elementname)) {
            return;
        }

        /** @var field_controller $field */
        $field = $this->get_field();
        $ids = (array) $datanew->{$elementname};
        $datanew->{$elementname} = api::encode_ids(api::validate_ids($field->get_category_id(), $ids));

        parent::instance_form_save($datanew);
    }

    /**
     * Loads the stored CSV value as an array of ids for the form element.
     *
     * @param  stdClass $instance The instance being edited.
     * @return void
     */
    public function instance_form_before_set_data(stdClass $instance) {
        $instance->{$this->get_form_element_name()} = api::decode_ids((string) $this->get_value());
    }

    /**
     * Returns the selection in a human-readable format.
     *
     * @return string|null Comma-separated formatted names, or null if empty.
     */
    public function export_value() {
        $ids = api::decode_ids((string) $this->get_value());
        if ($ids === []) {
            return null;
        }
        return implode(', ', api::get_option_names($ids));
    }
}
