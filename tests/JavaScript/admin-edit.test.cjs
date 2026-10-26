const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const view = fs.readFileSync(
    path.join(__dirname, '..', '..', 'resources', 'views', 'admin', 'admins', 'edit.blade.php'),
    'utf8'
);
const script = view.match(/<script>([\s\S]*?)<\/script>/)[1];

function editor() {
    let submit;
    let request;
    let disabled = false;
    const alerts = [];
    const messages = new Map();
    const location = { href: '' };
    const fields = [{ name: '_method', value: 'PUT' }, { name: '_token', value: 'test-token' }];
    const element = {
        find() { return this; },
        prop(_name, value) { disabled = value; },
        attr() { return '/admin/users/update/1'; },
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

test('admin editor uses POST with method override, CSRF token, and JSON accept header', () => {
    const page = editor();
    assert.equal(page.disabled, true);
    assert.equal(page.request.type, 'POST');
    assert.equal(page.request.url, '/admin/users/update/1');
    assert.equal(page.request.headers.Accept, 'application/json');
    assert.equal(page.request.data, page.fields);
    assert.ok(view.includes("@method('PUT')"));
    assert.ok(view.includes('@csrf'));
    assert.ok(view.includes('action="{{ route(\'admin.users.update\', $user->id) }}"'));
});

test('HTML responses and HTTP failures show actionable errors and restore the button', () => {
    for (const [status, expected] of [
        [200, 'could not confirm the update'],
        [401, 'session has expired'],
        [419, 'session has expired'],
        [403, 'Only super admins'],
        [405, 'HTTP 405'],
        [422, 'HTTP 422'],
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
        assert.equal(page.location.href, '');
    }
});

test('validation errors display for every field with either supported response status', () => {
    const fields = ['name', 'email', 'designation', 'mobile', 'role', 'status'];
    const errors = Object.fromEntries(fields.map(field => [field, [`Invalid ${field}.`]]));
    for (const status of [200, 422]) {
        const page = editor();
        if (status === 200) {
            page.request.success({ status: false, errors });
        } else {
            page.request.error({ status, responseJSON: { errors } });
        }
        page.request.complete();
        for (const field of fields) {
            assert.equal(page.messages.get(`#${field}Error`), `Invalid ${field}.`);
        }
        assert.equal(page.disabled, false);
        assert.equal(page.alerts.length, 0);
        assert.equal(page.location.href, '');
    }
});

test('unexpected JSON responses report failure instead of redirecting', () => {
    for (const response of [null, {}, { status: false }]) {
        const page = editor();
        page.request.success(response);
        page.request.complete();
        assert.equal(page.alerts.length, 1);
        assert.ok(page.alerts[0].includes('unexpected response'));
        assert.equal(page.disabled, false);
        assert.equal(page.location.href, '');
    }
});

test('successful admin updates still redirect to the admins list', () => {
    const page = editor();
    page.request.success({ status: true });
    page.request.complete();
    assert.ok(page.location.href.includes("route('admin.users.admins')"));
    assert.equal(page.disabled, false);
    assert.equal(page.alerts.length, 0);
});
