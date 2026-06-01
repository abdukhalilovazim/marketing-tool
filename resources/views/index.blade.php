<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tezda — Marketing Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg: #0f1117;
    --surface: #1a1d27;
    --surface2: #22263a;
    --border: rgba(255,255,255,0.07);
    --border-hover: rgba(255,255,255,0.14);
    --text: #f1f5f9;
    --text-muted: #64748b;
    --text-dim: #94a3b8;
    --accent: #6366f1;
    --accent2: #8b5cf6;
    --green: #10b981;
    --red: #f43f5e;
    --blue: #3b82f6;
    --orange: #f97316;
    --cyan: #06b6d4;
    --sidebar-w: 220px;
  }

  body {
    font-family: 'Inter', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    display: flex;
  }

  /* ── Sidebar ── */
  .sidebar {
    width: var(--sidebar-w);
    min-height: 100vh;
    background: var(--surface);
    border-right: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0; left: 0; bottom: 0;
    z-index: 50;
  }

  .sidebar-logo {
    padding: 24px 20px 20px;
    border-bottom: 1px solid var(--border);
  }

  .logo-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
  }

  .logo-icon {
    width: 36px; height: 36px;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    box-shadow: 0 4px 16px rgba(99,102,241,0.35);
  }

  .logo-text { font-size: 16px; font-weight: 800; letter-spacing: -0.5px; }
  .logo-sub { font-size: 10px; color: var(--text-muted); font-weight: 500; margin-top: 1px; letter-spacing: 0.5px; text-transform: uppercase; }

  .sidebar-nav { padding: 16px 12px; flex: 1; }

  .nav-label {
    font-size: 10px; font-weight: 600; color: var(--text-muted);
    text-transform: uppercase; letter-spacing: 1px;
    padding: 0 8px; margin-bottom: 8px; margin-top: 16px;
  }

  .nav-item {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 12px; border-radius: 10px;
    font-size: 13px; font-weight: 500; color: var(--text-dim);
    cursor: pointer; transition: all .15s; text-decoration: none;
    margin-bottom: 2px;
  }

  .nav-item:hover { background: var(--surface2); color: var(--text); }
  .nav-item.active { background: rgba(99,102,241,0.15); color: var(--accent); font-weight: 600; }
  .nav-item .nav-icon { font-size: 16px; width: 20px; text-align: center; }

  .sidebar-footer {
    padding: 16px 20px;
    border-top: 1px solid var(--border);
    font-size: 11px; color: var(--text-muted);
  }

  /* ── Main ── */
  .main {
    margin-left: var(--sidebar-w);
    flex: 1;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }

  /* ── Topbar ── */
  .topbar {
    height: 60px;
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 28px;
    position: sticky; top: 0; z-index: 40;
  }

  .topbar-left { display: flex; align-items: center; gap: 16px; }
  .page-title { font-size: 15px; font-weight: 700; }
  .page-badge {
    font-size: 10px; font-weight: 600; padding: 3px 8px;
    background: rgba(99,102,241,0.15); color: var(--accent);
    border-radius: 6px; letter-spacing: 0.3px;
  }

  .topbar-right { display: flex; align-items: center; gap: 10px; }

  /* ── Date Filter Bar ── */
  .filter-bar {
    display: flex; align-items: center; gap: 12px;
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 8px 16px;
  }

  .filter-lbl { font-size: 11px; font-weight: 600; color: var(--text-muted); white-space: nowrap; }
  .filter-arrow { color: var(--text-muted); font-size: 14px; }

  .date-inp {
    background: transparent;
    border: none; outline: none;
    color: var(--text); font-family: 'Inter', sans-serif;
    font-size: 12px; font-weight: 500;
    cursor: pointer;
    color-scheme: dark;
  }

  .btn-apply {
    padding: 7px 18px;
    background: var(--accent);
    color: #fff;
    border: none; border-radius: 8px;
    font-size: 12px; font-weight: 600;
    cursor: pointer; font-family: 'Inter', sans-serif;
    transition: all .15s;
    box-shadow: 0 2px 12px rgba(99,102,241,0.4);
  }
  .btn-apply:hover { background: #4f46e5; transform: translateY(-1px); box-shadow: 0 4px 16px rgba(99,102,241,0.5); }

  /* ── Page content ── */
  .page-content { padding: 24px 28px 48px; }

  /* ── Cards ── */
  .card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 20px 24px;
    transition: border-color .2s;
  }
  .card:hover { border-color: var(--border-hover); }

  .card-title {
    font-size: 12px; font-weight: 700; color: var(--text-dim);
    text-transform: uppercase; letter-spacing: 0.8px;
    margin-bottom: 4px;
  }

  /* ── KPI Grid ── */
  .kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 20px;
  }

  .kpi-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 20px;
    position: relative;
    overflow: hidden;
    transition: all .2s;
  }
  .kpi-card:hover { border-color: var(--border-hover); transform: translateY(-2px); }

  .kpi-card::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 2px;
    border-radius: 16px 16px 0 0;
  }
  .kpi-card.indigo::before { background: linear-gradient(90deg, var(--accent), var(--accent2)); }
  .kpi-card.green::before  { background: linear-gradient(90deg, var(--green), #34d399); }
  .kpi-card.blue::before   { background: linear-gradient(90deg, var(--blue), var(--cyan)); }
  .kpi-card.orange::before { background: linear-gradient(90deg, var(--orange), #fbbf24); }

  .kpi-icon {
    width: 40px; height: 40px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; margin-bottom: 14px;
  }
  .kpi-card.indigo .kpi-icon { background: rgba(99,102,241,0.12); }
  .kpi-card.green  .kpi-icon { background: rgba(16,185,129,0.12); }
  .kpi-card.blue   .kpi-icon { background: rgba(59,130,246,0.12); }
  .kpi-card.orange .kpi-icon { background: rgba(249,115,22,0.12); }

  .kpi-label { font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px; }
  .kpi-value { font-size: 28px; font-weight: 800; letter-spacing: -1px; line-height: 1; margin-bottom: 8px; }
  .kpi-sub { display: flex; align-items: center; gap: 6px; font-size: 12px; }
  .kpi-badge {
    padding: 2px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;
  }
  .kpi-badge.up   { background: rgba(16,185,129,0.12); color: var(--green); }
  .kpi-badge.down { background: rgba(244,63,94,0.12);  color: var(--red); }
  .kpi-desc { color: var(--text-muted); font-size: 11px; }

  /* ── 2-column grid ── */
  .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px; }
  .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; margin-bottom: 20px; }
  .grid-3-1 { display: grid; grid-template-columns: 2fr 1fr; gap: 14px; margin-bottom: 20px; }
  .mb-14 { margin-bottom: 14px; }

  /* ── Chart card ── */
  .chart-hdr { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; }
  .chart-title { font-size: 13px; font-weight: 700; color: var(--text); }
  .chart-sub { font-size: 11px; color: var(--text-muted); margin-top: 2px; }

  .chart-type-sel {
    background: var(--surface2); border: 1px solid var(--border);
    border-radius: 8px; padding: 5px 10px;
    font-size: 11px; font-weight: 600; color: var(--text-dim);
    font-family: 'Inter', sans-serif; outline: none; cursor: pointer;
    color-scheme: dark;
  }

  /* ── Month comparison ── */
  .month-cmp {
    display: flex; align-items: stretch;
    overflow: hidden;
  }
  .month-bars { display: flex; flex: 1; align-items: flex-end; gap: 0; padding: 0 0 0 4px; }
  .month-col { flex: 1; text-align: center; display: flex; flex-direction: column; align-items: center; padding: 0 6px; }
  .month-bar-wrap { width: 100%; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 90px; }
  .month-bar-val { font-size: 12px; font-weight: 800; color: var(--text); margin-bottom: 4px; }
  .month-bar { width: 55%; border-radius: 6px 6px 0 0; }
  .month-baseline { width: 100%; height: 1px; background: var(--border); margin-bottom: 6px; }
  .month-lbl { font-size: 11px; color: var(--text-muted); font-weight: 500; padding-bottom: 8px; }
  .month-flags { display: flex; gap: 4px; justify-content: center; margin-top: 4px; margin-bottom: 8px; flex-wrap: wrap; }
  .flag-badge { font-size: 10px; font-weight: 600; padding: 2px 6px; border-radius: 4px; background: var(--surface2); color: var(--text-dim); }

  .month-current {
    width: 200px; flex-shrink: 0;
    border-left: 1px solid var(--border);
    padding: 20px 22px;
    display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;
    background: rgba(99,102,241,0.04);
  }
  .cur-month-name { font-size: 28px; font-weight: 900; letter-spacing: -1px; line-height: 1; margin-bottom: 4px; }
  .cur-month-range { font-size: 11px; color: var(--text-muted); margin-bottom: 12px; }
  .cur-month-num { font-size: 36px; font-weight: 900; letter-spacing: -1.5px; line-height: 1; }
  .cur-month-pct { font-size: 20px; font-weight: 700; }
  .cur-month-pct.up { color: var(--green); }
  .cur-month-pct.down { color: var(--red); }
  .cur-month-diff { font-size: 14px; font-weight: 700; margin: 4px 0; }
  .cur-month-diff.up   { color: var(--green); }
  .cur-month-diff.down { color: var(--red); }
  .cur-month-desc { font-size: 11px; color: var(--text-muted); }

  /* ── Funnel ── */
  .funnel-hdr { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
  .funnel-icon { width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; }
  .funnel-title { font-size: 13px; font-weight: 700; }

  .funnel-row { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--border); }
  .funnel-row:last-child { border-bottom: none; }
  .funnel-lbl { font-size: 12px; color: var(--text-dim); font-weight: 500; flex: 0 0 140px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .funnel-bar-wrap { flex: 1; height: 5px; background: var(--surface2); border-radius: 99px; overflow: hidden; }
  .funnel-bar { height: 100%; border-radius: 99px; transition: width .5s cubic-bezier(.4,0,.2,1); }
  .funnel-val { font-size: 12px; font-weight: 700; flex: 0 0 60px; text-align: right; }
  .funnel-pct { font-size: 11px; font-weight: 600; flex: 0 0 42px; text-align: right; color: var(--text-muted); }
  .funnel-flags { display: flex; gap: 4px; margin-top: 3px; flex-wrap: wrap; }

  /* ── Source donut ── */
  .donut-wrap { display: flex; flex-direction: column; align-items: center; }
  .donut-canvas { width: 180px !important; height: 180px !important; }
  .source-legend { display: flex; flex-wrap: wrap; gap: 8px 16px; justify-content: center; margin-top: 16px; }
  .legend-item { display: flex; align-items: center; gap: 6px; }
  .legend-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
  .legend-name { font-size: 11px; color: var(--text-dim); }
  .legend-val { font-size: 11px; font-weight: 700; }
  .legend-pct { font-size: 11px; color: var(--text-muted); }

  /* ── Loading overlay ── */
  .loading-overlay {
    position: fixed; inset: 0;
    background: rgba(15,17,23,0.8);
    display: none; align-items: center; justify-content: center;
    z-index: 999;
    backdrop-filter: blur(4px);
  }
  .loading-overlay.visible { display: flex; }
  .spinner-ring {
    width: 48px; height: 48px;
    border: 3px solid rgba(255,255,255,0.08);
    border-top-color: var(--accent);
    border-radius: 50%;
    animation: spin .8s linear infinite;
  }
  @keyframes spin { to { transform: rotate(360deg); } }

  /* ── Scrollbar ── */
  ::-webkit-scrollbar { width: 6px; }
  ::-webkit-scrollbar-track { background: transparent; }
  ::-webkit-scrollbar-thumb { background: var(--surface2); border-radius: 99px; }

  /* ── Responsive ── */
  @media(max-width: 1100px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
  @media(max-width: 768px) {
    .sidebar { display: none; }
    .main { margin-left: 0; }
    .kpi-grid, .grid-2, .grid-3, .grid-3-1 { grid-template-columns: 1fr; }
    .filter-bar { flex-wrap: wrap; }
  }
  /* ── Tooltips ── */
  .info-tooltip-container {
    position: absolute;
    top: 18px;
    right: 18px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  .info-icon {
    font-size: 13px;
    color: var(--text-muted);
    transition: color 0.15s;
    user-select: none;
    opacity: 0.7;
  }
  .info-tooltip-container:hover .info-icon {
    color: var(--accent);
    opacity: 1;
  }
  .info-tooltip {
    visibility: hidden;
    opacity: 0;
    width: 220px;
    background: rgba(30, 41, 59, 0.96);
    backdrop-filter: blur(8px);
    color: #f1f5f9;
    text-align: left;
    border-radius: 8px;
    padding: 10px 14px;
    position: absolute;
    z-index: 100;
    bottom: 130%;
    right: -6px;
    font-size: 11px;
    line-height: 1.45;
    font-weight: 500;
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5);
    transition: opacity 0.15s ease-out, visibility 0.15s ease-out, transform 0.15s ease-out;
    transform: translateY(4px);
    pointer-events: none;
  }
  .info-tooltip::after {
    content: "";
    position: absolute;
    top: 100%;
    right: 10px;
    border-width: 5px;
    border-style: solid;
    border-color: rgba(30, 41, 59, 0.96) transparent transparent transparent;
  }
  .info-tooltip-container:hover .info-tooltip {
    visibility: visible;
    opacity: 1;
    transform: translateY(0);
  }
</style>
</head>
<body>

<!-- Loading -->
<div id="loadingOverlay" class="loading-overlay">
  <div class="spinner-ring"></div>
</div>

<!-- Sidebar -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-badge">
      <div class="logo-icon">📣</div>
      <div>
        <div class="logo-text">Tezda</div>
        <div class="logo-sub">Marketing</div>
      </div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-label">Asosiy</div>
    <a class="nav-item active" href="#">
      <span class="nav-icon">📊</span> Dashboard
    </a>
  </nav>

  <div class="sidebar-footer">
    &copy; {{ date('Y') }} Revolution Global
  </div>
</aside>

<!-- Main -->
<div class="main">

  <!-- Topbar -->
  <header class="topbar">
    <div class="topbar-left">
      <div class="page-title">Marketing Dashboard</div>
      <span class="page-badge">LIVE</span>
    </div>
    <div class="topbar-right">
      <div class="filter-bar">
        <span class="filter-lbl">Dan</span>
        <input class="date-inp" type="date" id="mktFrom">
        <span class="filter-arrow">→</span>
        <span class="filter-lbl">Gacha</span>
        <input class="date-inp" type="date" id="mktTo">
        <button class="btn-apply" onclick="refreshData()">Qo'llash</button>
      </div>
    </div>
  </header>

  <!-- Content -->
  <div class="page-content">

    <!-- Month Comparison (Moved to very top) -->
    <div class="card mb-14" id="monthCmpArea"></div>

    <!-- KPI Cards -->
    <div class="kpi-grid" id="kpiArea">
      <div class="kpi-card indigo">
        <div class="info-tooltip-container">
          <span class="info-icon">ℹ️</span>
          <div class="info-tooltip">Tizimda ro'yxatdan o'tgan jami foydalanuvchilar soni</div>
        </div>
        <div class="kpi-icon">👤</div>
        <div class="kpi-label">Jami foydalanuvchi</div>
        <div class="kpi-value" id="kpiTotalUsers">—</div>
        <div class="kpi-sub"><span class="kpi-desc">Yuklanmoqda...</span></div>
      </div>
      <div class="kpi-card green">
        <div class="info-tooltip-container">
          <span class="info-icon">ℹ️</span>
          <div class="info-tooltip">Tanlangan davr mobaynida ilovadan foydalangan faol foydalanuvchilar soni</div>
        </div>
        <div class="kpi-icon">✅</div>
        <div class="kpi-label">Aktiv foydalanuvchi</div>
        <div class="kpi-value" id="kpiActiveUsers">—</div>
        <div class="kpi-sub"><span class="kpi-desc">Yuklanmoqda...</span></div>
      </div>
      <div class="kpi-card blue">
        <div class="info-tooltip-container">
          <span class="info-icon">ℹ️</span>
          <div class="info-tooltip">Tanlangan davrda ro'yxatdan o'tgan yangi foydalanuvchilar soni</div>
        </div>
        <div class="kpi-icon">🆕</div>
        <div class="kpi-label">Yangi foydalanuvchi</div>
        <div class="kpi-value" id="kpiNewUsers">—</div>
        <div class="kpi-sub"><span class="kpi-desc">Yuklanmoqda...</span></div>
      </div>
      <div class="kpi-card orange">
        <div class="info-tooltip-container">
          <span class="info-icon">ℹ️</span>
          <div class="info-tooltip">Tanlangan davrda amalga oshirilgan muvaffaqiyatli pul o'tkazmalari soni</div>
        </div>
        <div class="kpi-icon">💸</div>
        <div class="kpi-label">Transferlar</div>
        <div class="kpi-value" id="kpiTransfers">—</div>
        <div class="kpi-sub"><span class="kpi-desc">Yuklanmoqda...</span></div>
      </div>
    </div>

    <!-- Funnels -->
    <div class="grid-2" id="funnelArea"></div>

    <!-- Charts row -->
    <div class="card mb-14" style="position: relative;">
      <div class="info-tooltip-container">
        <span class="info-icon">ℹ️</span>
        <div class="info-tooltip">Tanlangan davr mobaynida kunlik yangi ro'yxatdan o'tgan foydalanuvchilar soni dinamikasi</div>
      </div>
      <div class="chart-hdr">
        <div>
          <div class="chart-title">Yangi foydalanuvchilar</div>
          <div class="chart-sub">Kunlik dinamika (UZ + RU)</div>
        </div>
        <select class="chart-type-sel" id="mktNewUserSel" onchange="updateCharts()">
          <option value="line">Line</option>
          <option value="bar">Bar</option>
        </select>
      </div>
      <canvas id="mktNewUserChart" height="75"></canvas>
    </div>

    <div class="card mb-14" id="activeChartWrap" style="position: relative;">
      <div class="info-tooltip-container">
        <span class="info-icon">ℹ️</span>
        <div class="info-tooltip">Tanlangan davr mobaynida kunlik faol (ilovadan foydalangan) foydalanuvchilar soni dinamikasi</div>
      </div>
      <div class="chart-hdr">
        <div>
          <div class="chart-title">Faol foydalanuvchilar</div>
          <div class="chart-sub">Kunlik faollik dinamikasi</div>
        </div>
        <select class="chart-type-sel" id="mktActiveSel" onchange="updateCharts()">
          <option value="line">Line</option>
          <option value="bar">Bar</option>
        </select>
      </div>
      <canvas id="mktActiveChart" height="75"></canvas>
    </div>

    <!-- Source donut -->
    <div class="card mb-14" id="sourceChartWrap" style="position: relative;">
      <div class="info-tooltip-container">
        <span class="info-icon">ℹ️</span>
        <div class="info-tooltip">Foydalanuvchilar qaysi kanallar (Instagram, Telegram, YouTube va b.) orqali ilovani topganligi taqsimoti</div>
      </div>
      <div class="chart-hdr">
        <div>
          <div class="chart-title">Foydalanuvchi manbalari</div>
          <div class="chart-sub">Ilova topish kanallari bo'yicha taqsimot</div>
        </div>
      </div>
      <div class="donut-wrap">
        <canvas class="donut-canvas" id="sourceChart"></canvas>
        <div class="source-legend" id="sourceLegend"></div>
      </div>
    </div>

  </div><!-- /page-content -->
</div><!-- /main -->

<script>
let currentData = null;
let newUserChart = null;
let activeUserChart = null;
let sourceChart = null;

const PALETTE = {
  indigo: '#6366f1',
  purple: '#8b5cf6',
  green: '#10b981',
  blue: '#3b82f6',
  cyan: '#06b6d4',
  orange: '#f97316',
  yellow: '#fbbf24',
  pink: '#ec4899',
  slate: '#64748b',
};

const CHART_COLORS = Object.values(PALETTE);

const TT = {
  backgroundColor: '#1a1d27',
  titleColor: '#94a3b8',
  bodyColor: '#f1f5f9',
  borderColor: 'rgba(255,255,255,.07)',
  borderWidth: 1,
  padding: { x: 14, y: 10 },
  cornerRadius: 10,
  displayColors: true,
  boxWidth: 8, boxHeight: 8,
  usePointStyle: true,
  titleFont: { size: 12, weight: '400' },
  bodyFont: { size: 12 },
};

function fmtK(v) {
  if (!v) return '0';
  if (v >= 1000000) return (v / 1000000).toFixed(1) + 'M';
  if (v >= 1000) return (v % 1000 === 0 ? v / 1000 : (v / 1000).toFixed(1)) + 'K';
  return v.toLocaleString();
}

function fmtDiff(v) {
  const a = Math.abs(v);
  const sign = v > 0 ? '+' : (v < 0 ? '-' : '');
  return sign + fmtK(a);
}

function getSign(v) {
  return v > 0 ? 'up' : (v < 0 ? 'down' : 'neutral');
}

async function refreshData() {
  showLoading(true);
  const from = document.getElementById('mktFrom').value;
  const to   = document.getElementById('mktTo').value;

  try {
    const url = new URL('/' + '{{ config("marketing-tool.prefix") }}' + '/data', window.location.origin);
    if (from) url.searchParams.append('from', from);
    if (to)   url.searchParams.append('to', to);

    const res = await fetch(url);
    currentData = await res.json();

    updateKPIs();
    renderMonthCmp();
    renderFunnels();
    updateCharts();
    renderSourceDonut();
  } catch (e) {
    console.error('Data load error', e);
  } finally {
    showLoading(false);
  }
}

function showLoading(show) {
  document.getElementById('loadingOverlay').className = 'loading-overlay' + (show ? ' visible' : '');
}

function updateKPIs() {
  const d = currentData;
  const funnels = d.funnels;

  // New users total
  const newTotal = funnels?.new_user?.[0]?.total ?? 0;
  const activeTotal = funnels?.active_user?.[0]?.total ?? 0;

  // Use daily_stats sum for period
  const newSum = d.daily_stats?.new_user?.total?.reduce((a, b) => a + b, 0) ?? 0;
  const activeSum = d.daily_stats?.active_user?.total?.reduce((a, b) => a + b, 0) ?? 0;

  const curMonth = d.monthly_comparison?.current;
  const transfersVal = d.daily_stats?.transfers?.total?.reduce?.((a, b) => a + b, 0) ?? 0;

  document.getElementById('kpiTotalUsers').textContent = fmtK(newTotal);
  document.getElementById('kpiActiveUsers').textContent = fmtK(activeTotal);
  document.getElementById('kpiNewUsers').textContent = fmtK(newSum);
  document.getElementById('kpiTransfers').textContent = fmtK(transfersVal);

  // Update subtexts
  if (curMonth) {
    const sign = getSign(curMonth.diff);
    const arrow = curMonth.diff > 0 ? '▲' : (curMonth.diff < 0 ? '▼' : '');
    document.querySelectorAll('#kpiArea .kpi-sub')[0].innerHTML =
      `<span class="kpi-badge ${sign}">${arrow} ${Math.abs(curMonth.pct)}%</span><span class="kpi-desc">o'tgan oyga nisbatan</span>`;
  }

  // Active Users Subtext
  const activeRate = newTotal > 0 ? ((activeTotal / newTotal) * 100).toFixed(1) : 0;
  document.querySelectorAll('#kpiArea .kpi-sub')[1].innerHTML =
    `<span class="kpi-badge up">${activeRate}%</span><span class="kpi-desc">faollik darajasi</span>`;

  // New Users Subtext
  document.querySelectorAll('#kpiArea .kpi-sub')[2].innerHTML =
    `<span class="kpi-badge neutral">Joriy</span><span class="kpi-desc">davrdagi yangi ro'yxatdan o'tganlar</span>`;

  // Transfers Subtext
  document.querySelectorAll('#kpiArea .kpi-sub')[3].innerHTML =
    `<span class="kpi-badge up">Muvaffaqiyatli</span><span class="kpi-desc">tranzaksiyalar soni</span>`;
}

function renderMonthCmp() {
  const area = document.getElementById('monthCmpArea');
  const { history, current } = currentData.monthly_comparison;
  const maxVal = Math.max(...history.map(m => m.total), 1);
  const BAR_MAX = 80;

  const barsHtml = history.map((h, i) => {
    const height = Math.max(8, Math.round(h.total / maxVal * BAR_MAX));
    const ruH = h.total > 0 ? Math.max(2, Math.round(height * (h.ru / h.total))) : 0;
    const uzH = height - ruH;
    const alpha = 0.4 + (i / history.length) * 0.6;
    return `
      <div class="month-col">
        <div class="month-bar-wrap">
          <div class="month-bar-val">${fmtK(h.total)}</div>
          <div class="month-bar" style="height:${height}px;display:flex;flex-direction:column;overflow:hidden;width:55%;border-radius:6px 6px 0 0">
            <div style="flex:0 0 ${ruH}px;background:rgba(59,130,246,${alpha})"></div>
            <div style="flex:0 0 ${uzH}px;background:rgba(99,102,241,${alpha})"></div>
          </div>
        </div>
        <div class="month-baseline"></div>
        <div class="month-lbl">${h.name}</div>
        <div class="month-flags">
          <span class="flag-badge">🇺🇿 ${fmtK(h.uz)}</span>
          <span class="flag-badge">🇷🇺 ${fmtK(h.ru)}</span>
        </div>
      </div>`;
  }).join('');

  let curHtml = '';
  if (!current.total) {
    curHtml = `<div class="month-current"><div style="font-size:32px;opacity:.2">—</div><div class="cur-month-desc">Ma'lumot yo'q</div></div>`;
  } else {
    const sign = getSign(current.diff);
    const arrow = current.diff > 0 ? '▲' : (current.diff < 0 ? '▼' : '');
    curHtml = `
      <div class="month-current">
        <div class="cur-month-name">${current.name}</div>
        <div class="cur-month-range">${current.range}</div>
        <div style="display:flex;align-items:baseline;gap:4px;margin-bottom:4px">
          <div class="cur-month-num">${fmtK(current.total)}</div>
          <div class="cur-month-pct ${sign}">${arrow}${Math.abs(current.pct)}%</div>
        </div>
        <div class="cur-month-diff ${sign}">${fmtDiff(current.diff)} ta</div>
        <div class="cur-month-desc">o'tgan oy shu davriga nisbatan</div>
      </div>`;
  }

  area.innerHTML = `
    <div class="chart-hdr" style="position: relative;">
      <div class="info-tooltip-container" style="top: 0; right: 0;">
        <span class="info-icon">ℹ️</span>
        <div class="info-tooltip">Oylar kesimida foydalanuvchilar o'sishi va o'tgan oyning shu davriga nisbatan taqqoslama ko'rsatkichlari</div>
      </div>
      <div><div class="chart-title">Oylik taqqoslama</div><div class="chart-sub">So'nggi oylar dinamikasi</div></div>
    </div>
    <div class="month-cmp">
      <div class="month-bars">${barsHtml}</div>
      ${curHtml}
    </div>`;
}

function renderFunnels() {
  const area = document.getElementById('funnelArea');
  const { new_user, active_user } = currentData.funnels;

  function funnel(items, color, icon, title) {
    if (!items || !items.length) return '';
    const maxVal = items[0]?.total || 1;
    const rows = items.map(item => {
      const pct = maxVal > 0 ? ((item.total / maxVal) * 100).toFixed(1) : 0;
      return `
        <div class="funnel-row">
          <div class="funnel-lbl">
            ${item.label}
            <div class="funnel-flags">
              <span class="flag-badge">🇺🇿 ${item.uz.toLocaleString()}</span>
              <span class="flag-badge">🇷🇺 ${item.ru.toLocaleString()}</span>
            </div>
          </div>
          <div class="funnel-bar-wrap"><div class="funnel-bar" style="width:${pct}%;background:${color}"></div></div>
          <div class="funnel-val">${item.total.toLocaleString()}</div>
          <div class="funnel-pct">${pct}%</div>
        </div>`;
    }).join('');

    const tooltipText = title === 'Yangi foydalanuvchi funnel' 
      ? 'Yangi foydalanuvchilarning ro\'yxatdan o\'tishdan boshlab birinchi tranzaksiyagacha bo\'lgan bosqichlari' 
      : 'Faol foydalanuvchilarning faollikdan boshlab tranzaksiyagacha bo\'lgan konversiya bosqichlari';

    return `
      <div class="card" style="position: relative;">
        <div class="info-tooltip-container" style="top: 18px; right: 18px;">
          <span class="info-icon">ℹ️</span>
          <div class="info-tooltip">${tooltipText}</div>
        </div>
        <div class="funnel-hdr">
          <div class="funnel-icon" style="background:${color}1a">${icon}</div>
          <div class="funnel-title">${title}</div>
        </div>
        ${rows}
      </div>`;
  }

  area.innerHTML =
    funnel(new_user, PALETTE.indigo, '👤', 'Yangi foydalanuvchi funnel') +
    ((active_user && active_user[0]?.total > 0)
      ? funnel(active_user, PALETTE.green, '⚡', 'Faol foydalanuvchi funnel')
      : '');
}

function updateCharts() {
  const nuType = document.getElementById('mktNewUserSel').value;
  const auType = document.getElementById('mktActiveSel').value;
  const stats  = currentData.daily_stats;

  const gridColor = 'rgba(255,255,255,0.04)';
  const tickColor = '#64748b';

  // ── New User Chart ──
  if (newUserChart) newUserChart.destroy();
  newUserChart = new Chart(document.getElementById('mktNewUserChart'), {
    type: nuType,
    data: {
      labels: stats.labels,
      datasets: [
        { label: 'Jami', data: stats.new_user.total, borderColor: PALETTE.indigo, backgroundColor: nuType === 'bar' ? PALETTE.indigo + 'cc' : PALETTE.indigo + '12', fill: nuType !== 'bar', tension: 0.45, borderRadius: 5, borderWidth: 2 },
        { label: '🇺🇿 UZ',  data: stats.new_user.uz,    borderColor: PALETTE.blue,   backgroundColor: nuType === 'bar' ? PALETTE.blue   + 'cc' : PALETTE.blue   + '0d', fill: false, tension: 0.45, borderRadius: 5, borderWidth: 2 },
        { label: '🇷🇺 RU',  data: stats.new_user.ru,    borderColor: PALETTE.green,  backgroundColor: nuType === 'bar' ? PALETTE.green  + 'cc' : PALETTE.green  + '0d', fill: false, tension: 0.45, borderRadius: 5, borderWidth: 2 },
      ]
    },
    options: chartOptions(gridColor, tickColor),
  });

  // ── Active User Chart ──
  const showActive = stats.active_user?.total?.some(v => v > 0);
  document.getElementById('activeChartWrap').style.display = showActive ? 'block' : 'none';
  if (showActive) {
    if (activeUserChart) activeUserChart.destroy();
    activeUserChart = new Chart(document.getElementById('mktActiveChart'), {
      type: auType,
      data: {
        labels: stats.labels,
        datasets: [
          { label: 'Faol (jami)', data: stats.active_user.total, borderColor: PALETTE.purple, backgroundColor: nuType === 'bar' ? PALETTE.purple + 'cc' : PALETTE.purple + '10', fill: auType !== 'bar', tension: 0.42, borderRadius: 4, borderWidth: 2 },
          { label: '🇺🇿 Faol UZ', data: stats.active_user.uz,    borderColor: PALETTE.blue,   backgroundColor: 'transparent', fill: false, tension: 0.42, borderRadius: 4, borderWidth: 2 },
          { label: '🇷🇺 Faol RU', data: stats.active_user.ru,    borderColor: PALETTE.orange, backgroundColor: 'transparent', fill: false, tension: 0.42, borderRadius: 4, borderWidth: 2 },
        ]
      },
      options: chartOptions(gridColor, tickColor),
    });
  }
}

function chartOptions(gridColor, tickColor) {
  return {
    responsive: true,
    plugins: {
      legend: { display: true, position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 8, color: '#94a3b8', font: { size: 11 } } },
      tooltip: { ...TT, mode: 'index', intersect: false },
    },
    scales: {
      x: { grid: { color: gridColor }, ticks: { color: tickColor, maxTicksLimit: 10, font: { size: 11 } }, border: { color: 'transparent' } },
      y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 11 }, callback: v => fmtK(v) }, border: { color: 'transparent' } },
    },
  };
}

function renderSourceDonut() {
  const sources = currentData.sources;
  const wrap = document.getElementById('sourceChartWrap');
  if (!sources || !sources.length) { wrap.style.display = 'none'; return; }
  wrap.style.display = 'block';

  const labels = sources.map(s => s.label);
  const data   = sources.map(s => s.value);
  const total  = data.reduce((a, b) => a + b, 0);

  if (sourceChart) sourceChart.destroy();

  const centerPlugin = {
    id: 'center',
    afterDraw(chart) {
      const { ctx, chartArea: { top, bottom, left, right } } = chart;
      const cx = (left + right) / 2, cy = (top + bottom) / 2;
      ctx.save();
      ctx.textAlign = 'center';
      ctx.fillStyle = '#f1f5f9'; ctx.font = '700 22px Inter,sans-serif';
      ctx.fillText(fmtK(total), cx, cy - 6);
      ctx.fillStyle = '#64748b'; ctx.font = '500 11px Inter,sans-serif';
      ctx.fillText('jami', cx, cy + 12);
      ctx.restore();
    }
  };

  sourceChart = new Chart(document.getElementById('sourceChart'), {
    type: 'doughnut',
    data: { labels, datasets: [{ data, backgroundColor: CHART_COLORS, borderWidth: 3, borderColor: '#1a1d27', hoverOffset: 6 }] },
    options: {
      responsive: true, maintainAspectRatio: true, cutout: '70%',
      plugins: {
        legend: { display: false },
        tooltip: { ...TT, callbacks: { label: ctx => ` ${ctx.raw.toLocaleString()} (${(ctx.raw / total * 100).toFixed(1)}%)` } },
      },
    },
    plugins: [centerPlugin],
  });

  document.getElementById('sourceLegend').innerHTML = sources.map((s, i) => `
    <div class="legend-item">
      <div class="legend-dot" style="background:${CHART_COLORS[i % CHART_COLORS.length]}"></div>
      <span class="legend-name">${s.label}</span>
      <span class="legend-val">${s.value.toLocaleString()}</span>
      <span class="legend-pct">${(s.value / total * 100).toFixed(1)}%</span>
    </div>`).join('');
}

// ── Init ──
const today    = new Date();
const lastMonth = new Date(); lastMonth.setDate(today.getDate() - 29);
document.getElementById('mktFrom').value = lastMonth.toISOString().split('T')[0];
document.getElementById('mktTo').value   = today.toISOString().split('T')[0];

refreshData();
</script>
</body>
</html>
