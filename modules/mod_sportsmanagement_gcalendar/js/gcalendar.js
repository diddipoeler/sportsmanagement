(() => {
    'use strict';

    const pad = (value) => String(value).padStart(2, '0');

    const parseDate = (value) => {
        const text = String(value || '').trim();
        const dateOnly = /^(\d{4})-(\d{2})-(\d{2})$/.exec(text);

        if (dateOnly) {
            return new Date(Number(dateOnly[1]), Number(dateOnly[2]) - 1, Number(dateOnly[3]));
        }

        const date = new Date(text);
        return Number.isNaN(date.getTime()) ? null : date;
    };

    const dayKey = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;

    const stripHtml = (value) => {
        const element = document.createElement('div');
        element.innerHTML = String(value || '');
        return (element.textContent || '').trim();
    };

    const formatTitle = (date, format, config) => {
        const tokens = {
            F: config.monthNames?.[date.getMonth()] || '',
            M: config.monthNamesShort?.[date.getMonth()] || '',
            Y: String(date.getFullYear()),
            y: String(date.getFullYear()).slice(-2),
            m: pad(date.getMonth() + 1),
            n: String(date.getMonth() + 1),
            d: pad(date.getDate()),
            j: String(date.getDate()),
        };
        let result = '';
        let escaped = false;

        for (const character of String(format || 'M Y')) {
            if (escaped) {
                result += character;
                escaped = false;
                continue;
            }

            if (character === '\\') {
                escaped = true;
                continue;
            }

            result += Object.prototype.hasOwnProperty.call(tokens, character) ? tokens[character] : character;
        }

        return result;
    };

    const formatTime = (date, format) => {
        const hour24 = date.getHours();
        const hour12 = hour24 % 12 || 12;
        const tokens = {
            H: pad(hour24),
            G: String(hour24),
            h: pad(hour12),
            g: String(hour12),
            i: pad(date.getMinutes()),
            a: hour24 < 12 ? 'am' : 'pm',
            A: hour24 < 12 ? 'AM' : 'PM',
        };
        let result = '';
        let escaped = false;

        for (const character of String(format || 'g:i a')) {
            if (escaped) {
                result += character;
                escaped = false;
                continue;
            }

            if (character === '\\') {
                escaped = true;
                continue;
            }

            result += Object.prototype.hasOwnProperty.call(tokens, character) ? tokens[character] : character;
        }

        return result;
    };

    const visibleEventDates = (event) => {
        const start = parseDate(event.start);
        if (!start) {
            return [];
        }

        const dates = [dayKey(start)];
        if (!event.allDay || !event.end) {
            return dates;
        }

        const end = parseDate(event.end);
        if (!end) {
            return dates;
        }

        const cursor = new Date(start.getFullYear(), start.getMonth(), start.getDate() + 1);
        const endDay = new Date(end.getFullYear(), end.getMonth(), end.getDate());

        while (cursor < endDay && dates.length < 370) {
            dates.push(dayKey(cursor));
            cursor.setDate(cursor.getDate() + 1);
        }

        return dates;
    };

    const createEvent = (event, config) => {
        const link = document.createElement('a');
        const title = String(event.title || '').replace(/\u00a0/g, ' ').trim();
        const start = parseDate(event.start);
        const time = start && !event.allDay ? formatTime(start, config.timeFormat) : '';
        const label = title || '•';

        link.className = 'jsm-gcalendar-event';
        link.href = String(event.url || '#');
        link.textContent = time ? `${time} ${label}` : label;
        link.style.backgroundColor = String(event.color || config.eventColor || '#D0DFF1');
        link.style.borderColor = String(config.eventColor || event.color || '#135CAE');

        const description = stripHtml(event.description);
        if (description) {
            link.title = description;
        }

        return link;
    };

    const initialise = (root) => {
        const optionsKey = String(root.dataset.calendarOptionsKey || '').trim();
        const config = optionsKey && window.Joomla?.getOptions
            ? Joomla.getOptions(optionsKey, {})
            : {};

        const grid = root.querySelector('[data-calendar-grid]');
        const weekdays = root.querySelector('[data-calendar-weekdays]');
        const title = root.querySelector('[data-calendar-title]');
        const loading = root.querySelector('[data-calendar-loading]');

        if (!grid || !weekdays || !title || !loading || !config.feedUrl) {
            return;
        }

        let current = new Date();
        current = new Date(current.getFullYear(), current.getMonth(), 1);
        let requestSerial = 0;

        const renderWeekdays = () => {
            weekdays.replaceChildren();
            const names = Array.isArray(config.dayNamesMin) ? config.dayNamesMin : [];
            const weekStart = Math.max(0, Math.min(6, Number(config.weekStart) || 0));

            for (let offset = 0; offset < 7; offset += 1) {
                const item = document.createElement('div');
                const index = (weekStart + offset) % 7;
                item.className = 'jsm-gcalendar-weekday';
                item.textContent = names[index] || '';
                weekdays.append(item);
            }
        };

        const loadMonth = async () => {
            const serial = ++requestSerial;
            const weekStart = Math.max(0, Math.min(6, Number(config.weekStart) || 0));
            const first = new Date(current.getFullYear(), current.getMonth(), 1);
            const offset = (first.getDay() - weekStart + 7) % 7;
            const gridStart = new Date(first.getFullYear(), first.getMonth(), 1 - offset);
            const gridEnd = new Date(gridStart.getFullYear(), gridStart.getMonth(), gridStart.getDate() + 42);
            const requestUrl = new URL(String(config.feedUrl), document.baseURI);

            requestUrl.searchParams.set('start', String(Math.floor(gridStart.getTime() / 1000)));
            requestUrl.searchParams.set('end', String(Math.floor(gridEnd.getTime() / 1000)));
            loading.hidden = false;

            let events = [];
            try {
                const response = await fetch(requestUrl, {
                    method: 'GET',
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const payload = await response.json();
                events = Array.isArray(payload) ? payload : [];
            } catch (error) {
                events = [];
            }

            if (serial !== requestSerial) {
                return;
            }

            const eventsByDay = new Map();
            events.forEach((event) => {
                visibleEventDates(event).forEach((key) => {
                    if (!eventsByDay.has(key)) {
                        eventsByDay.set(key, []);
                    }
                    eventsByDay.get(key).push(event);
                });
            });

            title.textContent = formatTitle(first, config.titleFormat, config);
            grid.replaceChildren();
            const today = dayKey(new Date());

            for (let index = 0; index < 42; index += 1) {
                const date = new Date(gridStart.getFullYear(), gridStart.getMonth(), gridStart.getDate() + index);
                const key = dayKey(date);
                const cell = document.createElement('div');
                const number = document.createElement('span');

                cell.className = 'jsm-gcalendar-day';
                if (date.getMonth() !== first.getMonth()) {
                    cell.classList.add('is-outside');
                }
                if (key === today) {
                    cell.classList.add('is-today');
                }

                cell.dataset.date = key;
                number.className = 'jsm-gcalendar-day-number';
                number.textContent = String(date.getDate());
                cell.append(number);

                (eventsByDay.get(key) || []).forEach((event) => cell.append(createEvent(event, config)));
                grid.append(cell);
            }

            loading.hidden = true;
        };

        root.querySelectorAll('[data-calendar-action]').forEach((button) => {
            button.addEventListener('click', () => {
                const action = button.dataset.calendarAction;

                if (action === 'prev') {
                    current = new Date(current.getFullYear(), current.getMonth() - 1, 1);
                } else if (action === 'next') {
                    current = new Date(current.getFullYear(), current.getMonth() + 1, 1);
                } else if (action === 'today') {
                    const now = new Date();
                    current = new Date(now.getFullYear(), now.getMonth(), 1);
                }

                loadMonth();
            });
        });

        renderWeekdays();
        loadMonth();
    };

    const boot = () => document.querySelectorAll('[data-jsm-gcalendar]').forEach(initialise);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, {once: true});
    } else {
        boot();
    }
})();
