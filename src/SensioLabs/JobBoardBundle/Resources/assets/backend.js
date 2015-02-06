$(function () {
    "use strict";

    // JOB FORM
    // Bind the "Publish the announcement" and the "Change status" fields

    var $validateField = $('#adminjob_publish');
    var $statusField = $('#adminjob_status');

    $validateField.on('change', function () {
        var newStatus = $validateField.is(':checked') ? $validateField.attr('data-status-if-checked') : $statusField.attr('data-initial-value');
        $statusField.val(newStatus).change();
    });

    $statusField.on('change', function () {
        $validateField.prop('checked', $statusField.val() === $validateField.attr('data-status-if-checked'));
    });

});
