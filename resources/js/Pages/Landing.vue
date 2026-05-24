<template>
  <div class="landing-root min-h-screen bg-[#0d1117] text-[#e6edf3]">
    <header class="sticky top-0 z-50 border-b border-[#30363d] bg-[#0d1117]/95 backdrop-blur-sm">
      <div class="container-custom mx-auto flex h-16 items-center justify-between gap-4 px-4">
        <div class="flex items-center gap-5">
          <div class="flex items-center gap-2 font-[700] uppercase tracking-[0.24em] text-[#e6edf3] font-jetbrains-mono">
            <span>INFLUENCE</span>
            <span class="inline-flex h-2.5 w-2.5 rounded-full bg-[#3fb950] shadow-[0_0_0_4px_rgba(63,185,80,0.12)]"></span>
            <!-- <span class="text-[#8b949e] text-[0.72rem] uppercase tracking-[0.2em]">live</span> -->
          </div>
          <nav class="hidden md:flex items-center gap-8 text-sm text-[#8b949e]">
            <a href="#features" class="nav-link">Features</a>
            <a href="#pricing" class="nav-link">Pricing</a>
            <a href="#changelog" class="nav-link">Changelog</a>
            <a href="#api" class="nav-link">API</a>
          </nav>
        </div>

        <div class="flex items-center gap-3">
          <a href="/login" class="rounded-full border border-[#30363d] px-4 py-2 text-sm text-[#8b949e] transition hover:border-[#58a6ff] hover:text-[#e6edf3]">Sign in</a>
          <a href="/register" class="rounded-full bg-[#58a6ff] px-5 py-2.5 text-sm font-semibold text-[#0d1117] transition hover:bg-[#3f8cee]">Start free</a>
        </div>
      </div>
    </header>

    <main class="overflow-hidden">
      <section class="container-custom mx-auto grid gap-12 px-4 py-14 lg:grid-cols-[55%_45%] lg:items-center">
        <div class="space-y-8">
          <div class="inline-flex items-center gap-2 rounded-full border border-[#30363d] bg-[#161b22] px-4 py-2 text-[0.82rem] font-jetbrains-mono text-[#79c0ff]">
            <span>v2.1 — now tracking 1,200+ channels</span>
          </div>

          <div class="space-y-6">
            <h1 class="max-w-3xl text-5xl font-[800] leading-[0.92] tracking-[-0.04em] text-[#e6edf3] sm:text-6xl lg:text-[4.5rem]">
              Know exactly when<br />your channel peaks.
            </h1>
            <p class="max-w-2xl text-lg leading-8 text-[#8b949e] sm:text-xl">
              Influence tracks views, forwards, and engagement velocity across your Telegram channels — in real time.
            </p>
          </div>

          <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <a href="/register" class="inline-flex items-center justify-center rounded-full bg-[#58a6ff] px-7 py-3 text-sm font-semibold text-[#0d1117] transition hover:bg-[#3f8cee]">Connect your channel →</a>
            <a href="#live" class="inline-flex items-center justify-center rounded-full border border-[#30363d] px-6 py-3 text-sm font-jetbrains-mono text-[#79c0ff] transition hover:border-[#58a6ff] hover:text-[#e6edf3]">View live demo</a>
          </div>

          <div class="grid gap-3 sm:grid-cols-3 text-[0.83rem] font-jetbrains-mono text-[#79c0ff]">
            <div class="rounded-2xl border border-[#30363d] bg-[#161b22] px-4 py-3">● 1,247 channels</div>
            <div class="rounded-2xl border border-[#30363d] bg-[#161b22] px-4 py-3">▲ 98.2% uptime</div>
            <div class="rounded-2xl border border-[#30363d] bg-[#161b22] px-4 py-3">⚡ 15-min sync</div>
          </div>
        </div>

        <div id="live" class="relative">
          <div class="rounded-[30px] border border-[#30363d] bg-[#161b22] p-6 shadow-[0_20px_80px_rgba(0,0,0,0.25)]">
            <div class="mb-5 flex items-center justify-between rounded-2xl bg-[#0f161f] px-4 py-3 text-sm uppercase tracking-[0.26em] text-[#8b949e]">
              <span class="font-[700] text-[#e6edf3]">INFLUENCE TERMINAL</span>
              <!-- <span class="inline-flex items-center gap-2 text-[#3fb950]">
                <span class="h-2 w-2 rounded-full bg-[#3fb950]"></span>
                LIVE
              </span> -->
            </div>

            <div class="space-y-4 text-[0.95rem] leading-7 text-[#e6edf3] font-jetbrains-mono">
              <div class="rounded-xl bg-[#171f29] px-4 py-3">
                <div class="text-[#79c0ff]">{{ terminalDisplay[0] || '@uzbekistan_news' }}</div>
              </div>
              <div class="rounded-xl bg-[#171f29] px-4 py-3">
                <div class="font-semibold">{{ terminalDisplay[1] || 'Subscribers  ████████ 128K' }}</div>
              </div>
              <div class="rounded-xl bg-[#171f29] px-4 py-3">
                <div class="text-[#3fb950]">{{ terminalDisplay[2] || 'Last post    ▲ 2.4x spike' }}</div>
              </div>
              <div class="rounded-xl bg-[#171f29] px-4 py-3">
                <div>{{ terminalDisplay[3] || 'Best window  19:00 – 21:00' }}</div>
              </div>
            </div>

            <div class="mt-6 h-px bg-[#30363d]"></div>

            <div class="mt-5 space-y-3 text-[0.95rem] leading-7 text-[#e6edf3] font-jetbrains-mono">
              <div class="rounded-xl bg-[#171f29] px-4 py-3">
                <div>{{ terminalDisplay[4] || '@techuz          ▲ trending' }}</div>
              </div>
              <div class="rounded-xl bg-[#171f29] px-4 py-3">
                <div class="font-semibold">{{ terminalDisplay[5] || 'Fwd ratio    ████░░░ 6.2%' }}</div>
              </div>
              <div class="rounded-xl bg-[#171f29] px-4 py-3">
                <div class="font-semibold">{{ terminalDisplay[6] || 'Viral score  94 / 100' }}</div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="border-y border-[#30363d] bg-[#161b22] py-4">
        <div class="container-custom mx-auto overflow-hidden px-4">
          <div class="ticker flex min-w-full animate-ticker gap-10 whitespace-nowrap text-[0.94rem] font-jetbrains-mono uppercase tracking-[0.08em] text-[#79c0ff]">
            <span>@texnouz ▲ +2,340 subs today</span>
            <span class="text-[#8b949e]">|</span>
            <span>@uzfinance 🔥 viral post detected</span>
            <span class="text-[#8b949e]">|</span>
            <span>@marketuz ● 19:00 peak window</span>
            <span class="text-[#8b949e]">|</span>
            <span>@newsift ▲ +1,120 engagement</span>
            <span class="text-[#8b949e]">|</span>
            <span>@crypto_uz ● 22:00 surge</span>
            <span class="text-[#8b949e]">|</span>
            <span>@cultureuz 🔥 trending story</span>
          </div>
        </div>
      </section>

      <section class="container-custom mx-auto px-4 py-16">
        <div class="space-y-5 text-center">
          <h2 class="text-3xl font-[700] text-[#e6edf3] sm:text-4xl">Flying blind costs you reach.</h2>
          <p class="mx-auto max-w-2xl text-[#8b949e]">Every missed peak, wrong post type, and artificial growth signal steals real audience attention.</p>
        </div>

        <div class="mt-12 grid gap-4 lg:grid-cols-3">
          <div class="rounded-3xl border-l-4 border-[#d29922] bg-[#161b22] p-6 text-[#e6edf3]">
            <p class="text-sm uppercase tracking-[0.24em] text-[#79c0ff]">01 / timing</p>
            <h3 class="mt-3 text-xl font-semibold">You post at random times</h3>
            <p class="mt-3 text-[#8b949e]">→ avg 12% of potential reach.</p>
          </div>
          <div class="rounded-3xl border-l-4 border-[#d29922] bg-[#161b22] p-6 text-[#e6edf3]">
            <p class="text-sm uppercase tracking-[0.24em] text-[#79c0ff]">02 / format</p>
            <h3 class="mt-3 text-xl font-semibold">You can't see which post type works</h3>
            <p class="mt-3 text-[#8b949e]">→ guessing content instead of optimizing it.</p>
          </div>
          <div class="rounded-3xl border-l-4 border-[#d29922] bg-[#161b22] p-6 text-[#e6edf3]">
            <p class="text-sm uppercase tracking-[0.24em] text-[#79c0ff]">03 / growth</p>
            <h3 class="mt-3 text-xl font-semibold">You don't know if growth is real</h3>
            <p class="mt-3 text-[#8b949e]">→ fake reach feels good but never delivers value.</p>
          </div>
        </div>

        <div class="mt-8 flex flex-col gap-4 rounded-3xl border border-[#30363d] bg-[#0f161f] p-6 text-center text-[#8b949e] sm:flex-row sm:items-center sm:justify-center">
          <span class="text-[#79c0ff]">Influence fixes all three.</span>
          <div class="flex flex-wrap items-center justify-center gap-4 text-sm text-[#3fb950]">
            <span>✓ Timing</span>
            <span>✓ Content signals</span>
            <span>✓ True virality</span>
          </div>
        </div>
      </section>

      <section id="features" class="container-custom mx-auto space-y-24 px-4 py-20">
        <div class="grid gap-12 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
          <div class="space-y-4">
            <span class="text-sm uppercase tracking-[0.28em] text-[#79c0ff]">Feature</span>
            <h2 class="text-3xl font-[700] text-[#e6edf3] sm:text-4xl">View velocity, not just view count.</h2>
            <p class="max-w-xl text-[#8b949e]">See how fast views accumulate in the first 2 hours. That's when viral potential is decided.</p>
          </div>
          <div class="rounded-[28px] border border-[#30363d] bg-[#161b22] p-6 shadow-[0_18px_50px_rgba(0,0,0,0.18)]">
            <div class="flex items-center justify-between rounded-2xl bg-[#0f161f] px-4 py-3 text-sm font-jetbrains-mono uppercase tracking-[0.26em] text-[#8b949e]">
              <span>Velocity curve</span>
              <span>Realtime</span>
            </div>
            <div class="mt-6 space-y-5">
              <div class="h-52 overflow-hidden rounded-3xl bg-[#0d1117] p-5">
                <div class="relative h-full w-full">
                  <div class="absolute inset-x-0 bottom-0 grid grid-cols-8 gap-3">
                    <span class="h-[18%] rounded-full bg-[#58a6ff]/20"></span>
                    <span class="h-[24%] rounded-full bg-[#58a6ff]/30"></span>
                    <span class="h-[35%] rounded-full bg-[#58a6ff]/30"></span>
                    <span class="h-[62%] rounded-full bg-[#58a6ff]/50"></span>
                    <span class="h-[48%] rounded-full bg-[#58a6ff]/40"></span>
                    <span class="h-[72%] rounded-full bg-[#58a6ff]/60"></span>
                    <span class="h-[84%] rounded-full bg-[#58a6ff]"></span>
                    <span class="h-[65%] rounded-full bg-[#58a6ff]/70"></span>
                  </div>
                </div>
              </div>
              <div class="flex items-center justify-between text-sm text-[#8b949e] font-jetbrains-mono">
                <span>0 min</span>
                <span>120 min</span>
              </div>
            </div>
          </div>
        </div>

        <div class="grid gap-12 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
          <div class="rounded-[28px] border border-[#30363d] bg-[#161b22] p-6 shadow-[0_18px_50px_rgba(0,0,0,0.18)]">
            <div class="flex items-center justify-between rounded-2xl bg-[#0f161f] px-4 py-3 text-sm font-jetbrains-mono uppercase tracking-[0.26em] text-[#8b949e]">
              <span>Heatmap</span>
              <span>7×24</span>
            </div>
            <div class="mt-6 grid gap-2">
              <div class="grid grid-cols-[repeat(24,minmax(0,1fr))] gap-1 text-[0.65rem] text-[#8b949e] uppercase">
                <template v-for="hour in 24" :key="hour">
                  <div class="text-center">{{ hour }}</div>
                </template>
              </div>
              <div class="space-y-1 rounded-3xl bg-[#0d1117] p-4">
                <template v-for="row in heatmapRows" :key="row.label">
                  <div class="grid grid-cols-[auto_1fr] items-center gap-3 text-sm">
                    <span class="w-14 text-[#8b949e]">{{ row.label }}</span>
                    <div class="grid grid-cols-[repeat(24,minmax(0,1fr))] gap-1">
                      <template v-for="(cell, index) in row.cells" :key="index">
                        <div :class="['h-3 rounded-full', cell === 'low' ? 'bg-[#21262d]' : cell === 'mid' ? 'bg-[#58a6ff]/40' : 'bg-[#58a6ff]']"></div>
                      </template>
                    </div>
                  </div>
                </template>
              </div>
            </div>
          </div>
          <div class="space-y-4">
            <span class="text-sm uppercase tracking-[0.28em] text-[#79c0ff]">Feature</span>
            <h2 class="text-3xl font-[700] text-[#e6edf3] sm:text-4xl">Your audience has a schedule. Learn it.</h2>
            <p class="max-w-xl text-[#8b949e]">A 7×24 heatmap built from your channel's actual data, not generic advice.</p>
          </div>
        </div>

        <div class="grid gap-12 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
          <div class="space-y-4">
            <span class="text-sm uppercase tracking-[0.28em] text-[#79c0ff]">Feature</span>
            <h2 class="text-3xl font-[700] text-[#e6edf3] sm:text-4xl">Forward ratio reveals true virality.</h2>
            <p class="max-w-xl text-[#8b949e]">Views can be inflated. Forwards can't. Track the metric that actually matters.</p>
          </div>
          <div class="rounded-[28px] border border-[#30363d] bg-[#161b22] p-6 shadow-[0_18px_50px_rgba(0,0,0,0.18)]">
            <div class="flex items-center justify-between rounded-2xl bg-[#0f161f] px-4 py-3 text-sm font-jetbrains-mono uppercase tracking-[0.26em] text-[#8b949e]">
              <span>Forward ratio</span>
              <span>Actual virality</span>
            </div>
            <div class="mt-6 space-y-4">
              <div class="space-y-2 rounded-3xl bg-[#0d1117] p-5">
                <div class="mb-3 flex items-center justify-between text-sm text-[#8b949e]"><span>Post A</span><span>14%</span></div>
                <div class="h-3 rounded-full bg-[#21262d]">
                  <div class="h-full w-[72%] rounded-full bg-[#58a6ff]"></div>
                </div>
              </div>
              <div class="space-y-2 rounded-3xl bg-[#0d1117] p-5">
                <div class="mb-3 flex items-center justify-between text-sm text-[#8b949e]"><span>Post B</span><span>9.2%</span></div>
                <div class="h-3 rounded-full bg-[#21262d]">
                  <div class="h-full w-[52%] rounded-full bg-[#3fb950]"></div>
                </div>
              </div>
              <div class="space-y-2 rounded-3xl bg-[#0d1117] p-5">
                <div class="mb-3 flex items-center justify-between text-sm text-[#8b949e]"><span>Post C</span><span>3.4%</span></div>
                <div class="h-3 rounded-full bg-[#21262d]">
                  <div class="h-full w-[32%] rounded-full bg-[#d29922]"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="bg-[#161b22] py-12 live-metrics-strip">
        <div class="container-custom mx-auto px-4">
          <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-3xl border border-[#30363d] bg-[#0d1117] p-6 text-center">
              <div class="text-sm uppercase tracking-[0.24em] text-[#79c0ff]">Channels</div>
              <div class="mt-3 text-3xl font-[700] font-jetbrains-mono">{{ counters[0].display.toLocaleString() }}{{ counters[0].suffix }}</div>
              <div class="mt-2 text-sm text-[#8b949e]">channels tracked</div>
            </div>
            <div class="rounded-3xl border border-[#30363d] bg-[#0d1117] p-6 text-center">
              <div class="text-sm uppercase tracking-[0.24em] text-[#79c0ff]">Posts</div>
              <div class="mt-3 text-3xl font-[700] font-jetbrains-mono">{{ counters[1].display.toLocaleString() }}{{ counters[1].suffix }}</div>
              <div class="mt-2 text-sm text-[#8b949e]">posts analyzed</div>
            </div>
            <div class="rounded-3xl border border-[#30363d] bg-[#0d1117] p-6 text-center">
              <div class="text-sm uppercase tracking-[0.24em] text-[#79c0ff]">Viral hits</div>
              <div class="mt-3 text-3xl font-[700] font-jetbrains-mono">{{ counters[2].display.toLocaleString() }}{{ counters[2].suffix }}</div>
              <div class="mt-2 text-sm text-[#8b949e]">viral detections</div>
            </div>
            <div class="rounded-3xl border border-[#30363d] bg-[#0d1117] p-6 text-center">
              <div class="text-sm uppercase tracking-[0.24em] text-[#79c0ff]">Lift</div>
              <div class="mt-3 text-3xl font-[700] font-jetbrains-mono">{{ counters[3].display }}{{ counters[3].suffix }}</div>
              <div class="mt-2 text-sm text-[#8b949e]">avg engagement lift</div>
            </div>
          </div>
        </div>
      </section>

      <section class="container-custom mx-auto px-4 py-20">
        <div class="rounded-[30px] border border-[#30363d] bg-[#161b22] p-8 font-jetbrains-mono text-[#e6edf3] shadow-[0_20px_70px_rgba(0,0,0,0.18)]">
          <div class="text-sm uppercase tracking-[0.26em] text-[#79c0ff]">How it works</div>
          <pre class="mt-6 whitespace-pre-wrap text-[0.94rem] leading-7 text-[#e6edf3]">$ influence connect @yourchannel
  ✓ Channel verified
  ✓ Syncing 847 historical posts...
  ✓ Building engagement heatmap
  ✓ Calculating viral baseline
  → Dashboard ready at influence.uz/dashboard</pre>
        </div>
      </section>

      <section id="pricing" class="container-custom mx-auto px-4 py-20">
        <div class="space-y-4 text-center">
          <span class="text-sm uppercase tracking-[0.28em] text-[#79c0ff]">Pricing</span>
          <h2 class="text-3xl font-[700] text-[#e6edf3] sm:text-4xl">Plans built for any Telegram workflow.</h2>
        </div>

        <div class="mt-12 grid gap-6 lg:grid-cols-3">
          <div class="rounded-[28px] border border-[#30363d] bg-[#161b22] p-8 text-[#e6edf3]">
            <div class="text-sm uppercase tracking-[0.22em] text-[#8b949e]">Free</div>
            <div class="mt-6 text-5xl font-[700] font-jetbrains-mono text-[#e6edf3]">$0</div>
            <div class="mt-3 text-[#8b949e]">For individual creators who want straightforward channel signals.</div>
            <ul class="mt-8 space-y-3 text-sm text-[#8b949e]">
              <li>Basic channel tracking</li>
              <li>Weekly reports</li>
              <li>Up to 1 channel</li>
            </ul>
            <div class="mt-8 text-sm text-[#79c0ff]">Most used by: Individual creators</div>
            <a href="/register" class="mt-6 inline-flex w-full items-center justify-center rounded-full border border-[#30363d] px-5 py-3 text-sm font-semibold text-[#79c0ff] transition hover:border-[#58a6ff] hover:text-[#e6edf3]">Start free</a>
          </div>

          <div class="rounded-[28px] border-l-4 border-[#58a6ff] bg-[#161b22] p-8 text-[#e6edf3]">
            <div class="text-sm uppercase tracking-[0.22em] text-[#8b949e]">Pro</div>
            <div class="mt-6 text-5xl font-[700] font-jetbrains-mono text-[#e6edf3]">$29</div>
            <div class="mt-3 text-[#8b949e]">For channel admins and media buyers tracking growth, peak windows, and virality.</div>
            <ul class="mt-8 space-y-3 text-sm text-[#8b949e]">
              <li>Realtime velocity charts</li>
              <li>Forward ratio alerts</li>
              <li>Historical heatmaps</li>
            </ul>
            <div class="mt-8 text-sm text-[#79c0ff]">Most used by: Channel admins, media buyers</div>
            <a href="/register" class="mt-6 inline-flex w-full items-center justify-center rounded-full bg-[#58a6ff] px-5 py-3 text-sm font-semibold text-[#0d1117] transition hover:bg-[#3f8cee]">Choose Pro</a>
          </div>

          <div class="rounded-[28px] border border-[#30363d] bg-[#161b22] p-8 text-[#e6edf3]">
            <div class="text-sm uppercase tracking-[0.22em] text-[#8b949e]">Agency</div>
            <div class="mt-6 text-5xl font-[700] font-jetbrains-mono text-[#e6edf3]">$79</div>
            <div class="mt-3 text-[#8b949e]">For agencies and networks that need multiple channels, exports, and custom reporting.</div>
            <ul class="mt-8 space-y-3 text-sm text-[#8b949e]">
              <li>Multi-channel dashboards</li>
              <li>Excel export ready</li>
              <li>Priority support</li>
            </ul>
            <div class="mt-8 text-sm text-[#79c0ff]">Most used by: Marketing agencies, influencer networks</div>
            <a href="/register" class="mt-6 inline-flex w-full items-center justify-center rounded-full border border-[#30363d] px-5 py-3 text-sm font-semibold text-[#79c0ff] transition hover:border-[#58a6ff] hover:text-[#e6edf3]">Talk to sales</a>
          </div>
        </div>
      </section>

      <section class="container-custom mx-auto px-4 py-20">
        <div class="space-y-6 text-center">
          <span class="text-sm uppercase tracking-[0.26em] text-[#79c0ff]">Social proof</span>
          <h2 class="text-3xl font-[700] text-[#e6edf3] sm:text-4xl">Channels that grew with tracked signals.</h2>
        </div>

        <div class="mt-12 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
          <div v-for="card in proofCards" :key="card.name" class="rounded-[28px] border border-[#30363d] bg-[#161b22] p-6">
            <div class="flex items-center justify-between text-sm text-[#8b949e]"><span class="font-jetbrains-mono text-[#79c0ff]">{{ card.name }}</span><span>{{ card.subs }}</span></div>
            <div class="mt-6 text-2xl font-[700] text-[#e6edf3]">Before</div>
            <div class="mt-2 text-[#8b949e]">avg {{ card.before }} views</div>
            <div class="mt-6 text-2xl font-[700] text-[#e6edf3]">After</div>
            <div class="mt-2 text-[#3fb950]">avg {{ card.after }} views</div>
          </div>
        </div>
      </section>

      <section class="container-custom mx-auto px-4 py-20">
        <div class="space-y-6 text-center">
          <span class="text-sm uppercase tracking-[0.26em] text-[#79c0ff]">FAQ</span>
          <h2 class="text-3xl font-[700] text-[#e6edf3] sm:text-4xl">Common questions, answered directly.</h2>
        </div>

        <div class="mt-10 space-y-4">
          <div v-for="(faq, index) in faqs" :key="faq.question" class="rounded-3xl border border-[#30363d] bg-[#161b22] p-5">
            <button type="button" @click="toggleFaq(index)" class="flex w-full items-start justify-between gap-4 text-left text-sm text-[#e6edf3]">
              <div>
                <span class="font-jetbrains-mono text-[#79c0ff]">Q:</span>
                <span class="ml-2 font-[600]">{{ faq.question }}</span>
              </div>
              <span class="text-[#8b949e]">{{ activeFaq === index ? '-' : '+' }}</span>
            </button>
            <div v-if="activeFaq === index" class="mt-4 text-[#8b949e] text-sm leading-7">{{ faq.answer }}</div>
          </div>
        </div>
      </section>

      <section class="bg-[#161b22] py-20">
        <div class="container-custom mx-auto px-4">
          <div class="rounded-[24px] border border-[#30363d] bg-[#0d1117] p-8 text-center">
            <div class="mx-auto inline-flex rounded-[14px] border border-[#30363d] bg-[#161b22] px-6 py-5 text-left text-[#79c0ff] sm:px-10">
              <div class="space-y-2 text-left">
                <div class="text-xl font-[700] text-[#e6edf3]">╔════════════════════════════════════╗</div>
                <div class="text-3xl font-[800] leading-none text-[#e6edf3]">║  Stop guessing. Start tracking.   ║</div>
                <div class="text-xl font-[700] text-[#e6edf3]">╚════════════════════════════════════╝</div>
              </div>
            </div>

            <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
              <a href="/register" class="inline-flex items-center justify-center rounded-full bg-[#58a6ff] px-8 py-3 text-sm font-semibold text-[#0d1117] transition hover:bg-[#3f8cee]">Connect free</a>
              <a href="/register" class="inline-flex items-center justify-center rounded-full border border-[#30363d] px-8 py-3 text-sm font-semibold text-[#79c0ff] transition hover:border-[#58a6ff] hover:text-[#e6edf3]">Book a demo</a>
            </div>

            <p class="mt-5 text-sm font-jetbrains-mono text-[#8b949e]">No credit card. No setup. 60-second connect.</p>
          </div>
        </div>
      </section>
    </main>

    <footer class="border-t border-[#30363d] bg-[#0d1117] py-12 text-[#8b949e]">
      <div class="container-custom mx-auto px-4">
        <div class="grid gap-8 md:grid-cols-4">
          <div>
            <div class="text-lg font-[700] text-[#e6edf3]">Influence</div>
            <p class="mt-3 max-w-[280px] text-sm text-[#8b949e]">Built for Telegram. Made in Uzbekistan. 🇺🇿</p>
          </div>
          <div>
            <div class="mb-3 font-[700] text-[#e6edf3]">Product</div>
            <ul class="space-y-2 text-sm">
              <li><a href="#features" class="footer-link">Features</a></li>
              <li><a href="#pricing" class="footer-link">Pricing</a></li>
              <li><a href="#api" class="footer-link">API</a></li>
            </ul>
          </div>
          <div>
            <div class="mb-3 font-[700] text-[#e6edf3]">Company</div>
            <ul class="space-y-2 text-sm">
              <li><a href="/login" class="footer-link">Sign in</a></li>
              <li><a href="/register" class="footer-link">Start free</a></li>
            </ul>
          </div>
          <div>
            <div class="mb-3 font-[700] text-[#e6edf3]">Contact</div>
            <p class="text-sm text-[#8b949e]">support@influence.uz</p>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';

const heatmapRows = [
  { label: 'Mon', cells: ['low', 'low', 'mid', 'mid', 'hot', 'hot', 'mid', 'low', 'low', 'mid', 'mid', 'hot', 'hot', 'mid', 'low', 'low', 'mid', 'hot', 'hot', 'mid', 'low', 'low', 'low', 'low'] },
  { label: 'Tue', cells: ['low', 'mid', 'mid', 'hot', 'hot', 'mid', 'mid', 'low', 'low', 'low', 'mid', 'hot', 'hot', 'mid', 'mid', 'low', 'low', 'mid', 'hot', 'mid', 'low', 'low', 'low', 'low'] },
  { label: 'Wed', cells: ['low', 'low', 'mid', 'mid', 'hot', 'hot', 'mid', 'low', 'low', 'mid', 'hot', 'hot', 'mid', 'mid', 'low', 'low', 'low', 'mid', 'hot', 'mid', 'low', 'low', 'low', 'low'] },
  { label: 'Thu', cells: ['low', 'low', 'low', 'mid', 'hot', 'hot', 'mid', 'mid', 'low', 'mid', 'hot', 'hot', 'mid', 'mid', 'low', 'low', 'low', 'mid', 'hot', 'mid', 'low', 'low', 'low', 'low'] },
  { label: 'Fri', cells: ['low', 'low', 'mid', 'mid', 'hot', 'hot', 'mid', 'mid', 'low', 'mid', 'hot', 'hot', 'mid', 'mid', 'low', 'low', 'low', 'mid', 'hot', 'mid', 'low', 'low', 'low', 'low'] },
  { label: 'Sat', cells: ['low', 'low', 'mid', 'hot', 'hot', 'mid', 'mid', 'low', 'low', 'mid', 'hot', 'hot', 'mid', 'mid', 'low', 'low', 'low', 'mid', 'hot', 'mid', 'low', 'low', 'low', 'low'] },
  { label: 'Sun', cells: ['low', 'low', 'mid', 'mid', 'hot', 'hot', 'mid', 'low', 'low', 'mid', 'hot', 'hot', 'mid', 'mid', 'low', 'low', 'low', 'mid', 'hot', 'mid', 'low', 'low', 'low', 'low'] },
];

const proofCards = [
  { name: '@uzreport', subs: '128K subs', before: '24K', after: '46K' },
  { name: '@texnouz', subs: '92K subs', before: '18K', after: '39K' },
  { name: '@marketuz', subs: '74K subs', before: '11K', after: '26K' },
];

const faqs = [
  {
    question: "Does it work for channels I don't own?",
    answer: "Yes. Influence can monitor public Telegram channels and compare competitor signals with your own channel performance.",
  },
  {
    question: "Is my data shared with advertisers?",
    answer: "No. Your channel metrics remain private and are not sold or shared with third-party advertisers.",
  },
  {
    question: "Can I export data to Excel?",
    answer: "Yes. Export charts, heatmaps, and raw post metrics into XLSX for offline analysis and reporting.",
  },
  {
    question: "How fast does the dashboard update?",
    answer: "Channel signals refresh every 15 minutes so you can act on peaks and momentum without delay.",
  },
  {
    question: "What makes forward ratio more reliable?",
    answer: "Views can be inflated by bots or reposts. Forwards are harder to fake and show real distribution velocity.",
  },
];

const activeFaq = ref(0);
const typeBuffer = ref(['', '', '', '']);

const terminalRows = [
  '@uzbekistan_news',
  'Subscribers  ████████ 128K',
  'Last post    ▲ 2.4x spike',
  'Best window  19:00 – 21:00',
  '@techuz          ▲ trending',
  'Fwd ratio    ████░░░ 6.2%',
  'Viral score  94 / 100',
];

const terminalDisplay = reactive(Array(terminalRows.length).fill(''));

const counters = reactive([
  { label: 'channels tracked', value: 1247, display: 0, suffix: '' },
  { label: 'posts analyzed', value: 48000000, display: 0, suffix: '' },
  { label: 'viral detections', value: 3200000, display: 0, suffix: '' },
  { label: 'engagement lift', value: 31, display: 0, suffix: '%' },
]);

function toggleFaq(index) {
  activeFaq.value = activeFaq.value === index ? -1 : index;
}

function animateTerminal() {
  let row = 0;
  let char = 0;
  const reveal = () => {
    const current = terminalRows[row];
    terminalDisplay[row] = current.slice(0, char);
    if (char < current.length) {
      char += 1;
      setTimeout(reveal, 40);
      return;
    }
    terminalDisplay[row] = current;
    row = (row + 1) % terminalRows.length;
    char = 0;
    setTimeout(reveal, 260);
  };
  reveal();
}

function animateCounter(counter) {
  const start = performance.now();
  const duration = 1200;
  const initial = 0;
  const target = counter.value;
  const step = (time) => {
    const progress = Math.min(1, (time - start) / duration);
    if (target >= 1000) {
      counter.display = Math.round(progress * target / 1000) * 1000;
    } else {
      counter.display = Math.round(progress * target);
    }
    if (progress < 1) {
      requestAnimationFrame(step);
    } else {
      counter.display = target;
    }
  };
  requestAnimationFrame(step);
}

onMounted(() => {
  animateTerminal();
  const strip = document.querySelector('.live-metrics-strip');
  if (!strip) return;
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        counters.forEach((counter) => {
          if (counter.display === 0) animateCounter(counter);
        });
        observer.disconnect();
      }
    });
  }, { threshold: 0.4 });
  observer.observe(strip);
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap');

.landing-root {
  font-family: 'Inter', system-ui, sans-serif;
  background: #0d1117;
}

.font-jetbrains-mono {
  font-family: 'JetBrains Mono', ui-monospace, monospace;
}

.nav-link {
  position: relative;
  color: #8b949e;
  transition: color 0.2s ease;
}

.nav-link::after {
  content: '';
  position: absolute;
  left: 0;
  bottom: -4px;
  width: 0;
  height: 2px;
  background: #58a6ff;
  transition: width 0.2s ease;
}

.nav-link:hover {
  color: #e6edf3;
}

.nav-link:hover::after {
  width: 100%;
}

.ticker {
  animation: ticker 22s linear infinite;
}

.ticker:hover {
  animation-play-state: paused;
}

@keyframes ticker {
  0% { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}

@media (max-width: 900px) {
  .landing-root header .container-custom { padding-left: 16px; padding-right: 16px; }
}

.live-metrics-strip {
  position: relative;
}

@media (max-width: 768px) {
  .ticker {
    animation-duration: 30s;
  }
}
</style>
