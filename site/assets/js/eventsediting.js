function jsmResolveElement(elementOrId) {
    if (typeof elementOrId === 'string') {
        return document.getElementById(elementOrId);
    }

    return elementOrId || null;
}

function jl_load_new_match_events(sender, targetcontainer) {
    const from = jsmResolveElement(sender);
    const to = jsmResolveElement(targetcontainer);

    if (!from || !to || !checkforchanges()) {
        return false;
    }

    const changesCheck = document.getElementById('changes_check');

    if (changesCheck) {
        changesCheck.value = 0;
    }

    if (from.selectedIndex !== 0) {
        jl_ajaxLoad(from.value, to);
    } else {
        to.replaceChildren();
    }

    return true;
}

function checkforchanges() {
    const changesCheck = document.getElementById('changes_check');

    if (changesCheck && Number(changesCheck.value) > 0) {
        if (!window.confirm('You made roster changes and did not save... ARE YOU REALLY SURE?')) {
            return false;
        }
    }

    if (changesCheck) {
        changesCheck.value = 0;
    }

    return true;
}

function move(fbox, tbox) {
    const fromOptions = [];
    const toOptions = [];
    const lookup = {};

    for (let i = 0; i < tbox.options.length; i += 1) {
        lookup[tbox.options[i].text] = tbox.options[i].value;
        toOptions.push(tbox.options[i].text);
    }

    for (let i = 0; i < fbox.options.length; i += 1) {
        lookup[fbox.options[i].text] = fbox.options[i].value;

        if (fbox.options[i].selected && fbox.options[i].value !== '') {
            toOptions.push(fbox.options[i].text);
        } else {
            fromOptions.push(fbox.options[i].text);
        }
    }

    fbox.length = 0;
    tbox.length = 0;

    fromOptions.forEach((text, index) => {
        fbox[index] = new Option(text, lookup[text]);
    });

    toOptions.forEach((text, index) => {
        tbox[index] = new Option(text, lookup[text]);
    });
}

function selectAll(box) {
    for (let i = 0; i < box.length; i += 1) {
        box[i].selected = true;
    }
}

window.jl_ajaxPost = function (frmName, el) {
    const form = jsmResolveElement(frmName);
    const target = jsmResolveElement(el);
    const log = document.getElementById('log_res');

    if (!form) {
        return false;
    }

    if (log) {
        log.classList.add('ajax-loading');
    }

    const method = (form.method || 'POST').toUpperCase();
    let requestUrl = form.action || window.location.href;
    const requestOptions = {
        method,
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
    };

    if (method === 'GET') {
        const url = new URL(requestUrl, window.location.href);
        const params = new URLSearchParams(new FormData(form));

        params.forEach((value, key) => url.searchParams.append(key, value));
        requestUrl = url.toString();
    } else {
        requestOptions.body = new FormData(form);
    }

    fetch(requestUrl, requestOptions)
        .then((response) => {
            if (!response.ok) {
                throw new Error(`Request failed with status ${response.status}`);
            }

            return response.text();
        })
        .then((html) => {
            if (target) {
                target.innerHTML = html;
            }
        })
        .catch((error) => {
            console.error('SportsManagement event form request failed.', error);
        })
        .finally(() => {
            if (log) {
                log.classList.remove('ajax-loading');
            }

            const guestTeam = document.getElementById('guestteam');
            const homeTeam = document.getElementById('hometeam');

            if (guestTeam) {
                guestTeam.disabled = false;
            }

            if (homeTeam) {
                homeTeam.disabled = false;
            }

            form.reset();
        });

    return false;
};

window.jl_ajaxLoad = function (url, el) {
    const target = jsmResolveElement(el);
    const log = document.getElementById('log_res');

    if (!target) {
        return false;
    }

    target.replaceChildren();

    if (log) {
        log.replaceChildren();
        log.classList.add('ajax-loading');
    }

    fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error(`Request failed with status ${response.status}`);
            }

            return response.text();
        })
        .then((html) => {
            target.innerHTML = html;
        })
        .catch((error) => {
            console.error('SportsManagement event request failed.', error);
        })
        .finally(() => {
            if (log) {
                log.classList.remove('ajax-loading');
            }
        });

    return false;
};

function moveOptionUp(selectId) {
    const selectList = document.getElementById(selectId);

    if (!selectList) {
        return false;
    }

    const selectOptions = selectList.getElementsByTagName('option');

    for (let i = 1; i < selectOptions.length; i += 1) {
        const option = selectOptions[i];

        if (option.selected) {
            selectList.insertBefore(option, selectOptions[i - 1]);
            return true;
        }
    }

    return false;
}

function moveOptionDown(selectId) {
    const selectList = document.getElementById(selectId);

    if (!selectList) {
        return false;
    }

    const selectOptions = selectList.getElementsByTagName('option');

    for (let i = 0; i < selectOptions.length - 1; i += 1) {
        const option = selectOptions[i];

        if (option.selected) {
            selectList.insertBefore(selectOptions[i + 1], option);
            return true;
        }
    }

    return false;
}
