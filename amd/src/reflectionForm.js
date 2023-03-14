import Fragment from 'core/fragment';
import Templates from 'core/templates';
import Ajax from 'core/ajax';
import $ from 'jquery';


const loadForm = (formdata, contextid) => {
    console.log("LOAD FORM");
    if (typeof formdata === "undefined") {
        formdata = {};
    }

    var params = { jsonformdata: JSON.stringify(formdata) };
    console.log(contextid);

    Fragment.loadFragment('assignsubmission_reflection', 'reflectionpanel', contextid, params).done(function (html, js) {
        // console.log(html);
        $(document.querySelector('.reflection-form-container')).fadeOut("fast", function () {
            Templates.replaceNodeContents($(document.querySelector('.reflection-form-container')), html, js);
            $(document.querySelector('.reflection-form-container')).fadeIn("fast");
            console.log($(document.querySelector('.reflection-form-container form')));
            $(document.querySelector('.reflection-form-container form  #id_submitbutton')).on('click', submitFormAjax.bind(this));
            document.getElementById('id_itemid').value = document.querySelector('.reflection-form-container').getAttribute('data-itemid');
            // reflection-form-container

            //
        });

    });

}


const handleFormSubmissionResponse = () => {
    // We could trigger an event instead.
    // Yuk.
    Y.use('moodle-core-formchangechecker', function () {
        M.core_formchangechecker.reset_form_dirty_state();
    });
    //document.location.reload();
    console.log("handleFormSubmissionResponse");
};

/**
   * @method handleFormSubmissionFailure
   * @private
   * @return {Promise}
   */
const handleFormSubmissionFailure = (data) => {
    // Oh noes! Epic fail :(
    // Ah wait - this is normal. We need to re-display the form with errors!
    //this.modal.setBody(this.getBody(data));
    console.log("ERROR");
};
const submitFormAjax = (e) => {
    // We don't want to do a real form submission.
    e.preventDefault();

    console.log("submitFormAjax");
    var changeEvent = document.createEvent('HTMLEvents');
    changeEvent.initEvent('change', true, true);


    var formData = $(document.querySelector('.reflection-form-container form')).serialize();
    console.log(formData);
    var contextid = document.querySelector(".reflection-form-container").getAttribute('data-contextid');
    // Now we can continue...
    Ajax.call([{
        methodname: 'assignsubmission_reflection_reflection_form',
        args: { contextid: contextid, jsonformdata: JSON.stringify(formData) },
        done: handleFormSubmissionResponse.bind(this, formData),
        fail: handleFormSubmissionFailure.bind(this, formData)
    }]);


}

export const init = (undefined, contextid) => {
    loadForm('', contextid);
}
