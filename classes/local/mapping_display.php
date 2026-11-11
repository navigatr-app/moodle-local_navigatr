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
 * Resolves the provider and badge shown for a course's stored mapping.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\local;

/**
 * Builds the provider and badge display data for the course settings page.
 */
class mapping_display {
    /**
     * Resolve the provider and badge for a stored mapping.
     *
     * The provider name comes from the providers list the page already loads. The badge
     * comes from the cache, then the API, then the stored row, so neither value is ever null.
     *
     * @param \stdClass $mapping Row from local_navigatr_map
     * @param array $providers Providers list from the Navigatr API
     * @param api_client|null $client API client, created when needed
     * @return array ['provider' => array, 'badge' => array]
     */
    public static function resolve(\stdClass $mapping, array $providers, ?api_client $client = null): array {
        return [
            'provider' => self::resolve_provider($mapping, $providers),
            'badge' => self::resolve_badge($mapping, $client),
        ];
    }

    /**
     * Find the provider name in the providers list.
     *
     * @param \stdClass $mapping Row from local_navigatr_map
     * @param array $providers Providers list
     * @return array Provider data with a name
     */
    protected static function resolve_provider(\stdClass $mapping, array $providers): array {
        foreach ($providers as $provider) {
            if (is_array($provider) && isset($provider['id'], $provider['name']) && $provider['id'] == $mapping->provider_id) {
                return ['name' => $provider['name']];
            }
        }
        return ['name' => get_string('unknown_provider', 'local_navigatr')];
    }

    /**
     * Get badge details from the cache, the API, or the stored row.
     *
     * @param \stdClass $mapping Row from local_navigatr_map
     * @param api_client|null $client API client
     * @return array Badge data
     */
    protected static function resolve_badge(\stdClass $mapping, ?api_client $client): array {
        global $DB;

        $cached = cache::get_badge($mapping->badge_id);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $client = $client ?? new api_client();
            $response = $client->get("/badge/{$mapping->badge_id}");
            if ($response->ok && is_array($response->body)) {
                $badge = $response->body;
                cache::set_badge($mapping->badge_id, $badge);

                // Keep the stored badge metadata in step with the API.
                $name = $badge['name'] ?? null;
                $imageurl = $badge['image_url'] ?? null;
                if ($mapping->badge_name !== $name || $mapping->badge_image_url !== $imageurl) {
                    $mapping->badge_name = $name;
                    $mapping->badge_image_url = $imageurl;
                    $mapping->timemodified = time();
                    $DB->update_record('local_navigatr_map', $mapping);
                }
                return $badge;
            }
        } catch (\Exception $e) {
            // The API is unreachable, so fall through to the stored data.
            unset($e);
        }

        // Lookup failed, so show the stored data.
        return [
            'name' => $mapping->badge_name ?? 'Unknown Badge',
            'image_url' => $mapping->badge_image_url ?? null,
            'description' => null,
            'url' => null,
        ];
    }
}
