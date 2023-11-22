@extends('./layout.blade.php')

@section('content')
    <div class="pure-g">
        <div class="pure-u-1">
            <section class="top-page-nav">
                <a href="/user" class="pure-button btn-secondary-outline btn-s">
                    <i class="fa-solid fa-arrow-left"></i> {{ __('back_to_profile') }}
                </a>
            </section>
        </div>
    </div>

    <div class="pure-g">
        <div class="pure-u pure-u-md-1 pure-u-lg-1-2">
            <p>{{ $msg }}</p>

            @if ($user->getAccessToken())
                <p class="text-warning">Access token is uploaded. It will be replaced on this form submitting</p>
            @endif
            <form class="pure-form pure-form-aligned" method="post" autocomplete="off">
                <fieldset class="pure-group" >
                    <input autocomplete="false" name="hidden" type="text" style="display:none;">
                    <input
                            type="text"
                            class="pure-input-1 "
                            placeholder="Pivate Access Token"
                            name="token"
                            id="token"
                            spellcheck="false"
                            style="font-size: small; font-family: monospace;">
                    <input type="hidden" id="token-expiration-date" name="expiration_date">
                    <button onclick="accessTokenHandler.checkToken(this); return false;" class="pure-button pure-input-1 btn-secondary">Check token</button>
                </fieldset>

                <div id="github-user-info" style="display: none;"></div>

                <button type="submit" class="pure-button pure-input-1 pure-button-primary">{{ __('save') }}</button>
            </form>
        </div>

        <div class="pure-u-1">
            <p id="doneLog"></p>
        </div>
    </div>

    <script>
        const accessTokenHandler = {
            checkToken: function (btn) {
                const $tokenField = $('#token')
                const token = $tokenField.val()

                if (token.length < 1) {
                    $tokenField.addClass('error')
                    return false;
                }
                $tokenField.removeClass('error')

                let _this = this
                spinnerOn(btn)
                $.post(
                    '/user/check-token',
                    {token: token},
                    function (res) {
                        $('#github-user-info').html('<span>' +
                            `<b>User: </b> ${res.name} ( ${res.login} ) ` +
                            "<br>" +
                            `<b>Expiration date: </b>${res.expiration_date}` +
                            '</span>'
                        ).show()

                        $('#token-expiration-date').val(res.expiration_date)

                        spinnerOff(btn)
                    },
                    'json',
                )
                    .fail(function (jqxhr, textStatus, error) {
                        console.log(jqxhr, textStatus, error)
                        _this.log(
                            jqxhr && jqxhr.responseJSON && jqxhr.responseJSON.error
                                ? jqxhr.responseJSON.error
                                : (textStatus + ' ' + error)
                        )
                        spinnerOff(btn)
                    });

                return false
            },
            log: function (data, el) {
                $('#doneLog').html('<span class="text-error">' + data + '</span>')
            }
        }
    </script>
@endsection
