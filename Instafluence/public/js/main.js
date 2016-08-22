var bars = [];
var b = 0;
var influencers = [];

bars[0] = {
    barName: "may",
    i: 253,
    dA: "M173.834,252.375h-31.667V",
    dB: "c0-1.115,1.371-2.018,3.062-2.018h25.544c1.691,0,3.062,0.904,3.062,2.018V252.375z",
    height: 204,
    speed: 15
};
bars[1] = {
    barName: "jun",
    i: 253,
    dA: "M220.084,252.375h-31.667V",
    dB: "c0-1.661,1.371-3.007,3.062-3.007h25.544c1.691,0,3.062,1.346,3.062,3.007V252.375z",
    height: 180,
    speed: 10
};
bars[2] = {
    barName: "jul",
    i: 253,
    dA: "M266.334,252.375h-32V",
    dB: "c0-1.153,1.345-2.088,3.004-2.088h25.991c1.659,0,3.004,0.935,3.004,2.088V252.375z",
    height: 68,
    speed: 8
};
bars[3] = {
    barName: "aug",
    i: 253,
    dA: "M313,252.375h-32V",
    dB: "c0-1.55,1.345-2.806,3.004-2.806h25.991c1.659,0,3.004,1.256,3.004,2.806V252.375z",
    height: 4,
    speed: 2
};

influencers[0] = {
    percent: "0%",
    margin: "0 6.25% 0 0",
    followers: 3486298,
    speed: 10,
    i: 0,
    inf: 1
};
influencers[1] = {
    percent: "21%",
    margin: "0 6.25% 0 0",
    followers: 2782219,
    speed: 10,
    i: 0,
    inf: 2
}
influencers[2] = {
    percent: "42.5%",
    margin: "0 6.25% 0 0",
    followers: 2436168,
    speed: 10,
    i: 0,
    inf: 3
};
influencers[3] = {
    percent: "64%",
    margin: "0 6.25% 0 0",
    followers: 1106737,
    speed: 10,
    i: 0,
    inf: 4
};
influencers[4] = {
    percent: "85%",
    margin: "0",
    followers: 886298,
    speed: 10,
    i: 0,
    inf: 5
};


function loop(bar) {
    setTimeout(function() {
        var d = bar.dA;
        d += bar.i;
        d += bar.dB;
        $('#' + bar.barName + "-bar").attr('d', d);

        bar.i -= 1;
        if (bar.i == 100) {
            $('#' + bar.barName + "-bar, #" + bar.barName + "-text").animate({
                svgFill: '#FF5D5F'
            }, 1000);
        }
        if (bar.i >= bar.height) {
            loop(bar);
        }
    }, bar.speed);
}

function barStep() {
    setTimeout(function() {
        $("#" + bars[b].barName + "-bar").show();
        $("#" + bars[b].barName + "-text").fadeIn(400);

        loop(bars[b]);
        b++;

        if (b < bars.length) {
            barStep();
        }
    }, 300);
}

function commaSeparateNumber(val) {
    while (/(\d+)(\d{3})/.test(val.toString())) {
        val = val.toString().replace(/(\d+)(\d{3})/, '$1' + ',' + '$2');
    }
    return val;
}

function count(f) {
    setTimeout(function() {
        var limit = f.followers;

        if (f.i < limit / 2) {
            if (f.i < 50) {
                f.i += 1;
            } else if (f.i < 500) {
                f.i += 11;
            } else if (f.i < 5000) {
                f.i += 111;
            } else if (f.i < 50000) {
                f.i += 1111;
            } else if (f.i < 500000) {
                f.i += 11111;
            } else if (f.i < 5000000) {
                f.i += 111111;
            } else if (f.i < 50000000) {
                f.i += 111111;
            }
        } else {
            if ((f.i / limit) * 100 < 90) {
                f.i += 111111;
            } else if ((f.i / limit) * 100 < 99.5) {
                f.i += 11111;
            } else if ((f.i / limit) * 100 < 99.8) {
                f.i += 1111;
            } else if ((f.i / limit) * 100 < 99.99) {
                f.i += 555;
            } else if ((f.i / limit) * 100 < 99.9997) {
                f.i += 31;
            } else {
                f.i += 1;
            }
        }
        var printVal;
        if (f.i < limit) {
            printVal = f.i;
        } else {
            printVal = limit;
        }

        $('#fl' + f.inf).html(commaSeparateNumber(printVal));

        if (f.i > (limit / 4) * 3) {
            f.speed++;
        }

        if (f.i <= limit) {
            count(f);
        }
    }, f.speed);
}

var lastScrollTop = 0;
var windowHeight;
var windowWidth;
var graphComplete = false;
var infComplete = false;

$(document).ready(function() {
    windowWidth = $(window).width();
    windowHeight = $(window).height();
});

$(window).resize(function() {
    windowWidth = $(this).width();
    windowHeight = $(this).height();
});

$(window).scroll(function() {
    var st = $(window).scrollTop();
    var graphPos = $('#graph').offset().top;
   /*  var infPos = $('#people').offset().top; */

    if (st > lastScrollTop) {
        if (st + ((windowHeight / 5) * 3) >= graphPos && graphComplete == false) {
            barStep();
            graphComplete = true;
        }
    } else {
        // upscroll code
    }
    lastScrollTop = st;
});