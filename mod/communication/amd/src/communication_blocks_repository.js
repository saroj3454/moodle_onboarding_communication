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
 * A javascript module to retrieve communication blocks from the server.
 *
 * @module     mod_communication/communication_blocks_repository
 * @class      repository
 * @package    mod_communication
 * @copyright  2017 onwards Strategenics <contact@strategenics.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {

    var DEFAULT_LIMIT = 20;

    /**
     * Retrieve a list of communication blocks.
     *
     * Valid args are:
     * int from  Only get blocks after this number
     * int limit Limit the number of results returned
     *
     * @method query
     * @param {object} args The request arguments
     * @return {promise} Resolved with an array of the communication blocks
     */
    var query = function(args) {
        if (!args.hasOwnProperty('from')) {
            args.from = 0;
        }
        if (!args.hasOwnProperty('limit')) {
            args.limit = DEFAULT_LIMIT;
        }

        args.limitfrom = args.from;
        delete args.from;

        args.limitnum = args.limit;
        delete args.limit;

        var request = {
            methodname: 'mod_communication_get_blocks',
            args: args
        };

        var promise = Ajax.call([request])[0];

        promise.fail(Notification.exception);

        return promise;
    };

    return {
        query: query
    };
});
