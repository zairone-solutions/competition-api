// $(document).ready(function () {
//     // Initialize intlTelInput
//     var input = document.querySelector("#phone_no");
//     window.intlTelInput(input, {
//         initialCountry: "PK", // Set initial country to US
//         separateDialCode: true,
//         utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
//         preferredCountries: ["US", "PK", "GB"], // Set preferred countries to US, Pakistan, and UK
//     });
// });

// $(document).ready(function () {
//     // Initialize intlTelInput
//     var input = document.querySelector("#phone_no");
//     var iti = window.intlTelInput(input, {
//         initialCountry: "PK", // Set initial country to PK
//         separateDialCode: true,
//         utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
//         preferredCountries: ["US", "PK", "GB"], // Set preferred countries to US, Pakistan, and UK
//     });

//     // Listen to country change event
//     input.addEventListener("countrychange", function() {
//         var phoneCode = iti.getSelectedCountryData().dialCode;
//         $("#phone_code").val("+" + phoneCode); // Update hidden input field with selected country code
//     });
// });



$(document).ready(function () {
    // Initialize intlTelInput
    var input = document.querySelector("#phone_no");
    var iti = window.intlTelInput(input, {
        initialCountry: "PK", // Set initial country to PK
        separateDialCode: true,
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
        preferredCountries: ["US", "PK", "GB"], // Set preferred countries to US, Pakistan, and UK
    });

    // Function to update the hidden input field with selected phone code
    function updatePhoneCode() {
        var phoneCode = iti.getSelectedCountryData().dialCode;
        $("#phone_code").val("+" + phoneCode); // Update hidden input field with selected phone code
    }

    // Listen to country change event
    input.addEventListener("countrychange", updatePhoneCode);

    // Call updatePhoneCode initially to set the default phone code
    updatePhoneCode();

});

$(document).ready(function() {
    $('.js-example-basic-single').select2();
    // $(".js-example-theme-single").select2({
    //     theme: "classic"
    //   });
});