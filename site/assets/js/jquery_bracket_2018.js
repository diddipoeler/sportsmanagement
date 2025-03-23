/* jQuery Bracket | Copyright (c) Teijo Laine 2011-2018 | Licenced under the MIT licence */

var __extends =
    (this && this.__extends) ||
    (function () {
        var t =
            Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array &&
                function (t, e) {
                    t.__proto__ = e;
                }) ||
            function (t, e) {
                for (var n in e) e.hasOwnProperty(n) && (t[n] = e[n]);
            };
        return function (e, n) {
            t(e, n);
            function r() {
                this.constructor = e;
            }
            e.prototype = null === n ? Object.create(n) : ((r.prototype = n.prototype), new r());
        };
    })();
!(function (t) {
    var e,
        n = (function () {
            function t(e) {
                if (((this.val = e), e instanceof t)) throw new Error("Trying to wrap Option into an Option");
                if (void 0 === this.val) throw new Error("Option cannot contain undefined");
            }
            return (
                (t.of = function (e) {
                    return new t(e);
                }),
                (t.empty = function () {
                    return new t(null);
                }),
                (t.prototype.get = function () {
                    if (null === this.val) throw new Error("Trying to get() empty Option");
                    return this.val;
                }),
                (t.prototype.orElse = function (t) {
                    return null === this.val ? t : this.val;
                }),
                (t.prototype.orElseGet = function (t) {
                    return null === this.val ? t() : this.val;
                }),
                (t.prototype.map = function (e) {
                    return null === this.val ? t.empty() : new t(e(this.val));
                }),
                (t.prototype.forEach = function (t) {
                    return null !== this.val && t(this.val), this;
                }),
                (t.prototype.toNull = function () {
                    return null === this.val ? null : this.val;
                }),
                (t.prototype.isEmpty = function () {
                    return null === this.val;
                }),
                t
            );
        })(),
        r = (function (t) {
            __extends(e, t);
            function e() {
                return (null !== t && t.apply(this, arguments)) || this;
            }
            return (
                (e.of = function (e) {
                    var n = typeof e;
                    if (null !== e && "number" !== n) throw new Error("Invalid score format, expected number, got " + n);
                    return t.of.call(this, e);
                }),
                (e.empty = function () {
                    return n.empty();
                }),
                e
            );
        })(n),
        i = (function () {
            return function (t, e, n) {
                if (((this.first = t), (this.second = e), (this.userData = n), !t || !e)) throw new Error("Cannot create ResultObject with undefined scores");
            };
        })();
    ((o = e || (e = {}))[(o.TBD = 0)] = "TBD"), (o[(o.BYE = 1)] = "BYE"), (o[(o.END = 2)] = "END");
    var o,
        s = (function () {
            function t(t) {
                this.isFirst = t;
            }
            return (
                (t.first = function () {
                    return new t(!0);
                }),
                (t.second = function () {
                    return new t(!1);
                }),
                (t.prototype.map = function (t, e) {
                    return this.isFirst ? t : e;
                }),
                t
            );
        })(),
        a = (function () {
            function t(t, e, n, r, i) {
                (this.source = t),
                    (this.nameOrGetter = e),
                    (this.order = n),
                    (this.seed = r),
                    (this.score = i),
                    (this.sibling = function () {
                        throw new Error("No sibling asigned");
                    });
            }
            return (
                Object.defineProperty(t.prototype, "name", {
                    get: function () {
                        return "function" == typeof this.nameOrGetter ? this.nameOrGetter() : this.nameOrGetter;
                    },
                    set: function (t) {
                        this.nameOrGetter = t;
                    },
                    enumerable: !0,
                    configurable: !0,
                }),
                (t.prototype.emptyBranch = function () {
                    if (!this.name.isEmpty()) return this.sibling().name.isEmpty() ? e.BYE : e.TBD;
                    try {
                        var t = this.source().emptyBranch();
                        if (t === e.TBD) return e.TBD;
                        if (t === e.END) return e.BYE;
                        return this.source().sibling().emptyBranch() === e.TBD ? e.TBD : e.BYE;
                    } catch (t) {
                        if (t instanceof c) return e.END;
                        throw new Error('Unexpected exception type (message: "' + t.message + '")');
                    }
                }),
                t
            );
        })();
    function h(t) {
        return !isNaN(parseFloat(t)) && isFinite(t);
    }
    function c() {
        (this.message = "Root of information for this team"), (this.name = "EndOfBranchException");
    }
    var u,
        l = (function () {
            function t(t, e) {
                (this.a = t), (this.b = e);
            }
            return (
                (t.teamsInResultOrder = function (t) {
                    var n = t.a.name.isEmpty(),
                        r = t.b.name.isEmpty();
                    if (r && !n) return t.b.emptyBranch() === e.BYE ? [t.a, t.b] : [];
                    if (n && !r) return t.a.emptyBranch() === e.BYE ? [t.b, t.a] : [];
                    if (!t.a.score.isEmpty() && !t.b.score.isEmpty()) {
                        if (t.a.score.get() > t.b.score.get()) return [t.a, t.b];
                        if (t.a.score.get() < t.b.score.get()) return [t.b, t.a];
                    }
                    return [];
                }),
                (t.emptyTeam = function (t, e) {
                    var i = new a(t, n.empty(), n.empty(), n.empty(), r.empty());
                    return (
                        (i.sibling = function () {
                            return e;
                        }),
                        i
                    );
                }),
                (t.prototype.winner = function () {
                    return t.teamsInResultOrder(this)[0] || t.emptyTeam(this.a.source, this.b);
                }),
                (t.prototype.loser = function () {
                    return t.teamsInResultOrder(this)[1] || t.emptyTeam(this.b.source, this.a);
                }),
                t
            );
        })();
    ((p = u || (u = {})).EMPTY_BYE = "empty-bye"), (p.EMPTY_TBD = "empty-tbd"), (p.ENTRY_NO_SCORE = "entry-no-score"), (p.ENTRY_DEFAULT_WIN = "entry-default-win"), (p.ENTRY_COMPLETE = "entry-complete");
    var p;
    function d(e, n, r) {
        var i = r.find(".team[data-teamid=" + e + "]"),
            o = n || "highlight";
        return {
            highlight: function () {
                i.each(function () {
                    t(this).addClass(o), t(this).hasClass("win") && t(this).parent().find(".connector").addClass(o);
                });
            },
            deHighlight: function () {
                i.each(function () {
                    t(this).removeClass(o), t(this).parent().find(".connector").removeClass(o);
                });
            },
        };
    }
    function f(e, n, r) {
        var i = t('<input type="text">');
        i.val(n),
            e.empty().append(i),
            i.focus(),
            i.blur(function () {
                r(i.val());
            }),
            i.keydown(function (t) {
                var e = t.keyCode || t.which;
                (9 !== e && 13 !== e && 27 !== e) || (t.preventDefault(), r(i.val(), 27 !== e));
            });
    }
    function m(t, e, n, r) {
        switch (r) {
            case u.EMPTY_BYE:
                return void t.append("BYE");
            case u.EMPTY_TBD:
                return void t.append("TBD");
            case u.ENTRY_NO_SCORE:
            case u.ENTRY_DEFAULT_WIN:
            case u.ENTRY_COMPLETE:
                return void t.append(e);
        }
    }
    function g(t) {
        var e = t.el;
        e.find(".team.win").append('<div class="bubble">1st</div>');
        return e.find(".team.lose").append('<div class="bubble">2nd</div>'), !0;
    }
    function b(t) {
        var e = t.el;
        e.find(".team.win").append('<div class="bubble third">3rd</div>');
        return e.find(".team.lose").append('<div class="bubble fourth">4th</div>'), !0;
    }
    var v = function () {
            throw new c();
        },
        y = function (t, e) {
            return function () {
                var i = new a(
                        v,
                        function () {
                            return t[e][0];
                        },
                        n.of(s.first()),
                        n.of(2 * e),
                        r.empty()
                    ),
                    o = new a(
                        v,
                        function () {
                            return t[e][1];
                        },
                        n.of(s.second()),
                        n.of(2 * e + 1),
                        r.empty()
                    );
                return (
                    (i.sibling = function () {
                        return o;
                    }),
                    (o.sibling = function () {
                        return i;
                    }),
                    [
                        {
                            source: function () {
                                return i;
                            },
                        },
                        {
                            source: function () {
                                return o;
                            },
                        },
                    ]
                );
            };
        },
        C = function (t, e) {
            return function (n) {
                n.css("top", ""), n.css("position", "absolute"), e ? n.css("top", t.el.height() / 2 - n.height() / 2 + "px") : n.css("bottom", -n.height() / 2 + "px");
            };
        };
    var E = function (t, e, n, r, i, o) {
            return function () {
                if (i % 2 == 0 && 0 === o)
                    return [
                        {
                            source: function () {
                                return t
                                    .round(0)
                                    .match(2 * r)
                                    .loser();
                            },
                        },
                        {
                            source: function () {
                                return t
                                    .round(0)
                                    .match(2 * r + 1)
                                    .loser();
                            },
                        },
                    ];
                var s = o % 2 == 0 ? n - r - 1 : r;
                return [
                    {
                        source: function () {
                            return e
                                .round(2 * o)
                                .match(r)
                                .winner();
                        },
                    },
                    {
                        source: function () {
                            return t
                                .round(o + 1)
                                .match(s)
                                .loser();
                        },
                    },
                ];
            };
        },
        w = function (t, e) {
            return function () {
                return t.css("top", e.el.height() / 2 - t.height() / 2 + "px");
            };
        },
        k = function (t) {
            return function (e, n) {
                var r = e.height() / 4,
                    i = { height: 0, shift: 2 * r };
                return n
                    .winner()
                    .order.map(function (e) {
                        return e.map(t ? i : { height: 0, shift: r }, t ? i : { height: 2 * -r, shift: r });
                    })
                    .orElse(i);
            };
        };
    var T = (function () {
            function e(e, n, r, i, o, s, a, h) {
                (this.bracket = e),
                    (this.previousRound = n),
                    (this.roundNumber = r),
                    (this.roundResults = i),
                    (this.doRenderCb = o),
                    (this.mkMatch = s),
                    (this.isFirstBracket = a),
                    (this.opts = h),
                    (this.containerWidth = this.opts.teamWidth + this.opts.scoreWidth),
                    (this.roundCon = t('<div class="round" style="width: ' + this.containerWidth + "px; margin-right: " + this.opts.roundMargin + 'px"/>')),
                    (this.matches = []);
            }
            return (
                Object.defineProperty(e.prototype, "el", {
                    get: function () {
                        return this.roundCon;
                    },
                    enumerable: !0,
                    configurable: !0,
                }),
                (e.prototype.addMatch = function (t, e) {
                    var i = this,
                        o = this.matches.length,
                        h =
                            null !== t
                                ? t()
                                : [
                                      {
                                          source: function () {
                                              return i.bracket
                                                  .round(i.roundNumber - 1)
                                                  .match(2 * o)
                                                  .winner();
                                          },
                                      },
                                      {
                                          source: function () {
                                              return i.bracket
                                                  .round(i.roundNumber - 1)
                                                  .match(2 * o + 1)
                                                  .winner();
                                          },
                                      },
                                  ],
                        c = function () {
                            return h[0].source();
                        },
                        u = function () {
                            return h[1].source();
                        },
                        p = new a(c, c().name, n.of(s.first()), c().seed, r.empty()),
                        d = new a(u, u().name, n.of(s.second()), u().seed, r.empty());
                    (p.sibling = function () {
                        return d;
                    }),
                        (d.sibling = function () {
                            return p;
                        });
                    var f = new l(p, d),
                        m = this.mkMatch(
                            this,
                            f,
                            o,
                            this.roundResults.map(function (t) {
                                return void 0 === t[o] ? null : t[o];
                            }),
                            e,
                            this.isFirstBracket,
                            this.opts
                        );
                    return this.matches.push(m), m;
                }),
                (e.prototype.match = function (t) {
                    return this.matches[t];
                }),
                (e.prototype.prev = function () {
                    return this.previousRound;
                }),
                (e.prototype.size = function () {
                    return this.matches.length;
                }),
                (e.prototype.render = function () {
                    this.roundCon.empty(),
                        (this.doRenderCb.isEmpty() || this.doRenderCb.get()()) &&
                            (this.roundCon.appendTo(this.bracket.el),
                            this.matches.forEach(function (t) {
                                return t.render();
                            }));
                }),
                (e.prototype.results = function () {
                    return this.matches.reduce(function (t, e) {
                        return t.concat([e.results()]);
                    }, []);
                }),
                e
            );
        })(),
        M = (function () {
            function t(t, e, n, r, i) {
                (this.bracketCon = t), (this.initResults = e), (this.mkMatch = n), (this.isFirstBracket = r), (this.opts = i), (this.rounds = []);
            }
            return (
                Object.defineProperty(t.prototype, "el", {
                    get: function () {
                        return this.bracketCon;
                    },
                    enumerable: !0,
                    configurable: !0,
                }),
                (t.prototype.addRound = function (t) {
                    var e = this.rounds.length,
                        o = e > 0 ? n.of(this.rounds[e - 1]) : n.empty(),
                        s = this.initResults.map(function (t) {
                            return void 0 === t[e] ? new i(r.empty(), r.empty(), void 0) : t[e];
                        }),
                        a = new T(this, o, e, s, t, this.mkMatch, this.isFirstBracket, this.opts);
                    return this.rounds.push(a), a;
                }),
                (t.prototype.dropRound = function () {
                    this.rounds.pop();
                }),
                (t.prototype.round = function (t) {
                    return this.rounds[t];
                }),
                (t.prototype.size = function () {
                    return this.rounds.length;
                }),
                (t.prototype.final = function () {
                    return this.rounds[this.rounds.length - 1].match(0);
                }),
                (t.prototype.winner = function () {
                    return this.rounds[this.rounds.length - 1].match(0).winner();
                }),
                (t.prototype.loser = function () {
                    return this.rounds[this.rounds.length - 1].match(0).loser();
                }),
                (t.prototype.render = function () {
                    this.bracketCon.empty();
                    for (var t = 0, e = this.rounds; t < e.length; t++) {
                        e[t].render();
                    }
                }),
                (t.prototype.results = function () {
                    return this.rounds.reduce(function (t, e) {
                        return t.concat([e.results()]);
                    }, []);
                }),
                t
            );
        })(),
        R = function (t) {
            return t < 0 ? { height: -t, drop: !1 } : t < 2 ? { height: 0, drop: !0 } : { height: t, drop: !0 };
        };
    function x(e) {
        var n = t.extend(!0, {}, e);
        return (
            (n.teams = n.teams.map(function (t) {
                return t.map(function (t) {
                    return t.toNull();
                });
            })),
            (n.results = n.results.map(function (t) {
                return t.map(function (t) {
                    return t.map(function (t) {
                        var e = [t.first.toNull(), t.second.toNull()];
                        return void 0 !== t.userData && e.push(t.userData), e;
                    });
                });
            })),
            n
        );
    }
    var O = (function () {
        function t() {
            this.counter = 0;
        }
        return (
            (t.prototype.get = function () {
                return this.counter;
            }),
            (t.prototype.getNext = function () {
                return ++this.counter;
            }),
            (t.prototype.reset = function () {
                this.counter = 0;
            }),
            t
        );
    })();
    function B(i, o, s, a, c, l, p, d, f, m) {
        var g = s.name.isEmpty() || a.name.isEmpty() ? "" : 'data-resultid="result-' + d.getNext() + '"',
            b = t('<div class="score" style="width: ' + p.scoreWidth + 'px;" ' + g + "></div>"),
            v =
                s.name.isEmpty() || a.name.isEmpty() || !c
                    ? n.empty()
                    : s.score.map(function (t) {
                          return "" + t;
                      }),
            y = v.orElse("--");
        b.text(y);
        var C = t('<div class="team" style="width: ' + (p.teamWidth + p.scoreWidth) + 'px;"></div>'),
            E = t('<div class="label" style="width: ' + p.teamWidth + 'px;"></div>').appendTo(C);
        p.decorator.render(
            E,
            s.name.toNull(),
            y,
            ((k = a),
            (T = v),
            (w = s).name
                .map(function () {
                    return T.map(function () {
                        return u.ENTRY_COMPLETE;
                    }).orElseGet(function () {
                        return k.emptyBranch() === e.BYE ? u.ENTRY_DEFAULT_WIN : u.ENTRY_NO_SCORE;
                    });
                })
                .orElseGet(function () {
                    var t = w.emptyBranch();
                    switch (t) {
                        case e.BYE:
                            return u.EMPTY_BYE;
                        case e.TBD:
                            return u.EMPTY_TBD;
                        default:
                            throw new Error("Unexpected branch type " + t);
                    }
                }))
        );
        var w, k, T;
        if (
            (s.seed.forEach(function (t) {
                C.attr("data-teamid", t);
            }),
            s.name.isEmpty() ? C.addClass("na") : o.winner().name === s.name ? C.addClass("win") : o.loser().name === s.name && C.addClass("lose"),
            C.append(b),
            (!s.name.isEmpty() || (s.name.isEmpty() && 0 === i && l)) &&
                "function" == typeof p.save &&
                (p.disableTeamEdit ||
                    (E.addClass("editable"),
                    E.click(function () {
                        var e = t(this);
                        !(function r() {
                            e.unbind(),
                                p.decorator.edit(e, s.name.toNull(), function (o, a) {
                                    var h = s.seed.get();
                                    (p.init.teams[~~(h / 2)][h % 2] = n.of(o || null)), m(!0), e.click(r);
                                    var c = p.el.find(".team[data-teamid=" + (h + 1) + "] div.label:first");
                                    c.length && !0 === a && 0 === i && t(c).click();
                                });
                        })();
                    })),
                !s.name.isEmpty() && !a.name.isEmpty() && c))
        ) {
            var M = d.get();
            b.addClass("editable"),
                b.click(function () {
                    var e = t(this);
                    !(function n() {
                        e.unbind();
                        var i = h(s.score) ? e.text() : "0",
                            o = t('<input type="text">');
                        o.val(i),
                            e.empty().append(o),
                            o.focus().select(),
                            o.keydown(function (e) {
                                h(t(this).val()) ? t(this).removeClass("error") : t(this).addClass("error");
                                var n = e.keyCode || e.which;
                                if (9 === n || 13 === n || 27 === n) {
                                    if ((e.preventDefault(), t(this).blur(), 27 === n)) return;
                                    var r = f.find("div.score[data-resultid=result-" + (M + 1) + "]");
                                    r && r.click();
                                }
                            }),
                            o.blur(function () {
                                var t = o.val();
                                (t && h(t)) || h(s.score) ? (t && h(t)) || !h(s.score) || (t = s.score) : (t = "0"), e.html(t), h(t) && ((s.score = r.of(parseInt(t, 10))), m(!0)), e.click(n);
                            });
                    })();
                });
        }
        return C;
    }
    var N = (function () {
            function e(e, i, o, s, a, c, u, l, p, d) {
                if (
                    ((this.round = e),
                    (this.match = i),
                    (this.seed = o),
                    (this.renderCb = a),
                    (this.isFirstBracket = c),
                    (this.opts = u),
                    (this.resultId = l),
                    (this.topCon = p),
                    (this.renderAll = d),
                    (this.connectorCb = n.empty()),
                    (this.matchCon = t('<div class="match"></div>')),
                    (this.teamCon = t('<div class="teamContainer"></div>')),
                    (this.alignCb = null),
                    (this.matchUserData = s.isEmpty() ? void 0 : s.get().userData),
                    !u.save)
                ) {
                    var f = this.matchUserData;
                    u.onMatchHover &&
                        this.teamCon.hover(
                            function () {
                                u.onMatchHover(f, !0);
                            },
                            function () {
                                u.onMatchHover(f, !1);
                            }
                        ),
                        u.onMatchClick &&
                            this.teamCon.click(function () {
                                u.onMatchClick(f);
                            });
                }
                (i.a.name = i.a.source().name),
                    (i.b.name = i.b.source().name),
                    (i.a.score = s.map(function (t) {
                        return t.first.toNull();
                    })),
                    (i.b.score = s.map(function (t) {
                        return t.second.toNull();
                    })),
                    (i.a.name && i.b.name) ||
                        (!h(i.a.score) && !h(i.b.score)) ||
                        (console.warn("ERROR IN SCORE DATA: " + i.a.source().name + ": " + i.a.score + ", " + i.b.source().name + ": " + i.b.score), (i.a.score = i.b.score = r.empty()));
            }
            return (
                Object.defineProperty(e.prototype, "el", {
                    get: function () {
                        return this.matchCon;
                    },
                    enumerable: !0,
                    configurable: !0,
                }),
                (e.prototype.getRound = function () {
                    return this.round;
                }),
                (e.prototype.setConnectorCb = function (t) {
                    this.connectorCb = t;
                }),
                (e.prototype.connect = function (e) {
                    var n = this,
                        r = "lr" === this.opts.dir ? "right" : "left",
                        i = this.teamCon.height() / 4,
                        o = this.matchCon.height() / 2,
                        s = e
                            .map(function (t) {
                                return t(n.teamCon, n);
                            })
                            .orElseGet(function () {
                                return n.seed % 2 == 0
                                    ? n
                                          .winner()
                                          .order.map(function (t) {
                                              return t.map({ height: o, shift: i * (n.opts.centerConnectors ? 2 : 1) }, { height: o - i * (n.opts.centerConnectors ? 0 : 2), shift: i * (n.opts.centerConnectors ? 2 : 3) });
                                          })
                                          .orElse({ height: o - i * (n.opts.centerConnectors ? 0 : 1), shift: 2 * i })
                                    : n
                                          .winner()
                                          .order.map(function (t) {
                                              return t.map({ height: -o + i * (n.opts.centerConnectors ? 0 : 2), shift: -i * (n.opts.centerConnectors ? 2 : 3) }, { height: -o, shift: -i * (n.opts.centerConnectors ? 2 : 1) });
                                          })
                                          .orElse({ height: -o + i * (n.opts.centerConnectors ? 0 : 1), shift: 2 * -i });
                            });
                    this.teamCon.append(
                        (function (e, n, r, i) {
                            var o = n.shift,
                                s = R(n.height),
                                a = s.height,
                                h = s.drop,
                                c = e / 2,
                                u = t('<div class="connector"></div>').appendTo(r);
                            u.css("height", a), u.css("width", c + "px"), u.css(i, -c - 2 + "px"), o >= 0 ? u.css("top", o - 1 + "px") : u.css("bottom", -o - 1 + "px"), h ? u.css("border-bottom", "none") : u.css("border-top", "none");
                            var l = t('<div class="connector"></div>').appendTo(u);
                            return l.css("width", c + "px"), l.css(i, -c + "px"), h ? l.css("bottom", "0px") : l.css("top", "0px"), u;
                        })(this.opts.roundMargin, s, this.teamCon, r)
                    );
                }),
                (e.prototype.winner = function () {
                    return this.match.winner();
                }),
                (e.prototype.loser = function () {
                    return this.match.loser();
                }),
                (e.prototype.first = function () {
                    return this.match.a;
                }),
                (e.prototype.second = function () {
                    return this.match.b;
                }),
                (e.prototype.setAlignCb = function (t) {
                    this.alignCb = t;
                }),
                (e.prototype.render = function () {
                    var t = this;
                    this.matchCon.empty(),
                        this.teamCon.empty(),
                        (this.match.a.name = this.match.a.source().name),
                        (this.match.b.name = this.match.b.source().name),
                        (this.match.a.seed = this.match.a.source().seed),
                        (this.match.b.seed = this.match.b.source().seed);
                    this.match.a.name.isEmpty() && this.match.b.name.isEmpty() ? this.teamCon.addClass("np") : this.match.winner().name ? this.teamCon.removeClass("np") : this.teamCon.addClass("np");
                    var e = !this.match.a.name.isEmpty() && !this.match.b.name.isEmpty();
                    this.teamCon.append(B(this.round.roundNumber, this.match, this.match.a, this.match.b, e, this.isFirstBracket, this.opts, this.resultId, this.topCon, this.renderAll)),
                        this.teamCon.append(B(this.round.roundNumber, this.match, this.match.b, this.match.a, e, this.isFirstBracket, this.opts, this.resultId, this.topCon, this.renderAll)),
                        this.matchCon.appendTo(this.round.el),
                        this.matchCon.append(this.teamCon),
                        this.el.css("height", this.round.bracket.el.height() / this.round.size() + "px"),
                        this.teamCon.css("top", this.el.height() / 2 - this.teamCon.height() / 2 + "px"),
                        null !== this.alignCb && this.alignCb(this.teamCon);
                    this.renderCb
                        .map(function (e) {
                            return e(t);
                        })
                        .orElse(!1) || this.connect(this.connectorCb);
                }),
                (e.prototype.results = function () {
                    return (this.match.a.name.isEmpty() || this.match.b.name.isEmpty()) && (this.match.a.score = this.match.b.score = r.empty()), new i(this.match.a.score, this.match.b.score, this.matchUserData);
                }),
                e
            );
        })(),
        _ = function (t) {
            return void 0 === t ? null : t;
        },
        D = function (e) {
            var r = new O(),
                i = e.init,
                o = i.results.length <= 1,
                s = 45 * i.teams.length + i.teams.length * e.matchMargin,
                a = t('<div class="jQBracket ' + e.dir + '"></div>').appendTo(e.el.empty());
            function h() {
                var t = (function (t, e, n, r, i) {
                    if (e) return Math.log(2 * t) / Math.log(2);
                    if (n) return Math.max(2, 2 * (Math.log(2 * t) / Math.log(2) - 1) - 1);
                    var o = !r && 3 === i.length && 2 === i[2].length;
                    return 2 * (Math.log(2 * t) / Math.log(2) - 1) + 1 + (o ? 1 : 0);
                })(i.teams.length, o, e.skipGrandFinalComeback, e.skipSecondaryFinal, i.results);
                e.disableToolbar ? a.css("width", t * (e.teamWidth + e.scoreWidth + e.roundMargin) + 10) : a.css("width", t * (e.teamWidth + e.scoreWidth + e.roundMargin) + 40),
                    o && i.teams.length <= 2 && !e.skipConsolationRound && a.css("height", s + 40);
            }
            var c, u, l;
            function p(n) {
                r.reset(),
                    c.render(),
                    u && u.render(),
                    l && !e.skipGrandFinalComeback && l.render(),
                    e.disableHighlight ||
                        (function (e, n, r) {
                            var i = r || n,
                                o = i.winner(),
                                s = i.loser();
                            o && s && (o.name.isEmpty() || d(o.seed.get(), "highlightWinner", e).highlight(), s.name.isEmpty() || d(s.seed.get(), "highlightLoser", e).highlight()),
                                e.find(".team").mouseover(function () {
                                    var n = t(this).attr("data-teamid");
                                    if (void 0 !== n) {
                                        var r = d(parseInt(n, 10), null, e);
                                        r.highlight(),
                                            t(this).mouseout(function () {
                                                r.deHighlight(), t(this).unbind("mouseout");
                                            });
                                    }
                                });
                        })(a, c, l),
                    n && ((i.results[0] = c.results()), u && (i.results[1] = u.results()), l && !e.skipGrandFinalComeback && (i.results[2] = l.results()), h(), e.save && e.save(x(i), e.userData));
            }
            e.skipSecondaryFinal && o && t.error("skipSecondaryFinal setting is viable only in double elimination mode"),
                e.disableToolbar ||
                    (function (e, r, i) {
                        var o = t('<div class="tools"></div>').appendTo(e);
                        if (
                            (t('<span class="increment">+</span>')
                                .appendTo(o)
                                .click(function () {
                                    for (var t = r.teams.length, e = 0; e < t; e += 1) r.teams.push([n.empty(), n.empty()]);
                                    return D(i);
                                }),
                            (r.teams.length > 1 && 1 === r.results.length) || (r.teams.length > 2 && 3 === r.results.length))
                        ) {
                            var s = t('<span class="decrement">-</span>').appendTo(o);
                            s.click(function () {
                                if (r.teams.length > 1) return (r.teams = r.teams.slice(0, r.teams.length / 2)), D(i);
                            });
                        }
                        if (1 === r.results.length && r.teams.length > 1) {
                            var a = t('<span class="doubleElimination">de</span>').appendTo(o);
                            a.click(function () {
                                if (r.teams.length > 1 && r.results.length < 3) return r.results.push([], []), D(i);
                            });
                        } else if (3 === r.results.length && r.teams.length > 1) {
                            var a = t('<span class="singleElimination">se</span>').appendTo(o);
                            a.click(function () {
                                if (3 === r.results.length) return (r.results = r.results.slice(0, 1)), D(i);
                            });
                        }
                    })(a, i, e);
            var f, m, v;
            o
                ? (m = t('<div class="bracket"></div>').appendTo(a))
                : (e.skipGrandFinalComeback || (f = t('<div class="finals"></div>').appendTo(a)), (m = t('<div class="bracket"></div>').appendTo(a)), (v = t('<div class="loserBracket"></div>').appendTo(a))),
                m.css("height", s),
                v && v.css("height", m.height() / 2),
                h();
            var T = function (t, e, n, i, o, s, h) {
                return new N(t, e, n, i, o, s, h, r, a, p);
            };
            return (
                (c = new M(m, n.of(i.results[0] || null), T, !0, e)),
                o || ((u = new M(v, n.of(i.results[1] || null), T, !1, e)), e.skipGrandFinalComeback || (l = new M(f, n.of(i.results[2] || null), T, !1, e))),
                (function (t, e, r, i, o) {
                    for (var s, a = Math.log(2 * e.length) / Math.log(2), h = e.length, c = 0; c < a; c += 1) {
                        s = t.addRound(n.empty());
                        for (var u = 0; u < h; u += 1) {
                            var l = 0 === c ? y(e, u) : null;
                            if ((c === a - 1 && r) || (c === a - 1 && o)) {
                                var p = s.addMatch(l, n.of(g));
                                o || p.setAlignCb(C(p, i.skipConsolationRound));
                            } else s.addMatch(l, n.empty());
                        }
                        h /= 2;
                    }
                    if (r && (t.final().setConnectorCb(n.empty()), e.length > 1 && !i.skipConsolationRound)) {
                        var d = t.final().getRound().prev(),
                            f = d
                                .map(function (t) {
                                    return function () {
                                        return t.match(0).loser();
                                    };
                                })
                                .toNull(),
                            m = d
                                .map(function (t) {
                                    return function () {
                                        return t.match(1).loser();
                                    };
                                })
                                .toNull(),
                            v = s.addMatch(function () {
                                return [{ source: f }, { source: m }];
                            }, n.of(b));
                        v.setAlignCb(function (e) {
                            var n = t.el.height() / 2;
                            v.el.css("height", n + "px");
                            var r = e.height() / 2 + i.matchMargin;
                            e.css("top", r + "px");
                        }),
                            v.setConnectorCb(n.empty());
                    }
                })(c, i.teams, o, e, e.skipGrandFinalComeback && !o),
                o ||
                    ((function (t, e, r, i, o) {
                        for (var s = Math.log(2 * r) / Math.log(2) - 1, a = r / 2, h = 0; h < s; h += 1) {
                            for (var c = i && h === s - 1 ? 1 : 2, u = 0; u < c; u += 1)
                                for (var l = e.addRound(n.empty()), p = 0; p < a; p += 1) {
                                    var d = u % 2 != 0 || 0 === h ? E(t, e, a, p, u, h) : null,
                                        f = h === s - 1 && i,
                                        m = l.addMatch(d, n.of(f ? b : null));
                                    if ((m.setAlignCb(w(m.el.find(".teamContainer"), m)), f)) m.setConnectorCb(n.empty());
                                    else if (h < s - 1 || u < 1) {
                                        var g = u % 2 == 0 ? k(o) : null;
                                        m.setConnectorCb(n.of(g));
                                    }
                                }
                            a /= 2;
                        }
                    })(c, u, i.teams.length, e.skipGrandFinalComeback, e.centerConnectors),
                    e.skipGrandFinalComeback ||
                        (function (t, e, r, i, o, s) {
                            var a = t.addRound(n.empty()),
                                h = a.addMatch(
                                    function () {
                                        return [
                                            {
                                                source: function () {
                                                    return e.winner();
                                                },
                                            },
                                            {
                                                source: function () {
                                                    return r.winner();
                                                },
                                            },
                                        ];
                                    },
                                    n.of(function (o) {
                                        var a = !1;
                                        if (i.skipSecondaryFinal || o.winner().name.isEmpty() || o.winner().name !== r.winner().name) {
                                            if (2 === t.size()) t.dropRound();
                                            else if (t.size() > 2) throw new Error("Unexpected number of final rounds");
                                            return g(o);
                                        }
                                        if (2 === t.size()) return !1;
                                        var h = t
                                            .addRound(
                                                n.of(function () {
                                                    var e = !o.winner().name.isEmpty() && o.winner().name === r.winner().name;
                                                    return !1 === a && e && ((a = !0), s()), !e && a && ((a = !1), t.dropRound(), s()), e;
                                                })
                                            )
                                            .addMatch(function () {
                                                return [
                                                    {
                                                        source: function () {
                                                            return o.first();
                                                        },
                                                    },
                                                    {
                                                        source: function () {
                                                            return o.second();
                                                        },
                                                    },
                                                ];
                                            }, n.of(g));
                                        return (
                                            o.setConnectorCb(
                                                n.of(function (t) {
                                                    return { height: 0, shift: t.height() / 2 };
                                                })
                                            ),
                                            h.setConnectorCb(n.empty()),
                                            h.setAlignCb(function (t) {
                                                var n = e.el.height() + r.el.height();
                                                h.el.css("height", n + "px");
                                                var i = (e.el.height() / 2 + e.el.height() + r.el.height() / 2) / 2 - t.height();
                                                t.css("top", i + "px");
                                            }),
                                            !1
                                        );
                                    })
                                );
                            if (
                                (h.setAlignCb(function (t) {
                                    var n = e.el.height() + r.el.height();
                                    i.skipConsolationRound || (n /= 2), h.el.css("height", n + "px");
                                    var o = (e.el.height() / 2 + e.el.height() + r.el.height() / 2) / 2 - t.height();
                                    t.css("top", o + "px");
                                }),
                                !i.skipConsolationRound)
                            ) {
                                var c = r.final().getRound().prev(),
                                    u = a.addMatch(function () {
                                        return [
                                            {
                                                source: function () {
                                                    return c.get().match(0).loser();
                                                },
                                            },
                                            {
                                                source: function () {
                                                    return r.loser();
                                                },
                                            },
                                        ];
                                    }, n.of(b));
                                u.setAlignCb(function (t) {
                                    var n = (e.el.height() + r.el.height()) / 2;
                                    u.el.css("height", n + "px");
                                    var i = (e.el.height() / 2 + e.el.height() + r.el.height() / 2) / 2 + t.height() / 2 - n;
                                    t.css("top", i + "px");
                                }),
                                    h.setConnectorCb(n.empty()),
                                    u.setConnectorCb(n.empty());
                            }
                            e.final().setConnectorCb(
                                n.of(function (t) {
                                    var n = t.height() / 4,
                                        o = (e.el.height() / 2 + e.el.height() + r.el.height() / 2) / 2 - t.height() / 2 - e.el.height() / 2,
                                        s = e
                                            .winner()
                                            .order.map(function (t) {
                                                return t.map({ height: o + 2 * n, shift: n * (i.centerConnectors ? 2 : 1) }, { height: o + n * (i.centerConnectors ? 2 : 0), shift: n * (i.centerConnectors ? 2 : 3) });
                                            })
                                            .orElse({ height: o + n * (i.centerConnectors ? 2 : 1), shift: 2 * n }),
                                        a = s.height,
                                        h = s.shift;
                                    return { height: a - t.height() / 2, shift: h };
                                })
                            ),
                                r.final().setConnectorCb(
                                    n.of(function (t) {
                                        var n = t.height() / 4,
                                            o = (e.el.height() / 2 + e.el.height() + r.el.height() / 2) / 2 - t.height() / 2 - e.el.height() / 2,
                                            s = r
                                                .winner()
                                                .order.map(function (t) {
                                                    return t.map({ height: o + n * (i.centerConnectors ? 2 : 0), shift: n * (i.centerConnectors ? 2 : 3) }, { height: o + 2 * n, shift: n * (i.centerConnectors ? 2 : 1) });
                                                })
                                                .orElse({ height: o + n * (i.centerConnectors ? 2 : 1), shift: 2 * n }),
                                            a = s.height,
                                            h = s.shift;
                                        return { height: -(a + t.height() / 2), shift: -h };
                                    })
                                );
                        })(l, c, u, e, 0, h)),
                p(!1),
                {
                    data: function () {
                        return x(e.init);
                    },
                }
            );
        };
    var F = function (t, e) {
            if (t.hasOwnProperty(e)) {
                var n = typeof t[e];
                if ("number" !== n) throw new Error('Option "' + e + '" is ' + n + " instead of number");
            }
        },
        Y = function (t, e) {
            var n = typeof t[e];
            if ("boolean" !== n) throw new Error("Value of " + e + " must be boolean, got boolean, got " + n);
        },
        P = function (t, e, n) {
            var r = e[n];
            if (r < t) throw new Error("Value of " + n + " must be greater than " + t + ", got " + r);
        },
        W = {
            init: function (e) {
                var o = t.extend(!0, {}, e);
                if (!o) throw Error("Options not set");
                if (!o.init && !o.save) throw Error("No bracket data or save callback given");
                if ((void 0 === o.userData && (o.userData = null), !(!o.decorator || (o.decorator.edit && o.decorator.render)))) throw Error("Invalid decorator input");
                o.decorator || (o.decorator = { edit: f, render: m }),
                    o.init || (o.init = { results: [], teams: [[n.empty(), n.empty()]] }),
                    (o.el = this),
                    o.save && (o.onMatchClick || o.onMatchHover) && t.error("Match callbacks may not be passed in edit mode (in conjunction with save callback)");
                var s = typeof o.disableToolbar,
                    a = o.hasOwnProperty("disableToolbar");
                a && "boolean" !== s && t.error("disableToolbar must be a boolean, got " + s),
                    !o.save && a && t.error('disableToolbar can be used only if the bracket is editable, i.e. "save" callback given'),
                    a || (o.disableToolbar = void 0 === o.save);
                var h = typeof o.disableTeamEdit,
                    c = o.hasOwnProperty("disableTeamEdit");
                c && "boolean" !== h && t.error("disableTeamEdit must be a boolean, got " + h),
                    !o.save && c && t.error('disableTeamEdit can be used only if the bracket is editable, i.e. "save" callback given'),
                    c || (o.disableTeamEdit = !1),
                    !o.disableToolbar && o.disableTeamEdit && t.error('disableTeamEdit requires also resizing to be disabled, initialize with "disableToolbar: true"');
                var u,
                    l = (function t(e, n) {
                        return n > 0 && (e = t([e], n - 1)), e;
                    })(
                        o.init.results,
                        4 -
                            ((u = o.init.results),
                            (function t(e, n) {
                                return e instanceof Array ? t(e[0], n + 1) : n;
                            })(u, 0))
                    );
                o.init.results =
                    ((p = l),
                    p.map(function (t) {
                        return t.map(function (t) {
                            return t.map(function (t) {
                                return new i(r.of(_(t[0])), r.of(_(t[1])), t[2]);
                            });
                        });
                    }));
                var p;
                F(o, "teamWidth"),
                    F(o, "scoreWidth"),
                    F(o, "roundMargin"),
                    F(o, "matchMargin"),
                    o.hasOwnProperty("teamWidth") || (o.teamWidth = 70),
                    o.hasOwnProperty("scoreWidth") || (o.scoreWidth = 30),
                    o.hasOwnProperty("roundMargin") || (o.roundMargin = 40),
                    o.hasOwnProperty("matchMargin") || (o.matchMargin = 20),
                    P(0, o, "teamWidth"),
                    P(0, o, "scoreWidth"),
                    P(0, o, "roundMargin"),
                    P(0, o, "matchMargin"),
                    o.hasOwnProperty("centerConnectors") || (o.centerConnectors = !1),
                    Y(o, "centerConnectors"),
                    o.hasOwnProperty("disableHighlight") || (o.disableHighlight = !1),
                    Y(o, "disableHighlight");
                var d,
                    g = ((d = o.init.teams.length), d & (d - 1));
                g !== Math.floor(g) && t.error('"teams" property must have 2^n number of team pairs, i.e. 1, 2, 4, etc. Got ' + o.init.teams.length + " team pairs."),
                    (o.dir = o.dir || "lr"),
                    (o.init.teams = o.init.teams && 0 !== o.init.teams.length ? o.init.teams : [[null, null]]),
                    (o.init.teams = o.init.teams.map(function (t) {
                        return t.map(function (t) {
                            return null === t ? n.empty() : n.of(t);
                        });
                    })),
                    (o.skipConsolationRound = o.skipConsolationRound || !1),
                    (o.skipSecondaryFinal = o.skipSecondaryFinal || !1),
                    "lr" !== o.dir && "rl" !== o.dir && t.error('Direction must be either: "lr" or "rl"');
                var b = D(o);
                return t(this).data("bracket", { target: this, obj: b }), b;
            },
            data: function () {
                return t(this).data("bracket").obj.data();
            },
        };
    t.fn.bracket = function (e) {
        return W[e] ? W[e].apply(this, Array.prototype.slice.call(arguments, 1)) : "object" != typeof e && e ? void t.error("Method " + e + " does not exist on jQuery.bracket") : W.init.apply(this, arguments);
    };
})(jQuery);
