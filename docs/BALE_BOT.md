# راه‌اندازی و چک کردن بازوی بله (PFMbot)

مستندات API: [https://docs.bale.ai/](https://docs.bale.ai/)

---

## ۱. تنظیم توکن

در `.env` مقداردهی کن:

```env
BALE_BOT_TOKEN=1440597715:0cClH-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

سپس کش کانفیگ را خالی کن:

```bash
php artisan config:clear
```

---

## ۲. چک کردن اینکه بازو و وب‌هوک کار می‌کنند

### الف) تست توکن (getMe)

اگر سرور در دسترس است و توکن درست است، از ترمینال:

```bash
php artisan tinker
>>> app(\App\Services\BaleApiService::class)->getMe()
```

خروجی باید شبیه `["ok" => true, "result" => ["id" => ..., "first_name" => "PFMbot", ...]]` باشد. اگر `ok => false` بود توکن یا شبکه را چک کن.

### ب) ثبت وب‌هوک

**شرط:** سرور باید از بیرون با **HTTPS** روی پورت **۴۴۳** یا **۸۸** در دسترس باشد (مثلاً دامنه `https://yourdomain.com`).

```bash
php artisan bale:set-webhook "https://YOUR_DOMAIN/api/webhook/bale"
```

به‌جای `YOUR_DOMAIN` دامنه واقعی (مثلاً `example.com`) را بگذار. اگر همه‌چیز درست باشد پیام «وب‌هوک با موفقیت تنظیم شد» را می‌بینی.

### ج) مطمئن شدن از ثبت وب‌هوک

```bash
php artisan bale:webhook-info
```

باید آدرس همان URLی که در مرحله قبل فرستادی را نشان دهد. اگر خالی بود یعنی وب‌هوک ثبت نشده یا با دستور خالی حذف شده.

### د) تست از داخل اپ بله

1. در اپ بله به **@PFMbot** یا لینک **ble.ir/PFMbot** برو.
2. یک پیام متنی بفرست (مثلاً «سلام»).
3. باید ظرف چند ثانیه:
   - اول حالت «در حال تایپ» دیده شود،
   - بعد یک پاسخ متنی از سرویس AI بیاید.

اگر پاسخی نیامد:
- **سرویس AI (Python)** باید روشن و در دسترس باشد (`PYTHON_AI_URL` در `.env`).
- لاگ‌های لاراول را ببین: `storage/logs/laravel.log` (خطاهای مربوط به Bale یا AI).

---

## ۳. تست دستی وب‌هوک (اختیاری)

اگر می‌خواهی بدون اپ بله بررسی کنی که endpoint به درخواست پاسخ می‌دهد:

```bash
curl -X POST "https://YOUR_DOMAIN/api/webhook/bale" \
  -H "Content-Type: application/json" \
  -d "{\"update_id\":1,\"message\":{\"message_id\":1,\"from\":{\"id\":123,\"first_name\":\"Test\"},\"chat\":{\"id\":123},\"date\":1234567890,\"text\":\"سلام\"}}"
```

باید همیشه **HTTP 200** برگردد. توجه: با این درخواست فقط endpoint چک می‌شود؛ برای پاسخ واقعی بازو باید توکن و سرویس AI هم درست باشند و از طرف سرور بله به این آدرس POST زده شود.

---

## ۴. جمع‌بندی چک‌لیست

| مرحله | دستور / کار | انتظار |
|--------|-------------|--------|
| ۱ | `BALE_BOT_TOKEN` در `.env` + `php artisan config:clear` | بدون خطا |
| ۲ | `php artisan bale:set-webhook "https://DOMAIN/api/webhook/bale"` | پیام موفقیت |
| ۳ | `php artisan bale:webhook-info` | نمایش همان URL |
| ۴ | فرستادن پیام به @PFMbot در بله | پاسخ متنی از بازو |
| ۵ | در صورت خطا | بررسی `storage/logs/laravel.log` و سرویس AI |

اگر همه مراحل بالا درست باشند، بازو درست کار می‌کند.
