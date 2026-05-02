<!DOCTYPE html>
<html lang="fa" dir="rtl" data-support-ai-page="kish-demo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پرزنت — فرم نمونهٔ سفر به کیش</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    @vite(['resources/js/kish-demo.js'])
    <style>
        :root {
            --kd-bg: #f0fdfa;
            --kd-card: #fff;
            --kd-border: #ccfbf1;
            --kd-accent: #0d9488;
            --kd-text: #134e4a;
            --kd-muted: #5b7c78;
            --kd-danger-bg: #fef2f2;
            --kd-danger: #b91c1c;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Vazirmatn', 'Segoe UI', Tahoma, sans-serif;
            background: var(--kd-bg);
            color: var(--kd-text);
            line-height: 1.65;
            padding: 1.25rem 1rem 5rem;
        }
        .kd-wrap { max-width: 920px; margin: 0 auto; }
        .kd-hero {
            text-align: center;
            margin-bottom: 1.75rem;
        }
        .kd-hero h1 { font-size: 1.45rem; margin: 0 0 .5rem; font-weight: 700; }
        .kd-hero p { margin: 0; color: var(--kd-muted); font-size: .9rem; }
        .kd-badge {
            display: inline-block;
            background: #ccfbf1;
            color: #0f766e;
            font-size: .72rem;
            padding: .2rem .55rem;
            border-radius: 999px;
            margin-bottom: .5rem;
            font-weight: 600;
        }
        .kd-form {
            background: var(--kd-card);
            border-radius: 16px;
            border: 1px solid var(--kd-border);
            box-shadow: 0 8px 28px rgba(13, 148, 136, 0.08);
            padding: 1.25rem 1.35rem;
        }
        fieldset {
            border: 1px solid var(--kd-border);
            border-radius: 12px;
            margin: 0 0 1.1rem;
            padding: .85rem 1rem 1rem;
        }
        legend {
            font-weight: 700;
            font-size: .88rem;
            padding: 0 .35rem;
            color: var(--kd-accent);
        }
        .kd-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: .75rem 1rem;
        }
        label.kd-l {
            display: flex;
            flex-direction: column;
            gap: .25rem;
            font-size: .8rem;
            font-weight: 600;
            color: #115e59;
        }
        .kd-l input, .kd-l select, .kd-l textarea {
            font: inherit;
            padding: .45rem .55rem;
            border: 1px solid #99f6e4;
            border-radius: 8px;
            background: #fafafa;
        }
        .kd-l textarea { min-height: 64px; resize: vertical; }
        .kd-l input.is-invalid, .kd-l select.is-invalid, .kd-l textarea.is-invalid {
            border-color: #f87171;
            background: #fff7f7;
        }
        .kd-scenarios {
            display: flex;
            flex-wrap: wrap;
            gap: .6rem;
            justify-content: center;
            margin: 1.25rem 0 1rem;
        }
        .kd-scenarios button {
            font: inherit;
            cursor: pointer;
            border: none;
            border-radius: 10px;
            padding: .55rem 1rem;
            font-weight: 600;
            font-size: .82rem;
        }
        .kd-scenarios .kd-s1 { background: #0d9488; color: #fff; }
        .kd-scenarios .kd-s2 { background: #0e7490; color: #fff; }
        .kd-scenarios .kd-s3 { background: #155e75; color: #fff; }
        .kd-scenarios .kd-clear {
            background: #e2e8f0;
            color: #334155;
        }
        .kd-scenarios button:hover { filter: brightness(1.05); }
        #kish-demo-alert[role="alert"] {
            margin: 0 0 1rem;
            padding: .75rem 1rem;
            border-radius: 10px;
            background: var(--kd-danger-bg);
            color: var(--kd-danger);
            font-size: .85rem;
            font-weight: 600;
            border: 1px solid #fecaca;
        }
        #kish-demo-alert[hidden] { display: none !important; }
        .kd-note {
            font-size: .78rem;
            color: var(--kd-muted);
            margin-top: 1rem;
            padding: .75rem;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px dashed #cbd5e1;
        }
    </style>
</head>
<body>
    <div class="kd-wrap">
        <header class="kd-hero">
            <span class="kd-badge">صفحهٔ آزمایشی پرزنت — دادهٔ ساختگی</span>
            <h1>فرم جامع «ثبت درخواست سفر و اقامت در جزیرهٔ کیش»</h1>
            <p>فرم را پر کنید؛ سپس یکی از سه سناریوی خطا را بزنید تا ویجت چت پیشنهاد کمک دهد و با زمینهٔ کامل به دستیار وصل شوید.</p>
        </header>

        <div class="kd-scenarios">
            <button type="button" class="kd-s1" id="kish-demo-s1">سناریو ۱ — VTMS و ضمانت خودرو</button>
            <button type="button" class="kd-s2" id="kish-demo-s2">سناریو ۲ — PNR کشتی و کد ملی</button>
            <button type="button" class="kd-s3" id="kish-demo-s3">سناریو ۳ — سن کودک و هتل/مرز</button>
            <button type="button" class="kd-clear" id="kish-demo-clear">پاک کردن خطا</button>
        </div>

        <form class="kd-form" id="kish-demo-form" onsubmit="return false;">
            <div id="kish-demo-alert" role="alert" hidden></div>

            <fieldset>
                <legend>۱ — هویت و تماس</legend>
                <div class="kd-grid">
                    <label class="kd-l">نام و نام خانوادگی
                        <input name="full_name" data-kish-demo-field="full_name" value="نمونهٔ پرزنت" autocomplete="name">
                    </label>
                    <label class="kd-l">کد ملی
                        <input name="national_id" data-kish-demo-field="national_id" value="0012345678" inputmode="numeric" maxlength="10">
                    </label>
                    <label class="kd-l">شمارهٔ موبایل
                        <input name="mobile" value="09120000000" inputmode="tel">
                    </label>
                    <label class="kd-l">ایمیل
                        <input name="email" type="email" value="demo@example.com" autocomplete="email">
                    </label>
                </div>
            </fieldset>

            <fieldset>
                <legend>۲ — پاسپورت و تاریخ تولد</legend>
                <div class="kd-grid">
                    <label class="kd-l">شمارهٔ پاسپورت
                        <input name="passport_no" data-kish-demo-field="passport_no" value="A12345678" autocomplete="off">
                    </label>
                    <label class="kd-l">تاریخ تولد (میلادی)
                        <input name="dob_gregorian" type="date" value="1990-05-20">
                    </label>
                    <label class="kd-l">تاریخ انقضای پاسپورت
                        <input name="passport_expiry" type="date" value="2030-01-01">
                    </label>
                </div>
            </fieldset>

            <fieldset>
                <legend>۳ — پنجرهٔ سفر</legend>
                <div class="kd-grid">
                    <label class="kd-l">تاریخ ورود به کیش
                        <input name="arrival_date" type="date">
                    </label>
                    <label class="kd-l">تاریخ خروج
                        <input name="departure_date" type="date">
                    </label>
                    <label class="kd-l">هدف سفر
                        <select name="trip_purpose">
                            <option>تفریح</option>
                            <option>کسب‌وکار</option>
                            <option>رویداد / همایش</option>
                            <option selected>ترکیبی</option>
                        </select>
                    </label>
                    <label class="kd-l">بندر / نقطهٔ ورود ترجیحی
                        <select name="entry_point">
                            <option>فرودگاه بین‌المللی کیش</option>
                            <option selected>بندرگاه شهید حقانی</option>
                            <option>سایر</option>
                        </select>
                    </label>
                </div>
            </fieldset>

            <fieldset>
                <legend>۴ — حمل‌ونقل دریایی / هوایی</legend>
                <div class="kd-grid">
                    <label class="kd-l">PNR یا کد رزرو کشتی
                        <input name="ferry_pnr" data-kish-demo-field="ferry_pnr" value="DEMO-PNR-8X2" placeholder="مثلاً ABC12">
                    </label>
                    <label class="kd-l">شمارهٔ پرواز (اختیاری)
                        <input name="flight_no" placeholder="مثلاً W5 1234">
                    </label>
                    <label class="kd-l">کلاس صندلی کشتی
                        <select name="ferry_class">
                            <option selected>اقتصادی</option>
                            <option>وی‌آی‌پی سالن</option>
                        </select>
                    </label>
                </div>
            </fieldset>

            <fieldset>
                <legend>۵ — اقامتگاه</legend>
                <div class="kd-grid">
                    <label class="kd-l">نام هتل / اقامتگاه
                        <input name="hotel_name" value="هتل نمونهٔ پرزنت">
                    </label>
                    <label class="kd-l">کد تأیید رزرو (OTA / هتل)
                        <input name="hotel_confirmation" data-kish-demo-field="hotel_confirmation" value="HTL-DEMO-991">
                    </label>
                    <label class="kd-l">نوع اتاق
                        <select name="room_type">
                            <option selected>دو تخته دریایی</option>
                            <option>سوئیت خانوادگی</option>
                        </select>
                    </label>
                </div>
            </fieldset>

            <fieldset>
                <legend>۶ — خودرو در منطقهٔ آزاد</legend>
                <div class="kd-grid">
                    <label class="kd-l">VIN (۱۷ رقم)
                        <input name="vehicle_vin" data-kish-demo-field="vehicle_vin" value="WBAFR9C50BC123456" maxlength="17">
                    </label>
                    <label class="kd-l">پلاک ملی
                        <input name="plate" value="۱۲ ص ۳۴۵ ایران ۲۲">
                    </label>
                    <label class="kd-l">شمارهٔ اظهارنامهٔ گمرکی (FZ-09)
                        <input name="customs_declaration_no" data-kish-demo-field="customs_declaration_no" value="FZ09-DEMO-7788">
                    </label>
                    <label class="kd-l">کد رهگیری ضمانت بانکی
                        <input name="bank_guarantee_trace" value="KI-GL-02-TR-000555">
                    </label>
                </div>
            </fieldset>

            <fieldset>
                <legend>۷ — همراهان و کودک</legend>
                <div class="kd-grid">
                    <label class="kd-l">تعداد همراهان
                        <input name="companions_count" type="number" min="0" value="1">
                    </label>
                    <label class="kd-l">تاریخ تولد کودک (برای تخت اضافه / سیاست هتل)
                        <input name="child_dob" data-kish-demo-field="child_dob" type="date" value="2013-06-15">
                    </label>
                    <label class="kd-l">یادداشت ویژهٔ پزشکی / غذایی
                        <textarea name="special_notes" placeholder="در پرزنت می‌توانید خالی بگذارید"></textarea>
                    </label>
                </div>
            </fieldset>

            <fieldset>
                <legend>۸ — بیمه و هماهنگی‌ها</legend>
                <div class="kd-grid">
                    <label class="kd-l">بیمهٔ مسافرتی
                        <select name="insurance">
                            <option selected>خرید از درگاه رسمی نمایش</option>
                            <option>خودم بیمه دارم</option>
                        </select>
                    </label>
                    <label class="kd-l">نیاز به ترانسفر فرودگاهی
                        <select name="transfer">
                            <option>خیر</option>
                            <option selected>بله — ون مشترک</option>
                        </select>
                    </label>
                </div>
            </fieldset>

            <p class="kd-note">
                این فرم هیچ داده‌ای را به سرور ارسال نمی‌کند. دکمه‌های سناریو فقط خطاهای نمایشی و زمینهٔ صفحه را برای ویجت چت تنظیم می‌کنند تا در جلسهٔ پرزنت، مسیر «پیشنهاد کمک → چت → راهنمایی چندمرحله‌ای» را نشان دهید.
            </p>
        </form>
    </div>
</body>
</html>
