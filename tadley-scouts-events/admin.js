jQuery(document).ready(function () {
    let startDatetime = jQuery('#start_datetime input.hasDatepicker');
    let endDatetime = jQuery('#end_datetime input.hasDatepicker');

    var oldStartValue = startDatetime.val();

    startDatetime.change(function () {
        let endValue = endDatetime.val();
        if (endValue === oldStartValue || endValue === '') {
            endDatetime.val(startDatetime.val());
        }
        oldStartValue = startDatetime.val();
    });
});