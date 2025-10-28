"use strict";

$(document).ready(function (e) {
    $('body').on('change click load option:selected', '[data-dependency="true"]', function () {
        dependency_set(this, true);
    });
});
dependency_detect();

function dependency_detect() {
    $.each($('[data-dependency="true"]'), function () {
        dependency_set(this);
    });
}

function dependency_set($this, clicked = false) {
    if ($($this).data('dependency-field').length > 0) {
        let this_checked = $this.checked;
        let group = $($this).data('dependency-group');
        let field = $($this).data('dependency-field');
        $.each($('[data-dependend-group="' + group + '"]'), function () {

            if (!clicked) {
                let is_processed = $(this).attr('data-processed');
                if (is_processed == 1) {
                    return;
                }
            }

            let depended_arr = $(this).data('dependend').split(' ');
            let in_array = (depended_arr.indexOf(field)) >= 0;
            if (in_array) {
                if (this_checked) {
                    $(this).show('slow');
                } else {
                    $(this).hide('slow');
                }

                if (!clicked) {
                    $(this).attr('data-processed', '1');
                }

                return true;
            } else {
                $(this).hide('slow');
            }
        });
    }
}