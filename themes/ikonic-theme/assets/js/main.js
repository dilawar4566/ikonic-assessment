jQuery(document).ready(function ($) {
  // AJAX request
  $.ajax({
    url: ajax_object.ajax_url,
    type: "POST",
    dataType: "json",
    data: {
      action: "ajax_get_projects",
    },
    success: function (response) {
      if (response) {
        console.log(response);
      } else {
        console.log("Error: " + response);
        alert("fail");
      }
    },
  });
});
