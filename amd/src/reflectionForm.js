import Fragment from 'core/fragment';
import Templates from 'core/templates';
import Ajax from 'core/ajax';
import $ from 'jquery';

const loadForm = (contextid, formData) => {

    if (typeof formData === "undefined") {
        formData = {}
    }

    var params = { jsonformdata: JSON.stringify(formData)};

    // Check where the form will be displayed. If its in the submissions table, we only have to show
    // the text. If its the student view, then display the form.

    if (document.getElementById('page-mod-assign-grading')) {
        renderReflectionInSubmissionTable();

    } else {

        Fragment.loadFragment('assignsubmission_reflection', 'reflectionpanel', contextid, params).done(function (html, js) {
            $(document.querySelector('.reflection-form-container')).fadeOut("fast", function () {
                Templates.replaceNodeContents($(document.querySelector('.reflection-form-container')), html, js);

                $(document.querySelector('.reflection-form-container')).fadeIn("fast");
                $(document.querySelector('.reflection-form-container form  #id_submitbutton')).on('click', submitFormAjax.bind(this));
                document.getElementById('id_submission').value = document.querySelector('.reflection-form-container').getAttribute('data-itemid');
                document.getElementById('id_assignment').value = document.querySelector('.reflection-form-container').getAttribute('data-assignment');
            });

        }).fail(function (response) {
            console.log(response);
        })
    }


}

const submitFormAjax = (e) => {
    // We don't want to do a real form submission.
    e.preventDefault();

    var formData = $(document.querySelector('.reflection-form-container form')).serialize();
    var contextid = document.querySelector(".reflection-form-container").getAttribute('data-contextid');
    var canedit = document.querySelector(".reflection-form-container").getAttribute('data-editing-enable');

    Ajax.call([{
        methodname: 'assignsubmission_reflection_reflection_form',
        args: {
            contextid: contextid,
            jsonformdata: JSON.stringify(formData),
            canedit: canedit
        },
        done: function (response) {
            console.log(response);
            responseReflectionDisplay(response);
        },
        fail: function (reason) {
            console.log(reason);

        }
    }]);

}

const responseReflectionDisplay = (response) => {
    response = JSON.parse(response);

    switch (response.result) {
        case "FAIL":
            if (document.querySelector('.cgs-reflection-fail-alert') == null) {
                const alert1 = "<div class='alert alert-danger cgs-reflection-alert cgs-reflection-fail-alert' role='alert'>There was a problem when saving the reflection. Please try again later</div>"
                $(document.querySelector('.reflection-form-container form')).after(alert1);
            }
            fadeAlert(".cgs-reflection-fail-alert");
            break;
        case "EMPTY":
            if (document.querySelector('.cgs-reflection-empty-alert') == null) {
                const alert2 = "<div class='alert alert-danger cgs-reflection-alert cgs-reflection-empty-alert' role='alert'>The reflection cannot be empty</div>"
                $(document.querySelector('.reflection-form-container form')).after(alert2);
            }
            fadeAlert(".cgs-reflection-empty-alert");
            break;
        case "SUCCESS_NON_EDITABLE":
            $(document.querySelector('.reflection-form-container')).fadeOut("fast", function () {
                $(document.querySelector('.reflection-form-container')).replaceWith(response.reflection);
                $(document.querySelector('.reflection-form-container')).fadeIn("fast");
            });
            break;
        case "SUCCESS_EDITABLE":
            loadForm(document.querySelector(".reflection-form-container").getAttribute('data-contextid'), response.serialiseddata);
            if (document.querySelector('.cgs-reflection-editing') == null) {
                const alert3 = `<div class='alert alert-success alert-dismissible cgs-reflection-alert cgs-reflection-editing'>
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Success!</strong> Reflection saved.
                              </div>`
                $(document.querySelector('.reflection-form-container form')).after(alert3);

                // Fade it after a while.
                fadeAlert(".cgs-reflection-editing");

            }
            break;

    }
}

const fadeAlert = (alert) => {
    $(alert).fadeOut(5000);
}

const renderReflectionInSubmissionTable = () => {

    if (document.querySelector('.reflection-form-container') == null) {
        return;
    }
    // Add the truncate css class
    $('div.reflection-form-container').addClass('text-truncate');

    const container = document.querySelector('.reflection-form-container');
    const submission = container.getAttribute('data-itemid');
    const assignment = container.getAttribute('data-assignment');
    const context = container.getAttribute('data-contextid');

    Ajax.call([{
        methodname: 'assignsubmission_reflection_get_reflection',
        args: {
            submission: submission,
            assignment: assignment,
            context:context
        },
        done: function (response) {

            document.querySelector('.reflection-form-container').innerHTML = response;

            $('#more').on('click', function(e) {
                e.stopPropagation();
                if ($('div.reflection-form-container').hasClass('text-truncate')) {

                    $('div.reflection-form-container').removeClass('text-truncate');
                    $('.assignsubmission_reflection-plus').removeClass('fa-plus');
                    $('.assignsubmission_reflection-plus').addClass('fa-minus');
                    document.getElementById('more').setAttribute('title', 'View summary');
                    $('div.reflection-form-container').css({
                        'height': 'auto'
                    })
                } else {
                    document.getElementById('more').setAttribute('title', 'View full');

                    $('div.reflection-form-container').addClass('text-truncate');
                    $('.assignsubmission_reflection-plus').removeClass('fa-minus');
                    $('.assignsubmission_reflection-plus').addClass('fa-plus');

                }
            });
        },
        fail: function (reason) {
            console.log(reason);

        }
    }]);
}

export const init = (contextid, formData, nonEditable = false) => {
    if (nonEditable) {
        $('#view-reflection').on('click', function(e) {
            e.stopPropagation();
            if ($('.assignsubmission_reflection-non-editable-plus').hasClass('fa-plus')) {
                $('.assignsubmission_reflection-non-editable-plus').removeClass('fa-plus');
                $('.assignsubmission_reflection-non-editable-plus').addClass('fa-minus');
                $('.summary_assignsubmission_reflection').removeClass('view-summary');
                $('.summary_assignsubmission_reflection').addClass('view-full');
                document.getElementById('view-reflection').setAttribute('title', 'View summary');

            } else {
                document.getElementById('view-reflection').setAttribute('title', 'View full');

                $('.assignsubmission_reflection-non-editable-plus').removeClass('fa-minus');
                $('.assignsubmission_reflection-non-editable-plus').removeClass('view-full');
                $('.assignsubmission_reflection-non-editable-plus').addClass('fa-plus');
                $('.summary_assignsubmission_reflection').addClass('view-summary');


            }
        });
    } else {
        loadForm(contextid, formData);
    }
}


