import Fragment from 'core/fragment';
import Templates from 'core/templates';
import Ajax from 'core/ajax';
import $ from 'jquery';

const loadForm = (contextid, formData) => {

    if (typeof formData === "undefined") {
        formData = {}
    }

    var params = { jsonformdata: JSON.stringify(formData)};

    // Check where the form will be displayed. If its in the submissions table, we only have to show the text.
    // Same with the grader view.
    //If its the student view, then display the form.

    if (document.getElementById('page-mod-assign-grading')) {
        renderReflectionInSubmissionTable();

    } else if (document.getElementById('page-mod-assign-grader')) {
        assignsubmission_reflection_get_reflection(document.querySelector('.reflection-form-container'), true);
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

    var formData  = $(document.querySelector('.reflection-form-container form')).serialize();
    var contextid = document.querySelector(".reflection-form-container").getAttribute('data-contextid');
    var canedit   = document.querySelector(".reflection-form-container").getAttribute('data-editing-enable');
    var userid    = document.querySelector(".reflection-form-container").getAttribute('data-userid');

    Ajax.call([{
        methodname: 'assignsubmission_reflection_reflection_form',
        args: {
            contextid: contextid,
            jsonformdata: JSON.stringify(formData),
            canedit: canedit,
            userid: userid
        },
        done: function (response) {
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

    // Get the header of reflection. it has a class like header c9.
    //that c9 matches the cell where the reflection goes.The td class is cell c9
    const reflectionTh = Array.from(document.querySelector('table thead tr').children).filter(th => {
        if (th.innerHTML.includes('Reflection')) {
            return th;
        }

    });

    let cellNumber = reflectionTh[0].getAttribute('class').split(' ');
    cellNumber = cellNumber[cellNumber.length - 1];

    const submissionsTableCells = Array.from(document.querySelectorAll('table tbody tr td'));

    submissionsTableCells.forEach(td => {
        if (td.getAttribute('class').includes(cellNumber)) {

            const container = td.querySelector('.reflection-form-container');

            if (container != null) {
                assignsubmission_reflection_get_reflection(container);
            } else {
                td.innerHTML = '<span class = "reflectionsubmissionstatus">No Reflection</span>'
            }

        }
    });

}

const assignsubmission_reflection_get_reflection = (container, fromGraderView = false) => {

    if (document.querySelector('.reflection-form-container') == null) {
        return;
    }
    // Add the truncate css class
    $('div.reflection-form-container').addClass('text-truncate');

    // const container = document.querySelector('.reflection-form-container');
    const submission = container.getAttribute('data-itemid');
    const assignment = container.getAttribute('data-assignment');
    const context = container.getAttribute('data-contextid');
    const userid = container.getAttribute('data-userid');

    Ajax.call([{
        methodname: 'assignsubmission_reflection_get_reflection',
        args: {
            submission: submission,
            assignment: assignment,
            context: context,
            userid: userid
        },
        done: function (response) {

            if (fromGraderView) {
                document.querySelector('.reflection-form-container').innerHTML = response;
            } else {
                document.querySelector(`[data-userid="${userid}"]`).innerHTML = response;
            }

            controlViewFull(userid);
        },
        fail: function (reason) {
            console.log(reason);

        }
    }]);
}

const controlViewFull = (userid) => {

    $(`#more-${userid}`).on('click', function(e) {
        e.stopPropagation();

        if ($(`.reflection-form-container[data-userid = "${userid}"]`).hasClass('text-truncate')) {

            $(`.reflection-form-container[data-userid = "${userid}"]`).removeClass('text-truncate');
            $(`#more-${userid} > i`).removeClass('fa-plus');
            $(`#more-${userid} > i`).addClass('fa-minus');
            document.getElementById(`more-${userid}`).setAttribute('title', 'View summary');
            $(`.reflection-form-container[data-userid = "${userid}"]`).css({
                'height': 'auto'
            })
        } else {
            document.getElementById(`more-${userid}`).setAttribute('title', 'View full');
            $(`.reflection-form-container[data-userid = "${userid}"]`).addClass('text-truncate');
            $(`#more-${userid} > i`).removeClass('fa-minus');
            $(`#more-${userid} > i`).addClass('fa-plus');

        }
    });
}

export const init = (contextid, formData, nonEditable = false, userid) => {
    // Check that you are in the gr ader view
    if (document.getElementById('page-mod-assign-grader') != null
        && document.querySelector('span.reflectionsubmissionstatus') != null) {
        document.querySelector('span.reflectionsubmissionstatus').removeAttribute('hidden');
    }

    if (nonEditable) {
        controlViewFull(userid);
    } else {
        loadForm(contextid, formData);
    }
}


