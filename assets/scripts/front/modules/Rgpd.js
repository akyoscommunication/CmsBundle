class Rgpd {
    static init() {
        const akyCookiesgestion = $('#akyCookiesGestion');
        if (akyCookiesgestion) {
            
            akyCookiesgestion.removeClass('hidden');
            $(window).on('scroll', function () {
                if ($(window).scrollTop() + $(window).height() == $(document).height()) {
                    akyCookiesgestion.addClass('active');
                } else {
                    akyCookiesgestion.removeClass('active');
                }
            });
            
            akyCookiesgestion.click(function () {
                tarteaucitron.userInterface.openPanel();
            })
            
            if (akyCookiesgestion.data('ua') && akyCookiesgestion.data('ua').length) {
                tarteaucitron.user.gajsUa = akyCookiesgestion.data('ua');
                tarteaucitron.user.gajsUaMore = function () { /* add here your optionnal ga.push() */
                };
                (tarteaucitron.job = tarteaucitron.job || []).push("gajsUa");
            }
            
            if (akyCookiesgestion.data('gtm') && akyCookiesgestion.data('gtm').length) {
                if(akyCookiesgestion.data('gtm').indexOf('|') > -1) {
                    const tagManagers = akyCookiesgestion.data('gtm').split('|');
                    tarteaucitron.user.multiplegoogletagmanagerId = tagManagers;
                    (tarteaucitron.job = tarteaucitron.job || []).push('multiplegoogletagmanager');
                } else {
                    tarteaucitron.user.googletagmanagerId = akyCookiesgestion.data('gtm');
                    (tarteaucitron.job = tarteaucitron.job || []).push("googletagmanager");
                }
            }
        }
    }
}

export default Rgpd