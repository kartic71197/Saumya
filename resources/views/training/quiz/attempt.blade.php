<x-app-layout>

<div class="max-w-2xl mx-auto py-10">
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-2xl font-bold text-purple-800 mb-2">{{ $quiz->title }}</h2>
        @if($quiz->description)
            <p class="text-gray-600 mb-4">{{ $quiz->description }}</p>
        @endif

        @if(isset($progress) && $progress && !$showQuiz)
            <div class="mb-6">
                <div class="text-lg text-gray-800 font-semibold mb-2">You have already attempted this quiz.</div>
                <div class="mb-2">Your last score: <span class="font-bold text-purple-700">{{ $progress->score }} / {{ $quiz->questions->count() }}</span></div>
                <form method="GET" action="">
                    <input type="hidden" name="retake" value="1">
                    <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 font-semibold">Attempt Quizz</button>
                </form>
            </div>
        @else
            <form method="POST" action="{{ route('training.submit-quiz', $quiz->id) }}">
                @csrf
                @foreach($quiz->questions as $qIndex => $question)
                    <div class="mb-6">
                        <div class="font-semibold text-gray-800 mb-2">Q{{ $qIndex+1 }}: {{ $question->question }}</div>
                        @foreach($question->options as $oIndex => $choice)
                            <div class="flex items-center mb-1">
                                <input type="radio" name="answers[{{ $question->id }}]" id="q{{ $question->id }}_{{ $oIndex }}" value="{{ $choice }}" required>
                                <label for="q{{ $question->id }}_{{ $oIndex }}" class="ml-2">{{ chr(65 + $oIndex) }}. {{ $choice }}</label>
                            </div>
                        @endforeach
                    </div>
                @endforeach
                @if($errors->any())
                    <div class="mb-4 text-red-600">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 font-semibold">Submit Quiz</button>
            </form>
        @endif
    </div>
</div>
</x-app-layout>