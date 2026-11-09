/** Run with Playwright available: node tests/time-slot-availability.cjs */
const assert = require('node:assert/strict');
const path = require('node:path');
const { chromium } = require('playwright');

class TimeSlotPage {
    constructor(page) { this.page = page; }
    async load() {
        await this.page.setContent('<input id="rbfw_buffer_time" value="2"><select id="pickup_time"></select><div id="calendar"></div>');
        await this.page.addScriptTag({ path: path.resolve(__dirname, '../../../../wp-includes/js/jquery/jquery.min.js') });
        await this.page.evaluate(() => {
            window.rbfw_js_variables = { timeFormat: 'H:i' };
            window.rbfw_translation = { pickup_time: 'Pickup Time', return_time: 'Return Time' };
        });
        await this.page.addScriptTag({ path: path.resolve(__dirname, '../assets/mp_script/rbfw_script.js') });
    }
    async slots(day, times, specific, calendar) {
        return this.page.evaluate(({ day, times, specific, calendar }) => {
            // Deliberately cross midnight with a two-hour pickup buffer.
            window.rbfw_site_now = () => new Date(2030, 0, 1, 23, 30);
            const schedule = specific ? [{ start_date: day, end_date: day, available_time: times }] : [];
            getAvailableTimes(schedule, day, times, calendar ? 'calendar' : 'pickup_time', calendar ? 'calendar' : null);
            const selector = calendar ? '#calendar a[data-time]' : '#pickup_time option[value]:not([value=""])';
            return Array.from(document.querySelectorAll(selector), slot => ({
                time: calendar ? slot.dataset.time : slot.value,
                blocked: calendar ? slot.classList.contains('rbfw_bikecarsd_time_disable') : slot.disabled,
                text: slot.textContent,
            }));
        }, { day, times, specific, calendar });
    }
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    let checks = 0;
    try {
        await Promise.all(['UTC', 'America/Los_Angeles', 'Asia/Tokyo'].map(async timezoneId => {
            const context = await browser.newContext({ timezoneId });
            await context.tracing.start({ screenshots: true, snapshots: true });
            const page = await context.newPage();
            try {
                const widget = new TimeSlotPage(page);
                await widget.load();
                for (const calendar of [false, true]) {
                    for (const specific of [false, true]) {
                        const today = await widget.slots('2030-01-01', ['22:00', '23:45'], specific, calendar);
                        assert.deepEqual(today.map(slot => slot.blocked), [true, true], `${timezoneId}: today's slots stay blocked across midnight`);
                        checks++;
                        const tomorrow = await widget.slots('2030-01-02', ['00:30', '02:00'], specific, calendar);
                        assert.deepEqual(tomorrow.map(slot => slot.blocked), [true, false], `${timezoneId}: tomorrow's slots respect the buffer`);
                        checks++;
                        const later = await widget.slots('2030-01-03', [
                            { time: '2:00 PM', status: 'enabled' }, { time: '09:00', status: 'enabled' },
                            { time: '10:00', status: 'disabled' }, { time: 'bad', status: 'enabled' },
                        ], specific, calendar);
                        assert.deepEqual(later.map(slot => slot.time), ['09:00', '2:00 PM'], `${timezoneId}: old/new formats, sorting and disabled rows`);
                        assert(later.every(slot => !slot.blocked && !slot.text.includes('NaN')));
                        checks += 2;
                    }
                }
            } catch (error) {
                await page.screenshot({ path: `/tmp/rbfw-time-slots-${timezoneId.replaceAll('/', '-')}.png` });
                await context.tracing.stop({ path: `/tmp/rbfw-time-slots-${timezoneId.replaceAll('/', '-')}.zip` });
                throw error;
            } finally {
                await context.close();
            }
        }));
        console.log(`PASS ${checks} browser checks`);
    } finally {
        await browser.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
