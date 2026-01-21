<div>
    <div class="mb-4">
        <h4 class="text-lg font-semibold text-indigo-700 mb-2">Overall Score</h4>
        <div class="flex flex-wrap gap-6 mb-2">
            <div class="text-gray-700">Total Quizzes Attempted: <span class="font-bold">{{ $totalAttempted }}</span></div>
            <div class="text-gray-700">Total Correct Answers: <span class="font-bold">{{ $totalCorrect }}</span></div>
            <div class="text-gray-700">Average Score: <span class="font-bold">{{ $averageScore }}</span></div>
        </div>
    </div>
    <div>
        <h4 class="text-lg font-semibold text-indigo-700 mb-2">Quiz-wise Score</h4>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left border">
                <thead>
                    <tr class="bg-indigo-100">
                        <th class="px-3 py-2">Quiz</th>
                        <th class="px-3 py-2">Chapter</th>
                        <th class="px-3 py-2">Your Score</th>
                        <th class="px-3 py-2">Total Questions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quizzes as $quiz)
                        @php
                            $userProgress = $progress->where('quiz_id', $quiz->id)->first();
                        @endphp
                        <tr>
                            <td class="px-3 py-2">{{ $quiz->title }}</td>
                            <td class="px-3 py-2">{{ $quiz->chapter ? $quiz->chapter->title : '-' }}</td>
                            <td class="px-3 py-2">
                                @if($userProgress)
                                    <span class="font-bold text-purple-700">{{ $userProgress->score }} / {{ $quiz->questions->count() }}</span>
                                @else
                                    <span class="text-gray-400">Not Attempted</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">{{ $quiz->questions->count() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div> 