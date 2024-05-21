<?php
/**
 * @var \Commands\Command\CommandProto $command
 */

$classString = 'btn ';
$classString .= $command->isPrimary() ? 'btn-primary': '';
$classString .= $command->isDanger() ? 'btn-danger': '';
$classString .= $command->isConfirmRequired() ? 'btn-warning': '';

if (isset($classes)) {
    $classString .= ' ' . $classes;
}

$disabled = $disabled ?? $command->isDisabled();
?>
@if ($disabled)
    <span class="inline-block px-4 py-1 {{ $classString }}" disabled="disabled">{{ $command->getHumanName() }}</span>
@else
    <a href="/commands/apply?command={{ $command->getId() }}&context={{ $command->getContext()->serialize() }}"
        class="inline-block px-4 py-1 {{ $classString }}"
        @if($command->isConfirmRequired())
            onclick="confirmed=confirm('Are you sure to run \'{{ $command->getHumanName() }}\'?'); if(!confirmed) return false; window.spinnerOn(this);"
        @else
            onclick="window.spinnerOn(this)"
        @endif
    >
        {{ $command->getHumanName() }}
    </a>
@endif
