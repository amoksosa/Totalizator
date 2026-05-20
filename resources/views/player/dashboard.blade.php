<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Player Dashboard</title>

    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow+Condensed:wght@400;600;700;800;900&family=Anton&display=swap" rel="stylesheet">

    @vite([
        'resources/css/app.css',
        'resources/css/player-dashboard.css',
        'resources/js/app.js',
        'resources/js/player-dashboard.js',
    ])
</head>

<body
    data-user-id="{{ auth()->id() }}"
    data-player-balance="{{ (float) (auth()->user()->credit_balance ?? 0) }}"
    data-bet-url="{{ route('player.bet') }}"
    data-csrf-token="{{ csrf_token() }}"
    data-latest-declaration-url="{{ route('player.latest-declaration') }}"
    data-current-bet-totals-url="{{ route('player.current-bet-totals') }}"
>
    <nav class="player-nav">
        <div>
            <div class="player-nav-title">
                Player Game
            </div>

            <div class="player-nav-sub">
                Welcome, {{ auth()->user()->username }}
            </div>
        </div>

        <div class="player-nav-actions">
            <div class="nav-balance">
                Balance: ₱<span id="player-balance">{{ number_format(auth()->user()->credit_balance ?? 0, 2) }}</span>
            </div>

            <a href="{{ route('player.bet-history') }}" class="nav-link">
                Bet History
            </a>

            <a href="{{ route('withdrawals.index') }}" class="nav-link">
                Withdraw
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button class="nav-logout">
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <div class="page-wrap">
        <div class="odds-card">
            <div class="odds-head">
                ODDS
            </div>

            <div class="odds-input-zone">
                <input
                    id="odds-amount"
                    type="number"
                    inputmode="numeric"
                    class="odds-input"
                    placeholder="Enter Amount"
                />

                <button type="button" id="odds-allin" class="odds-allin-btn">
                    ALL-IN
                </button>
            </div>

            <div class="odds-chips-row">
                <button type="button" class="odds-chip" data-val="200">200</button>
                <button type="button" class="odds-chip" data-val="500">500</button>
                <button type="button" class="odds-chip" data-val="1000">1,000</button>
                <button type="button" class="odds-chip" data-val="2000">2,000</button>
                <button type="button" class="odds-chip" data-val="3000">3,000</button>
                <button type="button" class="odds-chip" data-val="5000">5,000</button>
                <button type="button" class="odds-chip" data-val="10000">10,000</button>
            </div>

            <div class="odds-tbl-head">
                <div class="odds-th-meron odds-th-cell">
                    MERON
                </div>

                <div class="odds-th-total-red odds-th-cell dim">
                    TOTAL
                </div>

                <div class="odds-th-mid odds-th-cell dim">
                    ODDS
                </div>

                <div class="odds-th-total-blue odds-th-cell dim">
                    TOTAL
                </div>

                <div class="odds-th-wala odds-th-cell">
                    WALA
                </div>
            </div>

            <div id="odds-table-rows"></div>

            <div class="odds-draw-row">
                <div class="odds-draw-play">
                    DRAW BET
                </div>

                <div class="odds-draw-18">
                    1-8
                </div>

                <div class="odds-draw-label">
                    DRAW
                </div>

                <div class="odds-draw-total">
                    TOTAL
                </div>

                <div class="odds-cell-wala">
                    <button
                        type="button"
                        class="odds-btn-minus"
                        data-side="DRAW"
                        data-odds="1-8"
                        data-dir="minus"
                    >
                        −
                    </button>

                    <span id="odds-cell-DRAW-1_8" class="odds-counter">
                        0
                    </span>

                    <button
                        type="button"
                        class="odds-btn-plus"
                        data-side="DRAW"
                        data-odds="1-8"
                        data-dir="plus"
                    >
                        +
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>