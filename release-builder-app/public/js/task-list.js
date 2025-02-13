(function (window, document) {
    let $taskListButton = $('#set-task-list-button')
    let $taskListWrapper = $('#task-list-wrapper')
    let $parseLinksButton = $('#task-list-parse-button')
    let $taskListTextarea = $('#task-list-textarea')

    const $branchesListFilter = $('#branches-list-filter')

    if ($taskListButton) {
        $taskListButton.on('click', function () {
            $taskListWrapper.toggle(400);
        })
    }

    if ($parseLinksButton) {
        $parseLinksButton.on('click', function () {
            const regexpList = [
                RegExp(/#(task-\d+)-.*/), // Buildstack task link
            ];

            // 1. get task list from textarea
            let text = $taskListTextarea.val()
            let list = text.split('\n').filter((item) => item.length > 0)

            // 2. parse tasks from list
            let filterValues = list.map(function (link) {
                let value = null
                regexpList.forEach(function (regexp) {
                    let match = regexp.exec(link)
                    if (match) {
                        value = match[1]
                    }
                })
                return value
            });

            if (filterValues.length < 1) {
                return
            }

            // add to branches filter input without doubles
            let value = $branchesListFilter.val();
            filterValues.unshift(...value.split(' '))

            filterValues = [...new Set(filterValues)];
            $branchesListFilter.val(filterValues.join(' '))
            $branchesListFilter.trigger('keyup')
        })
    }
}(window, window.document));
