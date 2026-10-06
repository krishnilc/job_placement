const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const view = fs.readFileSync(
    path.join(__dirname, '..', '..', 'resources', 'views', 'admin', 'dashboard.blade.php'),
    'utf8'
);
const script = fs.readFileSync(
    path.join(__dirname, '..', '..', 'public', 'assets', 'js', 'dashboard-tabs.js'),
    'utf8'
);

test('dashboard loads the tab script from its maintained asset', () => {
    assert.ok(view.includes("asset('assets/js/dashboard-tabs.js')"));
});

function dashboard(url) {
    const location = new URL(url, 'http://localhost');
    let selected = null;
    let scrolled = false;
    const buttons = ['overview', 'applications', 'placement', 'job-types', 'categories', 'rejection', 'employers', 'metrics']
        .map(name => ({
            target: '#tab-' + name,
            listeners: [],
            getAttribute() { return this.target; },
            addEventListener(event, listener) { this.listeners.push(listener); },
        }));
    function show(button) {
        selected = button.target;
        button.listeners.forEach(listener => listener({ target: button }));
    }
    vm.runInNewContext(script, {
        URLSearchParams,
        window: { location },
        document: {
            querySelectorAll() { return buttons; },
            querySelector(selector) {
                return buttons.find(button => selector.includes('"' + button.target + '"'));
            },
            getElementById() { return { scrollIntoView() { scrolled = true; } }; },
        },
        bootstrap: { Tab: class { constructor(button) { this.button = button; } show() { show(this.button); } } },
        history: { replaceState(_state, _title, url) { location.href = new URL(url, location).href; } },
    });
    return {
        get selected() { return selected; },
        get scrolled() { return scrolled; },
        get url() { return location.href; },
        click(name) { show(buttons.find(button => button.target === '#tab-' + name)); },
    };
}

test('Overview hash overrides a stale Job Types parameter on refresh', () => {
    assert.equal(dashboard('/admin/home?tab=job-types&report_job_type=1#tab-overview').selected, '#tab-overview');
});

test('switching tabs updates both URL states and preserves filters on refresh', () => {
    const page = dashboard('/admin/home?tab=job-types&report_job_type=1');
    page.click('overview');
    const url = new URL(page.url);
    assert.equal(url.searchParams.get('tab'), 'overview');
    assert.equal(url.searchParams.get('report_job_type'), '1');
    assert.equal(url.hash, '#tab-overview');
    assert.equal(dashboard(page.url).selected, '#tab-overview');
    page.click('applications');
    assert.equal(dashboard(page.url).selected, '#tab-applications');
});

test('filter defaults and explicit tab links still work', () => {
    for (const [url, selected] of [
        ['/admin/home?report_job_type=1', '#tab-job-types'],
        ['/admin/home?category_college=1', '#tab-categories'],
        ['/admin/home?college=1', '#tab-placement'],
        ['/admin/home?report_organization=1', '#tab-employers'],
        ['/admin/home?funnel_job=1', '#tab-employers'],
        ['/admin/home?tab=overview&report_job_type=1', '#tab-overview'],
        ['/admin/home?tab=job-types#job-type-colleges', '#tab-job-types'],
        ['/admin/home?tab=unknown&college=1#unknown', '#tab-placement'],
    ]) {
        assert.equal(dashboard(url).selected, selected);
    }
    assert.equal(dashboard('/admin/home?tab=job-types#job-type-colleges').scrolled, false);
});
