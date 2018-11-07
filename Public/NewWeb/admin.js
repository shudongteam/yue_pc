
ko.bindingHandlers.date = {
  init: function (element, valueAccessor, bindings) {
    ko.bindingHandlers.date._update(element, valueAccessor, bindings);
  },
  update: function (element, valueAccessor, bindings) {
    ko.bindingHandlers.date._update(element, valueAccessor, bindings);
  },
  _update: function (element, valueAccessor, bindings) {
    var value = ko.unwrap(valueAccessor());
    if (value) {
      var format = bindings.get('format');
      $(element).html(moment(value * 1000).format(format || 'YYYY-MM-DD'));
    } else {
      $(element).html('');
    }
  }
};

ko.bindingHandlers.datetimepicker = {
  init: function (element, valueAccessor, bindings) {
    var options = {
      format: 'YYYY-MM-DD HH:mm',
      sideBySide: true
    };

    if (bindings.has('datetimepickerOptions')) {
      options = _.assign(options, ko.unwrap(bindings.get('datetimepickerOptions')));
    }

    var date = ko.unwrap(valueAccessor());
    if (date) {
      options.defaultDate = date;
    }

    $(element).datetimepicker(options);
    $(element).on('dp.change', function (e) {
       valueAccessor()(e.date.format(options.format));
    });
  },
  update: function (element, valueAccessor) {
    var date = ko.unwrap(valueAccessor());
    if (date) {
      $(element).data('DateTimePicker').date(date);
    }
  }
};

ko.bindingHandlers.price = {
  update: function (element, valueAccess) {
    var value = ko.unwrap(valueAccess());
    if (value === null || value === undefined) {
      $(element).html('');
    } else {
      $(element).html(numeral(value / 100).format('0,0.00'));
    }
  }
};

ko.bindingHandlers.integer = {
  update: function (element, valueAccessor, bindings) {
    var value = ko.unwrap(valueAccessor());
    var format = bindings.get('format');
    $(element).html(numeral(value).format(format || '0,0'));
  }
};

ko.bindingHandlers.percentage = {
  update: function (element, valueAccessor, bindings) {
    var value = ko.unwrap(valueAccessor());
    var format = bindings.get('format');
    $(element).html(numeral(value).format(format || '0%'));
  }
};

ko.bindingHandlers.numeral = {
  update: function (element, valueAccessor, bindings) {
    var value = ko.unwrap(valueAccessor());
    var format = bindings.get('format');
    $(element).html(numeral(value).format(format || '0,0.00'));
  }
};

ko.bindingHandlers.duration = {
  update: function (element, valueAccessor) {
    var remainingSeconds = ko.unwrap(valueAccessor());

    if (remainingSeconds == 0) {
      $(element).html('0');
      return;
    }

    var days = Math.floor(remainingSeconds / (24 * 60 * 60));

    remainingSeconds = remainingSeconds % (24 * 60 * 60);

    var hours = Math.floor(remainingSeconds / 3600);

    remainingSeconds = hours % 3600;

    var minutes = Math.floor(remainingSeconds / 60);
    var seconds = remainingSeconds % 60;

    var text = '';

    if (days > 0) {
      text += days + ' 天';
    }
    if (hours > 0) {
      text += ' ' + hours + ' 小时';
    }
    if (minutes > 0) {
      text += ' ' + minutes + ' 分';
    }
    if (seconds > 0) {
      text += ' ' + seconds + ' 秒';
    }

    $(element).html(text);
  }
};

ko.bindingHandlers.select2 = {
  init: function (element, valueAccessor, allBindingsAccessor) {
    $(element).select2(ko.unwrap(valueAccessor()));

    var allBindings = allBindingsAccessor();
    if (allBindings.value) {
      $(element).val(allBindings.value());
    }
  }
};

ko.bindingHandlers.imageupload = {
  init: function (element, valueAccessor, allBindingsAccessor) {
    $(element).parent().find('.fa-spinner').hide();

    var url = $(element).data('url');

    var allBindings = allBindingsAccessor();

    if (allBindings.imageuploadOptions && allBindings.imageuploadOptions.url) {
      url = allBindings.imageuploadOptions.url;
    }

    var options = {
      url: url,
      dataType: 'json',
      add: function (e, data) {
        var file = data.files[0];
        if (!(/\.(jpg|jpeg|png)$/i).test(file.name)) {
          toastr.error('请选择图片文件');
          return false;
        }
        if (file.size > 512 * 1024) {
          toastr.error('图片必须小于512KB');
          return false;
        }

        $(e.target).parent().find('.fa-spinner').show();

        data.submit();
      },
      done: function (e, data) {
        $(e.target).parent().find('.fa-spinner').hide();
        valueAccessor()(data.result.url);
        toastr.success('上传成功');
      },
      fail: function (e, data) {
        $(e.target).parent().find('.fa-spinner').hide();
        handleAjaxError(data.jqXHR);
      }
    };

    $(element).fileupload(options);
  }
};

ko.bindingHandlers.inline_user_info = {
  update: function (element, valueAccessor, bindings) {
    $(element).data('uid', ko.unwrap(valueAccessor()));
    UIComponent.handlers['inline-user-info'].init([$(element)]);
  }
};

function scrollToElement(element, options, callback) {
  options = _.assign({}, { duration: 200, offset: 0 }, options);
  $('html,body').animate({ scrollTop: $(element).offset().top - options.offset }, options.duration, callback);
}

function parseQueryString() {
  if (!location.search || location.search.length === 1) {
    return {};
  }

  var pairs = location.search.substr(1).split('&');
  var qs = { };

  _.each(pairs, function (pair) {
    var parts = pair.split('=');
    qs[parts[0]] = decodeURIComponent(parts[1]);
  });

  return qs;
}

function buildQueryString(data) {
  var qs = _.chain(data)
    .map(function (val, key) {
      return key + '=' + encodeURIComponent(val);
    })
    .value()
    .join('&');

  return qs ? '?' + qs : null;
}

function handleAjaxError(xhr) {
  var error = JSON.parse(xhr.responseText);
  alert(error.message);
}

function reloadPage(delay) {
  delay = delay || 0;

  setTimeout(function () {
    location.href = location.pathname + location.search;
  }, delay);
}

function plainTextToHtml(text) {
  if (!text) {
    return text;
  }

  // urls to hyper links
  text = text.replace(/((http|https):\/\/[^\s]+)/g, function (url) {
    return '<a href="' + url + '" target="_blank">' + url + '</a>';
  });

  // line breaks to <br/>
  text = text.replace(/\n/g, '<br/>');

  return text;
}

// Modal
var Modal = function () {
  var self = this;
  var id = null;
  var opts = null;
  var template =
    '<div class="modal fade" id="' + id + '">' +
      '<div class="modal-dialog">' +
        '<div class="modal-content">' +
          '<div class="modal-header" data-bind="visible: title">' +
            '<button type="button" class="close" data-bind="visible: showCloseButton, click: close"><span aria-hidden="true">&times;</span></button>' +
            '<h4 class="modal-title" data-bind="html: title"></h4>' +
          '</div>' +
          '<div class="modal-body" data-bind="html: body"></div>' +
          '<div class="modal-footer">' +
            '<!-- ko foreach: buttons -->' +
            '<button type="button" data-bind="attr: { \'id\': id, \'class\': \'btn \' + className }, html: text, click: $parent.onButtonClick"></button>' +
            '<!-- /ko -->' +
          '</div>' +
        '</div>' +
      '</div>' +
    '</div>';
  var defer = null;
  var model = null;

  self.$modal = function () {
    return $('#' + id);
  };

  self.defer = function () {
    return defer;
  };

  self.open = function (options) {
    opts = options;

    if (options && options.buttons) {
      $.each(options.buttons, function () {
        if (!this.id) {
          this.id = '__btn_' + new Date().getTime() + Math.floor(Math.random() * 1000);
        }
        if (!this.className) {
          this.className = 'btn-default';
        }
      })
    }

    model = {
      title: ko.observable(options.title),
      body: ko.observable(options.body),
      showCloseButton: ko.observable(true),
      buttons: ko.observableArray(options.buttons),
      close: function () {
        self.close();
      },
      onButtonClick: function (button, e) {
        if (button.click) {
          button.click.apply(self, [e]);
        }
      }
    };

    if (options.showCloseButton !== undefined) {
      model.showCloseButton(options.showCloseButton);
    }

    id = '__modal_' + new Date().getTime();

    $(document.body).append($(template).attr('id', id));
    ko.applyBindings(model, document.getElementById(id));

    var $modal = $('#' + id);

    if (options) {
      $modal.modal(options);
    }

    $modal.on('shown.bs.modal', function () {
      _.each(options.buttons, function (button) {
          if (button.clipboard) {
            var clipboard = new Clipboard('#' + button.id, button.clipboard);
            if (button.clipboard.success) {
              clipboard.on('success', button.clipboard.success.bind(self));
            }
          }
      });

      if (opts.callbacks && opts.callbacks.shown) {
        opts.callbacks.shown.apply(self);
      }
    });

    self.resize();

    $modal.modal('show');

    defer = $.Deferred();

    return defer.promise();
  };

  self.resize = function () {
    var winHeight = $(window).height();
    $('#' + id)
      .find('.modal-body')
      .css('max-height', Math.round(winHeight * 0.7) + 'px')
      .css('overflow', 'auto');
  };

  self.close = function (options) {
    var $modal = $('#' + id);

    $modal.modal('hide');
    $modal.on('hidden.bs.modal', function () {
      $modal.remove();
    });

    if (opts.callbacks && opts.callbacks.close) {
      opts.callbacks.close.apply(self);
    }

    if (options && options.reject) {
      defer.reject();
    } else {
      defer.resolve();
    }
  };
};

Modal.instance = new Modal();

Modal.open = function (options) {
  return Modal.instance.open(options);
};

Modal.alert = function (options) {
  return Modal.instance.open({
    title: options.title,
    body: options.message,
    showCloseButton: false,
    buttons: options.buttons || [
      {
        'className': 'btn-primary',
        'text': '确定',
        'click': function () {
          this.close();
        }
      }
    ]
  })
};

Modal.confirm = function (options) {
  return Modal.instance.open({
    title: options.title,
    body: '<div class="modal-confirm-message">' + options.message + '</div>',
    showCloseButton: false,
    buttons: [
      {
        'className': 'btn-default',
        'text': '取消',
        'click': function () {
          this.close({ reject: true });
        }
      },
      {
        'className': 'btn-primary',
        'text': '确定',
        'click': function () {
          this.close();
        }
      }
    ]
  })
};

var DateRangePicker = function (element) {
  var self = this;
  var $element = $(element);

  this.init = function () {
    $element.data('DateRangePicker', self);
    $element.find('.from-date-picker').datetimepicker({ format: 'YYYY-MM-DD' });
    $element.find('.to-date-picker').datetimepicker({ format: 'YYYY-MM-DD' });
  };

  this.value = function (value) {
    if (arguments.length === 0) {
      return {
        'from': $element.find('.from-date-picker :text').val(),
        'to': $element.find('.to-date-picker :text').val()
      };
    } else {
      $element.find('.from-date-picker').data('DateTimePicker').date(value.from);
      $element.find('.to-date-picker').data('DateTimePicker').date(value.to);
    }
  };

  this.timespan = function () {
    var range = self.value();
    return new Date(range.to).getTime() + 24 * 60 * 60 * 1000 - new Date(range.from).getTime();
  }
};

DateRangePicker.create = function (element) {
  var picker = new DateRangePicker(element);
  picker.init();
  return picker;
};

// UI Component
$(function () {

  var UIComponent = window.UIComponent = {
    init: function (container, context) {
      var $container = $(container);
      var $elements = null;

      if ($container.data('ui')) {
        $elements = $container;
      } else {
        $elements = $container.find('[data-ui]');
      }

      var groups = {};

      $elements.each(function () {
        var $element = $(this);
        var componentName = $element.data('ui');

        // if in auto bind process (document ready), by pass the component if auto bind is set to false
        if (context && context.is_in_auto_bind_process && $element.data('ui-auto-bind') === false) {
          return;
        }

        if (!groups[componentName]) {
          groups[componentName] = [];
        }

        groups[componentName].push($element);
      });

      _.each(_.keys(groups), function (name) {
        var handler = UIComponent.handlers[name];
        if (handler) {
          handler.init(groups[name]);
        }
      });
    }
  };

  UIComponent.handlers = {};

  UIComponent.handlers['inline-user-info'] = {
    init: function ($elements) {
      var ids = _.map($elements, function ($element) {
        return $element.data('uid');
      });

      ids = _.filter(_.uniq(ids), function (id) { return !!id; });

      if (ids.length === 0) {
        return;
      }

      $.get('/backend/users/api_get_user_infos', {ids: ids.join(',')}, function (result) {
        _.each($elements, function ($element) {
          var uid = $element.data('uid');
          var user = result[uid];
          if (!user) {
            return;
          }

          $element.html('<span>' + user.nickname + '</span>');

          if ($element[0].tagName === 'A') {
            UIComponent.handlers['user-info-popover'].init([$element]);

            if ($element.data('link-url')) {
              var url = Handlebars.compile($element.data('link-url'))(user);
              $element.attr('href', url);
            }
          }
        });
      });
    }
  };
  
  UIComponent.handlers['user-search-box'] = {
    init: function ($elements) {
      _.each($elements, function ($element) {
        var placeholder = $element.data('placeholder');
        $element.width(300);

        var params = {
          user_type: $element.data('user-type')
        };

        if (!params.user_type) {
          throw new Error('UserSearchBox: user_type attribute is required');
        }

        if ($element.data('upline-uid')) {
          params.upline_uid = $element.data('upline-uid');
        }

        $($elements).select2({
          placeholder: placeholder,
          ajax: {
            url: '/NewWeb/Agent/api_search?' + $.param(params),
            dataType: 'json',
            quietMillis: 100,
            delay: 250,
            data: function (params) {
              return {
                q: params.term,
                page: params.page
              };
            },
            processResults: function (data) {
              return {
                results: data
              };
            }
          },
          templateResult: function formatRepoSelection (repo) {
            if (!repo.id) return '';
            return repo.nickname + ' (' + repo.username + ')';
          },
          templateSelection: function formatRepoSelection (repo) {
            if (!repo.id) return placeholder;
            return repo.nickname + ' (' + repo.username + ')';
          }
        }); 
      });
    }
  };
  
  UIComponent.handlers['user-info-popover'] = {
    init: function ($elements) {
      var buildHtmlRow = function (label, content) {
        return '<div class="form-group" style="margin-bottom:0">' +
          '<label class="control-label col-sm-4">' + label + '</label>' +
          '<div class="col-sm-8 no-padding-left"><div class="form-control-static">' + content + '</div></div>' +
          '</div>';
      };

      _.each($elements, function ($element) {
        var uid = $element.data('uid');
        if (!uid) {
          return;
        }

        $element.css('cursor', 'pointer');
        $element.webuiPopover({
          title: null,
          trigger: 'hover',
          delay: {
            show: 300
          },
          width: 350,
          type: 'async',
          url: '/backend/users/api_get_user_popover_info/' + uid,
          content: function (data) {
            var html =
              '<div class="form-horizontal">' +
              buildHtmlRow('用户名', data.username) +
              buildHtmlRow('昵称', data.nickname);

            var mp = null;

            if (data.type === 'agent') {
              html += buildHtmlRow('所属渠道', data.channel.nickname);
              mp = data.channel.mp;
            } else {
              mp = data.mp;
            }

            if (data.type === 'channel' || data.type === 'agent') {
              if (mp) {
                html += buildHtmlRow('渠道公众号',
                  '<img style="width:100px;border:#eee 1px solid;" src="//open.weixin.qq.com/qr/code/?username=' + mp.raw_id + '"/>' +
                  '<div style="margin-top:5px">' + mp.nickname + '</div>');
              } else {
                html += buildHtmlRow('渠道公众号', '未配置');
              }
            }

            html += '</div>';

            return html;
          }
        });
      });
    }
  };

  UIComponent.handlers['inline-member-info'] = {
    init: function ($elements) {
      var ids = _.map($elements, function ($element) {
        return $element.data('member-id');
      });

      ids = _.filter(_.uniq(ids), function (id) { return !!id; });

      if (ids.length === 0) {
        return;
      }

      $.get('/backend/members/api_get_member_infos', { ids: ids.join(',') }, function (result) {
        _.each($elements, function ($element) {
          var memberId = $element.data('member-id');
          var member = result[memberId];
          if (member) {
            var html = '';
            if (member.headimgurl) {
              html += '<img style="width:18px;margin-right:5px" src="' + member.headimgurl + '"/>'
            }

            var text = member.nickname
                     ? '<span>' + member.nickname + '</span>' + ' <span>(' + memberId + ')</span>'
                     : '<span>' + memberId + '</span>';

            if ($element.data('link-to-detail')) {
              html += '<a href="/backend/members/view/' + memberId + '">' + text + '</a>';
            } else {
              html += text;
            }

            $element.html(html);
          }
        });
      });
    }
  };

  UIComponent.handlers['table-sort'] = {
    init: function ($elements) {
      var qs = parseQueryString();

      _.each($elements, function ($element) {
        $element.css('cursor', 'pointer');
        $element.attr('title', '点击排序');
        $element.append('<i class="fa table-sort-icon" style="display:none"></i>');

        var $icon = $element.find('.table-sort-icon');

        var field = $element.data('field');
        var startDir = $element.data('start-dir') || 'asc';
        var orderBy = qs.order_by ? parseOrderBy(qs.order_by) : parseOrderBy('NBookPaidan.nums desc');

        // update icon status from query string
        if (orderBy && orderBy.field === field) {
          if (orderBy.dir === 'asc') {
            $icon.removeClass('fa-caret-down').addClass('fa-caret-up');
          } else {
            $icon.removeClass('fa-caret-up').addClass('fa-caret-down');
          }

          $icon.show();
        }

        $element.click(function () {
          var dir = startDir;
          if (orderBy && orderBy.field === field) {
            dir = orderBy.dir === 'asc' ? 'desc' : 'asc';
          }

          var newQs = _.assign({}, qs);

          delete newQs.page;

          newQs.order_by = field + ' ' + dir;

          var search = _.chain(newQs)
            .map(function (value, key) {
              return key + '=' + encodeURIComponent(value);
            })
            .value()
            .join('&');

          location.href = location.pathname + '?' + search;
        });
      });

      function parseOrderBy(value) {
        if (!value) {
          return null;
        }

        var parts = value.split(' ');
        if (parts.length === 1) {
          return { field: parts[0], dir: 'asc' };
        }

        return { field: parts[0], dir: parts[1] };
      }
    }
  };

  UIComponent.handlers['ref-link-search-box'] = {
    init: function ($elements) {
      _.each($elements, function ($element) {
        $element.typeahead({
          limit: 10,
          minLength: 0
        }, {
          name: 'ref_link_source',
          display: 'url',
          source: new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
              url: '/backend/referral_links/api_suggest?q=%QUERY',
              wildcard: '%QUERY'
            }
          }),
          templates: {
            suggestion: Handlebars.compile(
              '<div><span>{{description}}</span><div class="text-muted" style="font-size:13px;">{{url}}</div></div>'
            )
          }
        });

        $element.on('typeahead:change', function () {
          $(this).trigger('change');
        });
      });
    }
  };

  UIComponent.init(document.body, {
    is_in_auto_bind_process: true
  });

  // Pager
  $('.pager')
    .on('click', '.pager-jump', function () {
      jumpPage($(this).closest('.pager'));
    })
    .on('keyup', '.page-input', function (e) {
      if (e.which === 13) {
        jumpPage($(this).closest('.pager'));
      }
    });

  function jumpPage($pager) {
    var $input = $pager.find('.page-input');
    var page = $input.val();
    if (!page || !/^\d+$/.test(page) || page < 1) {
      toastr.error('无效页码');
      return false;
    }

    var qs = parseQueryString();

    qs.p = page;
    location.href = location.pathname + buildQueryString(qs);

    return false;
  }
});