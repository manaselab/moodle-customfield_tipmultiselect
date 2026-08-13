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
 * Tests for the multiselect custom field data controller.
 *
 * @package    customfield_tipmultiselect
 * @copyright  2026 3iPunt (contacte@tresipunt.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_tipmultiselect;

use advanced_testcase;
use core_customfield\data_controller as core_data_controller;
use core_customfield\field_controller as core_field_controller;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

/**
 * Tests for {@see data_controller}.
 *
 * @package    customfield_tipmultiselect
 * @copyright  2026 3iPunt (contacte@tresipunt.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(data_controller::class)]
final class data_controller_test extends advanced_testcase {
    /**
     * Creates an option category with options and a course custom field bound to it.
     *
     * @return array [field controller, course record, option models array, option category].
     */
    private function create_fixture(): array {
        $optiongenerator = $this->getDataGenerator()->get_plugin_generator('local_tipmultiselect');
        $optioncategory = $optiongenerator->create_category();
        $options = [
            $optiongenerator->create_option(['categoryid' => $optioncategory->id, 'name' => 'One']),
            $optiongenerator->create_option(['categoryid' => $optioncategory->id, 'name' => 'Two']),
            $optiongenerator->create_option(['categoryid' => $optioncategory->id, 'name' => 'Three']),
        ];

        $fieldgenerator = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        $fieldcategory = $fieldgenerator->create_category();
        $field = $fieldgenerator->create_field([
            'categoryid' => $fieldcategory->get('id'),
            'shortname' => 'areas',
            'name' => 'Areas',
            'type' => 'tipmultiselect',
            'configdata' => ['categoryid' => $optioncategory->id, 'required' => 0],
        ]);

        $course = $this->getDataGenerator()->create_course();

        return [$field, $course, $options, $optioncategory];
    }

    /**
     * Returns a fresh data controller for the field and course.
     *
     * @param  core_field_controller $field  The field controller.
     * @param  stdClass              $course The course record.
     * @return core_data_controller The data controller.
     */
    private function get_data_controller(core_field_controller $field, stdClass $course): core_data_controller {
        return core_data_controller::create(0, (object) [
            'instanceid' => $course->id,
            'contextid' => \context_course::instance($course->id)->id,
        ], $field);
    }

    /**
     * Saving submitted ids stores the comma-wrapped CSV in charvalue.
     */
    public function test_save_and_load_round_trip(): void {
        global $DB;
        $this->resetAfterTest();

        [$field, $course, $options] = $this->create_fixture();

        $data = $this->get_data_controller($field, $course);
        $data->instance_form_save((object) [
            'customfield_areas' => [$options[0]->id, $options[2]->id],
        ]);

        $stored = $DB->get_field('customfield_data', 'charvalue', ['fieldid' => $field->get('id'), 'instanceid' => $course->id]);
        $this->assertSame(",{$options[0]->id},{$options[2]->id},", $stored);

        // Reload and check the form gets the ids back as an array.
        $data = core_data_controller::create((int) $DB->get_field('customfield_data', 'id', ['fieldid' => $field->get('id')]));
        $instance = new stdClass();
        $data->instance_form_before_set_data($instance);
        $this->assertSame([$options[0]->id, $options[2]->id], $instance->customfield_areas);
    }

    /**
     * Forged ids and ids from another category are discarded on save.
     */
    public function test_forged_ids_are_discarded(): void {
        global $DB;
        $this->resetAfterTest();

        [$field, $course, $options] = $this->create_fixture();
        $optiongenerator = $this->getDataGenerator()->get_plugin_generator('local_tipmultiselect');
        $othercategory = $optiongenerator->create_category();
        $foreign = $optiongenerator->create_option(['categoryid' => $othercategory->id]);

        $data = $this->get_data_controller($field, $course);
        $data->instance_form_save((object) [
            'customfield_areas' => [$options[1]->id, $foreign->id, 999999],
        ]);

        $stored = $DB->get_field('customfield_data', 'charvalue', ['fieldid' => $field->get('id'), 'instanceid' => $course->id]);
        $this->assertSame(",{$options[1]->id},", $stored);
    }

    /**
     * export_value() renders the formatted option names, or null when empty.
     */
    public function test_export_value(): void {
        $this->resetAfterTest();

        [$field, $course, $options] = $this->create_fixture();

        $data = $this->get_data_controller($field, $course);
        $data->instance_form_save((object) [
            'customfield_areas' => [$options[0]->id, $options[1]->id],
        ]);
        $this->assertSame('One, Two', $data->export_value());

        $empty = $this->get_data_controller($field, $this->getDataGenerator()->create_course());
        $this->assertNull($empty->export_value());
    }

    /**
     * A stored option that is later hidden keeps being offered and displayed.
     */
    public function test_hidden_stored_option_is_preserved(): void {
        global $DB;
        $this->resetAfterTest();

        [$field, $course, $options] = $this->create_fixture();

        $data = $this->get_data_controller($field, $course);
        $data->instance_form_save((object) [
            'customfield_areas' => [$options[0]->id, $options[1]->id],
        ]);

        $options[0]->visible = 0;
        $options[0]->save();

        $dataid = (int) $DB->get_field('customfield_data', 'id', ['fieldid' => $field->get('id')]);
        $data = core_data_controller::create($dataid);
        $this->assertSame('One, Two', $data->export_value());

        // Re-saving the same selection keeps the hidden option.
        $data->instance_form_save((object) [
            'customfield_areas' => [$options[0]->id, $options[1]->id],
        ]);
        $stored = $DB->get_field('customfield_data', 'charvalue', ['id' => $dataid]);
        $this->assertSame(",{$options[0]->id},{$options[1]->id},", $stored);
    }

    /**
     * Too many selections for the charvalue column are rejected in validation.
     */
    public function test_too_many_selections_validation(): void {
        $this->resetAfterTest();

        [$field, $course] = $this->create_fixture();
        $data = $this->get_data_controller($field, $course);

        // Fake a huge submission: enough 7-digit ids to exceed 1333 chars.
        $ids = range(1000000, 1000200);
        $errors = $data->instance_form_validation(['customfield_areas' => $ids], []);

        $this->assertArrayHasKey('customfield_areas', $errors);
    }
}
