<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>صفحه پیدا نشد | سامانه پشتیبانی</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            color-scheme: dark;
        }
        body {
            margin: 0;
            font-family: 'Vazirmatn', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0f172a;
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
            padding: 48px 36px;
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
            color: #38bdf8;
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
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(8, 145, 178, 0.35);
        }
        .btn:active {
            transform: translateY(0);
        }
        .btn-primary {
            background: linear-gradient(135deg, #0ea5e9, #0f766e);
            color: #e0f2fe;
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
    <div class="error-code">404</div>
    <h1>اینجا چیزی پیدا نشد!</h1>
    <p>هیچ ردی از صفحه یا منبعی که دنبالش هستید پیدا نکردیم. ممکن است آدرس به اشتباه وارد شده باشد یا صفحه حذف شده باشد.</p>
    <div class="actions">
        <a href="{{ route('landing') }}" class="btn btn-primary">بازگشت به صفحه اصلی</a>
        <button type="button" class="btn btn-ghost" onclick="window.history.back()">بازگشت به صفحه قبل</button>
    </div>
</main>
</body>
</html>
