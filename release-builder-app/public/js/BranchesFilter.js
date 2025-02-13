const BranchesFilter = {
    inputFieldId: 'branches-list-filter',
    itemsClassName: 'branches-item',
    chargedFilter: null,

    init () {
        const $filterInput = $('#' + this.inputFieldId);
        if ($filterInput.length === 0) {
            return;
        }

        const filterName = $filterInput.data('search-id');
        if (!filterName) {
            throw new Error('You should set \'data-search-id\' for branch filter input!')
        }

        this.chargedFilter = {
            filterName: filterName,
            $input: $filterInput,
            value: '',
            items: $('.' + this.itemsClassName),
            version: 0,

            filter: function () {
                let self = this
                let search = self.$input.val().trim()
                localStorage.setItem(filterName, search);

                let curVersion = ++self.version;
                let searchArray = search.split(' ').map(function (val) {
                    return new RegExp(val.trim(), 'ig');
                });

                let text;
                let line;
                let matched = false;

                self.items.each(function (idx, obj) {
                    if (curVersion !== self.version) {
                        return;
                    }
                    line = $(obj);
                    text = line.find('label').text().trim();
                    matched = false;
                    let lineMatched = false;

                    for (let id in searchArray) {
                        lineMatched = (text.match(searchArray[id]) || line.find('.checkbox-item:checked').length);
                        matched = matched || lineMatched;
                    }

                    if (matched) {
                        line.removeClass('hidden');
                    } else {
                        line.addClass('hidden');
                    }
                })
            }
        }

        // still keep sandboxes branches in localstorage
        let filterVal = localStorage.getItem(filterName) || $filterInput.val()
        this.chargedFilter.$input.val(filterVal);
    },

    filter() {
        if (this.chargedFilter === null) {
            this.init()
        }

        this.chargedFilter && this.chargedFilter.filter()
    }
};

(function () {
    BranchesFilter.filter()
})()
