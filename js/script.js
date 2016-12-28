$(document).ready(function(){
    try
    {
        window[$('body').attr('data-page')]();
    }
    catch(err)
    {
        // Handle error(s) here
    }
    $('.menu-a').click(function(){
        loadAjax($(this).attr('href'), 0);
        return false;
    });
    $(window).on('popstate', function(){
        url = window.location.href.replace($('base').attr('href'), '');
        loadAjax(url, 1);
    });
});

function loadAjax(url, f)
{
    if(f != 1)
    {
        window.history.pushState(null, null, url);
        url = url.replace($('base').attr('href'), '');
    }
    $.ajax({
        url: 'aj' + url,
        type: 'get',
        dataType: 'json',
        cache: false,
        beforeSend: function(xhr) {
            $('.content *').off();
            $('.content').html('<div class="loader-walk"><div></div><div></div><div></div><div></div><div></div></div>');
        },
        success: function(result, status, xhr) {
            $('title').text(result.title);
            $('body').attr('data-page', result.page);
            $('.content').html(result.content);
            try
            {
                window[$('body').attr('data-page')]();
            }
            catch(err)
            {
                // Handle error(s) here
            }
        }
    });
}

//Home
function home()
{
    $('.enter').click(function(){
        $('body').removeClass('enter-mode');
    });
}

//Projects
function projects()
{
    if($('body').data('device') == 'computer')
    {
        $('.projects-container').perfectScrollbar({
            suppressScrollX: true
        });
    }
    $('.projects-box').click(function(){
        loadAjax($(this).attr('href'), 0);
        return false;
    });
}

//Project
function project()
{
    $('.project-arrow.prev').click(previuos_img);
    $('.project-arrow.next').click(next_img);
    $('.project-gallery').swipe({
        swipeLeft: function(){
            next_img();
        },
        swipeRight: function(){
            previuos_img();
        }
    });
    $(window).keyup(function(e){
        if (e.which == 37)
        {
            //left arrow
            previuos_img();
        }
        else if (e.which == 39)
        {
            //right arrow
            next_img();
        }
    });
}

function previuos_img()
{
    var cimg = $('.project-img').index($('.project-img.show')[0]);
    $('.project-img').eq(cimg).removeClass('show');
    cimg--;
    if(cimg < 0)
    {
        cimg = $('.project-img').length - 1;
    }
    $('.project-img').eq(cimg).addClass('show');
}

function next_img()
{
    var cimg = $('.project-img').index($('.project-img.show')[0]);
    $('.project-img').eq(cimg).removeClass('show');
    cimg++;
    if(cimg >= $('.project-img').length)
    {
        cimg = 0;
    }
    $('.project-img').eq(cimg).addClass('show');
}

//Awards
function awards()
{
    if($('body').data('device') == 'computer')
    {
        $('.awards').perfectScrollbar({
            suppressScrollX: true
        });
        $('.award').perfectScrollbar({
            suppressScrollX: true
        });
    }
    $('.awards-box').click(function(){
        window.history.pushState(null, null, $(this).attr('href'));
        $('body').attr('data-page', 'award');
        $('.awards-box').addClass('opacity');
        $(this).removeClass('opacity');
        return false;
    });
}

//Publications
function publications()
{
    if($('body').data('device') == 'computer')
    {
        $('.publications-container').perfectScrollbar({
            suppressScrollX: true
        });
    }
}