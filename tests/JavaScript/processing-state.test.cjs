const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const root = path.join(__dirname, '..', '..');
const script = fs.readFileSync(path.join(root, 'public', 'assets', 'js', 'processing-state.js'), 'utf8');

function page() {
    const listeners = {};
    const windowListeners = {};
    const microtasks = [];
    const timers = [];
    const alerts = [];
    let prefilter;
    const attributes = {};
    const button = { isConnected: true, disabled: false, focus() { document.activeElement = this; } };
    const content = { inert: false };
    const alreadyInert = { inert: true };
    const overlay = { hidden: true, focus() { document.activeElement = this; } };
    const document = {
        activeElement: button,
        body: {
            children: [content, alreadyInert, overlay],
            setAttribute(key, value) { attributes[key] = value; },
            removeAttribute(key) { delete attributes[key]; },
        },
        getElementById() { return overlay; },
        addEventListener(type, listener) { (listeners[type] ??= []).push(listener); },
    };
    vm.runInNewContext(script, {
        document,
        window: { addEventListener(type, listener) { windowListeners[type] = listener; } },
        $: { ajaxPrefilter(callback) { prefilter = callback; } },
        queueMicrotask(callback) { microtasks.push(callback); },
        setTimeout(callback) { timers.push(callback); },
        alert(message) { alerts.push(message); },
    });
    return {
        overlay, content, alreadyInert, button, document, attributes, alerts,
        dispatch(type, overrides = {}) {
            const event = {
                target: { method: 'post', target: '' },
                key: 'Enter',
                defaultPrevented: false,
                stopped: false,
                preventDefault() { this.defaultPrevented = true; },
                stopImmediatePropagation() { this.stopped = true; },
                ...overrides,
            };
            for (const listener of listeners[type] ?? []) {
                listener(event);
                microtasks.splice(0).forEach(callback => callback());
                if (event.stopped) break;
            }
            return event;
        },
        request(options = {}) {
            const callbacks = [];
            options = { type: 'POST', ...options };
            prefilter(options, options, { always(callback) { callbacks.push(callback); } });
            const cancelled = options.beforeSend?.() === false;
            return {
                options, cancelled,
                complete() {
                    callbacks.forEach(callback => callback());
                    timers.splice(0).forEach(callback => callback());
                },
            };
        },
        flush() {
            microtasks.splice(0).forEach(callback => callback());
            timers.splice(0).forEach(callback => callback());
        },
        pageshow() { windowListeners.pageshow(); },
    };
}

test('shared layout loads processing guard before page handlers and provides an accessible status', () => {
    const layout = fs.readFileSync(path.join(root, 'resources', 'views', 'front', 'layouts', 'app.blade.php'), 'utf8');
    assert.ok(layout.indexOf("asset('assets/js/processing-state.js')") < layout.indexOf("@yield('customJS')"));
    assert.ok(layout.includes('id="processingOverlay"'));
    assert.ok(layout.includes('role="status" aria-live="polite"'));
});

test('AJAX writes show feedback, block repeat interaction and restore focus without changing form controls', () => {
    const ui = page();
    const request = ui.request();
    assert.equal(ui.overlay.hidden, false);
    assert.equal(ui.content.inert, true);
    assert.equal(ui.attributes['aria-busy'], 'true');
    assert.equal(ui.button.disabled, false);
    for (const type of ['click', 'submit', 'change', 'keydown']) {
        const event = ui.dispatch(type);
        assert.equal(event.defaultPrevented, true);
        assert.equal(event.stopped, true);
    }
    assert.equal(ui.dispatch('keydown', { key: 'Tab' }).defaultPrevented, false);
    assert.equal(ui.dispatch('keydown', { key: 'r', ctrlKey: true }).defaultPrevented, false);
    request.complete();
    assert.equal(ui.overlay.hidden, true);
    assert.equal(ui.content.inert, false);
    assert.equal(ui.alreadyInert.inert, true);
    assert.equal(ui.document.activeElement, ui.button);
    assert.equal(ui.dispatch('click').defaultPrevented, false);
});

test('concurrent writes stay locked until all requests finish', () => {
    const ui = page();
    const first = ui.request({ type: 'PATCH' });
    const second = ui.request({ type: 'DELETE' });
    first.complete();
    assert.equal(ui.overlay.hidden, false);
    second.complete();
    assert.equal(ui.overlay.hidden, true);
});

test('cancelled requests and read-only searches do not lock the page', () => {
    const ui = page();
    for (const type of ['GET', 'HEAD', 'OPTIONS']) {
        ui.request({ type });
        assert.equal(ui.overlay.hidden, true);
    }
    assert.equal(ui.request({ beforeSend() { return false; } }).cancelled, true);
    assert.equal(ui.overlay.hidden, true);
});

test('validation and network failures allow retry while preserving existing error handlers', () => {
    const ui = page();
    const error = () => {};
    const validation = ui.request({ error });
    assert.equal(validation.options.error, error);
    validation.complete();
    assert.equal(ui.overlay.hidden, true);
    const network = ui.request();
    network.options.error({}, 'error');
    assert.equal(ui.alerts.length, 1);
    network.complete();
    assert.equal(ui.overlay.hidden, true);
});

test('native forms lock after submission handlers and reset when returning with Back', () => {
    const ui = page();
    ui.dispatch('submit');
    assert.equal(ui.overlay.hidden, true);
    ui.flush();
    assert.equal(ui.overlay.hidden, false);
    ui.pageshow();
    assert.equal(ui.overlay.hidden, true);
    assert.equal(ui.content.inert, false);
});

test('cancelled AJAX submissions, invalid forms and new-tab submissions do not leave a native lock', () => {
    const ui = page();
    const event = ui.dispatch('submit');
    event.preventDefault();
    ui.flush();
    assert.equal(ui.overlay.hidden, true);
    ui.dispatch('submit', { target: { method: 'post', target: '_blank' } });
    ui.dispatch('submit', { target: { method: 'dialog', target: '' } });
    ui.flush();
    assert.equal(ui.overlay.hidden, true);
    ui.dispatch('submit', {
        submitter: { getAttribute(name) { return name === 'formtarget' ? '_blank' : null; } },
    });
    ui.flush();
    assert.equal(ui.overlay.hidden, true);
});

test('AJAX form completion releases the only lock after browser event-listener microtask checkpoints', () => {
    const ui = page();
    const event = ui.dispatch('submit');
    // The form's target handler runs after document capture listeners and their microtasks.
    event.preventDefault();
    const request = ui.request();
    ui.flush();
    assert.equal(ui.overlay.hidden, false);
    request.complete();
    assert.equal(ui.overlay.hidden, true);
    assert.equal(ui.content.inert, false);

    const retry = ui.dispatch('submit');
    assert.equal(retry.defaultPrevented, false);
    retry.preventDefault();
    const nextRequest = ui.request();
    ui.flush();
    nextRequest.complete();
    assert.equal(ui.overlay.hidden, true);
});
