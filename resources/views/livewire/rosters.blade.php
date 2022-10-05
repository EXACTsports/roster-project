<div class="relative p-3 overflow-x-auto shadow-md sm:rounded-lg" wire:init="init">
    <div class="flex justify-between py-5">
        <form>
            <label class="block cursor-pointer">
                <label for="file" class="text-white focus:outline-none focus:ring-2 font-medium rounded-lg cursor-pointer text-sm px-5 py-2.5 mr-2 mb-2 bg-gray-800 hover:bg-gray-700 focus:ring-gray-800 border-gray-700 transition">Import Excel(Roster)</label>
                <input type="file" id="file" class="hidden" wire:model="file" />
            </label>
        </form>
    </div>
    <table class="w-full text-sm text-left text-gray-400" id="roster_table">
        <thead class="text-xs text-gray-400 uppercase">
            <tr>
                <th class="w-1/5 px-6 py-3 bg-gray-800">
                    University
                </th>
                <th class="w-1/5 px-6 py-3">
                    sport
                </th>
                <th class="px-6 py-3 bg-gray-800">
                    URL
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rosters as $roster)
            <tr class="w-full border-b border-gray-700">
                <th class="!w-1/5 px-6 py-4 font-medium text-white bg-gray-800 whitespace-nowrap">
                    {{ $roster->university }}
                </th>
                <td class="!w-1/5 px-6 py-4 ">
                    {{ $roster->sport }}
                </td>
                <td class="px-6 py-4 bg-gray-800">
                    {{ $roster->url }}
                </td>
            </tr>
            @empty
            <tr class="w-full text-center border-gray-700">
                <th colspan="3" class="p-2">Loading...</th>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        window.addEventListener('draw-datatable', event => {
            $('#roster_table').DataTable();
        })
    });
</script>