import Fragment from 'core/fragment';
import Templates from 'core/templates';
import Ajax from 'core/ajax';
import $ from 'jquery';

const loadForm = (formdata, contextid) => {
    if (typeof formdata === "undefined") {
        formdata = {};
    }

    var params = { jsonformdata: JSON.stringify(formdata) };

    Fragment.loadFragment('assignsubmission_reflection', 'reflectionpanel', contextid, params).done(function (html, js) {
        $(document.querySelector('.reflection-form-container')).fadeOut("fast", function () {
            Templates.replaceNodeContents($(document.querySelector('.reflection-form-container')), html, js);
            $(document.querySelector('.reflection-form-container')).fadeIn("fast");
            $(document.querySelector('.reflection-form-container form  #id_submitbutton')).on('click', submitFormAjax.bind(this));
            document.getElementById('id_submission').value = document.querySelector('.reflection-form-container').getAttribute('data-itemid');
            document.getElementById('id_assignment').value = document.querySelector('.reflection-form-container').getAttribute('data-assignment');
        });

    });

}

const submitFormAjax = (e) => {
    // We don't want to do a real form submission.
    e.preventDefault();

    var formData = $(document.querySelector('.reflection-form-container form')).serialize();
    var contextid = document.querySelector(".reflection-form-container").getAttribute('data-contextid');

    Ajax.call([{
        methodname: 'assignsubmission_reflection_reflection_form',
        args: { contextid: contextid,
                jsonformdata: JSON.stringify(formData)
            },
        done: function(response) {
            switch (response) {
                case "FAIL":
                    if (document.querySelector('.cgs-reflection-fail-alert') == null) {
                        const alert1 = "<div class='alert alert-danger cgs-reflection-alert cgs-reflection-fail-alert' role='alert'>There was a problem when saving the reflection. Please try again later</div>"
                        $(document.querySelector('.reflection-form-container form')).after(alert1);
                    }
                    break;
                case "EMPTY":
                    if(document.querySelector('.cgs-reflection-empty-alert') == null) {
                        const alert2 = "<div class='alert alert-danger cgs-reflection-alert cgs-reflection-empty-alert' role='alert'>The reflection cannot be empty</div>"
                        $(document.querySelector('.reflection-form-container form')).after(alert2);
                    }
                break;

                default:
                    $(document.querySelector('.reflection-form-container')).fadeOut("fast", function () {
                        $(document.querySelector('.reflection-form-container')).replaceWith(response)
                        $(document.querySelector('.reflection-form-container')).fadeIn("fast");
                    })
                    break;
            }
        },
        fail: function(reason) {
            console.log(reason);

        }
    }]);

}

export const init = (undefined, contextid) => {
    loadForm('', contextid);
}
