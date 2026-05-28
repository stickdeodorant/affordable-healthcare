/*
  OHPLoadingModal
  - Small drop-in helper around the Bootstrap modal with id #loading
  - Optional CircleProgress integration (https://github.com/tigrr/circle-progress)

  Requirements for full behavior:
  - jQuery + Bootstrap modal plugin (for $('#loading').modal('show'))
  - (Optional) CircleProgress available on window as CircleProgress
*/

(function (root, factory) {
  if (typeof module === 'object' && typeof module.exports === 'object') {
    module.exports = factory();
  } else {
    root.OHPLoadingModal = factory();
  }
})(typeof window !== 'undefined' ? window : this, function () {
  'use strict';

  var DEFAULTS = {
    modalSelector: '#loading',
    progressSelector: '.submit-progress',
    textSelector: '.submit-text',
    durationMs: 3000,
    intervalMs: 100,
    statuses: [
      { max: 30, text: 'Verifying Your Information' },
      { min: 31, max: 49, text: 'Submitting Your Application' },
      { min: 50, max: 85, text: 'Finalizing Application' },
      { min: 86, max: 100, text: 'Done. Your Information has been received!' }
    ]
  };

  var state = {
    opts: null,
    intervalId: null,
    cp: null
  };

  function extend(target, src) {
    target = target || {};
    for (var k in src) {
      if (Object.prototype.hasOwnProperty.call(src, k)) target[k] = src[k];
    }
    return target;
  }

  function $(selector) {
    return (window.jQuery && window.jQuery(selector)) || null;
  }

  function getTextForPercent(percent) {
    var statuses = state.opts.statuses;
    for (var i = 0; i < statuses.length; i++) {
      var s = statuses[i];
      var hasMin = typeof s.min === 'number';
      var hasMax = typeof s.max === 'number';
      if ((hasMin ? percent >= s.min : true) && (hasMax ? percent <= s.max : true)) {
        return s.text;
      }
    }
    return statuses[0] ? statuses[0].text : '';
  }

  function setText(text) {
    var jq = $(state.opts.modalSelector + ' ' + state.opts.textSelector) || $(state.opts.textSelector);
    if (jq) jq.text(text);
  }

  function clearProgressContainer() {
    var jq = $(state.opts.modalSelector + ' ' + state.opts.progressSelector) || $(state.opts.progressSelector);
    if (jq) jq.empty();
  }

  function ensureCircleProgress() {
    if (state.cp) return state.cp;

    if (typeof window.CircleProgress !== 'function') {
      return null;
    }

    state.cp = new window.CircleProgress(state.opts.progressSelector, {
      value: 0,
      textFormat: 'percent',
      animation: 'easeInOutCubic',
      max: 100
    });

    return state.cp;
  }

  function setProgress(percent) {
    var cp = ensureCircleProgress();
    if (cp) {
      cp.value = Math.floor(percent);
    }
  }

  function show() {
    var jq = $(state.opts.modalSelector);
    if (jq && typeof jq.modal === 'function') {
      jq.modal('show');
      return;
    }

    var el = document.querySelector(state.opts.modalSelector);
    if (el) el.style.display = 'block';
  }

  function hide() {
    var jq = $(state.opts.modalSelector);
    if (jq && typeof jq.modal === 'function') {
      jq.modal('hide');
      return;
    }

    var el = document.querySelector(state.opts.modalSelector);
    if (el) el.style.display = 'none';
  }

  function stopProgress() {
    if (state.intervalId) {
      clearInterval(state.intervalId);
      state.intervalId = null;
    }
  }

  function startProgress(options) {
    options = options || {};
    stopProgress();

    // Reset UI
    clearProgressContainer();
    state.cp = null;
    ensureCircleProgress();

    var durationMs = options.durationMs || state.opts.durationMs;
    var intervalMs = options.intervalMs || state.opts.intervalMs;
    var totalIntervals = Math.max(1, Math.floor(durationMs / intervalMs));

    var currentValue = 0;
    var intervalsPassed = 0;

    state.intervalId = setInterval(function () {
      intervalsPassed++;

      if (intervalsPassed >= totalIntervals) {
        currentValue = 100;
        stopProgress();
      } else {
        var remainingIntervals = totalIntervals - intervalsPassed;
        var maxPossibleIncrement = (100 - currentValue) / Math.max(1, remainingIntervals);
        var randomIncrement = Math.random() * maxPossibleIncrement;
        currentValue += randomIncrement;
      }

      var percent = Math.max(0, Math.min(100, Math.floor(currentValue)));
      setProgress(percent);
      setText(getTextForPercent(percent));

      if (percent === 100 && typeof options.onDone === 'function') {
        options.onDone();
      }
    }, intervalMs);

    return state.intervalId;
  }

  function init(opts) {
    state.opts = extend(extend({}, DEFAULTS), opts || {});
    return api;
  }

  var api = {
    init: init,
    show: show,
    hide: hide,
    setText: setText,
    setProgress: setProgress,
    startProgress: startProgress,
    stopProgress: stopProgress
  };

  // auto-init with defaults
  init();

  return api;
});
