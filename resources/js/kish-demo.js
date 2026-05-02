/**
 * صفحهٔ پرزنت «فرم جزیرهٔ کیش» — ویجت شناور + سناریوهای خطای عمدی برای نمایش proactive کمک.
 */
import { mountFloatingChatWidget } from './floating-chat';

const OFFER = 'supportai:offer-help';
const PAGE_CTX = 'supportai:page-context';

function clearScenarioUi() {
    const alertEl = document.getElementById('kish-demo-alert');
    if (alertEl) {
        alertEl.textContent = '';
        alertEl.hidden = true;
        alertEl.removeAttribute('data-error-text');
    }
    document.querySelectorAll('[data-kish-demo-field]').forEach((el) => {
        el.classList.remove('is-invalid');
        el.removeAttribute('aria-invalid');
    });
}

/**
 * @param {1|2|3} id
 */
function triggerScenario(id) {
    clearScenarioUi();

    const spec = SCENARIOS[id];
    if (!spec) return;

    const alertEl = document.getElementById('kish-demo-alert');
    if (alertEl) {
        alertEl.hidden = false;
        alertEl.textContent = spec.banner;
        alertEl.setAttribute('data-error-text', spec.banner);
    }

    for (const name of spec.invalidateNames) {
        const el = document.querySelector(`[data-kish-demo-field="${name}"]`);
        if (el) {
            el.classList.add('is-invalid');
            el.setAttribute('aria-invalid', 'true');
        }
    }

    const form = document.getElementById('kish-demo-form');
    const fd = new FormData(form);
    const snapshot = {};
    for (const [k, v] of fd.entries()) {
        if (String(k).toLowerCase().includes('passport') || String(k).toLowerCase().includes('national')) {
            snapshot[k] = v ? '(وارد شده — مقدار مخفی در پرزنت)' : '(خالی)';
        } else {
            snapshot[k] = String(v).slice(0, 80);
        }
    }

    document.dispatchEvent(
        new CustomEvent(PAGE_CTX, {
            bubbles: true,
            detail: {
                kishDemoScenarioId: id,
                kishDemoScenarioTitle: spec.title,
                kishDemoNarrative: spec.narrative,
                kishDemoResolutionHints: spec.hintsForModel,
                kishDemoFormSnapshot: snapshot,
                kishDemoFictional: true,
            },
        })
    );

    document.dispatchEvent(
        new CustomEvent(OFFER, {
            bubbles: true,
            detail: {
                source: `kish-demo/scenario-${id}`,
                message: spec.offerHelpMessage,
                silentSound: false,
            },
        })
    );
}

/** متن‌های طولانی برای مدل؛ راه‌حل واقعی در دنیای پرزنت عمداً چندلایه و اداری است */
const SCENARIOS = {
    1: {
        title: 'سناریو ۱ — مغایرت VIN و قفل ضمانت در VTMS منطقهٔ آزاد کیش',
        banner:
            'خطای VTMS-KISH-4471: وضعیت «خروج ناقص» — شناسهٔ وسیله در سامانهٔ VTMS با پروانهٔ گمرکی منطقهٔ آزاد (فرم FZ-09) هم‌خوان نیست؛ ضمانت‌نامهٔ بانکی آزادسازی نمی‌شود.',
        offerHelpMessage:
            'کاربر روی دکمهٔ سناریو ۱ زده: VTMS می‌گوید VIN با پروانه گمرکی جابه‌جاست و ضمانت آزاد نمی‌شود. زمینهٔ کامل در page_context.kishDemoNarrative است.',
        invalidateNames: ['vehicle_vin', 'customs_declaration_no'],
        narrative: `این یک دادهٔ نمایشی برای پرزنت است (نه پروندهٔ واقعی).

کاربر خودروی شخصی را برای ورود به منطقهٔ آزاد کیش ثبت کرده است. در سامانهٔ VTMS (مدیریت ناوگان منطقهٔ آزاد) پس از صدور برچسب ورود، وضعیت «خروج ناقص» مانده است. علت اعلام‌شده از سوی سامانه: «عدم تطابق VIN بین پروانهٔ گمرکی FZ-09 و رجیستری پلاک ملی». در عمل یک رقم VIN در مرحلهٔ ورودی دستی اشتباه ثبت شده و پس از اصلاح در پنجرهٔ گمرک، رکورد VTMS هنوز به نسخهٔ قدیمی قفل است و بانک ضامن (کد شعبهٔ KI-GL-02) اجازهٔ آزادسازی سپردهٔ الکترونیک را نمی‌دهد.

محدودیت‌های پرزنت: کاربر به پنل بانک دسترسی ندارد؛ فقط رهگیری عمومی دارد. باید مسیر اداری در چند سازمان موازی (گمرک منطقه، دبیرخانهٔ VTMS، بانک ضامن) با شمارهٔ درخواست و فرم مشخص توصیف شود — نه صرفاً «دوباره امتحان کنید».`,
        hintsForModel: `برای پاسخ خوب: (۱) اولویت با «ثبت مغایرت فنی» در VTMS و پیوست اسکن پروانهٔ اصلاح‌شده است؛ (۲) فرم «تأیید اصلاح شناسهٔ وسیله» کد فرضی KFZ-MR-11 را ذکر کن؛ (۳) بانک را با کد رهگیری ضمانت و SLA ۴۸–۷۲ ساعت کاری بعد از امضای گمرک قاب کن؛ (۴) اگر VTMS هنوز قفل است، مسیر شکایت مردمی سازمان منطقه را به‌عنوان گام پشتیبان بگو؛ (۵) هرگز نگو فقط «با پشتیبانی تماس بگیر» بدون مراحل مشخص.`,
    },
    2: {
        title: 'سناریو ۲ — PNR کشتی دریایی و تغییر کد ملی پس از صدور بلیط',
        banner:
            'خطای STC-SEA-882: PNR قفل شده — هویت مسافر با سامانهٔ احوال هنگام سوارشدن مغایرت دارد؛ امکان صدور بارکد سوارشدن وجود ندارد.',
        offerHelpMessage:
            'کاربر سناریو ۲ را زده: PNR کشتی قفل است و کد ملی جدید با بلیط هم‌خوان نیست. جزئیات در kishDemoNarrative.',
        invalidateNames: ['national_id', 'ferry_pnr'],
        narrative: `نمایشی برای پرزنت.

مسافر بلیط کشتی شهید حقانی را با کد ملی قدیمی خریده است. پس از خرید، در ثبت احوال کد ملی تغییر کرده (سری جدید). سامانهٔ کشتی PNR را «قفل امنیتی» کرده و اجازهٔ ویرایش آنلاین نمی‌دهد؛ در پایانهٔ بندر، بارکد سوارشدن صادر نمی‌شود و پیام «عدم تطابق هویت با PNR» می‌آید.

کاربر نمی‌تواند بلیط را کنسل کند چون کمتر از ۶ ساعت به حرکت مانده (قانون فرضی نمایش). باید مسیر اداری ترکیبی: (الف) استعلام رسمی تغییر کد ملی از درگاه ثبت احوال با کد رهگیری؛ (ب) مراجعهٔ حضوری یا درخواست الکترونیک به دفتر نمایندگی خطوط ساحلی با فرم «تطبیق هویت پس از صدور بلیط»؛ (ج) در صورت رد، مسیر استرداد جزئی با کمیسیون انضباطی حمل‌ونقل دریایی فرضی.

راه‌حل سادهٔ «بلیط جدید بخر» کافی نیست چون صندلی تمام شده است.`,
        hintsForModel: `پاسخ باید اولویت‌بندی زمانی (deadline حرکت) داشته باشد؛ فرم‌ها و کدهای فرضی واضح بده؛ اگر داده ناقص است بگو دقیقاً چه سندی لازم است؛ به فارسی رسمی و قابل اجرا در پرزنت.`,
    },
    3: {
        title: 'سناریو ۳ — مغایرت سن کودک بین PMS هتل و استعلام مرزی (ICAO MRZ)',
        banner:
            'خطای ICAO-HOTEL-AX-03: رزرو هتل تأیید شده است اما استعلام مرزی رد می‌کند — «سن مسافر وابسته با MRZ پاسپورت هم‌خوان نیست».',
        offerHelpMessage:
            'کاربر سناریو ۳ را زده: هتل تأیید دارد ولی مرز سن کودک را رد می‌کند. جزئیات در kishDemoNarrative.',
        invalidateNames: ['child_dob', 'hotel_confirmation'],
        narrative: `نمایشی برای پرزنت.

خانوادهٔ سه‌نفره رزرو هتل ۵ ستاره را با تاریخ تولد کودک مطابق پاسپورت قدیمی (MRZ) انجام داده‌اند. بین زمان رزرو و سفر، کودک تولدش به تقویم شمسی/میلادی دیگری «مرز سنی» را رد کرده (مثلاً از ۱۱ سال و ۱۱ ماه به ۱۲ سال تمام) و سیاست هتل برای تخت اضافه و سیاست گذرنامهٔ فرودگاهی متفاوت است. PMS هتل هنوز سن قدیمی را نشان می‌دهد چون OTA واسط کشیده است.

گیت مرزی می‌گوید MRZ با دادهٔ رزرو ICAO-HOTEL-AX-03 مغایرت دارد. هتل می‌گوید پیش‌پرداخت غیرقابل‌استرداد است اگر تاریخ تولد عوض شود باید قرارداد جدید با نرخ روز صادر شود.

راه‌حل باید شامل: به‌روزرسانی MRZ در سیستم OTA، نامهٔ هماهنگی هتل با مهر امور بین‌الملل، و در صورت نیاز اصلاح نوع بلیط هوایی داخلی برای سنین متفاوت باشد — نه فقط «با هتل تماس بگیر».`,
        hintsForModel: `مراحل را با وابستگی زمانی بنویس (اول مرز یا اول هتل؟). ذکر کن اگر OTA قفل است مسیر پشتیبان دستی چیست. از اصطلاحات MRZ/PMS به‌صورت خلاصه و درست استفاده کن.`,
    },
};

function init() {
    mountFloatingChatWidget();

    document.getElementById('kish-demo-s1')?.addEventListener('click', () => triggerScenario(1));
    document.getElementById('kish-demo-s2')?.addEventListener('click', () => triggerScenario(2));
    document.getElementById('kish-demo-s3')?.addEventListener('click', () => triggerScenario(3));
    document.getElementById('kish-demo-clear')?.addEventListener('click', () => {
        clearScenarioUi();
        document.dispatchEvent(new CustomEvent(PAGE_CTX, { bubbles: true, detail: { kishDemoScenarioId: null } }));
    });

    window.__kishDemoTriggerScenario = triggerScenario;
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
