(function (exports,main_core) {
   'use strict';

   function unwrapExports(x) {
      return x && x.__esModule && Object.prototype.hasOwnProperty.call(x, 'default') ? x['default'] : x;
   }
   function createCommonjsModule(fn, module) {
      return module = {
         exports: {}
      }, fn(module, module.exports), module.exports;
   }

   var slimselect_min = createCommonjsModule(function (module, exports) {
      !function (e, t) {
         "object" == (babelHelpers.typeof(exports)) && "object" == (babelHelpers.typeof(module)) ? module.exports = t() : "object" == (babelHelpers.typeof(exports)) ? exports.SlimSelect = t() : e.SlimSelect = t();
      }(window, function () {
         return s = {}, n.m = i = [function (e, t, i) {

            function s(e, t) {
               t = t || {
                  bubbles: !1,
                  cancelable: !1,
                  detail: void 0
               };
               var i = document.createEvent("CustomEvent");
               return i.initCustomEvent(e, t.bubbles, t.cancelable, t.detail), i;
            }

            var n;
            t.__esModule = !0, t.hasClassInTree = function (e, t) {
               function s(e, t) {
                  return t && e && e.classList && e.classList.contains(t) ? e : null;
               }

               return s(e, t) || function e(t, i) {
                  return t && t !== document ? s(t, i) ? t : e(t.parentNode, i) : null;
               }(e, t);
            }, t.ensureElementInView = function (e, t) {
               var i = e.scrollTop + e.offsetTop,
                  s = i + e.clientHeight,
                  n = t.offsetTop,
                  a = n + t.clientHeight;
               n < i ? e.scrollTop -= i - n : s < a && (e.scrollTop += a - s);
            }, t.putContent = function (e, t, i) {
               var s = e.offsetHeight,
                  n = e.getBoundingClientRect(),
                  a = i ? n.top : n.top - s,
                  o = i ? n.bottom : n.bottom + s;
               return a <= 0 ? "below" : o >= window.innerHeight ? "above" : i ? t : "below";
            }, t.debounce = function (n, a, o) {
               var l;
               return void 0 === a && (a = 100), void 0 === o && (o = !1), function () {
                  for (var e = [], t = 0; t < arguments.length; t++) {
                     e[t] = arguments[t];
                  }

                  var i = self,
                     s = o && !l;
                  clearTimeout(l), l = setTimeout(function () {
                     l = null, o || n.apply(i, e);
                  }, a), s && n.apply(i, e);
               };
            }, t.isValueInArrayOfObjects = function (e, t, i) {
               if (!Array.isArray(e)) return e[t] === i;

               for (var s = 0, n = e; s < n.length; s++) {
                  var a = n[s];
                  if (a && a[t] && a[t] === i) return !0;
               }

               return !1;
            }, t.highlight = function (e, t, i) {
               var s = e,
                  n = new RegExp("(" + t.trim() + ")(?![^<]*>[^<>]*</)", "i");
               if (!e.match(n)) return e;
               var a = e.match(n).index,
                  o = a + e.match(n)[0].toString().length,
                  l = e.substring(a, o);
               return s = s.replace(n, '<mark class="' + i + '">' + l + "</mark>");
            }, t.kebabCase = function (e) {
               var t = e.replace(/[A-Z\u00C0-\u00D6\u00D8-\u00DE]/g, function (e) {
                  return "-" + e.toLowerCase();
               });
               return e[0] === e[0].toUpperCase() ? t.substring(1) : t;
            }, "function" != typeof (n = window).CustomEvent && (s.prototype = n.Event.prototype, n.CustomEvent = s);
         }, function (e, t, i) {

            t.__esModule = !0;
            var s = (n.prototype.newOption = function (e) {
               return {
                  id: e.id ? e.id : String(Math.floor(1e8 * Math.random())),
                  value: e.value ? e.value : "",
                  text: e.text ? e.text : "",
                  innerHTML: e.innerHTML ? e.innerHTML : "",
                  selected: !!e.selected && e.selected,
                  display: void 0 === e.display || e.display,
                  disabled: !!e.disabled && e.disabled,
                  placeholder: !!e.placeholder && e.placeholder,
                  class: e.class ? e.class : void 0,
                  data: e.data ? e.data : {},
                  mandatory: !!e.mandatory && e.mandatory
               };
            }, n.prototype.add = function (e) {
               this.data.push({
                  id: String(Math.floor(1e8 * Math.random())),
                  value: e.value,
                  text: e.text,
                  innerHTML: "",
                  selected: !1,
                  display: !0,
                  disabled: !1,
                  placeholder: !1,
                  class: void 0,
                  mandatory: e.mandatory,
                  data: {}
               });
            }, n.prototype.parseSelectData = function () {
               this.data = [];

               for (var e = 0, t = this.main.select.element.childNodes; e < t.length; e++) {
                  var i = t[e];

                  if ("OPTGROUP" === i.nodeName) {
                     for (var s = {
                        label: i.label,
                        options: []
                     }, n = 0, a = i.childNodes; n < a.length; n++) {
                        var o = a[n];

                        if ("OPTION" === o.nodeName) {
                           var l = this.pullOptionData(o);
                           s.options.push(l), l.placeholder && "" !== l.text.trim() && (this.main.config.placeholderText = l.text);
                        }
                     }

                     this.data.push(s);
                  } else "OPTION" === i.nodeName && (l = this.pullOptionData(i), this.data.push(l), l.placeholder && "" !== l.text.trim() && (this.main.config.placeholderText = l.text));
               }
            }, n.prototype.pullOptionData = function (e) {
               return {
                  id: !!e.dataset && e.dataset.id || String(Math.floor(1e8 * Math.random())),
                  value: e.value,
                  text: e.text,
                  innerHTML: e.innerHTML,
                  selected: e.selected,
                  disabled: e.disabled,
                  placeholder: "true" === e.dataset.placeholder,
                  class: e.className,
                  style: e.style.cssText,
                  data: e.dataset,
                  mandatory: !!e.dataset && "true" === e.dataset.mandatory
               };
            }, n.prototype.setSelectedFromSelect = function () {
               if (this.main.config.isMultiple) {
                  for (var e = [], t = 0, i = this.main.select.element.options; t < i.length; t++) {
                     var s = i[t];

                     if (s.selected) {
                        var n = this.getObjectFromData(s.value, "value");
                        n && n.id && e.push(n.id);
                     }
                  }

                  this.setSelected(e, "id");
               } else {
                  var a = this.main.select.element;

                  if (-1 !== a.selectedIndex) {
                     var o = a.options[a.selectedIndex].value;
                     this.setSelected(o, "value");
                  }
               }
            }, n.prototype.setSelected = function (e, t) {
               void 0 === t && (t = "id");

               for (var i = 0, s = this.data; i < s.length; i++) {
                  var n = s[i];

                  if (n.hasOwnProperty("label")) {
                     if (n.hasOwnProperty("options")) {
                        var a = n.options;
                        if (a) for (var o = 0, l = a; o < l.length; o++) {
                           var r = l[o];
                           r.placeholder || (r.selected = this.shouldBeSelected(r, e, t));
                        }
                     }
                  } else n.selected = this.shouldBeSelected(n, e, t);
               }
            }, n.prototype.shouldBeSelected = function (e, t, i) {
               if (void 0 === i && (i = "id"), Array.isArray(t)) for (var s = 0, n = t; s < n.length; s++) {
                  var a = n[s];
                  if (i in e && String(e[i]) === String(a)) return !0;
               } else if (i in e && String(e[i]) === String(t)) return !0;
               return !1;
            }, n.prototype.getSelected = function () {
               for (var e = {
                  text: "",
                  placeholder: this.main.config.placeholderText
               }, t = [], i = 0, s = this.data; i < s.length; i++) {
                  var n = s[i];

                  if (n.hasOwnProperty("label")) {
                     if (n.hasOwnProperty("options")) {
                        var a = n.options;
                        if (a) for (var o = 0, l = a; o < l.length; o++) {
                           var r = l[o];
                           r.selected && (this.main.config.isMultiple ? t.push(r) : e = r);
                        }
                     }
                  } else n.selected && (this.main.config.isMultiple ? t.push(n) : e = n);
               }

               return this.main.config.isMultiple ? t : e;
            }, n.prototype.addToSelected = function (e, t) {
               if (void 0 === t && (t = "id"), this.main.config.isMultiple) {
                  var i = [],
                     s = this.getSelected();
                  if (Array.isArray(s)) for (var n = 0, a = s; n < a.length; n++) {
                     var o = a[n];
                     i.push(o[t]);
                  }
                  i.push(e), this.setSelected(i, t);
               }
            }, n.prototype.removeFromSelected = function (e, t) {
               if (void 0 === t && (t = "id"), this.main.config.isMultiple) {
                  for (var i = [], s = 0, n = this.getSelected(); s < n.length; s++) {
                     var a = n[s];
                     String(a[t]) !== String(e) && i.push(a[t]);
                  }

                  this.setSelected(i, t);
               }
            }, n.prototype.onDataChange = function () {
               this.main.onChange && this.isOnChangeEnabled && this.main.onChange(JSON.parse(JSON.stringify(this.getSelected())));
            }, n.prototype.getObjectFromData = function (e, t) {
               void 0 === t && (t = "id");

               for (var i = 0, s = this.data; i < s.length; i++) {
                  var n = s[i];
                  if (t in n && String(n[t]) === String(e)) return n;
                  if (n.hasOwnProperty("options") && n.options) for (var a = 0, o = n.options; a < o.length; a++) {
                     var l = o[a];
                     if (String(l[t]) === String(e)) return l;
                  }
               }

               return null;
            }, n.prototype.search = function (n) {
               if ("" !== (this.searchValue = n).trim()) {
                  var a = this.main.config.searchFilter,
                     e = this.data.slice(0);
                  n = n.trim();
                  var t = e.map(function (e) {
                     if (e.hasOwnProperty("options")) {
                        var t = e,
                           i = [];

                        if (t.options && (i = t.options.filter(function (e) {
                           return a(e, n);
                        })), 0 !== i.length) {
                           var s = Object.assign({}, t);
                           return s.options = i, s;
                        }
                     }

                     return e.hasOwnProperty("text") && a(e, n) ? e : null;
                  });
                  this.filtered = t.filter(function (e) {
                     return e;
                  });
               } else this.filtered = null;
            }, n);

            function n(e) {
               this.contentOpen = !1, this.contentPosition = "below", this.isOnChangeEnabled = !0, this.main = e.main, this.searchValue = "", this.data = [], this.filtered = null, this.parseSelectData(), this.setSelectedFromSelect();
            }

            function r(e) {
               return void 0 !== e.text || (console.error("Data object option must have at least have a text value. Check object: " + JSON.stringify(e)), !1);
            }

            t.Data = s, t.validateData = function (e) {
               if (!e) return console.error("Data must be an array of objects"), !1;

               for (var t = 0, i = 0, s = e; i < s.length; i++) {
                  var n = s[i];

                  if (n.hasOwnProperty("label")) {
                     if (n.hasOwnProperty("options")) {
                        var a = n.options;
                        if (a) for (var o = 0, l = a; o < l.length; o++) {
                           r(l[o]) || t++;
                        }
                     }
                  } else r(n) || t++;
               }

               return 0 === t;
            }, t.validateOption = r;
         }, function (e, t, i) {

            t.__esModule = !0;
            var s = i(3),
               n = i(4),
               a = i(5),
               r = i(1),
               o = i(0),
               l = (c.prototype.validate = function (e) {
                  var t = "string" == typeof e.select ? document.querySelector(e.select) : e.select;
                  if (!t) throw new Error("Could not find select element");
                  if ("SELECT" !== t.tagName) throw new Error("Element isnt of type select");
                  return t;
               }, c.prototype.selected = function () {
                  if (this.config.isMultiple) {
                     for (var e = [], t = 0, i = n = this.data.getSelected(); t < i.length; t++) {
                        var s = i[t];
                        e.push(s.value);
                     }

                     return e;
                  }

                  var n;
                  return (n = this.data.getSelected()) ? n.value : "";
               }, c.prototype.set = function (e, t, i, s) {
                  void 0 === t && (t = "value"), void 0 === i && (i = !0), void 0 === s && (s = !0), this.config.isMultiple && !Array.isArray(e) ? this.data.addToSelected(e, t) : this.data.setSelected(e, t), this.select.setValue(), this.data.onDataChange(), this.render(), i && this.close();
               }, c.prototype.setSelected = function (e, t, i, s) {
                  void 0 === t && (t = "value"), void 0 === i && (i = !0), void 0 === s && (s = !0), this.set(e, t, i, s);
               }, c.prototype.setData = function (e) {
                  if (r.validateData(e)) {
                     for (var t = JSON.parse(JSON.stringify(e)), i = this.data.getSelected(), s = 0; s < t.length; s++) {
                        t[s].value || t[s].placeholder || (t[s].value = t[s].text);
                     }

                     if (this.config.isAjax && i) if (this.config.isMultiple) for (var n = 0, a = i.reverse(); n < a.length; n++) {
                        var o = a[n];
                        t.unshift(o);
                     } else {
                        for (t.unshift(i), s = 0; s < t.length; s++) {
                           t[s].placeholder || t[s].value !== i.value || t[s].text !== i.text || delete t[s];
                        }

                        var l = !1;

                        for (s = 0; s < t.length; s++) {
                           t[s].placeholder && (l = !0);
                        }

                        l || t.unshift({
                           text: "",
                           placeholder: !0
                        });
                     }
                     this.select.create(t), this.data.parseSelectData(), this.data.setSelectedFromSelect();
                  } else console.error("Validation problem on: #" + this.select.element.id);
               }, c.prototype.addData = function (e) {
                  r.validateData([e]) ? (this.data.add(this.data.newOption(e)), this.select.create(this.data.data), this.data.parseSelectData(), this.data.setSelectedFromSelect(), this.render()) : console.error("Validation problem on: #" + this.select.element.id);
               }, c.prototype.open = function () {
                  var e = this;

                  if (this.config.isEnabled && !this.data.contentOpen) {
                     if (this.beforeOpen && this.beforeOpen(), this.config.isMultiple && this.slim.multiSelected ? this.slim.multiSelected.plus.classList.add("ss-cross") : this.slim.singleSelected && (this.slim.singleSelected.arrowIcon.arrow.classList.remove("arrow-down"), this.slim.singleSelected.arrowIcon.arrow.classList.add("arrow-up")), this.slim[this.config.isMultiple ? "multiSelected" : "singleSelected"].container.classList.add("above" === this.data.contentPosition ? this.config.openAbove : this.config.openBelow), this.config.addToBody) {
                        var t = this.slim.container.getBoundingClientRect();
                        this.slim.content.style.top = t.top + t.height + window.scrollY + "px", this.slim.content.style.left = t.left + window.scrollX + "px", this.slim.content.style.width = t.width + "px";
                     }

                     if (this.slim.content.classList.add(this.config.open), "up" === this.config.showContent.toLowerCase() || "down" !== this.config.showContent.toLowerCase() && "above" === o.putContent(this.slim.content, this.data.contentPosition, this.data.contentOpen) ? this.moveContentAbove() : this.moveContentBelow(), !this.config.isMultiple) {
                        var i = this.data.getSelected();

                        if (i) {
                           var s = i.id,
                              n = this.slim.list.querySelector('[data-id="' + s + '"]');
                           n && o.ensureElementInView(this.slim.list, n);
                        }
                     }

                     setTimeout(function () {
                        e.data.contentOpen = !0, e.config.searchFocus && e.slim.search.input.focus(), e.afterOpen && e.afterOpen();
                     }, this.config.timeoutDelay);
                  }
               }, c.prototype.close = function () {
                  var e = this;
                  this.data.contentOpen && (this.beforeClose && this.beforeClose(), this.config.isMultiple && this.slim.multiSelected ? (this.slim.multiSelected.container.classList.remove(this.config.openAbove), this.slim.multiSelected.container.classList.remove(this.config.openBelow), this.slim.multiSelected.plus.classList.remove("ss-cross")) : this.slim.singleSelected && (this.slim.singleSelected.container.classList.remove(this.config.openAbove), this.slim.singleSelected.container.classList.remove(this.config.openBelow), this.slim.singleSelected.arrowIcon.arrow.classList.add("arrow-down"), this.slim.singleSelected.arrowIcon.arrow.classList.remove("arrow-up")), this.slim.content.classList.remove(this.config.open), this.data.contentOpen = !1, this.search(""), setTimeout(function () {
                     e.slim.content.removeAttribute("style"), e.data.contentPosition = "below", e.config.isMultiple && e.slim.multiSelected ? (e.slim.multiSelected.container.classList.remove(e.config.openAbove), e.slim.multiSelected.container.classList.remove(e.config.openBelow)) : e.slim.singleSelected && (e.slim.singleSelected.container.classList.remove(e.config.openAbove), e.slim.singleSelected.container.classList.remove(e.config.openBelow)), e.slim.search.input.blur(), e.afterClose && e.afterClose();
                  }, this.config.timeoutDelay));
               }, c.prototype.moveContentAbove = function () {
                  var e = 0;
                  this.config.isMultiple && this.slim.multiSelected ? e = this.slim.multiSelected.container.offsetHeight : this.slim.singleSelected && (e = this.slim.singleSelected.container.offsetHeight);
                  var t = e + this.slim.content.offsetHeight - 1;
                  this.slim.content.style.margin = "-" + t + "px 0 0 0", this.slim.content.style.height = t - e + 1 + "px", this.slim.content.style.transformOrigin = "center bottom", this.data.contentPosition = "above", this.config.isMultiple && this.slim.multiSelected ? (this.slim.multiSelected.container.classList.remove(this.config.openBelow), this.slim.multiSelected.container.classList.add(this.config.openAbove)) : this.slim.singleSelected && (this.slim.singleSelected.container.classList.remove(this.config.openBelow), this.slim.singleSelected.container.classList.add(this.config.openAbove));
               }, c.prototype.moveContentBelow = function () {
                  this.data.contentPosition = "below", this.config.isMultiple && this.slim.multiSelected ? (this.slim.multiSelected.container.classList.remove(this.config.openAbove), this.slim.multiSelected.container.classList.add(this.config.openBelow)) : this.slim.singleSelected && (this.slim.singleSelected.container.classList.remove(this.config.openAbove), this.slim.singleSelected.container.classList.add(this.config.openBelow));
               }, c.prototype.enable = function () {
                  this.config.isEnabled = !0, this.config.isMultiple && this.slim.multiSelected ? this.slim.multiSelected.container.classList.remove(this.config.disabled) : this.slim.singleSelected && this.slim.singleSelected.container.classList.remove(this.config.disabled), this.select.triggerMutationObserver = !1, this.select.element.disabled = !1, this.slim.search.input.disabled = !1, this.select.triggerMutationObserver = !0;
               }, c.prototype.disable = function () {
                  this.config.isEnabled = !1, this.config.isMultiple && this.slim.multiSelected ? this.slim.multiSelected.container.classList.add(this.config.disabled) : this.slim.singleSelected && this.slim.singleSelected.container.classList.add(this.config.disabled), this.select.triggerMutationObserver = !1, this.select.element.disabled = !0, this.slim.search.input.disabled = !0, this.select.triggerMutationObserver = !0;
               }, c.prototype.search = function (t) {
                  if (this.data.searchValue !== t) if (this.slim.search.input.value = t, this.config.isAjax) {
                     var i = this;
                     this.config.isSearching = !0, this.render(), this.ajax && this.ajax(t, function (e) {
                        i.config.isSearching = !1, Array.isArray(e) ? (e.unshift({
                           text: "",
                           placeholder: !0
                        }), i.setData(e), i.data.search(t), i.render()) : "string" == typeof e ? i.slim.options(e) : i.render();
                     });
                  } else this.data.search(t), this.render();
               }, c.prototype.setSearchText = function (e) {
                  this.config.searchText = e;
               }, c.prototype.render = function () {
                  this.config.isMultiple ? this.slim.values() : (this.slim.placeholder(), this.slim.deselect()), this.slim.options();
               }, c.prototype.destroy = function (e) {
                  void 0 === e && (e = null);
                  var t = e ? document.querySelector("." + e + ".ss-main") : this.slim.container,
                     i = e ? document.querySelector("[data-ssid=" + e + "]") : this.select.element;

                  if (t && i && (document.removeEventListener("click", this.documentClick), "auto" === this.config.showContent && window.removeEventListener("scroll", this.windowScroll, !1), i.style.display = "", delete i.dataset.ssid, i.slim = null, t.parentElement && t.parentElement.removeChild(t), this.config.addToBody)) {
                     var s = e ? document.querySelector("." + e + ".ss-content") : this.slim.content;
                     if (!s) return;
                     document.body.removeChild(s);
                  }
               }, c);

            function c(e) {
               var t = this;
               this.ajax = null, this.addable = null, this.beforeOnChange = null, this.onChange = null, this.beforeOpen = null, this.afterOpen = null, this.beforeClose = null, this.afterClose = null, this.windowScroll = o.debounce(function (e) {
                  t.data.contentOpen && ("above" === o.putContent(t.slim.content, t.data.contentPosition, t.data.contentOpen) ? t.moveContentAbove() : t.moveContentBelow());
               }), this.documentClick = function (e) {
                  e.target && !o.hasClassInTree(e.target, t.config.id) && t.close();
               };
               var i = this.validate(e);
               i.dataset.ssid && this.destroy(i.dataset.ssid), e.ajax && (this.ajax = e.ajax), e.addable && (this.addable = e.addable), this.config = new s.Config({
                  select: i,
                  isAjax: !!e.ajax,
                  showSearch: e.showSearch,
                  searchPlaceholder: e.searchPlaceholder,
                  searchText: e.searchText,
                  searchingText: e.searchingText,
                  searchFocus: e.searchFocus,
                  searchHighlight: e.searchHighlight,
                  searchFilter: e.searchFilter,
                  closeOnSelect: e.closeOnSelect,
                  showContent: e.showContent,
                  placeholderText: e.placeholder,
                  allowDeselect: e.allowDeselect,
                  allowDeselectOption: e.allowDeselectOption,
                  hideSelectedOption: e.hideSelectedOption,
                  deselectLabel: e.deselectLabel,
                  isEnabled: e.isEnabled,
                  valuesUseText: e.valuesUseText,
                  showOptionTooltips: e.showOptionTooltips,
                  selectByGroup: e.selectByGroup,
                  limit: e.limit,
                  timeoutDelay: e.timeoutDelay,
                  addToBody: e.addToBody
               }), this.select = new n.Select({
                  select: i,
                  main: this
               }), this.data = new r.Data({
                  main: this
               }), this.slim = new a.Slim({
                  main: this
               }), this.select.element.parentNode && this.select.element.parentNode.insertBefore(this.slim.container, this.select.element.nextSibling), e.data ? this.setData(e.data) : this.render(), document.addEventListener("click", this.documentClick), "auto" === this.config.showContent && window.addEventListener("scroll", this.windowScroll, !1), e.beforeOnChange && (this.beforeOnChange = e.beforeOnChange), e.onChange && (this.onChange = e.onChange), e.beforeOpen && (this.beforeOpen = e.beforeOpen), e.afterOpen && (this.afterOpen = e.afterOpen), e.beforeClose && (this.beforeClose = e.beforeClose), e.afterClose && (this.afterClose = e.afterClose), this.config.isEnabled || this.disable();
            }

            t.default = l;
         }, function (e, t, i) {

            t.__esModule = !0;
            var s = (n.prototype.searchFilter = function (e, t) {
               return -1 !== e.text.toLowerCase().indexOf(t.toLowerCase());
            }, n);

            function n(e) {
               this.id = "", this.isMultiple = !1, this.isAjax = !1, this.isSearching = !1, this.showSearch = !0, this.searchFocus = !0, this.searchHighlight = !1, this.closeOnSelect = !0, this.showContent = "auto", this.searchPlaceholder = "Search", this.searchText = "No Results", this.searchingText = "Searching...", this.placeholderText = "Select Value", this.allowDeselect = !1, this.allowDeselectOption = !1, this.hideSelectedOption = !1, this.deselectLabel = "x", this.isEnabled = !0, this.valuesUseText = !1, this.showOptionTooltips = !1, this.selectByGroup = !1, this.limit = 0, this.timeoutDelay = 200, this.addToBody = !1, this.main = "ss-main", this.singleSelected = "ss-single-selected", this.arrow = "ss-arrow", this.multiSelected = "ss-multi-selected", this.add = "ss-add", this.plus = "ss-plus", this.values = "ss-values", this.value = "ss-value", this.valueText = "ss-value-text", this.valueDelete = "ss-value-delete", this.content = "ss-content", this.open = "ss-open", this.openAbove = "ss-open-above", this.openBelow = "ss-open-below", this.search = "ss-search", this.searchHighlighter = "ss-search-highlight", this.addable = "ss-addable", this.list = "ss-list", this.optgroup = "ss-optgroup", this.optgroupLabel = "ss-optgroup-label", this.optgroupLabelSelectable = "ss-optgroup-label-selectable", this.option = "ss-option", this.optionSelected = "ss-option-selected", this.highlighted = "ss-highlighted", this.disabled = "ss-disabled", this.hide = "ss-hide", this.id = "ss-" + Math.floor(1e5 * Math.random()), this.style = e.select.style.cssText, this.class = e.select.className.split(" "), this.isMultiple = e.select.multiple, this.isAjax = e.isAjax, this.showSearch = !1 !== e.showSearch, this.searchFocus = !1 !== e.searchFocus, this.searchHighlight = !0 === e.searchHighlight, this.closeOnSelect = !1 !== e.closeOnSelect, e.showContent && (this.showContent = e.showContent), this.isEnabled = !1 !== e.isEnabled, e.searchPlaceholder && (this.searchPlaceholder = e.searchPlaceholder), e.searchText && (this.searchText = e.searchText), e.searchingText && (this.searchingText = e.searchingText), e.placeholderText && (this.placeholderText = e.placeholderText), this.allowDeselect = !0 === e.allowDeselect, this.allowDeselectOption = !0 === e.allowDeselectOption, this.hideSelectedOption = !0 === e.hideSelectedOption, e.deselectLabel && (this.deselectLabel = e.deselectLabel), e.valuesUseText && (this.valuesUseText = e.valuesUseText), e.showOptionTooltips && (this.showOptionTooltips = e.showOptionTooltips), e.selectByGroup && (this.selectByGroup = e.selectByGroup), e.limit && (this.limit = e.limit), e.searchFilter && (this.searchFilter = e.searchFilter), null != e.timeoutDelay && (this.timeoutDelay = e.timeoutDelay), this.addToBody = !0 === e.addToBody;
            }

            t.Config = s;
         }, function (e, t, i) {

            t.__esModule = !0;
            var s = i(0),
               n = (a.prototype.setValue = function () {
                  if (this.main.data.getSelected()) {
                     if (this.main.config.isMultiple) for (var e = this.main.data.getSelected(), t = 0, i = this.element.options; t < i.length; t++) {
                        var s = i[t];
                        s.selected = !1;

                        for (var n = 0, a = e; n < a.length; n++) {
                           a[n].value === s.value && (s.selected = !0);
                        }
                     } else e = this.main.data.getSelected(), this.element.value = e ? e.value : "";
                     this.main.data.isOnChangeEnabled = !1, this.element.dispatchEvent(new CustomEvent("change", {
                        bubbles: !0
                     })), this.main.data.isOnChangeEnabled = !0;
                  }
               }, a.prototype.addAttributes = function () {
                  this.element.tabIndex = -1, this.element.style.display = "none", this.element.dataset.ssid = this.main.config.id;
               }, a.prototype.addEventListeners = function () {
                  var t = this;
                  this.element.addEventListener("change", function (e) {
                     t.main.data.setSelectedFromSelect(), t.main.render();
                  });
               }, a.prototype.addMutationObserver = function () {
                  var t = this;
                  this.main.config.isAjax || (this.mutationObserver = new MutationObserver(function (e) {
                     t.triggerMutationObserver && (t.main.data.parseSelectData(), t.main.data.setSelectedFromSelect(), t.main.render(), e.forEach(function (e) {
                        "class" === e.attributeName && t.main.slim.updateContainerDivClass(t.main.slim.container);
                     }));
                  }), this.observeMutationObserver());
               }, a.prototype.observeMutationObserver = function () {
                  this.mutationObserver && this.mutationObserver.observe(this.element, {
                     attributes: !0,
                     childList: !0,
                     characterData: !0
                  });
               }, a.prototype.disconnectMutationObserver = function () {
                  this.mutationObserver && this.mutationObserver.disconnect();
               }, a.prototype.create = function (e) {
                  this.element.innerHTML = "";

                  for (var t = 0, i = e; t < i.length; t++) {
                     var s = i[t];

                     if (s.hasOwnProperty("options")) {
                        var n = s,
                           a = document.createElement("optgroup");
                        if (a.label = n.label, n.options) for (var o = 0, l = n.options; o < l.length; o++) {
                           var r = l[o];
                           a.appendChild(this.createOption(r));
                        }
                        this.element.appendChild(a);
                     } else this.element.appendChild(this.createOption(s));
                  }
               }, a.prototype.createOption = function (t) {
                  var i = document.createElement("option");
                  return i.value = "" !== t.value ? t.value : t.text, i.innerHTML = t.innerHTML || t.text, t.selected && (i.selected = t.selected), !1 === t.display && (i.style.display = "none"), t.disabled && (i.disabled = !0), t.placeholder && i.setAttribute("data-placeholder", "true"), t.mandatory && i.setAttribute("data-mandatory", "true"), t.class && t.class.split(" ").forEach(function (e) {
                     i.classList.add(e);
                  }), t.data && "object" == babelHelpers.typeof(t.data) && Object.keys(t.data).forEach(function (e) {
                     i.setAttribute("data-" + s.kebabCase(e), t.data[e]);
                  }), i;
               }, a);

            function a(e) {
               this.triggerMutationObserver = !0, this.element = e.select, this.main = e.main, this.element.disabled && (this.main.config.isEnabled = !1), this.addAttributes(), this.addEventListeners(), this.mutationObserver = null, this.addMutationObserver(), this.element.slim = e.main;
            }

            t.Select = n;
         }, function (e, t, i) {

            t.__esModule = !0;
            var a = i(0),
               o = i(1),
               s = (n.prototype.containerDiv = function () {
                  var e = document.createElement("div");
                  return e.style.cssText = this.main.config.style, this.updateContainerDivClass(e), e;
               }, n.prototype.updateContainerDivClass = function (e) {
                  this.main.config.class = this.main.select.element.className.split(" "), e.className = "", e.classList.add(this.main.config.id), e.classList.add(this.main.config.main);

                  for (var t = 0, i = this.main.config.class; t < i.length; t++) {
                     var s = i[t];
                     "" !== s.trim() && e.classList.add(s);
                  }
               }, n.prototype.singleSelectedDiv = function () {
                  var t = this,
                     e = document.createElement("div");
                  e.classList.add(this.main.config.singleSelected);
                  var i = document.createElement("span");
                  i.classList.add("placeholder"), e.appendChild(i);
                  var s = document.createElement("span");
                  s.innerHTML = this.main.config.deselectLabel, s.classList.add("ss-deselect"), s.onclick = function (e) {
                     e.stopPropagation(), t.main.config.isEnabled && t.main.set("");
                  }, e.appendChild(s);
                  var n = document.createElement("span");
                  n.classList.add(this.main.config.arrow);
                  var a = document.createElement("span");
                  return a.classList.add("arrow-down"), n.appendChild(a), e.appendChild(n), e.onclick = function () {
                     t.main.config.isEnabled && (t.main.data.contentOpen ? t.main.close() : t.main.open());
                  }, {
                     container: e,
                     placeholder: i,
                     deselect: s,
                     arrowIcon: {
                        container: n,
                        arrow: a
                     }
                  };
               }, n.prototype.placeholder = function () {
                  var e = this.main.data.getSelected();

                  if (null === e || e && e.placeholder) {
                     var t = document.createElement("span");
                     t.classList.add(this.main.config.disabled), t.innerHTML = this.main.config.placeholderText, this.singleSelected && (this.singleSelected.placeholder.innerHTML = t.outerHTML);
                  } else {
                     var i = "";
                     e && (i = e.innerHTML && !0 !== this.main.config.valuesUseText ? e.innerHTML : e.text), this.singleSelected && (this.singleSelected.placeholder.innerHTML = e ? i : "");
                  }
               }, n.prototype.deselect = function () {
                  if (this.singleSelected) {
                     if (!this.main.config.allowDeselect) return void this.singleSelected.deselect.classList.add("ss-hide");
                     "" === this.main.selected() ? this.singleSelected.deselect.classList.add("ss-hide") : this.singleSelected.deselect.classList.remove("ss-hide");
                  }
               }, n.prototype.multiSelectedDiv = function () {
                  var t = this,
                     e = document.createElement("div");
                  e.classList.add(this.main.config.multiSelected);
                  var i = document.createElement("div");
                  i.classList.add(this.main.config.values), e.appendChild(i);
                  var s = document.createElement("div");
                  s.classList.add(this.main.config.add);
                  var n = document.createElement("span");
                  return n.classList.add(this.main.config.plus), n.onclick = function (e) {
                     t.main.data.contentOpen && (t.main.close(), e.stopPropagation());
                  }, s.appendChild(n), e.appendChild(s), e.onclick = function (e) {
                     t.main.config.isEnabled && (e.target.classList.contains(t.main.config.valueDelete) || (t.main.data.contentOpen ? t.main.close() : t.main.open()));
                  }, {
                     container: e,
                     values: i,
                     add: s,
                     plus: n
                  };
               }, n.prototype.values = function () {
                  if (this.multiSelected) {
                     for (var e, t = this.multiSelected.values.childNodes, i = this.main.data.getSelected(), s = [], n = 0, a = t; n < a.length; n++) {
                        var o = a[n];
                        e = !0;

                        for (var l = 0, r = i; l < r.length; l++) {
                           var c = r[l];
                           String(c.id) === String(o.dataset.id) && (e = !1);
                        }

                        e && s.push(o);
                     }

                     for (var d = 0, h = s; d < h.length; d++) {
                        var u = h[d];
                        u.classList.add("ss-out"), this.multiSelected.values.removeChild(u);
                     }

                     for (t = this.multiSelected.values.childNodes, c = 0; c < i.length; c++) {
                        e = !1;

                        for (var p = 0, m = t; p < m.length; p++) {
                           o = m[p], String(i[c].id) === String(o.dataset.id) && (e = !0);
                        }

                        e || (0 !== t.length && HTMLElement.prototype.insertAdjacentElement ? 0 === c ? this.multiSelected.values.insertBefore(this.valueDiv(i[c]), t[c]) : t[c - 1].insertAdjacentElement("afterend", this.valueDiv(i[c])) : this.multiSelected.values.appendChild(this.valueDiv(i[c])));
                     }

                     if (0 === i.length) {
                        var f = document.createElement("span");
                        f.classList.add(this.main.config.disabled), f.innerHTML = this.main.config.placeholderText, this.multiSelected.values.innerHTML = f.outerHTML;
                     }
                  }
               }, n.prototype.valueDiv = function (a) {
                  var o = this,
                     e = document.createElement("div");
                  e.classList.add(this.main.config.value), e.dataset.id = a.id;
                  var t = document.createElement("span");

                  if (t.classList.add(this.main.config.valueText), t.innerHTML = a.innerHTML && !0 !== this.main.config.valuesUseText ? a.innerHTML : a.text, e.appendChild(t), !a.mandatory) {
                     var i = document.createElement("span");
                     i.classList.add(this.main.config.valueDelete), i.innerHTML = this.main.config.deselectLabel, i.onclick = function (e) {
                        e.preventDefault(), e.stopPropagation();
                        var t = !1;

                        if (o.main.beforeOnChange || (t = !0), o.main.beforeOnChange) {
                           for (var i = o.main.data.getSelected(), s = JSON.parse(JSON.stringify(i)), n = 0; n < s.length; n++) {
                              s[n].id === a.id && s.splice(n, 1);
                           }

                           !1 !== o.main.beforeOnChange(s) && (t = !0);
                        }

                        t && (o.main.data.removeFromSelected(a.id, "id"), o.main.render(), o.main.select.setValue(), o.main.data.onDataChange());
                     }, e.appendChild(i);
                  }

                  return e;
               }, n.prototype.contentDiv = function () {
                  var e = document.createElement("div");
                  return e.classList.add(this.main.config.content), e;
               }, n.prototype.searchDiv = function () {
                  var n = this,
                     e = document.createElement("div"),
                     s = document.createElement("input"),
                     a = document.createElement("div");
                  e.classList.add(this.main.config.search);
                  var t = {
                     container: e,
                     input: s
                  };
                  return this.main.config.showSearch || (e.classList.add(this.main.config.hide), s.readOnly = !0), s.type = "search", s.placeholder = this.main.config.searchPlaceholder, s.tabIndex = 0, s.setAttribute("aria-label", this.main.config.searchPlaceholder), s.setAttribute("autocapitalize", "off"), s.setAttribute("autocomplete", "off"), s.setAttribute("autocorrect", "off"), s.onclick = function (e) {
                     setTimeout(function () {
                        "" === e.target.value && n.main.search("");
                     }, 10);
                  }, s.onkeydown = function (e) {
                     "ArrowUp" === e.key ? (n.main.open(), n.highlightUp(), e.preventDefault()) : "ArrowDown" === e.key ? (n.main.open(), n.highlightDown(), e.preventDefault()) : "Tab" === e.key ? n.main.data.contentOpen ? n.main.close() : setTimeout(function () {
                        n.main.close();
                     }, n.main.config.timeoutDelay) : "Enter" === e.key && e.preventDefault();
                  }, s.onkeyup = function (e) {
                     var t = e.target;

                     if ("Enter" === e.key) {
                        if (n.main.addable && e.ctrlKey) return a.click(), e.preventDefault(), void e.stopPropagation();
                        var i = n.list.querySelector("." + n.main.config.highlighted);
                        i && i.click();
                     } else "ArrowUp" === e.key || "ArrowDown" === e.key || ("Escape" === e.key ? n.main.close() : n.main.config.showSearch && n.main.data.contentOpen ? n.main.search(t.value) : s.value = "");

                     e.preventDefault(), e.stopPropagation();
                  }, s.onfocus = function () {
                     n.main.open();
                  }, e.appendChild(s), this.main.addable && (a.classList.add(this.main.config.addable), a.innerHTML = "+", a.onclick = function (e) {
                     if (n.main.addable) {
                        e.preventDefault(), e.stopPropagation();
                        var t = n.search.input.value;
                        if ("" === t.trim()) return void n.search.input.focus();
                        var i = n.main.addable(t),
                           s = "";
                        if (!i) return;
                        "object" == babelHelpers.typeof(i) ? o.validateOption(i) && (n.main.addData(i), s = i.value ? i.value : i.text) : (n.main.addData(n.main.data.newOption({
                           text: i,
                           value: i
                        })), s = i), n.main.search(""), setTimeout(function () {
                           n.main.set(s, "value", !1, !1);
                        }, 100), n.main.config.closeOnSelect && setTimeout(function () {
                           n.main.close();
                        }, 100);
                     }
                  }, e.appendChild(a), t.addable = a), t;
               }, n.prototype.highlightUp = function () {
                  var e = this.list.querySelector("." + this.main.config.highlighted),
                     t = null;
                  if (e) for (t = e.previousSibling; null !== t && t.classList.contains(this.main.config.disabled);) {
                     t = t.previousSibling;
                  } else {
                     var i = this.list.querySelectorAll("." + this.main.config.option + ":not(." + this.main.config.disabled + ")");
                     t = i[i.length - 1];
                  }

                  if (t && t.classList.contains(this.main.config.optgroupLabel) && (t = null), null === t) {
                     var s = e.parentNode;

                     if (s.classList.contains(this.main.config.optgroup) && s.previousSibling) {
                        var n = s.previousSibling.querySelectorAll("." + this.main.config.option + ":not(." + this.main.config.disabled + ")");
                        n.length && (t = n[n.length - 1]);
                     }
                  }

                  t && (e && e.classList.remove(this.main.config.highlighted), t.classList.add(this.main.config.highlighted), a.ensureElementInView(this.list, t));
               }, n.prototype.highlightDown = function () {
                  var e = this.list.querySelector("." + this.main.config.highlighted),
                     t = null;
                  if (e) for (t = e.nextSibling; null !== t && t.classList.contains(this.main.config.disabled);) {
                     t = t.nextSibling;
                  } else t = this.list.querySelector("." + this.main.config.option + ":not(." + this.main.config.disabled + ")");

                  if (null === t && null !== e) {
                     var i = e.parentNode;
                     i.classList.contains(this.main.config.optgroup) && i.nextSibling && (t = i.nextSibling.querySelector("." + this.main.config.option + ":not(." + this.main.config.disabled + ")"));
                  }

                  t && (e && e.classList.remove(this.main.config.highlighted), t.classList.add(this.main.config.highlighted), a.ensureElementInView(this.list, t));
               }, n.prototype.listDiv = function () {
                  var e = document.createElement("div");
                  return e.classList.add(this.main.config.list), e;
               }, n.prototype.options = function (e) {
                  void 0 === e && (e = "");
                  var t,
                     i = this.main.data.filtered || this.main.data.data;
                  if ((this.list.innerHTML = "") !== e) return (t = document.createElement("div")).classList.add(this.main.config.option), t.classList.add(this.main.config.disabled), t.innerHTML = e, void this.list.appendChild(t);
                  if (this.main.config.isAjax && this.main.config.isSearching) return (t = document.createElement("div")).classList.add(this.main.config.option), t.classList.add(this.main.config.disabled), t.innerHTML = this.main.config.searchingText, void this.list.appendChild(t);

                  if (0 === i.length) {
                     var s = document.createElement("div");
                     return s.classList.add(this.main.config.option), s.classList.add(this.main.config.disabled), s.innerHTML = this.main.config.searchText, void this.list.appendChild(s);
                  }

                  for (var n = function n(e) {
                     if (e.hasOwnProperty("label")) {
                        var t = e,
                           n = document.createElement("div");
                        n.classList.add(c.main.config.optgroup);
                        var i = document.createElement("div");
                        i.classList.add(c.main.config.optgroupLabel), c.main.config.selectByGroup && c.main.config.isMultiple && i.classList.add(c.main.config.optgroupLabelSelectable), i.innerHTML = t.label, n.appendChild(i);
                        var s = t.options;

                        if (s) {
                           for (var a = 0, o = s; a < o.length; a++) {
                              var l = o[a];
                              n.appendChild(c.option(l));
                           }

                           if (c.main.config.selectByGroup && c.main.config.isMultiple) {
                              var r = c;
                              i.addEventListener("click", function (e) {
                                 e.preventDefault(), e.stopPropagation();

                                 for (var t = 0, i = n.children; t < i.length; t++) {
                                    var s = i[t];
                                    -1 !== s.className.indexOf(r.main.config.option) && s.click();
                                 }
                              });
                           }
                        }

                        c.list.appendChild(n);
                     } else c.list.appendChild(c.option(e));
                  }, c = this, a = 0, o = i; a < o.length; a++) {
                     n(o[a]);
                  }
               }, n.prototype.option = function (r) {
                  if (r.placeholder) {
                     var e = document.createElement("div");
                     return e.classList.add(this.main.config.option), e.classList.add(this.main.config.hide), e;
                  }

                  var t = document.createElement("div");
                  t.classList.add(this.main.config.option), r.class && r.class.split(" ").forEach(function (e) {
                     t.classList.add(e);
                  }), r.style && (t.style.cssText = r.style);
                  var c = this.main.data.getSelected();
                  t.dataset.id = r.id, this.main.config.searchHighlight && this.main.slim && r.innerHTML && "" !== this.main.slim.search.input.value.trim() ? t.innerHTML = a.highlight(r.innerHTML, this.main.slim.search.input.value, this.main.config.searchHighlighter) : r.innerHTML && (t.innerHTML = r.innerHTML), this.main.config.showOptionTooltips && t.textContent && t.setAttribute("title", t.textContent);
                  var d = this;
                  t.addEventListener("click", function (e) {
                     e.preventDefault(), e.stopPropagation();
                     var t = this.dataset.id;

                     if (!0 === r.selected && d.main.config.allowDeselectOption) {
                        var i = !1;

                        if (d.main.beforeOnChange && d.main.config.isMultiple || (i = !0), d.main.beforeOnChange && d.main.config.isMultiple) {
                           for (var s = d.main.data.getSelected(), n = JSON.parse(JSON.stringify(s)), a = 0; a < n.length; a++) {
                              n[a].id === t && n.splice(a, 1);
                           }

                           !1 !== d.main.beforeOnChange(n) && (i = !0);
                        }

                        i && (d.main.config.isMultiple ? (d.main.data.removeFromSelected(t, "id"), d.main.render(), d.main.select.setValue(), d.main.data.onDataChange()) : d.main.set(""));
                     } else {
                        if (r.disabled || r.selected) return;
                        if (d.main.config.limit && Array.isArray(c) && d.main.config.limit <= c.length) return;

                        if (d.main.beforeOnChange) {
                           var o = void 0,
                              l = JSON.parse(JSON.stringify(d.main.data.getObjectFromData(t)));
                           l.selected = !0, d.main.config.isMultiple ? (o = JSON.parse(JSON.stringify(c))).push(l) : o = JSON.parse(JSON.stringify(l)), !1 !== d.main.beforeOnChange(o) && d.main.set(t, "id", d.main.config.closeOnSelect);
                        } else d.main.set(t, "id", d.main.config.closeOnSelect);
                     }
                  });
                  var i = c && a.isValueInArrayOfObjects(c, "id", r.id);
                  return (r.disabled || i) && (t.onclick = null, d.main.config.allowDeselectOption || t.classList.add(this.main.config.disabled), d.main.config.hideSelectedOption && t.classList.add(this.main.config.hide)), i ? t.classList.add(this.main.config.optionSelected) : t.classList.remove(this.main.config.optionSelected), t;
               }, n);

            function n(e) {
               this.main = e.main, this.container = this.containerDiv(), this.content = this.contentDiv(), this.search = this.searchDiv(), this.list = this.listDiv(), this.options(), this.singleSelected = null, this.multiSelected = null, this.main.config.isMultiple ? (this.multiSelected = this.multiSelectedDiv(), this.multiSelected && this.container.appendChild(this.multiSelected.container)) : (this.singleSelected = this.singleSelectedDiv(), this.container.appendChild(this.singleSelected.container)), this.main.config.addToBody ? (this.content.classList.add(this.main.config.id), document.body.appendChild(this.content)) : this.container.appendChild(this.content), this.content.appendChild(this.search.container), this.content.appendChild(this.list);
            }

            t.Slim = s;
         }], n.c = s, n.d = function (e, t, i) {
            n.o(e, t) || Object.defineProperty(e, t, {
               enumerable: !0,
               get: i
            });
         }, n.r = function (e) {
            "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(e, Symbol.toStringTag, {
               value: "Module"
            }), Object.defineProperty(e, "__esModule", {
               value: !0
            });
         }, n.t = function (t, e) {
            if (1 & e && (t = n(t)), 8 & e) return t;
            if (4 & e && "object" == babelHelpers.typeof(t) && t && t.__esModule) return t;
            var i = Object.create(null);
            if (n.r(i), Object.defineProperty(i, "default", {
               enumerable: !0,
               value: t
            }), 2 & e && "string" != typeof t) for (var s in t) {
               n.d(i, s, function (e) {
                  return t[e];
               }.bind(null, s));
            }
            return i;
         }, n.n = function (e) {
            var t = e && e.__esModule ? function () {
               return e.default;
            } : function () {
               return e;
            };
            return n.d(t, "a", t), t;
         }, n.o = function (e, t) {
            return Object.prototype.hasOwnProperty.call(e, t);
         }, n.p = "", n(n.s = 2).default;

         function n(e) {
            if (s[e]) return s[e].exports;
            var t = s[e] = {
               i: e,
               l: !1,
               exports: {}
            };
            return i[e].call(t.exports, t, t.exports, n), t.l = !0, t.exports;
         }

         var i, s;
      });
   });

   var SlimSelect = unwrapExports(slimselect_min);
   var slimselect_min_1 = slimselect_min.SlimSelect;

   function _typeof(obj) {
      "@babel/helpers - typeof";

      if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") {
         _typeof = function _typeof(obj) {
            return typeof obj;
         };
      } else {
         _typeof = function _typeof(obj) {
            return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
         };
      }

      return _typeof(obj);
   }

   function _classCallCheck(instance, Constructor) {
      if (!(instance instanceof Constructor)) {
         throw new TypeError("Cannot call a class as a function");
      }
   }

   function _defineProperties(target, props) {
      for (var i = 0; i < props.length; i++) {
         var descriptor = props[i];
         descriptor.enumerable = descriptor.enumerable || false;
         descriptor.configurable = true;
         if ("value" in descriptor) descriptor.writable = true;
         Object.defineProperty(target, descriptor.key, descriptor);
      }
   }

   function _createClass(Constructor, protoProps, staticProps) {
      if (protoProps) _defineProperties(Constructor.prototype, protoProps);
      if (staticProps) _defineProperties(Constructor, staticProps);
      return Constructor;
   }

   function _defineProperty(obj, key, value) {
      if (key in obj) {
         Object.defineProperty(obj, key, {
            value: value,
            enumerable: true,
            configurable: true,
            writable: true
         });
      } else {
         obj[key] = value;
      }

      return obj;
   }

   function _inherits(subClass, superClass) {
      if (typeof superClass !== "function" && superClass !== null) {
         throw new TypeError("Super expression must either be null or a function");
      }

      subClass.prototype = Object.create(superClass && superClass.prototype, {
         constructor: {
            value: subClass,
            writable: true,
            configurable: true
         }
      });
      if (superClass) _setPrototypeOf(subClass, superClass);
   }

   function _getPrototypeOf(o) {
      _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) {
         return o.__proto__ || Object.getPrototypeOf(o);
      };
      return _getPrototypeOf(o);
   }

   function _setPrototypeOf(o, p) {
      _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) {
         o.__proto__ = p;
         return o;
      };

      return _setPrototypeOf(o, p);
   }

   function _isNativeReflectConstruct() {
      if (typeof Reflect === "undefined" || !Reflect.construct) return false;
      if (Reflect.construct.sham) return false;
      if (typeof Proxy === "function") return true;

      try {
         Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {}));
         return true;
      } catch (e) {
         return false;
      }
   }

   function _objectWithoutPropertiesLoose(source, excluded) {
      if (source == null) return {};
      var target = {};
      var sourceKeys = Object.keys(source);
      var key, i;

      for (i = 0; i < sourceKeys.length; i++) {
         key = sourceKeys[i];
         if (excluded.indexOf(key) >= 0) continue;
         target[key] = source[key];
      }

      return target;
   }

   function _objectWithoutProperties(source, excluded) {
      if (source == null) return {};

      var target = _objectWithoutPropertiesLoose(source, excluded);

      var key, i;

      if (Object.getOwnPropertySymbols) {
         var sourceSymbolKeys = Object.getOwnPropertySymbols(source);

         for (i = 0; i < sourceSymbolKeys.length; i++) {
            key = sourceSymbolKeys[i];
            if (excluded.indexOf(key) >= 0) continue;
            if (!Object.prototype.propertyIsEnumerable.call(source, key)) continue;
            target[key] = source[key];
         }
      }

      return target;
   }

   function _assertThisInitialized(self) {
      if (self === void 0) {
         throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
      }

      return self;
   }

   function _possibleConstructorReturn(self, call) {
      if (call && (babelHelpers.typeof(call) === "object" || typeof call === "function")) {
         return call;
      } else if (call !== void 0) {
         throw new TypeError("Derived constructors may only return object or undefined");
      }

      return _assertThisInitialized(self);
   }

   function _createSuper(Derived) {
      var hasNativeReflectConstruct = _isNativeReflectConstruct();

      return function _createSuperInternal() {
         var Super = _getPrototypeOf(Derived),
            result;

         if (hasNativeReflectConstruct) {
            var NewTarget = _getPrototypeOf(this).constructor;

            result = Reflect.construct(Super, arguments, NewTarget);
         } else {
            result = Super.apply(this, arguments);
         }

         return _possibleConstructorReturn(this, result);
      };
   }

   function _superPropBase(object, property) {
      while (!Object.prototype.hasOwnProperty.call(object, property)) {
         object = _getPrototypeOf(object);
         if (object === null) break;
      }

      return object;
   }

   function _get(target, property, receiver) {
      if (typeof Reflect !== "undefined" && Reflect.get) {
         _get = Reflect.get;
      } else {
         _get = function _get(target, property, receiver) {
            var base = _superPropBase(target, property);

            if (!base) return;
            var desc = Object.getOwnPropertyDescriptor(base, property);

            if (desc.get) {
               return desc.get.call(receiver);
            }

            return desc.value;
         };
      }

      return _get(target, property, receiver || target);
   }

   function set(target, property, value, receiver) {
      if (typeof Reflect !== "undefined" && Reflect.set) {
         set = Reflect.set;
      } else {
         set = function set(target, property, value, receiver) {
            var base = _superPropBase(target, property);

            var desc;

            if (base) {
               desc = Object.getOwnPropertyDescriptor(base, property);

               if (desc.set) {
                  desc.set.call(receiver, value);
                  return true;
               } else if (!desc.writable) {
                  return false;
               }
            }

            desc = Object.getOwnPropertyDescriptor(receiver, property);

            if (desc) {
               if (!desc.writable) {
                  return false;
               }

               desc.value = value;
               Object.defineProperty(receiver, property, desc);
            } else {
               _defineProperty(receiver, property, value);
            }

            return true;
         };
      }

      return set(target, property, value, receiver);
   }

   function _set(target, property, value, receiver, isStrict) {
      var s = set(target, property, value, receiver || target);

      if (!s && isStrict) {
         throw new Error('failed to set property');
      }

      return value;
   }

   function _slicedToArray(arr, i) {
      return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest();
   }

   function _arrayWithHoles(arr) {
      if (Array.isArray(arr)) return arr;
   }

   function _iterableToArrayLimit(arr, i) {
      var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"];

      if (_i == null) return;
      var _arr = [];
      var _n = true;
      var _d = false;

      var _s, _e;

      try {
         for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) {
            _arr.push(_s.value);

            if (i && _arr.length === i) break;
         }
      } catch (err) {
         _d = true;
         _e = err;
      } finally {
         try {
            if (!_n && _i["return"] != null) _i["return"]();
         } finally {
            if (_d) throw _e;
         }
      }

      return _arr;
   }

   function _unsupportedIterableToArray(o, minLen) {
      if (!o) return;
      if (typeof o === "string") return _arrayLikeToArray(o, minLen);
      var n = Object.prototype.toString.call(o).slice(8, -1);
      if (n === "Object" && o.constructor) n = o.constructor.name;
      if (n === "Map" || n === "Set") return Array.from(o);
      if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen);
   }

   function _arrayLikeToArray(arr, len) {
      if (len == null || len > arr.length) len = arr.length;

      for (var i = 0, arr2 = new Array(len); i < len; i++) {
         arr2[i] = arr[i];
      }

      return arr2;
   }

   function _nonIterableRest() {
      throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
   }

   /** Checks if value is string */

   function isString(str) {
      return typeof str === 'string' || str instanceof String;
   }
   /**
    Direction
    @prop {string} NONE
    @prop {string} LEFT
    @prop {string} FORCE_LEFT
    @prop {string} RIGHT
    @prop {string} FORCE_RIGHT
    */


   var DIRECTION = {
      NONE: 'NONE',
      LEFT: 'LEFT',
      FORCE_LEFT: 'FORCE_LEFT',
      RIGHT: 'RIGHT',
      FORCE_RIGHT: 'FORCE_RIGHT'
   };
   /** */


   function forceDirection(direction) {
      switch (direction) {
         case DIRECTION.LEFT:
            return DIRECTION.FORCE_LEFT;

         case DIRECTION.RIGHT:
            return DIRECTION.FORCE_RIGHT;

         default:
            return direction;
      }
   }
   /** Escapes regular expression control chars */


   function escapeRegExp(str) {
      return str.replace(/([.*+?^=!:${}()|[\]\/\\])/g, '\\$1');
   } // cloned from https://github.com/epoberezkin/fast-deep-equal with small changes


   function objectIncludes(b, a) {
      if (a === b) return true;
      var arrA = Array.isArray(a),
         arrB = Array.isArray(b),
         i;

      if (arrA && arrB) {
         if (a.length != b.length) return false;

         for (i = 0; i < a.length; i++) {
            if (!objectIncludes(a[i], b[i])) return false;
         }

         return true;
      }

      if (arrA != arrB) return false;

      if (a && b && _typeof(a) === 'object' && _typeof(b) === 'object') {
         var dateA = a instanceof Date,
            dateB = b instanceof Date;
         if (dateA && dateB) return a.getTime() == b.getTime();
         if (dateA != dateB) return false;
         var regexpA = a instanceof RegExp,
            regexpB = b instanceof RegExp;
         if (regexpA && regexpB) return a.toString() == b.toString();
         if (regexpA != regexpB) return false;
         var keys = Object.keys(a); // if (keys.length !== Object.keys(b).length) return false;

         for (i = 0; i < keys.length; i++) {
            if (!Object.prototype.hasOwnProperty.call(b, keys[i])) return false;
         }

         for (i = 0; i < keys.length; i++) {
            if (!objectIncludes(b[keys[i]], a[keys[i]])) return false;
         }

         return true;
      } else if (a && b && typeof a === 'function' && typeof b === 'function') {
         return a.toString() === b.toString();
      }

      return false;
   }

   /** Provides details of changing input */

   var ActionDetails = /*#__PURE__*/function () {
      /** Current input value */

      /** Current cursor position */

      /** Old input value */

      /** Old selection */
      function ActionDetails(value, cursorPos, oldValue, oldSelection) {
         _classCallCheck(this, ActionDetails);

         this.value = value;
         this.cursorPos = cursorPos;
         this.oldValue = oldValue;
         this.oldSelection = oldSelection; // double check if left part was changed (autofilling, other non-standard input triggers)

         while (this.value.slice(0, this.startChangePos) !== this.oldValue.slice(0, this.startChangePos)) {
            --this.oldSelection.start;
         }
      }
      /**
       Start changing position
       @readonly
       */


      _createClass(ActionDetails, [{
         key: "startChangePos",
         get: function get() {
            return Math.min(this.cursorPos, this.oldSelection.start);
         }
         /**
          Inserted symbols count
          @readonly
          */

      }, {
         key: "insertedCount",
         get: function get() {
            return this.cursorPos - this.startChangePos;
         }
         /**
          Inserted symbols
          @readonly
          */

      }, {
         key: "inserted",
         get: function get() {
            return this.value.substr(this.startChangePos, this.insertedCount);
         }
         /**
          Removed symbols count
          @readonly
          */

      }, {
         key: "removedCount",
         get: function get() {
            // Math.max for opposite operation
            return Math.max(this.oldSelection.end - this.startChangePos || // for Delete
               this.oldValue.length - this.value.length, 0);
         }
         /**
          Removed symbols
          @readonly
          */

      }, {
         key: "removed",
         get: function get() {
            return this.oldValue.substr(this.startChangePos, this.removedCount);
         }
         /**
          Unchanged head symbols
          @readonly
          */

      }, {
         key: "head",
         get: function get() {
            return this.value.substring(0, this.startChangePos);
         }
         /**
          Unchanged tail symbols
          @readonly
          */

      }, {
         key: "tail",
         get: function get() {
            return this.value.substring(this.startChangePos + this.insertedCount);
         }
         /**
          Remove direction
          @readonly
          */

      }, {
         key: "removeDirection",
         get: function get() {
            if (!this.removedCount || this.insertedCount) return DIRECTION.NONE; // align right if delete at right or if range removed (event with backspace)

            return this.oldSelection.end === this.cursorPos || this.oldSelection.start === this.cursorPos ? DIRECTION.RIGHT : DIRECTION.LEFT;
         }
      }]);

      return ActionDetails;
   }();

   /**
    Provides details of changing model value
    @param {Object} [details]
    @param {string} [details.inserted] - Inserted symbols
    @param {boolean} [details.skip] - Can skip chars
    @param {number} [details.removeCount] - Removed symbols count
    @param {number} [details.tailShift] - Additional offset if any changes occurred before tail
    */

   var ChangeDetails = /*#__PURE__*/function () {
      /** Inserted symbols */

      /** Can skip chars */

      /** Additional offset if any changes occurred before tail */

      /** Raw inserted is used by dynamic mask */
      function ChangeDetails(details) {
         _classCallCheck(this, ChangeDetails);

         Object.assign(this, {
            inserted: '',
            rawInserted: '',
            skip: false,
            tailShift: 0
         }, details);
      }
      /**
       Aggregate changes
       @returns {ChangeDetails} `this`
       */


      _createClass(ChangeDetails, [{
         key: "aggregate",
         value: function aggregate(details) {
            this.rawInserted += details.rawInserted;
            this.skip = this.skip || details.skip;
            this.inserted += details.inserted;
            this.tailShift += details.tailShift;
            return this;
         }
         /** Total offset considering all changes */

      }, {
         key: "offset",
         get: function get() {
            return this.tailShift + this.inserted.length;
         }
      }]);

      return ChangeDetails;
   }();

   /** Provides details of continuous extracted tail */

   var ContinuousTailDetails = /*#__PURE__*/function () {
      /** Tail value as string */

      /** Tail start position */

      /** Start position */
      function ContinuousTailDetails() {
         var value = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
         var from = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
         var stop = arguments.length > 2 ? arguments[2] : undefined;

         _classCallCheck(this, ContinuousTailDetails);

         this.value = value;
         this.from = from;
         this.stop = stop;
      }

      _createClass(ContinuousTailDetails, [{
         key: "toString",
         value: function toString() {
            return this.value;
         }
      }, {
         key: "extend",
         value: function extend(tail) {
            this.value += String(tail);
         }
      }, {
         key: "appendTo",
         value: function appendTo(masked) {
            return masked.append(this.toString(), {
               tail: true
            }).aggregate(masked._appendPlaceholder());
         }
      }, {
         key: "state",
         get: function get() {
            return {
               value: this.value,
               from: this.from,
               stop: this.stop
            };
         },
         set: function set(state) {
            Object.assign(this, state);
         }
      }, {
         key: "shiftBefore",
         value: function shiftBefore(pos) {
            if (this.from >= pos || !this.value.length) return '';
            var shiftChar = this.value[0];
            this.value = this.value.slice(1);
            return shiftChar;
         }
      }]);

      return ContinuousTailDetails;
   }();

   /**
    * Applies mask on element.
    * @constructor
    * @param {HTMLInputElement|HTMLTextAreaElement|MaskElement} el - Element to apply mask
    * @param {Object} opts - Custom mask options
    * @return {InputMask}
    */
   function IMask(el) {
      var opts = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {}; // currently available only for input-like elements

      return new IMask.InputMask(el, opts);
   }

   /** Supported mask type */

   /** Provides common masking stuff */

   var Masked = /*#__PURE__*/function () {
      // $Shape<MaskedOptions>; TODO after fix https://github.com/facebook/flow/issues/4773

      /** @type {Mask} */

      /** */
      // $FlowFixMe no ideas

      /** Transforms value before mask processing */

      /** Validates if value is acceptable */

      /** Does additional processing in the end of editing */

      /** Format typed value to string */

      /** Parse strgin to get typed value */

      /** Enable characters overwriting */

      /** */
      function Masked(opts) {
         _classCallCheck(this, Masked);

         this._value = '';

         this._update(Object.assign({}, Masked.DEFAULTS, opts));

         this.isInitialized = true;
      }
      /** Sets and applies new options */


      _createClass(Masked, [{
         key: "updateOptions",
         value: function updateOptions(opts) {
            if (!Object.keys(opts).length) return;
            this.withValueRefresh(this._update.bind(this, opts));
         }
         /**
          Sets new options
          @protected
          */

      }, {
         key: "_update",
         value: function _update(opts) {
            Object.assign(this, opts);
         }
         /** Mask state */

      }, {
         key: "state",
         get: function get() {
            return {
               _value: this.value
            };
         },
         set: function set(state) {
            this._value = state._value;
         }
         /** Resets value */

      }, {
         key: "reset",
         value: function reset() {
            this._value = '';
         }
         /** */

      }, {
         key: "value",
         get: function get() {
            return this._value;
         },
         set: function set(value) {
            this.resolve(value);
         }
         /** Resolve new value */

      }, {
         key: "resolve",
         value: function resolve(value) {
            this.reset();
            this.append(value, {
               input: true
            }, '');
            this.doCommit();
            return this.value;
         }
         /** */

      }, {
         key: "unmaskedValue",
         get: function get() {
            return this.value;
         },
         set: function set(value) {
            this.reset();
            this.append(value, {}, '');
            this.doCommit();
         }
         /** */

      }, {
         key: "typedValue",
         get: function get() {
            return this.doParse(this.value);
         },
         set: function set(value) {
            this.value = this.doFormat(value);
         }
         /** Value that includes raw user input */

      }, {
         key: "rawInputValue",
         get: function get() {
            return this.extractInput(0, this.value.length, {
               raw: true
            });
         },
         set: function set(value) {
            this.reset();
            this.append(value, {
               raw: true
            }, '');
            this.doCommit();
         }
         /** */

      }, {
         key: "isComplete",
         get: function get() {
            return true;
         }
         /** Finds nearest input position in direction */

      }, {
         key: "nearestInputPos",
         value: function nearestInputPos(cursorPos, direction) {
            return cursorPos;
         }
         /** Extracts value in range considering flags */

      }, {
         key: "extractInput",
         value: function extractInput() {
            var fromPos = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
            var toPos = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : this.value.length;
            return this.value.slice(fromPos, toPos);
         }
         /** Extracts tail in range */

      }, {
         key: "extractTail",
         value: function extractTail() {
            var fromPos = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
            var toPos = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : this.value.length;
            return new ContinuousTailDetails(this.extractInput(fromPos, toPos), fromPos);
         }
         /** Appends tail */
         // $FlowFixMe no ideas

      }, {
         key: "appendTail",
         value: function appendTail(tail) {
            if (isString(tail)) tail = new ContinuousTailDetails(String(tail));
            return tail.appendTo(this);
         }
         /** Appends char */

      }, {
         key: "_appendCharRaw",
         value: function _appendCharRaw(ch) {
            if (!ch) return new ChangeDetails();
            this._value += ch;
            return new ChangeDetails({
               inserted: ch,
               rawInserted: ch
            });
         }
         /** Appends char */

      }, {
         key: "_appendChar",
         value: function _appendChar(ch) {
            var flags = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
            var checkTail = arguments.length > 2 ? arguments[2] : undefined;
            var consistentState = this.state;

            var details = this._appendCharRaw(this.doPrepare(ch, flags), flags);

            if (details.inserted) {
               var consistentTail;
               var appended = this.doValidate(flags) !== false;

               if (appended && checkTail != null) {
                  // validation ok, check tail
                  var beforeTailState = this.state;

                  if (this.overwrite) {
                     consistentTail = checkTail.state;
                     checkTail.shiftBefore(this.value.length);
                  }

                  var tailDetails = this.appendTail(checkTail);
                  appended = tailDetails.rawInserted === checkTail.toString(); // if ok, rollback state after tail

                  if (appended && tailDetails.inserted) this.state = beforeTailState;
               } // revert all if something went wrong


               if (!appended) {
                  details = new ChangeDetails();
                  this.state = consistentState;
                  if (checkTail && consistentTail) checkTail.state = consistentTail;
               }
            }

            return details;
         }
         /** Appends optional placeholder at end */

      }, {
         key: "_appendPlaceholder",
         value: function _appendPlaceholder() {
            return new ChangeDetails();
         }
         /** Appends symbols considering flags */
         // $FlowFixMe no ideas

      }, {
         key: "append",
         value: function append(str, flags, tail) {
            if (!isString(str)) throw new Error('value should be string');
            var details = new ChangeDetails();
            var checkTail = isString(tail) ? new ContinuousTailDetails(String(tail)) : tail;
            if (flags && flags.tail) flags._beforeTailState = this.state;

            for (var ci = 0; ci < str.length; ++ci) {
               details.aggregate(this._appendChar(str[ci], flags, checkTail));
            } // append tail but aggregate only tailShift


            if (checkTail != null) {
               details.tailShift += this.appendTail(checkTail).tailShift; // TODO it's a good idea to clear state after appending ends
               // but it causes bugs when one append calls another (when dynamic dispatch set rawInputValue)
               // this._resetBeforeTailState();
            }

            return details;
         }
         /** */

      }, {
         key: "remove",
         value: function remove() {
            var fromPos = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
            var toPos = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : this.value.length;
            this._value = this.value.slice(0, fromPos) + this.value.slice(toPos);
            return new ChangeDetails();
         }
         /** Calls function and reapplies current value */

      }, {
         key: "withValueRefresh",
         value: function withValueRefresh(fn) {
            if (this._refreshing || !this.isInitialized) return fn();
            this._refreshing = true;
            var rawInput = this.rawInputValue;
            var value = this.value;
            var ret = fn();
            this.rawInputValue = rawInput; // append lost trailing chars at end

            if (this.value && this.value !== value && value.indexOf(this.value) === 0) {
               this.append(value.slice(this.value.length), {}, '');
            }

            delete this._refreshing;
            return ret;
         }
         /** */

      }, {
         key: "runIsolated",
         value: function runIsolated(fn) {
            if (this._isolated || !this.isInitialized) return fn(this);
            this._isolated = true;
            var state = this.state;
            var ret = fn(this);
            this.state = state;
            delete this._isolated;
            return ret;
         }
         /**
          Prepares string before mask processing
          @protected
          */

      }, {
         key: "doPrepare",
         value: function doPrepare(str) {
            var flags = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
            return this.prepare ? this.prepare(str, this, flags) : str;
         }
         /**
          Validates if value is acceptable
          @protected
          */

      }, {
         key: "doValidate",
         value: function doValidate(flags) {
            return (!this.validate || this.validate(this.value, this, flags)) && (!this.parent || this.parent.doValidate(flags));
         }
         /**
          Does additional processing in the end of editing
          @protected
          */

      }, {
         key: "doCommit",
         value: function doCommit() {
            if (this.commit) this.commit(this.value, this);
         }
         /** */

      }, {
         key: "doFormat",
         value: function doFormat(value) {
            return this.format ? this.format(value, this) : value;
         }
         /** */

      }, {
         key: "doParse",
         value: function doParse(str) {
            return this.parse ? this.parse(str, this) : str;
         }
         /** */

      }, {
         key: "splice",
         value: function splice(start, deleteCount, inserted, removeDirection) {
            var tailPos = start + deleteCount;
            var tail = this.extractTail(tailPos);
            var startChangePos = this.nearestInputPos(start, removeDirection);
            var changeDetails = new ChangeDetails({
               tailShift: startChangePos - start // adjust tailShift if start was aligned

            }).aggregate(this.remove(startChangePos)).aggregate(this.append(inserted, {
               input: true
            }, tail));
            return changeDetails;
         }
      }]);

      return Masked;
   }();

   Masked.DEFAULTS = {
      format: function format(v) {
         return v;
      },
      parse: function parse(v) {
         return v;
      }
   };
   IMask.Masked = Masked;

   /** Get Masked class by mask type */

   function maskedClass(mask) {
      if (mask == null) {
         throw new Error('mask property should be defined');
      } // $FlowFixMe


      if (mask instanceof RegExp) return IMask.MaskedRegExp; // $FlowFixMe

      if (isString(mask)) return IMask.MaskedPattern; // $FlowFixMe

      if (mask instanceof Date || mask === Date) return IMask.MaskedDate; // $FlowFixMe

      if (mask instanceof Number || typeof mask === 'number' || mask === Number) return IMask.MaskedNumber; // $FlowFixMe

      if (Array.isArray(mask) || mask === Array) return IMask.MaskedDynamic; // $FlowFixMe

      if (IMask.Masked && mask.prototype instanceof IMask.Masked) return mask; // $FlowFixMe

      if (mask instanceof Function) return IMask.MaskedFunction; // $FlowFixMe

      if (mask instanceof IMask.Masked) return mask.constructor;
      console.warn('Mask not found for mask', mask); // eslint-disable-line no-console
      // $FlowFixMe

      return IMask.Masked;
   }
   /** Creates new {@link Masked} depending on mask type */


   function createMask(opts) {
      // $FlowFixMe
      if (IMask.Masked && opts instanceof IMask.Masked) return opts;
      opts = Object.assign({}, opts);
      var mask = opts.mask; // $FlowFixMe

      if (IMask.Masked && mask instanceof IMask.Masked) return mask;
      var MaskedClass = maskedClass(mask);
      if (!MaskedClass) throw new Error('Masked class is not found for provided mask, appropriate module needs to be import manually before creating mask.');
      return new MaskedClass(opts);
   }

   IMask.createMask = createMask;

   var _excluded = ["mask"];
   var DEFAULT_INPUT_DEFINITIONS = {
      '0': /\d/,
      'a': /[\u0041-\u005A\u0061-\u007A\u00AA\u00B5\u00BA\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u02C1\u02C6-\u02D1\u02E0-\u02E4\u02EC\u02EE\u0370-\u0374\u0376\u0377\u037A-\u037D\u0386\u0388-\u038A\u038C\u038E-\u03A1\u03A3-\u03F5\u03F7-\u0481\u048A-\u0527\u0531-\u0556\u0559\u0561-\u0587\u05D0-\u05EA\u05F0-\u05F2\u0620-\u064A\u066E\u066F\u0671-\u06D3\u06D5\u06E5\u06E6\u06EE\u06EF\u06FA-\u06FC\u06FF\u0710\u0712-\u072F\u074D-\u07A5\u07B1\u07CA-\u07EA\u07F4\u07F5\u07FA\u0800-\u0815\u081A\u0824\u0828\u0840-\u0858\u08A0\u08A2-\u08AC\u0904-\u0939\u093D\u0950\u0958-\u0961\u0971-\u0977\u0979-\u097F\u0985-\u098C\u098F\u0990\u0993-\u09A8\u09AA-\u09B0\u09B2\u09B6-\u09B9\u09BD\u09CE\u09DC\u09DD\u09DF-\u09E1\u09F0\u09F1\u0A05-\u0A0A\u0A0F\u0A10\u0A13-\u0A28\u0A2A-\u0A30\u0A32\u0A33\u0A35\u0A36\u0A38\u0A39\u0A59-\u0A5C\u0A5E\u0A72-\u0A74\u0A85-\u0A8D\u0A8F-\u0A91\u0A93-\u0AA8\u0AAA-\u0AB0\u0AB2\u0AB3\u0AB5-\u0AB9\u0ABD\u0AD0\u0AE0\u0AE1\u0B05-\u0B0C\u0B0F\u0B10\u0B13-\u0B28\u0B2A-\u0B30\u0B32\u0B33\u0B35-\u0B39\u0B3D\u0B5C\u0B5D\u0B5F-\u0B61\u0B71\u0B83\u0B85-\u0B8A\u0B8E-\u0B90\u0B92-\u0B95\u0B99\u0B9A\u0B9C\u0B9E\u0B9F\u0BA3\u0BA4\u0BA8-\u0BAA\u0BAE-\u0BB9\u0BD0\u0C05-\u0C0C\u0C0E-\u0C10\u0C12-\u0C28\u0C2A-\u0C33\u0C35-\u0C39\u0C3D\u0C58\u0C59\u0C60\u0C61\u0C85-\u0C8C\u0C8E-\u0C90\u0C92-\u0CA8\u0CAA-\u0CB3\u0CB5-\u0CB9\u0CBD\u0CDE\u0CE0\u0CE1\u0CF1\u0CF2\u0D05-\u0D0C\u0D0E-\u0D10\u0D12-\u0D3A\u0D3D\u0D4E\u0D60\u0D61\u0D7A-\u0D7F\u0D85-\u0D96\u0D9A-\u0DB1\u0DB3-\u0DBB\u0DBD\u0DC0-\u0DC6\u0E01-\u0E30\u0E32\u0E33\u0E40-\u0E46\u0E81\u0E82\u0E84\u0E87\u0E88\u0E8A\u0E8D\u0E94-\u0E97\u0E99-\u0E9F\u0EA1-\u0EA3\u0EA5\u0EA7\u0EAA\u0EAB\u0EAD-\u0EB0\u0EB2\u0EB3\u0EBD\u0EC0-\u0EC4\u0EC6\u0EDC-\u0EDF\u0F00\u0F40-\u0F47\u0F49-\u0F6C\u0F88-\u0F8C\u1000-\u102A\u103F\u1050-\u1055\u105A-\u105D\u1061\u1065\u1066\u106E-\u1070\u1075-\u1081\u108E\u10A0-\u10C5\u10C7\u10CD\u10D0-\u10FA\u10FC-\u1248\u124A-\u124D\u1250-\u1256\u1258\u125A-\u125D\u1260-\u1288\u128A-\u128D\u1290-\u12B0\u12B2-\u12B5\u12B8-\u12BE\u12C0\u12C2-\u12C5\u12C8-\u12D6\u12D8-\u1310\u1312-\u1315\u1318-\u135A\u1380-\u138F\u13A0-\u13F4\u1401-\u166C\u166F-\u167F\u1681-\u169A\u16A0-\u16EA\u1700-\u170C\u170E-\u1711\u1720-\u1731\u1740-\u1751\u1760-\u176C\u176E-\u1770\u1780-\u17B3\u17D7\u17DC\u1820-\u1877\u1880-\u18A8\u18AA\u18B0-\u18F5\u1900-\u191C\u1950-\u196D\u1970-\u1974\u1980-\u19AB\u19C1-\u19C7\u1A00-\u1A16\u1A20-\u1A54\u1AA7\u1B05-\u1B33\u1B45-\u1B4B\u1B83-\u1BA0\u1BAE\u1BAF\u1BBA-\u1BE5\u1C00-\u1C23\u1C4D-\u1C4F\u1C5A-\u1C7D\u1CE9-\u1CEC\u1CEE-\u1CF1\u1CF5\u1CF6\u1D00-\u1DBF\u1E00-\u1F15\u1F18-\u1F1D\u1F20-\u1F45\u1F48-\u1F4D\u1F50-\u1F57\u1F59\u1F5B\u1F5D\u1F5F-\u1F7D\u1F80-\u1FB4\u1FB6-\u1FBC\u1FBE\u1FC2-\u1FC4\u1FC6-\u1FCC\u1FD0-\u1FD3\u1FD6-\u1FDB\u1FE0-\u1FEC\u1FF2-\u1FF4\u1FF6-\u1FFC\u2071\u207F\u2090-\u209C\u2102\u2107\u210A-\u2113\u2115\u2119-\u211D\u2124\u2126\u2128\u212A-\u212D\u212F-\u2139\u213C-\u213F\u2145-\u2149\u214E\u2183\u2184\u2C00-\u2C2E\u2C30-\u2C5E\u2C60-\u2CE4\u2CEB-\u2CEE\u2CF2\u2CF3\u2D00-\u2D25\u2D27\u2D2D\u2D30-\u2D67\u2D6F\u2D80-\u2D96\u2DA0-\u2DA6\u2DA8-\u2DAE\u2DB0-\u2DB6\u2DB8-\u2DBE\u2DC0-\u2DC6\u2DC8-\u2DCE\u2DD0-\u2DD6\u2DD8-\u2DDE\u2E2F\u3005\u3006\u3031-\u3035\u303B\u303C\u3041-\u3096\u309D-\u309F\u30A1-\u30FA\u30FC-\u30FF\u3105-\u312D\u3131-\u318E\u31A0-\u31BA\u31F0-\u31FF\u3400-\u4DB5\u4E00-\u9FCC\uA000-\uA48C\uA4D0-\uA4FD\uA500-\uA60C\uA610-\uA61F\uA62A\uA62B\uA640-\uA66E\uA67F-\uA697\uA6A0-\uA6E5\uA717-\uA71F\uA722-\uA788\uA78B-\uA78E\uA790-\uA793\uA7A0-\uA7AA\uA7F8-\uA801\uA803-\uA805\uA807-\uA80A\uA80C-\uA822\uA840-\uA873\uA882-\uA8B3\uA8F2-\uA8F7\uA8FB\uA90A-\uA925\uA930-\uA946\uA960-\uA97C\uA984-\uA9B2\uA9CF\uAA00-\uAA28\uAA40-\uAA42\uAA44-\uAA4B\uAA60-\uAA76\uAA7A\uAA80-\uAAAF\uAAB1\uAAB5\uAAB6\uAAB9-\uAABD\uAAC0\uAAC2\uAADB-\uAADD\uAAE0-\uAAEA\uAAF2-\uAAF4\uAB01-\uAB06\uAB09-\uAB0E\uAB11-\uAB16\uAB20-\uAB26\uAB28-\uAB2E\uABC0-\uABE2\uAC00-\uD7A3\uD7B0-\uD7C6\uD7CB-\uD7FB\uF900-\uFA6D\uFA70-\uFAD9\uFB00-\uFB06\uFB13-\uFB17\uFB1D\uFB1F-\uFB28\uFB2A-\uFB36\uFB38-\uFB3C\uFB3E\uFB40\uFB41\uFB43\uFB44\uFB46-\uFBB1\uFBD3-\uFD3D\uFD50-\uFD8F\uFD92-\uFDC7\uFDF0-\uFDFB\uFE70-\uFE74\uFE76-\uFEFC\uFF21-\uFF3A\uFF41-\uFF5A\uFF66-\uFFBE\uFFC2-\uFFC7\uFFCA-\uFFCF\uFFD2-\uFFD7\uFFDA-\uFFDC]/,
      // http://stackoverflow.com/a/22075070
      '*': /./
   };
   /** */

   var PatternInputDefinition = /*#__PURE__*/function () {
      /** */

      /** */

      /** */

      /** */

      /** */

      /** */
      function PatternInputDefinition(opts) {
         _classCallCheck(this, PatternInputDefinition);

         var mask = opts.mask,
            blockOpts = _objectWithoutProperties(opts, _excluded);

         this.masked = createMask({
            mask: mask
         });
         Object.assign(this, blockOpts);
      }

      _createClass(PatternInputDefinition, [{
         key: "reset",
         value: function reset() {
            this._isFilled = false;
            this.masked.reset();
         }
      }, {
         key: "remove",
         value: function remove() {
            var fromPos = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
            var toPos = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : this.value.length;

            if (fromPos === 0 && toPos >= 1) {
               this._isFilled = false;
               return this.masked.remove(fromPos, toPos);
            }

            return new ChangeDetails();
         }
      }, {
         key: "value",
         get: function get() {
            return this.masked.value || (this._isFilled && !this.isOptional ? this.placeholderChar : '');
         }
      }, {
         key: "unmaskedValue",
         get: function get() {
            return this.masked.unmaskedValue;
         }
      }, {
         key: "isComplete",
         get: function get() {
            return Boolean(this.masked.value) || this.isOptional;
         }
      }, {
         key: "_appendChar",
         value: function _appendChar(str) {
            var flags = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
            if (this._isFilled) return new ChangeDetails();
            var state = this.masked.state; // simulate input

            var details = this.masked._appendChar(str, flags);

            if (details.inserted && this.doValidate(flags) === false) {
               details.inserted = details.rawInserted = '';
               this.masked.state = state;
            }

            if (!details.inserted && !this.isOptional && !this.lazy && !flags.input) {
               details.inserted = this.placeholderChar;
            }

            details.skip = !details.inserted && !this.isOptional;
            this._isFilled = Boolean(details.inserted);
            return details;
         }
      }, {
         key: "append",
         value: function append() {
            var _this$masked;

            return (_this$masked = this.masked).append.apply(_this$masked, arguments);
         }
      }, {
         key: "_appendPlaceholder",
         value: function _appendPlaceholder() {
            var details = new ChangeDetails();
            if (this._isFilled || this.isOptional) return details;
            this._isFilled = true;
            details.inserted = this.placeholderChar;
            return details;
         }
      }, {
         key: "extractTail",
         value: function extractTail() {
            var _this$masked2;

            return (_this$masked2 = this.masked).extractTail.apply(_this$masked2, arguments);
         }
      }, {
         key: "appendTail",
         value: function appendTail() {
            var _this$masked3;

            return (_this$masked3 = this.masked).appendTail.apply(_this$masked3, arguments);
         }
      }, {
         key: "extractInput",
         value: function extractInput() {
            var fromPos = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
            var toPos = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : this.value.length;
            var flags = arguments.length > 2 ? arguments[2] : undefined;
            return this.masked.extractInput(fromPos, toPos, flags);
         }
      }, {
         key: "nearestInputPos",
         value: function nearestInputPos(cursorPos) {
            var direction = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : DIRECTION.NONE;
            var minPos = 0;
            var maxPos = this.value.length;
            var boundPos = Math.min(Math.max(cursorPos, minPos), maxPos);

            switch (direction) {
               case DIRECTION.LEFT:
               case DIRECTION.FORCE_LEFT:
                  return this.isComplete ? boundPos : minPos;

               case DIRECTION.RIGHT:
               case DIRECTION.FORCE_RIGHT:
                  return this.isComplete ? boundPos : maxPos;

               case DIRECTION.NONE:
               default:
                  return boundPos;
            }
         }
      }, {
         key: "doValidate",
         value: function doValidate() {
            var _this$masked4, _this$parent;

            return (_this$masked4 = this.masked).doValidate.apply(_this$masked4, arguments) && (!this.parent || (_this$parent = this.parent).doValidate.apply(_this$parent, arguments));
         }
      }, {
         key: "doCommit",
         value: function doCommit() {
            this.masked.doCommit();
         }
      }, {
         key: "state",
         get: function get() {
            return {
               masked: this.masked.state,
               _isFilled: this._isFilled
            };
         },
         set: function set(state) {
            this.masked.state = state.masked;
            this._isFilled = state._isFilled;
         }
      }]);

      return PatternInputDefinition;
   }();

   var PatternFixedDefinition = /*#__PURE__*/function () {
      /** */

      /** */

      /** */

      /** */
      function PatternFixedDefinition(opts) {
         _classCallCheck(this, PatternFixedDefinition);

         Object.assign(this, opts);
         this._value = '';
      }

      _createClass(PatternFixedDefinition, [{
         key: "value",
         get: function get() {
            return this._value;
         }
      }, {
         key: "unmaskedValue",
         get: function get() {
            return this.isUnmasking ? this.value : '';
         }
      }, {
         key: "reset",
         value: function reset() {
            this._isRawInput = false;
            this._value = '';
         }
      }, {
         key: "remove",
         value: function remove() {
            var fromPos = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
            var toPos = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : this._value.length;
            this._value = this._value.slice(0, fromPos) + this._value.slice(toPos);
            if (!this._value) this._isRawInput = false;
            return new ChangeDetails();
         }
      }, {
         key: "nearestInputPos",
         value: function nearestInputPos(cursorPos) {
            var direction = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : DIRECTION.NONE;
            var minPos = 0;
            var maxPos = this._value.length;

            switch (direction) {
               case DIRECTION.LEFT:
               case DIRECTION.FORCE_LEFT:
                  return minPos;

               case DIRECTION.NONE:
               case DIRECTION.RIGHT:
               case DIRECTION.FORCE_RIGHT:
               default:
                  return maxPos;
            }
         }
      }, {
         key: "extractInput",
         value: function extractInput() {
            var fromPos = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
            var toPos = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : this._value.length;
            var flags = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
            return flags.raw && this._isRawInput && this._value.slice(fromPos, toPos) || '';
         }
      }, {
         key: "isComplete",
         get: function get() {
            return true;
         }
      }, {
         key: "_appendChar",
         value: function _appendChar(str) {
            var flags = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
            var details = new ChangeDetails();
            if (this._value) return details;
            var appended = this.char === str[0];
            var isResolved = appended && (this.isUnmasking || flags.input || flags.raw) && !flags.tail;
            if (isResolved) details.rawInserted = this.char;
            this._value = details.inserted = this.char;
            this._isRawInput = isResolved && (flags.raw || flags.input);
            return details;
         }
      }, {
         key: "_appendPlaceholder",
         value: function _appendPlaceholder() {
            var details = new ChangeDetails();
            if (this._value) return details;
            this._value = details.inserted = this.char;
            return details;
         }
      }, {
         key: "extractTail",
         value: function extractTail() {
            arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : this.value.length;
            return new ContinuousTailDetails('');
         } // $FlowFixMe no ideas

      }, {
         key: "appendTail",
         value: function appendTail(tail) {
            if (isString(tail)) tail = new ContinuousTailDetails(String(tail));
            return tail.appendTo(this);
         }
      }, {
         key: "append",
         value: function append(str, flags, tail) {
            var details = this._appendChar(str, flags);

            if (tail != null) {
               details.tailShift += this.appendTail(tail).tailShift;
            }

            return details;
         }
      }, {
         key: "doCommit",
         value: function doCommit() {}
      }, {
         key: "state",
         get: function get() {
            return {
               _value: this._value,
               _isRawInput: this._isRawInput
            };
         },
         set: function set(state) {
            Object.assign(this, state);
         }
      }]);

      return PatternFixedDefinition;
   }();

   var _excluded$1 = ["chunks"];

   var ChunksTailDetails = /*#__PURE__*/function () {
      /** */
      function ChunksTailDetails() {
         var chunks = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
         var from = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;

         _classCallCheck(this, ChunksTailDetails);

         this.chunks = chunks;
         this.from = from;
      }

      _createClass(ChunksTailDetails, [{
         key: "toString",
         value: function toString() {
            return this.chunks.map(String).join('');
         } // $FlowFixMe no ideas

      }, {
         key: "extend",
         value: function extend(tailChunk) {
            if (!String(tailChunk)) return;
            if (isString(tailChunk)) tailChunk = new ContinuousTailDetails(String(tailChunk));
            var lastChunk = this.chunks[this.chunks.length - 1];
            var extendLast = lastChunk && (lastChunk.stop === tailChunk.stop || tailChunk.stop == null) && // if tail chunk goes just after last chunk
               tailChunk.from === lastChunk.from + lastChunk.toString().length;

            if (tailChunk instanceof ContinuousTailDetails) {
               // check the ability to extend previous chunk
               if (extendLast) {
                  // extend previous chunk
                  lastChunk.extend(tailChunk.toString());
               } else {
                  // append new chunk
                  this.chunks.push(tailChunk);
               }
            } else if (tailChunk instanceof ChunksTailDetails) {
               if (tailChunk.stop == null) {
                  // unwrap floating chunks to parent, keeping `from` pos
                  var firstTailChunk;

                  while (tailChunk.chunks.length && tailChunk.chunks[0].stop == null) {
                     firstTailChunk = tailChunk.chunks.shift();
                     firstTailChunk.from += tailChunk.from;
                     this.extend(firstTailChunk);
                  }
               } // if tail chunk still has value


               if (tailChunk.toString()) {
                  // if chunks contains stops, then popup stop to container
                  tailChunk.stop = tailChunk.blockIndex;
                  this.chunks.push(tailChunk);
               }
            }
         }
      }, {
         key: "appendTo",
         value: function appendTo(masked) {
            // $FlowFixMe
            if (!(masked instanceof IMask.MaskedPattern)) {
               var tail = new ContinuousTailDetails(this.toString());
               return tail.appendTo(masked);
            }

            var details = new ChangeDetails();

            for (var ci = 0; ci < this.chunks.length && !details.skip; ++ci) {
               var chunk = this.chunks[ci];

               var lastBlockIter = masked._mapPosToBlock(masked.value.length);

               var stop = chunk.stop;
               var chunkBlock = void 0;

               if (stop != null && (!lastBlockIter || lastBlockIter.index <= stop)) {
                  if (chunk instanceof ChunksTailDetails || // for continuous block also check if stop is exist
                     masked._stops.indexOf(stop) >= 0) {
                     details.aggregate(masked._appendPlaceholder(stop));
                  }

                  chunkBlock = chunk instanceof ChunksTailDetails && masked._blocks[stop];
               }

               if (chunkBlock) {
                  var tailDetails = chunkBlock.appendTail(chunk);
                  tailDetails.skip = false; // always ignore skip, it will be set on last

                  details.aggregate(tailDetails);
                  masked._value += tailDetails.inserted; // get not inserted chars

                  var remainChars = chunk.toString().slice(tailDetails.rawInserted.length);
                  if (remainChars) details.aggregate(masked.append(remainChars, {
                     tail: true
                  }));
               } else {
                  details.aggregate(masked.append(chunk.toString(), {
                     tail: true
                  }));
               }
            }

            return details;
         }
      }, {
         key: "state",
         get: function get() {
            return {
               chunks: this.chunks.map(function (c) {
                  return c.state;
               }),
               from: this.from,
               stop: this.stop,
               blockIndex: this.blockIndex
            };
         },
         set: function set(state) {
            var chunks = state.chunks,
               props = _objectWithoutProperties(state, _excluded$1);

            Object.assign(this, props);
            this.chunks = chunks.map(function (cstate) {
               var chunk = "chunks" in cstate ? new ChunksTailDetails() : new ContinuousTailDetails(); // $FlowFixMe already checked above

               chunk.state = cstate;
               return chunk;
            });
         }
      }, {
         key: "shiftBefore",
         value: function shiftBefore(pos) {
            if (this.from >= pos || !this.chunks.length) return '';
            var chunkShiftPos = pos - this.from;
            var ci = 0;

            while (ci < this.chunks.length) {
               var chunk = this.chunks[ci];
               var shiftChar = chunk.shiftBefore(chunkShiftPos);

               if (chunk.toString()) {
                  // chunk still contains value
                  // but not shifted - means no more available chars to shift
                  if (!shiftChar) break;
                  ++ci;
               } else {
                  // clean if chunk has no value
                  this.chunks.splice(ci, 1);
               }

               if (shiftChar) return shiftChar;
            }

            return '';
         }
      }]);

      return ChunksTailDetails;
   }();

   /** Masking by RegExp */

   var MaskedRegExp = /*#__PURE__*/function (_Masked) {
      _inherits(MaskedRegExp, _Masked);

      var _super = _createSuper(MaskedRegExp);

      function MaskedRegExp() {
         _classCallCheck(this, MaskedRegExp);

         return _super.apply(this, arguments);
      }

      _createClass(MaskedRegExp, [{
         key: "_update",
         value:
            /**
             @override
             @param {Object} opts
             */
            function _update(opts) {
               if (opts.mask) opts.validate = function (value) {
                  return value.search(opts.mask) >= 0;
               };

               _get(_getPrototypeOf(MaskedRegExp.prototype), "_update", this).call(this, opts);
            }
      }]);

      return MaskedRegExp;
   }(Masked);

   IMask.MaskedRegExp = MaskedRegExp;

   var _excluded$2 = ["_blocks"];
   /**
    Pattern mask
    @param {Object} opts
    @param {Object} opts.blocks
    @param {Object} opts.definitions
    @param {string} opts.placeholderChar
    @param {boolean} opts.lazy
    */

   var MaskedPattern = /*#__PURE__*/function (_Masked) {
      _inherits(MaskedPattern, _Masked);

      var _super = _createSuper(MaskedPattern);
      /** */

      /** */

      /** Single char for empty input */

      /** Show placeholder only when needed */


      function MaskedPattern() {
         var opts = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

         _classCallCheck(this, MaskedPattern); // TODO type $Shape<MaskedPatternOptions>={} does not work


         opts.definitions = Object.assign({}, DEFAULT_INPUT_DEFINITIONS, opts.definitions);
         return _super.call(this, Object.assign({}, MaskedPattern.DEFAULTS, opts));
      }
      /**
       @override
       @param {Object} opts
       */


      _createClass(MaskedPattern, [{
         key: "_update",
         value: function _update() {
            var opts = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
            opts.definitions = Object.assign({}, this.definitions, opts.definitions);

            _get(_getPrototypeOf(MaskedPattern.prototype), "_update", this).call(this, opts);

            this._rebuildMask();
         }
         /** */

      }, {
         key: "_rebuildMask",
         value: function _rebuildMask() {
            var _this = this;

            var defs = this.definitions;
            this._blocks = [];
            this._stops = [];
            this._maskedBlocks = {};
            var pattern = this.mask;
            if (!pattern || !defs) return;
            var unmaskingBlock = false;
            var optionalBlock = false;

            for (var i$$1 = 0; i$$1 < pattern.length; ++i$$1) {
               if (this.blocks) {
                  var _ret = function () {
                     var p = pattern.slice(i$$1);
                     var bNames = Object.keys(_this.blocks).filter(function (bName) {
                        return p.indexOf(bName) === 0;
                     }); // order by key length

                     bNames.sort(function (a$$1, b$$1) {
                        return b$$1.length - a$$1.length;
                     }); // use block name with max length

                     var bName = bNames[0];

                     if (bName) {
                        // $FlowFixMe no ideas
                        var maskedBlock = createMask(Object.assign({
                           parent: _this,
                           lazy: _this.lazy,
                           placeholderChar: _this.placeholderChar,
                           overwrite: _this.overwrite
                        }, _this.blocks[bName]));

                        if (maskedBlock) {
                           _this._blocks.push(maskedBlock); // store block index


                           if (!_this._maskedBlocks[bName]) _this._maskedBlocks[bName] = [];

                           _this._maskedBlocks[bName].push(_this._blocks.length - 1);
                        }

                        i$$1 += bName.length - 1;
                        return "continue";
                     }
                  }();

                  if (_ret === "continue") continue;
               }

               var char = pattern[i$$1];

               var _isInput = (char in defs);

               if (char === MaskedPattern.STOP_CHAR) {
                  this._stops.push(this._blocks.length);

                  continue;
               }

               if (char === '{' || char === '}') {
                  unmaskingBlock = !unmaskingBlock;
                  continue;
               }

               if (char === '[' || char === ']') {
                  optionalBlock = !optionalBlock;
                  continue;
               }

               if (char === MaskedPattern.ESCAPE_CHAR) {
                  ++i$$1;
                  char = pattern[i$$1];
                  if (!char) break;
                  _isInput = false;
               }

               var def = _isInput ? new PatternInputDefinition({
                  parent: this,
                  lazy: this.lazy,
                  placeholderChar: this.placeholderChar,
                  mask: defs[char],
                  isOptional: optionalBlock
               }) : new PatternFixedDefinition({
                  char: char,
                  isUnmasking: unmaskingBlock
               });

               this._blocks.push(def);
            }
         }
         /**
          @override
          */

      }, {
         key: "state",
         get: function get() {
            return Object.assign({}, _get(_getPrototypeOf(MaskedPattern.prototype), "state", this), {
               _blocks: this._blocks.map(function (b$$1) {
                  return b$$1.state;
               })
            });
         },
         set: function set(state) {
            var _blocks = state._blocks,
               maskedState = _objectWithoutProperties(state, _excluded$2);

            this._blocks.forEach(function (b$$1, bi) {
               return b$$1.state = _blocks[bi];
            });

            _set(_getPrototypeOf(MaskedPattern.prototype), "state", maskedState, this, true);
         }
         /**
          @override
          */

      }, {
         key: "reset",
         value: function reset() {
            _get(_getPrototypeOf(MaskedPattern.prototype), "reset", this).call(this);

            this._blocks.forEach(function (b$$1) {
               return b$$1.reset();
            });
         }
         /**
          @override
          */

      }, {
         key: "isComplete",
         get: function get() {
            return this._blocks.every(function (b$$1) {
               return b$$1.isComplete;
            });
         }
         /**
          @override
          */

      }, {
         key: "doCommit",
         value: function doCommit() {
            this._blocks.forEach(function (b$$1) {
               return b$$1.doCommit();
            });

            _get(_getPrototypeOf(MaskedPattern.prototype), "doCommit", this).call(this);
         }
         /**
          @override
          */

      }, {
         key: "unmaskedValue",
         get: function get() {
            return this._blocks.reduce(function (str, b$$1) {
               return str += b$$1.unmaskedValue;
            }, '');
         },
         set: function set(unmaskedValue) {
            _set(_getPrototypeOf(MaskedPattern.prototype), "unmaskedValue", unmaskedValue, this, true);
         }
         /**
          @override
          */

      }, {
         key: "value",
         get: function get() {
            // TODO return _value when not in change?
            return this._blocks.reduce(function (str, b$$1) {
               return str += b$$1.value;
            }, '');
         },
         set: function set(value) {
            _set(_getPrototypeOf(MaskedPattern.prototype), "value", value, this, true);
         }
         /**
          @override
          */

      }, {
         key: "appendTail",
         value: function appendTail(tail) {
            return _get(_getPrototypeOf(MaskedPattern.prototype), "appendTail", this).call(this, tail).aggregate(this._appendPlaceholder());
         }
         /**
          @override
          */

      }, {
         key: "_appendCharRaw",
         value: function _appendCharRaw(ch) {
            var flags = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

            var blockIter = this._mapPosToBlock(this.value.length);

            var details = new ChangeDetails();
            if (!blockIter) return details;

            for (var bi = blockIter.index;; ++bi) {
               var _block = this._blocks[bi];
               if (!_block) break;

               var blockDetails = _block._appendChar(ch, flags);

               var skip = blockDetails.skip;
               details.aggregate(blockDetails);
               if (skip || blockDetails.rawInserted) break; // go next char
            }

            return details;
         }
         /**
          @override
          */

      }, {
         key: "extractTail",
         value: function extractTail() {
            var _this2 = this;

            var fromPos = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
            var toPos = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : this.value.length;
            var chunkTail = new ChunksTailDetails();
            if (fromPos === toPos) return chunkTail;

            this._forEachBlocksInRange(fromPos, toPos, function (b$$1, bi, bFromPos, bToPos) {
               var blockChunk = b$$1.extractTail(bFromPos, bToPos);
               blockChunk.stop = _this2._findStopBefore(bi);
               blockChunk.from = _this2._blockStartPos(bi);
               if (blockChunk instanceof ChunksTailDetails) blockChunk.blockIndex = bi;
               chunkTail.extend(blockChunk);
            });

            return chunkTail;
         }
         /**
          @override
          */

      }, {
         key: "extractInput",
         value: function extractInput() {
            var fromPos = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
            var toPos = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : this.value.length;
            var flags = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
            if (fromPos === toPos) return '';
            var input = '';

            this._forEachBlocksInRange(fromPos, toPos, function (b$$1, _$$1, fromPos, toPos) {
               input += b$$1.extractInput(fromPos, toPos, flags);
            });

            return input;
         }
      }, {
         key: "_findStopBefore",
         value: function _findStopBefore(blockIndex) {
            var stopBefore;

            for (var si = 0; si < this._stops.length; ++si) {
               var stop = this._stops[si];
               if (stop <= blockIndex) stopBefore = stop;else break;
            }

            return stopBefore;
         }
         /** Appends placeholder depending on laziness */

      }, {
         key: "_appendPlaceholder",
         value: function _appendPlaceholder(toBlockIndex) {
            var _this3 = this;

            var details = new ChangeDetails();
            if (this.lazy && toBlockIndex == null) return details;

            var startBlockIter = this._mapPosToBlock(this.value.length);

            if (!startBlockIter) return details;
            var startBlockIndex = startBlockIter.index;
            var endBlockIndex = toBlockIndex != null ? toBlockIndex : this._blocks.length;

            this._blocks.slice(startBlockIndex, endBlockIndex).forEach(function (b$$1) {
               if (!b$$1.lazy || toBlockIndex != null) {
                  // $FlowFixMe `_blocks` may not be present
                  var args = b$$1._blocks != null ? [b$$1._blocks.length] : [];

                  var bDetails = b$$1._appendPlaceholder.apply(b$$1, args);

                  _this3._value += bDetails.inserted;
                  details.aggregate(bDetails);
               }
            });

            return details;
         }
         /** Finds block in pos */

      }, {
         key: "_mapPosToBlock",
         value: function _mapPosToBlock(pos) {
            var accVal = '';

            for (var bi = 0; bi < this._blocks.length; ++bi) {
               var _block2 = this._blocks[bi];
               var blockStartPos = accVal.length;
               accVal += _block2.value;

               if (pos <= accVal.length) {
                  return {
                     index: bi,
                     offset: pos - blockStartPos
                  };
               }
            }
         }
         /** */

      }, {
         key: "_blockStartPos",
         value: function _blockStartPos(blockIndex) {
            return this._blocks.slice(0, blockIndex).reduce(function (pos, b$$1) {
               return pos += b$$1.value.length;
            }, 0);
         }
         /** */

      }, {
         key: "_forEachBlocksInRange",
         value: function _forEachBlocksInRange(fromPos) {
            var toPos = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : this.value.length;
            var fn = arguments.length > 2 ? arguments[2] : undefined;

            var fromBlockIter = this._mapPosToBlock(fromPos);

            if (fromBlockIter) {
               var toBlockIter = this._mapPosToBlock(toPos); // process first block


               var isSameBlock = toBlockIter && fromBlockIter.index === toBlockIter.index;
               var fromBlockStartPos = fromBlockIter.offset;
               var fromBlockEndPos = toBlockIter && isSameBlock ? toBlockIter.offset : this._blocks[fromBlockIter.index].value.length;
               fn(this._blocks[fromBlockIter.index], fromBlockIter.index, fromBlockStartPos, fromBlockEndPos);

               if (toBlockIter && !isSameBlock) {
                  // process intermediate blocks
                  for (var bi = fromBlockIter.index + 1; bi < toBlockIter.index; ++bi) {
                     fn(this._blocks[bi], bi, 0, this._blocks[bi].value.length);
                  } // process last block


                  fn(this._blocks[toBlockIter.index], toBlockIter.index, 0, toBlockIter.offset);
               }
            }
         }
         /**
          @override
          */

      }, {
         key: "remove",
         value: function remove() {
            var fromPos = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
            var toPos = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : this.value.length;

            var removeDetails = _get(_getPrototypeOf(MaskedPattern.prototype), "remove", this).call(this, fromPos, toPos);

            this._forEachBlocksInRange(fromPos, toPos, function (b$$1, _$$1, bFromPos, bToPos) {
               removeDetails.aggregate(b$$1.remove(bFromPos, bToPos));
            });

            return removeDetails;
         }
         /**
          @override
          */

      }, {
         key: "nearestInputPos",
         value: function nearestInputPos(cursorPos) {
            var direction = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : DIRECTION.NONE; // TODO refactor - extract alignblock

            var beginBlockData = this._mapPosToBlock(cursorPos) || {
               index: 0,
               offset: 0
            };
            var beginBlockOffset = beginBlockData.offset,
               beginBlockIndex = beginBlockData.index;
            var beginBlock = this._blocks[beginBlockIndex];
            if (!beginBlock) return cursorPos;
            var beginBlockCursorPos = beginBlockOffset; // if position inside block - try to adjust it

            if (beginBlockCursorPos !== 0 && beginBlockCursorPos < beginBlock.value.length) {
               beginBlockCursorPos = beginBlock.nearestInputPos(beginBlockOffset, forceDirection(direction));
            }

            var cursorAtRight = beginBlockCursorPos === beginBlock.value.length;
            var cursorAtLeft = beginBlockCursorPos === 0; //  cursor is INSIDE first block (not at bounds)

            if (!cursorAtLeft && !cursorAtRight) return this._blockStartPos(beginBlockIndex) + beginBlockCursorPos;
            var searchBlockIndex = cursorAtRight ? beginBlockIndex + 1 : beginBlockIndex;

            if (direction === DIRECTION.NONE) {
               // NONE direction used to calculate start input position if no chars were removed
               // FOR NONE:
               // -
               // input|any
               // ->
               //  any|input
               // <-
               //  filled-input|any
               // check if first block at left is input
               if (searchBlockIndex > 0) {
                  var blockIndexAtLeft = searchBlockIndex - 1;
                  var blockAtLeft = this._blocks[blockIndexAtLeft];
                  var blockInputPos = blockAtLeft.nearestInputPos(0, DIRECTION.NONE); // is input

                  if (!blockAtLeft.value.length || blockInputPos !== blockAtLeft.value.length) {
                     return this._blockStartPos(searchBlockIndex);
                  }
               } // ->


               var firstInputAtRight = searchBlockIndex;

               for (var bi = firstInputAtRight; bi < this._blocks.length; ++bi) {
                  var blockAtRight = this._blocks[bi];

                  var _blockInputPos = blockAtRight.nearestInputPos(0, DIRECTION.NONE);

                  if (!blockAtRight.value.length || _blockInputPos !== blockAtRight.value.length) {
                     return this._blockStartPos(bi) + _blockInputPos;
                  }
               } // <-
               // find first non-fixed symbol


               for (var _bi = searchBlockIndex - 1; _bi >= 0; --_bi) {
                  var _block3 = this._blocks[_bi];

                  var _blockInputPos2 = _block3.nearestInputPos(0, DIRECTION.NONE); // is input


                  if (!_block3.value.length || _blockInputPos2 !== _block3.value.length) {
                     return this._blockStartPos(_bi) + _block3.value.length;
                  }
               }

               return cursorPos;
            }

            if (direction === DIRECTION.LEFT || direction === DIRECTION.FORCE_LEFT) {
               // -
               //  any|filled-input
               // <-
               //  any|first not empty is not-len-aligned
               //  not-0-aligned|any
               // ->
               //  any|not-len-aligned or end
               // check if first block at right is filled input
               var firstFilledBlockIndexAtRight;

               for (var _bi2 = searchBlockIndex; _bi2 < this._blocks.length; ++_bi2) {
                  if (this._blocks[_bi2].value) {
                     firstFilledBlockIndexAtRight = _bi2;
                     break;
                  }
               }

               if (firstFilledBlockIndexAtRight != null) {
                  var filledBlock = this._blocks[firstFilledBlockIndexAtRight];

                  var _blockInputPos3 = filledBlock.nearestInputPos(0, DIRECTION.RIGHT);

                  if (_blockInputPos3 === 0 && filledBlock.unmaskedValue.length) {
                     // filled block is input
                     return this._blockStartPos(firstFilledBlockIndexAtRight) + _blockInputPos3;
                  }
               } // <-
               // find this vars


               var firstFilledInputBlockIndex = -1;
               var firstEmptyInputBlockIndex; // TODO consider nested empty inputs

               for (var _bi3 = searchBlockIndex - 1; _bi3 >= 0; --_bi3) {
                  var _block4 = this._blocks[_bi3];

                  var _blockInputPos4 = _block4.nearestInputPos(_block4.value.length, DIRECTION.FORCE_LEFT);

                  if (!_block4.value || _blockInputPos4 !== 0) firstEmptyInputBlockIndex = _bi3;

                  if (_blockInputPos4 !== 0) {
                     if (_blockInputPos4 !== _block4.value.length) {
                        // aligned inside block - return immediately
                        return this._blockStartPos(_bi3) + _blockInputPos4;
                     } else {
                        // found filled
                        firstFilledInputBlockIndex = _bi3;
                        break;
                     }
                  }
               }

               if (direction === DIRECTION.LEFT) {
                  // try find first empty input before start searching position only when not forced
                  for (var _bi4 = firstFilledInputBlockIndex + 1; _bi4 <= Math.min(searchBlockIndex, this._blocks.length - 1); ++_bi4) {
                     var _block5 = this._blocks[_bi4];

                     var _blockInputPos5 = _block5.nearestInputPos(0, DIRECTION.NONE);

                     var blockAlignedPos = this._blockStartPos(_bi4) + _blockInputPos5;

                     if (blockAlignedPos > cursorPos) break; // if block is not lazy input

                     if (_blockInputPos5 !== _block5.value.length) return blockAlignedPos;
                  }
               } // process overflow


               if (firstFilledInputBlockIndex >= 0) {
                  return this._blockStartPos(firstFilledInputBlockIndex) + this._blocks[firstFilledInputBlockIndex].value.length;
               } // for lazy if has aligned left inside fixed and has came to the start - use start position


               if (direction === DIRECTION.FORCE_LEFT || this.lazy && !this.extractInput() && !isInput(this._blocks[searchBlockIndex])) {
                  return 0;
               }

               if (firstEmptyInputBlockIndex != null) {
                  return this._blockStartPos(firstEmptyInputBlockIndex);
               } // find first input


               for (var _bi5 = searchBlockIndex; _bi5 < this._blocks.length; ++_bi5) {
                  var _block6 = this._blocks[_bi5];

                  var _blockInputPos6 = _block6.nearestInputPos(0, DIRECTION.NONE); // is input


                  if (!_block6.value.length || _blockInputPos6 !== _block6.value.length) {
                     return this._blockStartPos(_bi5) + _blockInputPos6;
                  }
               }

               return 0;
            }

            if (direction === DIRECTION.RIGHT || direction === DIRECTION.FORCE_RIGHT) {
               // ->
               //  any|not-len-aligned and filled
               //  any|not-len-aligned
               // <-
               //  not-0-aligned or start|any
               var firstInputBlockAlignedIndex;
               var firstInputBlockAlignedPos;

               for (var _bi6 = searchBlockIndex; _bi6 < this._blocks.length; ++_bi6) {
                  var _block7 = this._blocks[_bi6];

                  var _blockInputPos7 = _block7.nearestInputPos(0, DIRECTION.NONE);

                  if (_blockInputPos7 !== _block7.value.length) {
                     firstInputBlockAlignedPos = this._blockStartPos(_bi6) + _blockInputPos7;
                     firstInputBlockAlignedIndex = _bi6;
                     break;
                  }
               }

               if (firstInputBlockAlignedIndex != null && firstInputBlockAlignedPos != null) {
                  for (var _bi7 = firstInputBlockAlignedIndex; _bi7 < this._blocks.length; ++_bi7) {
                     var _block8 = this._blocks[_bi7];

                     var _blockInputPos8 = _block8.nearestInputPos(0, DIRECTION.FORCE_RIGHT);

                     if (_blockInputPos8 !== _block8.value.length) {
                        return this._blockStartPos(_bi7) + _blockInputPos8;
                     }
                  }

                  return direction === DIRECTION.FORCE_RIGHT ? this.value.length : firstInputBlockAlignedPos;
               }

               for (var _bi8 = Math.min(searchBlockIndex, this._blocks.length - 1); _bi8 >= 0; --_bi8) {
                  var _block9 = this._blocks[_bi8];

                  var _blockInputPos9 = _block9.nearestInputPos(_block9.value.length, DIRECTION.LEFT);

                  if (_blockInputPos9 !== 0) {
                     var alignedPos = this._blockStartPos(_bi8) + _blockInputPos9;

                     if (alignedPos >= cursorPos) return alignedPos;
                     break;
                  }
               }
            }

            return cursorPos;
         }
         /** Get block by name */

      }, {
         key: "maskedBlock",
         value: function maskedBlock(name) {
            return this.maskedBlocks(name)[0];
         }
         /** Get all blocks by name */

      }, {
         key: "maskedBlocks",
         value: function maskedBlocks(name) {
            var _this4 = this;

            var indices = this._maskedBlocks[name];
            if (!indices) return [];
            return indices.map(function (gi) {
               return _this4._blocks[gi];
            });
         }
      }]);

      return MaskedPattern;
   }(Masked);

   MaskedPattern.DEFAULTS = {
      lazy: true,
      placeholderChar: '_'
   };
   MaskedPattern.STOP_CHAR = '`';
   MaskedPattern.ESCAPE_CHAR = '\\';
   MaskedPattern.InputDefinition = PatternInputDefinition;
   MaskedPattern.FixedDefinition = PatternFixedDefinition;

   function isInput(block) {
      if (!block) return false;
      var value = block.value;
      return !value || block.nearestInputPos(0, DIRECTION.NONE) !== value.length;
   }

   IMask.MaskedPattern = MaskedPattern;

   /** Pattern which accepts ranges */

   var MaskedRange = /*#__PURE__*/function (_MaskedPattern) {
      _inherits(MaskedRange, _MaskedPattern);

      var _super = _createSuper(MaskedRange);

      function MaskedRange() {
         _classCallCheck(this, MaskedRange);

         return _super.apply(this, arguments);
      }

      _createClass(MaskedRange, [{
         key: "_matchFrom",
         get:
         /**
          Optionally sets max length of pattern.
          Used when pattern length is longer then `to` param length. Pads zeros at start in this case.
          */

         /** Min bound */

         /** Max bound */

            /** */
            function get() {
               return this.maxLength - String(this.from).length;
            }
         /**
          @override
          */

      }, {
         key: "_update",
         value: function _update(opts) {
            // TODO type
            opts = Object.assign({
               to: this.to || 0,
               from: this.from || 0
            }, opts);
            var maxLength = String(opts.to).length;
            if (opts.maxLength != null) maxLength = Math.max(maxLength, opts.maxLength);
            opts.maxLength = maxLength;
            var fromStr = String(opts.from).padStart(maxLength, '0');
            var toStr = String(opts.to).padStart(maxLength, '0');
            var sameCharsCount = 0;

            while (sameCharsCount < toStr.length && toStr[sameCharsCount] === fromStr[sameCharsCount]) {
               ++sameCharsCount;
            }

            opts.mask = toStr.slice(0, sameCharsCount).replace(/0/g, '\\0') + '0'.repeat(maxLength - sameCharsCount);

            _get(_getPrototypeOf(MaskedRange.prototype), "_update", this).call(this, opts);
         }
         /**
          @override
          */

      }, {
         key: "isComplete",
         get: function get() {
            return _get(_getPrototypeOf(MaskedRange.prototype), "isComplete", this) && Boolean(this.value);
         }
      }, {
         key: "boundaries",
         value: function boundaries(str) {
            var minstr = '';
            var maxstr = '';

            var _ref = str.match(/^(\D*)(\d*)(\D*)/) || [],
               _ref2 = _slicedToArray(_ref, 3),
               placeholder = _ref2[1],
               num = _ref2[2];

            if (num) {
               minstr = '0'.repeat(placeholder.length) + num;
               maxstr = '9'.repeat(placeholder.length) + num;
            }

            minstr = minstr.padEnd(this.maxLength, '0');
            maxstr = maxstr.padEnd(this.maxLength, '9');
            return [minstr, maxstr];
         }
         /**
          @override
          */

      }, {
         key: "doPrepare",
         value: function doPrepare(str) {
            var flags = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
            str = _get(_getPrototypeOf(MaskedRange.prototype), "doPrepare", this).call(this, str, flags).replace(/\D/g, '');
            if (!this.autofix) return str;
            var fromStr = String(this.from).padStart(this.maxLength, '0');
            var toStr = String(this.to).padStart(this.maxLength, '0');
            var val = this.value;
            var prepStr = '';

            for (var ci = 0; ci < str.length; ++ci) {
               var nextVal = val + prepStr + str[ci];

               var _this$boundaries = this.boundaries(nextVal),
                  _this$boundaries2 = _slicedToArray(_this$boundaries, 2),
                  minstr = _this$boundaries2[0],
                  maxstr = _this$boundaries2[1];

               if (Number(maxstr) < this.from) prepStr += fromStr[nextVal.length - 1];else if (Number(minstr) > this.to) prepStr += toStr[nextVal.length - 1];else prepStr += str[ci];
            }

            return prepStr;
         }
         /**
          @override
          */

      }, {
         key: "doValidate",
         value: function doValidate() {
            var _get2;

            var str = this.value;
            var firstNonZero = str.search(/[^0]/);
            if (firstNonZero === -1 && str.length <= this._matchFrom) return true;

            var _this$boundaries3 = this.boundaries(str),
               _this$boundaries4 = _slicedToArray(_this$boundaries3, 2),
               minstr = _this$boundaries4[0],
               maxstr = _this$boundaries4[1];

            for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
               args[_key] = arguments[_key];
            }

            return this.from <= Number(maxstr) && Number(minstr) <= this.to && (_get2 = _get(_getPrototypeOf(MaskedRange.prototype), "doValidate", this)).call.apply(_get2, [this].concat(args));
         }
      }]);

      return MaskedRange;
   }(MaskedPattern);

   IMask.MaskedRange = MaskedRange;

   /** Date mask */

   var MaskedDate = /*#__PURE__*/function (_MaskedPattern) {
      _inherits(MaskedDate, _MaskedPattern);

      var _super = _createSuper(MaskedDate);
      /** Pattern mask for date according to {@link MaskedDate#format} */

      /** Start date */

      /** End date */

      /** */

      /**
       @param {Object} opts
       */


      function MaskedDate(opts) {
         _classCallCheck(this, MaskedDate);

         return _super.call(this, Object.assign({}, MaskedDate.DEFAULTS, opts));
      }
      /**
       @override
       */


      _createClass(MaskedDate, [{
         key: "_update",
         value: function _update(opts) {
            if (opts.mask === Date) delete opts.mask;
            if (opts.pattern) opts.mask = opts.pattern;
            var blocks = opts.blocks;
            opts.blocks = Object.assign({}, MaskedDate.GET_DEFAULT_BLOCKS()); // adjust year block

            if (opts.min) opts.blocks.Y.from = opts.min.getFullYear();
            if (opts.max) opts.blocks.Y.to = opts.max.getFullYear();

            if (opts.min && opts.max && opts.blocks.Y.from === opts.blocks.Y.to) {
               opts.blocks.m.from = opts.min.getMonth() + 1;
               opts.blocks.m.to = opts.max.getMonth() + 1;

               if (opts.blocks.m.from === opts.blocks.m.to) {
                  opts.blocks.d.from = opts.min.getDate();
                  opts.blocks.d.to = opts.max.getDate();
               }
            }

            Object.assign(opts.blocks, blocks); // add autofix

            Object.keys(opts.blocks).forEach(function (bk) {
               var b = opts.blocks[bk];
               if (!('autofix' in b)) b.autofix = opts.autofix;
            });

            _get(_getPrototypeOf(MaskedDate.prototype), "_update", this).call(this, opts);
         }
         /**
          @override
          */

      }, {
         key: "doValidate",
         value: function doValidate() {
            var _get2;

            var date = this.date;

            for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
               args[_key] = arguments[_key];
            }

            return (_get2 = _get(_getPrototypeOf(MaskedDate.prototype), "doValidate", this)).call.apply(_get2, [this].concat(args)) && (!this.isComplete || this.isDateExist(this.value) && date != null && (this.min == null || this.min <= date) && (this.max == null || date <= this.max));
         }
         /** Checks if date is exists */

      }, {
         key: "isDateExist",
         value: function isDateExist(str) {
            return this.format(this.parse(str, this), this).indexOf(str) >= 0;
         }
         /** Parsed Date */

      }, {
         key: "date",
         get: function get() {
            return this.typedValue;
         },
         set: function set(date) {
            this.typedValue = date;
         }
         /**
          @override
          */

      }, {
         key: "typedValue",
         get: function get() {
            return this.isComplete ? _get(_getPrototypeOf(MaskedDate.prototype), "typedValue", this) : null;
         },
         set: function set(value) {
            _set(_getPrototypeOf(MaskedDate.prototype), "typedValue", value, this, true);
         }
      }]);

      return MaskedDate;
   }(MaskedPattern);

   MaskedDate.DEFAULTS = {
      pattern: 'd{.}`m{.}`Y',
      format: function format(date) {
         var day = String(date.getDate()).padStart(2, '0');
         var month = String(date.getMonth() + 1).padStart(2, '0');
         var year = date.getFullYear();
         return [day, month, year].join('.');
      },
      parse: function parse(str) {
         var _str$split = str.split('.'),
            _str$split2 = _slicedToArray(_str$split, 3),
            day = _str$split2[0],
            month = _str$split2[1],
            year = _str$split2[2];

         return new Date(year, month - 1, day);
      }
   };

   MaskedDate.GET_DEFAULT_BLOCKS = function () {
      return {
         d: {
            mask: MaskedRange,
            from: 1,
            to: 31,
            maxLength: 2
         },
         m: {
            mask: MaskedRange,
            from: 1,
            to: 12,
            maxLength: 2
         },
         Y: {
            mask: MaskedRange,
            from: 1900,
            to: 9999
         }
      };
   };

   IMask.MaskedDate = MaskedDate;

   /**
    Generic element API to use with mask
    @interface
    */

   var MaskElement = /*#__PURE__*/function () {
      function MaskElement() {
         _classCallCheck(this, MaskElement);
      }

      _createClass(MaskElement, [{
         key: "selectionStart",
         get:
         /** */

         /** */

         /** */

            /** Safely returns selection start */
            function get() {
               var start;

               try {
                  start = this._unsafeSelectionStart;
               } catch (e) {}

               return start != null ? start : this.value.length;
            }
         /** Safely returns selection end */

      }, {
         key: "selectionEnd",
         get: function get() {
            var end;

            try {
               end = this._unsafeSelectionEnd;
            } catch (e) {}

            return end != null ? end : this.value.length;
         }
         /** Safely sets element selection */

      }, {
         key: "select",
         value: function select(start, end) {
            if (start == null || end == null || start === this.selectionStart && end === this.selectionEnd) return;

            try {
               this._unsafeSelect(start, end);
            } catch (e) {}
         }
         /** Should be overriden in subclasses */

      }, {
         key: "_unsafeSelect",
         value: function _unsafeSelect(start, end) {}
         /** Should be overriden in subclasses */

      }, {
         key: "isActive",
         get: function get() {
            return false;
         }
         /** Should be overriden in subclasses */

      }, {
         key: "bindEvents",
         value: function bindEvents(handlers) {}
         /** Should be overriden in subclasses */

      }, {
         key: "unbindEvents",
         value: function unbindEvents() {}
      }]);

      return MaskElement;
   }();

   IMask.MaskElement = MaskElement;

   /** Bridge between HTMLElement and {@link Masked} */

   var HTMLMaskElement = /*#__PURE__*/function (_MaskElement) {
      _inherits(HTMLMaskElement, _MaskElement);

      var _super = _createSuper(HTMLMaskElement);
      /** Mapping between HTMLElement events and mask internal events */

      /** HTMLElement to use mask on */

      /**
       @param {HTMLInputElement|HTMLTextAreaElement} input
       */


      function HTMLMaskElement(input) {
         var _this;

         _classCallCheck(this, HTMLMaskElement);

         _this = _super.call(this);
         _this.input = input;
         _this._handlers = {};
         return _this;
      }
      /** */
      // $FlowFixMe https://github.com/facebook/flow/issues/2839


      _createClass(HTMLMaskElement, [{
         key: "rootElement",
         get: function get() {
            return this.input.getRootNode ? this.input.getRootNode() : document;
         }
         /**
          Is element in focus
          @readonly
          */

      }, {
         key: "isActive",
         get: function get() {
            //$FlowFixMe
            return this.input === this.rootElement.activeElement;
         }
         /**
          Returns HTMLElement selection start
          @override
          */

      }, {
         key: "_unsafeSelectionStart",
         get: function get() {
            return this.input.selectionStart;
         }
         /**
          Returns HTMLElement selection end
          @override
          */

      }, {
         key: "_unsafeSelectionEnd",
         get: function get() {
            return this.input.selectionEnd;
         }
         /**
          Sets HTMLElement selection
          @override
          */

      }, {
         key: "_unsafeSelect",
         value: function _unsafeSelect(start, end) {
            this.input.setSelectionRange(start, end);
         }
         /**
          HTMLElement value
          @override
          */

      }, {
         key: "value",
         get: function get() {
            return this.input.value;
         },
         set: function set(value) {
            this.input.value = value;
         }
         /**
          Binds HTMLElement events to mask internal events
          @override
          */

      }, {
         key: "bindEvents",
         value: function bindEvents(handlers) {
            var _this2 = this;

            Object.keys(handlers).forEach(function (event) {
               return _this2._toggleEventHandler(HTMLMaskElement.EVENTS_MAP[event], handlers[event]);
            });
         }
         /**
          Unbinds HTMLElement events to mask internal events
          @override
          */

      }, {
         key: "unbindEvents",
         value: function unbindEvents() {
            var _this3 = this;

            Object.keys(this._handlers).forEach(function (event) {
               return _this3._toggleEventHandler(event);
            });
         }
         /** */

      }, {
         key: "_toggleEventHandler",
         value: function _toggleEventHandler(event, handler) {
            if (this._handlers[event]) {
               this.input.removeEventListener(event, this._handlers[event]);
               delete this._handlers[event];
            }

            if (handler) {
               this.input.addEventListener(event, handler);
               this._handlers[event] = handler;
            }
         }
      }]);

      return HTMLMaskElement;
   }(MaskElement);

   HTMLMaskElement.EVENTS_MAP = {
      selectionChange: 'keydown',
      input: 'input',
      drop: 'drop',
      click: 'click',
      focus: 'focus',
      commit: 'blur'
   };
   IMask.HTMLMaskElement = HTMLMaskElement;

   var HTMLContenteditableMaskElement = /*#__PURE__*/function (_HTMLMaskElement) {
      _inherits(HTMLContenteditableMaskElement, _HTMLMaskElement);

      var _super = _createSuper(HTMLContenteditableMaskElement);

      function HTMLContenteditableMaskElement() {
         _classCallCheck(this, HTMLContenteditableMaskElement);

         return _super.apply(this, arguments);
      }

      _createClass(HTMLContenteditableMaskElement, [{
         key: "_unsafeSelectionStart",
         get:
            /**
             Returns HTMLElement selection start
             @override
             */
            function get() {
               var root = this.rootElement;
               var selection = root.getSelection && root.getSelection();
               return selection && selection.anchorOffset;
            }
         /**
          Returns HTMLElement selection end
          @override
          */

      }, {
         key: "_unsafeSelectionEnd",
         get: function get() {
            var root = this.rootElement;
            var selection = root.getSelection && root.getSelection();
            return selection && this._unsafeSelectionStart + String(selection).length;
         }
         /**
          Sets HTMLElement selection
          @override
          */

      }, {
         key: "_unsafeSelect",
         value: function _unsafeSelect(start, end) {
            if (!this.rootElement.createRange) return;
            var range = this.rootElement.createRange();
            range.setStart(this.input.firstChild || this.input, start);
            range.setEnd(this.input.lastChild || this.input, end);
            var root = this.rootElement;
            var selection = root.getSelection && root.getSelection();

            if (selection) {
               selection.removeAllRanges();
               selection.addRange(range);
            }
         }
         /**
          HTMLElement value
          @override
          */

      }, {
         key: "value",
         get: function get() {
            // $FlowFixMe
            return this.input.textContent;
         },
         set: function set(value) {
            this.input.textContent = value;
         }
      }]);

      return HTMLContenteditableMaskElement;
   }(HTMLMaskElement);

   IMask.HTMLContenteditableMaskElement = HTMLContenteditableMaskElement;

   var _excluded$3 = ["mask"];
   /** Listens to element events and controls changes between element and {@link Masked} */

   var InputMask = /*#__PURE__*/function () {
      /**
       View element
       @readonly
       */

      /**
       Internal {@link Masked} model
       @readonly
       */

      /**
       @param {MaskElement|HTMLInputElement|HTMLTextAreaElement} el
       @param {Object} opts
       */
      function InputMask(el, opts) {
         _classCallCheck(this, InputMask);

         this.el = el instanceof MaskElement ? el : el.isContentEditable && el.tagName !== 'INPUT' && el.tagName !== 'TEXTAREA' ? new HTMLContenteditableMaskElement(el) : new HTMLMaskElement(el);
         this.masked = createMask(opts);
         this._listeners = {};
         this._value = '';
         this._unmaskedValue = '';
         this._saveSelection = this._saveSelection.bind(this);
         this._onInput = this._onInput.bind(this);
         this._onChange = this._onChange.bind(this);
         this._onDrop = this._onDrop.bind(this);
         this._onFocus = this._onFocus.bind(this);
         this._onClick = this._onClick.bind(this);
         this.alignCursor = this.alignCursor.bind(this);
         this.alignCursorFriendly = this.alignCursorFriendly.bind(this);

         this._bindEvents(); // refresh


         this.updateValue();

         this._onChange();
      }
      /** Read or update mask */


      _createClass(InputMask, [{
         key: "mask",
         get: function get() {
            return this.masked.mask;
         },
         set: function set(mask) {
            if (this.maskEquals(mask)) return;

            if (!(mask instanceof IMask.Masked) && this.masked.constructor === maskedClass(mask)) {
               this.masked.updateOptions({
                  mask: mask
               });
               return;
            }

            var masked = createMask({
               mask: mask
            });
            masked.unmaskedValue = this.masked.unmaskedValue;
            this.masked = masked;
         }
         /** Raw value */

      }, {
         key: "maskEquals",
         value: function maskEquals(mask) {
            return mask == null || mask === this.masked.mask || mask === Date && this.masked instanceof MaskedDate;
         }
      }, {
         key: "value",
         get: function get() {
            return this._value;
         },
         set: function set(str) {
            this.masked.value = str;
            this.updateControl();
            this.alignCursor();
         }
         /** Unmasked value */

      }, {
         key: "unmaskedValue",
         get: function get() {
            return this._unmaskedValue;
         },
         set: function set(str) {
            this.masked.unmaskedValue = str;
            this.updateControl();
            this.alignCursor();
         }
         /** Typed unmasked value */

      }, {
         key: "typedValue",
         get: function get() {
            return this.masked.typedValue;
         },
         set: function set(val) {
            this.masked.typedValue = val;
            this.updateControl();
            this.alignCursor();
         }
         /**
          Starts listening to element events
          @protected
          */

      }, {
         key: "_bindEvents",
         value: function _bindEvents() {
            this.el.bindEvents({
               selectionChange: this._saveSelection,
               input: this._onInput,
               drop: this._onDrop,
               click: this._onClick,
               focus: this._onFocus,
               commit: this._onChange
            });
         }
         /**
          Stops listening to element events
          @protected
          */

      }, {
         key: "_unbindEvents",
         value: function _unbindEvents() {
            if (this.el) this.el.unbindEvents();
         }
         /**
          Fires custom event
          @protected
          */

      }, {
         key: "_fireEvent",
         value: function _fireEvent(ev) {
            for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
               args[_key - 1] = arguments[_key];
            }

            var listeners = this._listeners[ev];
            if (!listeners) return;
            listeners.forEach(function (l) {
               return l.apply(void 0, args);
            });
         }
         /**
          Current selection start
          @readonly
          */

      }, {
         key: "selectionStart",
         get: function get() {
            return this._cursorChanging ? this._changingCursorPos : this.el.selectionStart;
         }
         /** Current cursor position */

      }, {
         key: "cursorPos",
         get: function get() {
            return this._cursorChanging ? this._changingCursorPos : this.el.selectionEnd;
         },
         set: function set(pos) {
            if (!this.el || !this.el.isActive) return;
            this.el.select(pos, pos);

            this._saveSelection();
         }
         /**
          Stores current selection
          @protected
          */

      }, {
         key: "_saveSelection",
         value: function _saveSelection() {
            if (this.value !== this.el.value) {
               console.warn('Element value was changed outside of mask. Syncronize mask using `mask.updateValue()` to work properly.'); // eslint-disable-line no-console
            }

            this._selection = {
               start: this.selectionStart,
               end: this.cursorPos
            };
         }
         /** Syncronizes model value from view */

      }, {
         key: "updateValue",
         value: function updateValue() {
            this.masked.value = this.el.value;
            this._value = this.masked.value;
         }
         /** Syncronizes view from model value, fires change events */

      }, {
         key: "updateControl",
         value: function updateControl() {
            var newUnmaskedValue = this.masked.unmaskedValue;
            var newValue = this.masked.value;
            var isChanged = this.unmaskedValue !== newUnmaskedValue || this.value !== newValue;
            this._unmaskedValue = newUnmaskedValue;
            this._value = newValue;
            if (this.el.value !== newValue) this.el.value = newValue;
            if (isChanged) this._fireChangeEvents();
         }
         /** Updates options with deep equal check, recreates @{link Masked} model if mask type changes */

      }, {
         key: "updateOptions",
         value: function updateOptions(opts) {
            var mask = opts.mask,
               restOpts = _objectWithoutProperties(opts, _excluded$3);

            var updateMask = !this.maskEquals(mask);
            var updateOpts = !objectIncludes(this.masked, restOpts);
            if (updateMask) this.mask = mask;
            if (updateOpts) this.masked.updateOptions(restOpts);
            if (updateMask || updateOpts) this.updateControl();
         }
         /** Updates cursor */

      }, {
         key: "updateCursor",
         value: function updateCursor(cursorPos) {
            if (cursorPos == null) return;
            this.cursorPos = cursorPos; // also queue change cursor for mobile browsers

            this._delayUpdateCursor(cursorPos);
         }
         /**
          Delays cursor update to support mobile browsers
          @private
          */

      }, {
         key: "_delayUpdateCursor",
         value: function _delayUpdateCursor(cursorPos) {
            var _this = this;

            this._abortUpdateCursor();

            this._changingCursorPos = cursorPos;
            this._cursorChanging = setTimeout(function () {
               if (!_this.el) return; // if was destroyed

               _this.cursorPos = _this._changingCursorPos;

               _this._abortUpdateCursor();
            }, 10);
         }
         /**
          Fires custom events
          @protected
          */

      }, {
         key: "_fireChangeEvents",
         value: function _fireChangeEvents() {
            this._fireEvent('accept', this._inputEvent);

            if (this.masked.isComplete) this._fireEvent('complete', this._inputEvent);
         }
         /**
          Aborts delayed cursor update
          @private
          */

      }, {
         key: "_abortUpdateCursor",
         value: function _abortUpdateCursor() {
            if (this._cursorChanging) {
               clearTimeout(this._cursorChanging);
               delete this._cursorChanging;
            }
         }
         /** Aligns cursor to nearest available position */

      }, {
         key: "alignCursor",
         value: function alignCursor() {
            this.cursorPos = this.masked.nearestInputPos(this.cursorPos, DIRECTION.LEFT);
         }
         /** Aligns cursor only if selection is empty */

      }, {
         key: "alignCursorFriendly",
         value: function alignCursorFriendly() {
            if (this.selectionStart !== this.cursorPos) return; // skip if range is selected

            this.alignCursor();
         }
         /** Adds listener on custom event */

      }, {
         key: "on",
         value: function on(ev, handler) {
            if (!this._listeners[ev]) this._listeners[ev] = [];

            this._listeners[ev].push(handler);

            return this;
         }
         /** Removes custom event listener */

      }, {
         key: "off",
         value: function off(ev, handler) {
            if (!this._listeners[ev]) return this;

            if (!handler) {
               delete this._listeners[ev];
               return this;
            }

            var hIndex = this._listeners[ev].indexOf(handler);

            if (hIndex >= 0) this._listeners[ev].splice(hIndex, 1);
            return this;
         }
         /** Handles view input event */

      }, {
         key: "_onInput",
         value: function _onInput(e) {
            this._inputEvent = e;

            this._abortUpdateCursor(); // fix strange IE behavior


            if (!this._selection) return this.updateValue();
            var details = new ActionDetails( // new state
               this.el.value, this.cursorPos, // old state
               this.value, this._selection);
            var oldRawValue = this.masked.rawInputValue;
            var offset = this.masked.splice(details.startChangePos, details.removed.length, details.inserted, details.removeDirection).offset; // force align in remove direction only if no input chars were removed
            // otherwise we still need to align with NONE (to get out from fixed symbols for instance)

            var removeDirection = oldRawValue === this.masked.rawInputValue ? details.removeDirection : DIRECTION.NONE;
            var cursorPos = this.masked.nearestInputPos(details.startChangePos + offset, removeDirection);
            this.updateControl();
            this.updateCursor(cursorPos);
            delete this._inputEvent;
         }
         /** Handles view change event and commits model value */

      }, {
         key: "_onChange",
         value: function _onChange() {
            if (this.value !== this.el.value) {
               this.updateValue();
            }

            this.masked.doCommit();
            this.updateControl();

            this._saveSelection();
         }
         /** Handles view drop event, prevents by default */

      }, {
         key: "_onDrop",
         value: function _onDrop(ev) {
            ev.preventDefault();
            ev.stopPropagation();
         }
         /** Restore last selection on focus */

      }, {
         key: "_onFocus",
         value: function _onFocus(ev) {
            this.alignCursorFriendly();
         }
         /** Restore last selection on focus */

      }, {
         key: "_onClick",
         value: function _onClick(ev) {
            this.alignCursorFriendly();
         }
         /** Unbind view events and removes element reference */

      }, {
         key: "destroy",
         value: function destroy() {
            this._unbindEvents(); // $FlowFixMe why not do so?


            this._listeners.length = 0; // $FlowFixMe

            delete this.el;
         }
      }]);

      return InputMask;
   }();

   IMask.InputMask = InputMask;

   /** Pattern which validates enum values */

   var MaskedEnum = /*#__PURE__*/function (_MaskedPattern) {
      _inherits(MaskedEnum, _MaskedPattern);

      var _super = _createSuper(MaskedEnum);

      function MaskedEnum() {
         _classCallCheck(this, MaskedEnum);

         return _super.apply(this, arguments);
      }

      _createClass(MaskedEnum, [{
         key: "_update",
         value:
            /**
             @override
             @param {Object} opts
             */
            function _update(opts) {
               // TODO type
               if (opts.enum) opts.mask = '*'.repeat(opts.enum[0].length);

               _get(_getPrototypeOf(MaskedEnum.prototype), "_update", this).call(this, opts);
            }
         /**
          @override
          */

      }, {
         key: "doValidate",
         value: function doValidate() {
            var _this = this,
               _get2;

            for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
               args[_key] = arguments[_key];
            }

            return this.enum.some(function (e$$1) {
               return e$$1.indexOf(_this.unmaskedValue) >= 0;
            }) && (_get2 = _get(_getPrototypeOf(MaskedEnum.prototype), "doValidate", this)).call.apply(_get2, [this].concat(args));
         }
      }]);

      return MaskedEnum;
   }(MaskedPattern);

   IMask.MaskedEnum = MaskedEnum;

   /**
    Number mask
    @param {Object} opts
    @param {string} opts.radix - Single char
    @param {string} opts.thousandsSeparator - Single char
    @param {Array<string>} opts.mapToRadix - Array of single chars
    @param {number} opts.min
    @param {number} opts.max
    @param {number} opts.scale - Digits after point
    @param {boolean} opts.signed - Allow negative
    @param {boolean} opts.normalizeZeros - Flag to remove leading and trailing zeros in the end of editing
    @param {boolean} opts.padFractionalZeros - Flag to pad trailing zeros after point in the end of editing
    */

   var MaskedNumber = /*#__PURE__*/function (_Masked) {
      _inherits(MaskedNumber, _Masked);

      var _super = _createSuper(MaskedNumber);
      /** Single char */

      /** Single char */

      /** Array of single chars */

      /** */

      /** */

      /** Digits after point */

      /** */

      /** Flag to remove leading and trailing zeros in the end of editing */

      /** Flag to pad trailing zeros after point in the end of editing */


      function MaskedNumber(opts) {
         _classCallCheck(this, MaskedNumber);

         return _super.call(this, Object.assign({}, MaskedNumber.DEFAULTS, opts));
      }
      /**
       @override
       */


      _createClass(MaskedNumber, [{
         key: "_update",
         value: function _update(opts) {
            _get(_getPrototypeOf(MaskedNumber.prototype), "_update", this).call(this, opts);

            this._updateRegExps();
         }
         /** */

      }, {
         key: "_updateRegExps",
         value: function _updateRegExps() {
            // use different regexp to process user input (more strict, input suffix) and tail shifting
            var start = '^' + (this.allowNegative ? '[+|\\-]?' : '');
            var midInput = '(0|([1-9]+\\d*))?';
            var mid = '\\d*';
            var end = (this.scale ? '(' + escapeRegExp(this.radix) + '\\d{0,' + this.scale + '})?' : '') + '$';
            this._numberRegExpInput = new RegExp(start + midInput + end);
            this._numberRegExp = new RegExp(start + mid + end);
            this._mapToRadixRegExp = new RegExp('[' + this.mapToRadix.map(escapeRegExp).join('') + ']', 'g');
            this._thousandsSeparatorRegExp = new RegExp(escapeRegExp(this.thousandsSeparator), 'g');
         }
         /** */

      }, {
         key: "_removeThousandsSeparators",
         value: function _removeThousandsSeparators(value) {
            return value.replace(this._thousandsSeparatorRegExp, '');
         }
         /** */

      }, {
         key: "_insertThousandsSeparators",
         value: function _insertThousandsSeparators(value) {
            // https://stackoverflow.com/questions/2901102/how-to-print-a-number-with-commas-as-thousands-separators-in-javascript
            var parts = value.split(this.radix);
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, this.thousandsSeparator);
            return parts.join(this.radix);
         }
         /**
          @override
          */

      }, {
         key: "doPrepare",
         value: function doPrepare(str) {
            var _get2;

            for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
               args[_key - 1] = arguments[_key];
            }

            return (_get2 = _get(_getPrototypeOf(MaskedNumber.prototype), "doPrepare", this)).call.apply(_get2, [this, this._removeThousandsSeparators(str.replace(this._mapToRadixRegExp, this.radix))].concat(args));
         }
         /** */

      }, {
         key: "_separatorsCount",
         value: function _separatorsCount(to) {
            var extendOnSeparators = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
            var count = 0;

            for (var pos = 0; pos < to; ++pos) {
               if (this._value.indexOf(this.thousandsSeparator, pos) === pos) {
                  ++count;
                  if (extendOnSeparators) to += this.thousandsSeparator.length;
               }
            }

            return count;
         }
         /** */

      }, {
         key: "_separatorsCountFromSlice",
         value: function _separatorsCountFromSlice() {
            var slice = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : this._value;
            return this._separatorsCount(this._removeThousandsSeparators(slice).length, true);
         }
         /**
          @override
          */

      }, {
         key: "extractInput",
         value: function extractInput() {
            var fromPos = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
            var toPos = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : this.value.length;
            var flags = arguments.length > 2 ? arguments[2] : undefined;

            var _this$_adjustRangeWit = this._adjustRangeWithSeparators(fromPos, toPos);

            var _this$_adjustRangeWit2 = _slicedToArray(_this$_adjustRangeWit, 2);

            fromPos = _this$_adjustRangeWit2[0];
            toPos = _this$_adjustRangeWit2[1];
            return this._removeThousandsSeparators(_get(_getPrototypeOf(MaskedNumber.prototype), "extractInput", this).call(this, fromPos, toPos, flags));
         }
         /**
          @override
          */

      }, {
         key: "_appendCharRaw",
         value: function _appendCharRaw(ch) {
            var flags = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
            if (!this.thousandsSeparator) return _get(_getPrototypeOf(MaskedNumber.prototype), "_appendCharRaw", this).call(this, ch, flags);
            var prevBeforeTailValue = flags.tail && flags._beforeTailState ? flags._beforeTailState._value : this._value;

            var prevBeforeTailSeparatorsCount = this._separatorsCountFromSlice(prevBeforeTailValue);

            this._value = this._removeThousandsSeparators(this.value);

            var appendDetails = _get(_getPrototypeOf(MaskedNumber.prototype), "_appendCharRaw", this).call(this, ch, flags);

            this._value = this._insertThousandsSeparators(this._value);
            var beforeTailValue = flags.tail && flags._beforeTailState ? flags._beforeTailState._value : this._value;

            var beforeTailSeparatorsCount = this._separatorsCountFromSlice(beforeTailValue);

            appendDetails.tailShift += (beforeTailSeparatorsCount - prevBeforeTailSeparatorsCount) * this.thousandsSeparator.length;
            appendDetails.skip = !appendDetails.rawInserted && ch === this.thousandsSeparator;
            return appendDetails;
         }
         /** */

      }, {
         key: "_findSeparatorAround",
         value: function _findSeparatorAround(pos) {
            if (this.thousandsSeparator) {
               var searchFrom = pos - this.thousandsSeparator.length + 1;
               var separatorPos = this.value.indexOf(this.thousandsSeparator, searchFrom);
               if (separatorPos <= pos) return separatorPos;
            }

            return -1;
         }
      }, {
         key: "_adjustRangeWithSeparators",
         value: function _adjustRangeWithSeparators(from, to) {
            var separatorAroundFromPos = this._findSeparatorAround(from);

            if (separatorAroundFromPos >= 0) from = separatorAroundFromPos;

            var separatorAroundToPos = this._findSeparatorAround(to);

            if (separatorAroundToPos >= 0) to = separatorAroundToPos + this.thousandsSeparator.length;
            return [from, to];
         }
         /**
          @override
          */

      }, {
         key: "remove",
         value: function remove() {
            var fromPos = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
            var toPos = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : this.value.length;

            var _this$_adjustRangeWit3 = this._adjustRangeWithSeparators(fromPos, toPos);

            var _this$_adjustRangeWit4 = _slicedToArray(_this$_adjustRangeWit3, 2);

            fromPos = _this$_adjustRangeWit4[0];
            toPos = _this$_adjustRangeWit4[1];
            var valueBeforePos = this.value.slice(0, fromPos);
            var valueAfterPos = this.value.slice(toPos);

            var prevBeforeTailSeparatorsCount = this._separatorsCount(valueBeforePos.length);

            this._value = this._insertThousandsSeparators(this._removeThousandsSeparators(valueBeforePos + valueAfterPos));

            var beforeTailSeparatorsCount = this._separatorsCountFromSlice(valueBeforePos);

            return new ChangeDetails({
               tailShift: (beforeTailSeparatorsCount - prevBeforeTailSeparatorsCount) * this.thousandsSeparator.length
            });
         }
         /**
          @override
          */

      }, {
         key: "nearestInputPos",
         value: function nearestInputPos(cursorPos, direction) {
            if (!this.thousandsSeparator) return cursorPos;

            switch (direction) {
               case DIRECTION.NONE:
               case DIRECTION.LEFT:
               case DIRECTION.FORCE_LEFT:
               {
                  var separatorAtLeftPos = this._findSeparatorAround(cursorPos - 1);

                  if (separatorAtLeftPos >= 0) {
                     var separatorAtLeftEndPos = separatorAtLeftPos + this.thousandsSeparator.length;

                     if (cursorPos < separatorAtLeftEndPos || this.value.length <= separatorAtLeftEndPos || direction === DIRECTION.FORCE_LEFT) {
                        return separatorAtLeftPos;
                     }
                  }

                  break;
               }

               case DIRECTION.RIGHT:
               case DIRECTION.FORCE_RIGHT:
               {
                  var separatorAtRightPos = this._findSeparatorAround(cursorPos);

                  if (separatorAtRightPos >= 0) {
                     return separatorAtRightPos + this.thousandsSeparator.length;
                  }
               }
            }

            return cursorPos;
         }
         /**
          @override
          */

      }, {
         key: "doValidate",
         value: function doValidate(flags) {
            var regexp = flags.input ? this._numberRegExpInput : this._numberRegExp; // validate as string

            var valid = regexp.test(this._removeThousandsSeparators(this.value));

            if (valid) {
               // validate as number
               var number = this.number;
               valid = valid && !isNaN(number) && (this.min == null || this.min >= 0 || this.min <= this.number) && (this.max == null || this.max <= 0 || this.number <= this.max);
            }

            return valid && _get(_getPrototypeOf(MaskedNumber.prototype), "doValidate", this).call(this, flags);
         }
         /**
          @override
          */

      }, {
         key: "doCommit",
         value: function doCommit() {
            if (this.value) {
               var number = this.number;
               var validnum = number; // check bounds

               if (this.min != null) validnum = Math.max(validnum, this.min);
               if (this.max != null) validnum = Math.min(validnum, this.max);
               if (validnum !== number) this.unmaskedValue = String(validnum);
               var formatted = this.value;
               if (this.normalizeZeros) formatted = this._normalizeZeros(formatted);
               if (this.padFractionalZeros) formatted = this._padFractionalZeros(formatted);
               this._value = formatted;
            }

            _get(_getPrototypeOf(MaskedNumber.prototype), "doCommit", this).call(this);
         }
         /** */

      }, {
         key: "_normalizeZeros",
         value: function _normalizeZeros(value) {
            var parts = this._removeThousandsSeparators(value).split(this.radix); // remove leading zeros


            parts[0] = parts[0].replace(/^(\D*)(0*)(\d*)/, function (match, sign, zeros, num) {
               return sign + num;
            }); // add leading zero

            if (value.length && !/\d$/.test(parts[0])) parts[0] = parts[0] + '0';

            if (parts.length > 1) {
               parts[1] = parts[1].replace(/0*$/, ''); // remove trailing zeros

               if (!parts[1].length) parts.length = 1; // remove fractional
            }

            return this._insertThousandsSeparators(parts.join(this.radix));
         }
         /** */

      }, {
         key: "_padFractionalZeros",
         value: function _padFractionalZeros(value) {
            if (!value) return value;
            var parts = value.split(this.radix);
            if (parts.length < 2) parts.push('');
            parts[1] = parts[1].padEnd(this.scale, '0');
            return parts.join(this.radix);
         }
         /**
          @override
          */

      }, {
         key: "unmaskedValue",
         get: function get() {
            return this._removeThousandsSeparators(this._normalizeZeros(this.value)).replace(this.radix, '.');
         },
         set: function set(unmaskedValue) {
            _set(_getPrototypeOf(MaskedNumber.prototype), "unmaskedValue", unmaskedValue.replace('.', this.radix), this, true);
         }
         /**
          @override
          */

      }, {
         key: "typedValue",
         get: function get() {
            return Number(this.unmaskedValue);
         },
         set: function set(n) {
            _set(_getPrototypeOf(MaskedNumber.prototype), "unmaskedValue", String(n), this, true);
         }
         /** Parsed Number */

      }, {
         key: "number",
         get: function get() {
            return this.typedValue;
         },
         set: function set(number) {
            this.typedValue = number;
         }
         /**
          Is negative allowed
          @readonly
          */

      }, {
         key: "allowNegative",
         get: function get() {
            return this.signed || this.min != null && this.min < 0 || this.max != null && this.max < 0;
         }
      }]);

      return MaskedNumber;
   }(Masked);

   MaskedNumber.DEFAULTS = {
      radix: ',',
      thousandsSeparator: '',
      mapToRadix: ['.'],
      scale: 2,
      signed: false,
      normalizeZeros: true,
      padFractionalZeros: false
   };
   IMask.MaskedNumber = MaskedNumber;

   /** Masking by custom Function */

   var MaskedFunction = /*#__PURE__*/function (_Masked) {
      _inherits(MaskedFunction, _Masked);

      var _super = _createSuper(MaskedFunction);

      function MaskedFunction() {
         _classCallCheck(this, MaskedFunction);

         return _super.apply(this, arguments);
      }

      _createClass(MaskedFunction, [{
         key: "_update",
         value:
            /**
             @override
             @param {Object} opts
             */
            function _update(opts) {
               if (opts.mask) opts.validate = opts.mask;

               _get(_getPrototypeOf(MaskedFunction.prototype), "_update", this).call(this, opts);
            }
      }]);

      return MaskedFunction;
   }(Masked);

   IMask.MaskedFunction = MaskedFunction;

   var _excluded$4 = ["compiledMasks", "currentMaskRef", "currentMask"];
   /** Dynamic mask for choosing apropriate mask in run-time */

   var MaskedDynamic = /*#__PURE__*/function (_Masked) {
      _inherits(MaskedDynamic, _Masked);

      var _super = _createSuper(MaskedDynamic);
      /** Currently chosen mask */

      /** Compliled {@link Masked} options */

      /** Chooses {@link Masked} depending on input value */

      /**
       @param {Object} opts
       */


      function MaskedDynamic(opts) {
         var _this;

         _classCallCheck(this, MaskedDynamic);

         _this = _super.call(this, Object.assign({}, MaskedDynamic.DEFAULTS, opts));
         _this.currentMask = null;
         return _this;
      }
      /**
       @override
       */


      _createClass(MaskedDynamic, [{
         key: "_update",
         value: function _update(opts) {
            _get(_getPrototypeOf(MaskedDynamic.prototype), "_update", this).call(this, opts);

            if ('mask' in opts) {
               // mask could be totally dynamic with only `dispatch` option
               this.compiledMasks = Array.isArray(opts.mask) ? opts.mask.map(function (m) {
                  return createMask(m);
               }) : [];
            }
         }
         /**
          @override
          */

      }, {
         key: "_appendCharRaw",
         value: function _appendCharRaw(ch) {
            var flags = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

            var details = this._applyDispatch(ch, flags);

            if (this.currentMask) {
               details.aggregate(this.currentMask._appendChar(ch, flags));
            }

            return details;
         }
      }, {
         key: "_applyDispatch",
         value: function _applyDispatch() {
            var appended = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
            var flags = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
            var prevValueBeforeTail = flags.tail && flags._beforeTailState != null ? flags._beforeTailState._value : this.value;
            var inputValue = this.rawInputValue;
            var insertValue = flags.tail && flags._beforeTailState != null ? // $FlowFixMe - tired to fight with type system
               flags._beforeTailState._rawInputValue : inputValue;
            var tailValue = inputValue.slice(insertValue.length);
            var prevMask = this.currentMask;
            var details = new ChangeDetails();
            var prevMaskState = prevMask && prevMask.state; // clone flags to prevent overwriting `_beforeTailState`

            this.currentMask = this.doDispatch(appended, Object.assign({}, flags)); // restore state after dispatch

            if (this.currentMask) {
               if (this.currentMask !== prevMask) {
                  // if mask changed reapply input
                  this.currentMask.reset();

                  if (insertValue) {
                     // $FlowFixMe - it's ok, we don't change current mask above
                     var d$$1 = this.currentMask.append(insertValue, {
                        raw: true
                     });
                     details.tailShift = d$$1.inserted.length - prevValueBeforeTail.length;
                  }

                  if (tailValue) {
                     // $FlowFixMe - it's ok, we don't change current mask above
                     details.tailShift += this.currentMask.append(tailValue, {
                        raw: true,
                        tail: true
                     }).tailShift;
                  }
               } else {
                  // Dispatch can do something bad with state, so
                  // restore prev mask state
                  this.currentMask.state = prevMaskState;
               }
            }

            return details;
         }
      }, {
         key: "_appendPlaceholder",
         value: function _appendPlaceholder() {
            var details = this._applyDispatch.apply(this, arguments);

            if (this.currentMask) {
               details.aggregate(this.currentMask._appendPlaceholder());
            }

            return details;
         }
         /**
          @override
          */

      }, {
         key: "doDispatch",
         value: function doDispatch(appended) {
            var flags = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
            return this.dispatch(appended, this, flags);
         }
         /**
          @override
          */

      }, {
         key: "doValidate",
         value: function doValidate() {
            var _get2, _this$currentMask;

            for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
               args[_key] = arguments[_key];
            }

            return (_get2 = _get(_getPrototypeOf(MaskedDynamic.prototype), "doValidate", this)).call.apply(_get2, [this].concat(args)) && (!this.currentMask || (_this$currentMask = this.currentMask).doValidate.apply(_this$currentMask, args));
         }
         /**
          @override
          */

      }, {
         key: "reset",
         value: function reset() {
            if (this.currentMask) this.currentMask.reset();
            this.compiledMasks.forEach(function (m) {
               return m.reset();
            });
         }
         /**
          @override
          */

      }, {
         key: "value",
         get: function get() {
            return this.currentMask ? this.currentMask.value : '';
         },
         set: function set(value) {
            _set(_getPrototypeOf(MaskedDynamic.prototype), "value", value, this, true);
         }
         /**
          @override
          */

      }, {
         key: "unmaskedValue",
         get: function get() {
            return this.currentMask ? this.currentMask.unmaskedValue : '';
         },
         set: function set(unmaskedValue) {
            _set(_getPrototypeOf(MaskedDynamic.prototype), "unmaskedValue", unmaskedValue, this, true);
         }
         /**
          @override
          */

      }, {
         key: "typedValue",
         get: function get() {
            return this.currentMask ? this.currentMask.typedValue : '';
         } // probably typedValue should not be used with dynamic
         ,
         set: function set(value) {
            var unmaskedValue = String(value); // double check it

            if (this.currentMask) {
               this.currentMask.typedValue = value;
               unmaskedValue = this.currentMask.unmaskedValue;
            }

            this.unmaskedValue = unmaskedValue;
         }
         /**
          @override
          */

      }, {
         key: "isComplete",
         get: function get() {
            return !!this.currentMask && this.currentMask.isComplete;
         }
         /**
          @override
          */

      }, {
         key: "remove",
         value: function remove() {
            var details = new ChangeDetails();

            if (this.currentMask) {
               var _this$currentMask2;

               details.aggregate((_this$currentMask2 = this.currentMask).remove.apply(_this$currentMask2, arguments)) // update with dispatch
                  .aggregate(this._applyDispatch());
            }

            return details;
         }
         /**
          @override
          */

      }, {
         key: "state",
         get: function get() {
            return Object.assign({}, _get(_getPrototypeOf(MaskedDynamic.prototype), "state", this), {
               _rawInputValue: this.rawInputValue,
               compiledMasks: this.compiledMasks.map(function (m) {
                  return m.state;
               }),
               currentMaskRef: this.currentMask,
               currentMask: this.currentMask && this.currentMask.state
            });
         },
         set: function set(state) {
            var compiledMasks = state.compiledMasks,
               currentMaskRef = state.currentMaskRef,
               currentMask = state.currentMask,
               maskedState = _objectWithoutProperties(state, _excluded$4);

            this.compiledMasks.forEach(function (m, mi) {
               return m.state = compiledMasks[mi];
            });

            if (currentMaskRef != null) {
               this.currentMask = currentMaskRef;
               this.currentMask.state = currentMask;
            }

            _set(_getPrototypeOf(MaskedDynamic.prototype), "state", maskedState, this, true);
         }
         /**
          @override
          */

      }, {
         key: "extractInput",
         value: function extractInput() {
            var _this$currentMask3;

            return this.currentMask ? (_this$currentMask3 = this.currentMask).extractInput.apply(_this$currentMask3, arguments) : '';
         }
         /**
          @override
          */

      }, {
         key: "extractTail",
         value: function extractTail() {
            var _this$currentMask4, _get3;

            for (var _len2 = arguments.length, args = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
               args[_key2] = arguments[_key2];
            }

            return this.currentMask ? (_this$currentMask4 = this.currentMask).extractTail.apply(_this$currentMask4, args) : (_get3 = _get(_getPrototypeOf(MaskedDynamic.prototype), "extractTail", this)).call.apply(_get3, [this].concat(args));
         }
         /**
          @override
          */

      }, {
         key: "doCommit",
         value: function doCommit() {
            if (this.currentMask) this.currentMask.doCommit();

            _get(_getPrototypeOf(MaskedDynamic.prototype), "doCommit", this).call(this);
         }
         /**
          @override
          */

      }, {
         key: "nearestInputPos",
         value: function nearestInputPos() {
            var _this$currentMask5, _get4;

            for (var _len3 = arguments.length, args = new Array(_len3), _key3 = 0; _key3 < _len3; _key3++) {
               args[_key3] = arguments[_key3];
            }

            return this.currentMask ? (_this$currentMask5 = this.currentMask).nearestInputPos.apply(_this$currentMask5, args) : (_get4 = _get(_getPrototypeOf(MaskedDynamic.prototype), "nearestInputPos", this)).call.apply(_get4, [this].concat(args));
         }
      }, {
         key: "overwrite",
         get: function get() {
            return this.currentMask ? this.currentMask.overwrite : _get(_getPrototypeOf(MaskedDynamic.prototype), "overwrite", this);
         },
         set: function set(overwrite) {
            console.warn('"overwrite" option is not available in dynamic mask, use this option in siblings');
         }
      }]);

      return MaskedDynamic;
   }(Masked);

   MaskedDynamic.DEFAULTS = {
      dispatch: function dispatch(appended, masked, flags) {
         if (!masked.compiledMasks.length) return;
         var inputValue = masked.rawInputValue; // simulate input

         var inputs = masked.compiledMasks.map(function (m, index) {
            m.reset();
            m.append(inputValue, {
               raw: true
            });
            m.append(appended, flags);
            var weight = m.rawInputValue.length;
            return {
               weight: weight,
               index: index
            };
         }); // pop masks with longer values first

         inputs.sort(function (i1, i2) {
            return i2.weight - i1.weight;
         });
         return masked.compiledMasks[inputs[0].index];
      }
   };
   IMask.MaskedDynamic = MaskedDynamic;

   /** Mask pipe source and destination types */

   var PIPE_TYPE = {
      MASKED: 'value',
      UNMASKED: 'unmaskedValue',
      TYPED: 'typedValue'
   };
   /** Creates new pipe function depending on mask type, source and destination options */

   function createPipe(mask) {
      var from = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : PIPE_TYPE.MASKED;
      var to = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : PIPE_TYPE.MASKED;
      var masked = createMask(mask);
      return function (value) {
         return masked.runIsolated(function (m) {
            m[from] = value;
            return m[to];
         });
      };
   }
   /** Pipes value through mask depending on mask type, source and destination options */


   function pipe(value) {
      for (var _len = arguments.length, pipeArgs = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
         pipeArgs[_key - 1] = arguments[_key];
      }

      return createPipe.apply(void 0, pipeArgs)(value);
   }

   IMask.PIPE_TYPE = PIPE_TYPE;
   IMask.createPipe = createPipe;
   IMask.pipe = pipe;

   try {
      globalThis.IMask = IMask;
   } catch (e) {}

   function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$1(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it.return != null) it.return(); } finally { if (didErr) throw err; } } }; }

   function _unsupportedIterableToArray$1(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$1(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$1(o, minLen); }

   function _arrayLikeToArray$1(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

   var __create = Object.create;
   var __defProp = Object.defineProperty;
   var __getProtoOf = Object.getPrototypeOf;
   var __hasOwnProp = Object.prototype.hasOwnProperty;
   var __getOwnPropNames = Object.getOwnPropertyNames;
   var __getOwnPropDesc = Object.getOwnPropertyDescriptor;

   var __markAsModule = function __markAsModule(target) {
      return __defProp(target, "__esModule", {
         value: true
      });
   };

   var __commonJS = function __commonJS(callback, module) {
      return function () {
         if (!module) {
            module = {
               exports: {}
            };
            callback(module.exports, module);
         }

         return module.exports;
      };
   };

   var __exportStar = function __exportStar(target, module, desc) {
      if (module && babelHelpers.typeof(module) === "object" || typeof module === "function") {
         var _iterator = _createForOfIteratorHelper(__getOwnPropNames(module)),
            _step;

         try {
            var _loop = function _loop() {
               var key = _step.value;
               if (!__hasOwnProp.call(target, key) && key !== "default") __defProp(target, key, {
                  get: function get() {
                     return module[key];
                  },
                  enumerable: !(desc = __getOwnPropDesc(module, key)) || desc.enumerable
               });
            };

            for (_iterator.s(); !(_step = _iterator.n()).done;) {
               _loop();
            }
         } catch (err) {
            _iterator.e(err);
         } finally {
            _iterator.f();
         }
      }

      return target;
   };

   var __toModule = function __toModule(module) {
      return __exportStar(__markAsModule(__defProp(module != null ? __create(__getProtoOf(module)) : {}, "default", module && module.__esModule && "default" in module ? {
         get: function get() {
            return module.default;
         },
         enumerable: true
      } : {
         value: module,
         enumerable: true
      })), module);
   }; // node_modules/@vue/shared/dist/shared.cjs.js


   var require_shared_cjs = __commonJS(function (exports) {

      var _PatchFlagNames, _slotFlagsText;

      Object.defineProperty(exports, "__esModule", {
         value: true
      });

      function makeMap(str, expectsLowerCase) {
         var map = Object.create(null);
         var list = str.split(",");

         for (var i = 0; i < list.length; i++) {
            map[list[i]] = true;
         }

         return expectsLowerCase ? function (val) {
            return !!map[val.toLowerCase()];
         } : function (val) {
            return !!map[val];
         };
      }

      var PatchFlagNames = (_PatchFlagNames = {}, babelHelpers.defineProperty(_PatchFlagNames, 1, "TEXT"), babelHelpers.defineProperty(_PatchFlagNames, 2, "CLASS"), babelHelpers.defineProperty(_PatchFlagNames, 4, "STYLE"), babelHelpers.defineProperty(_PatchFlagNames, 8, "PROPS"), babelHelpers.defineProperty(_PatchFlagNames, 16, "FULL_PROPS"), babelHelpers.defineProperty(_PatchFlagNames, 32, "HYDRATE_EVENTS"), babelHelpers.defineProperty(_PatchFlagNames, 64, "STABLE_FRAGMENT"), babelHelpers.defineProperty(_PatchFlagNames, 128, "KEYED_FRAGMENT"), babelHelpers.defineProperty(_PatchFlagNames, 256, "UNKEYED_FRAGMENT"), babelHelpers.defineProperty(_PatchFlagNames, 512, "NEED_PATCH"), babelHelpers.defineProperty(_PatchFlagNames, 1024, "DYNAMIC_SLOTS"), babelHelpers.defineProperty(_PatchFlagNames, 2048, "DEV_ROOT_FRAGMENT"), babelHelpers.defineProperty(_PatchFlagNames, -1, "HOISTED"), babelHelpers.defineProperty(_PatchFlagNames, -2, "BAIL"), _PatchFlagNames);
      var slotFlagsText = (_slotFlagsText = {}, babelHelpers.defineProperty(_slotFlagsText, 1, "STABLE"), babelHelpers.defineProperty(_slotFlagsText, 2, "DYNAMIC"), babelHelpers.defineProperty(_slotFlagsText, 3, "FORWARDED"), _slotFlagsText);
      var GLOBALS_WHITE_LISTED = "Infinity,undefined,NaN,isFinite,isNaN,parseFloat,parseInt,decodeURI,decodeURIComponent,encodeURI,encodeURIComponent,Math,Number,Date,Array,Object,Boolean,String,RegExp,Map,Set,JSON,Intl,BigInt";
      var isGloballyWhitelisted = /* @__PURE__ */makeMap(GLOBALS_WHITE_LISTED);
      var range = 2;

      function generateCodeFrame(source) {
         var start2 = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
         var end = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : source.length;
         var lines = source.split(/\r?\n/);
         var count = 0;
         var res = [];

         for (var i = 0; i < lines.length; i++) {
            count += lines[i].length + 1;

            if (count >= start2) {
               for (var j = i - range; j <= i + range || end > count; j++) {
                  if (j < 0 || j >= lines.length) continue;
                  var line = j + 1;
                  res.push("".concat(line).concat(" ".repeat(Math.max(3 - String(line).length, 0)), "|  ").concat(lines[j]));
                  var lineLength = lines[j].length;

                  if (j === i) {
                     var pad = start2 - (count - lineLength) + 1;
                     var length = Math.max(1, end > count ? lineLength - pad : end - start2);
                     res.push("   |  " + " ".repeat(pad) + "^".repeat(length));
                  } else if (j > i) {
                     if (end > count) {
                        var _length = Math.max(Math.min(end - count, lineLength), 1);

                        res.push("   |  " + "^".repeat(_length));
                     }

                     count += lineLength + 1;
                  }
               }

               break;
            }
         }

         return res.join("\n");
      }

      var specialBooleanAttrs = "itemscope,allowfullscreen,formnovalidate,ismap,nomodule,novalidate,readonly";
      var isSpecialBooleanAttr = /* @__PURE__ */makeMap(specialBooleanAttrs);
      var isBooleanAttr2 = /* @__PURE__ */makeMap(specialBooleanAttrs + ",async,autofocus,autoplay,controls,default,defer,disabled,hidden,loop,open,required,reversed,scoped,seamless,checked,muted,multiple,selected");
      var unsafeAttrCharRE = /[>/="'\u0009\u000a\u000c\u0020]/;
      var attrValidationCache = {};

      function isSSRSafeAttrName(name) {
         if (attrValidationCache.hasOwnProperty(name)) {
            return attrValidationCache[name];
         }

         var isUnsafe = unsafeAttrCharRE.test(name);

         if (isUnsafe) {
            console.error("unsafe attribute name: ".concat(name));
         }

         return attrValidationCache[name] = !isUnsafe;
      }

      var propsToAttrMap = {
         acceptCharset: "accept-charset",
         className: "class",
         htmlFor: "for",
         httpEquiv: "http-equiv"
      };
      var isNoUnitNumericStyleProp = /* @__PURE__ */makeMap("animation-iteration-count,border-image-outset,border-image-slice,border-image-width,box-flex,box-flex-group,box-ordinal-group,column-count,columns,flex,flex-grow,flex-positive,flex-shrink,flex-negative,flex-order,grid-row,grid-row-end,grid-row-span,grid-row-start,grid-column,grid-column-end,grid-column-span,grid-column-start,font-weight,line-clamp,line-height,opacity,order,orphans,tab-size,widows,z-index,zoom,fill-opacity,flood-opacity,stop-opacity,stroke-dasharray,stroke-dashoffset,stroke-miterlimit,stroke-opacity,stroke-width");
      var isKnownAttr = /* @__PURE__ */makeMap("accept,accept-charset,accesskey,action,align,allow,alt,async,autocapitalize,autocomplete,autofocus,autoplay,background,bgcolor,border,buffered,capture,challenge,charset,checked,cite,class,code,codebase,color,cols,colspan,content,contenteditable,contextmenu,controls,coords,crossorigin,csp,data,datetime,decoding,default,defer,dir,dirname,disabled,download,draggable,dropzone,enctype,enterkeyhint,for,form,formaction,formenctype,formmethod,formnovalidate,formtarget,headers,height,hidden,high,href,hreflang,http-equiv,icon,id,importance,integrity,ismap,itemprop,keytype,kind,label,lang,language,loading,list,loop,low,manifest,max,maxlength,minlength,media,min,multiple,muted,name,novalidate,open,optimum,pattern,ping,placeholder,poster,preload,radiogroup,readonly,referrerpolicy,rel,required,reversed,rows,rowspan,sandbox,scope,scoped,selected,shape,size,sizes,slot,span,spellcheck,src,srcdoc,srclang,srcset,start,step,style,summary,tabindex,target,title,translate,type,usemap,value,width,wrap");

      function normalizeStyle(value) {
         if (isArray(value)) {
            var res = {};

            for (var i = 0; i < value.length; i++) {
               var item = value[i];
               var normalized = normalizeStyle(isString(item) ? parseStringStyle(item) : item);

               if (normalized) {
                  for (var key in normalized) {
                     res[key] = normalized[key];
                  }
               }
            }

            return res;
         } else if (isObject(value)) {
            return value;
         }
      }

      var listDelimiterRE = /;(?![^(]*\))/g;
      var propertyDelimiterRE = /:(.+)/;

      function parseStringStyle(cssText) {
         var ret = {};
         cssText.split(listDelimiterRE).forEach(function (item) {
            if (item) {
               var tmp = item.split(propertyDelimiterRE);
               tmp.length > 1 && (ret[tmp[0].trim()] = tmp[1].trim());
            }
         });
         return ret;
      }

      function stringifyStyle(styles) {
         var ret = "";

         if (!styles) {
            return ret;
         }

         for (var key in styles) {
            var value = styles[key];
            var normalizedKey = key.startsWith("--") ? key : hyphenate(key);

            if (isString(value) || typeof value === "number" && isNoUnitNumericStyleProp(normalizedKey)) {
               ret += "".concat(normalizedKey, ":").concat(value, ";");
            }
         }

         return ret;
      }

      function normalizeClass(value) {
         var res = "";

         if (isString(value)) {
            res = value;
         } else if (isArray(value)) {
            for (var i = 0; i < value.length; i++) {
               var normalized = normalizeClass(value[i]);

               if (normalized) {
                  res += normalized + " ";
               }
            }
         } else if (isObject(value)) {
            for (var name in value) {
               if (value[name]) {
                  res += name + " ";
               }
            }
         }

         return res.trim();
      }

      var HTML_TAGS = "html,body,base,head,link,meta,style,title,address,article,aside,footer,header,h1,h2,h3,h4,h5,h6,hgroup,nav,section,div,dd,dl,dt,figcaption,figure,picture,hr,img,li,main,ol,p,pre,ul,a,b,abbr,bdi,bdo,br,cite,code,data,dfn,em,i,kbd,mark,q,rp,rt,rtc,ruby,s,samp,small,span,strong,sub,sup,time,u,var,wbr,area,audio,map,track,video,embed,object,param,source,canvas,script,noscript,del,ins,caption,col,colgroup,table,thead,tbody,td,th,tr,button,datalist,fieldset,form,input,label,legend,meter,optgroup,option,output,progress,select,textarea,details,dialog,menu,summary,template,blockquote,iframe,tfoot";
      var SVG_TAGS = "svg,animate,animateMotion,animateTransform,circle,clipPath,color-profile,defs,desc,discard,ellipse,feBlend,feColorMatrix,feComponentTransfer,feComposite,feConvolveMatrix,feDiffuseLighting,feDisplacementMap,feDistanceLight,feDropShadow,feFlood,feFuncA,feFuncB,feFuncG,feFuncR,feGaussianBlur,feImage,feMerge,feMergeNode,feMorphology,feOffset,fePointLight,feSpecularLighting,feSpotLight,feTile,feTurbulence,filter,foreignObject,g,hatch,hatchpath,image,line,linearGradient,marker,mask,mesh,meshgradient,meshpatch,meshrow,metadata,mpath,path,pattern,polygon,polyline,radialGradient,rect,set,solidcolor,stop,switch,symbol,text,textPath,title,tspan,unknown,use,view";
      var VOID_TAGS = "area,base,br,col,embed,hr,img,input,link,meta,param,source,track,wbr";
      var isHTMLTag = /* @__PURE__ */makeMap(HTML_TAGS);
      var isSVGTag = /* @__PURE__ */makeMap(SVG_TAGS);
      var isVoidTag = /* @__PURE__ */makeMap(VOID_TAGS);
      var escapeRE = /["'&<>]/;

      function escapeHtml(string) {
         var str = "" + string;
         var match = escapeRE.exec(str);

         if (!match) {
            return str;
         }

         var html = "";
         var escaped;
         var index;
         var lastIndex = 0;

         for (index = match.index; index < str.length; index++) {
            switch (str.charCodeAt(index)) {
               case 34:
                  escaped = "&quot;";
                  break;

               case 38:
                  escaped = "&amp;";
                  break;

               case 39:
                  escaped = "&#39;";
                  break;

               case 60:
                  escaped = "&lt;";
                  break;

               case 62:
                  escaped = "&gt;";
                  break;

               default:
                  continue;
            }

            if (lastIndex !== index) {
               html += str.substring(lastIndex, index);
            }

            lastIndex = index + 1;
            html += escaped;
         }

         return lastIndex !== index ? html + str.substring(lastIndex, index) : html;
      }

      var commentStripRE = /^-?>|<!--|-->|--!>|<!-$/g;

      function escapeHtmlComment(src) {
         return src.replace(commentStripRE, "");
      }

      function looseCompareArrays(a, b) {
         if (a.length !== b.length) return false;
         var equal = true;

         for (var i = 0; equal && i < a.length; i++) {
            equal = looseEqual(a[i], b[i]);
         }

         return equal;
      }

      function looseEqual(a, b) {
         if (a === b) return true;
         var aValidType = isDate(a);
         var bValidType = isDate(b);

         if (aValidType || bValidType) {
            return aValidType && bValidType ? a.getTime() === b.getTime() : false;
         }

         aValidType = isArray(a);
         bValidType = isArray(b);

         if (aValidType || bValidType) {
            return aValidType && bValidType ? looseCompareArrays(a, b) : false;
         }

         aValidType = isObject(a);
         bValidType = isObject(b);

         if (aValidType || bValidType) {
            if (!aValidType || !bValidType) {
               return false;
            }

            var aKeysCount = Object.keys(a).length;
            var bKeysCount = Object.keys(b).length;

            if (aKeysCount !== bKeysCount) {
               return false;
            }

            for (var key in a) {
               var aHasKey = a.hasOwnProperty(key);
               var bHasKey = b.hasOwnProperty(key);

               if (aHasKey && !bHasKey || !aHasKey && bHasKey || !looseEqual(a[key], b[key])) {
                  return false;
               }
            }
         }

         return String(a) === String(b);
      }

      function looseIndexOf(arr, val) {
         return arr.findIndex(function (item) {
            return looseEqual(item, val);
         });
      }

      var toDisplayString = function toDisplayString(val) {
         return val == null ? "" : isObject(val) ? JSON.stringify(val, replacer, 2) : String(val);
      };

      var replacer = function replacer(_key, val) {
         if (isMap(val)) {
            return babelHelpers.defineProperty({}, "Map(".concat(val.size, ")"), babelHelpers.toConsumableArray(val.entries()).reduce(function (entries, _ref) {
               var _ref2 = babelHelpers.slicedToArray(_ref, 2),
                  key = _ref2[0],
                  val2 = _ref2[1];

               entries["".concat(key, " =>")] = val2;
               return entries;
            }, {}));
         } else if (isSet(val)) {
            return babelHelpers.defineProperty({}, "Set(".concat(val.size, ")"), babelHelpers.toConsumableArray(val.values()));
         } else if (isObject(val) && !isArray(val) && !isPlainObject(val)) {
            return String(val);
         }

         return val;
      };

      var babelParserDefaultPlugins = ["bigInt", "optionalChaining", "nullishCoalescingOperator"];
      var EMPTY_OBJ = Object.freeze({});
      var EMPTY_ARR = Object.freeze([]);

      var NOOP = function NOOP() {};

      var NO = function NO() {
         return false;
      };

      var onRE = /^on[^a-z]/;

      var isOn = function isOn(key) {
         return onRE.test(key);
      };

      var isModelListener = function isModelListener(key) {
         return key.startsWith("onUpdate:");
      };

      var extend = Object.assign;

      var remove = function remove(arr, el) {
         var i = arr.indexOf(el);

         if (i > -1) {
            arr.splice(i, 1);
         }
      };

      var hasOwnProperty = Object.prototype.hasOwnProperty;

      var hasOwn = function hasOwn(val, key) {
         return hasOwnProperty.call(val, key);
      };

      var isArray = Array.isArray;

      var isMap = function isMap(val) {
         return toTypeString(val) === "[object Map]";
      };

      var isSet = function isSet(val) {
         return toTypeString(val) === "[object Set]";
      };

      var isDate = function isDate(val) {
         return val instanceof Date;
      };

      var isFunction = function isFunction(val) {
         return typeof val === "function";
      };

      var isString = function isString(val) {
         return typeof val === "string";
      };

      var isSymbol = function isSymbol(val) {
         return babelHelpers.typeof(val) === "symbol";
      };

      var isObject = function isObject(val) {
         return val !== null && babelHelpers.typeof(val) === "object";
      };

      var isPromise = function isPromise(val) {
         return isObject(val) && isFunction(val.then) && isFunction(val.catch);
      };

      var objectToString = Object.prototype.toString;

      var toTypeString = function toTypeString(value) {
         return objectToString.call(value);
      };

      var toRawType = function toRawType(value) {
         return toTypeString(value).slice(8, -1);
      };

      var isPlainObject = function isPlainObject(val) {
         return toTypeString(val) === "[object Object]";
      };

      var isIntegerKey = function isIntegerKey(key) {
         return isString(key) && key !== "NaN" && key[0] !== "-" && "" + parseInt(key, 10) === key;
      };

      var isReservedProp = /* @__PURE__ */makeMap(",key,ref,onVnodeBeforeMount,onVnodeMounted,onVnodeBeforeUpdate,onVnodeUpdated,onVnodeBeforeUnmount,onVnodeUnmounted");

      var cacheStringFunction = function cacheStringFunction(fn) {
         var cache = Object.create(null);
         return function (str) {
            var hit = cache[str];
            return hit || (cache[str] = fn(str));
         };
      };

      var camelizeRE = /-(\w)/g;
      var camelize = cacheStringFunction(function (str) {
         return str.replace(camelizeRE, function (_, c) {
            return c ? c.toUpperCase() : "";
         });
      });
      var hyphenateRE = /\B([A-Z])/g;
      var hyphenate = cacheStringFunction(function (str) {
         return str.replace(hyphenateRE, "-$1").toLowerCase();
      });
      var capitalize = cacheStringFunction(function (str) {
         return str.charAt(0).toUpperCase() + str.slice(1);
      });
      var toHandlerKey = cacheStringFunction(function (str) {
         return str ? "on".concat(capitalize(str)) : "";
      });

      var hasChanged = function hasChanged(value, oldValue) {
         return value !== oldValue && (value === value || oldValue === oldValue);
      };

      var invokeArrayFns = function invokeArrayFns(fns, arg) {
         for (var i = 0; i < fns.length; i++) {
            fns[i](arg);
         }
      };

      var def = function def(obj, key, value) {
         Object.defineProperty(obj, key, {
            configurable: true,
            enumerable: false,
            value: value
         });
      };

      var toNumber = function toNumber(val) {
         var n = parseFloat(val);
         return isNaN(n) ? val : n;
      };

      var _globalThis;

      var getGlobalThis = function getGlobalThis() {
         return _globalThis || (_globalThis = typeof globalThis !== "undefined" ? globalThis : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : typeof global !== "undefined" ? global : {});
      };

      exports.EMPTY_ARR = EMPTY_ARR;
      exports.EMPTY_OBJ = EMPTY_OBJ;
      exports.NO = NO;
      exports.NOOP = NOOP;
      exports.PatchFlagNames = PatchFlagNames;
      exports.babelParserDefaultPlugins = babelParserDefaultPlugins;
      exports.camelize = camelize;
      exports.capitalize = capitalize;
      exports.def = def;
      exports.escapeHtml = escapeHtml;
      exports.escapeHtmlComment = escapeHtmlComment;
      exports.extend = extend;
      exports.generateCodeFrame = generateCodeFrame;
      exports.getGlobalThis = getGlobalThis;
      exports.hasChanged = hasChanged;
      exports.hasOwn = hasOwn;
      exports.hyphenate = hyphenate;
      exports.invokeArrayFns = invokeArrayFns;
      exports.isArray = isArray;
      exports.isBooleanAttr = isBooleanAttr2;
      exports.isDate = isDate;
      exports.isFunction = isFunction;
      exports.isGloballyWhitelisted = isGloballyWhitelisted;
      exports.isHTMLTag = isHTMLTag;
      exports.isIntegerKey = isIntegerKey;
      exports.isKnownAttr = isKnownAttr;
      exports.isMap = isMap;
      exports.isModelListener = isModelListener;
      exports.isNoUnitNumericStyleProp = isNoUnitNumericStyleProp;
      exports.isObject = isObject;
      exports.isOn = isOn;
      exports.isPlainObject = isPlainObject;
      exports.isPromise = isPromise;
      exports.isReservedProp = isReservedProp;
      exports.isSSRSafeAttrName = isSSRSafeAttrName;
      exports.isSVGTag = isSVGTag;
      exports.isSet = isSet;
      exports.isSpecialBooleanAttr = isSpecialBooleanAttr;
      exports.isString = isString;
      exports.isSymbol = isSymbol;
      exports.isVoidTag = isVoidTag;
      exports.looseEqual = looseEqual;
      exports.looseIndexOf = looseIndexOf;
      exports.makeMap = makeMap;
      exports.normalizeClass = normalizeClass;
      exports.normalizeStyle = normalizeStyle;
      exports.objectToString = objectToString;
      exports.parseStringStyle = parseStringStyle;
      exports.propsToAttrMap = propsToAttrMap;
      exports.remove = remove;
      exports.slotFlagsText = slotFlagsText;
      exports.stringifyStyle = stringifyStyle;
      exports.toDisplayString = toDisplayString;
      exports.toHandlerKey = toHandlerKey;
      exports.toNumber = toNumber;
      exports.toRawType = toRawType;
      exports.toTypeString = toTypeString;
   }); // node_modules/@vue/shared/index.js


   var require_shared = __commonJS(function (exports, module) {

      {
         module.exports = require_shared_cjs();
      }
   }); // node_modules/@vue/reactivity/dist/reactivity.cjs.js


   var require_reactivity_cjs = __commonJS(function (exports) {

      Object.defineProperty(exports, "__esModule", {
         value: true
      });
      var shared = require_shared();
      var targetMap = new WeakMap();
      var effectStack = [];
      var activeEffect;
      var ITERATE_KEY = Symbol("iterate");
      var MAP_KEY_ITERATE_KEY = Symbol("Map key iterate");

      function isEffect(fn) {
         return fn && fn._isEffect === true;
      }

      function effect3(fn) {
         var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : shared.EMPTY_OBJ;

         if (isEffect(fn)) {
            fn = fn.raw;
         }

         var effect4 = createReactiveEffect(fn, options);

         if (!options.lazy) {
            effect4();
         }

         return effect4;
      }

      function stop2(effect4) {
         if (effect4.active) {
            cleanup(effect4);

            if (effect4.options.onStop) {
               effect4.options.onStop();
            }

            effect4.active = false;
         }
      }

      var uid = 0;

      function createReactiveEffect(fn, options) {
         var effect4 = function reactiveEffect() {
            if (!effect4.active) {
               return fn();
            }

            if (!effectStack.includes(effect4)) {
               cleanup(effect4);

               try {
                  enableTracking();
                  effectStack.push(effect4);
                  activeEffect = effect4;
                  return fn();
               } finally {
                  effectStack.pop();
                  resetTracking();
                  activeEffect = effectStack[effectStack.length - 1];
               }
            }
         };

         effect4.id = uid++;
         effect4.allowRecurse = !!options.allowRecurse;
         effect4._isEffect = true;
         effect4.active = true;
         effect4.raw = fn;
         effect4.deps = [];
         effect4.options = options;
         return effect4;
      }

      function cleanup(effect4) {
         var deps = effect4.deps;

         if (deps.length) {
            for (var i = 0; i < deps.length; i++) {
               deps[i].delete(effect4);
            }

            deps.length = 0;
         }
      }

      var shouldTrack = true;
      var trackStack = [];

      function pauseTracking() {
         trackStack.push(shouldTrack);
         shouldTrack = false;
      }

      function enableTracking() {
         trackStack.push(shouldTrack);
         shouldTrack = true;
      }

      function resetTracking() {
         var last = trackStack.pop();
         shouldTrack = last === void 0 ? true : last;
      }

      function track(target, type, key) {
         if (!shouldTrack || activeEffect === void 0) {
            return;
         }

         var depsMap = targetMap.get(target);

         if (!depsMap) {
            targetMap.set(target, depsMap = new Map());
         }

         var dep = depsMap.get(key);

         if (!dep) {
            depsMap.set(key, dep = new Set());
         }

         if (!dep.has(activeEffect)) {
            dep.add(activeEffect);
            activeEffect.deps.push(dep);

            if (activeEffect.options.onTrack) {
               activeEffect.options.onTrack({
                  effect: activeEffect,
                  target: target,
                  type: type,
                  key: key
               });
            }
         }
      }

      function trigger(target, type, key, newValue, oldValue, oldTarget) {
         var depsMap = targetMap.get(target);

         if (!depsMap) {
            return;
         }

         var effects = new Set();

         var add2 = function add2(effectsToAdd) {
            if (effectsToAdd) {
               effectsToAdd.forEach(function (effect4) {
                  if (effect4 !== activeEffect || effect4.allowRecurse) {
                     effects.add(effect4);
                  }
               });
            }
         };

         if (type === "clear") {
            depsMap.forEach(add2);
         } else if (key === "length" && shared.isArray(target)) {
            depsMap.forEach(function (dep, key2) {
               if (key2 === "length" || key2 >= newValue) {
                  add2(dep);
               }
            });
         } else {
            if (key !== void 0) {
               add2(depsMap.get(key));
            }

            switch (type) {
               case "add":
                  if (!shared.isArray(target)) {
                     add2(depsMap.get(ITERATE_KEY));

                     if (shared.isMap(target)) {
                        add2(depsMap.get(MAP_KEY_ITERATE_KEY));
                     }
                  } else if (shared.isIntegerKey(key)) {
                     add2(depsMap.get("length"));
                  }

                  break;

               case "delete":
                  if (!shared.isArray(target)) {
                     add2(depsMap.get(ITERATE_KEY));

                     if (shared.isMap(target)) {
                        add2(depsMap.get(MAP_KEY_ITERATE_KEY));
                     }
                  }

                  break;

               case "set":
                  if (shared.isMap(target)) {
                     add2(depsMap.get(ITERATE_KEY));
                  }

                  break;
            }
         }

         var run = function run(effect4) {
            if (effect4.options.onTrigger) {
               effect4.options.onTrigger({
                  effect: effect4,
                  target: target,
                  key: key,
                  type: type,
                  newValue: newValue,
                  oldValue: oldValue,
                  oldTarget: oldTarget
               });
            }

            if (effect4.options.scheduler) {
               effect4.options.scheduler(effect4);
            } else {
               effect4();
            }
         };

         effects.forEach(run);
      }

      var isNonTrackableKeys = /* @__PURE__ */shared.makeMap("__proto__,__v_isRef,__isVue");
      var builtInSymbols = new Set(Object.getOwnPropertyNames(Symbol).map(function (key) {
         return Symbol[key];
      }).filter(shared.isSymbol));
      var get2 = /* @__PURE__ */createGetter();
      var shallowGet = /* @__PURE__ */createGetter(false, true);
      var readonlyGet = /* @__PURE__ */createGetter(true);
      var shallowReadonlyGet = /* @__PURE__ */createGetter(true, true);
      var arrayInstrumentations = {};
      ["includes", "indexOf", "lastIndexOf"].forEach(function (key) {
         var method = Array.prototype[key];

         arrayInstrumentations[key] = function () {
            var arr = toRaw2(this);

            for (var i = 0, l = this.length; i < l; i++) {
               track(arr, "get", i + "");
            }

            for (var _len = arguments.length, args = new Array(_len), _key2 = 0; _key2 < _len; _key2++) {
               args[_key2] = arguments[_key2];
            }

            var res = method.apply(arr, args);

            if (res === -1 || res === false) {
               return method.apply(arr, args.map(toRaw2));
            } else {
               return res;
            }
         };
      });
      ["push", "pop", "shift", "unshift", "splice"].forEach(function (key) {
         var method = Array.prototype[key];

         arrayInstrumentations[key] = function () {
            pauseTracking();

            for (var _len2 = arguments.length, args = new Array(_len2), _key3 = 0; _key3 < _len2; _key3++) {
               args[_key3] = arguments[_key3];
            }

            var res = method.apply(this, args);
            resetTracking();
            return res;
         };
      });

      function createGetter() {
         var isReadonly2 = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
         var shallow = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
         return function get3(target, key, receiver) {
            if (key === "__v_isReactive") {
               return !isReadonly2;
            } else if (key === "__v_isReadonly") {
               return isReadonly2;
            } else if (key === "__v_raw" && receiver === (isReadonly2 ? shallow ? shallowReadonlyMap : readonlyMap : shallow ? shallowReactiveMap : reactiveMap).get(target)) {
               return target;
            }

            var targetIsArray = shared.isArray(target);

            if (!isReadonly2 && targetIsArray && shared.hasOwn(arrayInstrumentations, key)) {
               return Reflect.get(arrayInstrumentations, key, receiver);
            }

            var res = Reflect.get(target, key, receiver);

            if (shared.isSymbol(key) ? builtInSymbols.has(key) : isNonTrackableKeys(key)) {
               return res;
            }

            if (!isReadonly2) {
               track(target, "get", key);
            }

            if (shallow) {
               return res;
            }

            if (isRef(res)) {
               var shouldUnwrap = !targetIsArray || !shared.isIntegerKey(key);
               return shouldUnwrap ? res.value : res;
            }

            if (shared.isObject(res)) {
               return isReadonly2 ? readonly(res) : reactive3(res);
            }

            return res;
         };
      }

      var set2 = /* @__PURE__ */createSetter();
      var shallowSet = /* @__PURE__ */createSetter(true);

      function createSetter() {
         var shallow = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
         return function set3(target, key, value, receiver) {
            var oldValue = target[key];

            if (!shallow) {
               value = toRaw2(value);
               oldValue = toRaw2(oldValue);

               if (!shared.isArray(target) && isRef(oldValue) && !isRef(value)) {
                  oldValue.value = value;
                  return true;
               }
            }

            var hadKey = shared.isArray(target) && shared.isIntegerKey(key) ? Number(key) < target.length : shared.hasOwn(target, key);
            var result = Reflect.set(target, key, value, receiver);

            if (target === toRaw2(receiver)) {
               if (!hadKey) {
                  trigger(target, "add", key, value);
               } else if (shared.hasChanged(value, oldValue)) {
                  trigger(target, "set", key, value, oldValue);
               }
            }

            return result;
         };
      }

      function deleteProperty(target, key) {
         var hadKey = shared.hasOwn(target, key);
         var oldValue = target[key];
         var result = Reflect.deleteProperty(target, key);

         if (result && hadKey) {
            trigger(target, "delete", key, void 0, oldValue);
         }

         return result;
      }

      function has(target, key) {
         var result = Reflect.has(target, key);

         if (!shared.isSymbol(key) || !builtInSymbols.has(key)) {
            track(target, "has", key);
         }

         return result;
      }

      function ownKeys(target) {
         track(target, "iterate", shared.isArray(target) ? "length" : ITERATE_KEY);
         return Reflect.ownKeys(target);
      }

      var mutableHandlers = {
         get: get2,
         set: set2,
         deleteProperty: deleteProperty,
         has: has,
         ownKeys: ownKeys
      };
      var readonlyHandlers = {
         get: readonlyGet,
         set: function set(target, key) {
            {
               console.warn("Set operation on key \"".concat(String(key), "\" failed: target is readonly."), target);
            }
            return true;
         },
         deleteProperty: function deleteProperty(target, key) {
            {
               console.warn("Delete operation on key \"".concat(String(key), "\" failed: target is readonly."), target);
            }
            return true;
         }
      };
      var shallowReactiveHandlers = shared.extend({}, mutableHandlers, {
         get: shallowGet,
         set: shallowSet
      });
      var shallowReadonlyHandlers = shared.extend({}, readonlyHandlers, {
         get: shallowReadonlyGet
      });

      var toReactive = function toReactive(value) {
         return shared.isObject(value) ? reactive3(value) : value;
      };

      var toReadonly = function toReadonly(value) {
         return shared.isObject(value) ? readonly(value) : value;
      };

      var toShallow = function toShallow(value) {
         return value;
      };

      var getProto = function getProto(v) {
         return Reflect.getPrototypeOf(v);
      };

      function get$1(target, key) {
         var isReadonly2 = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
         var isShallow = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : false;
         target = target["__v_raw"];
         var rawTarget = toRaw2(target);
         var rawKey = toRaw2(key);

         if (key !== rawKey) {
            !isReadonly2 && track(rawTarget, "get", key);
         }

         !isReadonly2 && track(rawTarget, "get", rawKey);

         var _getProto = getProto(rawTarget),
            has2 = _getProto.has;

         var wrap = isShallow ? toShallow : isReadonly2 ? toReadonly : toReactive;

         if (has2.call(rawTarget, key)) {
            return wrap(target.get(key));
         } else if (has2.call(rawTarget, rawKey)) {
            return wrap(target.get(rawKey));
         } else if (target !== rawTarget) {
            target.get(key);
         }
      }

      function has$1(key) {
         var isReadonly2 = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
         var target = this["__v_raw"];
         var rawTarget = toRaw2(target);
         var rawKey = toRaw2(key);

         if (key !== rawKey) {
            !isReadonly2 && track(rawTarget, "has", key);
         }

         !isReadonly2 && track(rawTarget, "has", rawKey);
         return key === rawKey ? target.has(key) : target.has(key) || target.has(rawKey);
      }

      function size(target) {
         var isReadonly2 = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
         target = target["__v_raw"];
         !isReadonly2 && track(toRaw2(target), "iterate", ITERATE_KEY);
         return Reflect.get(target, "size", target);
      }

      function add(value) {
         value = toRaw2(value);
         var target = toRaw2(this);
         var proto = getProto(target);
         var hadKey = proto.has.call(target, value);

         if (!hadKey) {
            target.add(value);
            trigger(target, "add", value, value);
         }

         return this;
      }

      function set$1(key, value) {
         value = toRaw2(value);
         var target = toRaw2(this);

         var _getProto2 = getProto(target),
            has2 = _getProto2.has,
            get3 = _getProto2.get;

         var hadKey = has2.call(target, key);

         if (!hadKey) {
            key = toRaw2(key);
            hadKey = has2.call(target, key);
         } else {
            checkIdentityKeys(target, has2, key);
         }

         var oldValue = get3.call(target, key);
         target.set(key, value);

         if (!hadKey) {
            trigger(target, "add", key, value);
         } else if (shared.hasChanged(value, oldValue)) {
            trigger(target, "set", key, value, oldValue);
         }

         return this;
      }

      function deleteEntry(key) {
         var target = toRaw2(this);

         var _getProto3 = getProto(target),
            has2 = _getProto3.has,
            get3 = _getProto3.get;

         var hadKey = has2.call(target, key);

         if (!hadKey) {
            key = toRaw2(key);
            hadKey = has2.call(target, key);
         } else {
            checkIdentityKeys(target, has2, key);
         }

         var oldValue = get3 ? get3.call(target, key) : void 0;
         var result = target.delete(key);

         if (hadKey) {
            trigger(target, "delete", key, void 0, oldValue);
         }

         return result;
      }

      function clear() {
         var target = toRaw2(this);
         var hadItems = target.size !== 0;
         var oldTarget = shared.isMap(target) ? new Map(target) : new Set(target);
         var result = target.clear();

         if (hadItems) {
            trigger(target, "clear", void 0, void 0, oldTarget);
         }

         return result;
      }

      function createForEach(isReadonly2, isShallow) {
         return function forEach(callback, thisArg) {
            var observed = this;
            var target = observed["__v_raw"];
            var rawTarget = toRaw2(target);
            var wrap = isShallow ? toShallow : isReadonly2 ? toReadonly : toReactive;
            !isReadonly2 && track(rawTarget, "iterate", ITERATE_KEY);
            return target.forEach(function (value, key) {
               return callback.call(thisArg, wrap(value), wrap(key), observed);
            });
         };
      }

      function createIterableMethod(method, isReadonly2, isShallow) {
         return function () {
            var target = this["__v_raw"];
            var rawTarget = toRaw2(target);
            var targetIsMap = shared.isMap(rawTarget);
            var isPair = method === "entries" || method === Symbol.iterator && targetIsMap;
            var isKeyOnly = method === "keys" && targetIsMap;
            var innerIterator = target[method].apply(target, arguments);
            var wrap = isShallow ? toShallow : isReadonly2 ? toReadonly : toReactive;
            !isReadonly2 && track(rawTarget, "iterate", isKeyOnly ? MAP_KEY_ITERATE_KEY : ITERATE_KEY);
            return babelHelpers.defineProperty({
               next: function next() {
                  var _innerIterator$next = innerIterator.next(),
                     value = _innerIterator$next.value,
                     done = _innerIterator$next.done;

                  return done ? {
                     value: value,
                     done: done
                  } : {
                     value: isPair ? [wrap(value[0]), wrap(value[1])] : wrap(value),
                     done: done
                  };
               }
            }, Symbol.iterator, function () {
               return this;
            });
         };
      }

      function createReadonlyMethod(type) {
         return function () {
            {
               var key = (arguments.length <= 0 ? undefined : arguments[0]) ? "on key \"".concat(arguments.length <= 0 ? undefined : arguments[0], "\" ") : "";
               console.warn("".concat(shared.capitalize(type), " operation ").concat(key, "failed: target is readonly."), toRaw2(this));
            }
            return type === "delete" ? false : this;
         };
      }

      var mutableInstrumentations = {
         get: function get(key) {
            return get$1(this, key);
         },

         get size() {
            return size(this);
         },

         has: has$1,
         add: add,
         set: set$1,
         delete: deleteEntry,
         clear: clear,
         forEach: createForEach(false, false)
      };
      var shallowInstrumentations = {
         get: function get(key) {
            return get$1(this, key, false, true);
         },

         get size() {
            return size(this);
         },

         has: has$1,
         add: add,
         set: set$1,
         delete: deleteEntry,
         clear: clear,
         forEach: createForEach(false, true)
      };
      var readonlyInstrumentations = {
         get: function get(key) {
            return get$1(this, key, true);
         },

         get size() {
            return size(this, true);
         },

         has: function has(key) {
            return has$1.call(this, key, true);
         },
         add: createReadonlyMethod("add"),
         set: createReadonlyMethod("set"),
         delete: createReadonlyMethod("delete"),
         clear: createReadonlyMethod("clear"),
         forEach: createForEach(true, false)
      };
      var shallowReadonlyInstrumentations = {
         get: function get(key) {
            return get$1(this, key, true, true);
         },

         get size() {
            return size(this, true);
         },

         has: function has(key) {
            return has$1.call(this, key, true);
         },
         add: createReadonlyMethod("add"),
         set: createReadonlyMethod("set"),
         delete: createReadonlyMethod("delete"),
         clear: createReadonlyMethod("clear"),
         forEach: createForEach(true, true)
      };
      var iteratorMethods = ["keys", "values", "entries", Symbol.iterator];
      iteratorMethods.forEach(function (method) {
         mutableInstrumentations[method] = createIterableMethod(method, false, false);
         readonlyInstrumentations[method] = createIterableMethod(method, true, false);
         shallowInstrumentations[method] = createIterableMethod(method, false, true);
         shallowReadonlyInstrumentations[method] = createIterableMethod(method, true, true);
      });

      function createInstrumentationGetter(isReadonly2, shallow) {
         var instrumentations = shallow ? isReadonly2 ? shallowReadonlyInstrumentations : shallowInstrumentations : isReadonly2 ? readonlyInstrumentations : mutableInstrumentations;
         return function (target, key, receiver) {
            if (key === "__v_isReactive") {
               return !isReadonly2;
            } else if (key === "__v_isReadonly") {
               return isReadonly2;
            } else if (key === "__v_raw") {
               return target;
            }

            return Reflect.get(shared.hasOwn(instrumentations, key) && key in target ? instrumentations : target, key, receiver);
         };
      }

      var mutableCollectionHandlers = {
         get: createInstrumentationGetter(false, false)
      };
      var shallowCollectionHandlers = {
         get: createInstrumentationGetter(false, true)
      };
      var readonlyCollectionHandlers = {
         get: createInstrumentationGetter(true, false)
      };
      var shallowReadonlyCollectionHandlers = {
         get: createInstrumentationGetter(true, true)
      };

      function checkIdentityKeys(target, has2, key) {
         var rawKey = toRaw2(key);

         if (rawKey !== key && has2.call(target, rawKey)) {
            var type = shared.toRawType(target);
            console.warn("Reactive ".concat(type, " contains both the raw and reactive versions of the same object").concat(type === "Map" ? " as keys" : "", ", which can lead to inconsistencies. Avoid differentiating between the raw and reactive versions of an object and only use the reactive version if possible."));
         }
      }

      var reactiveMap = new WeakMap();
      var shallowReactiveMap = new WeakMap();
      var readonlyMap = new WeakMap();
      var shallowReadonlyMap = new WeakMap();

      function targetTypeMap(rawType) {
         switch (rawType) {
            case "Object":
            case "Array":
               return 1;

            case "Map":
            case "Set":
            case "WeakMap":
            case "WeakSet":
               return 2;

            default:
               return 0;
         }
      }

      function getTargetType(value) {
         return value["__v_skip"] || !Object.isExtensible(value) ? 0 : targetTypeMap(shared.toRawType(value));
      }

      function reactive3(target) {
         if (target && target["__v_isReadonly"]) {
            return target;
         }

         return createReactiveObject(target, false, mutableHandlers, mutableCollectionHandlers, reactiveMap);
      }

      function shallowReactive(target) {
         return createReactiveObject(target, false, shallowReactiveHandlers, shallowCollectionHandlers, shallowReactiveMap);
      }

      function readonly(target) {
         return createReactiveObject(target, true, readonlyHandlers, readonlyCollectionHandlers, readonlyMap);
      }

      function shallowReadonly(target) {
         return createReactiveObject(target, true, shallowReadonlyHandlers, shallowReadonlyCollectionHandlers, shallowReadonlyMap);
      }

      function createReactiveObject(target, isReadonly2, baseHandlers, collectionHandlers, proxyMap) {
         if (!shared.isObject(target)) {
            {
               console.warn("value cannot be made reactive: ".concat(String(target)));
            }
            return target;
         }

         if (target["__v_raw"] && !(isReadonly2 && target["__v_isReactive"])) {
            return target;
         }

         var existingProxy = proxyMap.get(target);

         if (existingProxy) {
            return existingProxy;
         }

         var targetType = getTargetType(target);

         if (targetType === 0) {
            return target;
         }

         var proxy = new Proxy(target, targetType === 2 ? collectionHandlers : baseHandlers);
         proxyMap.set(target, proxy);
         return proxy;
      }

      function isReactive2(value) {
         if (isReadonly(value)) {
            return isReactive2(value["__v_raw"]);
         }

         return !!(value && value["__v_isReactive"]);
      }

      function isReadonly(value) {
         return !!(value && value["__v_isReadonly"]);
      }

      function isProxy(value) {
         return isReactive2(value) || isReadonly(value);
      }

      function toRaw2(observed) {
         return observed && toRaw2(observed["__v_raw"]) || observed;
      }

      function markRaw(value) {
         shared.def(value, "__v_skip", true);
         return value;
      }

      var convert = function convert(val) {
         return shared.isObject(val) ? reactive3(val) : val;
      };

      function isRef(r) {
         return Boolean(r && r.__v_isRef === true);
      }

      function ref(value) {
         return createRef(value);
      }

      function shallowRef(value) {
         return createRef(value, true);
      }

      var RefImpl = /*#__PURE__*/function () {
         function RefImpl(_rawValue) {
            var _shallow = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

            babelHelpers.classCallCheck(this, RefImpl);
            this._rawValue = _rawValue;
            this._shallow = _shallow;
            this.__v_isRef = true;
            this._value = _shallow ? _rawValue : convert(_rawValue);
         }

         babelHelpers.createClass(RefImpl, [{
            key: "value",
            get: function get() {
               track(toRaw2(this), "get", "value");
               return this._value;
            },
            set: function set(newVal) {
               if (shared.hasChanged(toRaw2(newVal), this._rawValue)) {
                  this._rawValue = newVal;
                  this._value = this._shallow ? newVal : convert(newVal);
                  trigger(toRaw2(this), "set", "value", newVal);
               }
            }
         }]);
         return RefImpl;
      }();

      function createRef(rawValue) {
         var shallow = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

         if (isRef(rawValue)) {
            return rawValue;
         }

         return new RefImpl(rawValue, shallow);
      }

      function triggerRef(ref2) {
         trigger(toRaw2(ref2), "set", "value", ref2.value);
      }

      function unref(ref2) {
         return isRef(ref2) ? ref2.value : ref2;
      }

      var shallowUnwrapHandlers = {
         get: function get(target, key, receiver) {
            return unref(Reflect.get(target, key, receiver));
         },
         set: function set(target, key, value, receiver) {
            var oldValue = target[key];

            if (isRef(oldValue) && !isRef(value)) {
               oldValue.value = value;
               return true;
            } else {
               return Reflect.set(target, key, value, receiver);
            }
         }
      };

      function proxyRefs(objectWithRefs) {
         return isReactive2(objectWithRefs) ? objectWithRefs : new Proxy(objectWithRefs, shallowUnwrapHandlers);
      }

      var CustomRefImpl = /*#__PURE__*/function () {
         function CustomRefImpl(factory) {
            var _this = this;

            babelHelpers.classCallCheck(this, CustomRefImpl);
            this.__v_isRef = true;

            var _factory = factory(function () {
                  return track(_this, "get", "value");
               }, function () {
                  return trigger(_this, "set", "value");
               }),
               get3 = _factory.get,
               set3 = _factory.set;

            this._get = get3;
            this._set = set3;
         }

         babelHelpers.createClass(CustomRefImpl, [{
            key: "value",
            get: function get() {
               return this._get();
            },
            set: function set(newVal) {
               this._set(newVal);
            }
         }]);
         return CustomRefImpl;
      }();

      function customRef(factory) {
         return new CustomRefImpl(factory);
      }

      function toRefs(object) {
         if (!isProxy(object)) {
            console.warn("toRefs() expects a reactive object but received a plain one.");
         }

         var ret = shared.isArray(object) ? new Array(object.length) : {};

         for (var key in object) {
            ret[key] = toRef(object, key);
         }

         return ret;
      }

      var ObjectRefImpl = /*#__PURE__*/function () {
         function ObjectRefImpl(_object, _key) {
            babelHelpers.classCallCheck(this, ObjectRefImpl);
            this._object = _object;
            this._key = _key;
            this.__v_isRef = true;
         }

         babelHelpers.createClass(ObjectRefImpl, [{
            key: "value",
            get: function get() {
               return this._object[this._key];
            },
            set: function set(newVal) {
               this._object[this._key] = newVal;
            }
         }]);
         return ObjectRefImpl;
      }();

      function toRef(object, key) {
         return isRef(object[key]) ? object[key] : new ObjectRefImpl(object, key);
      }

      var ComputedRefImpl = /*#__PURE__*/function () {
         function ComputedRefImpl(getter, _setter, isReadonly2) {
            var _this2 = this;

            babelHelpers.classCallCheck(this, ComputedRefImpl);
            this._setter = _setter;
            this._dirty = true;
            this.__v_isRef = true;
            this.effect = effect3(getter, {
               lazy: true,
               scheduler: function scheduler() {
                  if (!_this2._dirty) {
                     _this2._dirty = true;
                     trigger(toRaw2(_this2), "set", "value");
                  }
               }
            });
            this["__v_isReadonly"] = isReadonly2;
         }

         babelHelpers.createClass(ComputedRefImpl, [{
            key: "value",
            get: function get() {
               var self2 = toRaw2(this);

               if (self2._dirty) {
                  self2._value = this.effect();
                  self2._dirty = false;
               }

               track(self2, "get", "value");
               return self2._value;
            },
            set: function set(newValue) {
               this._setter(newValue);
            }
         }]);
         return ComputedRefImpl;
      }();

      function computed(getterOrOptions) {
         var getter;
         var setter;

         if (shared.isFunction(getterOrOptions)) {
            getter = getterOrOptions;

            setter = function setter() {
               console.warn("Write operation failed: computed value is readonly");
            };
         } else {
            getter = getterOrOptions.get;
            setter = getterOrOptions.set;
         }

         return new ComputedRefImpl(getter, setter, shared.isFunction(getterOrOptions) || !getterOrOptions.set);
      }

      exports.ITERATE_KEY = ITERATE_KEY;
      exports.computed = computed;
      exports.customRef = customRef;
      exports.effect = effect3;
      exports.enableTracking = enableTracking;
      exports.isProxy = isProxy;
      exports.isReactive = isReactive2;
      exports.isReadonly = isReadonly;
      exports.isRef = isRef;
      exports.markRaw = markRaw;
      exports.pauseTracking = pauseTracking;
      exports.proxyRefs = proxyRefs;
      exports.reactive = reactive3;
      exports.readonly = readonly;
      exports.ref = ref;
      exports.resetTracking = resetTracking;
      exports.shallowReactive = shallowReactive;
      exports.shallowReadonly = shallowReadonly;
      exports.shallowRef = shallowRef;
      exports.stop = stop2;
      exports.toRaw = toRaw2;
      exports.toRef = toRef;
      exports.toRefs = toRefs;
      exports.track = track;
      exports.trigger = trigger;
      exports.triggerRef = triggerRef;
      exports.unref = unref;
   }); // node_modules/@vue/reactivity/index.js


   var require_reactivity = __commonJS(function (exports, module) {

      {
         module.exports = require_reactivity_cjs();
      }
   }); // packages/alpinejs/src/scheduler.js


   var flushPending = false;
   var flushing = false;
   var queue = [];

   function _scheduler(callback) {
      queueJob(callback);
   }

   function queueJob(job) {
      if (!queue.includes(job)) queue.push(job);
      queueFlush();
   }

   function queueFlush() {
      if (!flushing && !flushPending) {
         flushPending = true;
         queueMicrotask(flushJobs);
      }
   }

   function flushJobs() {
      flushPending = false;
      flushing = true;

      for (var i = 0; i < queue.length; i++) {
         queue[i]();
      }

      queue.length = 0;
      flushing = false;
   } // packages/alpinejs/src/reactivity.js


   var reactive;
   var effect;
   var release;
   var raw;
   var shouldSchedule = true;

   function disableEffectScheduling(callback) {
      shouldSchedule = false;
      callback();
      shouldSchedule = true;
   }

   function setReactivityEngine(engine) {
      reactive = engine.reactive;
      release = engine.release;

      effect = function effect(callback) {
         return engine.effect(callback, {
            scheduler: function scheduler(task) {
               if (shouldSchedule) {
                  _scheduler(task);
               } else {
                  task();
               }
            }
         });
      };

      raw = engine.raw;
   }

   function overrideEffect(override) {
      effect = override;
   }

   function elementBoundEffect(el) {
      var cleanup = function cleanup() {};

      var wrappedEffect = function wrappedEffect(callback) {
         var effectReference = effect(callback);

         if (!el._x_effects) {
            el._x_effects = new Set();

            el._x_runEffects = function () {
               el._x_effects.forEach(function (i) {
                  return i();
               });
            };
         }

         el._x_effects.add(effectReference);

         cleanup = function cleanup() {
            if (effectReference === void 0) return;

            el._x_effects.delete(effectReference);

            release(effectReference);
         };
      };

      return [wrappedEffect, function () {
         cleanup();
      }];
   } // packages/alpinejs/src/mutation.js


   var onAttributeAddeds = [];
   var onElRemoveds = [];
   var onElAddeds = [];

   function onElAdded(callback) {
      onElAddeds.push(callback);
   }

   function onElRemoved(callback) {
      onElRemoveds.push(callback);
   }

   function onAttributesAdded(callback) {
      onAttributeAddeds.push(callback);
   }

   function onAttributeRemoved(el, name, callback) {
      if (!el._x_attributeCleanups) el._x_attributeCleanups = {};
      if (!el._x_attributeCleanups[name]) el._x_attributeCleanups[name] = [];

      el._x_attributeCleanups[name].push(callback);
   }

   function cleanupAttributes(el, names) {
      if (!el._x_attributeCleanups) return;
      Object.entries(el._x_attributeCleanups).forEach(function (_ref6) {
         var _ref7 = babelHelpers.slicedToArray(_ref6, 2),
            name = _ref7[0],
            value = _ref7[1];

         if (names === void 0 || names.includes(name)) {
            value.forEach(function (i) {
               return i();
            });
            delete el._x_attributeCleanups[name];
         }
      });
   }

   var observer = new MutationObserver(onMutate);
   var currentlyObserving = false;

   function startObservingMutations() {
      observer.observe(document, {
         subtree: true,
         childList: true,
         attributes: true,
         attributeOldValue: true
      });
      currentlyObserving = true;
   }

   function stopObservingMutations() {
      flushObserver();
      observer.disconnect();
      currentlyObserving = false;
   }

   var recordQueue = [];
   var willProcessRecordQueue = false;

   function flushObserver() {
      recordQueue = recordQueue.concat(observer.takeRecords());

      if (recordQueue.length && !willProcessRecordQueue) {
         willProcessRecordQueue = true;
         queueMicrotask(function () {
            processRecordQueue();
            willProcessRecordQueue = false;
         });
      }
   }

   function processRecordQueue() {
      onMutate(recordQueue);
      recordQueue.length = 0;
   }

   function mutateDom(callback) {
      if (!currentlyObserving) return callback();
      stopObservingMutations();
      var result = callback();
      startObservingMutations();
      return result;
   }

   var isCollecting = false;
   var deferredMutations = [];

   function deferMutations() {
      isCollecting = true;
   }

   function flushAndStopDeferringMutations() {
      isCollecting = false;
      onMutate(deferredMutations);
      deferredMutations = [];
   }

   function onMutate(mutations) {
      if (isCollecting) {
         deferredMutations = deferredMutations.concat(mutations);
         return;
      }

      var addedNodes = [];
      var removedNodes = [];
      var addedAttributes = new Map();
      var removedAttributes = new Map();

      for (var i = 0; i < mutations.length; i++) {
         if (mutations[i].target._x_ignoreMutationObserver) continue;

         if (mutations[i].type === "childList") {
            mutations[i].addedNodes.forEach(function (node) {
               return node.nodeType === 1 && addedNodes.push(node);
            });
            mutations[i].removedNodes.forEach(function (node) {
               return node.nodeType === 1 && removedNodes.push(node);
            });
         }

         if (mutations[i].type === "attributes") {
            (function () {
               var el = mutations[i].target;
               var name = mutations[i].attributeName;
               var oldValue = mutations[i].oldValue;

               var add = function add() {
                  if (!addedAttributes.has(el)) addedAttributes.set(el, []);
                  addedAttributes.get(el).push({
                     name: name,
                     value: el.getAttribute(name)
                  });
               };

               var remove = function remove() {
                  if (!removedAttributes.has(el)) removedAttributes.set(el, []);
                  removedAttributes.get(el).push(name);
               };

               if (el.hasAttribute(name) && oldValue === null) {
                  add();
               } else if (el.hasAttribute(name)) {
                  remove();
                  add();
               } else {
                  remove();
               }
            })();
         }
      }

      removedAttributes.forEach(function (attrs, el) {
         cleanupAttributes(el, attrs);
      });
      addedAttributes.forEach(function (attrs, el) {
         onAttributeAddeds.forEach(function (i) {
            return i(el, attrs);
         });
      });

      var _loop2 = function _loop2() {
         var node = _addedNodes[_i];
         if (removedNodes.includes(node)) return "continue";
         onElAddeds.forEach(function (i) {
            return i(node);
         });
      };

      for (var _i = 0, _addedNodes = addedNodes; _i < _addedNodes.length; _i++) {
         var _ret = _loop2();

         if (_ret === "continue") continue;
      }

      var _loop3 = function _loop3() {
         var node = _removedNodes[_i2];
         if (addedNodes.includes(node)) return "continue";
         onElRemoveds.forEach(function (i) {
            return i(node);
         });
      };

      for (var _i2 = 0, _removedNodes = removedNodes; _i2 < _removedNodes.length; _i2++) {
         var _ret2 = _loop3();

         if (_ret2 === "continue") continue;
      }

      addedNodes = null;
      removedNodes = null;
      addedAttributes = null;
      removedAttributes = null;
   } // packages/alpinejs/src/scope.js


   function addScopeToNode(node, data2, referenceNode) {
      node._x_dataStack = [data2].concat(babelHelpers.toConsumableArray(closestDataStack(referenceNode || node)));
      return function () {
         node._x_dataStack = node._x_dataStack.filter(function (i) {
            return i !== data2;
         });
      };
   }

   function refreshScope(element, scope) {
      var existingScope = element._x_dataStack[0];
      Object.entries(scope).forEach(function (_ref8) {
         var _ref9 = babelHelpers.slicedToArray(_ref8, 2),
            key = _ref9[0],
            value = _ref9[1];

         existingScope[key] = value;
      });
   }

   function closestDataStack(node) {
      if (node._x_dataStack) return node._x_dataStack;

      if (typeof ShadowRoot === "function" && node instanceof ShadowRoot) {
         return closestDataStack(node.host);
      }

      if (!node.parentNode) {
         return [];
      }

      return closestDataStack(node.parentNode);
   }

   function mergeProxies(objects) {
      var thisProxy = new Proxy({}, {
         ownKeys: function ownKeys() {
            return Array.from(new Set(objects.flatMap(function (i) {
               return Object.keys(i);
            })));
         },
         has: function has(target, name) {
            return objects.some(function (obj) {
               return obj.hasOwnProperty(name);
            });
         },
         get: function get(target, name) {
            return (objects.find(function (obj) {
               if (obj.hasOwnProperty(name)) {
                  var descriptor = Object.getOwnPropertyDescriptor(obj, name);

                  if (descriptor.get && descriptor.get._x_alreadyBound || descriptor.set && descriptor.set._x_alreadyBound) {
                     return true;
                  }

                  if ((descriptor.get || descriptor.set) && descriptor.enumerable) {
                     var getter = descriptor.get;
                     var setter = descriptor.set;
                     var property = descriptor;
                     getter = getter && getter.bind(thisProxy);
                     setter = setter && setter.bind(thisProxy);
                     if (getter) getter._x_alreadyBound = true;
                     if (setter) setter._x_alreadyBound = true;
                     Object.defineProperty(obj, name, babelHelpers.objectSpread({}, property, {
                        get: getter,
                        set: setter
                     }));
                  }

                  return true;
               }

               return false;
            }) || {})[name];
         },
         set: function set(target, name, value) {
            var closestObjectWithKey = objects.find(function (obj) {
               return obj.hasOwnProperty(name);
            });

            if (closestObjectWithKey) {
               closestObjectWithKey[name] = value;
            } else {
               objects[objects.length - 1][name] = value;
            }

            return true;
         }
      });
      return thisProxy;
   } // packages/alpinejs/src/interceptor.js


   function initInterceptors(data2) {
      var isObject = function isObject(val) {
         return babelHelpers.typeof(val) === "object" && !Array.isArray(val) && val !== null;
      };

      var recurse = function recurse(obj) {
         var basePath = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "";
         Object.entries(obj).forEach(function (_ref10) {
            var _ref11 = babelHelpers.slicedToArray(_ref10, 2),
               key = _ref11[0],
               value = _ref11[1];

            var path = basePath === "" ? key : "".concat(basePath, ".").concat(key);

            if (babelHelpers.typeof(value) === "object" && value !== null && value._x_interceptor) {
               obj[key] = value.initialize(data2, path, key);
            } else {
               if (isObject(value) && value !== obj && !(value instanceof Element)) {
                  recurse(value, path);
               }
            }
         });
      };

      return recurse(data2);
   }

   function interceptor(callback) {
      var mutateObj = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : function () {};
      var obj = {
         initialValue: void 0,
         _x_interceptor: true,
         initialize: function initialize(data2, path, key) {
            return callback(this.initialValue, function () {
               return get(data2, path);
            }, function (value) {
               return set$1(data2, path, value);
            }, path, key);
         }
      };
      mutateObj(obj);
      return function (initialValue) {
         if (babelHelpers.typeof(initialValue) === "object" && initialValue !== null && initialValue._x_interceptor) {
            var initialize = obj.initialize.bind(obj);

            obj.initialize = function (data2, path, key) {
               var innerValue = initialValue.initialize(data2, path, key);
               obj.initialValue = innerValue;
               return initialize(data2, path, key);
            };
         } else {
            obj.initialValue = initialValue;
         }

         return obj;
      };
   }

   function get(obj, path) {
      return path.split(".").reduce(function (carry, segment) {
         return carry[segment];
      }, obj);
   }

   function set$1(obj, path, value) {
      if (typeof path === "string") path = path.split(".");
      if (path.length === 1) obj[path[0]] = value;else if (path.length === 0) throw error;else {
         if (obj[path[0]]) return set$1(obj[path[0]], path.slice(1), value);else {
            obj[path[0]] = {};
            return set$1(obj[path[0]], path.slice(1), value);
         }
      }
   } // packages/alpinejs/src/magics.js


   var magics = {};

   function magic(name, callback) {
      magics[name] = callback;
   }

   function injectMagics(obj, el) {
      Object.entries(magics).forEach(function (_ref12) {
         var _ref13 = babelHelpers.slicedToArray(_ref12, 2),
            name = _ref13[0],
            callback = _ref13[1];

         Object.defineProperty(obj, "$".concat(name), {
            get: function get() {
               return callback(el, {
                  Alpine: alpine_default,
                  interceptor: interceptor
               });
            },
            enumerable: false
         });
      });
      return obj;
   } // packages/alpinejs/src/evaluator.js


   function evaluate(el, expression) {
      var extras = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
      var result;
      evaluateLater(el, expression)(function (value) {
         return result = value;
      }, extras);
      return result;
   }

   function evaluateLater() {
      return theEvaluatorFunction.apply(void 0, arguments);
   }

   var theEvaluatorFunction = normalEvaluator;

   function setEvaluator(newEvaluator) {
      theEvaluatorFunction = newEvaluator;
   }

   function normalEvaluator(el, expression) {
      var overriddenMagics = {};
      injectMagics(overriddenMagics, el);
      var dataStack = [overriddenMagics].concat(babelHelpers.toConsumableArray(closestDataStack(el)));

      if (typeof expression === "function") {
         return generateEvaluatorFromFunction(dataStack, expression);
      }

      var evaluator = generateEvaluatorFromString(dataStack, expression);
      return tryCatch.bind(null, el, expression, evaluator);
   }

   function generateEvaluatorFromFunction(dataStack, func) {
      return function () {
         var receiver = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : function () {};

         var _ref14 = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {},
            _ref14$scope = _ref14.scope,
            scope = _ref14$scope === void 0 ? {} : _ref14$scope,
            _ref14$params = _ref14.params,
            params = _ref14$params === void 0 ? [] : _ref14$params;

         var result = func.apply(mergeProxies([scope].concat(babelHelpers.toConsumableArray(dataStack))), params);
         runIfTypeOfFunction(receiver, result);
      };
   }

   var evaluatorMemo = {};

   function generateFunctionFromString(expression) {
      if (evaluatorMemo[expression]) {
         return evaluatorMemo[expression];
      }

      var AsyncFunction = Object.getPrototypeOf( /*#__PURE__*/babelHelpers.asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
         return regeneratorRuntime.wrap(function _callee$(_context) {
            while (1) {
               switch (_context.prev = _context.next) {
                  case 0:
                  case "end":
                     return _context.stop();
               }
            }
         }, _callee);
      }))).constructor;
      var rightSideSafeExpression = /^[\n\s]*if.*\(.*\)/.test(expression) || /^(let|const)/.test(expression) ? "(() => { ".concat(expression, " })()") : expression;
      var func = new AsyncFunction(["__self", "scope"], "with (scope) { __self.result = ".concat(rightSideSafeExpression, " }; __self.finished = true; return __self.result;"));
      evaluatorMemo[expression] = func;
      return func;
   }

   function generateEvaluatorFromString(dataStack, expression) {
      var func = generateFunctionFromString(expression);
      return function () {
         var receiver = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : function () {};

         var _ref16 = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {},
            _ref16$scope = _ref16.scope,
            scope = _ref16$scope === void 0 ? {} : _ref16$scope,
            _ref16$params = _ref16.params,
            params = _ref16$params === void 0 ? [] : _ref16$params;

         func.result = void 0;
         func.finished = false;
         var completeScope = mergeProxies([scope].concat(babelHelpers.toConsumableArray(dataStack)));
         var promise = func(func, completeScope);

         if (func.finished) {
            runIfTypeOfFunction(receiver, func.result, completeScope, params);
         } else {
            promise.then(function (result) {
               runIfTypeOfFunction(receiver, result, completeScope, params);
            });
         }
      };
   }

   function runIfTypeOfFunction(receiver, value, scope, params) {
      if (typeof value === "function") {
         var result = value.apply(scope, params);

         if (result instanceof Promise) {
            result.then(function (i) {
               return runIfTypeOfFunction(receiver, i, scope, params);
            });
         } else {
            receiver(result);
         }
      } else {
         receiver(value);
      }
   }

   function tryCatch(el, expression, callback) {
      try {
         for (var _len3 = arguments.length, args = new Array(_len3 > 3 ? _len3 - 3 : 0), _key4 = 3; _key4 < _len3; _key4++) {
            args[_key4 - 3] = arguments[_key4];
         }

         return callback.apply(void 0, args);
      } catch (e) {
         console.warn("Alpine Expression Error: ".concat(e.message, "\n\nExpression: \"").concat(expression, "\"\n\n"), el);
         throw e;
      }
   } // packages/alpinejs/src/directives.js


   var prefixAsString = "x-";

   function prefix() {
      var subject = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : "";
      return prefixAsString + subject;
   }

   function setPrefix(newPrefix) {
      prefixAsString = newPrefix;
   }

   var directiveHandlers = {};

   function directive(name, callback) {
      directiveHandlers[name] = callback;
   }

   function directives(el, attributes, originalAttributeOverride) {
      var transformedAttributeMap = {};
      var directives2 = Array.from(attributes).map(toTransformedAttributes(function (newName, oldName) {
         return transformedAttributeMap[newName] = oldName;
      })).filter(outNonAlpineAttributes).map(toParsedDirectives(transformedAttributeMap, originalAttributeOverride)).sort(byPriority);
      return directives2.map(function (directive2) {
         return getDirectiveHandler(el, directive2);
      });
   }

   function attributesOnly(attributes) {
      return Array.from(attributes).map(toTransformedAttributes()).filter(function (attr) {
         return !outNonAlpineAttributes(attr);
      });
   }

   var isDeferringHandlers = false;
   var directiveHandlerStacks = new Map();
   var currentHandlerStackKey = Symbol();

   function deferHandlingDirectives(callback) {
      isDeferringHandlers = true;
      var key = Symbol();
      currentHandlerStackKey = key;
      directiveHandlerStacks.set(key, []);

      var flushHandlers = function flushHandlers() {
         while (directiveHandlerStacks.get(key).length) {
            directiveHandlerStacks.get(key).shift()();
         }

         directiveHandlerStacks.delete(key);
      };

      var stopDeferring = function stopDeferring() {
         isDeferringHandlers = false;
         flushHandlers();
      };

      callback(flushHandlers);
      stopDeferring();
   }

   function getDirectiveHandler(el, directive2) {
      var noop = function noop() {};

      var handler3 = directiveHandlers[directive2.type] || noop;
      var cleanups = [];

      var cleanup = function cleanup(callback) {
         return cleanups.push(callback);
      };

      var _elementBoundEffect = elementBoundEffect(el),
         _elementBoundEffect2 = babelHelpers.slicedToArray(_elementBoundEffect, 2),
         effect3 = _elementBoundEffect2[0],
         cleanupEffect = _elementBoundEffect2[1];

      cleanups.push(cleanupEffect);
      var utilities = {
         Alpine: alpine_default,
         effect: effect3,
         cleanup: cleanup,
         evaluateLater: evaluateLater.bind(evaluateLater, el),
         evaluate: evaluate.bind(evaluate, el)
      };

      var doCleanup = function doCleanup() {
         return cleanups.forEach(function (i) {
            return i();
         });
      };

      onAttributeRemoved(el, directive2.original, doCleanup);

      var fullHandler = function fullHandler() {
         if (el._x_ignore || el._x_ignoreSelf) return;
         handler3.inline && handler3.inline(el, directive2, utilities);
         handler3 = handler3.bind(handler3, el, directive2, utilities);
         isDeferringHandlers ? directiveHandlerStacks.get(currentHandlerStackKey).push(handler3) : handler3();
      };

      fullHandler.runCleanups = doCleanup;
      return fullHandler;
   }

   var startingWith = function startingWith(subject, replacement) {
      return function (_ref17) {
         var name = _ref17.name,
            value = _ref17.value;
         if (name.startsWith(subject)) name = name.replace(subject, replacement);
         return {
            name: name,
            value: value
         };
      };
   };

   var into = function into(i) {
      return i;
   };

   function toTransformedAttributes() {
      var callback = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : function () {};
      return function (_ref18) {
         var name = _ref18.name,
            value = _ref18.value;

         var _attributeTransformer = attributeTransformers.reduce(function (carry, transform) {
               return transform(carry);
            }, {
               name: name,
               value: value
            }),
            newName = _attributeTransformer.name,
            newValue = _attributeTransformer.value;

         if (newName !== name) callback(newName, name);
         return {
            name: newName,
            value: newValue
         };
      };
   }

   var attributeTransformers = [];

   function mapAttributes(callback) {
      attributeTransformers.push(callback);
   }

   function outNonAlpineAttributes(_ref19) {
      var name = _ref19.name;
      return alpineAttributeRegex().test(name);
   }

   var alpineAttributeRegex = function alpineAttributeRegex() {
      return new RegExp("^".concat(prefixAsString, "([^:^.]+)\\b"));
   };

   function toParsedDirectives(transformedAttributeMap, originalAttributeOverride) {
      return function (_ref20) {
         var name = _ref20.name,
            value = _ref20.value;
         var typeMatch = name.match(alpineAttributeRegex());
         var valueMatch = name.match(/:([a-zA-Z0-9\-:]+)/);
         var modifiers = name.match(/\.[^.\]]+(?=[^\]]*$)/g) || [];
         var original = originalAttributeOverride || transformedAttributeMap[name] || name;
         return {
            type: typeMatch ? typeMatch[1] : null,
            value: valueMatch ? valueMatch[1] : null,
            modifiers: modifiers.map(function (i) {
               return i.replace(".", "");
            }),
            expression: value,
            original: original
         };
      };
   }

   var DEFAULT = "DEFAULT";
   var directiveOrder = ["ignore", "ref", "data", "bind", "init", "for", "model", "transition", "show", "if", DEFAULT, "element"];

   function byPriority(a, b) {
      var typeA = directiveOrder.indexOf(a.type) === -1 ? DEFAULT : a.type;
      var typeB = directiveOrder.indexOf(b.type) === -1 ? DEFAULT : b.type;
      return directiveOrder.indexOf(typeA) - directiveOrder.indexOf(typeB);
   } // packages/alpinejs/src/utils/dispatch.js


   function dispatch(el, name) {
      var detail = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
      el.dispatchEvent(new CustomEvent(name, {
         detail: detail,
         bubbles: true,
         composed: true,
         cancelable: true
      }));
   } // packages/alpinejs/src/nextTick.js


   var tickStack = [];
   var isHolding = false;

   function nextTick(callback) {
      tickStack.push(callback);
      queueMicrotask(function () {
         isHolding || setTimeout(function () {
            releaseNextTicks();
         });
      });
   }

   function releaseNextTicks() {
      isHolding = false;

      while (tickStack.length) {
         tickStack.shift()();
      }
   }

   function holdNextTicks() {
      isHolding = true;
   } // packages/alpinejs/src/utils/walk.js


   function walk(el, callback) {
      if (typeof ShadowRoot === "function" && el instanceof ShadowRoot) {
         Array.from(el.children).forEach(function (el2) {
            return walk(el2, callback);
         });
         return;
      }

      var skip = false;
      callback(el, function () {
         return skip = true;
      });
      if (skip) return;
      var node = el.firstElementChild;

      while (node) {
         walk(node, callback, false);
         node = node.nextElementSibling;
      }
   } // packages/alpinejs/src/utils/warn.js


   function warn(message) {
      var _console;

      for (var _len4 = arguments.length, args = new Array(_len4 > 1 ? _len4 - 1 : 0), _key5 = 1; _key5 < _len4; _key5++) {
         args[_key5 - 1] = arguments[_key5];
      }

      (_console = console).warn.apply(_console, ["Alpine Warning: ".concat(message)].concat(args));
   } // packages/alpinejs/src/lifecycle.js


   function start() {
      if (!document.body) warn("Unable to initialize. Trying to load Alpine before `<body>` is available. Did you forget to add `defer` in Alpine's `<script>` tag?");
      dispatch(document, "alpine:init");
      dispatch(document, "alpine:initializing");
      startObservingMutations();
      onElAdded(function (el) {
         return initTree(el, walk);
      });
      onElRemoved(function (el) {
         return nextTick(function () {
            return destroyTree(el);
         });
      });
      onAttributesAdded(function (el, attrs) {
         directives(el, attrs).forEach(function (handle) {
            return handle();
         });
      });

      var outNestedComponents = function outNestedComponents(el) {
         return !closestRoot(el.parentElement, true);
      };

      Array.from(document.querySelectorAll(allSelectors())).filter(outNestedComponents).forEach(function (el) {
         initTree(el);
      });
      dispatch(document, "alpine:initialized");
   }

   var rootSelectorCallbacks = [];
   var initSelectorCallbacks = [];

   function rootSelectors() {
      return rootSelectorCallbacks.map(function (fn) {
         return fn();
      });
   }

   function allSelectors() {
      return rootSelectorCallbacks.concat(initSelectorCallbacks).map(function (fn) {
         return fn();
      });
   }

   function addRootSelector(selectorCallback) {
      rootSelectorCallbacks.push(selectorCallback);
   }

   function addInitSelector(selectorCallback) {
      initSelectorCallbacks.push(selectorCallback);
   }

   function closestRoot(el) {
      var includeInitSelectors = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
      if (!el) return;
      var selectors = includeInitSelectors ? allSelectors() : rootSelectors();
      if (selectors.some(function (selector) {
         return el.matches(selector);
      })) return el;
      if (!el.parentElement) return;
      return closestRoot(el.parentElement, includeInitSelectors);
   }

   function isRoot(el) {
      return rootSelectors().some(function (selector) {
         return el.matches(selector);
      });
   }

   function initTree(el) {
      var walker = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : walk;
      deferHandlingDirectives(function () {
         walker(el, function (el2, skip) {
            directives(el2, el2.attributes).forEach(function (handle) {
               return handle();
            });
            el2._x_ignore && skip();
         });
      });
   }

   function destroyTree(root) {
      walk(root, function (el) {
         return cleanupAttributes(el);
      });
   } // packages/alpinejs/src/utils/classes.js


   function setClasses(el, value) {
      if (Array.isArray(value)) {
         return setClassesFromString(el, value.join(" "));
      } else if (babelHelpers.typeof(value) === "object" && value !== null) {
         return setClassesFromObject(el, value);
      } else if (typeof value === "function") {
         return setClasses(el, value());
      }

      return setClassesFromString(el, value);
   }

   function setClassesFromString(el, classString) {

      var missingClasses = function missingClasses(classString2) {
         return classString2.split(" ").filter(function (i) {
            return !el.classList.contains(i);
         }).filter(Boolean);
      };

      var addClassesAndReturnUndo = function addClassesAndReturnUndo(classes) {
         var _el$classList;

         (_el$classList = el.classList).add.apply(_el$classList, babelHelpers.toConsumableArray(classes));

         return function () {
            var _el$classList2;

            (_el$classList2 = el.classList).remove.apply(_el$classList2, babelHelpers.toConsumableArray(classes));
         };
      };

      classString = classString === true ? classString = "" : classString || "";
      return addClassesAndReturnUndo(missingClasses(classString));
   }

   function setClassesFromObject(el, classObject) {
      var split = function split(classString) {
         return classString.split(" ").filter(Boolean);
      };

      var forAdd = Object.entries(classObject).flatMap(function (_ref21) {
         var _ref22 = babelHelpers.slicedToArray(_ref21, 2),
            classString = _ref22[0],
            bool = _ref22[1];

         return bool ? split(classString) : false;
      }).filter(Boolean);
      var forRemove = Object.entries(classObject).flatMap(function (_ref23) {
         var _ref24 = babelHelpers.slicedToArray(_ref23, 2),
            classString = _ref24[0],
            bool = _ref24[1];

         return !bool ? split(classString) : false;
      }).filter(Boolean);
      var added = [];
      var removed = [];
      forRemove.forEach(function (i) {
         if (el.classList.contains(i)) {
            el.classList.remove(i);
            removed.push(i);
         }
      });
      forAdd.forEach(function (i) {
         if (!el.classList.contains(i)) {
            el.classList.add(i);
            added.push(i);
         }
      });
      return function () {
         removed.forEach(function (i) {
            return el.classList.add(i);
         });
         added.forEach(function (i) {
            return el.classList.remove(i);
         });
      };
   } // packages/alpinejs/src/utils/styles.js


   function setStyles(el, value) {
      if (babelHelpers.typeof(value) === "object" && value !== null) {
         return setStylesFromObject(el, value);
      }

      return setStylesFromString(el, value);
   }

   function setStylesFromObject(el, value) {
      var previousStyles = {};
      Object.entries(value).forEach(function (_ref25) {
         var _ref26 = babelHelpers.slicedToArray(_ref25, 2),
            key = _ref26[0],
            value2 = _ref26[1];

         previousStyles[key] = el.style[key];
         el.style.setProperty(kebabCase(key), value2);
      });
      setTimeout(function () {
         if (el.style.length === 0) {
            el.removeAttribute("style");
         }
      });
      return function () {
         setStyles(el, previousStyles);
      };
   }

   function setStylesFromString(el, value) {
      var cache = el.getAttribute("style", value);
      el.setAttribute("style", value);
      return function () {
         el.setAttribute("style", cache);
      };
   }

   function kebabCase(subject) {
      return subject.replace(/([a-z])([A-Z])/g, "$1-$2").toLowerCase();
   } // packages/alpinejs/src/utils/once.js


   function once(callback) {
      var fallback = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : function () {};
      var called = false;
      return function () {
         if (!called) {
            called = true;
            callback.apply(this, arguments);
         } else {
            fallback.apply(this, arguments);
         }
      };
   } // packages/alpinejs/src/directives/x-transition.js


   directive("transition", function (el, _ref27, _ref28) {
      var value = _ref27.value,
         modifiers = _ref27.modifiers,
         expression = _ref27.expression;
      var evaluate2 = _ref28.evaluate;
      if (typeof expression === "function") expression = evaluate2(expression);

      if (!expression) {
         registerTransitionsFromHelper(el, modifiers, value);
      } else {
         registerTransitionsFromClassString(el, expression, value);
      }
   });

   function registerTransitionsFromClassString(el, classString, stage) {
      registerTransitionObject(el, setClasses, "");
      var directiveStorageMap = {
         enter: function enter(classes) {
            el._x_transition.enter.during = classes;
         },
         "enter-start": function enterStart(classes) {
            el._x_transition.enter.start = classes;
         },
         "enter-end": function enterEnd(classes) {
            el._x_transition.enter.end = classes;
         },
         leave: function leave(classes) {
            el._x_transition.leave.during = classes;
         },
         "leave-start": function leaveStart(classes) {
            el._x_transition.leave.start = classes;
         },
         "leave-end": function leaveEnd(classes) {
            el._x_transition.leave.end = classes;
         }
      };
      directiveStorageMap[stage](classString);
   }

   function registerTransitionsFromHelper(el, modifiers, stage) {
      registerTransitionObject(el, setStyles);
      var doesntSpecify = !modifiers.includes("in") && !modifiers.includes("out") && !stage;
      var transitioningIn = doesntSpecify || modifiers.includes("in") || ["enter"].includes(stage);
      var transitioningOut = doesntSpecify || modifiers.includes("out") || ["leave"].includes(stage);

      if (modifiers.includes("in") && !doesntSpecify) {
         modifiers = modifiers.filter(function (i, index) {
            return index < modifiers.indexOf("out");
         });
      }

      if (modifiers.includes("out") && !doesntSpecify) {
         modifiers = modifiers.filter(function (i, index) {
            return index > modifiers.indexOf("out");
         });
      }

      var wantsAll = !modifiers.includes("opacity") && !modifiers.includes("scale");
      var wantsOpacity = wantsAll || modifiers.includes("opacity");
      var wantsScale = wantsAll || modifiers.includes("scale");
      var opacityValue = wantsOpacity ? 0 : 1;
      var scaleValue = wantsScale ? modifierValue(modifiers, "scale", 95) / 100 : 1;
      var delay = modifierValue(modifiers, "delay", 0);
      var origin = modifierValue(modifiers, "origin", "center");
      var property = "opacity, transform";
      var durationIn = modifierValue(modifiers, "duration", 150) / 1e3;
      var durationOut = modifierValue(modifiers, "duration", 75) / 1e3;
      var easing = "cubic-bezier(0.4, 0.0, 0.2, 1)";

      if (transitioningIn) {
         el._x_transition.enter.during = {
            transformOrigin: origin,
            transitionDelay: delay,
            transitionProperty: property,
            transitionDuration: "".concat(durationIn, "s"),
            transitionTimingFunction: easing
         };
         el._x_transition.enter.start = {
            opacity: opacityValue,
            transform: "scale(".concat(scaleValue, ")")
         };
         el._x_transition.enter.end = {
            opacity: 1,
            transform: "scale(1)"
         };
      }

      if (transitioningOut) {
         el._x_transition.leave.during = {
            transformOrigin: origin,
            transitionDelay: delay,
            transitionProperty: property,
            transitionDuration: "".concat(durationOut, "s"),
            transitionTimingFunction: easing
         };
         el._x_transition.leave.start = {
            opacity: 1,
            transform: "scale(1)"
         };
         el._x_transition.leave.end = {
            opacity: opacityValue,
            transform: "scale(".concat(scaleValue, ")")
         };
      }
   }

   function registerTransitionObject(el, setFunction) {
      var defaultValue = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
      if (!el._x_transition) el._x_transition = {
         enter: {
            during: defaultValue,
            start: defaultValue,
            end: defaultValue
         },
         leave: {
            during: defaultValue,
            start: defaultValue,
            end: defaultValue
         },
         in: function _in() {
            var before = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : function () {};
            var after = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : function () {};
            transition(el, setFunction, {
               during: this.enter.during,
               start: this.enter.start,
               end: this.enter.end
            }, before, after);
         },
         out: function out() {
            var before = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : function () {};
            var after = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : function () {};
            transition(el, setFunction, {
               during: this.leave.during,
               start: this.leave.start,
               end: this.leave.end
            }, before, after);
         }
      };
   }

   window.Element.prototype._x_toggleAndCascadeWithTransitions = function (el, value, show, hide) {
      var clickAwayCompatibleShow = function clickAwayCompatibleShow() {
         document.visibilityState === "visible" ? requestAnimationFrame(show) : setTimeout(show);
      };

      if (value) {
         el._x_transition ? el._x_transition.in(show) : clickAwayCompatibleShow();
         return;
      }

      el._x_hidePromise = el._x_transition ? new Promise(function (resolve, reject) {
         el._x_transition.out(function () {}, function () {
            return resolve(hide);
         });

         el._x_transitioning.beforeCancel(function () {
            return reject({
               isFromCancelledTransition: true
            });
         });
      }) : Promise.resolve(hide);
      queueMicrotask(function () {
         var closest = closestHide(el);

         if (closest) {
            if (!closest._x_hideChildren) closest._x_hideChildren = [];

            closest._x_hideChildren.push(el);
         } else {
            queueMicrotask(function () {
               var hideAfterChildren = function hideAfterChildren(el2) {
                  var carry = Promise.all([el2._x_hidePromise].concat(babelHelpers.toConsumableArray((el2._x_hideChildren || []).map(hideAfterChildren)))).then(function (_ref29) {
                     var _ref30 = babelHelpers.slicedToArray(_ref29, 1),
                        i = _ref30[0];

                     return i();
                  });
                  delete el2._x_hidePromise;
                  delete el2._x_hideChildren;
                  return carry;
               };

               hideAfterChildren(el).catch(function (e) {
                  if (!e.isFromCancelledTransition) throw e;
               });
            });
         }
      });
   };

   function closestHide(el) {
      var parent = el.parentNode;
      if (!parent) return;
      return parent._x_hidePromise ? parent : closestHide(parent);
   }

   function transition(el, setFunction) {
      var _ref31 = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {},
         _during = _ref31.during,
         start2 = _ref31.start,
         _end = _ref31.end;

      var before = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : function () {};
      var after = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : function () {};
      if (el._x_transitioning) el._x_transitioning.cancel();

      if (Object.keys(_during).length === 0 && Object.keys(start2).length === 0 && Object.keys(_end).length === 0) {
         before();
         after();
         return;
      }

      var undoStart, undoDuring, undoEnd;
      performTransition(el, {
         start: function start() {
            undoStart = setFunction(el, start2);
         },
         during: function during() {
            undoDuring = setFunction(el, _during);
         },
         before: before,
         end: function end() {
            undoStart();
            undoEnd = setFunction(el, _end);
         },
         after: after,
         cleanup: function cleanup() {
            undoDuring();
            undoEnd();
         }
      });
   }

   function performTransition(el, stages) {
      var interrupted, reachedBefore, reachedEnd;
      var finish = once(function () {
         mutateDom(function () {
            interrupted = true;
            if (!reachedBefore) stages.before();

            if (!reachedEnd) {
               stages.end();
               releaseNextTicks();
            }

            stages.after();
            if (el.isConnected) stages.cleanup();
            delete el._x_transitioning;
         });
      });
      el._x_transitioning = {
         beforeCancels: [],
         beforeCancel: function beforeCancel(callback) {
            this.beforeCancels.push(callback);
         },
         cancel: once(function () {
            while (this.beforeCancels.length) {
               this.beforeCancels.shift()();
            }
            finish();
         }),
         finish: finish
      };
      mutateDom(function () {
         stages.start();
         stages.during();
      });
      holdNextTicks();
      requestAnimationFrame(function () {
         if (interrupted) return;
         var duration = Number(getComputedStyle(el).transitionDuration.replace(/,.*/, "").replace("s", "")) * 1e3;
         var delay = Number(getComputedStyle(el).transitionDelay.replace(/,.*/, "").replace("s", "")) * 1e3;
         if (duration === 0) duration = Number(getComputedStyle(el).animationDuration.replace("s", "")) * 1e3;
         mutateDom(function () {
            stages.before();
         });
         reachedBefore = true;
         requestAnimationFrame(function () {
            if (interrupted) return;
            mutateDom(function () {
               stages.end();
            });
            releaseNextTicks();
            setTimeout(el._x_transitioning.finish, duration + delay);
            reachedEnd = true;
         });
      });
   }

   function modifierValue(modifiers, key, fallback) {
      if (modifiers.indexOf(key) === -1) return fallback;
      var rawValue = modifiers[modifiers.indexOf(key) + 1];
      if (!rawValue) return fallback;

      if (key === "scale") {
         if (isNaN(rawValue)) return fallback;
      }

      if (key === "duration") {
         var match = rawValue.match(/([0-9]+)ms/);
         if (match) return match[1];
      }

      if (key === "origin") {
         if (["top", "right", "left", "center", "bottom"].includes(modifiers[modifiers.indexOf(key) + 2])) {
            return [rawValue, modifiers[modifiers.indexOf(key) + 2]].join(" ");
         }
      }

      return rawValue;
   } // packages/alpinejs/src/utils/debounce.js


   function debounce(func, wait) {
      var timeout;
      return function () {
         var context = this,
            args = arguments;

         var later = function later() {
            timeout = null;
            func.apply(context, args);
         };

         clearTimeout(timeout);
         timeout = setTimeout(later, wait);
      };
   } // packages/alpinejs/src/utils/throttle.js


   function throttle(func, limit) {
      var inThrottle;
      return function () {
         var context = this,
            args = arguments;

         if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(function () {
               return inThrottle = false;
            }, limit);
         }
      };
   } // packages/alpinejs/src/plugin.js


   function plugin(callback) {
      callback(alpine_default);
   } // packages/alpinejs/src/store.js


   var stores = {};
   var isReactive = false;

   function store(name, value) {
      if (!isReactive) {
         stores = reactive(stores);
         isReactive = true;
      }

      if (value === void 0) {
         return stores[name];
      }

      stores[name] = value;

      if (babelHelpers.typeof(value) === "object" && value !== null && value.hasOwnProperty("init") && typeof value.init === "function") {
         stores[name].init();
      }
   }

   function getStores() {
      return stores;
   } // packages/alpinejs/src/clone.js


   var isCloning = false;

   function skipDuringClone(callback) {
      return function () {
         return isCloning || callback.apply(void 0, arguments);
      };
   }

   function clone(oldEl, newEl) {
      newEl._x_dataStack = oldEl._x_dataStack;
      isCloning = true;
      dontRegisterReactiveSideEffects(function () {
         cloneTree(newEl);
      });
      isCloning = false;
   }

   function cloneTree(el) {
      var hasRunThroughFirstEl = false;

      var shallowWalker = function shallowWalker(el2, callback) {
         walk(el2, function (el3, skip) {
            if (hasRunThroughFirstEl && isRoot(el3)) return skip();
            hasRunThroughFirstEl = true;
            callback(el3, skip);
         });
      };

      initTree(el, shallowWalker);
   }

   function dontRegisterReactiveSideEffects(callback) {
      var cache = effect;
      overrideEffect(function (callback2, el) {
         var storedEffect = cache(callback2);
         release(storedEffect);
         return function () {};
      });
      callback();
      overrideEffect(cache);
   } // packages/alpinejs/src/datas.js


   var datas = {};

   function data(name, callback) {
      datas[name] = callback;
   }

   function injectDataProviders(obj, context) {
      Object.entries(datas).forEach(function (_ref32) {
         var _ref33 = babelHelpers.slicedToArray(_ref32, 2),
            name = _ref33[0],
            callback = _ref33[1];

         Object.defineProperty(obj, name, {
            get: function get() {
               return function () {
                  return callback.bind(context).apply(void 0, arguments);
               };
            },
            enumerable: false
         });
      });
      return obj;
   } // packages/alpinejs/src/alpine.js


   var Alpine = {
      get reactive() {
         return reactive;
      },

      get release() {
         return release;
      },

      get effect() {
         return effect;
      },

      get raw() {
         return raw;
      },

      version: "3.4.2",
      flushAndStopDeferringMutations: flushAndStopDeferringMutations,
      disableEffectScheduling: disableEffectScheduling,
      setReactivityEngine: setReactivityEngine,
      addRootSelector: addRootSelector,
      deferMutations: deferMutations,
      mapAttributes: mapAttributes,
      evaluateLater: evaluateLater,
      setEvaluator: setEvaluator,
      closestRoot: closestRoot,
      interceptor: interceptor,
      transition: transition,
      setStyles: setStyles,
      mutateDom: mutateDom,
      directive: directive,
      throttle: throttle,
      debounce: debounce,
      evaluate: evaluate,
      initTree: initTree,
      nextTick: nextTick,
      prefix: setPrefix,
      plugin: plugin,
      magic: magic,
      store: store,
      start: start,
      clone: clone,
      data: data
   };
   var alpine_default = Alpine; // packages/alpinejs/src/index.js

   var import_reactivity9 = __toModule(require_reactivity()); // packages/alpinejs/src/magics/$nextTick.js


   magic("nextTick", function () {
      return nextTick;
   }); // packages/alpinejs/src/magics/$dispatch.js

   magic("dispatch", function (el) {
      return dispatch.bind(dispatch, el);
   }); // packages/alpinejs/src/magics/$watch.js

   magic("watch", function (el) {
      return function (key, callback) {
         var evaluate2 = evaluateLater(el, key);
         var firstTime = true;
         var oldValue;
         effect(function () {
            return evaluate2(function (value) {
               var div = document.createElement("div");
               div.dataset.throwAway = value;

               if (!firstTime) {
                  queueMicrotask(function () {
                     callback(value, oldValue);
                     oldValue = value;
                  });
               } else {
                  oldValue = value;
               }

               firstTime = false;
            });
         });
      };
   }); // packages/alpinejs/src/magics/$store.js

   magic("store", getStores); // packages/alpinejs/src/magics/$root.js

   magic("root", function (el) {
      return closestRoot(el);
   }); // packages/alpinejs/src/magics/$refs.js

   magic("refs", function (el) {
      if (el._x_refs_proxy) return el._x_refs_proxy;
      el._x_refs_proxy = mergeProxies(getArrayOfRefObject(el));
      return el._x_refs_proxy;
   });

   function getArrayOfRefObject(el) {
      var refObjects = [];
      var currentEl = el;

      while (currentEl) {
         if (currentEl._x_refs) refObjects.push(currentEl._x_refs);
         currentEl = currentEl.parentNode;
      }

      return refObjects;
   } // packages/alpinejs/src/magics/$el.js


   magic("el", function (el) {
      return el;
   }); // packages/alpinejs/src/directives/x-ignore.js

   var handler = function handler() {};

   handler.inline = function (el, _ref34, _ref35) {
      var modifiers = _ref34.modifiers;
      var cleanup = _ref35.cleanup;
      modifiers.includes("self") ? el._x_ignoreSelf = true : el._x_ignore = true;
      cleanup(function () {
         modifiers.includes("self") ? delete el._x_ignoreSelf : delete el._x_ignore;
      });
   };

   directive("ignore", handler); // packages/alpinejs/src/directives/x-effect.js

   directive("effect", function (el, _ref36, _ref37) {
      var expression = _ref36.expression;
      var effect3 = _ref37.effect;
      return effect3(evaluateLater(el, expression));
   }); // packages/alpinejs/src/utils/bind.js

   function bind(el, name, value) {
      var modifiers = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : [];
      if (!el._x_bindings) el._x_bindings = reactive({});
      el._x_bindings[name] = value;
      name = modifiers.includes("camel") ? camelCase(name) : name;

      switch (name) {
         case "value":
            bindInputValue(el, value);
            break;

         case "style":
            bindStyles(el, value);
            break;

         case "class":
            bindClasses(el, value);
            break;

         default:
            bindAttribute(el, name, value);
            break;
      }
   }

   function bindInputValue(el, value) {
      if (el.type === "radio") {
         if (el.attributes.value === void 0) {
            el.value = value;
         }

         if (window.fromModel) {
            el.checked = checkedAttrLooseCompare(el.value, value);
         }
      } else if (el.type === "checkbox") {
         if (Number.isInteger(value)) {
            el.value = value;
         } else if (!Number.isInteger(value) && !Array.isArray(value) && typeof value !== "boolean" && ![null, void 0].includes(value)) {
            el.value = String(value);
         } else {
            if (Array.isArray(value)) {
               el.checked = value.some(function (val) {
                  return checkedAttrLooseCompare(val, el.value);
               });
            } else {
               el.checked = !!value;
            }
         }
      } else if (el.tagName === "SELECT") {
         updateSelect(el, value);
      } else {
         if (el.value === value) return;
         el.value = value;
      }
   }

   function bindClasses(el, value) {
      if (el._x_undoAddedClasses) el._x_undoAddedClasses();
      el._x_undoAddedClasses = setClasses(el, value);
   }

   function bindStyles(el, value) {
      if (el._x_undoAddedStyles) el._x_undoAddedStyles();
      el._x_undoAddedStyles = setStyles(el, value);
   }

   function bindAttribute(el, name, value) {
      if ([null, void 0, false].includes(value) && attributeShouldntBePreservedIfFalsy(name)) {
         el.removeAttribute(name);
      } else {
         if (isBooleanAttr(name)) value = name;
         setIfChanged(el, name, value);
      }
   }

   function setIfChanged(el, attrName, value) {
      if (el.getAttribute(attrName) != value) {
         el.setAttribute(attrName, value);
      }
   }

   function updateSelect(el, value) {
      var arrayWrappedValue = [].concat(value).map(function (value2) {
         return value2 + "";
      });
      Array.from(el.options).forEach(function (option) {
         option.selected = arrayWrappedValue.includes(option.value);
      });
   }

   function camelCase(subject) {
      return subject.toLowerCase().replace(/-(\w)/g, function (match, char) {
         return char.toUpperCase();
      });
   }

   function checkedAttrLooseCompare(valueA, valueB) {
      return valueA == valueB;
   }

   function isBooleanAttr(attrName) {
      var booleanAttributes = ["disabled", "checked", "required", "readonly", "hidden", "open", "selected", "autofocus", "itemscope", "multiple", "novalidate", "allowfullscreen", "allowpaymentrequest", "formnovalidate", "autoplay", "controls", "loop", "muted", "playsinline", "default", "ismap", "reversed", "async", "defer", "nomodule"];
      return booleanAttributes.includes(attrName);
   }

   function attributeShouldntBePreservedIfFalsy(name) {
      return !["aria-pressed", "aria-checked", "aria-expanded"].includes(name);
   } // packages/alpinejs/src/utils/on.js


   function on(el, event, modifiers, callback) {
      var listenerTarget = el;

      var handler3 = function handler3(e) {
         return callback(e);
      };

      var options = {};

      var wrapHandler = function wrapHandler(callback2, wrapper) {
         return function (e) {
            return wrapper(callback2, e);
         };
      };

      if (modifiers.includes("dot")) event = dotSyntax(event);
      if (modifiers.includes("camel")) event = camelCase2(event);
      if (modifiers.includes("passive")) options.passive = true;
      if (modifiers.includes("capture")) options.capture = true;
      if (modifiers.includes("window")) listenerTarget = window;
      if (modifiers.includes("document")) listenerTarget = document;
      if (modifiers.includes("prevent")) handler3 = wrapHandler(handler3, function (next, e) {
         e.preventDefault();
         next(e);
      });
      if (modifiers.includes("stop")) handler3 = wrapHandler(handler3, function (next, e) {
         e.stopPropagation();
         next(e);
      });
      if (modifiers.includes("self")) handler3 = wrapHandler(handler3, function (next, e) {
         e.target === el && next(e);
      });

      if (modifiers.includes("away") || modifiers.includes("outside")) {
         listenerTarget = document;
         handler3 = wrapHandler(handler3, function (next, e) {
            if (el.contains(e.target)) return;
            if (el.offsetWidth < 1 && el.offsetHeight < 1) return;
            next(e);
         });
      }

      handler3 = wrapHandler(handler3, function (next, e) {
         if (isKeyEvent(event)) {
            if (isListeningForASpecificKeyThatHasntBeenPressed(e, modifiers)) {
               return;
            }
         }

         next(e);
      });

      if (modifiers.includes("debounce")) {
         var nextModifier = modifiers[modifiers.indexOf("debounce") + 1] || "invalid-wait";
         var wait = isNumeric(nextModifier.split("ms")[0]) ? Number(nextModifier.split("ms")[0]) : 250;
         handler3 = debounce(handler3, wait);
      }

      if (modifiers.includes("throttle")) {
         var _nextModifier = modifiers[modifiers.indexOf("throttle") + 1] || "invalid-wait";

         var _wait = isNumeric(_nextModifier.split("ms")[0]) ? Number(_nextModifier.split("ms")[0]) : 250;

         handler3 = throttle(handler3, _wait);
      }

      if (modifiers.includes("once")) {
         handler3 = wrapHandler(handler3, function (next, e) {
            next(e);
            listenerTarget.removeEventListener(event, handler3, options);
         });
      }

      listenerTarget.addEventListener(event, handler3, options);
      return function () {
         listenerTarget.removeEventListener(event, handler3, options);
      };
   }

   function dotSyntax(subject) {
      return subject.replace(/-/g, ".");
   }

   function camelCase2(subject) {
      return subject.toLowerCase().replace(/-(\w)/g, function (match, char) {
         return char.toUpperCase();
      });
   }

   function isNumeric(subject) {
      return !Array.isArray(subject) && !isNaN(subject);
   }

   function kebabCase2(subject) {
      return subject.replace(/([a-z])([A-Z])/g, "$1-$2").replace(/[_\s]/, "-").toLowerCase();
   }

   function isKeyEvent(event) {
      return ["keydown", "keyup"].includes(event);
   }

   function isListeningForASpecificKeyThatHasntBeenPressed(e, modifiers) {
      var keyModifiers = modifiers.filter(function (i) {
         return !["window", "document", "prevent", "stop", "once"].includes(i);
      });

      if (keyModifiers.includes("debounce")) {
         var debounceIndex = keyModifiers.indexOf("debounce");
         keyModifiers.splice(debounceIndex, isNumeric((keyModifiers[debounceIndex + 1] || "invalid-wait").split("ms")[0]) ? 2 : 1);
      }

      if (keyModifiers.length === 0) return false;
      if (keyModifiers.length === 1 && keyToModifiers(e.key).includes(keyModifiers[0])) return false;
      var systemKeyModifiers = ["ctrl", "shift", "alt", "meta", "cmd", "super"];
      var selectedSystemKeyModifiers = systemKeyModifiers.filter(function (modifier) {
         return keyModifiers.includes(modifier);
      });
      keyModifiers = keyModifiers.filter(function (i) {
         return !selectedSystemKeyModifiers.includes(i);
      });

      if (selectedSystemKeyModifiers.length > 0) {
         var activelyPressedKeyModifiers = selectedSystemKeyModifiers.filter(function (modifier) {
            if (modifier === "cmd" || modifier === "super") modifier = "meta";
            return e["".concat(modifier, "Key")];
         });

         if (activelyPressedKeyModifiers.length === selectedSystemKeyModifiers.length) {
            if (keyToModifiers(e.key).includes(keyModifiers[0])) return false;
         }
      }

      return true;
   }

   function keyToModifiers(key) {
      if (!key) return [];
      key = kebabCase2(key);
      var modifierToKeyMap = {
         ctrl: "control",
         slash: "/",
         space: "-",
         spacebar: "-",
         cmd: "meta",
         esc: "escape",
         up: "arrow-up",
         down: "arrow-down",
         left: "arrow-left",
         right: "arrow-right",
         period: ".",
         equal: "="
      };
      modifierToKeyMap[key] = key;
      return Object.keys(modifierToKeyMap).map(function (modifier) {
         if (modifierToKeyMap[modifier] === key) return modifier;
      }).filter(function (modifier) {
         return modifier;
      });
   } // packages/alpinejs/src/directives/x-model.js


   directive("model", function (el, _ref38, _ref39) {
      var modifiers = _ref38.modifiers,
         expression = _ref38.expression;
      var effect3 = _ref39.effect,
         cleanup = _ref39.cleanup;
      var evaluate2 = evaluateLater(el, expression);
      var assignmentExpression = "".concat(expression, " = rightSideOfExpression($event, ").concat(expression, ")");
      var evaluateAssignment = evaluateLater(el, assignmentExpression);
      var event = el.tagName.toLowerCase() === "select" || ["checkbox", "radio"].includes(el.type) || modifiers.includes("lazy") ? "change" : "input";
      var assigmentFunction = generateAssignmentFunction(el, modifiers, expression);
      var removeListener = on(el, event, modifiers, function (e) {
         evaluateAssignment(function () {}, {
            scope: {
               $event: e,
               rightSideOfExpression: assigmentFunction
            }
         });
      });
      cleanup(function () {
         return removeListener();
      });

      el._x_forceModelUpdate = function () {
         evaluate2(function (value) {
            if (value === void 0 && expression.match(/\./)) value = "";
            window.fromModel = true;
            mutateDom(function () {
               return bind(el, "value", value);
            });
            delete window.fromModel;
         });
      };

      effect3(function () {
         if (modifiers.includes("unintrusive") && document.activeElement.isSameNode(el)) return;

         el._x_forceModelUpdate();
      });
   });

   function generateAssignmentFunction(el, modifiers, expression) {
      if (el.type === "radio") {
         mutateDom(function () {
            if (!el.hasAttribute("name")) el.setAttribute("name", expression);
         });
      }

      return function (event, currentValue) {
         return mutateDom(function () {
            if (event instanceof CustomEvent && event.detail !== void 0) {
               return event.detail || event.target.value;
            } else if (el.type === "checkbox") {
               if (Array.isArray(currentValue)) {
                  var newValue = modifiers.includes("number") ? safeParseNumber(event.target.value) : event.target.value;
                  return event.target.checked ? currentValue.concat([newValue]) : currentValue.filter(function (el2) {
                     return !checkedAttrLooseCompare2(el2, newValue);
                  });
               } else {
                  return event.target.checked;
               }
            } else if (el.tagName.toLowerCase() === "select" && el.multiple) {
               return modifiers.includes("number") ? Array.from(event.target.selectedOptions).map(function (option) {
                  var rawValue = option.value || option.text;
                  return safeParseNumber(rawValue);
               }) : Array.from(event.target.selectedOptions).map(function (option) {
                  return option.value || option.text;
               });
            } else {
               var rawValue = event.target.value;
               return modifiers.includes("number") ? safeParseNumber(rawValue) : modifiers.includes("trim") ? rawValue.trim() : rawValue;
            }
         });
      };
   }

   function safeParseNumber(rawValue) {
      var number = rawValue ? parseFloat(rawValue) : null;
      return isNumeric2(number) ? number : rawValue;
   }

   function checkedAttrLooseCompare2(valueA, valueB) {
      return valueA == valueB;
   }

   function isNumeric2(subject) {
      return !Array.isArray(subject) && !isNaN(subject);
   } // packages/alpinejs/src/directives/x-cloak.js


   directive("cloak", function (el) {
      return queueMicrotask(function () {
         return mutateDom(function () {
            return el.removeAttribute(prefix("cloak"));
         });
      });
   }); // packages/alpinejs/src/directives/x-init.js

   addInitSelector(function () {
      return "[".concat(prefix("init"), "]");
   });
   directive("init", skipDuringClone(function (el, _ref40) {
      var expression = _ref40.expression;

      if (typeof expression === "string") {
         return !!expression.trim() && evaluate(el, expression, {}, false);
      }

      return evaluate(el, expression, {}, false);
   })); // packages/alpinejs/src/directives/x-text.js

   directive("text", function (el, _ref41, _ref42) {
      var expression = _ref41.expression;
      var effect3 = _ref42.effect,
         evaluateLater2 = _ref42.evaluateLater;
      var evaluate2 = evaluateLater2(expression);
      effect3(function () {
         evaluate2(function (value) {
            mutateDom(function () {
               el.textContent = value;
            });
         });
      });
   }); // packages/alpinejs/src/directives/x-html.js

   directive("html", function (el, _ref43, _ref44) {
      var expression = _ref43.expression;
      var effect3 = _ref44.effect,
         evaluateLater2 = _ref44.evaluateLater;
      var evaluate2 = evaluateLater2(expression);
      effect3(function () {
         evaluate2(function (value) {
            el.innerHTML = value;
         });
      });
   }); // packages/alpinejs/src/directives/x-bind.js

   mapAttributes(startingWith(":", into(prefix("bind:"))));
   directive("bind", function (el, _ref45, _ref46) {
      var value = _ref45.value,
         modifiers = _ref45.modifiers,
         expression = _ref45.expression,
         original = _ref45.original;
      var effect3 = _ref46.effect;
      if (!value) return applyBindingsObject(el, expression, original, effect3);
      if (value === "key") return storeKeyForXFor(el, expression);
      var evaluate2 = evaluateLater(el, expression);
      effect3(function () {
         return evaluate2(function (result) {
            if (result === void 0 && expression.match(/\./)) result = "";
            mutateDom(function () {
               return bind(el, value, result, modifiers);
            });
         });
      });
   });

   function applyBindingsObject(el, expression, original, effect3) {
      var getBindings = evaluateLater(el, expression);
      var cleanupRunners = [];
      effect3(function () {
         while (cleanupRunners.length) {
            cleanupRunners.pop()();
         }

         getBindings(function (bindings) {
            var attributes = Object.entries(bindings).map(function (_ref47) {
               var _ref48 = babelHelpers.slicedToArray(_ref47, 2),
                  name = _ref48[0],
                  value = _ref48[1];

               return {
                  name: name,
                  value: value
               };
            });
            attributesOnly(attributes).forEach(function (_ref49, index) {
               var name = _ref49.name,
                  value = _ref49.value;
               attributes[index] = {
                  name: "x-bind:".concat(name),
                  value: "\"".concat(value, "\"")
               };
            });
            directives(el, attributes, original).map(function (handle) {
               cleanupRunners.push(handle.runCleanups);
               handle();
            });
         });
      });
   }

   function storeKeyForXFor(el, expression) {
      el._x_keyExpression = expression;
   } // packages/alpinejs/src/directives/x-data.js


   addRootSelector(function () {
      return "[".concat(prefix("data"), "]");
   });
   directive("data", skipDuringClone(function (el, _ref50, _ref51) {
      var expression = _ref50.expression;
      var cleanup = _ref51.cleanup;
      expression = expression === "" ? "{}" : expression;
      var magicContext = {};
      injectMagics(magicContext, el);
      var dataProviderContext = {};
      injectDataProviders(dataProviderContext, magicContext);
      var data2 = evaluate(el, expression, {
         scope: dataProviderContext
      });
      injectMagics(data2, el);
      var reactiveData = reactive(data2);
      initInterceptors(reactiveData);
      var undo = addScopeToNode(el, reactiveData);
      reactiveData["init"] && evaluate(el, reactiveData["init"]);
      cleanup(function () {
         undo();
         reactiveData["destroy"] && evaluate(el, reactiveData["destroy"]);
      });
   })); // packages/alpinejs/src/directives/x-show.js

   directive("show", function (el, _ref52, _ref53) {
      var modifiers = _ref52.modifiers,
         expression = _ref52.expression;
      var effect3 = _ref53.effect;
      var evaluate2 = evaluateLater(el, expression);

      var hide = function hide() {
         return mutateDom(function () {
            el.style.display = "none";
            el._x_isShown = false;
         });
      };

      var show = function show() {
         return mutateDom(function () {
            if (el.style.length === 1 && el.style.display === "none") {
               el.removeAttribute("style");
            } else {
               el.style.removeProperty("display");
            }

            el._x_isShown = true;
         });
      };

      var clickAwayCompatibleShow = function clickAwayCompatibleShow() {
         return setTimeout(show);
      };

      var toggle = once(function (value) {
         return value ? show() : hide();
      }, function (value) {
         if (typeof el._x_toggleAndCascadeWithTransitions === "function") {
            el._x_toggleAndCascadeWithTransitions(el, value, show, hide);
         } else {
            value ? clickAwayCompatibleShow() : hide();
         }
      });
      var oldValue;
      var firstTime = true;
      effect3(function () {
         return evaluate2(function (value) {
            if (!firstTime && value === oldValue) return;
            if (modifiers.includes("immediate")) value ? clickAwayCompatibleShow() : hide();
            toggle(value);
            oldValue = value;
            firstTime = false;
         });
      });
   }); // packages/alpinejs/src/directives/x-for.js

   directive("for", function (el, _ref54, _ref55) {
      var expression = _ref54.expression;
      var effect3 = _ref55.effect,
         cleanup = _ref55.cleanup;
      var iteratorNames = parseForExpression(expression);
      var evaluateItems = evaluateLater(el, iteratorNames.items);
      var evaluateKey = evaluateLater(el, el._x_keyExpression || "index");
      el._x_prevKeys = [];
      el._x_lookup = {};
      effect3(function () {
         return loop(el, iteratorNames, evaluateItems, evaluateKey);
      });
      cleanup(function () {
         Object.values(el._x_lookup).forEach(function (el2) {
            return el2.remove();
         });
         delete el._x_prevKeys;
         delete el._x_lookup;
      });
   });

   function loop(el, iteratorNames, evaluateItems, evaluateKey) {
      var isObject = function isObject(i) {
         return babelHelpers.typeof(i) === "object" && !Array.isArray(i);
      };

      var templateEl = el;
      evaluateItems(function (items) {
         if (isNumeric3(items) && items >= 0) {
            items = Array.from(Array(items).keys(), function (i) {
               return i + 1;
            });
         }

         if (items === void 0) items = [];
         var lookup = el._x_lookup;
         var prevKeys = el._x_prevKeys;
         var scopes = [];
         var keys = [];

         if (isObject(items)) {
            items = Object.entries(items).map(function (_ref56) {
               var _ref57 = babelHelpers.slicedToArray(_ref56, 2),
                  key = _ref57[0],
                  value = _ref57[1];

               var scope = getIterationScopeVariables(iteratorNames, value, key, items);
               evaluateKey(function (value2) {
                  return keys.push(value2);
               }, {
                  scope: babelHelpers.objectSpread({
                     index: key
                  }, scope)
               });
               scopes.push(scope);
            });
         } else {
            for (var i = 0; i < items.length; i++) {
               var scope = getIterationScopeVariables(iteratorNames, items[i], i, items);
               evaluateKey(function (value) {
                  return keys.push(value);
               }, {
                  scope: babelHelpers.objectSpread({
                     index: i
                  }, scope)
               });
               scopes.push(scope);
            }
         }

         var adds = [];
         var moves = [];
         var removes = [];
         var sames = [];

         for (var _i3 = 0; _i3 < prevKeys.length; _i3++) {
            var key = prevKeys[_i3];
            if (keys.indexOf(key) === -1) removes.push(key);
         }

         prevKeys = prevKeys.filter(function (key) {
            return !removes.includes(key);
         });
         var lastKey = "template";

         for (var _i4 = 0; _i4 < keys.length; _i4++) {
            var _key6 = keys[_i4];
            var prevIndex = prevKeys.indexOf(_key6);

            if (prevIndex === -1) {
               prevKeys.splice(_i4, 0, _key6);
               adds.push([lastKey, _i4]);
            } else if (prevIndex !== _i4) {
               var keyInSpot = prevKeys.splice(_i4, 1)[0];
               var keyForSpot = prevKeys.splice(prevIndex - 1, 1)[0];
               prevKeys.splice(_i4, 0, keyForSpot);
               prevKeys.splice(prevIndex, 0, keyInSpot);
               moves.push([keyInSpot, keyForSpot]);
            } else {
               sames.push(_key6);
            }

            lastKey = _key6;
         }

         for (var _i5 = 0; _i5 < removes.length; _i5++) {
            var _key7 = removes[_i5];

            lookup[_key7].remove();

            lookup[_key7] = null;
            delete lookup[_key7];
         }

         var _loop4 = function _loop4(_i6) {
            var _moves$_i = babelHelpers.slicedToArray(moves[_i6], 2),
               keyInSpot = _moves$_i[0],
               keyForSpot = _moves$_i[1];

            var elInSpot = lookup[keyInSpot];
            var elForSpot = lookup[keyForSpot];
            var marker = document.createElement("div");
            mutateDom(function () {
               elForSpot.after(marker);
               elInSpot.after(elForSpot);
               marker.before(elInSpot);
               marker.remove();
            });
            refreshScope(elForSpot, scopes[keys.indexOf(keyForSpot)]);
         };

         for (var _i6 = 0; _i6 < moves.length; _i6++) {
            _loop4(_i6);
         }

         var _loop5 = function _loop5(_i7) {
            var _adds$_i = babelHelpers.slicedToArray(adds[_i7], 2),
               lastKey2 = _adds$_i[0],
               index = _adds$_i[1];

            var lastEl = lastKey2 === "template" ? templateEl : lookup[lastKey2];
            var scope = scopes[index];
            var key = keys[index];
            var clone2 = document.importNode(templateEl.content, true).firstElementChild;
            addScopeToNode(clone2, reactive(scope), templateEl);
            mutateDom(function () {
               lastEl.after(clone2);
               initTree(clone2);
            });

            if (babelHelpers.typeof(key) === "object") {
               warn("x-for key cannot be an object, it must be a string or an integer", templateEl);
            }

            lookup[key] = clone2;
         };

         for (var _i7 = 0; _i7 < adds.length; _i7++) {
            _loop5(_i7);
         }

         for (var _i8 = 0; _i8 < sames.length; _i8++) {
            refreshScope(lookup[sames[_i8]], scopes[keys.indexOf(sames[_i8])]);
         }

         templateEl._x_prevKeys = keys;
      });
   }

   function parseForExpression(expression) {
      var forIteratorRE = /,([^,\}\]]*)(?:,([^,\}\]]*))?$/;
      var stripParensRE = /^\s*\(|\)\s*$/g;
      var forAliasRE = /([\s\S]*?)\s+(?:in|of)\s+([\s\S]*)/;
      var inMatch = expression.match(forAliasRE);
      if (!inMatch) return;
      var res = {};
      res.items = inMatch[2].trim();
      var item = inMatch[1].replace(stripParensRE, "").trim();
      var iteratorMatch = item.match(forIteratorRE);

      if (iteratorMatch) {
         res.item = item.replace(forIteratorRE, "").trim();
         res.index = iteratorMatch[1].trim();

         if (iteratorMatch[2]) {
            res.collection = iteratorMatch[2].trim();
         }
      } else {
         res.item = item;
      }

      return res;
   }

   function getIterationScopeVariables(iteratorNames, item, index, items) {
      var scopeVariables = {};

      if (/^\[.*\]$/.test(iteratorNames.item) && Array.isArray(item)) {
         var names = iteratorNames.item.replace("[", "").replace("]", "").split(",").map(function (i) {
            return i.trim();
         });
         names.forEach(function (name, i) {
            scopeVariables[name] = item[i];
         });
      } else if (/^\{.*\}$/.test(iteratorNames.item) && !Array.isArray(item) && babelHelpers.typeof(item) === "object") {
         var _names = iteratorNames.item.replace("{", "").replace("}", "").split(",").map(function (i) {
            return i.trim();
         });

         _names.forEach(function (name) {
            scopeVariables[name] = item[name];
         });
      } else {
         scopeVariables[iteratorNames.item] = item;
      }

      if (iteratorNames.index) scopeVariables[iteratorNames.index] = index;
      if (iteratorNames.collection) scopeVariables[iteratorNames.collection] = items;
      return scopeVariables;
   }

   function isNumeric3(subject) {
      return !Array.isArray(subject) && !isNaN(subject);
   } // packages/alpinejs/src/directives/x-ref.js


   function handler2() {}

   handler2.inline = function (el, _ref58, _ref59) {
      var expression = _ref58.expression;
      var cleanup = _ref59.cleanup;
      var root = closestRoot(el);
      if (!root._x_refs) root._x_refs = {};
      root._x_refs[expression] = el;
      cleanup(function () {
         return delete root._x_refs[expression];
      });
   };

   directive("ref", handler2); // packages/alpinejs/src/directives/x-if.js

   directive("if", function (el, _ref60, _ref61) {
      var expression = _ref60.expression;
      var effect3 = _ref61.effect,
         cleanup = _ref61.cleanup;
      var evaluate2 = evaluateLater(el, expression);

      var show = function show() {
         if (el._x_currentIfEl) return el._x_currentIfEl;
         var clone2 = el.content.cloneNode(true).firstElementChild;
         addScopeToNode(clone2, {}, el);
         mutateDom(function () {
            el.after(clone2);
            initTree(clone2);
         });
         el._x_currentIfEl = clone2;

         el._x_undoIf = function () {
            clone2.remove();
            delete el._x_currentIfEl;
         };

         return clone2;
      };

      var hide = function hide() {
         if (!el._x_undoIf) return;

         el._x_undoIf();

         delete el._x_undoIf;
      };

      effect3(function () {
         return evaluate2(function (value) {
            value ? show() : hide();
         });
      });
      cleanup(function () {
         return el._x_undoIf && el._x_undoIf();
      });
   }); // packages/alpinejs/src/directives/x-on.js

   mapAttributes(startingWith("@", into(prefix("on:"))));
   directive("on", skipDuringClone(function (el, _ref62, _ref63) {
      var value = _ref62.value,
         modifiers = _ref62.modifiers,
         expression = _ref62.expression;
      var cleanup = _ref63.cleanup;
      var evaluate2 = expression ? evaluateLater(el, expression) : function () {};
      var removeListener = on(el, value, modifiers, function (e) {
         evaluate2(function () {}, {
            scope: {
               $event: e
            },
            params: [e]
         });
      });
      cleanup(function () {
         return removeListener();
      });
   })); // packages/alpinejs/src/index.js

   alpine_default.setEvaluator(normalEvaluator);
   alpine_default.setReactivityEngine({
      reactive: import_reactivity9.reactive,
      effect: import_reactivity9.effect,
      release: import_reactivity9.stop,
      raw: import_reactivity9.toRaw
   });
   var src_default = alpine_default; // packages/alpinejs/builds/module.js

   var module_default = src_default;

   var Usercard = /*#__PURE__*/function () {
      function Usercard() {
         babelHelpers.classCallCheck(this, Usercard);
      }

      babelHelpers.createClass(Usercard, [{
         key: "createSelect",
         value: function createSelect(options) {
            return new SlimSelect(options);
         }
      }, {
         key: "createMoneyField",
         value: function createMoneyField(value) {
            var prettyEuro = prettyMoney({
               currency: "€",
               decimals: "fixed",
               decimalDelimiter: ",",
               thousandsDelimiter: "."
            });
            return prettyEuro(value);
         }
      }]);
      return Usercard;
   }();
   window.Alpine = module_default;
   module_default.start();

   exports.Usercard = Usercard;

}((this.window = this.window || {}),BX));
//# sourceMappingURL=usercard.bundle.js.map
