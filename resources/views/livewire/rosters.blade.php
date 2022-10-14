<div>
    <div class="relative p-3 overflow-x-auto shadow-md sm:rounded-lg" wire:init="init">
        <div class="flex justify-between py-5">
            <form>
                <label class="block cursor-pointer">
                    <label for="file" class="text-white focus:outline-none focus:ring-2 font-medium rounded-lg cursor-pointer text-sm px-5 py-2.5 mr-2 mb-2 bg-gray-800 hover:bg-gray-700 focus:ring-gray-800 border-gray-700 transition">Import Excel(Roster)</label>
                    <input type="file" id="file" class="hidden" wire:model="file" />
                </label>
            </form>
    
            <div wire:click="scrapAll" class="text-white focus:outline-none focus:ring-2 font-medium rounded-lg cursor-pointer text-sm px-5 py-2.5 mr-2 mb-2 bg-gray-800 hover:bg-gray-700 focus:ring-gray-800 border-gray-700 transition">
                Scrap All ( {{ count($rosters) }} )
            </div>
        </div>
    
        @livewire('status')
    
        <table wire:init="init" class="w-full text-sm text-left text-gray-400" id="roster_table">
            <thead class="text-xs text-gray-400 uppercase">
                <tr>
                    <th class="w-1/5 px-6 py-3 bg-gray-800">
                        University
                    </th>
                    <th class="w-1/5 px-6 py-3">
                        sport
                    </th>
                    <th class="w-1/5 px-6 py-3 bg-gray-800">
                        URL
                    </th>
                    <th class="w-1/5 px-6 py-3">
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
                    <th class="!w-1/5 px-6 py-4 font-medium text-white bg-gray-800 whitespace-nowrap">
                        {{ $roster->university }}
                    </th>
                    <td class="!max-w-sm px-6 py-4 truncate">
                        {{ $roster->sport }}
                    </td>
                    <td class="!max-w-md px-6 py-4 bg-gray-800 truncate">
                        {{ $roster->url }}
                    </td>
                    <td class="!w-1/5 px-6 py-4">
                        
                    </td>
                    <td class="flex items-center justify-center px-6 py-2 bg-gray-100/10">
                        <div id="scrap-{{ $roster->id }}" wire:click="scrap({{ $roster->id }})" class="text-white flex items-center focus:outline-none focus:ring-2 font-medium rounded-lg cursor-pointer text-sm px-5 py-2.5 bg-gray-800 hover:bg-gray-700 focus:ring-gray-800 border-gray-700 transition">
                            <div wire:loading.remove wire:target="scrap({{ $roster->id }})">Scrap</div>
                            <div wire:loading wire:target="scrap({{ $roster->id }})">
                                ...
                            </div>
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

    <script>
        window.addEventListener('scrap', event => {
            $('#scrap-' + event.detail.id).click();
        })
    </script>
</div>