{{--
    Reusable empty state component for tables

    Props:
    - $colspan (int): Number of columns to span
    - $message (string): Message to display
--}}

@props([
    'colspan' => 1,
    'message' => 'No records found.',
])

<tr>
    <td colspan="{{ $colspan }}" class="px-4 py-8 text-center text-slate-500">
        {{ $message }}
    </td>
</tr>

