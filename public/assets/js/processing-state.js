(function () {
    'use strict';

    const overlay = document.getElementById('processingOverlay');
    let pending = 0;
    let previousFocus = null;
    let inertElements = [];

    function begin() {
        pending += 1;
        if (pending !== 1) {
            return;
        }

        previousFocus = document.activeElement;
        inertElements = Array.from(document.body.children)
            .filter(element => element !== overlay && !element.inert);
        inertElements.forEach(element => { element.inert = true; });
        document.body.setAttribute('aria-busy', 'true');
        overlay.hidden = false;
        overlay.focus();
    }

    function finish() {
        pending = Math.max(0, pending - 1);
        if (pending !== 0) {
            return;
        }

        const restoreFocus = document.activeElement === overlay;
        overlay.hidden = true;
        document.body.removeAttribute('aria-busy');
        inertElements.forEach(element => { element.inert = false; });
        inertElements = [];
        if (restoreFocus && previousFocus && previousFocus.isConnected) {
            previousFocus.focus();
        }
        previousFocus = null;
    }

    // Capture events before inline and delegated handlers can start another action.
    ['click', 'submit', 'change', 'keydown'].forEach(type => {
        document.addEventListener(type, event => {
            const activation = type !== 'keydown' || event.key === 'Enter' || event.key === ' ';
            if (pending > 0 && activation) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        }, true);
    });

    document.addEventListener('submit', event => {
        const form = event.target;
        const submitter = event.submitter;
        const target = submitter?.getAttribute('formtarget') ?? form.target;
        const method = submitter?.getAttribute('formmethod') ?? form.method;
        if ((target && target !== '_self') || method.toLowerCase() === 'dialog') {
            return;
        }

        // A microtask can run between native event listeners, before AJAX prevents submission.
        setTimeout(() => {
            if (!event.defaultPrevented) {
                begin();
            }
        }, 0);
    }, true);

    $.ajaxPrefilter(function (options, originalOptions, xhr) {
        if (/^(GET|HEAD|OPTIONS)$/i.test(options.type)) {
            return;
        }

        const beforeSend = options.beforeSend;
        options.beforeSend = function () {
            if (beforeSend && beforeSend.apply(this, arguments) === false) {
                return false;
            }

            begin();
            // Release after existing success/error/complete handlers have updated the UI.
            xhr.always(() => { setTimeout(finish, 0); });
        };
        if (!options.error) {
            options.error = function (_xhr, status) {
                if (status !== 'abort') {
                    alert('The request could not be completed. Check the result before trying again.');
                }
            };
        }
    });

    window.addEventListener('pageshow', () => {
        pending = 0;
        finish();
    });
})();
