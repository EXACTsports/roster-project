<div class="flex flex-col gap-2 mb-5">
    <span class="text-sm text-right text-white">Success: {{ $success_amount }} / {{ $total }}</span>
    <div class="w-full bg-gray-200 rounded-full dark:bg-gray-700">
        <div class="bg-teal-600 text-xs font-medium text-teal-100 text-center h-2.5 leading-none rounded-full" style="width: {{ $success_amount * 100 / $total }}%"> {{ $success_amount * 100 / $total }}%</div>
    </div>

    <span class="text-sm text-right text-white">Failure: {{ $failure_amount }} / {{ $total }}</span>
    <div class="w-full bg-gray-200 rounded-full dark:bg-gray-700">
        <div class="bg-orange-600 text-xs font-medium text-orange-100 text-center h-2.5 leading-none rounded-full" style="width: {{ $failure_amount * 100 / $total }}%"> {{ $failure_amount * 100 / $total }}%</div>
    </div>
</div>
