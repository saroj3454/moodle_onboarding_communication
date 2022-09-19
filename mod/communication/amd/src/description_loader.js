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

define(['jquery'],
    function($) {

        /**
         * Condition config object.
         * @param {String} sourseSelector The trigger selector.
         * @param {String} descriptionSelector The hidden input field selector.
         * @param {Array} dictionary Translation map.
         */
        var DescriptionLoader = function(sourseSelector, descriptionSelector, dictionary) {
            this.sourceSelector = sourseSelector;
            this.descriptionSelector = descriptionSelector;
            this.dictionary = dictionary;

            $(sourseSelector).change(this.showDescription.bind(this));
            this.showDescription();
        };

        /** @var {Array} The dictionary. */
        DescriptionLoader.prototype.dictionary = null;
        /** @var {String} The hidden field selector. */
        DescriptionLoader.prototype.descriptionSelector = null;
        /** @var {String} The trigger selector. */
        DescriptionLoader.prototype.sourceSelector = null;

        /**
         * Displays the condition configuration dialogue.
         *
         * @method showConfig
         */
        DescriptionLoader.prototype.showDescription = function() {
            var templateid = $(this.sourceSelector).val();
            if (this.dictionary[templateid]) {
                $(this.descriptionSelector).val(this.dictionary[templateid]);
            } else {
                $(this.descriptionSelector).val('');
            }
        };

        return {

            /**
             * Main initialisation.
             *
             * @param {String} sourseSelector The select box selector.
             * @param {String} descriptionSelector The hidden input field selector.
             * @param {Array} dictionary Translation map.
             * @return {DescriptionLoader} A new instance of DescriptionLoader.
             * @method init
             */
            init: function(sourseSelector, descriptionSelector, dictionary) {
                return new DescriptionLoader(sourseSelector, descriptionSelector, dictionary);
            }
        };
    }
);
