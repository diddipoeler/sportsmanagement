/*
    A simple jQuery modaljsm (http://github.com/kylefox/jquery-modal)
    Version 0.6.0
*/
(function($) {

  var current = null;

  $.modaljsm = function(el, options) {
    $.modaljsm.close(); // Close any open modals.
    var remove, target;
    this.$body = $('body');
    this.options = $.extend({}, $.modaljsm.defaults, options);
    this.options.doFade = !isNaN(parseInt(this.options.fadeDuration, 10));
    if (el.is('a')) {
      target = el.attr('href');
      //Select element by id from href
      if (/^#/.test(target)) {
        this.$elm = $(target);
        if (this.$elm.length !== 1) return null;
        this.$body.append(this.$elm);
        this.open();
      //AJAX
      } else {
        this.$elm = $('<div>');
        this.$body.append(this.$elm);
        remove = function(event, modaljsm) { modaljsm.elm.remove(); };
        this.showSpinner();
        el.trigger($.modaljsm.AJAX_SEND);
        $.get(target).done(function(html) {
          if (!current) return;
          el.trigger($.modaljsm.AJAX_SUCCESS);
          current.$elm.empty().append(html).on($.modaljsm.CLOSE, remove);
          current.hideSpinner();
          current.open();
          el.trigger($.modaljsm.AJAX_COMPLETE);
        }).fail(function() {
          el.trigger($.modaljsm.AJAX_FAIL);
          current.hideSpinner();
          el.trigger($.modaljsm.AJAX_COMPLETE);
        });
      }
    } else {
      this.$elm = el;
      this.$body.append(this.$elm);
      this.open();
    }
  };

  $.modaljsm.prototype = {
    constructor: $.modaljsm,

    open: function() {
      var m = this;
      if(this.options.doFade) {
        this.block();
        setTimeout(function() {
          m.show();
        }, this.options.fadeDuration * this.options.fadeDelay);
      } else {
        this.block();
        this.show();
      }
      if (this.options.escapeClose) {
        $(document).on('keydown.modaljsm', function(event) {
          if (event.which == 27) $.modaljsm.close();
        });
      }
      if (this.options.clickClose) this.blocker.click(function(e){
        if (e.target==this)
          $.modaljsm.close();
      });
    },

    close: function() {
      this.unblock();
      this.hide();
      $(document).off('keydown.modaljsm');
    },

    block: function() {
      this.$elm.trigger($.modaljsm.BEFORE_BLOCK, [this._ctx()]);
      this.blocker = $('<div class="jquery-modaljsm blocker"></div>');
      this.$body.css('overflow','hidden');
      this.$body.append(this.blocker);
      if(this.options.doFade) {
        this.blocker.css('opacity',0).animate({opacity: 1}, this.options.fadeDuration);
      }
      this.$elm.trigger($.modaljsm.BLOCK, [this._ctx()]);
    },

    unblock: function() {
      if(this.options.doFade) {
        var self=this;
        this.blocker.fadeOut(this.options.fadeDuration, function() {
          self.blocker.children().appendTo(self.$body);
          self.blocker.remove();
          self.$body.css('overflow','');
        });
      } else {
        this.blocker.children().appendTo(this.$body);
        this.blocker.remove();
        this.$body.css('overflow','');
      }
    },

    show: function() {
      this.$elm.trigger($.modaljsm.BEFORE_OPEN, [this._ctx()]);
      if (this.options.showClose) {
        this.closeButton = $('<a href="#close-modaljsm" rel="modaljsm:close" class="close-modaljsm ' + this.options.closeClass + '">' + this.options.closeText + '</a>');
        this.$elm.append(this.closeButton);
      }
      this.$elm.addClass(this.options.modaljsmClass + ' current');
      this.$elm.appendTo(this.blocker);
      if(this.options.doFade) {
        this.$elm.css('opacity',0).animate({opacity: 1}, this.options.fadeDuration);
      } else {
        this.$elm.show();
      }
      this.$elm.trigger($.modaljsm.OPEN, [this._ctx()]);
    },

    hide: function() {
      this.$elm.trigger($.modaljsm.BEFORE_CLOSE, [this._ctx()]);
      if (this.closeButton) this.closeButton.remove();
      this.$elm.removeClass('current');

      var _this = this;
      if(this.options.doFade) {
        this.$elm.fadeOut(this.options.fadeDuration, function () {
          _this.$elm.trigger($.modaljsm.AFTER_CLOSE, [_this._ctx()]);
        });
      } else {
        this.$elm.hide(0, function () {
          _this.$elm.trigger($.modaljsm.AFTER_CLOSE, [_this._ctx()]);
        });
      }
      this.$elm.trigger($.modaljsm.CLOSE, [this._ctx()]);
    },

    showSpinner: function() {
      if (!this.options.showSpinner) return;
      this.spinner = this.spinner || $('<div class="' + this.options.modaljsmClass + '-spinner"></div>')
        .append(this.options.spinnerHtml);
      this.$body.append(this.spinner);
      this.spinner.show();
    },

    hideSpinner: function() {
      if (this.spinner) this.spinner.remove();
    },

    //Return context for custom events
    _ctx: function() {
      return { elm: this.$elm, blocker: this.blocker, options: this.options };
    }
  };

  $.modaljsm.close = function(event) {
    if (!current) return;
    if (event) event.preventDefault();
    current.close();
    var that = current.$elm;
    current = null;
    return that;
  };

  // Returns if there currently is an active modal
  $.modaljsm.isActive = function () {
    return current ? true : false;
  }

  $.modaljsm.defaults = {
    escapeClose: true,
    clickClose: true,
    closeText: 'Close',
    closeClass: '',
    modaljsmClass: "modaljsm",
    spinnerHtml: null,
    showSpinner: true,
    showClose: true,
    fadeDuration: null,   // Number of milliseconds the fade animation takes.
    fadeDelay: 1.0        // Point during the overlay's fade-in that the modal begins to fade in (.5 = 50%, 1.5 = 150%, etc.)
  };

  // Event constants
  $.modaljsm.BEFORE_BLOCK = 'modaljsm:before-block';
  $.modaljsm.BLOCK = 'modaljsm:block';
  $.modaljsm.BEFORE_OPEN = 'modaljsm:before-open';
  $.modaljsm.OPEN = 'modaljsm:open';
  $.modaljsm.BEFORE_CLOSE = 'modaljsm:before-close';
  $.modaljsm.CLOSE = 'modaljsm:close';
  $.modaljsm.AFTER_CLOSE = 'modaljsm:after-close';
  $.modaljsm.AJAX_SEND = 'modaljsm:ajax:send';
  $.modaljsm.AJAX_SUCCESS = 'modaljsm:ajax:success';
  $.modaljsm.AJAX_FAIL = 'modaljsm:ajax:fail';
  $.modaljsm.AJAX_COMPLETE = 'modaljsm:ajax:complete';

  $.fn.modaljsm = function(options){
    if (this.length === 1) {
      current = new $.modaljsm(this, options);
    }
    return this;
  };

  // Automatically bind links with rel="modal:close" to, well, close the modal.
  $(document).on('click.modaljsm', 'a[rel="modaljsm:close"]', $.modaljsm.close);
  $(document).on('click.modaljsm', 'a[rel="modaljsm:open"]', function(event) {
    event.preventDefault();
    $(this).modaljsm();
  });
})(jQuery);