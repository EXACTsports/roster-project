<div class="flex flex-col gap-2 mb-5">
    <span class="text-sm text-right text-white">Success: {{ $success_amount }} / {{ $total }}</span>
    <div class="w-full bg-gray-200 rounded-full dark:bg-gray-700">
        <div class="bg-teal-600 text-xs font-medium text-teal-100 text-center h-2.5 leading-none rounded-full" style="width: {{ $total == 0 ? 0 : $success_amount * 100 / $total }}%"> {{ number_format($total == 0 ? 0 : $success_amount * 100 / $total, 2, ".", "") }}%</div>
    </div>

    <span class="text-sm text-right text-white">Failure: {{ $failure_amount }} / {{ $total }}</span>
    <div class="w-full bg-gray-200 rounded-full dark:bg-gray-700">
        <div class="bg-orange-600 text-xs font-medium text-orange-100 text-center h-2.5 leading-none rounded-full" style="width: {{ $total == 0 ? 0 : $failure_amount * 100 / $total }}%"> {{ number_format($total == 0 ? 0 : $failure_amount * 100 / $total, 2, ".", "") }}%</div>
    </div>
</div>
