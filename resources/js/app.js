import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';

Alpine.plugin(intersect);

Alpine.data('weeklyWins', (initialCounts = [], dates = []) => ({
    counts: initialCounts.map((count) => Math.max(0, Number.parseInt(count, 10) || 0)),
    dates,

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

Alpine.data('winTimeline', (endpoint, initialNextBefore) => ({
    endpoint,
    error: '',
    failed: false,
    groups: [],
    loading: false,
    nextBefore: initialNextBefore,

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
