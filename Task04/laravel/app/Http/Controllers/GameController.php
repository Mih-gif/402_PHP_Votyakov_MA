<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Progression;
use App\Models\ProgressionNumber;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB; // Важно! Импортируем фасад DB

class GameController extends Controller
{
    /**
     * Display a listing of all games.
     */
    public function index(): JsonResponse
    {
        $games = Game::with('progression')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($game) {
                $data = $game->toArray();
                if ($game->progression) {
                    $data['first_number'] = $game->progression->first_number;
                    $data['step'] = $game->progression->step;
                    $data['missing_position'] = $game->progression->missing_position;
                    $data['correct_number'] = $game->progression->correct_number;
                    $data['user_answer'] = $game->progression->user_answer;
                }
                return $data;
            });

        return response()->json($games);
    }

    /**
     * Store a newly created game.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'player_name' => 'nullable|string|max:255'
        ]);

        $playerName = $validated['player_name'] ?? 'Anonymous';

        // Generate random arithmetic progression
        $firstNumber = rand(1, 20);
        $step = rand(2, 10);
        $missingPosition = rand(0, 9);

        // Calculate all progression numbers
        $numbers = [];
        for ($i = 0; $i < 10; $i++) {
            $numbers[$i] = $firstNumber + $i * $step;
        }
        $correctNumber = $numbers[$missingPosition];

        // Begin transaction - используем DB facade
        DB::beginTransaction();

        try {
            // Create game
            $game = Game::create([
                'player_name' => $playerName,
                'is_finished' => false
            ]);

            // Create progression
            $progression = Progression::create([
                'game_id' => $game->id,
                'first_number' => $firstNumber,
                'step' => $step,
                'missing_position' => $missingPosition,
                'correct_number' => $correctNumber
            ]);

            // Save all progression numbers
            foreach ($numbers as $pos => $num) {
                ProgressionNumber::create([
                    'progression_id' => $progression->id,
                    'position' => $pos,
                    'number' => $num,
                    'is_missing' => $pos == $missingPosition
                ]);
            }

            DB::commit(); // Используем DB facade

            // Prepare display numbers (with missing number as '...')
            $displayNumbers = $numbers;
            $displayNumbers[$missingPosition] = '...';

            return response()->json([
                'id' => $game->id,
                'progression_id' => $progression->id,
                'numbers' => $displayNumbers,
                'missing_position' => $missingPosition
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack(); // Используем DB facade
            return response()->json(['error' => 'Failed to create game: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified game.
     */
    public function show(string $id): JsonResponse
    {
        $game = Game::with(['progression.numbers'])->find($id);

        if (!$game) {
            return response()->json(['error' => 'Game not found'], 404);
        }

        $progression = $game->progression;
        $numbers = $progression->numbers()->orderBy('position')->get();

        return response()->json([
            'game' => $game,
            'progression' => $progression,
            'numbers' => $numbers
        ]);
    }

    /**
     * Make a move (answer the question).
     */
    public function step(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'answer' => 'required|integer'
        ]);

        $game = Game::with('progression')->find($id);

        if (!$game) {
            return response()->json(['error' => 'Game not found'], 404);
        }

        if ($game->is_finished) {
            return response()->json(['error' => 'Game is already finished'], 400);
        }

        $progression = $game->progression;
        $userAnswer = $validated['answer'];
        $correctNumber = $progression->correct_number;

        // Check answer
        $isCorrect = ($userAnswer == $correctNumber);
        $result = $isCorrect ? 'win' : 'lose';

        // Begin transaction - используем DB facade
        DB::beginTransaction();

        try {
            // Update game
            $game->is_finished = true;
            $game->result = $result;
            $game->save();

            // Save user answer
            $progression->user_answer = $userAnswer;
            $progression->save();

            DB::commit(); // Используем DB facade

            // Get all numbers for display
            $numbersData = ProgressionNumber::where('progression_id', $progression->id)
                ->orderBy('position')
                ->get();

            $numbers = $numbersData->pluck('number')->toArray();
            
            // Prepare display numbers
            $displayNumbers = $numbers;
            if (!$isCorrect) {
                // Show correct number in case of error
                $displayNumbers[$progression->missing_position] = $correctNumber;
            }

            return response()->json([
                'game_id' => $game->id,
                'is_correct' => $isCorrect,
                'correct_number' => $correctNumber,
                'user_answer' => $userAnswer,
                'message' => $isCorrect ? 'Правильно! Молодец!' : 'Неправильно. Правильный ответ: ' . $correctNumber,
                'numbers' => $displayNumbers,
                'missing_position' => $progression->missing_position
            ]);

        } catch (\Exception $e) {
            DB::rollBack(); // Используем DB facade
            return response()->json(['error' => 'Failed to process answer: ' . $e->getMessage()], 500);
        }
    }
}