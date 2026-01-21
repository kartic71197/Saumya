<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\TrainingChapter;
use App\Models\TrainingUserProgress;
use App\Models\TrainingVideo;
use App\Models\TrainingNote;
use App\Models\TrainingQuiz;
use App\Models\TrainingQuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class TrainingController extends Controller
{
    public function index()
    {
        // Only allow access if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admins redirected to admin view, others see view-only
        if (auth()->user()->role_id <= 2) {
            return redirect()->route('training.admin');
        }

        // For view-only users
        $chapters = TrainingChapter::with(['quizzes' => function($q) {
            $q->orderBy('order')->with(['questions' => function($qq) { $qq->orderBy('order'); }]);
        }])->orderBy('order')->get();
        $notes = TrainingNote::with('chapter')->orderBy('order')->get();
        return view('training.admin.index', compact('chapters', 'notes'));
    }

    public function admin()
    {
        // Only allow access if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        // Only allow admin users
        if (auth()->user()->role_id > 2) {
            return redirect()->route('training.index')->with('error', 'Access denied. Admin privileges required.');
        }

        $chapters = TrainingChapter::with(['quizzes' => function($q) {
            $q->orderBy('order')->with(['questions' => function($qq) { $qq->orderBy('order'); }]);
        }])->orderBy('order')->get();
        $notes = TrainingNote::with('chapter')->orderBy('order')->get();
        return view('training.admin.index', compact('chapters', 'notes'));
    }

    public function downloadNote($noteId)
    {
        $chartId = Session::get('training_chart_id');

        if (!$chartId) {
            return redirect()->route('training.index')->with('error', 'Please enter your chart ID first.');
        }

        $patient = Patient::where('chartnumber', $chartId)->where('is_active', true)->first();

        if (!$patient) {
            Session::forget('training_chart_id');
            return redirect()->route('training.index')->with('error', 'Invalid chart ID.');
        }

        $note = TrainingNote::findOrFail($noteId);

        // Check if file exists in S3
        if (!Storage::disk('s3')->exists($note->file_url)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        // Generate temporary download URL
        $downloadUrl = Storage::disk('s3')->temporaryUrl(
            $note->file_url,
            now()->addMinutes(5),
            ['ResponseContentDisposition' => 'attachment; filename="' . $note->file_name . '"']
        );

        return redirect($downloadUrl);
    }

    public function logout()
    {
        Session::forget('training_chart_id');
        return redirect()->route('training.index')->with('success', 'You have been logged out of the training portal.');
    }

    public function storeChapter(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            // Check if user is admin
            if (!auth()->check() || auth()->user()->role_id > 2) {
                return response()->json(['error' => 'Access denied. Admin privileges required.'], 403);
            }

            $chapter = TrainingChapter::create([
                'title' => $request->title,
                'description' => $request->description,
                'order' => TrainingChapter::max('order') + 1,
                'is_active' => true,
                'created_by' => auth()->id(),
                'organization_id' => auth()->user()->organization_id,
            ]);

            \Log::info('Chapter created', [
                'chapter_id' => $chapter->id,
                'title' => $chapter->title,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Chapter created successfully!',
                'chapter' => $chapter
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creating chapter', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to create chapter: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateChapter(Request $request, $id)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            // Check if user is admin
            if (!auth()->check() || auth()->user()->role_id > 2) {
                return response()->json(['error' => 'Access denied. Admin privileges required.'], 403);
            }

            $chapter = TrainingChapter::findOrFail($id);
            $chapter->update([
                'title' => $request->title,
                'description' => $request->description,
            ]);

            \Log::info('Chapter updated', [
                'chapter_id' => $chapter->id,
                'title' => $chapter->title,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Chapter updated successfully!',
                'chapter' => $chapter
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating chapter', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to update chapter: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteChapter($id)
    {
        try {
            // Check if user is admin
            if (!auth()->check() || auth()->user()->role_id > 2) {
                return response()->json(['error' => 'Access denied. Admin privileges required.'], 403);
            }

            $chapter = TrainingChapter::findOrFail($id);
            $chapterTitle = $chapter->title;
            $chapter->delete();

            \Log::info('Chapter deleted', [
                'chapter_id' => $id,
                'title' => $chapterTitle,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Chapter deleted successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting chapter', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to delete chapter: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeNote(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'chapter_id' => 'required|exists:training_chapters,id',
                'note_file' => 'required|file|mimes:pdf,doc,docx,txt,rtf|max:10240', // 10MB max
            ]);

            // Check if user is admin
            if (!auth()->check() || auth()->user()->role_id > 2) {
                return response()->json(['error' => 'Access denied. Admin privileges required.'], 403);
            }

            $file = $request->file('note_file');
            \Log::info('Starting note upload (storage disk)', [
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getClientMimeType(),
                'user_id' => auth()->id()
            ]);

            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('training_notes', $filename, 'public'); // Use 'public' disk

            \Log::info('Storage disk upload result', [
                'path' => $path,
                'filename' => $filename
            ]);

            if (!$path || !\Storage::disk('public')->exists($path)) {
                \Log::error('Storage disk upload failed', [
                    'filename' => $filename,
                    'path' => $path
                ]);
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to upload file to storage disk. Please try again.'
                ], 500);
            }

            $note = TrainingNote::create([
                'title' => $request->title,
                'description' => $request->description,
                'chapter_id' => $request->chapter_id,
                'file_url' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'file_type' => $file->getClientMimeType(),
                'order' => TrainingNote::max('order') + 1,
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);

            \Log::info('Note created successfully (storage disk)', [
                'note_id' => $note->id,
                'title' => $note->title,
                'file_url' => $path,
                'file_size' => $file->getSize(),
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Note created successfully!',
                'note' => $note
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creating note (storage disk)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to create note: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateNote(Request $request, $id)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'chapter_id' => 'required|exists:training_chapters,id',
                'note_file' => 'nullable|file|mimes:pdf,doc,docx,txt,rtf|max:10240', // 10MB max
            ]);

            // Check if user is admin
            if (!auth()->check() || auth()->user()->role_id > 2) {
                return response()->json(['error' => 'Access denied. Admin privileges required.'], 403);
            }

            $note = TrainingNote::findOrFail($id);
            $updateData = [
                'title' => $request->title,
                'description' => $request->description,
                'chapter_id' => $request->chapter_id,
            ];

            // Handle file upload if provided
            if ($request->hasFile('note_file')) {
                $file = $request->file('note_file');
                $filename = uniqid() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('training_notes', $filename, 'storage');

                // Delete old file
                if ($note->file_url && \Storage::disk('storage')->exists($note->file_url)) {
                    \Storage::disk('storage')->delete($note->file_url);
                }

                $updateData['file_url'] = $path;
                $updateData['file_name'] = $file->getClientOriginalName();
                $updateData['file_size'] = $file->getSize();
                $updateData['file_type'] = $file->getClientMimeType();
            }

            $note->update($updateData);

            \Log::info('Note updated (storage disk)', [
                'note_id' => $note->id,
                'title' => $note->title,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Note updated successfully!',
                'note' => $note
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating note (storage disk)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to update note: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteNote($id)
    {
        try {
            // Check if user is admin
            if (!auth()->check() || auth()->user()->role_id > 2) {
                return response()->json(['error' => 'Access denied. Admin privileges required.'], 403);
            }

            $note = TrainingNote::findOrFail($id);
            $noteTitle = $note->title;
            $fileUrl = $note->file_url;

            // Delete file from storage disk
            if ($fileUrl && \Storage::disk('storage')->exists($fileUrl)) {
                \Storage::disk('storage')->delete($fileUrl);
            }

            $note->delete();

            \Log::info('Note deleted (storage disk)', [
                'note_id' => $id,
                'title' => $noteTitle,
                'file_url' => $fileUrl,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Note deleted successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting note (storage disk)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to delete note: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeQuiz(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'chapter_id' => 'required|exists:training_chapters,id',
            ]);

            // Check if user is admin
            if (!auth()->check() || auth()->user()->role_id > 2) {
                return response()->json(['error' => 'Access denied. Admin privileges required.'], 403);
            }

            $quiz = TrainingQuiz::create([
                'title' => $request->title,
                'description' => $request->description,
                'chapter_id' => $request->chapter_id,
                'order' => TrainingQuiz::where('chapter_id', $request->chapter_id)->max('order') + 1,
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);

            \Log::info('Quiz created', [
                'quiz_id' => $quiz->id,
                'title' => $quiz->title,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quiz created successfully!',
                'quiz' => $quiz
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creating quiz', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to create quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeQuestion(Request $request)
    {
        try {
            $request->validate([
                'quiz_id' => 'required|exists:training_quizzes,id',
                'question_text' => 'required|string',
                'choice_1' => 'required|string',
                'choice_2' => 'required|string',
                'choice_3' => 'required|string',
                'choice_4' => 'required|string',
                'correct_choice' => 'required|in:1,2,3,4',
            ]);

            // Check if user is admin
            if (!auth()->check() || auth()->user()->role_id > 2) {
                return response()->json(['error' => 'Access denied. Admin privileges required.'], 403);
            }

            $options = [
                $request->input('choice_1'),
                $request->input('choice_2'),
                $request->input('choice_3'),
                $request->input('choice_4'),
            ];
            $correctIndex = (int)$request->input('correct_choice') - 1;
            $correctAnswer = $options[$correctIndex];

            $question = TrainingQuizQuestion::create([
                'quiz_id' => $request->quiz_id,
                'question' => $request->question_text,
                'question_type' => 'multiple_choice',
                'options' => $options,
                'correct_answer' => $correctAnswer,
                'order' => TrainingQuizQuestion::where('quiz_id', $request->quiz_id)->max('order') + 1,
                'is_active' => true,
            ]);

            \Log::info('Quiz question created', [
                'question_id' => $question->id,
                'quiz_id' => $question->quiz_id,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Question created successfully!',
                'question' => $question
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creating quiz question', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to create question: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteQuiz($id)
    {
        try {
            logger('deleting quiz');
            if (!auth()->check() || auth()->user()->role_id > 2) {
                return response()->json(['error' => 'Access denied. Admin privileges required.'], 403);
            }
            $quiz = TrainingQuiz::with('questions')->findOrFail($id);
            // Delete all questions
            foreach ($quiz->questions as $question) {
                $question->delete();
            }
            $quiz->delete();
            \Log::info('Quiz deleted', [
                'quiz_id' => $id,
                'deleted_by' => auth()->id()
            ]);
            return response()->json(['success' => true, 'message' => 'Quiz and its questions deleted successfully!']);
        } catch (\Exception $e) {
            \Log::error('Error deleting quiz', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'error' => 'Failed to delete quiz: ' . $e->getMessage()], 500);
        }
    }

    public function deleteQuestion($id)
    {
        try {
            if (!auth()->check() || auth()->user()->role_id > 2) {
                return response()->json(['error' => 'Access denied. Admin privileges required.'], 403);
            }
            $question = TrainingQuizQuestion::findOrFail($id);
            $question->delete();
            \Log::info('Quiz question deleted', [
                'question_id' => $id,
                'deleted_by' => auth()->id()
            ]);
            return response()->json(['success' => true, 'message' => 'Question deleted successfully!']);
        } catch (\Exception $e) {
            \Log::error('Error deleting quiz question', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'error' => 'Failed to delete question: ' . $e->getMessage()], 500);
        }
    }

    public function attemptQuiz($id)
    {
        // Only allow access if user is authenticated and not admin
        if (!auth()->check() || auth()->user()->role_id <= 2) {
            return redirect()->route('training.index')->with('error', 'Access denied. Only regular users can attempt quizzes.');
        }
        $quiz = TrainingQuiz::with(['questions' => function($q) { $q->orderBy('order'); }])->findOrFail($id);
        if ($quiz->questions->count() === 0) {
            return redirect()->route('training.index')->with('error', 'This quiz has no questions yet.');
        }
        $progress = \App\Models\TrainingUserProgress::where('user_id', auth()->id())
            ->where('quiz_id', $quiz->id)
            ->first();
        $showQuiz = request()->query('retake') === '1' ? true : false;
        return view('training.quiz.attempt', compact('quiz', 'progress', 'showQuiz'));
    }

    public function submitQuiz(Request $request, $id)
    {
        // Only allow access if user is authenticated and not admin
        if (!auth()->check() || auth()->user()->role_id <= 2) {
            return redirect()->route('training.index')->with('error', 'Access denied. Only regular users can submit quizzes.');
        }
        $quiz = TrainingQuiz::with(['questions' => function($q) { $q->orderBy('order'); }])->findOrFail($id);
        $questions = $quiz->questions;
        $answers = $request->input('answers', []);

        $total = $questions->count();
        $attempted = 0;
        $correct = 0;
        $incorrect = 0;
        $details = [];

        foreach ($questions as $question) {
            $qid = $question->id;
            $userAnswer = isset($answers[$qid]) ? $answers[$qid] : null;
            if ($userAnswer !== null) {
                $attempted++;
                if (strtolower(trim($userAnswer)) === strtolower(trim($question->correct_answer))) {
                    $correct++;
                    $details[$qid] = ['status' => 'correct', 'user' => $userAnswer, 'correct' => $question->correct_answer];
                } else {
                    $incorrect++;
                    $details[$qid] = ['status' => 'incorrect', 'user' => $userAnswer, 'correct' => $question->correct_answer];
                }
            } else {
                $details[$qid] = ['status' => 'not_attempted', 'user' => null, 'correct' => $question->correct_answer];
            }
        }

        $summary = [
            'total' => $total,
            'attempted' => $attempted,
            'correct' => $correct,
            'incorrect' => $incorrect,
            'score' => $correct . ' / ' . $total,
            'details' => $details,
        ];

        // Save or update user progress
    TrainingUserProgress::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'quiz_id' => $quiz->id,
            ],
            [
                'status' => 'completed',
                'score' => $correct,
                'completed_at' => now(),
                'quiz_answers' => $answers,
            ]
        );

        return view('training.quiz.result', compact('quiz', 'summary', 'answers'));
    }

    public function scoreCard()
    {
        $user = auth()->user();
        $organizationId = $user->organization_id;
        // Get all quizzes in user's organization
        $quizIds = TrainingQuiz::whereHas('chapter', function($q) use ($organizationId) {
            $q->where('organization_id', $organizationId);
        })->pluck('id');
        logger($quizIds);
        // Get user progress for these quizzes
        $progress = TrainingUserProgress::where('user_id', $user->id)
            ->whereIn('quiz_id', $quizIds)
            ->get();
        $quizzes = TrainingQuiz::whereIn('id', $quizIds)->with('chapter')->get();
        logger($quizzes);
        logger($progress);
        // Overall stats
        $totalAttempted = $progress->count();
        $totalCorrect = $progress->sum('score');
        $totalQuestions = $quizzes->sum(function($quiz) { return $quiz->questions->count(); });
        $averageScore = $totalAttempted > 0 ? round($progress->avg('score'), 2) : 0;
        return view('training.quiz.score_card', compact('progress', 'quizzes', 'totalAttempted', 'totalCorrect', 'totalQuestions', 'averageScore'))->render();
    }

    public function scoreBoard(Request $request)
    {
        $user = auth()->user();
        $organizationId = $user->organization_id;
        $selectedChapter = $request->query('chapter', 'overall');
        $users = \App\Models\User::where('organization_id', $organizationId)->orderBy('name')->get();
        $chapters = TrainingChapter::where('organization_id', $organizationId)
            ->with(['quizzes' => function($q) { $q->orderBy('order'); }])
            ->orderBy('order')->get();
        $quizQuery = TrainingQuiz::whereHas('chapter', function($q) use ($organizationId) {
            $q->where('organization_id', $organizationId);
        });
        if ($selectedChapter !== 'overall') {
            $chapterId = (int)str_replace('chapter-', '', $selectedChapter);
            $quizQuery->where('chapter_id', $chapterId);
        }
        $quizIds = $quizQuery->pluck('id');
        $quizzes = TrainingQuiz::whereIn('id', $quizIds)->with('chapter')->get();
        $progress = TrainingUserProgress::whereIn('user_id', $users->pluck('id'))
            ->whereIn('quiz_id', $quizIds)
            ->get();
        return view('training.quiz.score_board', compact('users', 'quizzes', 'progress', 'chapters', 'selectedChapter'))->render();
    }
}
