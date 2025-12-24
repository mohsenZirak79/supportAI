<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>دسترسی غیرمجاز | سامانه پشتیبانی</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            color-scheme: dark;
        }
        body {
            margin: 0;
            font-family: 'Vazirmatn', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: radial-gradient(circle at top, rgba(8, 145, 178, 0.35), rgba(2, 6, 23, 0.95) 55%);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .error-card {
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 18px;
            padding: 44px 34px;
            max-width: 520px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(2, 6, 23, 0.65);
        }
        .error-code {
            font-size: 5rem;
            margin: 0 0 12px;
            font-weight: 800;
            letter-spacing: 8px;
            color: #f87171;
        }
        h1 {
            font-size: 1.8rem;
            margin-bottom: 12px;
        }
        p {
            margin: 0 auto 28px;
            line-height: 1.8;
            color: #94a3b8;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
        }
        .btn {
            padding: 12px 22px;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
            text-decoration: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #f97316, #dc2626);
            color: #fff7ed;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.5);
        }
        .btn-ghost {
            background: transparent;
            border: 1px solid rgba(148, 163, 184, 0.4);
            color: #cbd5f5;
        }
        @media (max-width: 480px) {
            .error-card {
                padding: 32px 24px;
            }
            .error-code {
                font-size: 3.5rem;
            }
        }
    </style>
</head>
<body>
<main class="error-card">
    <div class="error-code">403</div>
    <h1>دسترسی شما محدود شده است</h1>
    <p>برای دسترسی به این بخش باید مجوز لازم را داشته باشید. اگر فکر می‌کنید این پیام اشتباهی است، با پشتیبانی تماس بگیرید.</p>
    <div class="actions">
        <a href="{{ route('landing') }}" class="btn btn-primary">بازگشت به داشبورد</a>
        <button type="button" class="btn btn-ghost" onclick="window.history.back()">بازگشت به صفحه قبل</button>
    </div>
</main>
</body>
</html>
