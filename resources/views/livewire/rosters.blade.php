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
                            {{ $athlete->twitter }}
                        </td>

                        <td class="px-6 py-4">
                            {{ $athlete->instagram }}
                        </td>

                        <td class="px-6 py-4">
                            {{ $athlete->opendorse }}
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