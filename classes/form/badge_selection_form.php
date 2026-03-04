<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,.
// but WITHOUT ANY WARRANTY; without even the implied warranty of.
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the.
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Badge selection form for Navigatr plugin.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Badge selection form class.
 */
class badge_selection_form extends \moodleform {
    /**
     * Form definition.
     */
    protected function definition() {
        global $DB;

        $mform = $this->_form;
        $courseid = $this->_customdata['courseid'];
        $providerid = $this->_customdata['provider_id'];
        $providername = $this->_customdata['provider_name'];

        // Add hidden course ID.
        $mform->addElement('hidden', 'id', $courseid);
        $mform->setType('id', PARAM_INT);

        // Add hidden provider ID.
        $mform->addElement('hidden', 'provider_id', $providerid);
        $mform->setType('provider_id', PARAM_INT);

        // Show selected provider.
        $providerinfo = \html_writer::div(
            get_string('selected_provider', 'local_navigatr') . ': ' . s($providername),
            'alert alert-info'
        );
        $mform->addElement('html', $providerinfo);

        // Get badges for the selected provider.
        $badges = $this->get_badges($providerid);
        $badgeoptions = ['' => get_string('select_badge', 'local_navigatr')];

        // Ensure badges is an array before iterating.
        if (is_array($badges)) {
            foreach ($badges as $badge) {
                if (isset($badge['id']) && isset($badge['name'])) {
                    $badgeoptions[$badge['id']] = $badge['name'];
                }
            }
        }

        $mform->addElement('select', 'badge_id', get_string('badge', 'local_navigatr'), $badgeoptions);
        $mform->setType('badge_id', PARAM_INT);
        $mform->addHelpButton('badge_id', 'badge', 'local_navigatr');
        $mform->addRule('badge_id', get_string('required'), 'required', null, 'client');

        // Add submit button.
        $mform->addElement(
            'submit',
            'select_badge',
            get_string('select_badge_continue', 'local_navigatr'),
            ['class' => 'btn-primary mr-2']
        );

        // Add cancel button.
        $mform->addElement('cancel', 'cancel', get_string('cancel'), ['class' => 'btn-secondary']);
    }

    /**
     * Get badges for a provider.
     *
     * @param int $providerid Provider ID
     * @return array Badges array
     */
    private function get_badges($providerid) {
        try {
            // Check cache first.
            $cached = \local_navigatr\local\cache::get_badges($providerid, 1, 50);
            if ($cached !== null && $cached !== false && is_array($cached)) {
                return $cached;
            }

            // Fetch from API.
            $client = new \local_navigatr\local\api_client();
            $apipath = "/badge?provider_id={$providerid}&status=Published&source=Internal&page=1&size=50";
            $response = $client->get($apipath);

            if ($response->ok && isset($response->body['items']) && is_array($response->body['items'])) {
                $badges = $response->body['items'];
                \local_navigatr\local\cache::set_badges($providerid, 1, 50, $badges);
                return $badges;
            }

            return [];
        } catch (\Exception $e) {
            // Trigger event for failed API request.
            $eventdata = \local_navigatr\event\api_request_failed::create([
                'context' => \context_system::instance(),
                'other' => [
                    'operation' => 'get_badges',
                    'error' => $e->getMessage(),
                ],
            ]);
            $eventdata->trigger();
            return [];
        }
    }
}
