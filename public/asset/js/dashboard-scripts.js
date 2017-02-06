
  function setCSS() {
    var $window = $(window);
    var windowHeight = $(window).height();
    var windowWidth = $(window).width();
    $('.dashboard-page').css('min-height', (windowHeight - 55) );   
        
  }

 $(document).ready(function() {
  setCSS();
  $(window).resize(function() {
    setCSS();
  });

  });


     $(function(){
    $(".dropdown").hover(            
            function() {
                $('.dropdown-menu', this).stop( true, true ).fadeIn("fast");
                $(this).toggleClass('open');
                // $('b', this).toggleClass("caret caret-up");                
            },
            function() {
                $('.dropdown-menu', this).stop( true, true ).fadeOut("fast");
                $(this).toggleClass('open');
                // $('b', this).toggleClass("caret caret-up");                
            });
    });


$('.car-detail').slick({

    slidesToShow: 3,
  slidesToScroll: 1,
  autoplay: false,
 infinite: false,

})
    .on("mousewheel", function (event) {
        event.preventDefault();
    if (event.deltaX > 0 || event.deltaY < 0) {
        $('.slick-next').click();
    } else if (event.deltaX < 0 || event.deltaY > 0) {
        $('.slick-prev').click();
    }
});

   
    $('#collapseDiv').on('shown.bs.collapse', function () {
       $(".glyphicon").removeClass("glyphicon-folder-close").addClass("glyphicon-folder-open");
    });

    $('#collapseDiv').on('hidden.bs.collapse', function () {
       $(".glyphicon").removeClass("glyphicon-folder-open").addClass("glyphicon-folder-close");
    });



   //profile image upload preview
function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        
        reader.onload = function (e) {
            $('#profile_image_preview').attr('src', e.target.result);
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

$("#profile_img_upload_btn").change(function(){
    readURL(this);
});



