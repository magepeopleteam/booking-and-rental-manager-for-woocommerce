/**
 * RBFW cache-safe nonce guard.
 *
 * Problem: page caches (LiteSpeed, WP Rocket, Cloudflare, host-level caches)
 * can serve HTML that is older than the WordPress nonce lifetime (12–24 h).
 * The nonces embedded in `rbfw_ajax_front` are then expired, and every rbfw_*
 * AJAX request (price calculation, availability, sold-out check, …) dies in
 * check_ajax_referer() with "403 Forbidden".
 *
 * Fix: this guard watches every jQuery AJAX request sent to admin-ajax.php
 * with an `action=rbfw_*` + `nonce=` payload. If such a request fails with a
 * nonce rejection (HTTP 403 / body "-1"), it fetches a fresh nonce set from
 * the `rbfw_refresh_frontend_nonces` endpoint (which requires no nonce, like
 * core's heartbeat "nonces-expired" flow), updates `rbfw_ajax_front` in place
 * so all subsequent calls use fresh values, and transparently retries the
 * failed request exactly once with the same callbacks.
 *
 * No other plugin script needs to change: retries reuse the original request
 * options, so existing success/error handlers keep working.
 */
(function ($) {
    'use strict';

    if (!$) {
        return;
    }

    var refreshPromise = null;

    function config() {
        return (typeof window.rbfw_ajax_front === 'object' && window.rbfw_ajax_front) ? window.rbfw_ajax_front : null;
    }

    /**
     * Fetch a fresh nonce map and merge it into rbfw_ajax_front.
     * Concurrent failures share one in-flight refresh request.
     */
    function refreshNonces() {
        var cfg = config();
        if (!cfg || !cfg.rbfw_ajaxurl) {
            return $.Deferred().reject().promise();
        }
        if (!refreshPromise) {
            refreshPromise = $.ajax({
                type: 'POST',
                url: cfg.rbfw_ajaxurl,
                data: { action: 'rbfw_refresh_frontend_nonces' },
                dataType: 'json',
                cache: false
            }).then(function (response) {
                if (response && response.success && response.data) {
                    $.each(response.data, function (key, value) {
                        cfg[key] = String(value);
                    });
                    return response.data;
                }
                return $.Deferred().reject(response).promise();
            });
            // Let long-lived tabs refresh again later, but absorb bursts.
            refreshPromise.always(function () {
                window.setTimeout(function () {
                    refreshPromise = null;
                }, 30000);
            });
        }
        return refreshPromise;
    }

    /** Read one parameter out of a serialized (application/x-www-form-urlencoded) body. */
    function getParam(data, name) {
        var match = ('&' + data).match(new RegExp('&' + name + '=([^&]*)'));
        if (!match) {
            return null;
        }
        try {
            return decodeURIComponent(match[1].replace(/\+/g, ' '));
        } catch (e) {
            return match[1];
        }
    }

    $.ajaxPrefilter(function (options, originalOptions, jqXHR) {
        var cfg = config();
        if (!cfg || originalOptions._rbfwNonceRetry) {
            return;
        }
        if (!options.url || options.url.indexOf('admin-ajax.php') === -1) {
            return;
        }

        var data = (typeof options.data === 'string') ? options.data : '';
        var action = getParam(data, 'action');
        var sentNonce = getParam(data, 'nonce');
        if (!action || action.indexOf('rbfw_') !== 0 || !sentNonce) {
            return;
        }

        // Resolve which rbfw_ajax_front key supplied this nonce BEFORE any
        // refresh mutates the map. Matching by value handles handlers that
        // share a nonce (e.g. rbfw_day_wise_sold_out_check reuses
        // nonce_bikecarmd_ajax_price_calculation); the action-name convention
        // ("rbfw_X" -> "nonce_X") is the fallback.
        var nonceKey = null;
        $.each(cfg, function (key, value) {
            if (key.indexOf('nonce') === 0 && value === sentNonce) {
                nonceKey = key;
                return false;
            }
        });
        if (!nonceKey) {
            var guess = 'nonce_' + action.replace(/^rbfw_/, '');
            if (typeof cfg[guess] !== 'undefined') {
                nonceKey = guess;
            }
        }
        if (!nonceKey) {
            return;
        }

        jqXHR.fail(function (xhr, textStatus) {
            if (textStatus === 'abort' || !xhr) {
                return;
            }
            var body = (typeof xhr.responseText === 'string') ? xhr.responseText.replace(/\s/g, '') : '';
            // check_ajax_referer() / wp_die(-1, 403): HTTP 403, body "-1" (or "0" on legacy handlers).
            var nonceRejected = xhr.status === 403 || (xhr.status === 200 && (body === '-1' || body === '0'));
            if (!nonceRejected) {
                return;
            }
            refreshNonces().done(function () {
                var fresh = cfg[nonceKey];
                if (!fresh || fresh === sentNonce) {
                    return; // nothing new to retry with
                }
                var retry = $.extend(true, {}, originalOptions);
                retry._rbfwNonceRetry = true;
                if (retry.data && typeof retry.data === 'object') {
                    retry.data.nonce = fresh;
                } else if (typeof retry.data === 'string') {
                    retry.data = retry.data.replace(/((?:^|&)nonce=)[^&]*/, '$1' + encodeURIComponent(fresh));
                }
                $.ajax(retry);
            });
        });
    });
})(window.jQuery);
