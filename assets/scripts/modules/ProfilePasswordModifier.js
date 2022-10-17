class ProfilePasswordModifier {
    static init() {
        var passwordFieldWrapper = $('#profile-password-field-wrapper');
        var modifyPasswordCheckbox = $('.modify-password-checkbox');
        modifyPasswordCheckbox.on('change', function(e) {
            $.ajax({
                url: modifyPasswordCheckbox.data('profile-password-field-url'),
                data: {
                    modifyPassword: modifyPasswordCheckbox.prop('checked')
                },
                success: function (html) {
                    if (!html) {
                        passwordFieldWrapper.html('');
                        passwordFieldWrapper.addClass('d-none');
                        return;
                    }
                    // Replace the current field and show
                    passwordFieldWrapper
                        .html(html)
                        .removeClass('d-none')
                }
            });
        });
    }
}

export default ProfilePasswordModifier