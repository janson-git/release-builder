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
?>
<form action="/commands/apply" method="get">
    <input type="hidden" name="command" value="{{ $command->getId() }}">
    <input type="hidden" name="context" value="{{ $command->getContext()->serialize() }}">

    <input type="hidden"
           class="js-question-{{ $question['field'] }}"
           name="userData[{{ $question['field'] }}]"
           value="{{ $question['placeholder'] ?? '' }}">

    @if ($command->isConfirmRequired())
        <button onclick="confirmed=confirm('Are you sure to run {{ strtolower($command->getHumanName()) }} ?'); if (!confirmed) return false; window.spinnerOn(this);"
                class="pure-button {{ $command->isDanger() ? 'btn-danger' : '' }}"
        >
            {{ $command->getHumanName() }}
        </button>
    @else
        <button class="pure-button {{ $command->isDanger() ? 'btn-danger' : '' }}"
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