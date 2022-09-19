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
 * Javascript to load and render the list of communication templates for a
 * given message type.
 *
 * @module     mod_communication/template_list
 * @package    mod_communication
 * @copyright  2017 onwards Strategenics <contact@strategenics.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/notification', 'core/templates',
        'core/custom_interaction_events',
        'mod_communication/communication_templates_repository'],
    function($, Notification, Templates, CustomEvents, CommunicationTemplatesRepository) {

        var SELECTORS = {
            EMPTY_MESSAGE: '[data-region="empty-message"]',
            ROOT: '[data-region="template-list-container"]',
            TEMPLATE_LIST: '[data-region="template-list"]',
            TEMPLATE_LIST_CONTENT: '[data-region="template-list-content"]',
            TEMPLATE_LIST_GROUP_CONTAINER: '[data-region="template-list-group-container"]',
            LOADING_ICON_CONTAINER: '[data-region="loading-icon-container"]',
            VIEW_MORE_BUTTON: '[data-action="view-more"]'
        };

        /**
         * Set a flag on the element to indicate that it has completed
         * loading all communication templates.
         *
         * @method setLoadedAll
         * @private
         * @param {object} root The container element
         */
        var setLoadedAll = function(root) {
            root.attr('data-loaded-all', true);
        };

        /**
         * Check if all communication templates has finished loading.
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
                // Only enable the button if we've got more events to load.
                viewMoreButton.prop('disabled', false);
            }
        };

        /**
         * Check if the element is currently loading some communication templates.
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
         * Flag the root element to remember that it contains communication templates.
         *
         * @method setHasContent
         * @private
         * @param {object} root The container element
         */
        var setHasContent = function(root) {
            root.attr('data-has-templates', true);
        };

        /**
         * Check if the root element has had communication templates loaded.
         *
         * @method hasContent
         * @private
         * @param {object} root The container element
         * @return {bool}
         */
        var hasContent = function(root) {
            return root.attr('data-has-templates') ? true : false;
        };

        /**
         * Update the visibility of the content area. The content area
         * is hidden if we have no communication templates.
         *
         * @method updateContentVisibility
         * @private
         * @param {object} root The container element
         * @param {int} templateCount A count of the communication templates we just received.
         */
        var updateContentVisibility = function(root, templateCount) {
            if (templateCount) {
                viewMoreButton = root.find(SELECTORS.VIEW_MORE_BUTTON);
                    if(templateCount==20){
                        viewMoreButton.prop('disabled', false);
                    }else{
                         viewMoreButton.prop('disabled', true);
                    }
                   
                // We've rendered some templates, let's remember that.
                setHasContent(root);
            } else {
                // If this is the first time trying to load templates and
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
            root.find(SELECTORS.TEMPLATE_LIST_CONTENT).addClass('hidden');
            root.find(SELECTORS.EMPTY_MESSAGE).removeClass('hidden');
        };

        /**
         * Render a group of communication templates and add them to the template
         * list.
         *
         * @method renderGroup
         * @private
         * @param {object}  group           The group container element
         * @param {array}   communicationTemplates  The list of communication templates
         * @param {string}  templateName    The template name
         * @return {promise} Resolved when the elements are attached to the DOM
         */
        var renderGroup = function(group, communicationTemplates, templateName) {

            group.removeClass('hidden');

            return Templates.render(
                templateName,
                {templates: communicationTemplates}
            ).done(function(html, js) {
                Templates.appendNodeContents(group.find(SELECTORS.TEMPLATE_LIST), html, js);
            });
        };

        /**
         * Render the given communication templates in the container element.
         *
         * @method render
         * @private
         * @param {object}  root            The container element
         * @param {array}   communicationTemplates  A list of communication templates
         * @return {promise} Resolved with a count of the number of rendered templates
         */
        var render = function(root, communicationTemplates) {
            var renderCount = 0;

            return $.when.apply($, $.map(root.find(SELECTORS.TEMPLATE_LIST_GROUP_CONTAINER), function(container) {
                var templates = communicationTemplates;

                if (templates.length) {
                    renderCount += templates.length;
                    return renderGroup($(container), templates, 'mod_communication/template-list-items');
                } else {
                    return null;
                }
            })).then(function() {
                return renderCount;
            });
        };

        /**
         * Retrieve a list of communication templates, render and append them to the end of the
         * existing list. The templates will be loaded based on the set of data attributes
         * on the root element.
         *
         * This function can be provided with a jQuery promise. If it is then it won't
         * attempt to load data by itself, instead it will use the given promise.
         *
         * The provided promise must resolve with an an object that has a templates key
         * and value is an array of communication templates.
         * E.g.
         * { templates: ['template 1', 'template 2'] }
         *
         * @method load
         * @param {object} root The root element of the template list
         * @param {object} promise A jQuery promise resolved with templates
         * @return {promise} A jquery promise
         */
        var load = function(root, promise) {
            root = $(root);
            var limit = +root.attr('data-limit'),
                lastId = root.attr('data-last-id'),
                messagetype = $('#id_messagetypefilter').val();

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

                if (messagetype) {
                    args.messagetype = messagetype;
                } else {
                    args.messagetype = 0;
                }

                if (lastId) {
                    args.from = lastId;
                }

                promise = CommunicationTemplatesRepository.query(args);
            }

            // Request data from the server.
            return promise.then(function(result) {
                console.log('result',result);
                return result;
            }).then(function(communicationTemplates) {
                console.log('communicationTemplates.length',communicationTemplates.length);
                if (!communicationTemplates.length || (communicationTemplates.length < limit)) {
                    // We have no more templates so mark the list as done.
                    setLoadedAll(root);
                }

                if (communicationTemplates.length) {
                    // Remember the last id we've seen.
                    root.attr('data-last-id', communicationTemplates[communicationTemplates.length - 1].id);

                    // Render the templates.
                    return render(root, communicationTemplates).then(function(renderCount) {
                        console.log('renderCount',renderCount);
                        updateContentVisibility(root, communicationTemplates.length);

                        if (renderCount < communicationTemplates.length) {
                            // If the number of templates that was rendered is less than
                            // the number we sent for rendering we can assume that there
                            // are no groups to add them in. Since the ordering of the
                            // templates is guaranteed it means that any future requests will
                            // also yield templates that can't be rendered, so let's not bother
                            // sending any more requests.
                            setLoadedAll(root);
                        }
                    });
                } else {
                    updateContentVisibility(root, communicationTemplates.length);
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
