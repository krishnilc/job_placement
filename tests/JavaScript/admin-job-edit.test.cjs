const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const view = fs.readFileSync(
    path.join(__dirname, '..', '..', 'resources', 'views', 'admin', 'jobs', 'edit.blade.php'),
    'utf8'
);
const script = view.match(/<script type="text\/javascript">([\s\S]*?)<\/script>/)[1];

function editor() {
    let submit;
    let request;
    let disabled = false;
    const alerts = [];
    const messages = new Map();
    const location = { href: '' };
    const fields = [{ name: '_method', value: 'PUT' }, { name: '_token', value: 'test-token' }];
    const element = {
        clone() { return this; },
        val() { return ''; },
        empty() {},
        each() {},
        on() {},
        css() {},
        find() { return this; },
        prop(_name, value) { disabled = value; },
        attr() { return '/admin/jobs/update/1'; },
        serializeArray() { return fields; },
        submit(handler) { submit = handler; },
    };
    function $(selector) {
        if (typeof selector === 'string' && selector.endsWith('Error')) {
            return { text(value) { messages.set(selector, value); } };
        }
        return element;
    }
    $.ajax = options => { request = options; };
    vm.runInNewContext(script, { $, alert: message => alerts.push(message), window: { location } });
    submit.call(element, { preventDefault() {} });
    return { request, alerts, messages, location, fields, get disabled() { return disabled; } };
}

test('job editor submits POST with method override, CSRF token, and JSON accept header', () => {
    const page = editor();
    assert.equal(page.disabled, true);
    assert.equal(page.request.type, 'POST');
    assert.equal(page.request.url, '/admin/jobs/update/1');
    assert.equal(page.request.headers.Accept, 'application/json');
    assert.equal(page.request.data, page.fields);
    assert.ok(view.includes("@method('PUT')"));
    assert.ok(view.includes('@csrf'));
});

test('HTML parser errors and HTTP failures show actionable messages and restore the submit button', () => {
    for (const [status, expected] of [
        [200, 'could not confirm the update'],
        [401, 'session has expired'],
        [419, 'session has expired'],
        [403, 'update was denied'],
        [405, 'HTTP 405'],
        [500, 'HTTP 500'],
        [0, 'Check your connection'],
    ]) {
        const page = editor();
        page.request.error({ status, responseText: '<HTML>Server error</HTML>' }, 'parsererror', 'SyntaxError');
        page.request.complete();
        assert.equal(page.disabled, false);
        assert.equal(page.alerts.length, 1);
        assert.ok(page.alerts[0].includes(expected));
        assert.ok(!page.alerts[0].includes('<HTML>'));
        assert.ok(!page.alerts[0].includes('SyntaxError'));
    }
});

test('validation failures display closing date errors and leave the form usable', () => {
    const page = editor();
    page.request.success({ status: false, errors: { closing_date: ['Enter a valid closing date.'] } });
    page.request.complete();
    assert.equal(page.messages.get('#closingDateError'), 'Enter a valid closing date.');
    assert.equal(page.disabled, false);
    assert.equal(page.location.href, '');
});

test('successful job updates still redirect to the job listing', () => {
    const page = editor();
    page.request.success({ status: true });
    page.request.complete();
    assert.ok(page.location.href.includes("route('admin.jobs')"));
    assert.equal(page.disabled, false);
});
