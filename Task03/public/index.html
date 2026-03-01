<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prime Game</title>
    <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
<main class="container">
    <h1>Prime Game</h1>

    <section class="panel panel-hero">
        <h2>Новая игра</h2>
        <form id="new-game-form">
            <label for="player-name">Player name</label>
            <input id="player-name" type="text" required placeholder="Enter name">
            <button class="button" type="submit">Start</button>
        </form>
        <p class="muted" id="game-state">Игра не начата</p>
    </section>

    <section class="panel panel-game">
        <h2>Раунд</h2>
        <p class="muted">Это простое число?</p>
        <div class="number-box" id="current-number">--</div>
        <div class="row">
            <button class="button answer-yes" id="answer-yes" type="button">Yes</button>
            <button class="button answer-no" id="answer-no" type="button">No</button>
            <button class="button button-outline" type="button" id="next-number">Next number</button>
        </div>
        <div class="score row">
            <div><strong>Ходов:</strong> <span id="steps-count">0</span></div>
            <div><strong>Верных:</strong> <span id="correct-count">0</span></div>
            <div><strong>Точность:</strong> <span id="accuracy">0%</span></div>
        </div>
        <pre id="round-result" class="code">Нажми Start, затем выбери Yes или No.</pre>
    </section>

    <section class="panel">
        <div class="row row-between">
            <h2>История игр</h2>
            <div class="row">
                <button class="button button-outline" id="show-current-game" type="button">Current game</button>
                <button class="button" id="refresh-games" type="button">Refresh</button>
            </div>
        </div>
        <table id="games-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Player</th>
                <th>Created</th>
                <th>Updated</th>
                <th>Steps</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </section>

    <section class="panel">
        <h2>Детали выбранной игры</h2>
        <pre id="game-details" class="code">Выбери игру из таблицы.</pre>
    </section>
</main>

<script>
    const state = {
        currentGameId: null,
        currentPlayerName: '',
        currentNumber: null,
        totalSteps: 0,
        totalCorrect: 0,
    };

    const gameState = document.getElementById('game-state');
    const roundResult = document.getElementById('round-result');
    const gameDetails = document.getElementById('game-details');
    const currentNumber = document.getElementById('current-number');
    const stepsCount = document.getElementById('steps-count');
    const correctCount = document.getElementById('correct-count');
    const accuracy = document.getElementById('accuracy');
    const gamesTableBody = document.querySelector('#games-table tbody');

    function randomNumber() {
        return Math.floor(Math.random() * 99) + 2;
    }

    function nextNumber() {
        state.currentNumber = randomNumber();
        currentNumber.textContent = String(state.currentNumber);
    }

    function resetScore() {
        state.totalSteps = 0;
        state.totalCorrect = 0;
        renderScore();
    }

    function renderScore() {
        stepsCount.textContent = String(state.totalSteps);
        correctCount.textContent = String(state.totalCorrect);
        const ratio = state.totalSteps === 0 ? 0 : Math.round((state.totalCorrect / state.totalSteps) * 100);
        accuracy.textContent = `${ratio}%`;
    }

    function updateGameStateText() {
        if (state.currentGameId === null) {
            gameState.textContent = 'Игра не начата';
            return;
        }

        gameState.textContent = `Текущая игра: #${state.currentGameId} (${state.currentPlayerName})`;
    }

    async function requestJson(url, options = {}) {
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers,
            },
            ...options,
        });

        const payload = await response.json();
        if (!response.ok) {
            throw new Error(payload.error || 'Request failed');
        }

        return payload;
    }

    async function createGame(playerName) {
        const payload = await requestJson('/games', {
            method: 'POST',
            body: JSON.stringify({player_name: playerName}),
        });

        state.currentGameId = payload.id;
        state.currentPlayerName = playerName;
        resetScore();
        nextNumber();
        roundResult.textContent = 'Игра началась. Ответь: это простое число?';
        updateGameStateText();
        await loadGames();
        await loadGameDetails(payload.id);
    }

    async function sendStep(userAnswer) {
        if (state.currentGameId === null) {
            throw new Error('Сначала нажми Start');
        }

        if (state.currentNumber === null) {
            throw new Error('Сгенерируй число');
        }

        const payload = await requestJson(`/step/${state.currentGameId}`, {
            method: 'POST',
            body: JSON.stringify({
                number: Number(state.currentNumber),
                user_answer: userAnswer,
            }),
        });

        state.totalSteps += 1;
        if (payload.is_correct) {
            state.totalCorrect += 1;
        }
        renderScore();

        const divisorsText = payload.divisors.length > 0 ? ` | Делители: ${payload.divisors.join(', ')}` : '';
        roundResult.textContent =
            `Число ${payload.number}. Ты ответил "${payload.user_answer}". ` +
            `Правильный ответ: ${payload.is_prime ? 'yes' : 'no'}. ` +
            `${payload.is_correct ? 'Верно' : 'Неверно'}${divisorsText}`;

        nextNumber();
        await loadGameDetails(state.currentGameId);
        await loadGames();
    }

    async function loadGames() {
        const payload = await requestJson('/games');
        gamesTableBody.innerHTML = '';

        payload.games.forEach((game) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${game.id}</td>
                <td>${game.player_name}</td>
                <td>${game.created_at}</td>
                <td>${game.updated_at}</td>
                <td>${game.steps_count}</td>
                <td><button class="button button-outline" data-game-id="${game.id}" type="button">Open</button></td>
            `;
            gamesTableBody.appendChild(row);
        });
    }

    async function loadGameDetails(gameId) {
        const payload = await requestJson(`/games/${gameId}`);
        gameDetails.textContent = JSON.stringify(payload, null, 2);
    }

    document.getElementById('new-game-form').addEventListener('submit', async (event) => {
        event.preventDefault();
        const playerName = document.getElementById('player-name').value.trim();

        try {
            await createGame(playerName);
        } catch (error) {
            alert(error.message);
        }
    });

    document.getElementById('answer-yes').addEventListener('click', async () => {
        try {
            await sendStep('yes');
        } catch (error) {
            alert(error.message);
        }
    });

    document.getElementById('answer-no').addEventListener('click', async () => {
        try {
            await sendStep('no');
        } catch (error) {
            alert(error.message);
        }
    });

    document.getElementById('next-number').addEventListener('click', () => {
        nextNumber();
    });

    document.getElementById('refresh-games').addEventListener('click', async () => {
        try {
            await loadGames();
        } catch (error) {
            alert(error.message);
        }
    });

    document.getElementById('show-current-game').addEventListener('click', async () => {
        if (state.currentGameId === null) {
            alert('Текущей игры нет');
            return;
        }

        try {
            await loadGameDetails(state.currentGameId);
        } catch (error) {
            alert(error.message);
        }
    });

    gamesTableBody.addEventListener('click', async (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        const gameId = target.getAttribute('data-game-id');
        if (gameId === null) {
            return;
        }

        try {
            await loadGameDetails(Number(gameId));
        } catch (error) {
            alert(error.message);
        }
    });

    nextNumber();
    renderScore();
    updateGameStateText();
    loadGames().catch((error) => alert(error.message));
</script>
</body>
</html>
