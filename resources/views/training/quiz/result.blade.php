<x-app-layout>
    <div class="max-w-2xl mx-auto py-10">
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-purple-800 mb-2">Quiz Results: {{ $quiz->title }}</h2>
            <div class="mb-4">
                <div class="text-gray-700">Total Questions: <span class="font-semibold">{{ $summary['total'] }}</span></div>
                <div class="text-gray-700">Attempted: <span class="font-semibold">{{ $summary['attempted'] }}</span></div>
                <div class="text-gray-700">Correct: <span class="font-semibold text-green-700">{{ $summary['correct'] }}</span></div>
                <div class="text-gray-700">Incorrect: <span class="font-semibold text-red-700">{{ $summary['incorrect'] }}</span></div>
                <div class="text-gray-700">Score: <span class="font-semibold">{{ $summary['score'] }}</span></div>
            </div>
            <hr class="my-4">
            <h3 class="text-lg font-semibold mb-2">Question Breakdown</h3>
            <ol class="list-decimal ml-6 space-y-4">
                @foreach($quiz->questions as $qIndex => $question)
                    @php $detail = $summary['details'][$question->id] ?? null; @endphp
                    <li>
                        <div class="font-medium text-gray-800">Q{{ $qIndex+1 }}: {{ $question->question }}</div>
                        <ul class="ml-4 mt-1">
                            @foreach($question->options as $idx => $choice)
                                <li class="text-sm
                                    @if($choice === $question->correct_answer)
                                        text-green-700 font-semibold
                                    @elseif(isset($answers[$question->id]) && $answers[$question->id] === $choice)
                                        text-red-700 font-semibold
                                    @else
                                        text-gray-700
                                    @endif
                                ">
                                    {{ chr(65 + $idx) }}. {{ $choice }}
                                    @if($choice === $question->correct_answer)
                                        <span class="ml-2 text-green-500">(Correct Answer)</span>
                                    @endif
                                    @if(isset($answers[$question->id]) && $answers[$question->id] === $choice && $choice !== $question->correct_answer)
                                        <span class="ml-2 text-red-500">(Your Answer)</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-1">
                            @if($detail['status'] === 'correct')
                                <span class="text-green-700 font-semibold">Correct</span>
                            @elseif($detail['status'] === 'incorrect')
                                <span class="text-red-700 font-semibold">Incorrect</span>
                                <span class="ml-2 text-gray-600">Your answer: {{ $detail['user'] }}</span>
                                <span class="ml-2 text-green-600">Correct: {{ $detail['correct'] }}</span>
                            @else
                                <span class="text-gray-500">Not Attempted</span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
            <div class="mt-8">
                <a href="{{ route('training.index') }}" class="inline-block px-6 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 font-semibold">Back to Training</a>
            </div>
        </div>
    </div>
</x-app-layout> 