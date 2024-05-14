
function unsecuredCopyToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    let isCopied = false
    try {
        document.execCommand('copy');
        isCopied = true
    } catch (err) {
        console.error('Unable to copy to clipboard', err);
    }
    document.body.removeChild(textArea);

    return isCopied
}

Clipboard = {
    /**
     * @param text String
     */
    writeToClipboard: function (text) {
        return new Promise( (resolve, reject) => {
            // browser's Clipboard API allowed only in secured context
            // https://developer.mozilla.org/en-US/docs/Web/Security/Secure_Contexts
            // if we have unsecured context - 'create-and-copy-text-node' hack will be used
            if (window.isSecureContext) {
                navigator.clipboard.writeText(text)
                    .then(() => resolve())
                    .catch(() => reject())
            } else {
                if (unsecuredCopyToClipboard(text)) {
                    resolve()
                } else {
                    reject()
                }
            }
        })

    }
}
