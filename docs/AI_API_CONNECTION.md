# چرا پروژه (Laravel) به API هوش مصنوعی وصل نمی‌شود؟

## خلاصه مسیر درخواست

1. کاربر در **ai.pfm.ir** پیام می‌فرستد.
2. فرانت‌اند به `POST /api/v1/conversations/{id}/messages` درخواست می‌زند.
3. **Laravel** (`ConversationController::sendMessage`) درخواست را می‌گیرد و با `Http::post()` به آدرس **سرویس Python** درخواست می‌زند.
4. آدرس سرویس از **config** خوانده می‌شود: `config('services.python_ai.url')` که از `.env` مقدار `PYTHON_AI_URL` را می‌گیرد.

اگر **curl از شل** جواب می‌دهد ولی **Laravel** خطا می‌دهد، یعنی درخواست از **همان محیطی که PHP اجرا می‌شود** به پورت ۵۰۰۰ نمی‌رسد یا دیر جواب می‌آید.

---

## چک‌لیست روی سرور

### ۱) کد به‌روز روی سرور باشد

این تغییرات حتماً روی سرور (مثلاً `/var/www/ai.pfm.ir`) اعمال شده باشند:

- در **ConversationController** (هم UserPanel هم AdminPanel):
  - `Http::withoutVerifying()` برای رفع خطای SSL.
  - `->timeout(120)` (نه ۴۵).
  - `->withOptions(['connect_timeout' => 10])`.
- در **config/services.php**: پیش‌فرض `PYTHON_AI_URL` برابر `http://127.0.0.1:5000` (نه localhost).
- در **.env** مقدار `PYTHON_AI_URL=http://127.0.0.1:5000` (بدون فاصله، بدون `/` در آخر).

بعد از هر تغییر در `.env` یا config:

```bash
cd /var/www/ai.pfm.ir
php artisan config:clear
```

### ۲) تست اتصال از خود PHP (مهم)

این درخواست را از **همان سرور** بزن (یا از مرورگر با دامنه):

```bash
curl -s "https://ai.pfm.ir/api/v1/_ping-ai"
```

- اگر `"ok": true` و `"status": 200` دیدی → از نظر PHP به سرویس AI دسترسی هست؛ مشکل احتمالاً از جای دیگر است (مثلاً timeout یا خطای اپ).
- اگر `"ok": false` و خطای اتصال/تایم‌اوت دیدی → **PHP (مثلاً PHP-FPM یا کاربر www-data) به آدرس `PYTHON_AI_URL` دسترسی ندارد.** در آن صورت:
  - اگر Laravel داخل **Docker** است، `127.0.0.1` از داخل کانتینر به host اشاره نمی‌کند؛ باید از آدرس host (مثلاً `http://host.docker.internal:5000` یا IP واقعی host) استفاده کنی.
  - اگر فایروال یا محدودیت شبکه برای کاربر PHP هست، باید آن را برطرف کنی.

### ۳) لاگ Laravel هنگام ارسال پیام

وقتی از چت یک پیام می‌فرستی، در لاگ باید خطی شبیه این ببینی:

```
[production.INFO] AI API request {"url":"http://127.0.0.1:5000/api/ask"}
```

اگر به‌جای آن `https://ai.mokhtal.xyz` می‌بینی، یعنی یا `.env` ست نشده یا کش config خالی نشده. در صورت خطا، خطای دقیق و همان `url` در لاگ ثبت می‌شود:

```
[production.ERROR] AI API error {"message":"...","url":"http://127.0.0.1:5000/api/ask","exception":"..."}
```

### ۴) Nginx / PHP-FPM و تایم‌اوت

اگر پاسخ AI بیشتر از تایم‌اوت Nginx یا PHP طول بکشد، درخواست از طرف وب‌سرور قطع می‌شود. برای همان سرور (مثلاً ai.pfm.ir) در کانفیگ Nginx داخل بلوک `location ~ \.php$` می‌توانی اضافه کنی:

```nginx
fastcgi_read_timeout 120;
```

سپس Nginx را reload کن.

### ۵) سرویس Gunicorn (Python)

مطمئن شو فقط **یک** instance از Gunicorn روی پورت ۵۰۰۰ در حال اجراست و با آدرس درست bind شده (مثلاً `127.0.0.1:5000`). اگر قبلاً «Address already in use» داشتی، قبل از استارت سرویس پورت را آزاد کن:

```bash
sudo systemctl stop voice-assistant.service
sudo fuser -k 5000/tcp
sleep 2
sudo systemctl start voice-assistant.service
```

---

## جمع‌بندی

| مورد | کار |
|------|-----|
| curl از شل جواب می‌دهد، Laravel نه | اتصال از **محیط PHP** به همان آدرس را چک کن با `GET /api/v1/_ping-ai`. اگر آنجا خطا داد، آدرس یا شبکه برای PHP مشکل دارد (مثلاً Docker یا فایروال). |
| خطای SSL برای ai.mokhtal.xyz | در کد `withoutVerifying()` اضافه شده؛ اگر هنوز خطا می‌بینی، از `http://127.0.0.1:5000` استفاده کن. |
| تایم‌اوت ۴۵ ثانیه | در کد timeout به ۱۲۰ ثانیه و connect_timeout به ۱۰ ثانیه تغییر داده شده؛ کد به‌روز را روی سرور بریز و config:clear بزن. |
| لاگ دقیق خطا | در catch همان `url` و `message` و `exception` لاگ می‌شود؛ با `tail` روی `storage/logs/laravel.log` می‌توانی ببینی دقیقاً به چه آدرسی درخواست رفته و چه خطایی برگشته. |

در نهایت اگر `_ping-ai` از طرف همان سرور (یا مرورگر با دامنه) **ok: true** برگرداند و در لاگ هنگام ارسال پیام **url** برابر `http://127.0.0.1:5000/api/ask` باشد، درخواست از Laravel به API باید برسد؛ در غیر این صورت با همان لاگ و خروجی `_ping-ai` می‌توان مرحله بعد را مشخص کرد.
