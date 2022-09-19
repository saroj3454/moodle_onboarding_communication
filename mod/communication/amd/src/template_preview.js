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

define(['jquery', 'core/notification', 'core/templates', 'core/ajax'],
    function($, notification, templates, ajax) {

        /**
         * Condition config object.
         * @param {String} selectSelector The select box selector.
         * @param {String} inputSelector The hidden input field selector.
         * @param {String} triggerSelector The trigger selector.
         * @param {Number} fieldId Current field's ID.
         */
        var ConditionConfig = function(triggerSelector, inputSelector, styleSelector) {
            this.triggerSelector = triggerSelector;
            this.inputSelector = inputSelector;
            this.styleSelector = styleSelector;

            $(triggerSelector).click(this.showConfig.bind(this));
        };

        /** @var {String} The select box selector. */
        ConditionConfig.prototype.styleSelector = null;
        /** @var {String} The hidden field selector. */
        ConditionConfig.prototype.inputSelector = null;
        /** @var {String} The trigger selector. */
        ConditionConfig.prototype.triggerSelector = null;

        /**
         * Displays the condition configuration dialogue.
         *
         * @method showConfig
         */
        ConditionConfig.prototype.showConfig = function() {
            var request = {
                methodname: 'mod_communication_generate_preview',
                args: {
                    text: $(this.inputSelector).val(),
                    style: $(this.styleSelector).is(':checked')
                }
            };

            var promise = ajax.call([request])[0];

            promise.fail(notification.exception);
            var newWin = window.open('', 'template_preview');
            newWin.document.write('Generating the preview... This takes seconds.');
            newWin.focus();

            promise.then(function (result) {
                newWin.document.documentElement.innerHTML = result;
            });
        };

        return {

            /**
             * Main initialisation.
             *
             * @param {String} selectSelector The select box selector.
             * @param {String} inputSelector The hidden input field selector.
             * @param {String} triggerSelector The trigger selector.
             * @param {Number} fieldId The current fieldid.
             * @return {ConditionConfig} A new instance of ConditionConfig.
             * @method init
             */
            init: function(triggerSelector, inputSelector, styleSelector) {
                return new ConditionConfig(triggerSelector, inputSelector, styleSelector);
            }
        };
    }
);
