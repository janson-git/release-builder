<?php
/**
 * @var \Commands\Command\CommandProto $command
 */

$question = $command->isQuestion();

$classString = "pure-button ";
//$classString .= $command->isPrimary() ? 'btn-primary': '';
$classString .= $command->isDanger() ? 'btn-danger': '';

if (isset($classes)) {
    $classString .= ' ' . $classes;
}

$disabled = $disabled ?? $command->isDisabled();
?>
@if ($disabled)
    <span class="{{ $classString }}" disabled="disabled">{{ $command->getHumanName() }}</span>
@else
    <form action="/commands/apply" method="get">
        <input type="hidden" name="command" value="{{ $command->getId() }}">
        <input type="hidden" name="context" value="{{ $command->getContext()->serialize() }}">

        <input type="hidden"
               class="js-question-{{ $question['field'] }}"
               name="userData[{{ $question['field'] }}]"
               value="{{ $question['placeholder'] ?? '' }}">

        @if ($command->isConfirmRequired())
            <button onclick="confirmed=confirm('Are you sure to run {{ strtolower($command->getHumanName()) }} ?'); if (!confirmed) return false; window.spinnerOn(this);"
                    class="text-white px-4 py-1 rounded {{ $command->isDanger() ? 'bg-red-400 hover:bg-red-600' : 'bg-orange-400 hover:bg-orange-600' }}"
            >
                {{ $command->getHumanName() }}
            </button>
        @else
            <button class="text-white px-4 py-1 rounded {{ $command->isDanger() ? 'bg-red-400 hover:bg-red-600' : 'bg-orange-400 hover:bg-orange-600' }}"
                    @if (!empty($question['field']) && !empty($question['question']))
                        onclick="answer=prompt('{{ $question['question'] ?? '' }}', '{{ $question['placeholder'] ?? '' }}'); if (!answer) return false; window.spinnerOn(this); document.getElementsByClassName('js-question-{{ $question['field'] }}')[0].value=answer"
                    @else
                        onclick="window.spinnerOn(this)"
                    @endif
            >
                {{ $command->getHumanName() }}
            </button>
        @endif
    </form>
@endif
