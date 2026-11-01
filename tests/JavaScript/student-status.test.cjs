const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const view = fs.readFileSync(
    path.join(__dirname, '..', '..', 'resources', 'views', 'admin', 'students', 'index.blade.php'),
    'utf8'
);
const script = view.match(/<script type="text\/javascript">([\s\S]*?)<\/script>/)[1];

function page() {
    let change;
    let request;
    let value = 'active';
    let disabled = false;
    let color;
    let message;
    let alertClass;
    const alerts = [];
    const data = { original: 'pending', url: '/admin/users/students/7/status' };
    const select = {
        on(_event, handler) { change = handler; },
        data(key, next) {
            if (arguments.length > 1) data[key] = next;
            return data[key];
        },
        val(next) {
            if (arguments.length) value = next;
            return value;
        },
        prop(_key, next) { disabled = next; },
        removeClass() { return this; },
        addClass(next) { color = next; return this; },
        closest() { return { find: () => ({ eq: index => ({ text: () => index === 0 ? 'Test Student' : '123456789' }) }) }; },
        find() { return { text: () => 'Active' }; },
    };
    function $(selector) {
        if (selector === '#statusAlertText') return { text(next) { message = next; } };
        if (selector === '#statusAlert') {
            return { removeClass() { return this; }, addClass(next) { alertClass = next; } };
        }
        return select;
    }
    $.ajax = options => { request = options; };
    vm.runInNewContext(script, { $, alert: next => alerts.push(next) });
    change.call(select);
    return {
        request, alerts, data,
        get value() { return value; },
        get disabled() { return disabled; },
        get color() { return color; },
        get message() { return message; },
        get alertClass() { return alertClass; },
    };
}

test('student status uses the named route, POST override, CSRF, and JSON', () => {
    const student = page();
    assert.equal(student.disabled, true);
    assert.equal(student.request.url, '/admin/users/students/7/status');
    assert.equal(student.request.type, 'POST');
    assert.equal(student.request.data._method, 'PATCH');
    assert.equal(student.request.data.status, 'active');
    assert.equal(student.request.data._token, '{{ csrf_token() }}');
    assert.equal(student.request.headers.Accept, 'application/json');
    assert.equal(student.request.dataType, 'json');
    assert.ok(view.includes("route('admin.users.students.status', $user->id)"));
});

test('successful updates retain the new status and use the student name, including mail warnings', () => {
    for (const sent of [true, false, null]) {
        const student = page();
        student.request.success({ status: true, notification_sent: sent, message: 'Delivery result.' });
        student.request.complete();
        assert.equal(student.value, 'active');
        assert.equal(student.data.original, 'active');
        assert.equal(student.disabled, false);
        assert.equal(student.message, "Test Student's status is Active. Delivery result.");
        assert.equal(student.alertClass, sent === false ? 'alert-warning' : 'alert-success');
        assert.equal(student.alerts.length, 0);
    }
});

test('HTTP failures restore the select and show actionable errors without raw server output', () => {
    for (const [status, expected] of [
        [200, 'HTTP 200'], [401, 'session has expired'], [419, 'session has expired'],
        [403, 'Only admins and super admins'], [404, 'could not be found'],
        [405, 'HTTP 405'], [422, 'Invalid status.'], [500, 'HTTP 500'],
        [0, 'Check your connection'],
    ]) {
        const student = page();
        student.request.error({ status, responseJSON: { errors: { status: ['Invalid status.'] } }, responseText: '<HTML>Private error</HTML>' });
        student.request.complete();
        assert.equal(student.value, 'pending');
        assert.equal(student.data.original, 'pending');
        assert.equal(student.color, 'status-pending');
        assert.equal(student.disabled, false);
        assert.equal(student.alerts.length, 1);
        assert.ok(student.alerts[0].includes(expected));
        assert.ok(!student.alerts[0].includes('Private error'));
    }
});

test('unexpected JSON never confirms a status update', () => {
    for (const response of [null, {}, { status: false }]) {
        const student = page();
        student.request.success(response);
        student.request.complete();
        assert.equal(student.value, 'pending');
        assert.equal(student.data.original, 'pending');
        assert.equal(student.disabled, false);
        assert.ok(student.alerts[0].includes('unexpected response'));
        assert.equal(student.message, undefined);
    }
});
