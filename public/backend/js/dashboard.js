
$(function () {
    const url = $('#listRoute').val();
    initList(url);

    function initList(url) {
        $.ajax({
            url: url,
            type: "GET",
            success: function (response) {
                console.log("Success:", response);
            },
            error: function (xhr) {
                console.error("Error:", xhr);
            }
        });
    }
});
