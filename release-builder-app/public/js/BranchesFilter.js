const BranchesFilter = {
    inputFieldId: 'branches-list-filter',
    itemsClassName: 'branches-item',

    init (filterName) {
        if (!filterName) {
            throw new Error('Filter name required!')
        }

        const filter = {
            filterName: filterName,
            $input: $('#' + this.inputFieldId),
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

                filter.items.each(function (idx, obj) {
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

        filter.$input.val(localStorage.getItem(filterName));
        filter.filter()

        return filter
    },
}

