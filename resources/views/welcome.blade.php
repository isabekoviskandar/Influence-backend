<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Influence — Telegram Analytics for Creators</title>
    <meta name="description" content="Know exactly how your channel is performing. Influence tracks views, reactions, and growth across your Telegram channels automatically.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=Geist+Mono&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Backdrop blur for sticky nav */
        .nav-scrolled {
            background-color: rgba(9, 9, 11, 0.8) !important;
            backdrop-filter: blur(8px);
        }

        /* Floating Hero Animation */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
            100% { transform: translateY(0px); }
        }
        .hero-float {
            animation: float 3s ease-in-out infinite;
        }

        /* Heatmap Grid */
        .heatmap-grid {
            display: grid;
            grid-template-columns: repeat(24, 1fr);
            gap: 2px;
        }
        .heatmap-cell {
            aspect-ratio: 1;
            background-color: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 2px;
        }
        .heatmap-cell.active {
            background-color: var(--accent);
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.4);
        }

        /* Dashed Line Connector */
        .step-connector {
            flex-grow: 1;
            height: 1px;
            border-top: 1px dashed var(--accent);
            margin: 0 20px;
            margin-top: 16px;
        }

        /* Social Proof Overlap */
        .avatar-group .avatar {
            margin-left: -8px;
            border: 2px solid var(--bg-page);
        }
        .avatar-group .avatar:first-child { margin-left: 0; }
    </style>
</head>
<body class="antialiased">

    <!-- SECTION 1 — NAVBAR -->
    <nav class="sticky top-0 z-50 w-full bg-page/80 backdrop-blur-md border-b border-divider">
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
            <div class="flex items-center gap-4">
                <a href="/login" class="text-sm font-medium hover:text-accent transition-colors hidden sm:block">Sign in</a>
                <a href="/register" class="btn-primary">Start free</a>
            </div>
        </div>
    </nav>

    <!-- SECTION 2 — HERO -->
    <section class="section-padding pt-[160px] relative overflow-hidden">
        <div class="container-custom text-center">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-accent/30 bg-accent/10 text-accent text-[12px] font-medium mb-8">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-accent opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-accent"></span>
                </span>
                Built for Telegram Creators
            </div>

            <!-- Heading -->
            <h1 class="text-4xl md:text-6xl font-bold tracking-tight mb-6">
                Telegram analytics <br class="hidden md:block"> for elite creators.
            </h1>
            <p class="text-base md:text-xl text-muted max-w-2xl mx-auto mb-10 px-4">
                Influence tracks views, reactions, and growth across your Telegram channels — automatically. Add the bot, open the dashboard.
            </p>

            <!-- CTAs -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mb-10">
                <a href="#" class="btn-primary w-full sm:w-auto">Start for free</a>
                <a href="#" class="flex items-center gap-2 text-muted hover:text-white transition-colors px-5">
                    See how it works <span class="text-lg">→</span>
                </a>
            </div>

            <!-- Social Proof -->
            <div class="flex items-center justify-center gap-3 mb-20">
                <div class="flex avatar-group">
                    <div class="w-8 h-8 rounded-full bg-[#3b82f6] flex items-center justify-center text-[10px] font-bold avatar">JD</div>
                    <div class="w-8 h-8 rounded-full bg-[#ef4444] flex items-center justify-center text-[10px] font-bold avatar">AM</div>
                    <div class="w-8 h-8 rounded-full bg-[#10b981] flex items-center justify-center text-[10px] font-bold avatar">SK</div>
                </div>
                <span class="text-[14px] text-muted">Joined by 200+ Telegram creators</span>
            </div>

            <!-- Hero Visual -->
            <div class="relative max-w-4xl mx-auto px-4 mt-20">
                <div class="absolute inset-0 bg-accent/20 blur-[120px] rounded-full"></div>
                <div class="relative bg-surface border border-divider rounded-xl overflow-hidden shadow-2xl floating">
                    <img src="/images/hero-dashboard.png" alt="Influence Dashboard" class="w-full h-auto opacity-90 scale-105 md:scale-100">
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 3 — LOGO BAR -->
    <section class="border-y border-divider py-12">
        <div class="container-custom">
            <p class="text-[11px] font-bold text-muted tracking-[0.1em] text-center mb-8">TRUSTED BY CREATORS IN</p>
            <div class="flex flex-wrap justify-center gap-4">
                <span class="px-4 py-2 border border-divider rounded-full text-muted text-sm hover:border-accent hover:text-white transition-all cursor-default">Tech & Dev</span>
                <span class="px-4 py-2 border border-divider rounded-full text-muted text-sm hover:border-accent hover:text-white transition-all cursor-default">Finance</span>
                <span class="px-4 py-2 border border-divider rounded-full text-muted text-sm hover:border-accent hover:text-white transition-all cursor-default">News</span>
                <span class="px-4 py-2 border border-divider rounded-full text-muted text-sm hover:border-accent hover:text-white transition-all cursor-default">Lifestyle</span>
                <span class="px-4 py-2 border border-divider rounded-full text-muted text-sm hover:border-accent hover:text-white transition-all cursor-default">Gaming</span>
                <span class="px-4 py-2 border border-divider rounded-full text-muted text-sm hover:border-accent hover:text-white transition-all cursor-default">Education</span>
                <span class="px-4 py-2 border border-divider rounded-full text-muted text-sm hover:border-accent hover:text-white transition-all cursor-default">Marketing</span>
            </div>
        </div>
    </section>

    <!-- SECTION 4 — PROBLEM → SOLUTION -->
    <section class="section-padding">
        <div class="container-custom grid lg:grid-cols-2 gap-20 items-center">
            <div>
                <span class="text-accent text-[12px] font-bold tracking-[0.1em] mb-4 block">WHY INFLUENCE</span>
                <h2 class="text-[36px] font-semibold mb-6">Stop guessing. Start knowing.</h2>
                <p class="text-muted mb-10">
                    Most Telegram creators check views manually, one post at a time. There's no way to see trends, compare performance, or understand what actually grows a channel. Influence fixes that.
                </p>

                <!-- Pain points -->
                <div class="space-y-6">
                    <div class="flex items-center gap-4 pb-6 border-b border-divider">
                        <div class="w-5 h-5 flex items-center justify-center text-red-500">✕</div>
                        <span class="text-muted">Manual view counting is slow and inaccurate</span>
                    </div>
                    <div class="flex items-center gap-4 pb-6 border-b border-divider">
                        <div class="w-5 h-5 flex items-center justify-center text-red-500">✕</div>
                        <span class="text-muted">No historical data beyond what Telegram shows</span>
                    </div>
                    <div class="flex items-center gap-4 pb-6 border-b border-divider">
                        <div class="w-5 h-5 flex items-center justify-center text-red-500">✕</div>
                        <span class="text-muted">Hard to identify peak engagement hours</span>
                    </div>
                </div>

                <!-- Solution highlights -->
                <div class="mt-10 space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="w-5 h-5 flex items-center justify-center text-accent">✓</div>
                        <span class="text-white font-medium">Automatic real-time data ingestion</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-5 h-5 flex items-center justify-center text-accent">✓</div>
                        <span class="text-white font-medium">Beautiful visualizations of trends</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-5 h-5 flex items-center justify-center text-accent">✓</div>
                        <span class="text-white font-medium">Predictive scoring for reach potential</span>
                    </div>
                </div>
            </div>

            <!-- Heatmap Card Mockup -->
            <div class="card p-6 md:p-8 overflow-hidden">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-sm font-semibold">Best Time to Post</h3>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span>
                        <span class="text-[10px] text-muted">LIVE SYNC</span>
                    </div>
                </div>
                <div class="overflow-x-auto pb-2 -mx-2 px-2 scrollbar-none">
                    <div class="heatmap-grid mb-4 min-w-[500px] md:min-w-0">
                        @for ($i = 0; $i < 168; $i++)
                            <div class="heatmap-cell {{ $i % 7 == 0 ? 'active' : '' }}"></div>
                        @endfor
                    </div>
                </div>
                <div class="flex justify-between text-[10px] text-muted font-mono uppercase">
                    <span>Mon</span>
                    <span>Sun</span>
                </div>
                <div class="mt-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                    <span class="text-[12px] text-muted">Audience peak: <span class="text-white font-medium">19:00 — 21:00</span></span>
                    <span class="text-[12px] text-accent font-medium bg-accent/10 px-2 py-0.5 rounded">94% Active</span>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 5 — FEATURES -->
    <section id="features" class="section-padding bg-surface/30">
        <div class="container-custom">
            <div class="text-center mb-16">
                <span class="text-accent text-[12px] font-bold tracking-[0.1em] mb-4 block uppercase">Features</span>
                <h2 class="text-3xl md:text-[40px] font-semibold mb-4">Everything your channel needs.</h2>
                <p class="text-muted text-sm md:text-[16px]">One bot. One dashboard. Full visibility.</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Feature 1 -->
                <div class="card group">
                    <div class="w-8 h-8 rounded-lg bg-accent/10 flex items-center justify-center text-accent mb-6 group-hover:bg-accent/20 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="text-[15px] font-semibold mb-3">Live Post Tracking</h3>
                    <p class="text-[13px] text-muted">Views and reactions synced automatically as they come in.</p>
                </div>
                <!-- Feature 2 -->
                <div class="card group">
                    <div class="w-8 h-8 rounded-lg bg-accent/10 flex items-center justify-center text-accent mb-6 group-hover:bg-accent/20 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <h3 class="text-[15px] font-semibold mb-3">Engagement Rate</h3>
                    <p class="text-[13px] text-muted">Calculated per post and averaged across your channel.</p>
                </div>
                <!-- Feature 3 -->
                <div class="card group">
                    <div class="w-8 h-8 rounded-lg bg-accent/10 flex items-center justify-center text-accent mb-6 group-hover:bg-accent/20 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <h3 class="text-[15px] font-semibold mb-3">Subscriber Trends</h3>
                    <p class="text-[13px] text-muted">See your growth over 7, 30 days, or all time.</p>
                </div>
                <!-- Feature 4 -->
                <div class="card group">
                    <div class="w-8 h-8 rounded-lg bg-accent/10 flex items-center justify-center text-accent mb-6 group-hover:bg-accent/20 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                    <h3 class="text-[15px] font-semibold mb-3">Potential Score</h3>
                    <p class="text-[13px] text-muted">A composite score showing your channel's reach potential.</p>
                </div>
                <!-- Feature 5 -->
                <div class="card group">
                    <div class="w-8 h-8 rounded-lg bg-accent/10 flex items-center justify-center text-accent mb-6 group-hover:bg-accent/20 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-[15px] font-semibold mb-3">Best Time to Post</h3>
                    <p class="text-[13px] text-muted">Heatmap of when your audience is most active.</p>
                </div>
                <!-- Feature 6 -->
                <div class="card group">
                    <div class="w-8 h-8 rounded-lg bg-accent/10 flex items-center justify-center text-accent mb-6 group-hover:bg-accent/20 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <h3 class="text-[15px] font-semibold mb-3">Multi-Channel View</h3>
                    <p class="text-[13px] text-muted">Manage and compare all your channels in one place.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 6 — HOW IT WORKS -->
    <section class="section-padding bg-surface">
        <div class="container-custom">
            <div class="text-center mb-20">
                <span class="text-accent text-[12px] font-bold tracking-[0.1em] mb-4 block uppercase">HOW IT WORKS</span>
                <h2 class="text-3xl md:text-[38px] font-semibold">Set up in 60 seconds.</h2>
            </div>

            <div class="grid md:grid-cols-3 gap-10 mb-20">
                <div class="text-center md:text-left">
                    <div class="flex items-center justify-center md:justify-start gap-4 mb-6">
                        <div class="w-8 h-8 rounded-full bg-accent text-white flex items-center justify-center font-bold">1</div>
                        <div class="step-connector hidden md:block"></div>
                    </div>
                    <h3 class="text-lg font-semibold mb-4 text-white">Add the bot</h3>
                    <p class="text-muted text-[15px]">Search @InfluenceBot on Telegram and add it as an admin to your channel.</p>
                </div>
                <div class="text-center md:text-left">
                    <div class="flex items-center justify-center md:justify-start gap-4 mb-6">
                        <div class="w-8 h-8 rounded-full bg-accent text-white flex items-center justify-center font-bold">2</div>
                        <div class="step-connector hidden md:block"></div>
                    </div>
                    <h3 class="text-lg font-semibold mb-4 text-white">Bot starts tracking</h3>
                    <p class="text-muted text-[15px]">The bot immediately begins capturing views, reactions, and subscriber changes.</p>
                </div>
                <div class="text-center md:text-left">
                    <div class="flex items-center justify-center md:justify-start gap-4 mb-6">
                        <div class="w-8 h-8 rounded-full bg-accent text-white flex items-center justify-center font-bold">3</div>
                    </div>
                    <h3 class="text-lg font-semibold mb-4 text-white">Open your dashboard</h3>
                    <p class="text-muted text-[15px]">Log in via magic link. Your analytics are ready. No setup, no config.</p>
                </div>
            </div>

            <div class="terminal max-w-[600px] mx-auto shadow-2xl">
                <div class="flex gap-2 mb-6">
                    <div class="w-2.5 h-2.5 rounded-full bg-red-500/50"></div>
                    <div class="w-2.5 h-2.5 rounded-full bg-yellow-500/50"></div>
                    <div class="w-2.5 h-2.5 rounded-full bg-green-500/50"></div>
                </div>
                <div class="space-y-1 text-[14px]">
                    <div class="terminal-line"><span class="terminal-prompt">→</span> <span>Bot added to @yourchannel</span></div>
                    <div class="terminal-line"><span class="terminal-prompt">→</span> <span>Syncing historical posts... <span class="text-green-500">done</span> (45 posts)</span></div>
                    <div class="terminal-line"><span class="terminal-prompt">→</span> <span>Dashboard ready: <a href="#" class="text-accent underline">influence.app/dashboard</a></span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 7 — LIVE EXAMPLE / DEMO -->
    <section id="examples" class="section-padding">
        <div class="container-custom">
            <div class="text-center mb-16 px-4">
                <span class="text-accent text-[12px] font-bold tracking-[0.1em] mb-4 block uppercase font-mono">SEE IT IN ACTION</span>
                <h2 class="text-3xl md:text-[38px] font-semibold mb-4">Real data. Real channels.</h2>
            </div>

            <div class="max-w-[900px] mx-auto">
                <div class="demo-tabs justify-center">
                    <div class="demo-tab active" data-tab="overview">Channel Overview</div>
                    <div class="demo-tab" data-tab="analytics">Post Analytics</div>
                    <div class="demo-tab" data-tab="heatmap">Engagement Heatmap</div>
                </div>

                <div class="bg-surface border border-divider rounded-xl min-h-[400px] p-1 shadow-2xl overflow-hidden mb-10 mx-4 md:mx-0">
                    <div id="overview-panel" class="demo-panel active p-8">
                        <div class="grid sm:grid-cols-3 gap-6">
                            <div class="bg-bg-page border border-divider rounded-lg p-6">
                                <span class="text-muted text-[11px] font-bold block mb-4">TECH NEWS HUB</span>
                                <div class="text-2xl font-bold mb-2">55.2K</div>
                                <div class="text-[11px] text-green-500 font-medium">+0.8% Sub / 24h</div>
                            </div>
                            <div class="bg-bg-page border border-divider rounded-lg p-6">
                                <span class="text-muted text-[11px] font-bold block mb-4">GLOBAL POLITICS</span>
                                <div class="text-2xl font-bold mb-2">103.1K</div>
                                <div class="text-[11px] text-green-500 font-medium">+1.5% Sub / 24h</div>
                            </div>
                            <div class="bg-bg-page border border-divider rounded-lg p-6">
                                <span class="text-muted text-[11px] font-bold block mb-4">MARKET UPDATES</span>
                                <div class="text-2xl font-bold mb-2">44.7K</div>
                                <div class="text-[11px] text-green-500 font-medium">+0.6% Sub / 24h</div>
                            </div>
                        </div>
                    </div>
                    <div id="analytics-panel" class="demo-panel p-8">
                        <div class="space-y-4">
                            @for ($i = 0; $i < 4; $i++)
                                <div class="bg-bg-page border border-divider rounded-lg p-4 flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded bg-accent/20"></div>
                                        <div>
                                            <div class="text-sm font-medium">New Feature Rollout</div>
                                            <div class="text-[11px] text-muted">Posted 2h ago</div>
                                        </div>
                                    </div>
                                    <div class="flex gap-8">
                                        <div class="text-right">
                                            <div class="text-sm font-bold">12.4K</div>
                                            <div class="text-[10px] text-muted">VIEWS</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-bold">842</div>
                                            <div class="text-[10px] text-muted">REACTIONS</div>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                    <div id="heatmap-panel" class="demo-panel p-8">
                        <!-- Reusing heatmap logic simpler -->
                        <div class="heatmap-grid scale-75 origin-top">
                            @for ($i = 0; $i < 168; $i++)
                                <div class="heatmap-cell {{ ($i > 60 && $i < 90) || ($i > 120 && $i < 140) ? 'active' : '' }}"></div>
                            @endfor
                        </div>
                    </div>
                </div>

                <div class="text-center mt-10">
                    <p class="text-[13px] text-muted mb-6">
                        This is real data from the Influence dashboard. <a href="#" class="text-accent hover:underline">Start tracking your own channel →</a>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 8 — PRICING -->
    <section id="pricing" class="section-padding bg-surface/30">
        <div class="container-custom">
            <div class="text-center mb-16 px-4">
                <span class="text-accent text-[12px] font-bold tracking-[0.1em] mb-4 block uppercase">PRICING</span>
                <h2 class="text-3xl md:text-[38px] font-semibold mb-4">Simple, honest pricing.</h2>
                <p class="text-muted text-sm md:text-[16px] mb-10">Start free. Upgrade when you need more.</p>

                <!-- Pricing Toggle -->
                <div class="inline-flex items-center p-1 bg-bg-surface border border-divider rounded-lg mb-16">
                    <button id="monthly-toggle" class="px-4 py-1.5 rounded-md text-sm font-medium bg-accent text-white transition-all">Monthly</button>
                    <button id="yearly-toggle" class="px-4 py-1.5 rounded-md text-sm font-medium text-muted hover:text-white transition-all">Yearly <span class="ml-1 text-[10px] text-accent font-bold">-20%</span></butto            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 px-4 md:px-0">
                <!-- Free -->
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
                    <a href="#" class="btn-ghost border border-divider text-white text-[14px] w-full py-2 rounded-lg text-center font-medium mt-auto">Get started free</a>
                </div>

                <!-- Pro -->
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
                    <a href="#" class="btn-primary w-full text-center py-2 rounded-lg font-medium mt-auto">Start Pro free</a>
                </div>

                <!-- Agency -->
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
ter font-medium mt-auto">Contact us</a>
                </div>
            </div>

            <p class="text-center text-muted text-[13px] mt-16">
                All plans include the Telegram bot, magic link auth, and automatic tracking. No credit card required for Free.
            </p>
        </div>
    </section>

    <!-- SECTION 9 — FAQ -->
    <section class="section-padding">
        <div class="container-custom max-w-[800px]">
            <div class="text-center mb-16">
                <span class="text-accent text-[12px] font-bold tracking-[0.1em] mb-4 block uppercase">FAQ</span>
                <h2 class="text-[38px] font-semibold mb-4">Questions we actually get.</h2>
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
                <!-- Adding a few more for the aesthetic -->
                <div class="faq-item">
                    <button class="faq-question">
                        <span class="font-medium text-[15px]">What's the difference between Engagement Rate and Potential Score?</span>
                        <span class="text-muted faq-icon">+</span>
                    </button>
                    <div class="faq-answer text-sm">
                        Engagement Rate measures how your current audience interacts with your posts. Potential Score uses AI to predict your viral reach based on current trends and industry benchmarks.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 10 — TESTIMONIALS -->
    <section class="section-padding bg-surface/30">
        <div class="container-custom">
            <div class="text-center mb-16">
                <span class="text-accent text-[12px] font-bold tracking-[0.1em] mb-4 block uppercase">FROM CREATORS</span>
                <h2 class="text-[38px] font-semibold mb-4">They stopped guessing too.</h2>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="card">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-10 h-10 rounded-full bg-[#3b82f6] flex items-center justify-center font-bold text-sm">TB</div>
                        <div>
                            <div class="text-[14px] font-semibold">Tech Buddy</div>
                            <div class="text-[12px] text-muted">@techbuddy_news</div>
                        </div>
                    </div>
                    <p class="text-[15px] italic text-white/90 mb-6">"Finally, a tool that actually shows me when to post. My engagement went up 30% in the first week."</p>
                    <div class="text-amber-500 text-sm">★★★★★</div>
                </div>
                <div class="card">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-10 h-10 rounded-full bg-[#ef4444] flex items-center justify-center font-bold text-sm">FC</div>
                        <div>
                            <div class="text-[14px] font-semibold">Finance Coach</div>
                            <div class="text-[12px] text-muted">@financealpha</div>
                        </div>
                    </div>
                    <p class="text-[15px] italic text-white/90 mb-6">"The potential score is a game changer for sponsorship deals. Brands love seeing the hard data."</p>
                    <div class="text-amber-500 text-sm">★★★★★</div>
                </div>
                <div class="card">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-10 h-10 rounded-full bg-[#10b981] flex items-center justify-center font-bold text-sm">LG</div>
                        <div>
                            <div class="text-[14px] font-semibold">Life & Gaming</div>
                            <div class="text-[12px] text-muted">@lifegame_tg</div>
                        </div>
                    </div>
                    <p class="text-[15px] italic text-white/90 mb-6">"I manage 5 channels. Having them all in one dashboard saves me hours every single day."</p>
                    <div class="text-amber-500 text-sm">★★★★★</div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 11 — FINAL CTA BAND -->
    <section class="bg-accent py-24 text-center px-4">
        <div class="container-custom">
            <h2 class="text-3xl md:text-[40px] font-bold text-white mb-6">Your channel deserves better analytics.</h2>
            <p class="text-base md:text-[18px] text-white/70 mb-10 max-w-[600px] mx-auto">
                Add the bot in 10 seconds. Dashboard ready instantly.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="#" class="bg-white text-accent font-bold px-8 py-3 rounded-lg hover:bg-white/90 transition-colors">Start free — no card needed</a>
                <a href="#" class="border border-white text-white font-bold px-8 py-3 rounded-lg hover:bg-white/10 transition-colors">View live demo →</a>
            </div>
            <p class="text-[12px] text-white/60 mt-8">Used by 200+ creators · Free plan available · Cancel anytime</p>
        </div>
    </section>

    <!-- SECTION 12 — FOOTER -->
    <footer class="section-padding pb-12 border-t border-divider">
        <div class="container-custom">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-12 mb-20 px-4 md:px-0">
                <div class="col-span-2 md:col-span-1">
                    <div class="flex items-center gap-2 mb-6">
                        <div class="w-4 h-4 bg-accent rounded-[2px]"></div>
                        <span class="text-white font-bold text-lg tracking-tight">Influence</span>
                    </div>
                    <p class="text-[14px] text-muted leading-relaxed">
                        Telegram analytics for creators who care about growth.
                    </p>
                </div>
                <div>
                    <h4 class="text-[14px] font-bold mb-6 text-white uppercase tracking-widest">Product</h4>
                    <ul class="space-y-4 text-[14px] text-muted">
                        <li><a href="#" class="hover:text-white transition-colors">Dashboard</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Features</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Pricing</a></li>
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
                <p>© 2025 Influence. All rights reserved.</p>
                <p>Made for Telegram creators by <span class="text-white font-medium">Influence Team</span></p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 20) {
                nav.classList.add('nav-scrolled');
            } else {
                nav.classList.remove('nav-scrolled');
            }
        });

        // FAQ accordion
        const faqItems = document.querySelectorAll('.faq-item');
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question');
            const icon = item.querySelector('.faq-icon');
            question.addEventListener('click', () => {
                const isActive = item.classList.contains('active');
                
                // Close all others
                faqItems.forEach(i => {
                    i.classList.remove('active');
                    i.querySelector('.faq-icon').textContent = '+';
                });

                if (!isActive) {
                    item.classList.add('active');
                    icon.textContent = '×';
                }
            });
        });

        // Demo Tabs
        const tabs = document.querySelectorAll('.demo-tab');
        const panels = document.querySelectorAll('.demo-panel');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const target = tab.getAttribute('data-tab');
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                
                panels.forEach(p => {
                    p.classList.remove('active');
                    if (p.id === target + '-panel') {
                        p.classList.add('active');
                    }
                });
            });
        });

        // Pricing Toggle
        const monthlyBtn = document.getElementById('monthly-toggle');
        const yearlyBtn = document.getElementById('yearly-toggle');
        const priceVals = document.querySelectorAll('.price-val');
        
        yearlyBtn.addEventListener('click', () => {
            yearlyBtn.classList.add('bg-accent', 'text-white');
            yearlyBtn.classList.remove('text-muted');
            monthlyBtn.classList.remove('bg-accent', 'text-white');
            monthlyBtn.classList.add('text-muted');
            
            priceVals.forEach(v => {
                v.textContent = '$' + v.getAttribute('data-yearly');
            });
        });

        monthlyBtn.addEventListener('click', () => {
            monthlyBtn.classList.add('bg-accent', 'text-white');
            monthlyBtn.classList.remove('text-muted');
            yearlyBtn.classList.remove('bg-accent', 'text-white');
            yearlyBtn.classList.add('text-muted');
            
            priceVals.forEach(v => {
                v.textContent = '$' + v.getAttribute('data-monthly');
            });
        });
    </script>
</body>
</html>
