$(function () {
    var picker = $('#organizationPicker');
    if (!picker.length) {
        return;
    }
    var pendingSearch;
    $('#organization_search').on('input', function () {
        $('#organization_id').empty().append($('<option>').val('').text('Search and select an organization'));
        if (pendingSearch) {
            pendingSearch.abort();
        }
    });
    $('#organizationSearchButton').on('click', function () {
        var search = $('#organization_search').val().trim();
        var message = $('#organizationSearchMessage').removeClass('text-danger');
        if (search.length < 2) {
            message.addClass('text-danger').text('Enter at least 2 characters to search.');
            return;
        }
        if (pendingSearch) {
            pendingSearch.abort();
        }
        message.text('Searching...');
        pendingSearch = $.ajax({
            url: picker.data('search-url'),
            data: { search: search },
            dataType: 'json',
            success: function (response) {
                var select = $('#organization_id').empty();
                select.append($('<option>').val('').text('Select an organization'));
                response.organizations.forEach(function (organization) {
                    select.append($('<option>').val(organization.id).text(organization.name));
                });
                message.text(response.organizations.length
                    ? 'Select your organization from the results. Search a more specific name if needed.'
                    : 'No organizations found. You can request a new organization.');
            },
            error: function (xhr, status) {
                if (status !== 'abort') {
                    message.addClass('text-danger').text('Unable to search organizations. Please try again.');
                }
            }
        });
    });
    $('#organization_search').on('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            $('#organizationSearchButton').trigger('click');
        }
    });
});
