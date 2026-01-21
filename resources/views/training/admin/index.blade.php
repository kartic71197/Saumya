@php
    $canAdmin = auth()->check() && auth()->user()->role_id <= 2;
    $canView = auth()->check() || session('training_chart_id');
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Training') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">Training Content</h3>
                        @if($canAdmin)
                            <div class="space-x-3">
                                <button onclick="openAddChapterModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Add Chapter
                                </button>
                                <button onclick="openAddNoteModal()" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Add Note
                                </button>
                                <button onclick="openAddQuizModal()" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h8m-4-4v8" />
                                    </svg>
                                    Add Quiz
                                </button>
                            </div>
                        @endif
                        @if(auth()->check() && auth()->user()->role_id <= 2)
                            <button onclick="openScoreBoardModal()" class="inline-flex items-center px-4 py-2 bg-pink-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-pink-700 focus:bg-pink-700 active:bg-pink-900 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                                </svg>
                                View Score Board
                            </button>
                        @endif
                        @if(!$canAdmin)
                            <button onclick="openScoreCardModal()" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                                </svg>
                                View Score Card
                            </button>
                        @endif
                    </div>
                    <div class="mb-8">
                        <div class="bg-gray-50 rounded-lg p-4">
                            @if($chapters->count() > 0)
                                <div class="space-y-3">
                                    @foreach($chapters as $chapter)
                                        <div class="flex justify-between items-center p-3 bg-white rounded border">
                                            <div>
                                                <h5 class="font-medium text-gray-900">{{ $chapter->title }}</h5>
                                                <p class="text-sm text-gray-600">{{ $chapter->description }}</p>
                                            </div>
                                            <div class="flex space-x-2">
                                                @if($canAdmin)
                                                    <button onclick="editChapter({{ $chapter->id }}, '{{ $chapter->title }}', '{{ $chapter->description }}')" class="text-blue-600 hover:text-blue-800">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="ml-6 mt-2">
                                            <h6 class="text-xs text-gray-500 font-semibold mb-1">Notes</h6>
                                            @php $chapterNotes = $notes->where('chapter_id', $chapter->id); @endphp
                                            @if($chapterNotes->count() > 0)
                                                <div class="space-y-2">
                                                    @foreach($chapterNotes as $note)
                                                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded border">
                                                            <div>
                                                                <span class="font-medium text-gray-800">{{ $note->title }}</span>
                                                                <span class="text-xs text-gray-500 ml-2">({{ $note->file_name }})</span>
                                                                @if($note->description)
                                                                    <div class="text-xs text-gray-500 mt-1">{{ $note->description }}</div>
                                                                @endif
                                                            </div>
                                                            <div class="flex space-x-2">
                                                                <a href="{{ asset('storage/' . $note->file_url) }}" target="_blank" rel="noopener" class="text-green-600 hover:text-green-800">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                                    </svg>
                                                                </a>
                                                                @if($canAdmin)
                                                                    <button onclick="deleteNote({{ $note->id }})" class="text-red-600 hover:text-red-800">
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                        </svg>
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-xs text-gray-400 italic">No notes for this chapter.</div>
                                            @endif
                                        </div>
                                        <div class="ml-6 mt-4">
                                            <h6 class="text-xs text-purple-500 font-semibold mb-1">Quizzes</h6>
                                            @if($chapter->quizzes->count() > 0)
                                                <div class="space-y-2">
                                                    @foreach($chapter->quizzes as $quiz)
                                                        <div class="flex flex-col bg-purple-50 rounded border border-purple-200 mb-2">
                                                            <div class="flex justify-between items-center p-2">
                                                                <div>
                                                                    <span class="font-medium text-purple-800">{{ $quiz->title }}</span>
                                                                    @if($quiz->description)
                                                                        <div class="text-xs text-purple-600 mt-1">{{ $quiz->description }}</div>
                                                                    @endif
                                                                </div>
                                                                <div class="flex flex-col space-y-1 items-end">
                                                                    @if($canAdmin)
                                                                        <div class="flex space-x-2">
                                                                            <button onclick="openAddQuestionModal({{ $quiz->id }})" class="text-purple-600 hover:text-purple-800 text-xs font-semibold">Add Question</button>
                                                                            <button onclick="deleteQuiz({{ $quiz->id }})" class="text-red-600 hover:text-red-800 text-xs font-semibold">Delete Quiz</button>
                                                                        </div>
                                                                    @else
                                                                        @php
                                                                            $progress = \App\Models\TrainingUserProgress::where('user_id', auth()->id())->where('quiz_id', $quiz->id)->first();
                                                                        @endphp
                                                                        @if($progress)
                                                                            <div class="text-xs text-gray-700 mb-1">Last Score: <span class="font-bold text-purple-700">{{ $progress->score }} / {{ $quiz->questions->count() }}</span></div>
                                                                        @endif
                                                                        <a href="{{ route('training.attempt-quiz', $quiz->id) }}" class="inline-flex items-center px-3 py-1 bg-purple-600 text-white rounded text-xs font-semibold hover:bg-purple-700">Attempt Quizz</a>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            @if($canAdmin)
                                                                @if($quiz->questions->count() > 0)
                                                                    <div class="pl-4 pb-2">
                                                                        <h6 class="text-xs text-purple-700 font-semibold mb-1">Questions</h6>
                                                                        <ul class="space-y-2">
                                                                            @foreach($quiz->questions as $question)
                                                                                <li class="bg-white rounded p-2 border flex justify-between items-start">
                                                                                    <div>
                                                                                        <div class="font-medium text-gray-800">Q: {{ $question->question }}</div>
                                                                                        <ul class="ml-4 mt-1">
                                                                                            @foreach($question->options as $idx => $choice)
                                                                                                <li class="text-sm {{ $choice === $question->correct_answer ? 'text-green-700 font-semibold' : 'text-gray-700' }}">
                                                                                                    {{ chr(65 + $idx) }}. {{ $choice }}
                                                                                                    @if($choice === $question->correct_answer)
                                                                                                        <span class="ml-2 text-green-500">(Correct)</span>
                                                                                                    @endif
                                                                                                </li>
                                                                                            @endforeach
                                                                                        </ul>
                                                                                    </div>
                                                                                    <button onclick="deleteQuestion({{ $question->id }})" class="text-red-600 hover:text-red-800 text-xs font-semibold ml-4">Delete</button>
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                    </div>
                                                                @else
                                                                    <div class="pl-4 pb-2 text-xs text-purple-400 italic">No questions for this quiz.</div>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-xs text-purple-400 italic">No quizzes for this chapter.</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 text-center py-4">No chapters found. Create your first chapter!</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Chapter Modal --}}
    @if($canAdmin)
        <div id="addChapterModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Chapter</h3>
                    <form id="addChapterForm">
                        @csrf
                        <div class="mb-4">
                            <label for="chapter_title" class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" id="chapter_title" name="title" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="chapter_description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="chapter_description" name="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeAddChapterModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Add Chapter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Add Note Modal --}}
    @if($canAdmin)
        <div id="addNoteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Note</h3>
                    <form id="addNoteForm">
                        @csrf
                        <div class="mb-4">
                            <label for="note_title" class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" id="note_title" name="title" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="note_description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="note_description" name="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="note_chapter_id" class="block text-sm font-medium text-gray-700">Chapter</label>
                            <select id="note_chapter_id" name="chapter_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Select a chapter</option>
                                @foreach($chapters as $chapter)
                                    <option value="{{ $chapter->id }}">{{ $chapter->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="note_file" class="block text-sm font-medium text-gray-700">Note File (PDF)</label>
                            <input type="file" id="note_file" name="note_file" accept=".pdf" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeAddNoteModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Add Note</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Edit Chapter Modal --}}
    @if($canAdmin)
        <div id="editChapterModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Chapter</h3>
                    <form id="editChapterForm">
                        @csrf
                        <input type="hidden" id="edit_chapter_id" name="chapter_id">
                        <div class="mb-4">
                            <label for="edit_chapter_title" class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" id="edit_chapter_title" name="title" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="edit_chapter_description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="edit_chapter_description" name="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeEditChapterModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Update Chapter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Add Quiz Modal --}}
    @if($canAdmin)
        <div id="addQuizModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Quiz</h3>
                    <form id="addQuizForm">
                        @csrf
                        <div class="mb-4">
                            <label for="quiz_title" class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" id="quiz_title" name="title" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="quiz_description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="quiz_description" name="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="quiz_chapter_id" class="block text-sm font-medium text-gray-700">Chapter</label>
                            <select id="quiz_chapter_id" name="chapter_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Select a chapter</option>
                                @foreach($chapters as $chapter)
                                    <option value="{{ $chapter->id }}">{{ $chapter->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeAddQuizModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">Add Quiz</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Add Question Modal --}}
    @if($canAdmin)
        <div id="addQuestionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Question</h3>
                    <form id="addQuestionForm">
                        @csrf
                        <input type="hidden" id="question_quiz_id" name="quiz_id">
                        <div class="mb-4">
                            <label for="question_text" class="block text-sm font-medium text-gray-700">Question</label>
                            <textarea id="question_text" name="question_text" rows="2" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Choices</label>
                            @for($i = 1; $i <= 4; $i++)
                                <div class="flex items-center mb-2">
                                    <input type="radio" name="correct_choice" value="{{ $i }}" class="mr-2" required>
                                    <input type="text" name="choice_{{ $i }}" placeholder="Choice {{ $i }}" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                            @endfor
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeAddQuestionModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">Add Question</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Score Card Modal --}}
    @if(!$canAdmin)
        <div id="scoreCardModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Your Score Card</h3>
                        <button onclick="closeScoreCardModal()" class="text-gray-500 hover:text-gray-700">&times;</button>
                    </div>
                    <div id="scoreCardContent">
                        <!-- Score card content will be loaded here -->
                        <div class="text-center text-gray-500">Loading...</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Score Board Modal --}}
    @if(auth()->check() && auth()->user()->role_id <= 2)
        <div id="scoreBoardModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-pink-700">Organization Score Board</h3>
                        <button onclick="closeScoreBoardModal()" class="text-gray-500 hover:text-gray-700">&times;</button>
                    </div>
                    <div id="scoreBoardContent">
                        <div class="text-center text-gray-500">Loading...</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        // Chapter Modal Functions
        function openAddChapterModal() {
            document.getElementById('addChapterModal').classList.remove('hidden');
        }

        function closeAddChapterModal() {
            document.getElementById('addChapterModal').classList.add('hidden');
            document.getElementById('addChapterForm').reset();
        }

        function editChapter(id, title, description) {
            document.getElementById('editChapterModal').classList.remove('hidden');
            document.getElementById('edit_chapter_id').value = id;
            document.getElementById('edit_chapter_title').value = title;
            document.getElementById('edit_chapter_description').value = description;
        }

        function closeEditChapterModal() {
            document.getElementById('editChapterModal').classList.add('hidden');
            document.getElementById('editChapterForm').reset();
        }

        function deleteChapter(id) {
            if (confirm('Are you sure you want to delete this chapter?')) {
                fetch(`/training/chapters/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error deleting chapter: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error deleting chapter');
                    });
            }
        }

        // Note Modal Functions
        function openAddNoteModal() {
            document.getElementById('addNoteModal').classList.remove('hidden');
        }

        function closeAddNoteModal() {
            document.getElementById('addNoteModal').classList.add('hidden');
            document.getElementById('addNoteForm').reset();
        }

        function deleteNote(id) {
            if (confirm('Are you sure you want to delete this note?')) {
                fetch(`/training/notes/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error deleting note: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error deleting note');
                    });
            }
        }

        // Form Submissions
        document.getElementById('addChapterForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('{{ route("training.store-chapter") }}', {
                method: 'POST',
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeAddChapterModal();
                        location.reload();
                    } else {
                        alert('Error adding chapter: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error adding chapter');
                });
        });

        document.getElementById('addNoteForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('{{ route("training.store-note") }}', {
                method: 'POST',
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeAddNoteModal();
                        location.reload();
                    } else {
                        alert('Error adding note: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error adding note');
                });
        });

        document.getElementById('editChapterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('edit_chapter_id').value;
            const formData = new FormData(this);
            formData.append('_method', 'PUT');

            fetch(`/training/chapters/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeEditChapterModal();
                    location.reload();
                } else {
                    alert('Error updating chapter: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating chapter');
            });
        });

        function openAddQuizModal() {
            document.getElementById('addQuizModal').classList.remove('hidden');
        }
        function closeAddQuizModal() {
            document.getElementById('addQuizModal').classList.add('hidden');
            document.getElementById('addQuizForm').reset();
        }

        document.getElementById('addQuizForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('{{ route('training.store-quiz') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeAddQuizModal();
                    location.reload();
                } else {
                    alert('Error adding quiz: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding quiz');
            });
        });

        function openAddQuestionModal(quizId) {
            document.getElementById('addQuestionModal').classList.remove('hidden');
            document.getElementById('question_quiz_id').value = quizId;
        }

        function closeAddQuestionModal() {
            document.getElementById('addQuestionModal').classList.add('hidden');
            document.getElementById('addQuestionForm').reset();
        }

        document.getElementById('addQuestionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('{{ route('training.store-question') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeAddQuestionModal();
                    location.reload();
                } else {
                    alert('Error adding question: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding question');
            });
        });

        function deleteQuiz(id) {
            if (confirm('Are you sure you want to delete this quiz? All questions will be deleted.')) {
                fetch(`{{ url('/training/quizzes') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting quiz: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting quiz');
                });
            }
        }

        function deleteQuestion(id) {
            if (confirm('Are you sure you want to delete this question?')) {
                fetch(`{{ url('/training/questions') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting question: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting question');
                });
            }
        }

        function openScoreCardModal() {
            document.getElementById('scoreCardModal').classList.remove('hidden');
            loadScoreCard();
        }
        function closeScoreCardModal() {
            document.getElementById('scoreCardModal').classList.add('hidden');
        }
        function loadScoreCard() {
            fetch('/training/score-card')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('scoreCardContent').innerHTML = html;
                })
                .catch(() => {
                    document.getElementById('scoreCardContent').innerHTML = '<div class="text-red-600">Failed to load score card.</div>';
                });
        }

        function openScoreBoardModal() {
            document.getElementById('scoreBoardModal').classList.remove('hidden');
            loadScoreBoard('overall');
        }
        function closeScoreBoardModal() {
            document.getElementById('scoreBoardModal').classList.add('hidden');
        }
        function loadScoreBoard(chapter = 'overall') {
            let url = "{{ route('training.score-board') }}";
            if (chapter && chapter !== 'overall') {
                url += '?chapter=' + encodeURIComponent(chapter);
            }
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('scoreBoardContent').innerHTML = html;
                    // Attach event listener to chapterFilter in the loaded content
                    const chapterFilter = document.getElementById('chapterFilter');
                    if (chapterFilter) {
                        chapterFilter.value = chapter;
                        chapterFilter.addEventListener('change', function() {
                            loadScoreBoard(this.value);
                        });
                    }
                })
                .catch(() => {
                    document.getElementById('scoreBoardContent').innerHTML = '<div class="text-red-600">Failed to load score board.</div>';
                });
        }
    </script>
</x-app-layout>