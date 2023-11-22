<?php
/**
 * @var \Commands\Command\CommandProto $command
 */

$classString = "pure-button ";
$classString .= $command->isPrimary() ? 'btn-primary': '';
$classString .= $command->isDanger() ? 'btn-danger': '';

if (isset($classes)) {
    $classString .= ' ' . $classes;
}

$disabled = $disabled ?? false;
?>
@if ($disabled)
    <span class="{{ $classString }}" disabled="disabled">{{ $command->getHumanName() }}</span>
@else
    <a href="/commands/apply?command={{ $command->getId() }}&context={{ $command->getContext()->serialize() }}"
        class="{{ $classString }}"
        @if($command->isConfirmRequired())
            onclick="confirmed=confirm('Are you sure to run \'{{ $command->getHumanName() }}\'?'); if(!confirmed) return false; window.spinnerOn(this);"
        @else
            onclick="window.spinnerOn(this)"
        @endif
    >
        {{ $command->getHumanName() }}
    </a>
@endif
