<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Totalizator Game</title>

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
                Totalizator Game
            </div>

            <div class="player-nav-sub">
                Welcome, {{ auth()->user()->username }}
            </div>
        </div>

        <div class="player-nav-actions">

            <div class="nav-balance">
                Balance: ₱<span id="player-balance">{{ number_format(auth()->user()->credit_balance ?? 0, 2) }}</span>
            </div>

            <a href="{{ route('player.dashboard') }}" class="nav-link">
                Dashboard
            </a>

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

    <style>
        .player-nav {
            position: sticky;
            top: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 14px 22px;
            background:
                linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(30, 41, 59, 0.94)),
                radial-gradient(circle at top left, rgba(34, 197, 94, 0.18), transparent 35%),
                radial-gradient(circle at top right, rgba(59, 130, 246, 0.16), transparent 35%);
            border-bottom: 1px solid rgba(148, 163, 184, 0.22);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(18px);
        }

        .player-nav-title {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 30px;
            line-height: 1;
            letter-spacing: 1.5px;
            color: #ffffff;
            text-shadow: 0 2px 18px rgba(34, 197, 94, 0.18);
        }

        .player-nav-sub {
            margin-top: 4px;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 15px;
            font-weight: 700;
            color: rgba(226, 232, 240, 0.72);
            letter-spacing: 0.4px;
        }

        .player-nav-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 10px;
        }

        .nav-balance {
            border: 1px solid rgba(52, 211, 153, 0.35);
            background: linear-gradient(135deg, rgba(6, 78, 59, 0.8), rgba(16, 185, 129, 0.16));
            color: #d1fae5;
            padding: 10px 14px;
            border-radius: 16px;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 16px;
            font-weight: 900;
            letter-spacing: 0.5px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.08), 0 10px 25px rgba(16, 185, 129, 0.12);
        }

        .nav-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 10px 15px;
            border-radius: 15px;
            border: 1px solid rgba(148, 163, 184, 0.24);
            background: rgba(255, 255, 255, 0.08);
            color: #f8fafc;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 15px;
            font-weight: 900;
            letter-spacing: 0.5px;
            text-decoration: none;
            transition: 180ms ease;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.06);
        }

        .nav-link:hover {
            transform: translateY(-1px);
            border-color: rgba(96, 165, 250, 0.55);
            background: rgba(59, 130, 246, 0.18);
            color: #dbeafe;
        }

        .nav-logout {
            min-height: 42px;
            padding: 10px 15px;
            border: 0;
            border-radius: 15px;
            background: linear-gradient(135deg, #dc2626, #991b1b);
            color: #ffffff;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 15px;
            font-weight: 900;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: 180ms ease;
            box-shadow: 0 12px 26px rgba(220, 38, 38, 0.22);
        }

        .nav-logout:hover {
            transform: translateY(-1px);
            background: linear-gradient(135deg, #ef4444, #b91c1c);
        }

        @media (max-width: 768px) {
            .player-nav {
                align-items: stretch;
                flex-direction: column;
                padding: 14px;
            }

            .player-nav-actions {
                display: grid;
                grid-template-columns: 1fr 1fr;
                width: 100%;
            }

            .nav-balance {
                grid-column: span 2;
                text-align: center;
            }

            .nav-link,
            .nav-logout {
                width: 100%;
            }

            .player-nav-actions form {
                width: 100%;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>