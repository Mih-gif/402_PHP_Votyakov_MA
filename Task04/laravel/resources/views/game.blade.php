<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Арифметическая прогрессия</title>
    <style>
        /* Копируем все стили из вашего исходного index.html */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        h1 {
            text-align: center;
            color: white;
            margin-bottom: 30px;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .game-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            margin-bottom: 30px;
        }
        
        .player-section {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .player-input {
            padding: 10px 20px;
            font-size: 16px;
            border: 2px solid #ddd;
            border-radius: 5px;
            margin-right: 10px;
            width: 250px;
        }
        
        .player-input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            padding: 10px 30px;
            font-size: 16px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #764ba2;
        }
        
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .progression-container {
            text-align: center;
            margin: 40px 0;
        }
        
        .progression {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        
        .number-box {
            width: 60px;
            height: 60px;
            background: #f0f0f0;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .number-box.missing {
            background: #ffd700;
            color: #333;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .answer-section {
            text-align: center;
            margin: 20px 0;
        }
        
        .answer-input {
            padding: 10px 20px;
            font-size: 18px;
            border: 2px solid #ddd;
            border-radius: 5px;
            width: 150px;
            text-align: center;
            margin-right: 10px;
        }
        
        .message {
            text-align: center;
            font-size: 18px;
            margin: 20px 0;
            padding: 15px;
            border-radius: 5px;
            display: none;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            display: block;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            display: block;
        }
        
        .history-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        .history-section h2 {
            margin-bottom: 20px;
            color: #333;
        }
        
        .games-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .game-item {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }
        
        .game-item.win {
            border-left-color: #28a745;
        }
        
        .game-item.lose {
            border-left-color: #dc3545;
        }
        
        .game-item .date {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .game-item .player {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .game-item .result {
            font-size: 14px;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .error-message {
            color: #dc3545;
            text-align: center;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔢 Арифметическая прогрессия</h1>
        
        <div class="game-container">
            <div class="player-section">
                <input type="text" id="playerName" class="player-input" placeholder="Введите ваше имя" value="Игрок">
                <button id="newGameBtn" class="btn">Новая игра</button>
            </div>
            
            <div id="gameArea" style="display: none;">
                <div class="progression-container">
                    <div class="progression" id="progression"></div>
                </div>
                
                <div class="answer-section">
                    <input type="number" id="answerInput" class="answer-input" placeholder="Ваш ответ">
                    <button id="submitAnswerBtn" class="btn">Ответить</button>
                </div>
                
                <div id="message" class="message"></div>
            </div>
        </div>
        
        <div class="history-section">
            <h2>📊 История игр</h2>
            <div id="gamesList" class="games-list">
                <div class="loading">Загрузка...</div>
            </div>
        </div>
    </div>

    <script>
        let currentGameId = null;
        let currentMissingPosition = null;
        
        // Загрузка истории игр при загрузке страницы
        document.addEventListener('DOMContentLoaded', loadGamesHistory);
        
        // Обработчики событий
        document.getElementById('newGameBtn').addEventListener('click', startNewGame);
        document.getElementById('submitAnswerBtn').addEventListener('click', submitAnswer);
        document.getElementById('answerInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                submitAnswer();
            }
        });
        
        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        }
        
        async function loadGamesHistory() {
            try {
                const response = await fetch('/api/games');
                if (!response.ok) throw new Error('Ошибка загрузки истории');
                
                const games = await response.json();
                displayGamesHistory(games);
            } catch (error) {
                document.getElementById('gamesList').innerHTML = 
                    '<div class="error-message">Ошибка загрузки истории</div>';
            }
        }
        
        function displayGamesHistory(games) {
            const container = document.getElementById('gamesList');
            
            if (games.length === 0) {
                container.innerHTML = '<div class="loading">История игр пуста</div>';
                return;
            }
            
            container.innerHTML = games.map(game => {
                const date = new Date(game.created_at).toLocaleString('ru-RU');
                const resultText = game.result === 'win' ? 'Победа' : 
                                  game.result === 'lose' ? 'Поражение' : 'Не завершена';
                
                return `
                    <div class="game-item ${game.result || 'pending'}">
                        <div class="date">${date}</div>
                        <div class="player">Игрок: ${game.player_name}</div>
                        <div class="result">
                            Результат: ${resultText}<br>
                            Прогрессия: ${game.first_number}, шаг ${game.step}<br>
                            Пропущено число: ${game.correct_number}<br>
                            ${game.user_answer !== null ? 'Ответ: ' + game.user_answer : ''}
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        async function startNewGame() {
            const playerName = document.getElementById('playerName').value.trim() || 'Игрок';
            
            try {
                const response = await fetch('/api/games', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({ player_name: playerName })
                });
                
                if (!response.ok) throw new Error('Ошибка создания игры');
                
                const gameData = await response.json();
                currentGameId = gameData.id;
                currentMissingPosition = gameData.missing_position;
                
                displayProgression(gameData.numbers);
                document.getElementById('gameArea').style.display = 'block';
                document.getElementById('message').style.display = 'none';
                document.getElementById('answerInput').value = '';
                document.getElementById('answerInput').focus();
                
                // Обновляем историю
                loadGamesHistory();
            } catch (error) {
                alert('Ошибка: ' + error.message);
            }
        }
        
        function displayProgression(numbers) {
            const container = document.getElementById('progression');
            container.innerHTML = numbers.map((num, index) => {
                const isMissing = num === '...';
                return `<div class="number-box ${isMissing ? 'missing' : ''}">${num}</div>`;
            }).join('');
        }
        
        async function submitAnswer() {
            if (!currentGameId) {
                alert('Сначала начните новую игру');
                return;
            }
            
            const answer = document.getElementById('answerInput').value;
            if (answer === '') {
                alert('Введите ваш ответ');
                return;
            }
            
            try {
                const response = await fetch(`/api/step/${currentGameId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({ answer: parseInt(answer) })
                });
                
                if (!response.ok) throw new Error('Ошибка отправки ответа');
                
                const result = await response.json();
                
                // Показываем результат
                const messageElement = document.getElementById('message');
                messageElement.textContent = result.message;
                messageElement.className = result.is_correct ? 'message success' : 'message error';
                
                // Обновляем отображение прогрессии
                displayProgression(result.numbers);
                
                // Блокируем возможность ответа
                document.getElementById('submitAnswerBtn').disabled = true;
                document.getElementById('answerInput').disabled = true;
                
                // Обновляем историю
                loadGamesHistory();
                
                // Через 3 секунды разблокируем для новой игры
                setTimeout(() => {
                    document.getElementById('submitAnswerBtn').disabled = false;
                    document.getElementById('answerInput').disabled = false;
                    document.getElementById('answerInput').value = '';
                    document.getElementById('message').style.display = 'none';
                    document.getElementById('gameArea').style.display = 'none';
                    currentGameId = null;
                }, 3000);
            } catch (error) {
                alert('Ошибка: ' + error.message);
            }
        }
    </script>
</body>
</html>