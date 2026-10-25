document.addEventListener("click", function (event) {
    const el = event.target.closest("[data-confirm]");
    if (el && !confirm(el.getAttribute("data-confirm"))) {
        event.preventDefault();
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("contact-form");
    if (!form) return;

    const message = document.getElementById("message");
    const error = document.getElementById("message-error");

    form.addEventListener("submit", function (event) {
        if (message.value.trim().length < 10) {
            event.preventDefault();
            error.textContent = "Nội dung tin nhắn phải có ít nhất 10 ký tự.";
            message.focus();
        }
    });
});
