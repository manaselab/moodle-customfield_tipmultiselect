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
 * Field controller for customfield_tipmultiselect.
 *
 * @package    customfield_tipmultiselect
 * @copyright  2026 3iPunt (contacte@tresipunt.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_tipmultiselect;

use local_tipmultiselect\api;
use MoodleQuickForm;

/**
 * Multiselect custom field fed by a local_tipmultiselect option category.
 *
 * The bound category id lives in configdata. It cannot be changed once the
 * field has stored data.
 *
 * @package    customfield_tipmultiselect
 * @copyright  2026 3iPunt (contacte@tresipunt.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class field_controller extends \core_customfield\field_controller {
    /** @var string Customfield type name. */
    const TYPE = 'tipmultiselect';

    /**
     * Adds the category setting to the field configuration form.
     *
     * @param  MoodleQuickForm $mform The form.
     * @return void
     */
    public function config_form_definition(MoodleQuickForm $mform) {
        $mform->addElement(
            'header',
            'header_specificsettings',
            get_string('specificsettings', 'customfield_tipmultiselect')
        );
        $mform->setExpanded('header_specificsettings', true);

        $categories = api::get_categories();
        $mform->addElement(
            'select',
            'configdata[categoryid]',
            get_string('categoryid', 'customfield_tipmultiselect'),
            ['' => get_string('choosedots')] + $categories
        );
        $mform->setType('configdata[categoryid]', PARAM_INT);
        $mform->addHelpButton('configdata[categoryid]', 'categoryid', 'customfield_tipmultiselect');

        $fieldid = (int) $this->get('id');
        if ($fieldid > 0 && self::field_has_data($fieldid)) {
            $mform->hardFreeze('configdata[categoryid]');
            $mform->setConstant('configdata[categoryid]', $this->get_category_id());
        }
    }

    /**
     * Validates the category setting.
     *
     * @param  array $data  Submitted data.
     * @param  array $files Submitted files.
     * @return array Validation errors indexed by element name.
     */
    public function config_form_validation(array $data, $files = []): array {
        $errors = [];

        $categoryid = (int) ($data['configdata']['categoryid'] ?? 0);
        if ($categoryid <= 0 || !array_key_exists($categoryid, api::get_categories())) {
            $errors['configdata[categoryid]'] = get_string('errorcategoryrequired', 'customfield_tipmultiselect');
            return $errors;
        }

        $fieldid = (int) ($data['id'] ?? 0);
        if ($fieldid > 0 && self::field_has_data($fieldid)) {
            $current = $this->get_category_id();
            if ($current > 0 && $current !== $categoryid) {
                $errors['configdata[categoryid]'] = get_string('errorcategorylocked', 'customfield_tipmultiselect');
            }
        }

        return $errors;
    }

    /**
     * Returns the bound option category id.
     *
     * @return int The local_tipmultiselect category id, or 0 if not configured.
     */
    public function get_category_id(): int {
        return (int) $this->get_configdata_property('categoryid');
    }

    /**
     * Checks whether a field already has stored data.
     *
     * @param  int $fieldid Custom field id.
     * @return bool True if any non-empty value is stored for the field.
     */
    private static function field_has_data(int $fieldid): bool {
        global $DB;

        return $DB->record_exists_select(
            'customfield_data',
            "fieldid = :fieldid AND charvalue IS NOT NULL AND charvalue <> ''",
            ['fieldid' => $fieldid]
        );
    }
}
