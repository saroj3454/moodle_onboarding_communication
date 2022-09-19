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
         * @param {String} name The name of the hierarchy select element.
         * @param {Number} hierarchy The number of hierarchy levels.
         */
        var HierselectHiding = function(name, hierarchy) {
            this.name = name;
            this.hierarchy = hierarchy;

            $('select[name^="' + name + '\\["]').change(this.hiding.bind(this));
            this.hiding();
        };

        /** @var {String} The name of the hierarchy select element. */
        HierselectHiding.prototype.name = null;
        /** @var {Number} The number of hierarchy levels. */
        HierselectHiding.prototype.hierarchy = null;

        /**
         * Hide select elements if they only have one item; show them otherwise.
         *
         * @method showConfig
         */
        HierselectHiding.prototype.hiding = function() {
            $('select[name^="' + this.name + '\\["]').each(function() {
                if  ($(this).children('option').length > 1) {
                    $(this).show('slow');
                } else {
                    $(this).hide('slow');
                }
            });

        };

        return {

            /**
             * Main initialisation.
             *
             * @param {String} name The name of the hierarchy select element.
             * @param {Number} hierarchy The number of hierarchy levels.
             * @return {HierselectHiding} A new instance of HierselectHiding.
             * @method init
             */
            init: function(name, hierarchy) {
                return new HierselectHiding(name, hierarchy);
            }
        };
    }
);
