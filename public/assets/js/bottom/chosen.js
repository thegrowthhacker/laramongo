// chosen.js

/**
 * For every select element containing the "data-chosen" attribute.
 * the chosen plugin will be applied
 *
 * Ex:
 *   <select data-chosen="true" id="category" name="category">
 *       <option value="1">something</option>
 *       <option value="2">something else</option>
 *       ...
 *
 * PS:
 *   Requires: jquery.chosen.js
 * 
 */

$(function(){

    $('select[data-chosen]').chosen();
})
