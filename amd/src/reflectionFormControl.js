const submitForm = (e) => {
    e.preventDefault();
    console.log("SUBMIT FORM");
    document.querySelector('.reflection-form-container form').submit();
}

export const init = () => {
    console.log(document.querySelector("[data-initial-value='Save reflection']"));
    // document.querySelector("[data-initial-value='Save reflection']").addEventListener('click', submitForm);

}
