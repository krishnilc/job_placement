(function () {
    var filterKeys = ['college', 'programme', 'employer', 'year', 'opportunity_type', 'category'];
    var params = new URLSearchParams(window.location.search);
    var hasFilters = filterKeys.some(function (key) {
        return params.has(key) && params.get(key) !== '';
    });
    var hasCategoryCollege = params.has('category_college') && params.get('category_college') !== '';
    var hasJobTypeReportFilters = ['report_job_type', 'report_college'].some(function (key) {
        return params.has(key) && params.get(key) !== '';
    });
    var hasOrganizationReportFilter = ['report_organization', 'funnel_job'].some(function (key) {
        return params.has(key) && params.get(key) !== '';
    });
    var tabParam = params.get('tab');
    var tabButtons = Array.from(document.querySelectorAll('#dashboardMainTabs button[data-bs-toggle="tab"]'));
    var isTabTarget = function (selector) {
        return tabButtons.some(function (button) {
            return button.getAttribute('data-bs-target') === selector;
        });
    };
    var tabParamSelector = isTabTarget('#tab-' + tabParam) ? '#tab-' + tabParam : null;
    var hashSelector = isTabTarget(window.location.hash) ? window.location.hash : null;

    var targetSelector = hashSelector || tabParamSelector ||
        (hasOrganizationReportFilter ? '#tab-employers' : (hasJobTypeReportFilters ? '#tab-job-types' :
            (hasCategoryCollege ? '#tab-categories' : (hasFilters ? '#tab-placement' : null))));

    if (targetSelector) {
        var trigger = document.querySelector('#dashboardMainTabs [data-bs-target="' + targetSelector + '"]');
        if (trigger) {
            new bootstrap.Tab(trigger).show();
        }
    }

    tabButtons.forEach(function (button) {
        button.addEventListener('shown.bs.tab', function (event) {
            var target = event.target.getAttribute('data-bs-target');
            var updatedParams = new URLSearchParams(window.location.search);
            updatedParams.set('tab', target.substring('#tab-'.length));
            history.replaceState(null, '', window.location.pathname + '?' + updatedParams.toString() + target);
        });
    });
})();
