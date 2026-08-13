import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';

Alpine.plugin(intersect);

Alpine.data('weeklyWins', (initialCounts = [], dates = [], initialTimelineView = 'daily') => ({
    counts: initialCounts.map((count) => Math.max(0, Number.parseInt(count, 10) || 0)),
    dates,
    timelineView: initialTimelineView,

    get total() {
        return this.counts.reduce((total, count) => total + count, 0);
    },

    add(detail) {
        const index = this.resolveDayIndex(detail);

        if (index === null) {
            return;
        }

        this.counts[index] += this.amount(detail, 1);
    },

    remove(detail) {
        const index = this.resolveDayIndex(detail);

        if (index === null) {
            return;
        }

        this.counts[index] = Math.max(0, this.counts[index] - this.amount(detail, 1));
    },

    setTotal(detail) {
        const index = this.resolveDayIndex(detail);
        const total = Number(detail?.total ?? detail?.count);

        if (index === null || !Number.isFinite(total)) {
            return;
        }

        this.counts[index] = Math.max(0, Math.floor(total));
    },

    hasWins(index) {
        return this.counts[index] > 0;
    },

    displayBars(index) {
        return Math.min(this.counts[index], 5);
    },

    resolveDayIndex(detail = {}) {
        const day = Number(detail.day);

        if (Number.isInteger(day) && day >= 0 && day < this.counts.length) {
            return day;
        }

        const index = this.dates.indexOf(detail.date);

        return index === -1 ? null : index;
    },

    amount(detail = {}, fallback) {
        const amount = Number(detail.amount ?? fallback);

        return Number.isFinite(amount) && amount > 0 ? Math.floor(amount) : fallback;
    },
}));

Alpine.data('winCapture', (timezone) => ({
    content: '',
    cooldown: 0,
    cooldownTimer: null,
    error: '',
    submitting: false,

    get buttonLabel() {
        if (this.submitting) {
            return 'Saving...';
        }

        return this.cooldown > 0 ? `Wait ${this.cooldown}s` : 'Add a win';
    },

    async submit() {
        if (this.submitting || this.cooldown > 0) {
            return;
        }

        this.error = '';
        this.submitting = true;

        try {
            const response = await fetch(this.$el.action, {
                method: 'POST',
                body: new FormData(this.$el),
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const payload = await response.json();

            if (!response.ok) {
                const retryAfter = Number.parseInt(response.headers.get('Retry-After'), 10);

                if (response.status === 429 && Number.isFinite(retryAfter) && retryAfter > 0) {
                    this.startCooldown(retryAfter);
                }

                this.error = payload.errors?.content?.[0] ?? payload.message ?? 'We could not save that win. Please try again.';

                return;
            }

            this.content = '';
            this.startCooldown(3);
            window.dispatchEvent(new CustomEvent('weekly-wins:add', {
                detail: {
                    date: this.dateKey(payload.win.created_at),
                    amount: 1,
                    win: payload.win,
                },
            }));
            this.$nextTick(() => this.$refs.content.focus());
        } catch {
            this.error = 'We could not save that win. Please try again.';
        } finally {
            this.submitting = false;
        }
    },

    dateKey(timestamp) {
        const parts = new Intl.DateTimeFormat('en-US', {
            day: '2-digit',
            month: '2-digit',
            timeZone: timezone,
            year: 'numeric',
        }).formatToParts(new Date(timestamp));
        const values = Object.fromEntries(parts.map(({ type, value }) => [type, value]));

        return `${values.year}-${values.month}-${values.day}`;
    },

    startCooldown(seconds) {
        this.cooldown = Math.max(this.cooldown, Math.ceil(seconds));

        if (this.cooldown <= 0) {
            return;
        }

        window.clearInterval(this.cooldownTimer);
        this.cooldownTimer = window.setInterval(() => {
            this.cooldown = Math.max(0, this.cooldown - 1);

            if (this.cooldown === 0) {
                window.clearInterval(this.cooldownTimer);
                this.cooldownTimer = null;
            }
        }, 1000);
    },
}));

Alpine.data('copyAgentSetup', () => ({
    claudeSetupOpen: false,
    codexSetupOpen: false,
    labels: {
        claude: 'Copy command',
        codex: 'Copy commands',
        prompt: 'Copy prompt',
        server: 'Copy',
    },
    resetTimer: null,

    async copy(value, target) {
        try {
            await navigator.clipboard.writeText(value);
            this.labels[target] = 'Copied';
        } catch {
            const field = this.$refs[{
                claude: 'claudeCommand',
                codex: 'codexCommands',
                prompt: 'reflectionPrompt',
                server: 'serverUrl',
            }[target]];

            field.focus();
            field.select();
            this.labels[target] = 'Select to copy';
        }

        window.clearTimeout(this.resetTimer);
        this.resetTimer = window.setTimeout(() => {
            this.labels[target] = {
                claude: 'Copy command',
                codex: 'Copy commands',
                prompt: 'Copy prompt',
                server: 'Copy',
            }[target];
        }, 2500);
    },
}));

Alpine.data('winTimeline', (endpoint, initialGroups, initialNextBefore, initialView = 'daily') => ({
    dayLabels: ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'],
    endpoint,
    error: '',
    failed: false,
    groups: initialGroups,
    loading: false,
    nextBefore: initialNextBefore,
    view: initialView,

    get hasMore() {
        return this.nextBefore !== null;
    },

    formatDate(date) {
        return new Intl.DateTimeFormat(undefined, {
            day: 'numeric',
            month: 'short',
            timeZone: 'UTC',
            weekday: 'long',
        }).format(new Date(`${date}T00:00:00Z`));
    },

    formatWeekLabel(date) {
        return `Week of ${new Intl.DateTimeFormat(undefined, {
            day: 'numeric',
            month: 'short',
            timeZone: 'UTC',
        }).format(this.dateFromKey(date))}`;
    },

    formatWeekRange(date) {
        const start = this.dateFromKey(date);
        const end = new Date(start);
        end.setUTCDate(end.getUTCDate() + 6);
        const formatter = new Intl.DateTimeFormat(undefined, {
            day: 'numeric',
            month: 'short',
            timeZone: 'UTC',
        });

        if (start.getUTCMonth() === end.getUTCMonth()) {
            const month = new Intl.DateTimeFormat(undefined, {
                month: 'short',
                timeZone: 'UTC',
            }).format(start);

            return `${month} ${start.getUTCDate()}–${end.getUTCDate()}`;
        }

        return `${formatter.format(start)} – ${formatter.format(end)}`;
    },

    get weeklyGroups() {
        return this.groups.reduce((weeks, group) => {
            const start = this.weekStart(group.date);
            let week = weeks.find((item) => item.start === start);

            if (week === undefined) {
                week = {
                    days: this.dayLabels.map((label, index) => ({ index, label, total: 0 })),
                    groups: [],
                    start,
                    total: 0,
                };
                weeks.push(week);
            }

            const day = this.dayIndex(group.date);
            const winCount = group.wins.length;

            week.days[day].total += winCount;
            week.groups.push(group);
            week.total += winCount;

            return weeks;
        }, []);
    },

    async setView(view) {
        if (view === this.view) {
            return;
        }

        const previousView = this.view;

        this.view = view;
        window.dispatchEvent(new CustomEvent('wins-timeline:view-changed', { detail: { view } }));

        try {
            const formData = new FormData(this.$refs.viewPreferenceForm);

            formData.set('_method', 'PUT');
            formData.set('view', view);

            const response = await fetch(this.$refs.viewPreferenceForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error('Unable to save the timeline view.');
            }
        } catch {
            this.view = previousView;
            this.error = 'The timeline view could not be saved. Please try again.';
            window.dispatchEvent(new CustomEvent('wins-timeline:view-changed', { detail: { view: previousView } }));
        }
    },

    add(detail) {
        const date = detail?.date;
        const win = detail?.win;

        if (typeof date !== 'string' || win === undefined || typeof win.id !== 'number') {
            return;
        }

        const currentGroup = this.groups[0];

        if (currentGroup === undefined || date > currentGroup.date) {
            this.groups.unshift({ date, wins: [win] });

            return;
        }

        if (date !== currentGroup.date || currentGroup.wins.some((item) => item.id === win.id)) {
            return;
        }

        currentGroup.wins.unshift(win);
    },

    dateFromKey(date) {
        return new Date(`${date}T00:00:00Z`);
    },

    dayIndex(date) {
        return (this.dateFromKey(date).getUTCDay() + 6) % 7;
    },

    displayBars(total) {
        return Math.min(total, 5);
    },

    weekStart(date) {
        const start = this.dateFromKey(date);
        start.setUTCDate(start.getUTCDate() - this.dayIndex(date));

        return start.toISOString().slice(0, 10);
    },

    async loadMore() {
        if (!this.hasMore || this.loading || this.failed) {
            return;
        }

        this.loading = true;
        this.error = '';

        try {
            const url = new URL(this.endpoint, window.location.origin);
            url.searchParams.set('before', this.nextBefore);

            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error('Unable to load earlier wins.');
            }

            const payload = await response.json();

            this.groups.push(...payload.groups);
            this.nextBefore = payload.next_before;
        } catch {
            this.failed = true;
            this.error = 'Earlier wins could not be loaded. Refresh the page to try again.';
        } finally {
            this.loading = false;
        }
    },
}));

Alpine.data('backToTop', () => ({
    visible: false,

    updateVisibility() {
        this.visible = window.scrollY > 600;
    },

    scrollToTop() {
        window.scrollTo({
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
            top: 0,
        });
    },
}));

Alpine.data('colophon', () => ({
    open: false,

    close() {
        this.open = false;
    },
}));

window.Alpine = Alpine;
Alpine.start();

const timezoneForm = document.querySelector('[data-timezone-form]');
const registrationForm = document.querySelector('[data-registration-form]');

if (registrationForm instanceof HTMLFormElement) {
    const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    const field = registrationForm.querySelector('[data-timezone-field]');

    if (timezone && field instanceof HTMLInputElement && field.value === 'UTC') {
        field.value = timezone;
    }
}

if (timezoneForm instanceof HTMLFormElement) {
    const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    const field = timezoneForm.elements.namedItem('timezone');

    if (timezone && field instanceof HTMLInputElement && field.value !== timezone) {
        field.value = timezone;

        fetch(timezoneForm.action, {
            method: 'POST',
            body: new FormData(timezoneForm),
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
    }
}
