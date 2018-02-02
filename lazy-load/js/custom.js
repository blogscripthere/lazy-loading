(function($) {
    //library check
    if(lazy.library =='lazy'){
        $(".lazy").lazy();
    }else{
        $(".lazy").lazyLoadXT();
    }
})(jQuery);