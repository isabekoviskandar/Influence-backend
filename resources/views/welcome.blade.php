<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Influence - Telegram Analytics for Uzbekistan</title>
    <meta name="description" content="Influence tracks Telegram views, reactions, posting times, and growth for channels in Uzbekistan.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=Geist+Mono&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --bg-page: #0a0a0f;
            --bg-surface: #111118;
            --bg-surface-raised: #15151f;
            --border: #1e1e2e;
            --text: #f6f7fb;
            --muted: #9090a8;
            --label: #a1a1aa;
            --accent: #6366f1;
            --success: #22c55e;
            --warning: #f59e0b;
            --max-content-width: 1100px;
            --section-padding: 80px;
            --radius: 8px;
        }

        body {
            background:
                radial-gradient(circle at 50% -10%, rgba(99, 102, 241, 0.14), transparent 34rem),
                linear-gradient(180deg, #0a0a0f 0%, #0c0c12 48%, #0a0a0f 100%);
            position: relative;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            opacity: 0.32;
            background-image:
                radial-gradient(circle at 1px 1px, rgba(255, 255, 255, 0.13) 1px, transparent 0);
            background-size: 18px 18px;
            mix-blend-mode: overlay;
        }

        body > * {
            position: relative;
            z-index: 1;
        }

        .nav-scrolled {
            background-color: rgba(10, 10, 15, 0.86) !important;
            backdrop-filter: blur(12px);
        }

        .section-label {
            color: var(--label);
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            margin-bottom: 16px;
            text-transform: uppercase;
        }

        .surface-band {
            background: rgba(17, 17, 24, 0.58);
            border-bottom: 1px solid var(--border);
            border-top: 1px solid var(--border);
        }

        .metric-card,
        .feature-block,
        .mockup-panel {
            background: linear-gradient(180deg, rgba(21, 21, 31, 0.9), rgba(17, 17, 24, 0.96));
            border: 1px solid var(--border);
            border-radius: 8px;
        }

        .feature-block {
            display: grid;
            gap: 36px;
            grid-template-columns: minmax(0, 1.12fr) minmax(280px, 0.88fr);
            padding: 28px;
        }

        .feature-block:nth-child(even) {
            grid-template-columns: minmax(280px, 0.88fr) minmax(0, 1.12fr);
        }

        .feature-block:nth-child(even) .feature-visual {
            order: 2;
        }

        .heatmap-grid {
            display: grid;
            gap: 3px;
            grid-template-columns: repeat(24, minmax(10px, 1fr));
        }

        .heatmap-cell {
            aspect-ratio: 1;
            background: #161622;
            border: 1px solid rgba(30, 30, 46, 0.9);
            border-radius: 2px;
        }

        .heatmap-cell.low { background: rgba(99, 102, 241, 0.18); }
        .heatmap-cell.mid { background: rgba(99, 102, 241, 0.42); }
        .heatmap-cell.hot {
            background: rgba(99, 102, 241, 0.92);
            box-shadow: 0 0 12px rgba(99, 102, 241, 0.38);
        }

        .mock-window-bar {
            border-bottom: 1px solid var(--border);
            background: rgba(10, 10, 15, 0.55);
        }

        .sparkline {
            width: 100%;
            height: 76px;
            overflow: visible;
        }

        .footer-cta {
            background:
                radial-gradient(circle at 50% 45%, rgba(99, 102, 241, 0.15), transparent 31rem),
                #0a0a0f;
            border-top: 1px solid var(--border);
        }

        .step-connector {
            flex-grow: 1;
            height: 1px;
            border-top: 1px dashed rgba(99, 102, 241, 0.55);
            margin: 16px 20px 0;
        }

        @media (max-width: 900px) {
            .feature-block,
            .feature-block:nth-child(even) {
                grid-template-columns: 1fr;
            }

            .feature-block:nth-child(even) .feature-visual {
                order: 0;
            }
        }
    </style>
</head>
<body class="antialiased">
    <nav id="navbar" class="sticky top-0 z-50 w-full bg-bg-page/80 backdrop-blur-md border-b border-divider">
        <div class="max-w-[1100px] mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-8">
                <div class="flex items-center gap-2 font-semibold">
                    <div class="w-2.5 h-2.5 bg-accent rounded-[2px]"></div>
                    <span>Influence</span>
                </div>
                <div class="hidden md:flex items-center gap-6 text-sm text-muted">
                    <a href="#features" class="hover:text-text transition-colors">Features</a>
                    <a href="#pricing" class="hover:text-text transition-colors">Pricing</a>
                    <a href="#examples" class="hover:text-text transition-colors">Examples</a>
                    <a href="#changelog" class="hover:text-text transition-colors">Changelog</a>
                    <a href="#docs" class="hover:text-text transition-colors">Docs</a>
                </div>
            </div>
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="hidden sm:inline-flex items-center rounded-lg border border-divider bg-surface p-1 text-[12px] font-semibold">
                    <button class="rounded-md bg-accent px-2.5 py-1 text-white">UZ</button>
                    <button class="px-2.5 py-1 text-muted hover:text-white">EN</button>
                </div>
                <a href="/login" class="text-sm font-medium hover:text-accent transition-colors hidden sm:block">Sign in</a>
                <a href="/register" class="btn-primary">Start free</a>
            </div>
        </div>
    </nav>

    <section class="section-padding pt-[128px] relative overflow-hidden">
        <div class="container-custom text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-accent/30 bg-accent/10 text-accent text-[12px] font-medium mb-8">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-accent opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-accent"></span>
                </span>
                Built for Telegram channels in Uzbekistan
            </div>

            <h1 class="text-4xl md:text-6xl font-bold tracking-tight mb-6">
                Telegram analytics <br class="hidden md:block"> for serious channels.
            </h1>
            <p class="text-base md:text-xl text-muted max-w-2xl mx-auto mb-10 px-4">
                O'zbekistonning 500+ Telegram kanali allaqachon ulangan. Influence views, reactions, posting times, and growth signalsni avtomatik kuzatadi.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mb-12">
                <a href="/register" class="btn-primary w-full sm:w-auto">Start for free</a>
                <a href="#examples" class="flex items-center gap-2 text-muted hover:text-white transition-colors px-5">
                    See live metrics <span aria-hidden="true">-></span>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 max-w-[760px] mx-auto mb-14 text-left">
                <div class="metric-card p-5">
                    <div class="font-mono text-2xl font-bold text-white">847</div>
                    <div class="text-[13px] text-muted mt-1">ta kanal ulangan</div>
                </div>
                <div class="metric-card p-5">
                    <div class="font-mono text-2xl font-bold text-white">2.3M</div>
                    <div class="text-[13px] text-muted mt-1">post tahlil qilindi</div>
                </div>
                <div class="metric-card p-5">
                    <div class="font-mono text-2xl font-bold text-white">+34%</div>
                    <div class="text-[13px] text-muted mt-1">o'rtacha engagement oshdi</div>
                </div>
            </div>

            <div class="relative max-w-4xl mx-auto px-4">
                <div class="absolute inset-0 bg-accent/20 blur-[120px] rounded-full"></div>
                <div class="relative bg-surface border border-divider rounded-xl overflow-hidden shadow-2xl">
                    <img src="/images/hero-dashboard.png" alt="Influence Dashboard" class="w-full h-auto opacity-95 scale-105 md:scale-100">
                </div>
            </div>
        </div>
    </section>

    <section class="border-y border-divider py-10">
        <div class="container-custom">
            <p class="section-label text-center">POPULAR CHANNEL SEGMENTS</p>
            <div class="flex flex-wrap justify-center gap-3">
                <span class="px-4 py-2 border border-divider rounded-full text-muted text-sm hover:border-accent hover:text-white transition-all cursor-default">News</span>
                <span class="px-4 py-2 border border-divider rounded-full text-muted text-sm hover:border-accent hover:text-white transition-all cursor-default">Education</span>
                <span class="px-4 py-2 border border-divider rounded-full text-muted text-sm hover:border-accent hover:text-white transition-all cursor-default">Finance</span>
                <span class="px-4 py-2 border border-divider rounded-full text-muted text-sm hover:border-accent hover:text-white transition-all cursor-default">Marketing</span>
                <span class="px-4 py-2 border border-divider rounded-full text-muted text-sm hover:border-accent hover:text-white transition-all cursor-default">Tech</span>
                <span class="px-4 py-2 border border-divider rounded-full text-muted text-sm hover:border-accent hover:text-white transition-all cursor-default">Lifestyle</span>
            </div>
        </div>
    </section>

    <section class="section-padding">
        <div class="container-custom grid lg:grid-cols-2 gap-14 items-center">
            <div>
                <span class="section-label">WHY INFLUENCE</span>
                <h2 class="text-[34px] md:text-[38px] font-semibold mb-6">Stop guessing. Start knowing.</h2>
                <p class="text-muted mb-8">
                    Most Telegram teams still check views manually, one post at a time. Influence gives Uzbekistan-based channels trend history, peak-hour analysis, and post-level engagement in one dashboard.
                </p>

                <div class="space-y-5">
                    <div class="flex items-center gap-4 pb-5 border-b border-divider">
                        <div class="w-5 h-5 flex items-center justify-center text-red-500">x</div>
                        <span class="text-muted">Manual view counting misses 24-hour and 30-day trend changes</span>
                    </div>
                    <div class="flex items-center gap-4 pb-5 border-b border-divider">
                        <div class="w-5 h-5 flex items-center justify-center text-red-500">x</div>
                        <span class="text-muted">Telegram alone does not show your best posting windows</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-5 h-5 flex items-center justify-center text-accent">✓</div>
                        <span class="text-white font-medium">Influence detects reach patterns across posts, channels, and time</span>
                    </div>
                </div>
            </div>

            <div class="mockup-panel p-6 md:p-8 overflow-hidden">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-sm font-semibold">Best Time to Post</h3>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span>
                        <span class="text-[10px] text-muted">LIVE SYNC</span>
                    </div>
                </div>
                <div class="overflow-x-auto pb-2 -mx-2 px-2">
                    <div class="heatmap-grid mb-4 min-w-[520px] md:min-w-0">
                        @for ($i = 0; $i < 168; $i++)
                            @php
                                $hour = $i % 24;
                                $day = intdiv($i, 24);
                                $class = ($hour >= 19 && $hour <= 21) ? 'hot' : (($hour >= 17 && $hour <= 23) ? 'mid' : (($day % 2 === 0 && $hour >= 11 && $hour <= 13) ? 'low' : ''));
                            @endphp
                            <div class="heatmap-cell {{ $class }}"></div>
                        @endfor
                    </div>
                </div>
                <div class="flex justify-between text-[10px] text-muted font-mono uppercase">
                    <span>Mon</span>
                    <span>Sun</span>
                </div>
                <div class="mt-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                    <span class="text-[12px] text-muted">Audience peak: <span class="text-white font-medium">19:00-21:00</span></span>
                    <span class="text-[12px] text-accent font-medium bg-accent/10 px-2 py-0.5 rounded">3x more views</span>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="section-padding surface-band">
        <div class="container-custom">
            <div class="text-center mb-12">
                <span class="section-label">FEATURES</span>
                <h2 class="text-3xl md:text-[38px] font-semibold mb-4">Built around channel growth signals.</h2>
                <p class="text-muted text-sm md:text-[16px]">Fewer empty cards, more operational data your team can act on.</p>
            </div>

            <div class="space-y-6">
                <div class="feature-block">
                    <div class="feature-visual mockup-panel overflow-hidden">
                        <div class="mock-window-bar flex items-center gap-2 px-4 py-3">
                            <span class="h-2.5 w-2.5 rounded-full bg-red-500/50"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-yellow-500/50"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-green-500/50"></span>
                            <span class="ml-auto text-[11px] text-muted">posting-heatmap</span>
                        </div>
                        <div class="p-5">
                            <div class="heatmap-grid">
                                @for ($i = 0; $i < 168; $i++)
                                    @php
                                        $hour = $i % 24;
                                        $class = ($hour >= 19 && $hour <= 21) ? 'hot' : (($hour >= 16 && $hour <= 23) ? 'mid' : (($hour >= 10 && $hour <= 13) ? 'low' : ''));
                                    @endphp
                                    <div class="heatmap-cell {{ $class }}"></div>
                                @endfor
                            </div>
                            <div class="mt-5 grid grid-cols-3 gap-3 text-[12px]">
                                <div class="rounded-lg border border-divider bg-bg-page p-3">
                                    <div class="font-mono text-white">21:00</div>
                                    <div class="text-muted">peak hour</div>
                                </div>
                                <div class="rounded-lg border border-divider bg-bg-page p-3">
                                    <div class="font-mono text-white">3.1x</div>
                                    <div class="text-muted">more views</div>
                                </div>
                                <div class="rounded-lg border border-divider bg-bg-page p-3">
                                    <div class="font-mono text-white">87%</div>
                                    <div class="text-muted">active</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col justify-center">
                        <span class="section-label">BEST TIME TO POST</span>
                        <h3 class="text-2xl md:text-[30px] font-semibold mb-4">Post when Uzbekistan audiences are awake and reacting.</h3>
                        <ul class="space-y-3 text-[15px] text-muted">
                            <li class="flex gap-3"><span class="text-accent">✓</span><span>Kanallar 19:00-21:00 da 3x ko'proq views oladi.</span></li>
                            <li class="flex gap-3"><span class="text-accent">✓</span><span>Weekday and weekend heatmaps update after every synced post.</span></li>
                            <li class="flex gap-3"><span class="text-accent">✓</span><span>Best posting windows are calculated from your own 30-day history.</span></li>
                        </ul>
                    </div>
                </div>

                <div class="feature-block">
                    <div class="feature-visual mockup-panel overflow-hidden">
                        <div class="mock-window-bar flex items-center px-4 py-3">
                            <span class="text-[11px] text-muted">post-performance</span>
                            <span class="ml-auto rounded bg-success/10 px-2 py-0.5 text-[11px] text-success">+34%</span>
                        </div>
                        <div class="p-5 space-y-3">
                            @foreach ([
                                ['caption' => 'Morning market brief', 'views' => '28.4K', 'rate' => '+18%'],
                                ['caption' => 'Sponsor placement', 'views' => '41.2K', 'rate' => '+46%'],
                                ['caption' => 'Evening digest', 'views' => '36.9K', 'rate' => '+31%'],
                            ] as $post)
                                <div class="rounded-lg border border-divider bg-bg-page p-4">
                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <div class="text-sm font-medium text-white">{{ $post['caption'] }}</div>
                                            <div class="text-[11px] text-muted">Views tracked over first 24 hours</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-mono text-lg font-bold text-white">{{ $post['views'] }}</div>
                                            <div class="text-[11px] text-success">{{ $post['rate'] }}</div>
                                        </div>
                                    </div>
                                    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-[#1a1a26]">
                                        <div class="h-full rounded-full bg-accent" style="width: {{ $loop->index === 0 ? '62' : ($loop->index === 1 ? '88' : '74') }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex flex-col justify-center">
                        <span class="section-label">POST ANALYTICS</span>
                        <h3 class="text-2xl md:text-[30px] font-semibold mb-4">Know which posts deserve promotion before reach fades.</h3>
                        <ul class="space-y-3 text-[15px] text-muted">
                            <li class="flex gap-3"><span class="text-accent">✓</span><span>First 60-minute velocity flags posts that can outperform baseline by 25%+.</span></li>
                            <li class="flex gap-3"><span class="text-accent">✓</span><span>Views, reactions, and engagement rate stay tied to every post.</span></li>
                            <li class="flex gap-3"><span class="text-accent">✓</span><span>Compare sponsor posts against organic posts in the same dashboard.</span></li>
                        </ul>
                    </div>
                </div>

                <div class="feature-block">
                    <div class="feature-visual mockup-panel overflow-hidden">
                        <div class="mock-window-bar flex items-center px-4 py-3">
                            <span class="text-[11px] text-muted">multi-channel-overview</span>
                            <span class="ml-auto text-[11px] text-muted">30 days</span>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4 p-5">
                            <div class="rounded-lg border border-divider bg-bg-page p-4">
                                <div class="text-[11px] text-muted mb-2">News channel</div>
                                <div class="font-mono text-2xl font-bold">126K</div>
                                <svg class="sparkline mt-3" viewBox="0 0 220 76" fill="none">
                                    <path d="M0 58 C28 54 34 40 58 43 C84 46 82 24 108 28 C136 32 134 18 164 20 C188 22 196 12 220 10" stroke="#6366f1" stroke-width="3" fill="none"/>
                                    <path d="M0 58 C28 54 34 40 58 43 C84 46 82 24 108 28 C136 32 134 18 164 20 C188 22 196 12 220 10 L220 76 L0 76 Z" fill="url(#sparkA)"/>
                                    <defs><linearGradient id="sparkA" x1="0" y1="10" x2="0" y2="76"><stop stop-color="#6366f1" stop-opacity=".25"/><stop offset="1" stop-color="#6366f1" stop-opacity="0"/></linearGradient></defs>
                                </svg>
                            </div>
                            <div class="rounded-lg border border-divider bg-bg-page p-4">
                                <div class="text-[11px] text-muted mb-2">Education channel</div>
                                <div class="font-mono text-2xl font-bold">74K</div>
                                <svg class="sparkline mt-3" viewBox="0 0 220 76" fill="none">
                                    <path d="M0 48 C24 46 32 52 52 42 C76 30 88 36 112 30 C136 24 148 27 170 18 C194 10 204 18 220 12" stroke="#22c55e" stroke-width="3" fill="none"/>
                                    <path d="M0 48 C24 46 32 52 52 42 C76 30 88 36 112 30 C136 24 148 27 170 18 C194 10 204 18 220 12 L220 76 L0 76 Z" fill="url(#sparkB)"/>
                                    <defs><linearGradient id="sparkB" x1="0" y1="12" x2="0" y2="76"><stop stop-color="#22c55e" stop-opacity=".22"/><stop offset="1" stop-color="#22c55e" stop-opacity="0"/></linearGradient></defs>
                                </svg>
                            </div>
                            <div class="rounded-lg border border-divider bg-bg-page p-4 sm:col-span-2">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-[11px] text-muted mb-2">Agency total</div>
                                        <div class="font-mono text-2xl font-bold">12 channels</div>
                                    </div>
                                    <div class="text-right text-[12px] text-success">+9.8K subscribers</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col justify-center">
                        <span class="section-label">MULTI-CHANNEL VIEW</span>
                        <h3 class="text-2xl md:text-[30px] font-semibold mb-4">Run every Telegram channel from one compact workspace.</h3>
                        <ul class="space-y-3 text-[15px] text-muted">
                            <li class="flex gap-3"><span class="text-accent">✓</span><span>Agencies can compare 10+ channels without spreadsheets.</span></li>
                            <li class="flex gap-3"><span class="text-accent">✓</span><span>30-day subscriber and post trends show growth direction fast.</span></li>
                            <li class="flex gap-3"><span class="text-accent">✓</span><span>Spot channels with declining reach before advertisers notice.</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-padding">
        <div class="container-custom">
            <div class="text-center mb-14">
                <span class="section-label">HOW IT WORKS</span>
                <h2 class="text-3xl md:text-[38px] font-semibold">Set up in 60 seconds.</h2>
            </div>

            <div class="grid md:grid-cols-3 gap-8 mb-14">
                <div class="text-center md:text-left">
                    <div class="flex items-center justify-center md:justify-start gap-4 mb-6">
                        <div class="w-8 h-8 rounded-full bg-accent text-white flex items-center justify-center font-bold">1</div>
                        <div class="step-connector hidden md:block"></div>
                    </div>
                    <h3 class="text-lg font-semibold mb-3 text-white">Add the bot</h3>
                    <p class="text-muted text-[15px]">Search @InfluenceBot on Telegram and add it as an admin to your channel.</p>
                </div>
                <div class="text-center md:text-left">
                    <div class="flex items-center justify-center md:justify-start gap-4 mb-6">
                        <div class="w-8 h-8 rounded-full bg-accent text-white flex items-center justify-center font-bold">2</div>
                        <div class="step-connector hidden md:block"></div>
                    </div>
                    <h3 class="text-lg font-semibold mb-3 text-white">Bot starts tracking</h3>
                    <p class="text-muted text-[15px]">Views, reactions, post timing, and subscriber changes start syncing automatically.</p>
                </div>
                <div class="text-center md:text-left">
                    <div class="flex items-center justify-center md:justify-start gap-4 mb-6">
                        <div class="w-8 h-8 rounded-full bg-accent text-white flex items-center justify-center font-bold">3</div>
                    </div>
                    <h3 class="text-lg font-semibold mb-3 text-white">Open your dashboard</h3>
                    <p class="text-muted text-[15px]">Log in via magic link. Your analytics are ready with no manual setup.</p>
                </div>
            </div>

            <div class="terminal max-w-[600px] mx-auto shadow-2xl">
                <div class="flex gap-2 mb-6">
                    <div class="w-2.5 h-2.5 rounded-full bg-red-500/50"></div>
                    <div class="w-2.5 h-2.5 rounded-full bg-yellow-500/50"></div>
                    <div class="w-2.5 h-2.5 rounded-full bg-green-500/50"></div>
                </div>
                <div class="space-y-1 text-[14px]">
                    <div class="terminal-line"><span class="terminal-prompt">-></span> <span>Bot added to @yourchannel</span></div>
                    <div class="terminal-line"><span class="terminal-prompt">-></span> <span>Syncing historical posts... <span class="text-green-500">done</span> (45 posts)</span></div>
                    <div class="terminal-line"><span class="terminal-prompt">-></span> <span>Dashboard ready: <a href="#" class="text-accent underline">influence.uz/dashboard</a></span></div>
                </div>
            </div>
        </div>
    </section>

    <section id="examples" class="section-padding surface-band">
        <div class="container-custom">
            <div class="text-center mb-12 px-4">
                <span class="section-label">SEE IT IN ACTION</span>
                <h2 class="text-3xl md:text-[38px] font-semibold mb-4">Real data. 30-day movement.</h2>
            </div>

            <div class="max-w-[900px] mx-auto">
                <div class="demo-tabs justify-center">
                    <div class="demo-tab active" data-tab="overview">Channel Overview</div>
                    <div class="demo-tab" data-tab="analytics">Post Analytics</div>
                    <div class="demo-tab" data-tab="heatmap">Engagement Heatmap</div>
                </div>

                <div class="bg-surface border border-divider rounded-xl min-h-[400px] p-1 shadow-2xl overflow-hidden mb-8 mx-4 md:mx-0">
                    <div id="overview-panel" class="demo-panel active p-6 md:p-8">
                        <div class="grid sm:grid-cols-3 gap-5">
                            <div class="bg-bg-page border border-divider rounded-lg p-5">
                                <span class="text-muted text-[11px] font-bold block mb-3">NEWS CHANNEL</span>
                                <div class="text-2xl font-bold mb-1">55.2K</div>
                                <div class="text-[11px] text-green-500 font-medium mb-4">+0.8% subscribers / 24h</div>
                                <svg class="sparkline" viewBox="0 0 220 76" fill="none">
                                    <path d="M0 54 C30 48 42 56 66 42 C92 26 106 34 132 24 C158 14 174 22 220 10" stroke="#6366f1" stroke-width="3" fill="none"/>
                                </svg>
                            </div>
                            <div class="bg-bg-page border border-divider rounded-lg p-5">
                                <span class="text-muted text-[11px] font-bold block mb-3">EDUCATION HUB</span>
                                <div class="text-2xl font-bold mb-1">103.1K</div>
                                <div class="text-[11px] text-green-500 font-medium mb-4">+1.5% subscribers / 24h</div>
                                <svg class="sparkline" viewBox="0 0 220 76" fill="none">
                                    <path d="M0 62 C24 50 44 54 62 44 C84 32 104 38 126 29 C152 18 176 24 220 16" stroke="#22c55e" stroke-width="3" fill="none"/>
                                </svg>
                            </div>
                            <div class="bg-bg-page border border-divider rounded-lg p-5">
                                <span class="text-muted text-[11px] font-bold block mb-3">MARKET UPDATES</span>
                                <div class="text-2xl font-bold mb-1">44.7K</div>
                                <div class="text-[11px] text-green-500 font-medium mb-4">+0.6% subscribers / 24h</div>
                                <svg class="sparkline" viewBox="0 0 220 76" fill="none">
                                    <path d="M0 48 C24 52 36 38 58 42 C78 46 94 28 118 31 C144 34 158 18 184 20 C202 22 210 16 220 12" stroke="#f59e0b" stroke-width="3" fill="none"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div id="analytics-panel" class="demo-panel p-6 md:p-8">
                        <div class="space-y-4">
                            @for ($i = 0; $i < 4; $i++)
                                <div class="bg-bg-page border border-divider rounded-lg p-4 flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded bg-accent/20"></div>
                                        <div>
                                            <div class="text-sm font-medium">Sponsor placement analysis</div>
                                            <div class="text-[11px] text-muted">Posted {{ $i + 2 }}h ago</div>
                                        </div>
                                    </div>
                                    <div class="flex gap-6">
                                        <div class="text-right">
                                            <div class="text-sm font-bold">{{ 12 + ($i * 4) }}.4K</div>
                                            <div class="text-[10px] text-muted">VIEWS</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-bold">{{ 842 + ($i * 96) }}</div>
                                            <div class="text-[10px] text-muted">REACTIONS</div>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                    <div id="heatmap-panel" class="demo-panel p-6 md:p-8">
                        <div class="overflow-x-auto">
                            <div class="heatmap-grid min-w-[520px]">
                                @for ($i = 0; $i < 168; $i++)
                                    @php
                                        $hour = $i % 24;
                                        $class = ($hour >= 19 && $hour <= 21) ? 'hot' : (($hour >= 16 && $hour <= 23) ? 'mid' : (($hour >= 10 && $hour <= 13) ? 'low' : ''));
                                    @endphp
                                    <div class="heatmap-cell {{ $class }}"></div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-center text-[13px] text-muted">
                    Demo values mirror the kind of data available after connecting your own channel. <a href="/register" class="text-accent hover:underline">Start tracking -></a>
                </p>
            </div>
        </div>
    </section>

    <section id="pricing" class="section-padding">
        <div class="container-custom">
            <div class="text-center mb-12 px-4">
                <span class="section-label">PRICING</span>
                <h2 class="text-3xl md:text-[38px] font-semibold mb-4">Simple, honest pricing.</h2>
                <p class="text-muted text-sm md:text-[16px] mb-8">Start free. Upgrade when you need more.</p>

                <div class="inline-flex items-center p-1 bg-bg-surface border border-divider rounded-lg mb-12">
                    <button id="monthly-toggle" class="px-4 py-1.5 rounded-md text-sm font-medium bg-accent text-white transition-all">Monthly</button>
                    <button id="yearly-toggle" class="px-4 py-1.5 rounded-md text-sm font-medium text-muted hover:text-white transition-all">Yearly <span class="ml-1 text-[10px] text-accent font-bold">-20%</span></button>
                </div>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 px-4 md:px-0">
                <div class="card flex flex-col items-start gap-8">
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Free</h3>
                        <div class="text-4xl font-bold mb-4">$0 <span class="text-sm text-muted font-normal">/mo</span></div>
                    </div>
                    <ul class="space-y-4 w-full text-[14px]">
                        <li class="flex items-center gap-3"><span class="text-accent">✓</span> 1 channel</li>
                        <li class="flex items-center gap-3"><span class="text-accent">✓</span> 7-day analytics history</li>
                        <li class="flex items-center gap-3"><span class="text-accent">✓</span> Basic post tracking</li>
                        <li class="flex items-center gap-3"><span class="text-accent">✓</span> Views + reaction counts</li>
                        <li class="flex items-center gap-3"><span class="text-accent">✓</span> Magic link login</li>
                    </ul>
                    <a href="/register" class="btn-ghost border border-divider text-white text-[14px] w-full py-2 rounded-lg text-center font-medium mt-auto">Get started free</a>
                </div>

                <div class="card pricing-card featured flex flex-col items-start gap-8 scale-100 md:scale-105">
                    <div class="pricing-badge">Most Popular</div>
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Pro</h3>
                        <div class="text-4xl font-bold mb-4"><span class="price-val" data-monthly="12" data-yearly="9">$12</span> <span class="text-sm text-muted font-normal">/mo</span></div>
                    </div>
                    <ul class="space-y-4 w-full text-[14px]">
                        <li class="flex items-center gap-3"><span class="text-accent">✓</span> Unlimited channels</li>
                        <li class="flex items-center gap-3"><span class="text-accent">✓</span> 90-day history</li>
                        <li class="flex items-center gap-3"><span class="text-accent">✓</span> Engagement Rate + Potential Score</li>
                        <li class="flex items-center gap-3"><span class="text-accent">✓</span> Best Time to Post heatmap</li>
                        <li class="flex items-center gap-3"><span class="text-accent">✓</span> Competitor channel tracking</li>
                        <li class="flex items-center gap-3"><span class="text-accent">✓</span> Priority sync (every 5 min)</li>
                        <li class="flex items-center gap-3"><span class="text-accent">✓</span> PDF report export</li>
                    </ul>
                    <a href="/register" class="btn-primary w-full text-center py-2 rounded-lg font-medium mt-auto">Start Pro free</a>
                </div>

                <div class="card flex flex-col items-start gap-8 md:col-span-2 lg:col-span-1">
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Agency</h3>
                        <div class="text-4xl font-bold mb-4"><span class="price-val" data-monthly="49" data-yearly="39">$49</span> <span class="text-sm text-muted font-normal">/mo</span></div>
                    </div>
                    <ul class="space-y-4 w-full text-[14px]">
                        <li class="flex items-center gap-3"><span class="text-accent">✓</span> Everything in Pro</li>
                        <li class="flex items-center gap-3"><span class="text-accent">✓</span> Multi-user seats (up to 5)</li>
                        <li class="flex items-center gap-3"><span class="text-accent">✓</span> White-label PDF reports</li>
                        <li class="flex items-center gap-3"><span class="text-accent">✓</span> API access</li>
                        <li class="flex items-center gap-3"><span class="text-accent">✓</span> Dedicated support</li>
                    </ul>
                    <a href="#" class="btn-ghost border border-divider text-white text-[14px] w-full py-2 rounded-lg text-center font-medium mt-auto">Contact us</a>
                </div>
            </div>

            <p class="text-center text-muted text-[13px] mt-14">
                All plans include the Telegram bot, magic link auth, and automatic tracking. No credit card required for Free.
            </p>
        </div>
    </section>

    <section class="section-padding surface-band">
        <div class="container-custom max-w-[800px]">
            <div class="text-center mb-12">
                <span class="section-label">FAQ</span>
                <h2 class="text-[34px] md:text-[38px] font-semibold mb-4">Questions we actually get.</h2>
            </div>

            <div class="space-y-0">
                <div class="faq-item">
                    <button class="faq-question">
                        <span class="font-medium text-[15px]">How does the bot track my channel if I don't give it access to messages?</span>
                        <span class="text-muted faq-icon">+</span>
                    </button>
                    <div class="faq-answer text-sm">
                        Influence only needs read access to public channel statistics and post metadata like views and reactions. We don't read your message content or group chats.
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question">
                        <span class="font-medium text-[15px]">Is my channel data private?</span>
                        <span class="text-muted faq-icon">+</span>
                    </button>
                    <div class="faq-answer text-sm">
                        By default, your dashboard is private to your account. You can optionally generate a public read-only link if you'd like to share your stats with partners or advertisers.
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question">
                        <span class="font-medium text-[15px]">What's the difference between Engagement Rate and Potential Score?</span>
                        <span class="text-muted faq-icon">+</span>
                    </button>
                    <div class="faq-answer text-sm">
                        Engagement Rate measures how your current audience interacts with your posts. Potential Score predicts reach based on current trends and industry benchmarks.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-padding">
        <div class="container-custom">
            <div class="text-center mb-12">
                <span class="section-label">SOCIAL PROOF</span>
                <h2 class="text-[34px] md:text-[38px] font-semibold mb-4">Adoption without fake testimonials.</h2>
                <p class="text-muted max-w-2xl mx-auto">Real product counters show the size of the analytics network without invented names, avatars, or quotes.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                <div class="metric-card p-7">
                    <div class="font-mono text-4xl font-bold text-white mb-3">847</div>
                    <div class="text-muted">ta kanal ulangan</div>
                </div>
                <div class="metric-card p-7">
                    <div class="font-mono text-4xl font-bold text-white mb-3">2.3M</div>
                    <div class="text-muted">post tahlil qilindi</div>
                </div>
                <div class="metric-card p-7">
                    <div class="font-mono text-4xl font-bold text-white mb-3">+34%</div>
                    <div class="text-muted">o'rtacha engagement oshdi</div>
                </div>
            </div>
        </div>
    </section>

    <section class="footer-cta py-20 px-4">
        <div class="container-custom">
            <div class="mx-auto max-w-[760px] text-center">
                <span class="section-label">READY TO CONNECT</span>
                <h2 class="text-3xl md:text-[40px] font-bold text-white mb-5">Your Telegram channel deserves better analytics.</h2>
                <p class="text-base md:text-[18px] text-muted mb-9">
                    Add the bot in 10 seconds. See your first dashboard as soon as posts sync.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="/register" class="btn-primary w-full sm:w-auto px-8">Start free - no card needed</a>
                    <a href="#examples" class="w-full sm:w-auto border border-divider bg-surface text-white font-medium px-8 py-3 rounded-lg hover:border-accent transition-colors">View live demo</a>
                </div>
            </div>
        </div>
    </section>

    <footer class="section-padding pb-12 border-t border-divider">
        <div class="container-custom">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-12 mb-16 px-4 md:px-0">
                <div class="col-span-2 md:col-span-1">
                    <div class="flex items-center gap-2 mb-6">
                        <div class="w-4 h-4 bg-accent rounded-[2px]"></div>
                        <span class="text-white font-bold text-lg tracking-tight">Influence</span>
                    </div>
                    <p class="text-[14px] text-muted leading-relaxed">
                        Telegram analytics for Uzbekistan channels that care about growth.
                    </p>
                </div>
                <div>
                    <h4 class="text-[14px] font-bold mb-6 text-white uppercase tracking-widest">Product</h4>
                    <ul class="space-y-4 text-[14px] text-muted">
                        <li><a href="/dashboard" class="hover:text-white transition-colors">Dashboard</a></li>
                        <li><a href="#features" class="hover:text-white transition-colors">Features</a></li>
                        <li><a href="#pricing" class="hover:text-white transition-colors">Pricing</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Changelog</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-[14px] font-bold mb-6 text-white uppercase tracking-widest">Resources</h4>
                    <ul class="space-y-4 text-[14px] text-muted">
                        <li><a href="#" class="hover:text-white transition-colors">Docs</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">API Reference</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Bot Setup</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Status</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-[14px] font-bold mb-6 text-white uppercase tracking-widest">Company</h4>
                    <ul class="space-y-4 text-[14px] text-muted">
                        <li><a href="#" class="hover:text-white transition-colors">About</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Twitter/X</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Telegram</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Legal</a></li>
                    </ul>
                </div>
            </div>
            <div class="flex flex-col md:flex-row items-center justify-between pt-8 border-t border-divider text-[13px] text-muted">
                <p>© 2026 Influence. All rights reserved.</p>
                <p>Made for Telegram creators by <span class="text-white font-medium">Influence Team</span></p>
            </div>
        </div>
    </footer>

    <script>
        const nav = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (!nav) return;
            nav.classList.toggle('nav-scrolled', window.scrollY > 20);
        });

        const faqItems = document.querySelectorAll('.faq-item');
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question');
            const icon = item.querySelector('.faq-icon');
            question.addEventListener('click', () => {
                const isActive = item.classList.contains('active');

                faqItems.forEach(i => {
                    i.classList.remove('active');
                    i.querySelector('.faq-icon').textContent = '+';
                });

                if (!isActive) {
                    item.classList.add('active');
                    icon.textContent = 'x';
                }
            });
        });

        const tabs = document.querySelectorAll('.demo-tab');
        const panels = document.querySelectorAll('.demo-panel');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const target = tab.getAttribute('data-tab');
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                panels.forEach(panel => {
                    panel.classList.toggle('active', panel.id === target + '-panel');
                });
            });
        });

        const monthlyBtn = document.getElementById('monthly-toggle');
        const yearlyBtn = document.getElementById('yearly-toggle');
        const priceVals = document.querySelectorAll('.price-val');

        yearlyBtn?.addEventListener('click', () => {
            yearlyBtn.classList.add('bg-accent', 'text-white');
            yearlyBtn.classList.remove('text-muted');
            monthlyBtn.classList.remove('bg-accent', 'text-white');
            monthlyBtn.classList.add('text-muted');

            priceVals.forEach(value => {
                value.textContent = '$' + value.getAttribute('data-yearly');
            });
        });

        monthlyBtn?.addEventListener('click', () => {
            monthlyBtn.classList.add('bg-accent', 'text-white');
            monthlyBtn.classList.remove('text-muted');
            yearlyBtn.classList.remove('bg-accent', 'text-white');
            yearlyBtn.classList.add('text-muted');

            priceVals.forEach(value => {
                value.textContent = '$' + value.getAttribute('data-monthly');
            });
        });
    </script>
</body>
</html>
