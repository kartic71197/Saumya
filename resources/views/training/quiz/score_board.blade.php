<div>
    <div class="mb-4 flex items-center gap-4">
        <label for="chapterFilter" class="font-medium text-gray-700">Show:</label>
        <select id="chapterFilter" class="border rounded px-2 py-1">
            <option value="overall" {{ $selectedChapter === 'overall' ? 'selected' : '' }}>Overall</option>
            @foreach($chapters as $chapter)
                <option value="chapter-{{ $chapter->id }}" {{ $selectedChapter === 'chapter-' . $chapter->id ? 'selected' : '' }}>{{ $chapter->title }}</option>
            @endforeach
        </select>
    </div>
    <div class="overflow-x-auto">
        <table id="scoreBoardTable" class="min-w-full text-sm text-left border">
            <thead>
                <tr class="bg-pink-100">
                    <th class="px-3 py-2">User</th>
                    @foreach($quizzes as $quiz)
                        <th class="px-3 py-2">{{ $quiz->title }}</th>
                    @endforeach
                    <th class="px-3 py-2">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td class="px-3 py-2 font-semibold text-gray-800">{{ $user->name }}</td>
                        @php $userTotal = 0; @endphp
                        @foreach($quizzes as $quiz)
                            @php
                                $userProgress = $progress->where('user_id', $user->id)->where('quiz_id', $quiz->id)->first();
                                $score = $userProgress ? $userProgress->score : null;
                                $userTotal += $score ?? 0;
                            @endphp
                            <td class="px-3 py-2">
                                @if($score !== null)
                                    {{ $score }} / {{ $quiz->questions->count() }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="px-3 py-2 font-bold text-pink-700">{{ $userTotal }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <script>
        // On filter change, reload the score board via AJAX
        document.getElementById('chapterFilter').addEventListener('change', function() {
            // The event handler is attached in the parent JS after AJAX load
        });
    </script>
</div> 