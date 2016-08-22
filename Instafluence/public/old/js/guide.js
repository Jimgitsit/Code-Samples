/************************************\
            Nav Slow Scroll
\************************************/
var current = 1;
var windowHeight;
var windowWidth;

var sectionHeight = function(section) {
    section.height(windowHeight);
};

$(document).ready(function() {
    windowHeight = $(window).height();
    if (windowHeight >= 750) {
        sectionHeight($("#sec-9"));
    }
});

$('.title-select a, .dot a').click(function() {
    var elementClicked = $(this).attr("href");
    var destination = $(elementClicked).offset().top;
    current = $(this).data("section");

    $("html:not(:animated),body:not(:animated)").animate({
        scrollTop: destination
    }, 800);

    return false;
});

$('#pn-buttons a').click(function() {
    if ($(this).is('#prev') && current > 1) {
        current--;
    }
    if ($(this).is('#prev') && current == 1) {
        current = 9;
    }
    if ($(this).is('#next') && current < 9) {
        current++;
    } else if ($(this).is('#next') && current == 9) {
        current = 1;
    }
    var destination = $('#sec-' + current).offset().top;
    $("html:not(:animated),body:not(:animated)").animate({
        scrollTop: destination
    }, 800);
    return false;
});

$('#menu-icon').click(function() {
    $(this).toggleClass('active inactive');
    if ($(this).is('.active')) {
        $('#guide-nav').animate({
            left: "+=395px"
        });
    } else if ($(this).is('.inactive')) {
        $('#guide-nav').animate({
            left: "-=395px"
        });
    }
    return false;
});

$(window).scroll(function() {
    var scrollTop = $(window).scrollTop();

    $('.content').each(function() {
        var itemPos = $(this).offset().top
        if (itemPos - 5 <= scrollTop) {
            current = $(this).data('section');

            $('.title-select, .dot').not($('#pag-' + current + ", #item-" + current)).removeClass('active');
            $('#pag-' + current + ", #item-" + current).addClass('active');
        }
    });

    if ((current == 3 || current == 4) && $('#menu-icon').not('.light')) {
        $('#menu-icon').addClass('light');
    } else if ((current != 3 || current != 4) && $('#menu-icon').is('.light')) {
        $('#menu-icon').removeClass('light');
    }
});

$(window).resize(function() {
    windowHeight = $(window).height();
    windowWidth = $(window).width();

    if (windowHeight >= 750) {
        sectionHeight($("#sec-9"));
    }
    if (windowWidth > 1050) {
        $('#guide-nav').css('left', 0);
    }
    if (windowWidth < 1050 && $('#menu-icon').is('.active')) {
        $('#guide-nav').css('left', 0);
    }
    if (windowWidth < 1050 && $('#menu-icon').is('.inactive')) {
        $('#guide-nav').css('left', -395);
    }
});