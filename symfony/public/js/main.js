$(".sp-email-input").on("click", function(e) {
    var email = prompt("Please enter an email address:");

    $.post(
        "/pdf",
        { email: email }
    );
});
