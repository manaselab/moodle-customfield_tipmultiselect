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
 * Privacy provider for customfield_tipmultiselect.
 *
 * @package    customfield_tipmultiselect
 * @copyright  2026 3iPunt (contacte@tresipunt.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_tipmultiselect\privacy;

use core_customfield\data_controller;
use core_customfield\privacy\customfield_provider;
use core_privacy\local\request\writer;
use stdClass;

/**
 * Privacy provider: the plugin data is handled by the core_customfield subsystem.
 *
 * @package    customfield_tipmultiselect
 * @copyright  2026 3iPunt (contacte@tresipunt.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\null_provider, customfield_provider {
    /**
     * Returns the language string identifier explaining why no data is stored.
     *
     * @return string The language string identifier.
     */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }

    /**
     * Exports customfield data using the human-readable value.
     *
     * @param  data_controller $data       The data controller being exported.
     * @param  stdClass        $exportdata The export data object.
     * @param  array           $subcontext The export subcontext.
     * @return void
     */
    public static function export_customfield_data(data_controller $data, stdClass $exportdata, array $subcontext) {
        $context = $data->get_context();
        $exportdata->value = $data->export_value();
        writer::with_context($context)->export_data($subcontext, $exportdata);
    }

    /**
     * Hook before data records are deleted; nothing extra is stored.
     *
     * @param  string $dataidstest SQL fragment matching the data ids.
     * @param  array  $params      SQL parameters.
     * @param  array  $contextids  Affected context ids.
     * @return void
     */
    public static function before_delete_data(string $dataidstest, array $params, array $contextids) {
    }

    /**
     * Hook before field configurations are deleted; nothing extra is stored.
     *
     * @param  string $fieldidstest SQL fragment matching the field ids.
     * @param  array  $params       SQL parameters.
     * @param  array  $contextids   Affected context ids.
     * @return void
     */
    public static function before_delete_fields(string $fieldidstest, array $params, array $contextids) {
    }
}
