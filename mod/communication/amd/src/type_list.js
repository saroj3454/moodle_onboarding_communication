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
 * Javascript to load and render the list of communication message types
 *
 * @module     mod_communication/type_list
 * @package    mod_communication
 * @copyright  2017 onwards Strategenics <contact@strategenics.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/notification', 'core/templates',
        'core/custom_interaction_events',
        'mod_communication/communication_types_repository'],
    function($, Notification, Templates, CustomEvents, CommunicationTypesRepository) {

        var SELECTORS = {
            EMPTY_MESSAGE: '[data-region="empty-message"]',
            ROOT: '[data-region="type-list-container"]',
            TYPE_LIST: '[data-region="type-list"]',
            TYPE_LIST_CONTENT: '[data-region="type-list-content"]',
            TYPE_LIST_GROUP_CONTAINER: '[data-region="type-list-group-container"]',
            LOADING_ICON_CONTAINER: '[data-region="loading-icon-container"]',
            VIEW_MORE_BUTTON: '[data-action="view-more"]'
        };

        /**
         * Set a flag on the element to indicate that it has completed
         * loading all type data.
         *
         * @method setLoadedAll
         * @private
         * @param {object} root The container element
         */
        var setLoadedAll = function(root) {
            root.attr('data-loaded-all', true);
        };

        /**
         * Check if all type data has finished loading.
         *
         * @method hasLoadedAll
         * @private
         * @param {object} root The container element
         * @return {bool} if the element has completed all loading
         */
        var hasLoadedAll = function(root) {
            return !!root.attr('data-loaded-all');
        };

        /**
         * Set the element state to loading.
         *
         * @method startLoading
         * @private
         * @param {object} root The container element
         */
        var startLoading = function(root) {
            var loadingIcon = root.find(SELECTORS.LOADING_ICON_CONTAINER),
                viewMoreButton = root.find(SELECTORS.VIEW_MORE_BUTTON);

            root.addClass('loading');
            loadingIcon.removeClass('hidden');
            viewMoreButton.prop('disabled', true);
        };

        /**
         * Remove the loading state from the element.
         *
         * @method stopLoading
         * @private
         * @param {object} root The container element
         */
        var stopLoading = function(root) {
            var loadingIcon = root.find(SELECTORS.LOADING_ICON_CONTAINER),
                viewMoreButton = root.find(SELECTORS.VIEW_MORE_BUTTON);

            root.removeClass('loading');
            loadingIcon.addClass('hidden');

            if (!hasLoadedAll(root)) {
                // Only enable the button if we've got more types to load.
                viewMoreButton.prop('disabled', false);
            }
        };

        /**
         * Check if the element is currently loading some type data.
         *
         * @method isLoading
         * @private
         * @param {object} root The container element
         * @returns {Boolean}
         */
        var isLoading = function(root) {
            return root.hasClass('loading');
        };

        /**
         * Flag the root element to remember that it contains types.
         *
         * @method setHasContent
         * @private
         * @param {object} root The container element
         */
        var setHasContent = function(root) {
            root.attr('data-has-types', true);
        };

        /**
         * Check if the root element has had types loaded.
         *
         * @method hasContent
         * @private
         * @param {object} root The container element
         * @return {bool}
         */
        var hasContent = function(root) {
            return root.attr('data-has-types') ? true : false;
        };

        /**
         * Update the visibility of the content area. The content area
         * is hidden if we have no types.
         *
         * @method updateContentVisibility
         * @private
         * @param {object} root The container element
         * @param {int} typeCount A count of the types we just received.
         */
        var updateContentVisibility = function(root, typeCount) {
            if (typeCount) {
                // We've rendered some types, let's remember that.
                setHasContent(root);
            } else {
                // If this is the first time trying to load types and
                // we don't have any then there isn't any so let's show
                // the empty message.
                if (!hasContent(root)) {
                    hideContent(root);
                }
            }
        };

        /**
         * Hide the content area and display the empty content message.
         *
         * @method hideContent
         * @private
         * @param {object} root The container element
         */
        var hideContent = function(root) {
            root.find(SELECTORS.TYPE_LIST_CONTENT).addClass('hidden');
            root.find(SELECTORS.EMPTY_MESSAGE).removeClass('hidden');
        };

        /**
         * Render a group of message types and add them to the type
         * list.
         *
         * @method renderGroup
         * @private
         * @param {object}  group           The group container element
         * @param {array}   types  The list of communication message types
         * @param {string}  templateName    The template name
         * @return {promise} Resolved when the elements are attached to the DOM
         */
        var renderGroup = function(group, types, templateName) {

            group.removeClass('hidden');

            return Templates.render(
                templateName,
                {types: types}
            ).done(function(html, js) {
                Templates.appendNodeContents(group.find(SELECTORS.TYPE_LIST), html, js);
            });
        };

        /**
         * Render the given message types in the container element.
         *
         * @method render
         * @private
         * @param {object}  root            The container element
         * @param {array}   messageTypes  A list of communication message types
         * @return {promise} Resolved with a count of the number of rendered types
         */
        var render = function(root, messageTypes) {
            var renderCount = 0;

            return $.when.apply($, $.map(root.find(SELECTORS.TYPE_LIST_GROUP_CONTAINER), function(container) {
                var types = messageTypes;

                if (types.length) {
                    renderCount += types.length;
                    return renderGroup($(container), types, 'mod_communication/type-list-items');
                } else {
                    return null;
                }
            })).then(function() {
                return renderCount;
            });
        };

        /**
         * Retrieve a list of message types, render and append them to the end of the
         * existing list. The types will be loaded based on the set of data attributes
         * on the root element.
         *
         * This function can be provided with a jQuery promise. If it is then it won't
         * attempt to load data by itself, instead it will use the given promise.
         *
         * The provided promise must resolve with an an object that has an types key
         * and value is an array of message types.
         * E.g.
         * { types: ['type 1', 'type 2'] }
         *
         * @method load
         * @param {object} root The root element of the type list
         * @param {object} promise A jQuery promise resolved with types
         * @return {promise} A jquery promise
         */
        var load = function(root, promise) {
            root = $(root);
            var limit = +root.attr('data-limit'),
                lastId = root.attr('data-last-id');

            // Don't load twice.
            if (isLoading(root)) {
                return $.Deferred().resolve();
            }

            startLoading(root);

            // If we haven't been provided a promise to resolve the
            // data then we will load our own.
            if (typeof promise == 'undefined') {
                var args = {
                    from: 0,
                    limit: limit
                };

                if (lastId) {
                    args.from = lastId;
                }

                promise = CommunicationTypesRepository.query(args);
            }

            // Request data from the server.
            return promise.then(function(result) {
                return result;
            }).then(function(types) {
                if (!types.length || (types.length < limit)) {
                    // We have no more types so mark the list as done.
                    setLoadedAll(root);
                }

                if (types.length) {
                    // Remember the last id we've seen.
                    root.attr('data-last-id', types[types.length - 1].id);

                    // Render the types.
                    return render(root, types).then(function(renderCount) {
                        updateContentVisibility(root, types.length);

                        if (renderCount < types.length) {
                            // If the number of types that was rendered is less than
                            // the number we sent for rendering we can assume that there
                            // are no groups to add them in. Since the ordering of the
                            // types is guaranteed it means that any future requests will
                            // also yield types that can't be rendered, so let's not bother
                            // sending any more requests.
                            setLoadedAll(root);
                        }
                    });
                } else {
                    updateContentVisibility(root, types.length);
                }
            }).fail(
                Notification.exception
            ).always(function() {
                stopLoading(root);
            });
        };

        /**
         * Register the event listeners for the container element.
         *
         * @method registerEventListeners
         * @param {object} root The root element of the event list
         */
        var registerEventListeners = function(root) {
            CustomEvents.define(root, [CustomEvents.events.activate]);
            root.on(CustomEvents.events.activate, SELECTORS.VIEW_MORE_BUTTON, function() {
                load(root);
            });
        };

        return {
            init: function(root) {
                root = $(root);
                load(root);
                registerEventListeners(root);
            },
            registerEventListeners: registerEventListeners,
            load: load,
            rootSelector: SELECTORS.ROOT,
        };
    }
);
