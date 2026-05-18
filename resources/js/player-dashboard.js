const ODDS_ROWS = ["10-10", "10-9", "10-8", "8-6", "9-10", "8-10", "6-8"];

const pageData = document.body.dataset;

const oddsState = {};
let currentBalance = Number(pageData.playerBalance || 0);

const userId = pageData.userId;
const betUrl = pageData.betUrl;
const csrfToken = pageData.csrfToken;
const latestDeclarationUrl = pageData.latestDeclarationUrl;
const currentBetTotalsUrl = pageData.currentBetTotalsUrl;

let lastSeenDeclarationId = null;
let latestDeclarationInitialized = false;

const oddsTableRows = document.getElementById("odds-table-rows");
const oddsAmount = document.getElementById("odds-amount");
const allInButton = document.getElementById("odds-allin");
const chipButtons = document.querySelectorAll(".odds-chip");
const balanceDisplay = document.getElementById("player-balance");

function safeOddsId(label) {
    return label.replace(/[^0-9a-zA-Z]+/g, "_");
}

function formatMoney(value) {
    return Number(value || 0).toLocaleString("en-PH", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function formatNumber(value) {
    return Number(value || 0).toLocaleString("en-PH");
}

function getStep() {
    const value = parseFloat(oddsAmount.value || "0");

    return value && value > 0 ? value : 0;
}

async function placeBet(side, label, amount) {
    const response = await fetch(betUrl, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify({
            side,
            odds: label,
            amount,
        }),
    });

    const data = await response.json();

    if (!response.ok || !data.success) {
        throw new Error(data.message || "Unable to place bet.");
    }

    return data;
}

function updateBalance(newBalance) {
    currentBalance = Number(newBalance || 0);

    if (balanceDisplay) {
        balanceDisplay.textContent = formatMoney(currentBalance);
    }
}

function renderSingleCell(side, label, value) {
    const formattedValue = formatNumber(value);

    if (side === "DRAW") {
        const drawCell = document.getElementById("odds-cell-DRAW-1_8");

        if (drawCell) {
            drawCell.textContent = formattedValue;
        }

        return;
    }

    const sid = safeOddsId(label);

    const cell = document.getElementById(`odds-cell-${side}-${sid}`);
    const total = document.getElementById(`odds-total-${side}-${sid}`);

    if (cell) {
        cell.textContent = formattedValue;
    }

    if (total) {
        total.textContent = formattedValue;
    }
}

function resetBettingBoard() {
    ODDS_ROWS.forEach((label) => {
        oddsState[`MERON|${label}`] = 0;
        oddsState[`WALA|${label}`] = 0;

        renderSingleCell("MERON", label, 0);
        renderSingleCell("WALA", label, 0);
    });

    oddsState["DRAW|1-8"] = 0;
    renderSingleCell("DRAW", "1-8", 0);

    if (oddsAmount) {
        oddsAmount.value = "";
    }
}

function showWinnerPopup(event) {
    let color = "#2563eb";

    if (event.winner === "MERON") {
        color = "#dc2626";
    }

    if (event.winner === "WALA") {
        color = "#2563eb";
    }

    if (event.winner === "DRAW") {
        color = "#16a34a";
    }

    if (typeof Swal !== "undefined") {
        Swal.fire({
            icon: "success",
            title: "Winner Declared",
            html: `
                <div style="font-size: 42px; font-weight: 900; margin-top: 10px;">
                    ${event.winner}
                </div>

                <div style="margin-top: 10px; font-size: 16px;">
                    Round: <strong>${event.round_code ?? "N/A"}</strong>
                </div>

                <div style="margin-top: 14px; font-size: 14px;">
                    Betting board has been reset for the new fight.
                </div>
            `,
            confirmButtonColor: color,
        });
    }
}

async function updateOdds(side, label, dir) {
    const key = `${side}|${label}`;
    const currentValue = oddsState[key] || 0;
    const step = getStep();

    if (step <= 0) {
        Swal.fire({
            icon: "warning",
            title: "Enter Bet Amount",
            text: "Please enter an amount before placing a bet.",
            confirmButtonColor: "#f59e0b",
        });

        return;
    }

    if (dir === "minus") {
        const nextValue = Math.max(0, currentValue - step);

        oddsState[key] = nextValue;
        renderSingleCell(side, label, nextValue);

        return;
    }

    if (currentBalance < step) {
        Swal.fire({
            icon: "error",
            title: "Insufficient Balance",
            text: "Your credit balance is not enough for this bet.",
            confirmButtonColor: "#dc2626",
        });

        return;
    }

    try {
        const data = await placeBet(side, label, step);
        const nextValue = currentValue + step;

        oddsState[key] = nextValue;

        renderSingleCell(side, label, nextValue);
        updateBalance(data.new_balance);

        Swal.fire({
            icon: "success",
            title: "Bet Placed",
            text: `${side} ${label} bet amount ₱${formatMoney(step)} was placed successfully.`,
            timer: 1300,
            showConfirmButton: false,
        });
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: "Bet Failed",
            text: error.message,
            confirmButtonColor: "#dc2626",
        });
    }
}

function renderOddsRow(label) {
    const sid = safeOddsId(label);

    oddsState[`MERON|${label}`] = 0;
    oddsState[`WALA|${label}`] = 0;

    const row = document.createElement("div");

    row.className = "odds-data-row";

    row.innerHTML = `
        <div class="odds-cell-meron">
            <button type="button" class="odds-btn-minus" data-side="MERON" data-odds="${label}" data-dir="minus">−</button>
            <span id="odds-cell-MERON-${sid}" class="odds-counter">0</span>
            <button type="button" class="odds-btn-plus" data-side="MERON" data-odds="${label}" data-dir="plus">+</button>
        </div>

        <div class="odds-cell-total-red">
            <span id="odds-total-MERON-${sid}">0</span>
        </div>

        <div class="odds-cell-mid">${label}</div>

        <div class="odds-cell-total-blue">
            <span id="odds-total-WALA-${sid}">0</span>
        </div>

        <div class="odds-cell-wala">
            <button type="button" class="odds-btn-minus" data-side="WALA" data-odds="${label}" data-dir="minus">−</button>
            <span id="odds-cell-WALA-${sid}" class="odds-counter">0</span>
            <button type="button" class="odds-btn-plus" data-side="WALA" data-odds="${label}" data-dir="plus">+</button>
        </div>
    `;

    oddsTableRows.appendChild(row);
}

function initOddsTable() {
    if (!oddsTableRows) {
        return;
    }

    oddsTableRows.innerHTML = "";

    ODDS_ROWS.forEach((label) => {
        renderOddsRow(label);
    });

    oddsState["DRAW|1-8"] = 0;
}

function initBetButtons() {
    document.addEventListener("click", (event) => {
        const button = event.target.closest("[data-side][data-odds][data-dir]");

        if (!button) {
            return;
        }

        updateOdds(button.dataset.side, button.dataset.odds, button.dataset.dir);
    });
}

function initChips() {
    chipButtons.forEach((button) => {
        button.addEventListener("click", () => {
            oddsAmount.value = parseInt(button.dataset.val || "0", 10);
            oddsAmount.focus();
        });
    });
}

function initAllInButton() {
    if (!allInButton) {
        return;
    }

    allInButton.addEventListener("click", () => {
        oddsAmount.value = Math.floor(currentBalance);
        oddsAmount.focus();
    });
}

function initCreditBroadcastListener() {
    if (!window.Echo) {
        console.error("Laravel Echo is not loaded.");
        return;
    }

    window.Echo.channel("user." + userId)
        .listen(".credit.updated", (event) => {
            updateBalance(event.credit_balance);

            if (typeof Swal !== "undefined") {
                Swal.fire({
                    icon: "info",
                    title: "Credit Balance Updated",
                    text: "Your new balance is " + event.formatted_balance,
                    timer: 1400,
                    showConfirmButton: false,
                });
            }
        });
}

function initForceLogoutListener() {
    if (!window.Echo) {
        console.error("Laravel Echo is not loaded.");
        return;
    }

    window.Echo.channel("user." + userId)
        .listen(".force.logout", (event) => {
            Swal.fire({
                icon: "warning",
                title: "Logged Out",
                text: event.message,
                confirmButtonColor: "#dc2626",
                allowOutsideClick: false,
                allowEscapeKey: false,
            }).then(() => {
                window.location.href = event.redirect_url;
            });

            setTimeout(() => {
                window.location.href = event.redirect_url;
            }, 2500);
        });
}

function initWinnerDeclarationListener() {
    if (!window.Echo) {
        console.error("Laravel Echo is not loaded.");
        return;
    }

    window.Echo.channel("game.declarations")
        .listen(".winner.declared", (event) => {
            console.log("WINNER DECLARED RECEIVED:", event);

            lastSeenDeclarationId = event.id;

            resetBettingBoard();
            showWinnerPopup(event);
        });
}

async function checkLatestDeclaration() {
    if (!latestDeclarationUrl) {
        console.error("Latest declaration URL is missing. Check data-latest-declaration-url in player dashboard body.");
        return;
    }

    try {
        const response = await fetch(latestDeclarationUrl, {
            headers: {
                Accept: "application/json",
            },
        });

        const data = await response.json();

        if (!data.success || !data.declaration) {
            return;
        }

        const declaration = data.declaration;

        if (!latestDeclarationInitialized) {
            lastSeenDeclarationId = declaration.id;
            latestDeclarationInitialized = true;
            return;
        }

        if (Number(lastSeenDeclarationId) !== Number(declaration.id)) {
            lastSeenDeclarationId = declaration.id;

            resetBettingBoard();
            showWinnerPopup(declaration);
        }
    } catch (error) {
        console.error("Latest declaration check failed:", error);
    }
}

function initDeclarationPolling() {
    checkLatestDeclaration();

    setInterval(() => {
        checkLatestDeclaration();
    }, 2000);
}

function applyExternalBetToBoard(event) {
    const side = event.side;
    const label = event.odds;
    const amount = Number(event.amount || 0);
    const key = `${side}|${label}`;

    if (amount <= 0) {
        return;
    }

    const currentValue = oddsState[key] || 0;
    const nextValue = currentValue + amount;

    oddsState[key] = nextValue;
    renderSingleCell(side, label, nextValue);
}

function initLiveBetListener() {
    if (!window.Echo) {
        console.error("Laravel Echo is not loaded.");
        return;
    }

    window.Echo.channel("game.bets")
        .listen(".player.bet.placed", (event) => {
            /**
             * The player who placed the bet already updates their own board
             * after the request succeeds, so skip their own broadcast to avoid double count.
             */
            if (Number(event.player_id) === Number(userId)) {
                return;
            }

            applyExternalBetToBoard(event);
        });
}

function applyBetTotalToBoard(total) {
    const side = total.side;
    const label = total.odds;
    const amount = Number(total.amount || 0);
    const key = `${side}|${label}`;

    oddsState[key] = amount;
    renderSingleCell(side, label, amount);
}

async function loadCurrentBetTotals() {
    if (!currentBetTotalsUrl) {
        console.error("Current bet totals URL is missing.");
        return;
    }

    try {
        const response = await fetch(currentBetTotalsUrl, {
            headers: {
                Accept: "application/json",
            },
        });

        const data = await response.json();

        if (!data.success || !Array.isArray(data.totals)) {
            return;
        }

        data.totals.forEach((total) => {
            applyBetTotalToBoard(total);
        });
    } catch (error) {
        console.error("Loading current bet totals failed:", error);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    initOddsTable();
    loadCurrentBetTotals();
    initBetButtons();
    initChips();
    initAllInButton();
    initCreditBroadcastListener();
    initForceLogoutListener();
    initWinnerDeclarationListener();
    initDeclarationPolling();
    initLiveBetListener();
});