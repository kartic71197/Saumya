<div>
    @if ($hasTasks)
    <button
        class="relative inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150"
        wire:click="goToCycles">
        
        <!-- SVG icon -->
        <span class="text-lg">
            <svg class="w-6 h-6 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                <g id="SVGRepo_iconCarrier">
                    <title></title>
                    <g id="Productivity">
                        <path d="M12,22H4a1,1,0,0,0-1,1V47a1,1,0,0,0,1,1h8a1,1,0,0,0,1-1V23A1,1,0,0,0,12,22ZM11,46H5V24h6Z"></path>
                        <path d="M26,16H18a1,1,0,0,0-1,1V47a1,1,0,0,0,1,1h8a1,1,0,0,0,1-1V17A1,1,0,0,0,26,16ZM25,46H19V18h6Z"></path>
                        <path d="M41,31V27a1,1,0,0,0-1-1H32a1,1,0,0,0-1,1V47a1,1,0,0,0,1,1h6.25V46H33V28h6v3Z"></path>
                        <path d="M47,13h6V29.6h2V12a1,1,0,0,0-1-1H46a1,1,0,0,0-1,1V28.17h2Z"></path>
                        <path d="M51,33A10,10,0,1,0,61,43,10,10,0,0,0,51,33Zm3.29,14.71-4-4A1,1,0,0,1,50,43V37h2v5.59l3.71,3.7Z"></path>
                    </g>
                </g>
            </svg>
        </span>

        <!-- Badge -->
        <span class="absolute top-0 right-0 -mt-2 -mr-2 bg-purple-500 text-white text-xs rounded-full px-2 py-1">
            {{ $cycleCount }}
        </span>
    </button>
    @endif
</div>
