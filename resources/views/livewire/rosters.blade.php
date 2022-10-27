<div>
    <div class="relative p-3 overflow-x-auto shadow-md sm:rounded-lg" wire:init="init">
        <div class="flex justify-between py-5">
            <form>
                <label class="block cursor-pointer">
                    <label for="file" class="text-white focus:outline-none focus:ring-2 font-medium rounded-lg cursor-pointer text-sm px-5 py-2.5 mr-2 mb-2 bg-gray-800 hover:bg-gray-700 focus:ring-gray-800 border-gray-700 transition">Import Excel(Roster)</label>
                    <input type="file" id="file" class="hidden" wire:model="file" />
                </label>
            </form>

            <div wire:click="test" class="text-white focus:outline-none focus:ring-2 font-medium rounded-lg cursor-pointer text-sm px-5 py-2.5 mr-2 mb-2 bg-gray-800 hover:bg-gray-700 focus:ring-gray-800 border-gray-700 transition">
                Test
            </div>
    
            <div wire:click="scrapAll" class="text-white focus:outline-none focus:ring-2 font-medium rounded-lg cursor-pointer text-sm px-5 py-2.5 mr-2 mb-2 bg-gray-800 hover:bg-gray-700 focus:ring-gray-800 border-gray-700 transition">
                Scrap All ( {{ count($rosters) }} )
            </div>
        </div>
    
        @livewire('status')
    
        <table wire:init="init" class="w-full text-sm text-left text-gray-400" id="roster_table">
            <thead class="text-xs text-gray-400 uppercase">
                <tr>
                    <th class="w-1/6 px-6 py-3 bg-gray-800">
                        University
                    </th>
                    <th class="w-1/6 px-6 py-3">
                        sport
                    </th>
                    <th class="w-1/6 px-6 py-3 bg-gray-800">
                        URL
                    </th>
                    <th class="w-1/6 px-6 py-3">
                        Status
                    </th>
                    <th class="px-6 py-3 bg-gray-800">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody wire:ignore.self>
                @foreach ($rosters as $roster)
                <tr class="w-full border-b border-gray-700">
                    <th class="!w-1/6 px-6 py-4 font-medium text-white bg-gray-800 whitespace-nowrap">
                        {{ $roster->university }}
                    </th>
                    <td class="!max-w-sm px-6 py-4 truncate">
                        {{ $roster->sport }}
                    </td>
                    <td class="!max-w-sm px-6 py-4 bg-gray-800 truncate">
                        {{ $roster->url }}
                    </td>
                    <td class="!w-1/12 px-6 py-4">
                        @if ($roster->status == 0)
                            <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded dark:bg-yellow-200 dark:text-yellow-900">pending</span>
                        @endif
                        @if ($roster->status == 1)
                        <span class="bg-green-100 text-green-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded dark:bg-green-200 dark:text-green-900">Success</span>
                        @endif
                        @if ($roster->status == 2)
                            <span class="bg-red-100 text-red-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded dark:bg-red-200 dark:text-red-900">Failure</span>
                        @endif
                    </td>
                    <td class="flex items-center justify-center gap-1 px-6 py-2 bg-gray-100/10">
                        <div id="scrap-{{ $roster->id }}" wire:click="scrap({{ $roster->id }})" class="text-white flex items-center focus:outline-none focus:ring-2 font-medium rounded-lg {{ $roster->status != 0 ? 'cursor-not-allowed' : 'cursor-pointer hover:bg-gray-700 focus:ring-gray-800' }} text-sm px-5 py-2.5 bg-gray-800 border-gray-700 transition">
                            <div wire:loading.remove wire:target="scrap({{ $roster->id }})">{{ $roster->status != 0 ? 'Done' : 'Scrap' }}</div>
                            <div wire:loading wire:target="scrap({{ $roster->id }})">
                                ...
                            </div>
                        </div>

                        <div wire:click="view({{ $roster->id }})" class="text-white flex items-center focus:outline-none focus:ring-2 font-medium rounded-lg cursor-pointer hover:bg-gray-700 focus:ring-gray-800 text-sm px-5 py-2.5 bg-gray-800 border-gray-700 transition">
                            Athletes
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    
        <div wire:loading class="fixed inset-0 z-10 transition-opacity backdrop-blur-xs">
            <div class="flex flex-col items-center justify-center w-full h-full text-white">
                loading...
            </div>
        </div>
    </div>

    {{-- athletes modal for each roster --}}
    <x-modal name="athelete">
        {{-- {{ count($selectedAthletes) }} --}}
        {{-- header --}}
        <div class="text-2xl font-bold text-white">
            @if ($selectedRoster != null)
                {{ $selectedRoster->university }} - {{ $selectedRoster->sport }} - {{ $selectedRoster->id }}
            @endif
        </div>
        {{-- athlete table --}}
        <table wire:init="init" class="w-full text-sm text-left text-gray-400" id="athlete_table">
            <thead class="text-xs text-gray-400 uppercase">
                <tr>
                    <th class="px-6 py-3 bg-gray-800">
                        No
                    </th>
                    <th class="px-6 py-3">
                        Image
                    </th>
                    <th class="px-6 py-3 bg-gray-800">
                        Name
                    </th>
                    <th class="px-6 py-3">
                        P
                    </th>
                    <th class="px-6 py-3 bg-gray-800">
                        Y
                    </th>
                    <th class="px-6 py-3">
                        HomeTown
                    </th>
                    <th class="px-6 py-3 bg-gray-800">
                        Height
                    </th>
                    <th class="px-6 py-3">
                        High School
                    </th>
                    <th class="px-6 py-3 bg-gray-800">
                        T
                    </th>
                    <th class="px-6 py-3">
                        I
                    </th>
                    <th class="px-6 py-3 bg-gray-800">
                        O
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($selectedAthletes as $key => $athlete)
                    <tr>
                        <th class="px-6 py-4 font-medium text-white bg-gray-800 whitespace-nowrap">
                            {{ $key + 1 }}
                        </th>
    
                        <td class="px-6 py-4">
                            <img src="{{ $athlete->image_url == 'undefined' ? '/img/male.jpeg' : $athlete->image_url }}" width="50" height="50" />
                        </td>
    
                        <td class="px-6 py-4">
                            {{ $athlete->name }}
                        </td>
    
                        <td class="px-6 py-4">
                            {{ $athlete->position }}
                        </td>
    
                        <td class="px-6 py-4">
                            {{ $athlete->year }}
                        </td>
    
                        <td class="px-6 py-4">
                            {{ $athlete->home_town }}
                        </td>

                        <td class="px-6 py-4">
                            {{ $athlete->height }}
                        </td>

                        <td class="px-6 py-4">
                            {{ $athlete->high_school }}
                        </td>

                        <td class="px-6 py-4">
                            @if ($athlete->twitter)
                            <a href="{{ $athlete->twitter }}">
                                <svg class="w-5 h-5 fill-current" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"></path></svg>
                            </a>
                            @endif
                        </td>

                        <td class="px-6 py-4">
                            @if ($athlete->instagram)
                            <a href="{{ $athlete->instagram }}">
                                <svg class="w-5 h-5 fill-current" role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z"></path></svg>
                            </a>
                            @endif
                        </td>

                        <td class="px-6 py-4">
                            @if ($athlete->opendorse)
                            <a href="{{ $athlete->opendorse }}">
                                <svg class="w-5 h-5 fill-current" role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0zm5.01 4.744c.688 0 1.25.561 1.25 1.249a1.25 1.25 0 0 1-2.498.056l-2.597-.547-.8 3.747c1.824.07 3.48.632 4.674 1.488.308-.309.73-.491 1.207-.491.968 0 1.754.786 1.754 1.754 0 .716-.435 1.333-1.01 1.614a3.111 3.111 0 0 1 .042.52c0 2.694-3.13 4.87-7.004 4.87-3.874 0-7.004-2.176-7.004-4.87 0-.183.015-.366.043-.534A1.748 1.748 0 0 1 4.028 12c0-.968.786-1.754 1.754-1.754.463 0 .898.196 1.207.49 1.207-.883 2.878-1.43 4.744-1.487l.885-4.182a.342.342 0 0 1 .14-.197.35.35 0 0 1 .238-.042l2.906.617a1.214 1.214 0 0 1 1.108-.701zM9.25 12C8.561 12 8 12.562 8 13.25c0 .687.561 1.248 1.25 1.248.687 0 1.248-.561 1.248-1.249 0-.688-.561-1.249-1.249-1.249zm5.5 0c-.687 0-1.248.561-1.248 1.25 0 .687.561 1.248 1.249 1.248.688 0 1.249-.561 1.249-1.249 0-.687-.562-1.249-1.25-1.249zm-5.466 3.99a.327.327 0 0 0-.231.094.33.33 0 0 0 0 .463c.842.842 2.484.913 2.961.913.477 0 2.105-.056 2.961-.913a.361.361 0 0 0 .029-.463.33.33 0 0 0-.464 0c-.547.533-1.684.73-2.512.73-.828 0-1.979-.196-2.512-.73a.326.326 0 0 0-.232-.095z"></path></svg>
                            </a>
                            @endif
                        </td>

                        {{-- <td class="px-6 py-4">
                            <div wire:click="googleScrap('{{ $selectedRoster->university }}', '{{ $selectedRoster->sport }}', {{ $athlete->id }})" class="text-white flex items-center justify-center focus:outline-none focus:ring-2 font-medium rounded-lg cursor-pointer hover:bg-gray-600 focus:ring-gray-700 text-sm px-5 py-2.5 bg-gray-700 border-gray-600 transition">
                                scrap contact
                            </div>
                        </td> --}}
                    </tr>
                @empty
                    <tr>
                        <td class="text-center" colspan="6">Not found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-modal>

    <script>
        window.addEventListener('scrap', event => {
            $('#scrap-' + event.detail.id).click();
        })
    </script>
</div>