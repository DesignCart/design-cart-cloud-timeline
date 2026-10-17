/**
 * Design Cart Cloud Timeline
 * Code: design-cart-cloud-timeline
 * Autor: Paweł Nosko — https://www.designcart.pl/pawel-nosko.html
 * Firma: Design Cart — https://www.designcart.pl/
 */

(function () {
  "use strict";

  var NS = "http://www.w3.org/2000/svg";
  var RM = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  function parseConfig(el) {
    try {
      var raw = el.getAttribute("data-dcct-config") || "{}";
      return JSON.parse(raw);
    } catch (e) {
      return { orientation: "v", amp: 7, speed: 1, drift: 10, entries: [] };
    }
  }

  function wave(x1, y1, x2, y2, amp, sgn, ph) {
    var dx = x2 - x1;
    var dy = y2 - y1;
    var L = Math.hypot(dx, dy) || 1;
    var nx = -dy / L;
    var ny = dx / L;
    var d = "";
    for (var i = 0; i <= 60; i++) {
      var t = i / 60;
      var o = sgn * amp * Math.sin(t * Math.PI * 3 - ph) * Math.sin(t * Math.PI) * 1.6;
      d += (i ? "L" : "M") + (x1 + dx * t + nx * o).toFixed(1) + " " + (y1 + dy * t + ny * o).toFixed(1);
    }
    return d;
  }

  function rng(a) {
    return function () {
      a |= 0;
      a = (a + 0x6d2b79f5) | 0;
      var t = Math.imul(a ^ (a >>> 15), 1 | a);
      t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
      return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
  }

  function cloud(c, w, h, seed) {
    var R = rng(seed);
    var r = function (a, b) {
      return a + R() * (b - a);
    };
    var add = function (cx, cy, rad) {
      var p = document.createElement("i");
      p.className = "dcct__puff";
      p.style.cssText =
        "left:" + (cx - rad) + "px;top:" + (cy - rad) + "px;width:" + rad * 2 + "px;height:" + rad * 2 + "px";
      c.appendChild(p);
    };
    var q = function () {
      return r(36, 60) + "px";
    };
    c.style.borderRadius = q() + " " + q() + " " + q() + " " + q() + "/" + q() + " " + q() + " " + q() + " " + q();
    var nt = Math.round(r(3, 5.4));
    for (var i = 0; i < nt; i++) {
      var f = (i + 0.5 + r(-0.3, 0.3)) / nt;
      var s = 0.55 + 0.45 * Math.sin(Math.PI * f);
      add(w * (0.06 + 0.88 * f), r(2, 12), r(15, 32) * s + 4);
    }
    var nb = Math.round(r(2, 3.4));
    for (var j = 0; j < nb; j++) {
      var f2 = (j + 0.5 + r(-0.3, 0.3)) / nb;
      add(w * (0.1 + 0.8 * f2), h - r(2, 10), r(12, 22));
    }
    [0, 1].forEach(function (side) {
      if (R() < 0.75) {
        var y = h * (R() < 0.5 ? r(0.2, 0.35) : r(0.65, 0.8));
        add(side ? w - r(3, 9) : r(3, 9), y, r(12, 20));
      }
    });
  }

  function Timeline(root) {
    this.root = root;
    this.cfg = parseConfig(root);
    this.entries = Array.isArray(this.cfg.entries) ? this.cfg.entries : [];
    this.S = {
      o: this.cfg.orientation === "h" ? "h" : "v",
      amp: typeof this.cfg.amp === "number" ? this.cfg.amp : 7,
      drift: RM ? 0 : typeof this.cfg.drift === "number" ? this.cfg.drift : 10,
      speed: RM ? 0 : typeof this.cfg.speed === "number" ? this.cfg.speed : 1,
    };
    this.wrap = root.querySelector(".dcct__wrap");
    this.stage = root.querySelector(".dcct__stage");
    this.svg = root.querySelector(".dcct__lines");
    this.bar = root.querySelector(".dcct__bar");
    this.ITEMS = [];
    this.T0 = performance.now();
    this.LAST = this.T0;
    this.PH = 0;
    this.entries.forEach(function (e) {
      e.seed = (Math.random() * 1e9) | 0;
    });
    this._boundFrame = this.frame.bind(this);
    this._boundLoop = this.loop.bind(this);
    this._rt = null;
    this.render();
    requestAnimationFrame(this._boundLoop);
    window.addEventListener("resize", this.onResize.bind(this));
  }

  Timeline.prototype.onResize = function () {
    var self = this;
    clearTimeout(this._rt);
    this._rt = setTimeout(function () {
      self.render();
    }, 120);
  };

  Timeline.prototype.frame = function (now) {
    var t = (now - this.T0) / 1000;
    this.PH += Math.max(0, Math.min((now - this.LAST) / 1000, 0.1)) * this.S.speed * 2.2;
    this.LAST = now;
    for (var i = 0; i < this.ITEMS.length; i++) {
      var it = this.ITEMS[i];
      var k = it.i * 1.7;
      var dx = this.S.drift * Math.sin(t * 0.55 + k);
      var dy = this.S.drift * 0.8 * Math.cos(t * 0.42 + k * 1.3);
      it.c.style.translate = dx.toFixed(1) + "px " + dy.toFixed(1) + "px";
      var x2 = it.ex + dx;
      var y2 = it.ey + dy;
      it.pa.setAttribute("d", wave(it.p.x, it.p.y, x2, y2, this.S.amp, it.sgn, this.PH + it.i));
      it.ci.setAttribute("cx", x2);
      it.ci.setAttribute("cy", y2);
    }
  };

  Timeline.prototype.loop = function (n) {
    this.frame(n);
    requestAnimationFrame(this._boundLoop);
  };

  Timeline.prototype.render = function () {
    if (!this.stage || !this.wrap || !this.svg || !this.bar) return;
    var EV = this.entries;
    if (!EV.length) {
      this.stage.style.height = "80px";
      return;
    }

    this.stage.querySelectorAll(".dcct__card, .dcct__node").forEach(function (n) {
      n.remove();
    });
    this.svg.innerHTML = "";

    var n = EV.length;
    var W = this.wrap.clientWidth || 640;
    var V = this.S.o === "v";
    var LEN = V ? 120 : 110;
    var narrow = V && W < 700;
    var cardW = V ? (narrow ? Math.min(W - 100, 340) : Math.min(340, W / 2 - LEN - 24)) : 260;

    var cards = EV.map(
      function (e, i) {
        var c = document.createElement("div");
        var left = V && !narrow && i % 2 === 1;
        c.className = "dcct__card" + (left ? " dcct-card--rev" : "");
        c.style.width = cardW + "px";
        c.style.animationDelay = i * 90 + "ms";

        var imgHtml = "";
        if (e.image_url) {
          imgHtml = '<img class="dcct__photo" alt="" src="' + String(e.image_url).replace(/"/g, "&quot;") + '">';
        } else {
          imgHtml = '<span class="dcct__photo" aria-hidden="true"></span>';
        }

        var title = String(e.title || "");
        var date = String(e.date || "");

        c.innerHTML =
          imgHtml +
          '<div class="dcct__card-body"><h3></h3><div class="dcct__card-date"></div><div class="dcct__card-content"></div></div>';
        c.querySelector("h3").textContent = title;
        c.querySelector(".dcct__card-date").textContent = date;
        // Content is sanitized with wp_kses_post on save.
        c.querySelector(".dcct__card-content").innerHTML = e.content || "";

        this.stage.appendChild(c);
        cloud(c, cardW, c.offsetHeight, e.seed);
        return c;
      }.bind(this)
    );

    var maxH = Math.max.apply(
      null,
      cards.map(function (c) {
        return c.offsetHeight;
      })
    );
    var SW;
    var SH;
    var axis;
    var pos;

    if (V) {
      var gap = Math.max(maxH + 30, 170);
      var top = 70;
      SW = W;
      SH = top * 2 + (n - 1) * gap;
      axis = narrow ? 34 : W / 2;
      pos = function (i) {
        return { x: axis, y: top + i * gap };
      };
      this.bar.className = "dcct__bar dcct-bar--v";
      Object.assign(this.bar.style, {
        left: axis - 7 + "px",
        top: "24px",
        width: "14px",
        height: SH - 48 + "px",
      });
    } else {
      var gapH = 250;
      var pad = 170;
      SW = Math.max(W, pad * 2 + (n - 1) * gapH);
      SH = 2 * (LEN + maxH + 50);
      axis = SH / 2;
      pos = function (i) {
        return { x: pad + i * gapH, y: axis };
      };
      this.bar.className = "dcct__bar dcct-bar--h";
      Object.assign(this.bar.style, {
        left: "24px",
        top: axis - 7 + "px",
        height: "14px",
        width: SW - 48 + "px",
      });
    }

    this.stage.style.width = SW + "px";
    this.stage.style.height = SH + "px";
    this.svg.setAttribute("width", SW);
    this.svg.setAttribute("height", SH);

    this.ITEMS = [];
    var self = this;
    EV.forEach(function (e, i) {
      var p = pos(i);
      var c = cards[i];
      var h = c.offsetHeight;
      var sgn = i % 2 ? 1 : -1;
      var nd = document.createElement("div");
      nd.className = "dcct__node";
      nd.textContent = String(i + 1);
      nd.style.left = p.x + "px";
      nd.style.top = p.y + "px";
      self.stage.appendChild(nd);

      var ex;
      var ey;
      if (V) {
        var right = narrow || i % 2 === 0;
        var off = i % 2 ? -26 : 26;
        ey = p.y + off;
        ex = right ? p.x + LEN : p.x - LEN;
        c.style.left = (right ? ex : ex - cardW) + "px";
        c.style.top = ey - h / 2 + "px";
      } else {
        var up = i % 2 === 0;
        var offH = i % 2 ? -34 : 34;
        ex = p.x + offH;
        ey = up ? p.y - LEN : p.y + LEN;
        c.style.left = ex - cardW / 2 + "px";
        c.style.top = (up ? ey - h : ey) + "px";
      }

      var pa = document.createElementNS(NS, "path");
      var ci = document.createElementNS(NS, "circle");
      ci.setAttribute("r", 4.5);
      self.svg.appendChild(pa);
      self.svg.appendChild(ci);
      self.ITEMS.push({ p: p, ex: ex, ey: ey, c: c, sgn: sgn, i: i, pa: pa, ci: ci });
    });

    this.frame(performance.now());
    if (!V) this.wrap.scrollLeft = 0;
  };

  function initAll() {
    document.querySelectorAll(".dcct[data-dcct-config]").forEach(function (el) {
      if (el._dcct) return;
      el._dcct = new Timeline(el);
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initAll);
  } else {
    initAll();
  }
})();
