@extends('admin.layouts.master')

@section('title', 'داشبورد')

@push('styles')
<style>
    .dashboard-page { width: 100%; }
    .dashboard-page__header { margin-bottom: 2rem; }
    .dashboard-page h1 { font-size: 1.75rem; font-weight: 700; color: var(--admin-text); margin: 0 0 0.25rem 0; }
    .dashboard-page .subtitle { color: var(--admin-muted-text); font-size: 0.9375rem; margin: 0; }

    /* کارت‌های آمار: عرض یکسان با بخش تیکت‌های اخیر، گرید پرعرض */
    .stat-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2.5rem;
    }

    .stat-card {
        background: var(--admin-surface);
        border: 1px solid var(--admin-border);
        border-radius: 1rem;
        padding: 1.35rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
        transition: box-shadow 0.2s ease, transform 0.2s ease;
        text-decoration: none;
        color: inherit;
        display: block;
        text-align: right;
    }
    .stat-card:hover {
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.1);
        transform: translateY(-2px);
    }
    .stat-card__icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
    }
    .stat-card__icon svg { width: 24px; height: 24px; color: #fff; }
    .stat-card__value { font-size: 1.75rem; font-weight: 700; color: var(--admin-text); line-height: 1.2; margin-bottom: 0.25rem; }
    .stat-card__title { font-size: 0.9375rem; font-weight: 600; color: var(--admin-text); }
    .stat-card__sub { font-size: 0.8125rem; color: var(--admin-muted-text); margin-top: 2px; }

    .quick-actions { margin-bottom: 2.5rem; }
    .quick-actions h2 { font-size: 1.25rem; font-weight: 700; color: var(--admin-text); margin: 0 0 1rem 0; }
    .quick-actions__grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 1rem;
    }
    .quick-action {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem;
        border-radius: 1rem;
        color: #fff;
        text-decoration: none;
        text-align: right;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .quick-action:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,0.15); color: #fff; }
    .quick-action__icon { width: 48px; height: 48px; border-radius: 12px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .quick-action__icon svg { width: 24px; height: 24px; }
    .quick-action__text h3 { font-size: 1rem; font-weight: 700; margin: 0 0 0.25rem 0; }
    .quick-action__text p { font-size: 0.8125rem; opacity: 0.9; margin: 0; }
    .quick-action--primary { background: linear-gradient(135deg, var(--admin-primary), #1d4ed8); }
    .quick-action--accent { background: linear-gradient(135deg, var(--admin-accent), #06b6d4); }
    .quick-action--teal { background: linear-gradient(135deg, #0d9488, #14b8a6); }
    .quick-action--slate { background: linear-gradient(135deg, #475569, #64748b); }

    .recent-section { margin-bottom: 0; }
    .recent-section h2 { font-size: 1.25rem; font-weight: 700; color: var(--admin-text); margin: 0 0 1rem 0; }
    .recent-card {
        background: var(--admin-surface);
        border: 1px solid var(--admin-border);
        border-radius: 1rem;
        padding: 1.25rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
        width: 100%;
    }
    .recent-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--admin-border);
        text-decoration: none;
        color: inherit;
        transition: background 0.2s;
    }
    .recent-item:last-child { border-bottom: none; }
    .recent-item:hover { background: rgba(15, 23, 42, 0.03); }
    .recent-item__icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .recent-item__icon svg { width: 20px; height: 20px; }
    .recent-item__body { flex: 1; min-width: 0; }
    .recent-item__title { font-weight: 600; color: var(--admin-text); font-size: 0.9375rem; }
    .recent-item__meta { font-size: 0.8125rem; color: var(--admin-muted-text); margin-top: 2px; }
    .recent-item__time { font-size: 0.75rem; color: var(--admin-muted-text); flex-shrink: 0; }
    .recent-empty { text-align: center; padding: 2.5rem; color: var(--admin-muted-text); font-size: 0.9375rem; }
</style>
@endpush

@section('content')
<div class="dashboard-page">
    <header class="dashboard-page__header">
        <h1>داشبورد</h1>
        <p class="subtitle">سلام {{ auth()->user()->name ?? 'دوست عزیز' }} — مدیریت تیکت‌ها و گفت‌وگوها</p>
    </header>

    {{-- کارت‌های آمار (پالت پروژه) --}}
    <div class="stat-cards">
        <a href="{{ route('admin.tickets') }}" class="stat-card">
            <div class="stat-card__icon" style="background: linear-gradient(135deg, var(--admin-primary), #1d4ed8);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 9a3 3 0 0 1 3-3h14a3 3 0 0 1 3 3v6a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3V9z"/><path d="M6 9h.01M18 9h.01M6 15h.01M18 15h.01"/></svg>
            </div>
            <div class="stat-card__value">{{ $stats['tickets_total'] }}</div>
            <div class="stat-card__title">تیکت‌ها</div>
            <div class="stat-card__sub">کل تیکت‌های ثبت‌شده</div>
        </a>
        <a href="{{ route('admin.tickets') }}" class="stat-card">
            <div class="stat-card__icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            </div>
            <div class="stat-card__value">{{ $stats['tickets_pending'] }}</div>
            <div class="stat-card__title">در انتظار پاسخ</div>
            <div class="stat-card__sub">تیکت‌های باز</div>
        </a>
        <a href="{{ route('admin.chats') }}" class="stat-card">
            <div class="stat-card__icon" style="background: linear-gradient(135deg, var(--admin-accent), #06b6d4);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <div class="stat-card__value">{{ $stats['conversations_total'] }}</div>
            <div class="stat-card__title">گفت‌وگوها</div>
            <div class="stat-card__sub">مکالمات چت</div>
        </a>
        <a href="{{ route('admin.chats') }}" class="stat-card">
            <div class="stat-card__icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
            </div>
            <div class="stat-card__value">{{ $stats['referrals_open'] }}</div>
            <div class="stat-card__title">ارجاع‌های باز</div>
            <div class="stat-card__sub">در انتظار پاسخ</div>
        </a>
        @if($isAdmin ?? false)
        <a href="{{ route('admin.users') }}" class="stat-card">
            <div class="stat-card__icon" style="background: linear-gradient(135deg, #0d9488, #14b8a6);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="stat-card__value">{{ $stats['users_total'] ?? 0 }}</div>
            <div class="stat-card__title">کاربران</div>
            <div class="stat-card__sub">کل کاربران</div>
        </a>
        @endif
    </div>

    {{-- عملیات سریع --}}
    <section class="quick-actions">
        <h2>عملیات سریع</h2>
        <div class="quick-actions__grid">
            <a href="{{ route('admin.tickets') }}" class="quick-action quick-action--primary">
                <span class="quick-action__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 9a3 3 0 0 1 3-3h14a3 3 0 0 1 3 3v6a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3V9z"/></svg>
                </span>
                <span class="quick-action__text">
                    <h3>مشاهده تیکت‌ها</h3>
                    <p>لیست و پاسخ به تیکت‌ها</p>
                </span>
            </a>
            <a href="{{ route('admin.chats') }}" class="quick-action quick-action--accent">
                <span class="quick-action__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </span>
                <span class="quick-action__text">
                    <h3>گفت‌وگوها</h3>
                    <p>مکالمات و ارجاع‌ها</p>
                </span>
            </a>
            @if($isAdmin ?? false)
            <a href="{{ route('admin.users') }}" class="quick-action quick-action--teal">
                <span class="quick-action__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </span>
                <span class="quick-action__text">
                    <h3>کاربران</h3>
                    <p>مدیریت کاربران</p>
                </span>
            </a>
            <a href="{{ route('admin.roles') }}" class="quick-action quick-action--slate">
                <span class="quick-action__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </span>
                <span class="quick-action__text">
                    <h3>نقش‌ها</h3>
                    <p>تنظیم نقش و دسترسی</p>
                </span>
            </a>
            @endif
        </div>
    </section>

    {{-- نمودارها --}}
    <section class="charts-section" style="margin-bottom: 2.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--admin-text); margin: 0 0 1rem 0;">نمودار تیکت‌ها</h2>
        <div class="charts-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
            <div class="chart-card" style="background: var(--admin-surface); border: 1px solid var(--admin-border); border-radius: 1rem; padding: 1.25rem; box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);">
                <h3 style="font-size: 1rem; font-weight: 600; color: var(--admin-text); margin: 0 0 1rem 0; text-align: right;">تیکت‌های ۷ روز اخیر</h3>
                <div class="chart-wrap" style="position: relative; width: 100%; height: 220px;">
                    <canvas id="dashboard-chart-bars"></canvas>
                </div>
            </div>
            <div class="chart-card" style="background: var(--admin-surface); border: 1px solid var(--admin-border); border-radius: 1rem; padding: 1.25rem; box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);">
                <h3 style="font-size: 1rem; font-weight: 600; color: var(--admin-text); margin: 0 0 1rem 0; text-align: right;">توزیع بر اساس وضعیت</h3>
                <div class="chart-wrap" style="position: relative; width: 100%; height: 220px;">
                    <canvas id="dashboard-chart-donut"></canvas>
                </div>
            </div>
        </div>
    </section>

    {{-- تیکت‌های اخیر --}}
    <section class="recent-section">
        <h2>تیکت‌های اخیر</h2>
        <div class="recent-card">
            @if($recentTickets->isEmpty())
                <p class="recent-empty">هنوز تیکتی ثبت نشده است.</p>
            @else
                @foreach($recentTickets as $t)
                    <a href="{{ route('admin.tickets') }}?ticket={{ $t->id }}" class="recent-item">
                        <span class="recent-item__icon" style="background: rgba(15, 23, 42, 0.08); color: var(--admin-primary);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 9a3 3 0 0 1 3-3h14a3 3 0 0 1 3 3v6a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3V9z"/></svg>
                        </span>
                        <span class="recent-item__body">
                            <span class="recent-item__title">{{ \Illuminate\Support\Str::limit($t->title ?? '', 50) }}</span>
                            <span class="recent-item__meta">{{ $t->sender->name ?? '-' }} · {{ $t->status === 'pending' ? 'در انتظار' : ($t->status === 'answered' ? 'پاسخ‌داده‌شده' : 'بسته') }}</span>
                        </span>
                        <span class="recent-item__time">{{ $t->created_at->diffForHumans() }}</span>
                    </a>
                @endforeach
                <div style="text-align: center; padding: 0.75rem 0; border-top: 1px solid var(--admin-border);">
                    <a href="{{ route('admin.tickets') }}" style="font-size: 0.875rem; font-weight: 600; color: var(--admin-primary); text-decoration: none;">مشاهده همه تیکت‌ها</a>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var chartLabelsLastDays = @json($chartLabelsLastDays ?? []);
    var chartTicketsLastDays = @json($chartTicketsLastDays ?? []);
    var chartTicketsByStatus = @json($chartTicketsByStatus ?? ['pending' => 0, 'answered' => 0, 'closed' => 0]);
    if (typeof chartTicketsByStatus !== 'object' || chartTicketsByStatus === null) {
        chartTicketsByStatus = { pending: 0, answered: 0, closed: 0 };
    }
    function initCharts() {
        var Chart = window.Chart;
        if (typeof Chart === 'undefined') return;
        var barsCtx = document.getElementById('dashboard-chart-bars');
        if (barsCtx) {
            try {
            var dataArr = Array.isArray(chartTicketsLastDays) ? chartTicketsLastDays : [];
            var maxVal = dataArr.length ? Math.max.apply(null, dataArr) : 0;
            new Chart(barsCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: Array.isArray(chartLabelsLastDays) ? chartLabelsLastDays : [],
                    datasets: [{
                        label: 'تعداد تیکت',
                        data: dataArr,
                        backgroundColor: 'rgba(37, 99, 235, 0.7)',
                        borderColor: 'rgb(37, 99, 235)',
                        borderWidth: 1,
                        borderRadius: 6,
                        barThickness: 'flex',
                        minBarLength: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            suggestedMax: maxVal < 5 ? 5 : undefined,
                            ticks: {
                                stepSize: 1,
                                font: { family: 'Vazirmatn, Vazir, sans-serif' }
                            }
                        },
                        y: {
                            ticks: { font: { family: 'Vazirmatn, Vazir, sans-serif' } }
                        }
                    }
                }
            });
            } catch (e) { console.error('Dashboard bar chart error:', e); }
        }
        var donutCtx = document.getElementById('dashboard-chart-donut');
        if (donutCtx) {
            try {
            new Chart(donutCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['در انتظار', 'پاسخ‌داده‌شده', 'بسته'],
                    datasets: [{
                        data: [chartTicketsByStatus.pending || 0, chartTicketsByStatus.answered || 0, chartTicketsByStatus.closed || 0],
                        backgroundColor: ['#f59e0b', '#22c55e', '#64748b'],
                        borderColor: '#fff',
                        borderWidth: 2,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            rtl: true,
                            labels: {
                                font: { family: 'Vazirmatn, Vazir, sans-serif', size: 13 },
                                padding: 16,
                                usePointStyle: true,
                                pointStyle: 'rect',
                                boxWidth: 14,
                                boxHeight: 14,
                                generateLabels: function(chart) {
                                    var data = chart.data;
                                    return (data.labels || []).map(function(label, i) {
                                        return {
                                            text: label + ' (' + (data.datasets[0].data[i] || 0) + ')',
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            strokeStyle: data.datasets[0].borderColor || '#fff',
                                            lineWidth: 1,
                                            hidden: false,
                                            index: i
                                        };
                                    });
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
            } catch (e) { console.error('Dashboard donut chart error:', e); }
        }
    }
    var chartAttempts = 0;
    var maxChartAttempts = 100;
    function runWhenReady() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', runWhenReady);
            return;
        }
        if (typeof window.Chart === 'undefined') {
            chartAttempts++;
            if (chartAttempts < maxChartAttempts) {
                setTimeout(runWhenReady, 50);
            }
            return;
        }
        requestAnimationFrame(initCharts);
    }
    runWhenReady();
})();
</script>
@endpush
