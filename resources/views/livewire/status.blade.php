<div class="flex flex-col gap-2 mb-5">
    <div class="w-full bg-gray-200 rounded-full dark:bg-gray-700">
        <div class="bg-teal-600 text-xs font-medium text-teal-100 text-center h-2.5 leading-none rounded-full" style="width: {{ $success_percent }}%"> {{ $success_percent }}%</div>
    </div>

    <div class="w-full bg-gray-200 rounded-full dark:bg-gray-700">
        <div class="bg-orange-600 text-xs font-medium text-orange-100 text-center h-2.5 leading-none rounded-full" style="width: {{ $failure_percent }}%"> {{ $failure_percent }}%</div>
    </div>
</div>
