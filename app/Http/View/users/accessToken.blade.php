@extends('./layout.blade.php')

@section('content')
    <p class="mt-4">{{ $msg }}</p>

    @if ($user->getAccessToken())
        <p class="mt-2 text-warning">Access token is uploaded. It will be replaced on this form submitting</p>
    @endif

    <form class="mt-2" method="post" autocomplete="off">
        <input autocomplete="false" name="hidden" type="text" style="display:none;">
        <div class="mt-4 flex justify-left items-center">
            <label for="name">GitHub Token</label>
            <input
                type="text"
                class="ml-4 w-72 border-b border-b-gray-400 focus:border-b-black focus:outline-none"
                name="token"
                id="token"
                spellcheck="false"
                style="font-size: small; font-family: monospace;"
            >
        </div>

        <input type="hidden" id="token-expiration-date" name="expiration_date">

        <div id="github-user-info" style="display: none;"></div>

        <div class="flex justify-start mt-4">
            <button type="submit" class="block px-4 py-2 rounded border border-blue-400 text-blue-400 hover:bg-blue-400 hover:text-white">{{ __('save') }}</button>
            <button onclick="accessTokenHandler.checkToken(this, event); return false;" class="ml-4 inline-block px-4 py-2 rounded border border-green-400 text-green-400 hover:bg-green-400 hover:text-white">Check</button>
        </div>
    </form>

    <div class="mt-2">
        <p id="doneLog"></p>
    </div>

    <script>
        const accessTokenHandler = {
            checkToken: function (btn, e) {
                e.stopPropagation();
                e.preventDefault();
                console.log('yes');
                const $tokenField = $('#token')
                const token = $tokenField.val()

                if (token.length < 1) {
                    $tokenField.addClass('error')
                    return false;
                }
                $tokenField.removeClass('error')

                let _this = this
                // spinnerOn(btn)
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
                        // spinnerOff(btn)
                    });

                return false
            },
            log: function (data, el) {
                $('#doneLog').html('<span class="text-error">' + data + '</span>')
            }
        }
    </script>
@endsection
