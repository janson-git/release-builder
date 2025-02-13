(function (window, document) {

    var layout   = document.getElementById('layout'),
        menu     = document.getElementById('menu'),
        logsToggleButton= document.getElementById('logs-toggle-button'),
        logsContainer= document.getElementById('logs-container');

    function toggleClass(element, className) {
        var classes = element.className.split(/\s+/),
            length = classes.length,
            i = 0;

        for(; i < length; i++) {
          if (classes[i] === className) {
            classes.splice(i, 1);
            break;
          }
        }
        // The className is not found
        if (length === classes.length) {
            classes.push(className);
        }

        element.className = classes.join(' ');
    }

    if (logsToggleButton) {
        logsToggleButton.onclick = function (e) {
            const $container = $(logsContainer);
            $container.toggle()

            if ($container.css('display') !== 'none') {
                this.innerHTML = 'Hide Debug Logs';
            } else {
                this.innerHTML = 'Show Debug Logs'
            }
        };
    }

    window.spinnerOn = function(btn) {
        $('#loader').show();
        if (btn) {
            $(btn).addClass('btn-in-action')
        }
    };

    window.spinnerOff = function(btn) {
        $('#loader').hide();
        if (btn) {
            setTimeout(
                () => {
                    $(btn).removeClass('btn-in-action');
                },
                500
            );
        }
    };

    window.$('.btn-actionable').on('click', function(el) {
        $('#loader').removeClass("hidden");
        // $(el.target).addClass('btn-in-action');
        window.spinnerOn(el.target);
    });

    window.$('.action-button').on('click', function(el) {
        $('#loader').show();
    });

}(this, this.document));
