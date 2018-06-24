$(document).ready(function() {

$('#content form').submit(function(event) {
    event.preventDefault();
    var data = $('#content form').serializeArray();
    var resourceClasses = [];
    var properties = [];

    $.each(data, $.proxy(function(index, element) {
        if (!element) {
            return;
        }
        if (element.name.substring(0, 17) === 'resource_classes[') {
            resourceClasses.push(element);
            $('input[name="' + element.name + '"]').remove();
        } else if (element.name.substring(0, 11) === 'properties[') {
            properties.push(element);
            $('input[name="' + element.name + '"]').remove();
        }
    }, this));

    $('#resource_classes').val(JSON.stringify(resourceClasses));
    $('#properties').val(JSON.stringify(properties));
    $(this).unbind('submit').submit();
});

});
